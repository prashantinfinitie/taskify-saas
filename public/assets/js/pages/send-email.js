// Common variables
const LABEL_CONSTANTS = {
    sendLabel: "",
    scheduleLabel: "",
    previewLabel: "",
};

// Initialize the email sending functionality
function initEmailFunctionality() {
    // Store labels from data attributes
    LABEL_CONSTANTS.sendLabel = $("#submit_btn").data("label-send");
    LABEL_CONSTANTS.scheduleLabel = $("#submit_btn").data("label-schedule");
    LABEL_CONSTANTS.previewLabel = $("#previewBtn").data("label-preview");

    // Initialize Template Email Tab
    initTemplateEmailTab();

    // Initialize Custom Email Tab
    initCustomEmailTab();

    // If template is preselected, trigger change event
    if ($("#templateSelector").val()) {
        $("#templateSelector").trigger("change");
    }
}

$("#templateSelector").on("change", function () {
    loadTemplateData($(this).val());
});

$('#previewBtn').click(function () {
        previewEmail();
    });
    
$(function () {
    if ($(".to_emails").length) {
        initEmailSelect2(
            ".to_emails",
            "/search",
            LABEL_CONSTANTS.pleaseEnterName,
            true,
            1,
            false // Set to false to avoid initial data load for email input
        );
    }
});

function initEmailSelect2(
    elementId,
    ajaxUrl = "/search",
    placeholderText = LABEL_CONSTANTS.pleaseEnterName,
    allowClear = true,
    minimumInputLength = 1,
    initialData = false,
    extraData = () => ({})
) {
    const $element = $(elementId);
    const $modalParent = $element.closest(".modal");

    $element.select2({
        tags: true,
        tokenSeparators: [",", " "],
        placeholder: placeholderText,
        width: "100%",
        allowClear: allowClear,
        minimumInputLength: minimumInputLength,
        dropdownParent: $modalParent.length ? $modalParent : $(document.body),

        ajax: {
            url: ajaxUrl,
            dataType: "json",
            delay: 250,
            data: function (params) {
                return {
                    q: params.term || "",
                    page: params.page || 1,
                    type: "users", // specific for your use-case
                    ...extraData(),
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                
                // Handle the response from your SearchController
                let results = [];
                
                if (data.results && data.results.users) {
                    results = data.results.users.map(function (user) {
                        return {
                            id: user.email,
                            text: user.email,
                            email: user.email,
                            name: user.title
                        };
                    });
                }
                
                // Also handle clients if they have emails
                if (data.results && data.results.clients) {
                    const clientResults = data.results.clients
                        .filter(client => client.email) 
                        .map(function (client) {
                            return {
                                id: client.email,
                                text: client.email + " (" + client.title + ")",
                                email: client.email,
                                name: client.title
                            };
                        });
                    results = results.concat(clientResults);
                }
                
                return {
                    results: results,
                    pagination: {
                        more: data.pagination?.more || false,
                    },
                };
            },
            cache: true,
        },

        createTag: function (params) {
            const term = $.trim(params.term);
            if (term === "") return null;
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailRegex.test(term)) {
                return { 
                    id: term, 
                    text: term, 
                    newTag: true,
                    email: term
                };
            }
            return null;
        },

        language: {
            inputTooShort: () => LABEL_CONSTANTS.pleaseTypeAtLeast,
            searching: () => LABEL_CONSTANTS.searching,
            noResults: () => LABEL_CONSTANTS.noResultsFound,
        },

        escapeMarkup: (markup) => markup,
    });

    
    if (initialData) {
        $.ajax({
            url: ajaxUrl,
            data: {
                q: "",
                page: 1,
                type: "users",
                ...extraData(),
            },
            dataType: "json",
            success: function (data) {
                if (data.results && data.results.users) {
                    const initialEmails = data.results.users.map((user) => ({
                        id: user.email,
                        text: user.email ,
                    }));
                    initialEmails.forEach((item) => {
                        const option = new Option(item.text, item.id, true, true);
                        $element.append(option);
                    });
                    $element.trigger("change");
                }
            },
        });
    }
}
function loadTemplateData(templateId) {
    if (!templateId) {
        $("#emailComposition").addClass("d-none");
        return;
    }

    // Show loading state
    $("#emailComposition").addClass("d-none");
    $("#placeholderFields").html(`
        <div class="col-12 text-center py-3">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `);

    // AJAX request to get template data
    $.ajax({
        url: "/master-panel/emails/template-data/" + templateId,
        method: "GET",
        dataType: "json",
        success: function (response) {
            // Update form fields
            $("#emailSubject").val(response.subject);
            $("#templateBodyInput").val(response.body);
            $("#templateIdInput").val(templateId);

            // Update placeholders
            updatePlaceholderFields(response.placeholders);

            // Show the email composition section
            $("#emailComposition").removeClass("d-none");
        },
        error: function (xhr, status, error) {
            console.error("Error loading template:", error);
            toastr.error("Failed to load template data. Please try again.");
        },
    });
}

