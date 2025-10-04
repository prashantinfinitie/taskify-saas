"use strict";
$("#status , #filter_by_type").on("change", function () {
    $("#table").bootstrapTable("refresh");
});
function queryParams(p) {
    return {
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search,
        status: $("#status").val(),
        type: $("#filter_by_type").val(),
    };
}
window.icons = {
    refresh: "bx-refresh",
    toggleOff: "bx-toggle-left",
    toggleOn: "bx-toggle-right",
};
function loadingTemplate(message) {
    return '<i class="bx bx-loader-alt bx-spin bx-flip-vertical" ></i>';
}

function actionFormatter(value, row, index) {
    return [
        '<a href="' +
            routePrefix +
            "/plans/edit/" +
            row.id +
            '" title=' +
            label_update +
            ">" +
            '<i class="bx bx-edit mx-1">' +
            "</i>" +
            "</a>" +
            "<button title=" +
            label_delete +
            ' type="button" class="btn delete" data-id=' +
            row.id +
            ' data-type="plans">' +
            '<i class="bx bx-trash text-danger mx-1"></i>' +
            "</button>",
    ];
}
// Helper function to validate discounted prices
function validateDiscountedPrice(tenure, basePrice) {
    const discountedPrice = $(`#${tenure}_discounted_price`).val().trim();
    if (discountedPrice) {
        const parsedBasePrice = parseFloat(basePrice);
        const parsedDiscountedPrice = parseFloat(discountedPrice);

        if (isNaN(parsedDiscountedPrice) || parsedDiscountedPrice < 0) {
            toastr.error(
                `${
                    tenure.charAt(0).toUpperCase() + tenure.slice(1)
                } discounted price must be a non-negative numerical value.`
            );
            return false;
        } else if (parsedDiscountedPrice >= parsedBasePrice) {
            toastr.error(
                `${
                    tenure.charAt(0).toUpperCase() + tenure.slice(1)
                } discounted price must be lower than the main price.`
            );
            return false;
        }
    }
    return true;
}
$(document).ready(function () {
    $("#createPlanButton").on("click", function (event) {
        event.preventDefault();
        var $submitBtn = $(this);
        var orignalText = $submitBtn.text();
        $submitBtn.text(label_please_wait).prop("disabled", true);

        // Basic validation
        let isValid = true;
        const planName = $("#planName").val().trim();
        const planDescription = $("#planDescription").val().trim();
        const maxProjects = parseInt($("#maxProjects").val());
        const maxClients = parseInt($("#maxClients").val());
        const maxTeamMembers = parseInt($("#maxTeamMembers").val());
        const maxWorkshops = parseInt($("#maxWorkshops").val());
        const selectedModules = [];
        const tenureSwitchChecked = $("#allTenuresSwitch").prop("checked");
        const isPlanFree = $("#planFreeSwitch").prop("checked");
        $(".module-checkbox").each(function () {
            if ($(this).is(":checked")) {
                selectedModules.push($(this).val());
            }
        });

        // Check for empty fields
        if (!planName || !planDescription) {
            isValid = false;
            toastr.error("Please fill in all required fields.");
            $submitBtn.text(orignalText).prop("disabled", false);
        }

        // Check for non-numeric inputs
        if (
            isNaN(maxProjects) ||
            isNaN(maxClients) ||
            isNaN(maxTeamMembers) ||
            isNaN(maxWorkshops)
        ) {
            isValid = false;
            toastr.error("Maximum values must be numerical.");
            $submitBtn.text(orignalText).prop("disabled", false);
        }

        // Check for negative values
        if (
            maxProjects < -1 ||
            maxClients < -1 ||
            maxTeamMembers < -1 ||
            maxWorkshops < -1
        ) {
            isValid = false;
            toastr.error("Maximum values cannot be less than -1.");
            $submitBtn.text(orignalText).prop("disabled", false);
        }

        // Check for selected modules
        if (selectedModules.length === 0) {
            isValid = false;
            toastr.error("Please select at least one module.");
            $submitBtn.text(orignalText).prop("disabled", false);
        }

        // Check for tenure pricing if switch is checked and plan is not free
        if (tenureSwitchChecked && !isPlanFree) {
            // Monthly price validation - always required
            const monthlyPrice = $("#monthly_price").val().trim();
            if (!monthlyPrice) {
                isValid = false;
                toastr.error("Please enter price for monthly tenure.");
            }

            const parsedMonthlyPrice = parseFloat(monthlyPrice);
            if (isNaN(parsedMonthlyPrice) || parsedMonthlyPrice < 1) {
                isValid = false;
                toastr.error("Monthly price must be greater than 0 in paid plan.");
                $submitBtn.text(orignalText).prop("disabled", false);
            } else {
                // Validate monthly discounted price
                isValid = validateDiscountedPrice("monthly", monthlyPrice) && isValid;
            }

            // Yearly price validation - only if provided
            const yearlyPrice = $("#yearly_price").val().trim();
            if (yearlyPrice) {
                const parsedYearlyPrice = parseFloat(yearlyPrice);
                if (isNaN(parsedYearlyPrice) || parsedYearlyPrice < 1) {
                    isValid = false;
                    toastr.error("Yearly price must be greater than 0 in paid plan.");
                } else {
                    // Validate yearly discounted price
                    isValid = validateDiscountedPrice("yearly", yearlyPrice) && isValid;
                }
            }

            // Lifetime price validation - only if provided
            const lifetimePrice = $("#lifetime_price").val().trim();
            if (lifetimePrice) {
                const parsedLifetimePrice = parseFloat(lifetimePrice);
                if (isNaN(parsedLifetimePrice) || parsedLifetimePrice < 1) {
                    isValid = false;
                    toastr.error("Lifetime price must be greater than 0 in paid plan.");
                } else {
                    // Validate lifetime discounted price
                    isValid = validateDiscountedPrice("lifetime", lifetimePrice) && isValid;
                }
            }
        }

        var status = $('input[name="status"]:checked').val();

        if (isValid) {
            var fileInput = document.getElementById("planImage");
            var file = fileInput.files[0];
            var tenurePrices = getTenurePrices();
            var discountedPrices = getDiscountedPrices();

            var formData = new FormData();
            formData.append("name", planName);
            formData.append("description", planDescription);
            formData.append("max_projects", maxProjects);
            formData.append("max_clients", maxClients);
            formData.append("max_team_members", maxTeamMembers);
            formData.append("max_workspaces", maxWorkshops);
            formData.append("modules", JSON.stringify(selectedModules));
            formData.append("tenurePrices", JSON.stringify(tenurePrices));
            formData.append("discountedPrices", JSON.stringify(discountedPrices));
            formData.append("planType", $("#plan_type").val());
            formData.append("status", status);
            formData.append("plan_image", file);

            $.ajax({
                url: $("#plan-create-form").attr("action"),
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": $('input[name="_token"]').attr("value"),
                },
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (!response.error) {
                        toastr.success(response.message);
                        setTimeout(function () {
                            window.location = response.redirect_url;
                        }, 3000);
                    } else {
                        toastr.error(response.message);
                        setTimeout(function () {
                            window.location.reload();
                        }, 3000);
                    }
                },
                error: function (response) {
                    $("#createPlanButton").text(label_create_plan);
                    $("#createPlanButton").prop("disabled", false);
                    if (response.status === 422) {
                        var errors = response.responseJSON.errors;
                        $.each(errors, function (field, messages) {
                            toastr.error(messages.join(", "));
                        });
                    } else {
                        toastr.error("An unexpected error occurred.");
                    }
                },
                complete: function () {
                    $submitBtn.text(orignalText).prop("disabled", false);
                },
            });
        } else {
            $submitBtn.text(orignalText).prop("disabled", false);
        }
    });

    $('#updatePlanButton').on("click", function (event) {
        event.preventDefault();
        var $submitBtn = $(this); // Store button reference
        var originalText = $submitBtn.text();
        $submitBtn.text(label_please_wait).prop('disabled', true);
    
        // Basic validation
        let isValid = true;
        const planName = $('#planName').val().trim();
        const planDescription = $('#planDescription').val().trim();
        const maxProjects = parseInt($('#maxProjects').val());
        const maxClients = parseInt($('#maxClients').val());
        const maxTeamMembers = parseInt($('#maxTeamMembers').val());
        const maxWorkshops = parseInt($('#maxWorkshops').val());
        const selectedModules = [];
        const tenureSwitchChecked = $('#allTenuresSwitch').prop('checked');
        const isPlanFree = $('#planFreeSwitch').prop('checked');
    
        $('.module-checkbox').each(function () {
            if ($(this).is(':checked')) {
                selectedModules.push($(this).val());
            }
        });
    
        // Check for empty fields
        if (!planName || !planDescription) {
            isValid = false;
            toastr.error('Please fill in all required fields.');
        }
    
        // Check for non-numeric inputs
        if (isNaN(maxProjects) || isNaN(maxClients) || isNaN(maxTeamMembers) || isNaN(maxWorkshops)) {
            isValid = false;
            toastr.error('Maximum values must be numerical.');
        }
    
        // Check for negative values
        if (maxProjects < -1 || maxClients < -1 || maxTeamMembers < -1 || maxWorkshops < -1) {
            isValid = false;
            toastr.error('Maximum values cannot be less than -1.');
        }
    
        // Check for selected modules
        if (selectedModules.length === 0) {
            isValid = false;
            toastr.error('Please select at least one module.');
        }
    
        // Check for tenure pricing if switch is checked and plan is not free
        if (tenureSwitchChecked && !isPlanFree) {
            let prices = {};
    
            // Monthly price validation (required)
            const monthlyPrice = $('#monthly_price').val().trim();
            if (!monthlyPrice) {
                isValid = false;
                toastr.error('Please enter price for monthly tenure.');
            } else {
                const parsedMonthlyPrice = parseFloat(monthlyPrice);
                if (isNaN(parsedMonthlyPrice) || parsedMonthlyPrice < 1) {
                    isValid = false;
                    toastr.error('Monthly price must be greater than 0 in paid plan.');
                } else {
                    prices['monthly'] = parsedMonthlyPrice;
                    // Validate monthly discounted price
                    const monthlyDiscountedPrice = $('#monthly_discounted_price').val().trim();
                    if (monthlyDiscountedPrice) {
                        const parsedDiscountedPrice = parseFloat(monthlyDiscountedPrice);
                        if (isNaN(parsedDiscountedPrice) || parsedDiscountedPrice < 0) {
                            isValid = false;
                            toastr.error('Monthly discounted price must be a non-negative numerical value.');
                        } else if (parsedDiscountedPrice >= parsedMonthlyPrice) {
                            isValid = false;
                            toastr.error('Monthly discounted price must be lower than the main price.');
                        }
                    }
                }
            }
    
            // Yearly price validation (optional)
            const yearlyPrice = $('#yearly_price').val().trim();
            if (yearlyPrice) {
                const parsedYearlyPrice = parseFloat(yearlyPrice);
                if (isNaN(parsedYearlyPrice) || parsedYearlyPrice < 1) {
                    isValid = false;
                    toastr.error('Yearly price must be greater than 0 in paid plan.');
                } else {
                    prices['yearly'] = parsedYearlyPrice;
                    // Validate yearly discounted price
                    const yearlyDiscountedPrice = $('#yearly_discounted_price').val().trim();
                    if (yearlyDiscountedPrice) {
                        const parsedDiscountedPrice = parseFloat(yearlyDiscountedPrice);
                        if (isNaN(parsedDiscountedPrice) || parsedDiscountedPrice < 0) {
                            isValid = false;
                            toastr.error('Yearly discounted price must be a non-negative numerical value.');
                        } else if (parsedDiscountedPrice >= parsedYearlyPrice) {
                            isValid = false;
                            toastr.error('Yearly discounted price must be lower than the main price.');
                        }
                    }
                }
            }
    
            // Lifetime price validation (optional)
            const lifetimePrice = $('#lifetime_price').val().trim();
            if (lifetimePrice) {
                const parsedLifetimePrice = parseFloat(lifetimePrice);
                if (isNaN(parsedLifetimePrice) || parsedLifetimePrice < 1) {
                    isValid = false;
                    toastr.error('Lifetime price must be greater than 0 in paid plan.');
                } else {
                    prices['lifetime'] = parsedLifetimePrice;
                    // Validate lifetime discounted price
                    const lifetimeDiscountedPrice = $('#lifetime_discounted_price').val().trim();
                    if (lifetimeDiscountedPrice) {
                        const parsedDiscountedPrice = parseFloat(lifetimeDiscountedPrice);
                        if (isNaN(parsedDiscountedPrice) || parsedDiscountedPrice < 0) {
                            isValid = false;
                            toastr.error('Lifetime discounted price must be a non-negative numerical value.');
                        } else if (parsedDiscountedPrice >= parsedLifetimePrice) {
                            isValid = false;
                            toastr.error('Lifetime discounted price must be lower than the main price.');
                        }
                    }
                }
            }
        }
    
        var status = $('input[name="status"]:checked').val();
    
        if (isValid) {
            var fileInput = document.getElementById('planImage');
            var file = fileInput.files[0];
            var tenurePrices = getTenurePrices();
            var discountedPrices = getDiscountedPrices();
    
            var formData = new FormData();
            formData.append('name', planName);
            formData.append('description', planDescription);
            formData.append('max_projects', maxProjects);
            formData.append('max_clients', maxClients);
            formData.append('max_team_members', maxTeamMembers);
            formData.append('max_workspaces', maxWorkshops);
            formData.append('modules', JSON.stringify(selectedModules));
            formData.append('tenurePrices', JSON.stringify(tenurePrices));
            formData.append('discountedPrices', JSON.stringify(discountedPrices));
            formData.append('plan_type', $('#plan_type').val());
            formData.append('status', status);
            if (file) {
                formData.append('plan_image', file);
            }
    
            $.ajax({
                url: $('#plan-update-form').attr('action'),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').attr('value')
                },
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (!response.error) {
                        toastr.success(response.message);
                        setTimeout(function () {
                            window.location = response.redirect_url;
                        }, 3000);
                    } else {
                        toastr.error(response.message);
                        setTimeout(function () {
                            window.location.reload();
                        }, 5000);
                    }
                },
                error: function (response) {
                    if (response.status === 422) {
                        var errors = response.responseJSON.errors;
                        $.each(errors, function (field, messages) {
                            toastr.error(messages.join(', '));
                        });
                    } else {
                        toastr.error('An unexpected error occurred.');
                    }
                },
                complete: function () {
                    $submitBtn.text(originalText).prop('disabled', false); // Correctly reset button
                }
            });
        } else {
            $submitBtn.text(originalText).prop('disabled', false);
        }
    });
});
// Function to get tenure prices - without using switches
function getTenurePrices() {
    const tenurePrices = [];

    // Monthly is always required
    tenurePrices.push({
        tenure: "monthly_price",
        price: $("#monthly_price").val().trim() || 0,
    });

    // Add yearly price if provided, otherwise set to 0
    const yearlyPrice = $("#yearly_price").val().trim();
    tenurePrices.push({
        tenure: "yearly_price",
        price: yearlyPrice || 0,
    });

    // Add lifetime price if provided, otherwise set to 0
    const lifetimePrice = $("#lifetime_price").val().trim();
    tenurePrices.push({
        tenure: "lifetime_price",
        price: lifetimePrice || 0,
    });

    return tenurePrices;
}

