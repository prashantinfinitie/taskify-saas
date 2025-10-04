"use strict";

$("#candidates-kanban-filter").click(function () {
    // Get the selected values from status select and other filters
    var statuses = $("#selected_sources").val(); // Array of selected statuses
    var sort = $("#sort").val();

    // Form the URL with the selected filters
    var url = "/master-panel/candidate/kanban";
    var params = [];

    if (statuses && statuses.length > 0) {
        params.push("sources[]=" + statuses.join("&sources[]="));
    }

    if (sort) {
        params.push("sort=" + sort);
    }
    if ($("#candidate_kanban_start_date").val() != "") {
        params.push("start_date=" + $("#candidate_kanban_start_date").val());
    }
    if ($("#candidate_kanban_end_date").val() != "") {
        params.push("end_date=" + $("#candidate_kanban_end_date").val());
    }

    if (params.length > 0) {
        url += "?" + params.join("&");
    }

    // Redirect to the URL
    window.location.href = url;
});

$("#candidate_kanban_date_between").on(
    "apply.daterangepicker",
    function (ev, picker) {
        var startDate = picker.startDate.format("YYYY-MM-DD");
        var endDate = picker.endDate.format("YYYY-MM-DD");
        $("#candidate_kanban_end_date").val(endDate);
        $("#candidate_kanban_start_date").val(startDate);
    }
);

$("#candidate_kanban_date_between").on(
    "cancel.daterangepicker",
    function (ev, picker) {
        $("#candidate_kanban_end_date").val("");
        $("#candidate_kanban_start_date").val("");
        $("#candidate_kanban_date_between").val(""); // CORRECTED: Changed from #lead_kanban_date_range
    }
);

