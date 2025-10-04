$(document).ready(function () {
    if ($('#phone-input-edit').length) {

        const phoneInput = initPhoneInput('phone-input-edit', $('#country_code').val(), $('#country_iso_code').val());
        $('form').on('submit', function (e) {
            $('#country_code').val(phoneInput.getCountryCode());
            $('#phone_number').val(phoneInput.getNumber().replace(/\s+/g, ''));
            $('#country_iso_code').val(phoneInput.getISOCode());

        });
    }
});
