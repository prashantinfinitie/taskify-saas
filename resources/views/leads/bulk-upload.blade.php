@extends('layout')
@section('title')
    {{ get_label('bulk_upload', 'Bulk Upload') }} - {{ get_label('leads', 'Leads') }}
@endsection
@section('content')
    <div class="container-fluid">
        <!-- Header section with navigation -->
        <div class="d-flex justify-content-between mb-2 mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ url('home') }}">{{ get_label('home', 'Home') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <?= get_label('leads_management', 'Leads Management') ?>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('leads.index') }}">{{ get_label('leads', 'Leads') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ get_label('bulk_upload', 'Bulk Upload') }}
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('leads.create') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-original-title=" <?= get_label('create_lead', 'Create Lead') ?>"><i
                            class="bx bx-plus"></i></button></a>
                <a href="{{ route('leads.index') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="right"
                        data-bs-original-title=" <?= get_label('list_view', 'List View') ?>"><i
                            class="bx bx-list-ul"></i></button></a>
            
            </div>
        </div>

        <!-- Alerts for messages -->
        <div id="alert-container"></div>

        <!-- Step indicator -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-3">
                        <ul class="nav nav-pills nav-fill" id="import-steps">
                            <li class="nav-item">
                                <a class="nav-link active" id="step1-tab">
                                    <i class="bx bx-upload me-1"></i> 1. {{ get_label('upload_file', 'Upload File') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link disabled" id="step2-tab">
                                    <i class="bx bx-link me-1"></i> 2. {{ get_label('map_fields', 'Map Fields') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link disabled" id="step3-tab">
                                    <i class="bx bx-check-circle me-1"></i> 3.
                                    {{ get_label('import_data', 'Import Data') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 1: Upload File -->
        <div class="row" id="step1-content">
            <div class="col-md-12 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-info" role="alert">
                            <i class="bx bx-info-circle me-1"></i>
                            {{ get_label('leads_bulk_upload_info', 'Supported file formats: Excel (.xlsx, .xls) and CSV (.csv). Maximum file size: 10MB.') }}
                        </div>
                        <!-- ✅ Download Buttons -->
                        <div class="mb-3">
                            <a href="{{ asset('storage/files/Leads Bulk Upload Sample File.xlsx') }}" class="btn btn-success me-2"
                                download>
                                <i class="bx bx-download me-1"></i>
                                {{ get_label('download_sample_file', 'Download Sample File') }}
                            </a>
                            <a href="{{ asset('storage/files/Leads_Bulk_Upload_Instructions.pdf') }}"
                                class="btn btn-outline-info" download>
                                <i class="bx bx-help-circle me-1"></i>
                                {{ get_label('help_instructions', 'Help & Instructions') }}
                            </a>
                        </div>
                        <form id="upload-form" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="file" id="file" required class="form-control mb-3"
                                accept=".xlsx, .xls, .csv">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-upload me-1"></i>
                                {{ get_label('upload_and_continue', 'Upload & Continue') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Map Fields -->
        <div class="row d-none" id="step2-content">
            <div class="col-md-12 mb-3">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            {{ get_label('map_excel_fields_to_database_fields', 'Map Excel Fields to Database Fields') }}
                        </h5>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="back-to-step1">
                            <i class="bx bx-arrow-back"></i> {{ get_label('back_to_upload', 'Back to Upload') }}
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- File Summary -->
                        <div class="alert alert-info mb-3">
                            <div id="file-summary"></div>
                        </div>

                        <!-- Error Container -->
                        <div class="alert alert-danger d-none" id="mapping-error-alert">

                            <div id="mapping-error-content"></div>
                        </div>

                        <!-- Success Container -->
                        <div class="alert alert-success d-none" id="mapping-success-alert">

                            <div id="mapping-success-content"></div>
                        </div>

                        <form id="mapping-form" method="POST" action="">
                            @csrf
                            <input type="hidden" id="temp_path" name="temp_path">

                            <div class="row">
                                <div class="col-md-6">
                                    <h6>{{ get_label('field_mapping', 'Field Mapping') }}</h6>
                                    <p class="text-muted small text-danger">
                                        {{ get_label('map_fields_to_columns', 'Match each required field with a column from your file') }}
                                    </p>

                                    <div class="table-responsive">
                                        <table class="table-bordered table-sm table">
                                            <thead>
                                                <tr>
                                                    <th width="40%">{{ get_label('database_field', 'Database Field') }}
                                                    </th>
                                                    <th width="60%">{{ get_label('excel_field', 'Excel Field') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="mapping-body">
                                                <!-- Field mappings will be populated here -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="d-flex justify-content-between mt-3">
                                        <button type="button" class="btn btn-primary" id="preview-mapped-leads">
                                            <i class="bx bx-search me-1"></i>
                                            {{ get_label('preview_mapped_data', 'Preview Mapped Data') }}
                                        </button>

                                        <button type="submit" id="submit-btn" class="btn btn-success" disabled>
                                            <i class="bx bx-import me-1"></i>
                                            {{ get_label('import_data', 'Import Data') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h6>{{ get_label('data_preview', 'Data Preview') }}</h6>
                                    <div id="preview-container">
                                        <div id="raw-preview" class="table-responsive">
                                            <!-- Raw data preview will be shown here -->
                                        </div>

                                        <div id="mapped-preview" class="table-responsive d-none">
                                            <!-- Mapped data preview will be shown here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Import Results -->
        <div class="row d-none" id="step3-content">
            <div class="col-md-12 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ get_label('import_results', 'Import Results') }}</h5>
                    </div>
                    <div class="card-body">
                        <div id="results-summary"></div>
                        <div id="results-details" class="mt-3"></div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" id="start-new-import">
                                <i class="bx bx-upload me-1"></i> {{ get_label('start_new_import', 'Start New Import') }}
                            </button>

                            <a href="{{ route('leads.index') }}" class="btn btn-primary">
                                <i class="bx bx-list-ul me-1"></i> {{ get_label('leads', 'Leads') }}
                            </a>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Define routes object for use in JS file
        const routes = {
            parse: "{{ route('leads.parse') }}",
            previewMappedLeads: "{{ route('leads.previewMappedLeads') }}",
            import: "{{ route('leads.import') }}"
        };

        // Direct label variables
        const label_uploading = "{{ get_label('uploading', 'Uploading') }}";
        const label_upload_and_continue = "{{ get_label('upload_and_continue', 'Upload & Continue') }}";
        const label_processing = "{{ get_label('processing', 'Processing') }}";
        const label_preview_mapped_leads = "{{ get_label('preview_mapped_leads', 'Preview Mapped Leads') }}";
        const label_importing = "{{ get_label('importing', 'Importing') }}";
        const label_import_success = "{{ get_label('import_completed_successfully', 'Import Completed Successfully') }}";
        const label_imported_leads = "{{ get_label('imported_leads', 'Imported Leads') }}";
        const label_import_partially_completed =
            "{{ get_label('import_partially_completed', 'Import Partially Completed') }}";
        const label_no_detailed_error_information_available =
            "{{ get_label('no_detailed_error_information_available', 'No detailed error information available') }}";
        const label_import_errors = "{{ get_label('import_errors', 'Import Errors') }}";
        const label_successfully_imported = "{{ get_label('successfully_imported', 'Successfully imported') }}";
        const label_data_mapped_success =
            "{{ get_label('data_mapped_successfully_please_review_the_preview_before_importing', 'Data mapped successfully. Please review the preview before importing.') }}";
        const label_showing_preview = 'Showing preview of first ${count} rows out of ${total} total rows';
        const label_file_processed = "{{ get_label('file_processed_successfully', 'File processed successfully') }}";
        const label_total_rows = "{{ get_label('total_rows', 'Total rows') }}";
        const label_select_option = "{{ get_label('select', 'Select') }}";
        const label_show_raw_data = "{{ get_label('show_raw_data', 'Show raw data') }}";
        const label_show_mapped_data = "{{ get_label('show_mapped_data', 'Show mapped data') }}";
    </script>
    <script src="{{ asset('assets/js/pages/bulk-upload.js') }}"></script>
@endsection