document.addEventListener("DOMContentLoaded", function () {
    const columns = Array.from(
        document.querySelectorAll(".kanban-column-body")
    );
    const drake = dragula(columns, {
        direction: "vertical",
        moves: function (el, container, handle) {
            return !el.classList.contains("create-candidate-btn");
        },
        accepts: function (el, target) {
            return !el.classList.contains("create-candidate-btn");
        },
        invalid: function (el, handle) {
            return el.classList.contains("create-candidate-btn");
        },
    });

    // Add class on drag start
    drake.on("drag", function (el) {
        el.classList.add("dragging");
    });

    // Remove class on drag end
    drake.on("dragend", function (el) {
        el.classList.remove("dragging");
        el.classList.add("dropped");
        document.querySelectorAll(".drop-target").forEach((target) => {
            target.classList.remove("drop-target");
        });
    });

    // Highlight column on drag over
    drake.on("over", function (el, container) {
        container.classList.add("drop-target");
    });

    // Remove highlight on drag out
    drake.on("out", function (el, container) {
        container.classList.remove("drop-target");
    });

    // When dropped
    drake.on("drop", function (el, target, source, sibling) {
        const newStatusId = target.closest(".kanban-column").dataset.statusId;
        const candidateId = el.dataset.cardId;

        $.ajax({
            url: "/master-panel/candidate/" + candidateId + "/update_status",
            type: "POST",
            dataType: "json",
            contentType: "application/json",
            headers: {
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            data: JSON.stringify({
                status_id: newStatusId,
            }),
            success: function (response) {
                if (response.error === false) {
                    toastr.success(response.message);
                    updateColumnCounts();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error("Error:", error);
            },
        });
    });

    function updateColumnCounts() {
        const total = $(".kanban-card").length;
        $(".kanban-column").each(function () {
            const count = $(this).find(".kanban-card").length;
            $(this).find(".column-count").text(`${count}/${total}`);
        });
    }
});
// jQuery Document Ready
$(document).ready(function () {
    // Bootstrap Tooltip Init
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Quick View Modal
    $(document).ready(function () {
        $(".quick-candidate-view").on("click", function () {
            const candidateId = $(this).data("id");
            if (!candidateId) return;

            $.ajax({
                url: `/master-panel/candidate/${candidateId}/quick-view`,
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                    "X-Requested-With": "XMLHttpRequest",
                },
                success: function (data) {
                    if (!data?.candidate) {
                        alert("Candidate data is missing.");
                        return;
                    }

                    // Populate core details
                    const c = data.candidate;
                    $("#candidate-name").text(c.name || "-");
                    $("#candidate-position").text(c.position || "-");
                    $("#candidate-status").text(c.status || "-");
                    $("#candidate-avatar").attr("src", c.avatar || "");

                    // Populate form fields
                    $("#candidate-phone").val(c.phone || "-");
                    $("#candidate-email").val(c.email || "-");
                    $("#candidate-position-input").val(c.position || "-");
                    $("#candidate-source").val(c.source || "-");
                    $("#candidate-status-input").val(c.status || "-");
                    $("#candidate-created-at").val(c.created_at || "-");

                    // Populate attachments table with lightbox
                    populateTable(
                        "#attachments",
                        data.attachments,
                        ["id", "name", "type", "size", "created_at"],
                        (row) => {
                            const fileExtension = row.name
                                .split(".")
                                .pop()
                                .toLowerCase();
                            const imageExtensions = [
                                "jpg",
                                "jpeg",
                                "png",
                                "gif",
                                "bmp",
                                "webp",
                            ];
                            const isImage =
                                imageExtensions.includes(fileExtension);

                            const fileLink = isImage
                                ? `<a href="${row.url}" data-lightbox="candidate-attachments" data-title="${row.name}">
                             <img src="${row.url}" alt="${row.name}" width="50" class="img-thumbnail me-2">
                             ${row.name}
                           </a>`
                                : `<a href="${row.url}" target="_blank" class="text-decoration-none">
                             <i class="bi bi-file-earmark me-2"></i>${row.name}
                           </a>`;

                            return `
                        <td>${row.id}</td>
                        <td>${fileLink}</td>
                        <td>${row.type}</td>
                        <td>${row.size}</td>
                        <td>${row.created_at}</td>
                    `;
                        }
                    );

                    // Populate interviews table
                    populateTable(
                        "#interviews",
                        data.interviews,
                        [
                            "id",
                            "candidate_name",
                            "interviewer",
                            "round",
                            "scheduled_at",
                            "status",
                            "location",
                            "mode",
                            "created_at",
                            "updated_at",
                        ],
                        (row) => `
                    <td>${row.id}</td>
                    <td>${row.candidate_name}</td>
                    <td>${row.interviewer}</td>
                    <td>${row.round}</td>
                    <td>${row.scheduled_at}</td>
                    <td>${row.status}</td>
                    <td>${row.location}</td>
                    <td>${row.mode}</td>
                    <td>${row.created_at}</td>
                    <td>${row.updated_at}</td>
                `
                    );

                    $("#candidateQuickViewModal").modal("show");

                    // Reinitialize lightbox after content is loaded
                    if (typeof lightbox !== "undefined") {
                        lightbox.option({
                            resizeDuration: 200,
                            wrapAround: true,
                            albumLabel: "Attachment %1 of %2",
                            fadeDuration: 300,
                            imageFadeDuration: 300,
                            positionFromTop: 50,
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Ajax error:", error);
                    alert("Failed to load candidate profile.");
                },
            });
        });

        // Generic Table Population Utility
        function populateTable(prefix, items, fields, rowTemplateFn) {
            const $tbody = $(`${prefix}-body`);
            const $table = $(`${prefix}-table`);
            const $empty = $(`${prefix}-empty`);

            $tbody.empty();

            if (items && items.length > 0) {
                $table.show();
                $empty.hide();

                items.forEach((item) => {
                    const $tr = $("<tr>").html(rowTemplateFn(item));
                    $tbody.append($tr);
                });
            } else {
                $table.hide();
                $empty.show();
            }
        }
    });

    // Generic Table Population Utility
    function populateTable(prefix, items, fields, rowTemplateFn) {
        const $tbody = $(`${prefix}-body`);
        const $table = $(`${prefix}-table`);
        const $empty = $(`${prefix}-empty`);

        $tbody.empty();

        if (items.length > 0) {
            $table.show();
            $empty.hide();

            items.forEach((item) => {
                const $tr = $("<tr>").html(rowTemplateFn(item));
                $tbody.append($tr);
            });
        } else {
            $table.hide();
            $empty.show();
        }
    }
});
// Global helper
function get_label(key, defaultValue) {
    return window.labels?.[key] ?? defaultValue;
}
$(document).on('click', '.create-candidate-btn', function () {
    const statusId = $(this).data('status-id');

    // Set the selected status in the modal dropdown
    $('#candidate-status-select').val(statusId).trigger('change');

    // Optional: Reset other form fields
    const form = $('#candidateModal form')[0];
    if (form) form.reset();

    // Re-set the status again in case reset cleared it
    $('#candidate-status-select').val(statusId).trigger('change');
});
