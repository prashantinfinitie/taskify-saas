// Bootstrap Table query params
function queryParamsLeadForms(params) {
    return {
        search: params.search,
        limit: params.limit,
        offset: params.offset,
        order: params.order,
        sort: params.sort
    };
}

// Copy embed code function for the dedicated embed page
function copyEmbedCode() {
    const textarea = document.getElementById("embedCode");
    if (textarea) {
        textarea.select();
        navigator.clipboard.writeText(textarea.value);

        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = `<i class="bx bx-check fs-5"></i><span class="d-none d-md-inline">${window.appConfig?.labels?.copied || 'Copied'}</span>`;
        btn.classList.replace('btn-primary', 'btn-success');

        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.replace('btn-success', 'btn-primary');
        }, 2000);
    }
}

// Initialize any other lead form specific functionality
document.addEventListener('DOMContentLoaded', function () {
    console.log('Lead form page initialized');
});

// Handle embed options button click
$(document).on('click', '.embed-options-btn', function () {
    console.log("here");
    const formId = $(this).data('form-id');
    const embedCode = $(this).data('embed-code');
    const embedUrl = $(this).data('embed-url');

    if ($(this).data('action') === 'preview') {
        currentEmbedCode = embedCode;
        document.getElementById('embedFormCode').value = embedCode;
        document.getElementById('embedFormPreview').innerHTML = embedCode;
        document.getElementById('embedFormTitle').textContent = 'Form Preview';
        toggleEmbedForm();
        showEmbedTab('preview');
    } else {
        window.open(embedUrl, '_blank');
    }
});

// For create and edit - Initialize fieldIndex properly
let fieldIndex = window.appConfig && window.appConfig.fieldIndex ? window.appConfig.fieldIndex : 0;

