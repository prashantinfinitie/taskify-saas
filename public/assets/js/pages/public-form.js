document.getElementById('leadForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);
    const toastElement = document.getElementById('formToast');
    const toastMessage = document.getElementById('toastMessage');
    const submitButton = form.querySelector('button[type="submit"]');
    const toast = new bootstrap.Toast(toastElement);

    // Reset previous errors
    document.querySelectorAll('.text-danger').forEach(el => {
        el.textContent = '';
        el.classList.remove('show');
    });
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    // Validate required checkboxes and radios
    let isValid = true;
    const requiredCheckboxes = form.querySelectorAll('[data-required="true"][type="checkbox"]');
    const requiredRadios = form.querySelectorAll('[data-required="true"][type="radio"]');

    requiredCheckboxes.forEach(checkbox => {
        const name = checkbox.name;
        const checked = form.querySelectorAll(`input[name="${name}"]:checked`).length > 0;
        if (!checked) {
            isValid = false;
            const errorDiv = document.getElementById(`error_${name.replace(/\[\]/, '')}`);
            if (errorDiv) {
                errorDiv.textContent = 'Please select at least one option.';
                errorDiv.classList.add('show');
            }
            checkbox.closest('.mb-4').classList.add('is-invalid');
        }
    });

    requiredRadios.forEach(radio => {
        const name = radio.name;
        const checked = form.querySelector(`input[name="${name}"]:checked`);
        if (!checked) {
            isValid = false;
            const errorDiv = document.getElementById(`error_${name}`);
            if (errorDiv) {
                errorDiv.textContent = 'Please select an option.';
                errorDiv.classList.add('show');
            }
            radio.closest('.mb-4').classList.add('is-invalid');
        }
    });

    if (!isValid) {
        toastElement.classList.remove('bg-success');
        toastElement.classList.add('bg-danger');
        toastMessage.textContent = 'Please correct the errors below.';
        toast.show();
        return;
    }

    // Disable submit button
    submitButton.disabled = true;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';

    try {
        const response = await axios.post(form.action, formData, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        toastElement.classList.remove('bg-danger');
        toastElement.classList.add('bg-success');
        toastMessage.textContent = response.data.message || 'Form submitted successfully!';
        toast.show();

        if (response.data.redirect_url) {
            setTimeout(() => window.location.href = response.data.redirect_url, 1000);
        } else {
            form.reset();
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        }
    } catch (error) {
        toastElement.classList.remove('bg-success');
        toastElement.classList.add('bg-danger');
        if (error.response && error.response.status === 422) {
            const errors = error.response.data.errors;
            for (const [field, messages] of Object.entries(errors)) {
                let errorDivId = `error_${field.replace(/\[\]/, '')}`;
                const errorDiv = document.getElementById(errorDivId);
                const inputs = form.querySelectorAll(`[name="${field}"], [name="${field}[]"]`);
                if (inputs.length) {
                    inputs.forEach(input => input.classList.add('is-invalid'));
                }
                if (errorDiv) {
                    errorDiv.textContent = messages[0];
                    errorDiv.classList.add('show');
                } else {
                    toastMessage.textContent = messages[0];
                }
            }
            toastMessage.textContent = 'Please correct the errors below.';
        } else {
            toastMessage.textContent = error.response?.data?.message || 'An error occurred. Please try again.';
        }
        toast.show();
    } finally {
        submitButton.disabled = false;
        submitButton.textContent = 'Submit';
    }
});

// Initialize Select2
$(document).ready(function () {
    $('.form-select').select2({
        width: '100%'
    });
});


document.addEventListener('DOMContentLoaded', function () {
    const isInIframe = window.self !== window.top;
    if (!isInIframe) {
        document.body.classList.add('standalone-form');
    }
});
