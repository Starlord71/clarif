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

    'severities' => [
        'error' => 'Error',
        'warning' => 'Warning',
        'note' => 'Note',
        'none' => 'None',
    ],

    // Upload form.
    'upload_title' => 'Upload SARIF report',
    'upload_heading' => 'Upload a SARIF report',
    'upload_description' => 'Select a .sarif or .json file exported by CodeQL, ESLint, or Semgrep. Clarif parses it in the background and normalizes its findings.',
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

    // Pagination.
    'per_page_label' => 'Per page',
    'pagination_previous' => 'Previous',
    'pagination_next' => 'Next',
    'pagination_goto' => 'Go to page :page',
    'pagination_navigation' => 'Pagination Navigation',
    'pagination_summary' => 'Showing :first to :last of :total',

];
