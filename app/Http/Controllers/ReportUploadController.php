<?php

namespace App\Http\Controllers;

use App\Contracts\ReportRepositoryInterface;
use App\Enums\ReportStatus;
use App\Jobs\ParseSarifReportJob;
use App\Rules\ValidSarifFile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Handles SARIF report uploads.
 *
 * The controller only validates and stores the file, then dispatches the
 * parsing job. All domain failures are handled inside the job so the upload
 * endpoint stays thin and never surfaces technical errors to the user.
 */
class ReportUploadController extends Controller
{
    /**
     * Inject the report persistence contract.
     */
    public function __construct(private readonly ReportRepositoryInterface $reports) {}

    /**
     * Show the SARIF upload form.
     */
    public function create(): View
    {
        return view('reports.create', [
            'maxKilobytes' => (int) config('clarif.uploads.max_kilobytes'),
        ]);
    }

    /**
     * Store an uploaded SARIF file and queue it for parsing.
     */
    public function store(Request $request): RedirectResponse
    {
        $disk = config('clarif.uploads.disk');
        $maxKilobytes = (int) config('clarif.uploads.max_kilobytes');

        $validated = $request->validate([
            'report' => [
                'required',
                'file',
                'extensions:sarif,json',
                'max:'.$maxKilobytes,
                new ValidSarifFile,
            ],
        ]);

        $file = $validated['report'];

        // Store with a random name and a neutral, non-executable extension.
        $storedPath = $file->storeAs('', Str::uuid()->toString().'.json', $disk);

        $report = $this->reports->create([
            'original_filename' => mb_substr(basename($file->getClientOriginalName()), 0, 255),
            'tool_name' => config('clarif.unknown_tool_name'),
            'stored_path' => $storedPath,
            'status' => ReportStatus::Pending,
        ]);

        ParseSarifReportJob::dispatch($report, $storedPath);

        return redirect()->route('reports.show', $report);
    }
}
