// Open Edit Template Modal with data
$(document).on("click", ".edit-template-btn", function () {
    const template = $(this).data("template");
    const actionUrl = `/master-panel/email-templates/update/${template.id}`;

    $("#editEmailTemplateForm").attr("action", actionUrl);
    $("#editTemplateId").val(template.id);
    $("#editTemplateName").val(template.name);
    $("#editSubject").val(template.subject);
    $("#editBody").val(template.body);
    $("#editEmailTemplateModal").modal("show");
    // Wait for modal to be fully shown before initializing TinyMCE
    $("#editEmailTemplateModal").on("shown.bs.modal", function () {});
});

// js for email template list
function queryParams(params) {
    return {
        limit: params.limit,
        offset: params.offset,
        search: params.search,
        sort: params.sort,
        order: params.order,
    };
}

// placeholders modal

let placeholdersModal;

$(document).on("click", ".view-placeholders-btn", function () {
    const $modalElement = $("#placeholdersModal");

    const placeholdersData = $(this).data("placeholders") || [];
    const $list = $("#placeholdersList");
    $list.empty();

    if (placeholdersData.length > 0) {
        placeholdersData.forEach((ph) => {
            $list.append(`<li class="list-group-item">${ph}</li>`);
        });
    } else {
        $list.html('<li class="list-group-item">No placeholders found.</li>');
    }

    if (placeholdersModal) {
        placeholdersModal.dispose();
    }

    placeholdersModal = new bootstrap.Modal($modalElement[0]);
    placeholdersModal.show();
});

$modalElement.on("hidden.bs.modal", function () {
    $("body").removeClass("modal-open");
    $(".modal-backdrop").remove();
});
