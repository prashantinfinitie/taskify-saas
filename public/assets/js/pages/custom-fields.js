$(document).ready(function () {
    // Initialize Bootstrap table for custom fields if the table exists
    if ($("#custom_fields_table").length) {
        $("#custom_fields_table").bootstrapTable({});
    }

    // Show add field modal when button is clicked
    $("#add_field_btn").on("click", function () {
        $("#add_field_modal").modal("show");
    });

    // Handle field type change to show additional options if needed
    $("#field_type").on("change", function () {
        const fieldType = $(this).val();
        // console.log(fieldType);
        const fieldOptionsContainer = $("#field_options_container");

        fieldOptionsContainer.empty().addClass("d-none");

        if (
            fieldType === "radio" ||
            fieldType === "checkbox" ||
            fieldType === "select"
        ) {
            fieldOptionsContainer.removeClass("d-none").append(`
        <div class="mb-3">
            <label class="form-label">Options</label>
            <div id="options_list">
                <div class="input-group mb-2 option-item">
                    <input type="text" class="form-control" name="options[]" placeholder="Enter option">
<button type="button" class="btn btn-danger remove-option">
    <i class="bx bx-trash"></i>
</button>
                </div>
            </div>
            <button type="button" class="btn btn-primary add-option">Add Option</button>
        </div>
    `);
        }
    });

    // Handle adding new option input
    $(document).on("click", ".add-option", function () {
        const optionsList = $("#options_list");
        optionsList.append(`
        <div class="input-group mb-2 option-item">
            <input type="text" class="form-control" name="options[]" placeholder="Enter option">
           <button type="button" class="btn btn-danger remove-option">
    <i class="bx bx-trash"></i>
</button>
        </div>
    `);
    });

    // Handle removing an option input
    $(document).on("click", ".remove-option", function () {
        const optionItem = $(this).closest(".option-item");
        if ($("#options_list .option-item").length > 1) {
            optionItem.remove();
        } else {
            toastr.error("At least one option is required.");
        }
    });

    // Form validation for custom fields form
    $(".form-submit-event2").on("submit", function (e) {
        e.preventDefault();

        const form = $(this);
        const actionUrl = form.attr("action");
        const isUpdate =
            actionUrl.includes("/custom-fields/") && actionUrl.match(/\d+$/); // Check if URL has an ID
        const method = isUpdate ? "PUT" : "POST"; // Determine method based on action URL

        // Debug - log the action and method
        // console.log("Form action:", actionUrl);
        // console.log("Request method:", method);

        // Collect options[] array and join into a string
        const options = form
            .find('input[name="options[]"]')
            .map(function () {
                return $(this).val();
            })
            .get()
            .filter((val) => val.trim() !== "")
            .join("\n");

        // Create form data
        let formData = form.serializeArray();

        // Remove individual options[] from formData
        formData = formData.filter((item) => item.name !== "options[]");

        // Add options as a single string if present
        if (options) {
            formData.push({ name: "options", value: options });
        }

        // Ensure _method is included for updates
        if (isUpdate) {
            formData.push({ name: "_method", value: "PUT" });
        }

        // Add CSRF token
        formData.push({
            name: "_token",
            value: $('meta[name="csrf-token"]').attr("content"),
        });

        // Debug - log final data
        // console.log("Final form data:", formData);

        $.ajax({
            url: actionUrl,
            method: "POST", // Always POST, as _method spoofs PUT
            data: $.param(formData),
            dataType: "json",
            success: function (response) {
                toastr.success(response.success);
                $("#add_field_modal").modal("hide");
                $("#custom_fields_table").bootstrapTable("refresh");
            },
            error: function (xhr) {
                console.log("Error response:", xhr.responseJSON);
                const errors = xhr.responseJSON.errors;
                $.each(errors, function (key, value) {
                    toastr.error(value[0]);
                });
            },
        });
    });
    // Function to show error messages
    function showErrorMessage(message) {
        if (typeof toastr !== "undefined") {
            toastr.error(message);
        } else {
            alert(message);
        }
    }

    // Handle edit button click for custom fields
    $(document).on("click", ".edit-custom-field", function () {
        var id = $(this).data("id");
        $.ajax({
            url: "/master-panel/settings/custom-fields/" + id + "/edit",
            type: "get",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    const field = response.data;

                    // Populate form fields
                    $("#module").val(field.module);
                    $("#field_label").val(field.field_label);
                    $("#field_type").val(field.field_type).trigger("change");

                    // Handle required radio buttons
                    if (field.required == "1") {
                        $("#required_yes").prop("checked", true);
                    } else {
                        $("#required_no").prop("checked", true);
                    }

                    // Handle visibility checkbox
                    $("#show_in_table").prop(
                        "checked",
                        field.visibility == "1"
                    );

                    // Populate options for Select, Radio, or CheckBox
                    if (
                        field.options &&
                        (field.field_type === "radio" ||
                            field.field_type === "checkbox" ||
                            field.field_type === "select")
                    ) {
                        // Trigger change to render the options container
                        $("#field_type").trigger("change");

                        // Ensure the options list exists
                        const optionsList = $("#options_list");
                        if (optionsList.length) {
                            optionsList.empty(); // Clear default option input

                            // Use options directly (now an array from server)
                            const options = Array.isArray(field.options)
                                ? field.options
                                : field.options
                                      .split("\n")
                                      .filter((opt) => opt.trim() !== "");

                            // Append each option to the options list
                            options.forEach((option) => {
                                optionsList.append(`
                                    <div class="input-group mb-2 option-item">
                                        <input type="text" class="form-control" name="options[]" value="${option}">
                                        <button type="button" class="btn btn-danger remove-option">
    <i class="bx bx-trash"></i>
</button>
                                    </div>
                                `);
                            });
                        } else {
                            console.error("Options list container not found");
                            showErrorMessage(
                                "Failed to load options container"
                            );
                        }
                    }

                    // Change form action to update route
                    $(".form-submit-event2").attr(
                        "action",
                        "/master-panel/settings/custom-fields/" + id
                    );
                    if ($('input[name="_method"]').length === 0) {
                        $(".form-submit-event2").append(
                            '<input type="hidden" name="_method" value="PUT">'
                        );
                    } else {
                        $('input[name="_method"]').val("PUT");
                    }

                    $("#edit_custom_field").text("Edit Field");

                    $("#add_field_modal").modal("show");
                } else {
                    showErrorMessage("Could not fetch field data");
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
                showErrorMessage("An error occurred while fetching field data");
            },
        });
    });

    // Reset form when modal is closed
    $("#add_field_modal").on("hidden.bs.modal", function () {
        const form = $(this).find("form");
        form[0].reset();
        $("#field_options_container").empty().addClass("d-none");
        form.attr("action", "/master-panel/settings/custom-fields");
        $('input[name="_method"]').remove();
        $("#edit_custom_field").text("Add Field");
    });

    // Handle delete button click for custom fields
    // $(document).on("click", ".delete-custom-field", function () {
    //     const fieldId = $(this).data("id");

    //     // Store fieldId in modal's data for use on confirm
    //     $("#delete_field_modal").data("field-id", fieldId);

    //     // Show the delete confirmation modal
    //     $("#delete_field_modal").modal("show");
    // });

    // Handle confirm delete button click
    $(document).on("click", "#confirm_delete_btn", function () {
        const fieldId = $("#delete_field_modal").data("field-id");

        $.ajax({
            url: "/master-panel/settings/custom-fields/" + fieldId,
            type: "DELETE",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.success) {
                    $("#custom_fields_table").bootstrapTable("refresh");
                    toastr.success("Field deleted successfully");
                    $("#delete_field_modal").modal("hide");
                } else {
                    showErrorMessage(
                        response.message || "Could not delete field"
                    );
                }
            },
            error: function () {
                showErrorMessage("An error occurred while deleting the field");
            },
        });
    });
});

function customFieldActionsFormatter(value, row, index) {
    return [
        '<a href="javascript:void(0);" class="edit-custom-field" data-id=' +
            row.id +
            ' title="Edit" class="card-link"><i class="bx bx-edit mx-1"></i></a>' +
            '<button title="Delete" type="button" class="btn delete" data-type="settings/custom-fields" data-id=' +
            row.id +
            ">" +
            '<i class="bx bx-trash text-danger mx-1"></i>' +
            "</button>",
    ];
}

function queryParams(params) {
    return {
        search: params.search,
        sort: params.sort,
        order: params.order,
        limit: params.limit,
        offset: params.offset,
    };
}

// Ensure table is initialized with error handling
$(document).ready(function () {
    if ($("#custom_fields_table").length) {
        $("#custom_fields_table").bootstrapTable({
            onLoadSuccess: function () {
                // console.log("Table data loaded successfully");
            },
            onLoadError: function (status, res) {
                console.error("Table load error:", status, res);
            },
        });
    }
});