function addField() {
    const container = document.getElementById('fieldsContainer');
    const fieldHtml = `
        <div class="field-row border rounded p-3 mb-3 bg-white" id="field-${fieldIndex}" data-id="${fieldIndex}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">${window.appConfig.labels.field_label} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="fields[${fieldIndex}][label]" required
                           placeholder="${window.appConfig.labels.enter_field_label}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">${window.appConfig.labels.field_type} <span class="text-danger">*</span></label>
                    <select class="form-select" name="fields[${fieldIndex}][type]" onchange="toggleOptions(${fieldIndex})" required>
                        <option value="">${window.appConfig.labels.select_type}</option>
                        ${window.appConfig.fieldTypes.map(type => `<option value="${type.value}">${type.label}</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">${window.appConfig.labels.map_to}</label>
                    <select class="form-select" name="fields[${fieldIndex}][name]" onchange="toggleMapping(${fieldIndex})">
                        <option value="">${window.appConfig.labels.custom_field}</option>
                        ${window.appConfig.mappableFields.map(field => `<option value="${field.value}">${field.label}</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">${window.appConfig.labels.required}</label>
                    <select class="form-select" name="fields[${fieldIndex}][is_required]">
                        <option value="0">${window.appConfig.labels.no}</option>
                        <option value="1">${window.appConfig.labels.yes}</option>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <label class="form-label"> </label>
                    <div>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeField(${fieldIndex})">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Options Container -->
            <div class="options-container mt-3" id="options_container_${fieldIndex}" style="display:none;">
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label">${window.appConfig.labels.options}</label>
                        <div class="options-list mb-2" id="options_list_${fieldIndex}"></div>
                        <div class="input-group">
                            <input type="text" class="form-control" id="option_input_${fieldIndex}"
                                   placeholder="${window.appConfig.labels.add_option}">
                            <button class="btn btn-outline-primary" type="button" onclick="addOption(${fieldIndex})">
                                ${window.appConfig.labels.add}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="fields[${fieldIndex}][is_mapped]" value="0" id="is_mapped_${fieldIndex}">
            <div class="options-hidden" id="options_hidden_${fieldIndex}"></div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', fieldHtml);
    fieldIndex++;
}

function addOption(index) {
    const input = document.getElementById(`option_input_${index}`);
    const optionsList = document.getElementById(`options_list_${index}`);
    const optionsHidden = document.getElementById(`options_hidden_${index}`);
    const optionValue = input.value.trim();

    if (optionValue) {
        const optionHtml = `
            <span class="badge bg-light text-dark me-2 mb-2" data-value="${optionValue}">
                ${optionValue}
                <button type="button" class="btn-close btn-sm ms-1" onclick="removeOption(${index}, '${optionValue}')" aria-label="Remove option"></button>
            </span>
        `;
        optionsList.insertAdjacentHTML('beforeend', optionHtml);

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = `fields[${index}][options][]`;
        hiddenInput.value = optionValue;
        optionsHidden.appendChild(hiddenInput);

        input.value = '';
    }
}

function removeOption(index, optionValue) {
    const optionsList = document.getElementById(`options_list_${index}`);
    const optionsHidden = document.getElementById(`options_hidden_${index}`);
    const optionItem = optionsList.querySelector(`[data-value="${optionValue}"]`);
    if (optionItem) {
        optionItem.remove();
    }
    const hiddenInput = optionsHidden.querySelector(`input[value="${optionValue}"]`);
    if (hiddenInput) {
        hiddenInput.remove();
    }
}
// IMPROVED: Better form validation that accounts for all fields
function validateFields() {
    const form = document.getElementById(window.appConfig.formId);
    if (!form) {
        console.error('Form not found');
        return false;
    }

    // Get all field rows from both containers
    const allFieldRows = [
        ...form.querySelectorAll('#mandatoryFields .field-row'),
        ...form.querySelectorAll('#fieldsContainer .field-row')
    ];

    let isValid = true;
    let errorMessages = [];

    allFieldRows.forEach((fieldRow, rowIndex) => {
        const fieldId = fieldRow.dataset.id;

        // Use more robust selectors and add null checks
        const labelInput = fieldRow.querySelector(`[name*="[label]"]`);
        const typeSelect = fieldRow.querySelector(`[name*="[type]"]`);
        const nameSelect = fieldRow.querySelector(`[name*="[name]"]`);
        const isMappedInput = fieldRow.querySelector(`[name*="[is_mapped]"]`);

        // Skip validation for mandatory fields (they're always valid)
        if (fieldRow.closest('#mandatoryFields')) {
            return;
        }

        // Null checks before accessing values
        const hasLabel = labelInput && labelInput.value && labelInput.value.trim();
        const hasType = typeSelect && typeSelect.value;
        const hasName = nameSelect && nameSelect.value;
        const isMapped = isMappedInput && isMappedInput.value === '1';

        // If field has any content, validate required fields
        if (hasLabel || hasType || hasName) {
            if (!hasLabel) {
                errorMessages.push(`Field ${rowIndex + 1}: Label is required`);
                isValid = false;
            }
            if (!hasType) {
                errorMessages.push(`Field ${rowIndex + 1}: Type is required`);
                isValid = false;
            }

            // Validate mapped fields
            if (isMapped && !hasName) {
                errorMessages.push(`Field ${rowIndex + 1}: Mapped fields must have a mapping selected`);
                isValid = false;
            }

            // Validate options for select/radio/checkbox fields
            if (typeSelect && ['select', 'radio', 'checkbox'].includes(typeSelect.value)) {
                const optionsInputs = fieldRow.querySelectorAll(`[name*="[options]"]`);
                if (optionsInputs.length === 0) {
                    errorMessages.push(`Field ${rowIndex + 1}: At least one option is required for ${typeSelect.value} fields`);
                    isValid = false;
                }
            }
        }
    });

    if (!isValid) {
        toastr.error(errorMessages.join('<br>'));
    }

    return isValid;
}

// DEBUG: Add this function to check form data before submission
function debugFormData() {
    const form = document.getElementById(window.appConfig.formId);
    const formData = new FormData(form);

    console.log('Form data being submitted:');
    for (let [key, value] of formData.entries()) {
        if (key.includes('fields[')) {
            console.log(key, ':', value);
        }
    }
}

// Enhanced form submission with debugging
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById(window.appConfig?.formId);
    if (form) {
        form.addEventListener('submit', function (e) {
            // Debug form data
            console.log('Form submission triggered');
            debugFormData();

            if (!validateFields()) {
                e.preventDefault();
                console.log('Form validation failed');
            }
        });
    }
});

function toggleOptions(index) {
    const typeSelect = document.querySelector(`select[name="fields[${index}][type]"]`);
    const optionsContainer = document.getElementById(`options_container_${index}`);
    const optionsHidden = document.getElementById(`options_hidden_${index}`);
    const optionsList = document.getElementById(`options_list_${index}`);

    if (typeSelect && ['select', 'radio', 'checkbox'].includes(typeSelect.value)) {
        if (optionsContainer) optionsContainer.style.display = 'block';
    } else {
        if (optionsContainer) optionsContainer.style.display = 'none';
        // Clear options when hiding
        if (optionsHidden) optionsHidden.innerHTML = '';
        if (optionsList) optionsList.innerHTML = '';
    }
}

function toggleMapping(index) {
    const nameSelect = document.querySelector(`select[name="fields[${index}][name]"]`);
    const isMappedInput = document.getElementById(`is_mapped_${index}`);
    if (isMappedInput && nameSelect) {
        isMappedInput.value = nameSelect.value ? '1' : '0';
    }
}

function removeField(index) {
    const fieldElement = document.getElementById(`field-${index}`);
    if (fieldElement) {
        fieldElement.remove();
        reindexFields();
    }
}

// FIXED: More robust field reindexing function that includes mandatory fields
function reindexFields() {
    // Get all field rows from both mandatory and dynamic containers
    const mandatoryFields = document.querySelectorAll('#mandatoryFields .field-row');
    const dynamicFields = document.querySelectorAll('#fieldsContainer .field-row');

    let currentIndex = 0;

    // First, reindex mandatory fields
    mandatoryFields.forEach((fieldRow) => {
        const oldId = fieldRow.dataset.id;

        // Update all form elements with new index
        const formElements = fieldRow.querySelectorAll('input, select, textarea');
        formElements.forEach(element => {
            if (element.name && element.name.includes('fields[')) {
                element.name = element.name.replace(/fields\[\d+\]/, `fields[${currentIndex}]`);
            }
            if (element.id && element.id.includes('_')) {
                element.id = element.id.replace(new RegExp(`_${oldId}$`), `_${currentIndex}`);
            }
        });

        // Update field row attributes
        fieldRow.dataset.id = currentIndex;
        currentIndex++;
    });

    // Then, reindex dynamic fields
    dynamicFields.forEach((fieldRow) => {
        const oldId = fieldRow.dataset.id;

        // Update all form elements with new index
        const formElements = fieldRow.querySelectorAll('input, select, textarea');
        formElements.forEach(element => {
            if (element.name && element.name.includes('fields[')) {
                element.name = element.name.replace(/fields\[\d+\]/, `fields[${currentIndex}]`);
            }
            if (element.id && element.id.includes('_')) {
                element.id = element.id.replace(new RegExp(`_${oldId}$`), `_${currentIndex}`);
            }
        });

        // Update onclick attributes for buttons
        const buttons = fieldRow.querySelectorAll('button[onclick]');
        buttons.forEach(button => {
            const onclick = button.getAttribute('onclick');
            if (onclick) {
                button.setAttribute('onclick', onclick.replace(/\(\d+\)/g, `(${currentIndex})`));
            }
        });

        // Update onchange attributes for selects
        const selects = fieldRow.querySelectorAll('select[onchange]');
        selects.forEach(select => {
            const onchange = select.getAttribute('onchange');
            if (onchange) {
                select.setAttribute('onchange', onchange.replace(/\(\d+\)/g, `(${currentIndex})`));
            }
        });

        // Update IDs that use the field index
        const elementsWithId = fieldRow.querySelectorAll('[id*="_"]');
        elementsWithId.forEach(element => {
            if (element.id.includes(`_${oldId}`)) {
                element.id = element.id.replace(`_${oldId}`, `_${currentIndex}`);
            }
        });

        // Update field row attributes
        fieldRow.id = `field-${currentIndex}`;
        fieldRow.dataset.id = currentIndex;
        currentIndex++;
    });

    // Update global fieldIndex to continue from the last used index
    fieldIndex = currentIndex;
}


// Initialize sortable if container exists
if (document.getElementById('fieldsContainer')) {
    new Sortable(document.getElementById('fieldsContainer'), {
        animation: 150,
        handle: '.field-row',
        onEnd: function (evt) {
            reindexFields();
            // Show toast notification
            if (typeof toastr !== 'undefined') {
                toastr.success("Field order updated successfully!");
            }
        }
    });
}

// Client-side form validation
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById(window.appConfig?.formId);
    if (form) {
        form.addEventListener('submit', function (e) {
            if (!validateFields()) {
                e.preventDefault();
            }
        });
    }
});

// Initialize Select2
$(document).ready(function () {
    $('.select2').select2({
        width: '100%'
    });
});

function copyEmbedCode() {
    let textarea = document.getElementById("embedCode");
    textarea.select();
    textarea.setSelectionRange(0, 99999); // for mobile devices

    document.execCommand("copy");

    // Show success message
    if (typeof toastr !== 'undefined') {
        toastr.success("Embed code copied to clipboard!");
    }
}




// embed










//
// Copy to clipboard function
function copyToClipboard(elementId, button) {
    const textarea = document.getElementById(elementId);

    navigator.clipboard.writeText(textarea.value).then(function () {
        // Store original button content
        const originalContent = button.innerHTML;

        // Change button to show success
        button.innerHTML = '<i class="bx bx-check me-1"></i>' + window.appConfig.AppLabels.copied;
        button.classList.remove('btn-primary', 'btn-outline-danger', 'btn-outline-info',
            'btn-outline-warning');
        button.classList.add('btn-success');

        // Reset button after 2 seconds
        setTimeout(() => {
            button.innerHTML = originalContent;
            button.classList.remove('btn-success');

            // Add back the appropriate class based on the button type
            if (elementId.includes('Html')) {
                button.classList.add('btn-outline-danger');
            } else if (elementId.includes('Css')) {
                button.classList.add('btn-outline-info');
            } else if (elementId.includes('Js')) {
                button.classList.add('btn-outline-warning');
            } else {
                button.classList.add('btn-primary');
            }
        }, 2000);
    }).catch(function (err) {
        console.error('Failed to copy text: ', err);

        // Fallback for older browsers
        textarea.select();
        document.execCommand('copy');

        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="bx bx-check me-1"></i>' + window.appConfig.AppLabels.copied;
        button.classList.add('btn-success');

        setTimeout(() => {
            button.innerHTML = originalContent;
            button.classList.remove('btn-success');
            button.classList.add('btn-primary');
        }, 2000);
    });
}

// Copy all floating code
function copyAllFloatingCode(event) {
    const htmlCode = document.getElementById('floatingHtmlCode').value;
    const cssCode = document.getElementById('floatingCssCode').value;
    const jsCode = document.getElementById('floatingJsCode').value;

    const allCode = `${htmlCode}\n\n${cssCode}\n\n${jsCode}`;

    navigator.clipboard.writeText(allCode).then(function () {
        const button = event.target.closest('button');
        const originalContent = button.innerHTML;

        button.innerHTML =
            '<i class="bx bx-check me-2"></i>' + window.appConfig.AppLabels.all_code_copied;
        button.classList.remove('btn-success');
        button.classList.add('btn-success');

        setTimeout(() => {
            button.innerHTML = originalContent;
        }, 2000);
    }).catch(function (err) {
        console.error('Failed to copy all code: ', err);
    });
}

// Preview floating widget - Modified to show in preview box
function previewFloatingWidget(event) {
    const previewWidget = document.getElementById('previewFloatingWidget');
    const button = event.currentTarget;

    if (previewWidget.style.display === 'none') {
        previewWidget.style.display = 'block';
        button.innerHTML = '<i class="bx bx-hide me-2"></i>' + window.appConfig.AppLabels.hide_preview;
        button.classList.remove('btn-primary');
        button.classList.add('btn-secondary');
    } else {
        previewWidget.style.display = 'none';

        const container = document.getElementById('previewLeadFormContainer');
        if (container) {
            container.classList.remove('active');
        }

        button.innerHTML = '<i class="bx bx-show me-2"></i>' + window.appConfig.AppLabels.preview_widget;
        button.classList.remove('btn-secondary');
        button.classList.add('btn-primary');
    }
}

// Toggle preview lead form
function togglePreviewLeadForm() {
    const container = document.getElementById('previewLeadFormContainer');

    if (container.classList.contains('active')) {
        container.classList.remove('active');
    } else {
        container.classList.add('active');
    }
}

// Initialize tooltips if Bootstrap is available
document.addEventListener('DOMContentLoaded', function () {
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});

// Close preview form when clicking outside the preview box
document.addEventListener('click', function (event) {
    const container = document.getElementById('previewLeadFormContainer');
    const icon = document.querySelector('.preview-embed-icon');
    const previewBox = document.querySelector('.preview-box');

    if (container && container.classList.contains('active')) {
        // Only close if clicking outside the preview box entirely
        if (!previewBox.contains(event.target)) {
            container.classList.remove('active');
        }
        // Close if clicking inside preview box but not on container or icon
        else if (previewBox.contains(event.target) && !container.contains(event.target) && !icon.contains(event.target)) {
            container.classList.remove('active');
        }
    }
});

// Close preview form with Escape key
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        const container = document.getElementById('previewLeadFormContainer');
        if (container && container.classList.contains('active')) {
            container.classList.remove('active');
        }
    }
});
