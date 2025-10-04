$(document).ready(function () {

    let pluginSlugToUninstall = null;

    function ajaxPluginAction(action, pluginSlug, successMessage) {
        $.ajax({
            url: `${baseUrl}/superadmin/settings/plugins/${action}/${pluginSlug}`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.error) {
                    toastr.error(response.message || label_something_went_wrong);
                } else {
                    toastr.success(response.message || successMessage);
                    setTimeout(() => location.reload(), 1500);
                }
            },
            error: function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    toastr.error(xhr.responseJSON.message);
                } else {
                    toastr.error(label_something_went_wrong);
                }
            }
        });
    }

    // Enable plugin
    $(document).on('click', '.enable-plugin', function () {
        const pluginSlug = $(this).data('plugin');
        ajaxPluginAction('enable', pluginSlug, label_plugin_enabled);
    });

    // Disable plugin
    $(document).on('click', '.disable-plugin', function () {
        const pluginSlug = $(this).data('plugin');
        ajaxPluginAction('disable', pluginSlug, label_plugin_disabled);
    });

    // Show Bootstrap modal for uninstall confirmation
    $(document).on('click', '.uninstall-plugin', function () {
        pluginSlugToUninstall = $(this).data('plugin');
        const modal = new bootstrap.Modal(document.getElementById('uninstallPluginModal'));
        modal.show();
    });

    // Confirm uninstall on modal confirm button click
    $(document).on('click', '#confirm-uninstall-plugin', function () {
        if (pluginSlugToUninstall) {
            ajaxPluginAction('uninstall', pluginSlugToUninstall, label_plugin_uninstalled);
            pluginSlugToUninstall = null;
            const modalElement = document.getElementById('uninstallPluginModal');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            modalInstance.hide();
        }
    });
});