// Function to get discounted prices - without using switches
function getDiscountedPrices() {
    const discountedPrices = [];

    // Monthly discounted price
    discountedPrices.push({
        tenure: "monthly_discounted_price",
        discountedPrice: $("#monthly_discounted_price").val().trim() || 0,
    });

    // Add yearly discounted price
    discountedPrices.push({
        tenure: "yearly_discounted_price",
        discountedPrice: $("#yearly_discounted_price").val().trim() || 0,
    });

    // Add lifetime discounted price
    discountedPrices.push({
        tenure: "lifetime_discounted_price",
        discountedPrice: $("#lifetime_discounted_price").val().trim() || 0,
    });

    return discountedPrices;
}
$(document).ready(function () {
    // Select all checkboxes
    $("#select-all-checkbox").change(function () {
        $(".module-checkbox").prop("checked", $(this).prop("checked"));
    });
    $(".module-checkbox").change(function () {
        if (!$(this).prop("checked")) {
            $("#select-all-checkbox").prop("checked", false);
        }
    });
    function updateSelectAllCheckbox() {
        const allChecked =
            $(".module-checkbox").length ===
            $(".module-checkbox:checked").length;
        $("#select-all-checkbox").prop("checked", allChecked);
    }
    // Initial check when the page loads
    updateSelectAllCheckbox();
    $(".module-checkbox").on("change", function () {
        updateSelectAllCheckbox();
    });
});
$(document).ready(function () {
    // Hide pricing fields if switch is unchecked by default
    if (!$("#allTenuresSwitch").prop("checked")) {
        $("#plan_type").val("free");
        $(".monthly_tenure , .yearly_tenure , .lifetime_tenure").hide();
    }
    // Toggle visibility of pricing fields when switch is toggled
    $("#allTenuresSwitch").change(function () {
        if ($(this).prop("checked")) {
            $("#plan_type").val("paid");
            $(".monthly_tenure , .yearly_tenure , .lifetime_tenure").show();
        } else {
            $("#plan_type").val("free");
            $(".monthly_tenure , .yearly_tenure , .lifetime_tenure").hide();
        }
    });
});