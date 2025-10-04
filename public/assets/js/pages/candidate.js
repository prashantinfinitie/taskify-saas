// document.addEventListener("DOMContentLoaded", function () {
//     // Handle status color change
//     const statusSelect = document.getElementById("candidateStatusId");

//     if (statusSelect) {
//         statusSelect.addEventListener("change", function () {
//             const selectedOption = this.options[this.selectedIndex];
//             const color = selectedOption.getAttribute("data-color");

//             // Remove all existing color classes
//             this.className = this.className.replace(/select-bg-label-\w+/g, "");

//             // Add the new color class if a status is selected
//             if (color && this.value !== "") {
//                 this.classList.add("select-bg-label-" + color);
//             } else {
//                 this.classList.add("select-bg-label-primary");
//             }
//         });

//         // Set initial color when modal is opened and populated
//         const observer = new MutationObserver(function (mutations) {
//             mutations.forEach(function (mutation) {
//                 if (
//                     mutation.type === "attributes" &&
//                     mutation.attributeName === "value"
//                 ) {
//                     const selectedOption =
//                         statusSelect.options[statusSelect.selectedIndex];
//                     const color = selectedOption.getAttribute("data-color");

//                     // Remove all existing color classes
//                     statusSelect.className = statusSelect.className.replace(
//                         /select-bg-label-\w+/g,
//                         ""
//                     );

//                     // Add the new color class
//                     if (color && statusSelect.value !== "") {
//                         statusSelect.classList.add("select-bg-label-" + color);
//                     } else {
//                         statusSelect.classList.add("select-bg-label-primary");
//                     }
//                 }
//             });
//         });

//         observer.observe(statusSelect, {
//             attributes: true,
//             attributeFilter: ["value"],
//         });
//     }
// });

// $(document).ready(function () {
//     // Handle sort change
//     $("#sort").on("change", function () {
//         $("#table").bootstrapTable("refresh");
//     });

//     // Handle candidate status selection change
//     $("#select_candidate_statuses").on("change", function () {
//         $("#table").bootstrapTable("refresh");
//     });

//     // Handle date range picker apply event
//     $("#candidate_filter_date_range").on(
//         "apply.daterangepicker",
//         function (ev, picker) {
//             var startDate = picker.startDate.format("YYYY-MM-DD");
//             var endDate = picker.endDate.format("YYYY-MM-DD");

//             // Update hidden fields for query params
//             $("#candidate_end_date").val(endDate);
//             $("#candidate_start_date").val(startDate);

//             // Update display value for the date range input
//             $(this).val(
//                 picker.startDate.format(js_date_format) +
//                     " To " +
//                     picker.endDate.format(js_date_format)
//             );

//             // Refresh table
//             $("#table").bootstrapTable("refresh");
//         }
//     );

//     // Handle date range picker cancel event
//     $("#candidate_filter_date_range").on(
//         "cancel.daterangepicker",
//         function (ev, picker) {
//             $("#candidate_end_date").val("");
//             $("#candidate_start_date").val("");
//             $("#candidate_filter_date_range").val("");
//             picker.setStartDate(moment());
//             picker.setEndDate(moment());
//             picker.updateElement();
//             $("#table").bootstrapTable("refresh");
//         }
//     );

//     // Handle status color change
//     $(document).on("change", "#candidateStatusId", function () {
//         const selectedOption = this.options[this.selectedIndex];
//         const color = selectedOption.getAttribute("data-color");

//         this.className = this.className.replace(/select-bg-label-\w+/g, "");

//         if (color && this.value !== "") {
//             this.classList.add("select-bg-label-" + color);
//         } else {
//             this.classList.add("select-bg-label-primary");
//         }
//     });
// });

$(document).on("click", "#createCandidateBtn", function () {
    $("#candidateModal").modal("show");
});

function queryParams(p) {
    return {
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search,
        sort: $("#sort").val(),
        candidate_status: $("#select_candidate_statuses").val(),
        start_date: $("#candidate_start_date").val(),
        end_date: $("#candidate_end_date").val(),
    };
}

// Function to update status color
function updateStatusColor(selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const color = selectedOption.getAttribute("data-color");

    // Remove all existing color classes
    selectElement.className = selectElement.className.replace(
        /select-bg-label-\w+/g,
        ""
    );

    if (color && selectElement.value !== "") {
        selectElement.classList.add("select-bg-label-" + color);
    } else {
        selectElement.classList.add("select-bg-label-primary");
    }
}

