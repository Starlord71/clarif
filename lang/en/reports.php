<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Reports UI
    |--------------------------------------------------------------------------
    |
    | Every user-facing string of the report listing, upload form, detail view
    | and comparison UI. Views must reference these keys through __(); no text
    | is hardcoded in Blade templates.
    |
    */

    'brand' => 'Clarif',
    'nav_reports' => 'Reports',
    'nav_upload' => 'Upload',
    'nav_about' => 'What is SARIF?',
    'nav_help' => 'Help',
    'language_label' => 'Language',

    // Listing.
    'reports_title' => 'Reports',
    'reports_subtitle' => 'Every SARIF report ingested by Clarif.',
    'reports_empty' => 'No reports yet.',
    'reports_empty_hint' => 'Upload your first SARIF file to get started.',
    'upload_report' => 'Upload report',
    'column_number' => '#',
    'column_file' => 'File',
    'column_tool' => 'Tool',
    'column_status' => 'Status',
    'column_findings' => 'Findings',
    'column_uploaded' => 'Uploaded',
    'column_actions' => 'Actions',
    'action_view' => 'View',
    'action_delete' => 'Delete',
    'delete_modal_title' => 'Delete report',
    'delete_modal_body' => 'Are you sure you want to delete this report? All of its findings will be removed. This action cannot be undone.',
    'delete_confirm_yes' => 'Yes, delete',
    'delete_cancel' => 'Cancel',
    'report_deleted' => 'The report was deleted successfully.',
    'delete_failed' => 'The report could not be deleted. Please try again.',

    // Status and severity labels (used by the enums' label() methods).
    'statuses' => [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'failed' => 'Failed',
    ],

    // SARIF defines abstract levels (error, warning, note, none); these are
    // the app-owned, business-friendly labels Clarif shows instead.
    'severities' => [
        'error' => 'High',
        'warning' => 'Medium',
        'note' => 'Low',
        'none' => 'Info',
    ],

    // Tooltip on severity badges, exposing the original SARIF level.
    'severity_raw' => 'SARIF level: :level',

    // Upload form.
    'upload_title' => 'Upload SARIF report',
    'upload_heading' => 'Upload a SARIF report',
    'upload_description' => 'Select a .sarif or .json file in SARIF 2.1.0 format, for example exported by CodeQL, ESLint, Semgrep, or OWASP ZAP. Clarif parses it in the background and normalizes its findings.',
    'upload_field_label' => 'SARIF file',
    'upload_choose_file' => 'Choose file',
    'upload_no_file' => 'No file selected',
    'upload_submit' => 'Upload and parse',
    'upload_formats_hint' => 'Accepted formats: .sarif or .json in SARIF 2.1.0 format. Maximum size: :size MB.',
    'upload_tips_title' => 'Before you upload',
    'upload_tip_one' => 'The file must be a SARIF 2.1.0 document. Native JSON exports from tools such as Semgrep or ZAP are not accepted.',
    'upload_tip_two' => 'Only the first run (runs[0]) of the file is processed.',
    'upload_tip_three' => 'Parsing runs in the background; the status updates on the report page.',
    'back_to_reports' => 'Back to reports',

    // Detail.
    'report_title' => 'Report #:id',
    'detail_filename' => 'Filename',
    'detail_tool' => 'Tool',
    'detail_version' => 'Tool version',
    'detail_status' => 'Status',
    'detail_total_findings' => 'Total findings',
    'detail_uploaded' => 'Uploaded',
    'detail_unknown' => 'Unknown',

    'failure_title' => 'This report could not be processed',
    'failure_reference' => 'Reference: #:id',

    // Detail filters.
    'filters_title' => 'Filter findings',
    'filter_severity' => 'Severity',
    'filter_rule' => 'Rule ID',
    'filter_file' => 'File path',
    'filter_all' => 'All severities',
    'filter_apply' => 'Apply',
    'filter_reset' => 'Reset',
    'filter_rule_placeholder' => 'e.g. no-unused-vars',
    'filter_file_placeholder' => 'e.g. src/app.js',

    // Findings table.
    'findings_title' => 'Findings',
    'findings_empty' => 'This report has no findings.',
    'findings_no_match' => 'No findings match the current filters.',
    'column_severity' => 'Severity',
    'column_rule' => 'Rule',
    'column_line' => 'Line',
    'column_message' => 'Message',
    'value_unknown' => '-',
    'findings_view_detail' => 'View details',
    'finding_modal_title' => 'Finding details',
    'finding_modal_close' => 'Close',

    // Comparison.
    'column_select' => 'Select',
    'select_report' => 'Select report #:id',
    'compare_action' => 'Compare',
    'compare_selection_hint' => 'Select exactly two reports to compare them.',
    'compare_selection_ready' => 'Two reports selected. Ready to compare.',
    'compare_selection_error' => 'Select exactly two reports to compare.',
    'compare_title' => 'Compare runs',
    'compare_subtitle' => 'Findings classified as new, resolved or persistent between two runs.',
    'compare_base_label' => 'Base run',
    'compare_head_label' => 'Head run',
    'compare_new_title' => 'New findings',
    'compare_new_description' => 'Present in the head run but not in the base run.',
    'compare_new_empty' => 'No new findings.',
    'compare_resolved_title' => 'Resolved findings',
    'compare_resolved_description' => 'Present in the base run but no longer in the head run.',
    'compare_resolved_empty' => 'No resolved findings.',
    'compare_persistent_title' => 'Persistent findings',
    'compare_persistent_description' => 'Present in both runs.',
    'compare_persistent_empty' => 'No persistent findings.',
    'compare_line_drift_note' => 'Findings are matched by a fingerprint of rule, file and estimated line. If unrelated changes shift a finding\'s line, the fingerprint no longer matches even though the issue is the same (the known "line drift problem"). It is a conscious v1 limitation, not a bug.',

    // Comparison help (collapsible explainer shown at the top of the compare view).
    'compare_help_summary' => 'What is this view?',
    'compare_help_intro' => 'This view compares two runs of the same project: the base run (older) and the head run (newer). Each finding is matched by a fingerprint built from its rule, file and estimated line, so the base is always read as the past and the head as the present.',
    'compare_help_fingerprint' => 'Because matching relies on the line number, unrelated changes that add or remove lines above a finding can shift it and make it look new or resolved even when the issue is the same (the "line drift problem"). It is a known limitation of this version, not a bug.',

    // Pagination.
    'per_page_label' => 'Per page',
    'pagination_previous' => 'Previous',
    'pagination_next' => 'Next',
    'pagination_goto' => 'Go to page :page',
    'pagination_navigation' => 'Pagination Navigation',
    'pagination_summary' => 'Showing :first to :last of :total',

];
