@extends('layout')

@section('title')
    {{ get_label('embed_code', 'Embed Code') }}
@endsection

@section('content')
    {{-- @dd($leadForm->embed_code); --}}
    <div class="container-fluid">

        <div class="d-flex justify-content-between mb-2 mt-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home.index') }}">{{ get_label('home', 'Home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span>{{ get_label('leads_management', 'Leads Management') }}</span>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('lead-forms.index') }}">{{ get_label('lead_forms', 'Lead Forms') }}</a>
                    </li>
                    <li class="breadcrumb-item active">
                        {{ get_label('embed_code', 'Embed code') }}
                    </li>
                </ol>
            </nav>
        </div>


        <div class="card rounded-4 border-0 shadow">
            <div class="card-body p-4">

                <h4 class="fw-semibold mb-4">{{ get_label('embed_your_form', 'Embed Your Form') }}</h4>

                <ul class="nav nav-tabs mb-4" id="embedTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="iframe-tab" data-bs-toggle="tab"
                            data-bs-target="#iframe-content" type="button" role="tab">
                            <i class="bx bx-code-alt me-2"></i>{{ get_label('iframe_embed', 'iFrame Embed') }}
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="widget-tab" data-bs-toggle="tab" data-bs-target="#modal-content"
                            type="button" role="tab">
                            <i class="bx bx-widget me-2"></i>{{ get_label('floating_widget', 'Floating Widget') }}
                        </button>
                    </li>
                </ul>


                <div class="tab-content" id="embedTabContent">

                    <div class="tab-pane fade show active" id="iframe-content" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-6">

                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <label class="form-label fw-semibold mb-0">
                                            {{ get_label('iframe_embed_code', 'iFrame Embed Code') }}
                                        </label>
                                        <button class="btn btn-sm btn-primary"
                                            onclick="copyToClipboard('iframeEmbedCode', this)">
                                            <i class="bx bx-copy me-1"></i>{{ get_label('copy', 'Copy') }}
                                        </button>
                                    </div>
                                    <textarea class="form-control bg-light rounded-3 border" id="iframeEmbedCode" rows="6"
                                        readonly>{!! $leadForm->embed_code !!}</textarea>
                                    <small class="text-muted d-block mt-2">
                                        {{ get_label('iframe_description', 'Copy and paste this code into your website to embed the form as an iframe.') }}
                                    </small>
                                </div>
                            </div>
                            <div class="col-lg-6">

                                <div class="mb-4">
                                    <label class="form-label fw-semibold mb-3">
                                        {{ get_label('preview', 'Preview') }}
                                    </label>
                                    <div class="rounded-3 overflow-hidden border bg-white" style="height: 400px;">
                                        <div class="w-100 h-100">
                                            {!! str_replace('<iframe', '<iframe style="width:100%; height:100%; border:0;"', $leadForm->embed_code) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Floating Widget Tab -->
                    <div class="tab-pane fade" id="modal-content" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-6">

                                <div class="mb-4">

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-semibold text-danger mb-0">
                                                <i class="bx bxl-html5 me-1"></i>HTML Code
                                            </label>
                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="copyToClipboard('floatingHtmlCode', this)">
                                                <i class="bx bx-copy me-1"></i>{{ get_label('copy', 'Copy') }}
                                            </button>
                                        </div>
                                        <textarea class="form-control bg-light rounded-3 border" id="floatingHtmlCode"
                                            rows="8" readonly>
                                            <div onclick="toggleLeadFormWidget()">
                                                <span>
                                                    <a href="javascript:void(0);">
                                                        <img src="https://upload.wikimedia.org/wikipedia/commons/9/9c/Forms.png" class="leadform-embed-icon" alt="{{ $leadForm->title }}">
                                                    </a>
                                                </span>
                                            </div>

                                            <div id="leadFormWidgetContainer" class="leadform-widget-container">
                                                <div class="leadform-widget-header">
                                                    <h5>{{ $leadForm->title }}</h5>
                                                    <button onclick="toggleLeadFormWidget()" class="leadform-close-btn">×</button>
                                                </div>
                                                <div class="leadform-widget-body">
                                                    {!! str_replace(
        ['<iframe', '</iframe>'],
        ['<iframe class="leadform-widget-iframe"', '</iframe>'],
        $leadForm->embed_code,
    ) !!}
                                                </div>
                                            </div>
                                         </textarea>
                                    </div>


                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-semibold text-info mb-0">
                                                <i class="bx bxl-css3 me-1"></i>CSS Code
                                            </label>
                                            <button class="btn btn-sm btn-outline-info"
                                                onclick="copyToClipboard('floatingCssCode', this)">
                                                <i class="bx bx-copy me-1"></i>{{ get_label('copy', 'Copy') }}
                                            </button>
                                        </div>
                                        <textarea class="form-control bg-light rounded-3 border" id="floatingCssCode"
                                            rows="12" readonly>
                                            <style>
                                            /* Floating Form Button */
                                            .leadform-embed-icon {
                                                width: 54px !important;
                                                border-radius: 50px;
                                                cursor: pointer;
                                                position: fixed;
                                                bottom: 0.7rem;
                                                right: 11.625rem;
                                                padding: 10px;
                                                background-color: #ffffff;
                                                z-index: 999999;
                                                transition: transform 0.3s ease;
                                                box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3);
                                            }

                                            .leadform-embed-icon:hover {
                                                transform: translateY(-5px);
                                            }

                                            /* Floating Form Container */
                                            .leadform-widget-container {
                                                position: fixed;
                                                bottom: 90px;
                                                right: 20px;
                                                width: 400px;
                                                height: 500px;
                                                background: white;
                                                border-radius: 12px;
                                                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
                                                display: none;
                                                flex-direction: column;
                                                z-index: 1000001;
                                                overflow: hidden;
                                            }

                                            .leadform-widget-container.active {
                                                display: flex;
                                            }

                                            .leadform-widget-header {
                                                padding: 15px 20px;
                                                color: white;
                                                display: flex;
                                                justify-content: space-between;
                                                align-items: center;
                                            }

                                            .leadform-widget-header h5 {
                                                margin: 0;
                                                font-size: 16px;
                                            }

                                            .leadform-close-btn {
                                                background: none;
                                                border: none;
                                                color: white;
                                                font-size: 24px;
                                                cursor: pointer;
                                                padding: 0;
                                                width: 30px;
                                                height: 30px;
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                                border-radius: 50%;
                                                transition: background-color 0.2s;
                                            }

                                            .leadform-close-btn:hover {
                                                background-color: rgba(255, 255, 255, 0.2);
                                            }

                                            .leadform-widget-body {
                                                flex: 1;
                                                overflow: hidden;
                                            }

                                            .leadform-widget-iframe {
                                                width: 100%;
                                                height: 100%;
                                                border: none;
                                            }

                                            /* Mobile Responsive */
                                            @media (max-width: 768px) {
                                                .leadform-widget-container {
                                                    width: calc(100vw - 40px);
                                                    height: 70vh;
                                                    bottom: 90px;
                                                    right: 20px;
                                                    left: 20px;
                                                }
                                            
                                                .leadform-embed-icon {
                                                    width: 50px;
                                                    height: 50px;
                                                    bottom: 15px;
                                                    right: 15px;
                                                }
                                            }
                                            </style>
                                        </textarea>
                                    </div>

                                    <!-- JavaScript Section -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-semibold text-warning mb-0">
                                                <i class="bx bxl-javascript me-1"></i>JavaScript Code
                                            </label>
                                            <button class="btn btn-sm btn-outline-warning"
                                                onclick="copyToClipboard('floatingJsCode', this)">
                                                <i class="bx bx-copy me-1"></i>{{ get_label('copy', 'Copy') }}
                                            </button>
                                        </div>
                                        <textarea class="form-control bg-light rounded-3 border" id="floatingJsCode"
                                            rows="8" readonly>
                                            <script>
                                                function toggleLeadFormWidget() {
                                                    const container = document.getElementById('leadFormWidgetContainer');
                                                    if (container.classList.contains('active')) {
                                                        container.classList.remove('active');
                                                        console.log('Lead form widget closed');
                                                    } else {
                                                        container.classList.add('active');
                                                        console.log('Lead form widget opened');
                                                    }
                                                }
                                            
                                                // Close form when clicking outside
                                                document.addEventListener('click', function (event) {
                                                    const container = document.getElementById('leadFormWidgetContainer');
                                                    const icon = document.querySelector('.leadform-embed-icon');
                                                    if (container && container.classList.contains('active')) {
                                                        if (!container.contains(event.target) && !icon.contains(event.target)) {
                                                            container.classList.remove('active');
                                                        }
                                                    }
                                                });
                                            
                                                // Optional: Close with Escape key
                                                document.addEventListener('keydown', function (event) {
                                                    if (event.key === 'Escape') {
                                                        const container = document.getElementById('leadFormWidgetContainer');
                                                        if (container && container.classList.contains('active')) {
                                                            container.classList.remove('active');
                                                        }
                                                    }
                                                });
                                            </script>
                                        </textarea>
                                    </div>

                                    <!-- Copy All Button -->
                                    <div class="text-center">
                                        <button class="btn btn-success" onclick="copyAllFloatingCode(event)">
                                            <i
                                                class="bx bx-copy-alt me-2"></i>{{ get_label('copy_all_code', 'Copy All Code') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <!-- Floating Widget Preview -->
                                <div class="mb-4">
                                    <label class="form-label fw-semibold mb-3">
                                        {{ get_label('preview', 'Preview') }}
                                    </label>
                                    <div class="mb-3 text-center">
                                        <button type="button" class="btn btn-primary"
                                            onclick="previewFloatingWidget(event)">
                                            <i
                                                class="bx bx-show me-2"></i>{{ get_label('preview_widget', 'Preview Widget') }}
                                        </button>
                                    </div>

                                    <!-- Preview Box Container -->
                                    <div class="preview-box bg-light rounded-3 mb-3 border p-3"
                                        style="height: 300px; position: relative; overflow: hidden;">
                                        <div class="text-muted mb-2 text-center">
                                            <small>Widget Preview Area</small>
                                        </div>
                                        <!-- Preview Floating Widget inside the box -->
                                        <div id="previewFloatingWidget"
                                            style="display: none; position: relative; height: 100%;">
                                            <div onclick="togglePreviewLeadForm()"
                                                style="position: absolute; bottom: 20px; right: 20px;">
                                                <span>
                                                    <a href="javascript:void(0);">
                                                        <img src="https://upload.wikimedia.org/wikipedia/commons/9/9c/Forms.png"
                                                            class="preview-embed-icon" alt="{{ $leadForm->title }}">
                                                    </a>
                                                </span>
                                            </div>
                                            <div id="previewLeadFormContainer" class="preview-lead-form-container">
                                                <div class="lead-form-header">
                                                    <h5>{{ $leadForm->title }}</h5>
                                                    <button onclick="togglePreviewLeadForm()" class="close-btn">×</button>
                                                </div>
                                                <div class="lead-form-body">
                                                    {!! str_replace(
        ['<iframe', '</iframe>'],
        ['<iframe  style="width:100%; height:100%; border:0;" class="lead-form-iframe"', '</iframe>'],
        $leadForm->embed_code,
    ) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle me-2"></i>
                                        <strong>{{ get_label('widget_info', 'Widget Information') }}:</strong><br>
                                        {{ get_label('widget_description', 'This creates a floating button that toggles a form container. Perfect for non-intrusive lead capture.') }}
                                    </div>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ get_label('features', 'Features') }}:</h6>
                                            <ul class="list-unstyled mb-0">
                                                <li><i
                                                        class="bx bx-check text-success me-2"></i>{{ get_label('floating_button', 'Floating Button') }}
                                                </li>
                                                <li><i
                                                        class="bx bx-check text-success me-2"></i>{{ get_label('responsive_design', 'Responsive Design') }}
                                                </li>
                                                <li><i
                                                        class="bx bx-check text-success me-2"></i>{{ get_label('click_outside_close', 'Click Outside to Close') }}
                                                </li>
                                                <li><i
                                                        class="bx bx-check text-success me-2"></i>{{ get_label('escape_key_close', 'Escape Key to Close') }}
                                                </li>
                                                <li><i
                                                        class="bx bx-check text-success me-2"></i>{{ get_label('customizable_icon', 'Customizable Icon') }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.appConfig = {
            AppLabels: {
                copied: "{{ get_label('copied', 'Copied') }}",
                all_code_copied: "{{ get_label('copy_all_code', 'Copy All Code') }}",
                hide_preview: "{{ get_label('hide_preview', 'Hide Preview') }}",
                preview_widget: "{{ get_label('preview_widget', 'Preview Widget') }}",
            }
        };
    </script>

    <script src="{{ asset('assets/js/pages/lead-form.js') }}"></script>


@endsection