$(document).ready(function () {
    // Create candidate button click
    $(document).on("click", "#createCandidateBtn", function () {
        $("#candidateModal").modal("show");
    });

    // Handle status color change for both create and update modals
    $(document).on("change", "#candidateStatusId", function () {
        updateStatusColor(this);
    });

    // Edit candidate button click - FIXED VERSION
    $(document).on("click", ".edit-candidate-btn", function (e) {
        e.preventDefault();

        try {
            const candidate = $(this).data("candidate");

            if (!candidate) {
                console.error("No candidate data found");
                return;
            }

            const actionUrl = `/master-panel/candidate/update/${candidate.id}`;
            console.log("Candidate data:", candidate);

            // Set form action
            $("#updateCandidateForm").attr("action", actionUrl);

            // Populate form fields
            $("#candidateName").val(candidate.name || "");
            $("#candidateEmail").val(candidate.email || "");
            $("#candidatePhone").val(candidate.phone || "");
            $("#candidateSource").val(candidate.source || "");
            $("#candidatePosition").val(candidate.position || "");

            // Set status - FIXED: Handle both status_id and status fields
            const statusValue = candidate.status_id || candidate.status;
            const statusSelect = document.getElementById("candidateStatusId");

            if (statusSelect && statusValue) {
                statusSelect.value = statusValue;
                // Trigger change event manually to update color
                updateStatusColor(statusSelect);

                // Alternative method to trigger change event
                const changeEvent = new Event("change", { bubbles: true });
                statusSelect.dispatchEvent(changeEvent);
            }

            // Show the modal
            $("#candidateUpdateModal").modal("show");
        } catch (error) {
            console.error("Error in edit candidate:", error);
        }
    });

    // View interviews functionality
    $(document).on("click", ".view-interviews-btn", function () {
        const candidateId = $(this).data("id");
        const modal = $("#interviewDetailsModal");

        if (!candidateId) {
            showError(modal, "Candidate ID is missing.");
            return;
        }

        modal.find("#interviewDetailsContent").html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);

        modal.modal("show");

        // AJAX call to fetch interview details
        $.ajax({
            url: `/master-panel/candidate/${candidateId}/interviews`,
            method: "GET",
            success: function (response) {
                if (response && !response.error) {
                    console.log(response);
                    modal.find("#interviewDetailsContent").html(response.html);
                    modal.find(".modal-title").html(`
                        <i class="bx bx-calendar-check me-2"></i> Interviews for ${response.candidate.name}
                    `);
                    initializeModalComponents();
                } else {
                    showError(
                        modal,
                        response.message || "Error fetching interview details."
                    );
                }
            },
            error: function (xhr) {
                const errorMessage =
                    xhr.responseJSON?.message ||
                    "An unexpected error occurred.";
                showError(modal, errorMessage);
            },
        });
    });

    // Bootstrap initializers (tooltips/popovers) inside dynamic modal
    function initializeModalComponents() {
        document
            .querySelectorAll('[data-bs-toggle="tooltip"]')
            .forEach((el) => {
                new bootstrap.Tooltip(el);
            });

        document
            .querySelectorAll('[data-bs-toggle="popover"]')
            .forEach((el) => {
                new bootstrap.Popover(el);
            });
    }

    // Error rendering helper
    function showError(modal, message) {
        modal.find("#interviewDetailsContent").html(`
            <div class="alert alert-danger d-flex align-items-center">
                <i class="bx bx-error-circle me-2 fs-5"></i>
                <div>${message}</div>
            </div>
        `);
    }

    // Handle sort change
    $("#sort").on("change", function () {
        $("#table").bootstrapTable("refresh");
    });

    // Handle candidate status selection change
    $("#select_candidate_statuses").on("change", function () {
        $("#table").bootstrapTable("refresh");
    });

    // Handle date range picker apply event
    $("#candidate_filter_date_range").on(
        "apply.daterangepicker",
        function (ev, picker) {
            var startDate = picker.startDate.format("YYYY-MM-DD");
            var endDate = picker.endDate.format("YYYY-MM-DD");

            $("#candidate_end_date").val(endDate);
            $("#candidate_start_date").val(startDate);

            $(this).val(
                picker.startDate.format(js_date_format) +
                    " To " +
                    picker.endDate.format(js_date_format)
            );

            $("#table").bootstrapTable("refresh");
        }
    );

    // Handle date range picker cancel event
    $("#candidate_filter_date_range").on(
        "cancel.daterangepicker",
        function (ev, picker) {
            $("#candidate_end_date").val("");
            $("#candidate_start_date").val("");
            $("#candidate_filter_date_range").val("");
            picker.setStartDate(moment());
            picker.setEndDate(moment());
            picker.updateElement();
            $("#table").bootstrapTable("refresh");
        }
    );
});