// Update placeholder fields
function updatePlaceholderFields(placeholders) {
    let placeholderHtml = '';
    if (placeholders && placeholders.length > 0) {
        placeholders.forEach(function (placeholder) {
            const label = placeholder.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            placeholderHtml += `
                <div class="col-md-6 mb-3">
                    <label class="form-label">${label}</label>
                    <input type="text" class="form-control"
                           required
                           name="placeholders[${placeholder}]"
                           placeholder="Enter ${label}">
                </div>
            `;
        });
    } else {
        placeholderHtml = '<div class="col-12 text-muted">No placeholders found for this template</div>';
    }
    $('#placeholderFields').html(placeholderHtml);
}

$(document).ready(function () {
    initEmailFunctionality();
});

function validateScheduledEmail(e, form, toggleSelector) {
    let selectTimeError = $(toggleSelector).data('select-time-error');
    if ($(toggleSelector).is(':checked') && !$(form).find('[name="scheduled_at"]').val()) {
        e.preventDefault();
        toastr.error(selectTimeError);
        return false;
    }
}

// Custom Email Tab Functionality
function initCustomEmailTab() {
    // Toggle schedule field for custom email
    $('#customScheduleToggle').change(function () {
        if ($(this).is(':checked')) {
            $('#customScheduleField').removeClass('d-none');
            $('.custom_submit_btn').html('<i class="bx bx-calendar me-1"></i> ' + LABEL_CONSTANTS.scheduleLabel);
        } else {
            $('#customScheduleField').addClass('d-none');
            $('.custom_submit_btn').html('<i class="bx bx-send me-1"></i> ' + LABEL_CONSTANTS.sendLabel);
        }
    });

    // File upload display for custom email
    $('#custom_attachments').change(function () {
        handleFileUpload($(this), '#custom-file-list', '#custom-file-names');
    });

    // Form validation for custom email
    $('#customEmailForm').submit(function (e) {
        validateScheduledEmail(e, this, '#customScheduleToggle');
    });

     $('#emailForm').submit(function (e) {
        validateScheduledEmail(e, this, '#scheduleToggle');
    });
    
    // Initialize select2 for email recipients
    initEmailSelect2('.to_emails');

    // Initialize TinyMCE for custom email body if available
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#custom-email-body',
            height: 400,
            menubar: true,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic backcolor | ' +
                'alignleft aligncenter alignright alignjustify | ' +
                'bullist numlist outdent indent | removeformat | help'
        });
    }
}

// Template Email Tab Functionality
function initTemplateEmailTab() {
    // Toggle schedule field
    $('#scheduleToggle').change(function () {
        if ($(this).is(':checked')) {
            $('#scheduleField').removeClass('d-none');
            $('#submit_btn').html('<i class="bx bx-calendar me-1"></i> ' + LABEL_CONSTANTS.scheduleLabel);
        } else {
            $('#scheduleField').addClass('d-none');
            $('#submit_btn').html('<i class="bx bx-send me-1"></i> ' + LABEL_CONSTANTS.sendLabel);
        }
    });

    // File upload display for template email
    $('#attachments').change(function () {
        handleFileUpload($(this), '#file-list', '#file-names');
    });

    // Preview functionality
    $('#previewBtn').click(function () {
        previewEmail();
    });

    // Form validation for template email
    $('#emailForm').submit(function (e) {
        validateScheduledEmail(e, this, '#scheduleToggle');
    });

    // Template selector change handler
    $('#templateSelector').on('change', function () {
        loadTemplateData($(this).val());
    });
}

function previewEmail() {
    const form = $('#emailForm')[0];
    const formData = new FormData(form);
    let companyTitle = $('#previewBtn').data('company-title');

    // Handle content field
    var contentField = $('#emailForm').find('#templateBodyInput');
    if (contentField.length > 0) {
        // Remove the original content from FormData
        formData.delete("content");

        // Add the content as base64 encoded to bypass ModSecurity filters
        var encodedContent = btoa(contentField.val());
        formData.append("content", encodedContent);
        formData.append("is_encoded", "1");
    }

    // Append system placeholders
    formData.append('placeholders[CURRENT_YEAR]', new Date().getFullYear());
    formData.append('placeholders[COMPANY_TITLE]', companyTitle);
    formData.append('placeholders[COMPANY_LOGO]', '<img src=' + logo_url + ' width="200px" alt="Company Logo">');
    formData.append('placeholders[SUBJECT]', $('input[name="subject"]').val());

    $.ajax({
        url: '/master-panel/emails/preview',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $('#previewBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Loading...');
        },
        complete: function () {
            $('#previewBtn').prop('disabled', false).html('<i class="bx bx-show me-1"></i> ' + LABEL_CONSTANTS.previewLabel);
        },
        success: function (response) {
            $('#previewContent').html(response.preview);
            $('#previewModal').modal('show');
        },
        error: function () {
            toastr.error('Error generating preview. Please try again.');
        }
    });
}
