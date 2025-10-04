$(document).on('ready', function () {
    $('#plan_id , #filter_plans , #user_id').select2();
})
$('#filter_plans , #status').on('change', function () {
    $('#table').bootstrapTable('refresh');
});
function queryParams(p) {
    return {
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search,
        plan_id: $('#filter_plans').val(),
        status: $('#status').val()
    };
}
window.icons = {
    refresh: 'bx-refresh',
    toggleOff: 'bx-toggle-left',
    toggleOn: 'bx-toggle-right'
}
function loadingTemplate(message) {
    return '<i class="bx bx-loader-alt bx-spin bx-flip-vertical" ></i>'
}
function actionFormatter(value, row, index) {
    return [
        '<a href="' + routePrefix + '/subscriptions/edit/' + row.id + '" title=' + label_update + '>' +
        '<i class="bx bx-edit mx-1">' +
        '</i>' +
        '</a>' +
        '<button title=' + label_delete + ' type="button" class="btn delete" data-id=' + row.id + ' data-type="subscriptions">' +
        '<i class="bx bx-trash text-danger mx-1"></i>' +
        '</button>' +
        '<a href="javascript:void(0)" class="view-subscription" data-id=' + row.id + ' title=' + label_view + '>' +
        '<i class="bx bxs-info-circle text-dark mx-1">' +
        '</i>' +
        '</a>'
    ]
}
// show subscriptions details
$(document).on('click', '.view-subscription', function () {
    var subscriptionId = $(this).data('id');
    $.ajax({
        type: "GET",
        url: "/superadmin/subscriptions/get/" + subscriptionId,
        dataType: "json",
        success: function (response) {
            var subscription = response.subscription;
            // Populate basic subscription details
            $('#subscriptionId').text(subscription.id);
            $('#subscriptionUser').text(subscription.user.first_name + ' ' + subscription.user.last_name);
            $('#subscriptionPlan').text(subscription.plan.name);
            $('#subscriptionStatus').removeClass().addClass('badge').addClass(getStatusBadgeClass(subscription.status)).text(subscription.status);
            $('#subscriptionPaymentMethod').text(formatString(subscription.payment_method)); // Use the formatString function
            $('#subscriptionTenure').text(formatString(subscription.tenure));
            $('#subscriptionPrice').text(subscription.charging_currency + subscription.charging_price);
            $('#subscriptionStartsAt').text(subscription.starts_at);
            $('#subscriptionEndsAt').text(subscription.ends_at);
            // Populate features
            var features = JSON.parse(subscription.features);
            $('#subscriptionFeatures').empty();
            $('#subscriptionFeatures').append('<li>Max Clients: ' + features.max_clients + '</li>');
            $('#subscriptionFeatures').append('<li>Max Projects: ' + features.max_projects + '</li>');
            $('#subscriptionFeatures').append('<li>Max Team Members: ' + features.max_team_members + '</li>');
            $('#subscriptionFeatures').append('<li>Max Workspaces: ' + features.max_workspaces + '</li>');
            $('#subscriptionFeatures').append('<li>Modules: ' + features.modules.join(', ') + '</li>');
            // Populate transactions
            $('#subscriptionTransactions').empty();
            subscription.transactions.forEach(function (transaction) {
                $('#subscriptionTransactions').append('<tr><td>' + transaction.id + '</td><td>' + transaction.currency + transaction.amount + '</td><td>' + transaction.status + '</td><td>' + formatString(transaction.payment_method) + '</td><td>' + transaction.transaction_id + '</td></tr>');
            });
            // Open modal
            $('#subscriptionModal').modal('show');
            // Assuming 'subscription' is your subscription object
            $('#subscriptionStatus').removeClass().addClass('badge').addClass(getStatusBadgeClass(subscription.status)).text(subscription.status);
        }
    });
});
function formatString(str) {
    return str
        .split('_') // Split the string by underscores
        .map(word => word.charAt(0).toUpperCase() + word.slice(1)) // Capitalize the first letter of each word
        .join(' '); // Join the words back with spaces
}
function getStatusBadgeClass(status) {
    switch (status.toLowerCase()) {
        case 'active':
            return 'bg-label-primary';
        case 'inactive':
            return 'bg-label-danger';
        case 'pending':
            return 'bg-label-warning';
        case 'expired':
            return 'bg-label-danger';
        default:
            return 'bg-label-secondary';
    }
}
// Fetch Documents and Verify
$(document).on('click', '.view-documents', function (e) {
    e.preventDefault();
    const subscriptionId = $(this).data('subscription-id');

    // Clear previous content
    $('#documentsContainer').empty();

    // Show modal
    $('#viewDocumentsModal').modal('show');

    // Fetch documents
    $.ajax({
        url: `/superadmin/subscriptions/${subscriptionId}/documents`,
        type: 'GET',
        success: function (response) {
            if (response.success && response.documents.length > 0) {
                response.documents.forEach((document, index) => {
                    const row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="document-thumbnail me-3">
                                        <img src="${document.url}"
                                             alt="${document.name}"
                                             class="img-thumbnail cursor-pointer view-full-image"
                                             data-full-image="${document.url}"
                                             style="width: 100px; height: 100px; object-fit: cover;">
                                    </div>
                                    <div>
                                        <span class="document-name">${document.name}</span>
                                        <br>
                                        <small class="text-muted">Uploaded: ${new Date(document.created_at).toLocaleDateString()}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="${document.url}"
                                       class="btn btn-sm btn-primary"
                                       download="${document.name}"
                                       title="Download">
                                        <i class="bx bx-download"></i>
                                    </a>

                                </div>
                            </td>
                        </tr>
                    `;
                    $('#documentsContainer').append(row);
                });
            } else {
                $('#documentsContainer').html('<tr><td colspan="3" class="text-center">No documents found</td></tr>');
            }
        },
        error: function () {
            $('#documentsContainer').html('<tr><td colspan="3" class="text-center">Failed to load documents</td></tr>');
        }
    });
});

// Add click handler for image preview
$(document).on('click', '.view-full-image', function () {
    const imageUrl = $(this).data('full-image');
    const imageName = $(this).attr('alt');

    // Create and show image preview modal
    const previewModal = `
        <div class="modal fade" id="imagePreviewModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${imageName}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="${imageUrl}" class="img-fluid" alt="${imageName}">
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove existing preview modal if any
    $('#imagePreviewModal').remove();

    // Add new preview modal to body
    $('body').append(previewModal);

    // Show the preview modal
    const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
    modal.show();
});


let selectedSubscriptionId = null;

// Click handler for verify payment button
$(document).on('click', '.verify-payment', function (e) {
    e.preventDefault();
    selectedSubscriptionId = $(this).data('subscription-id');
    $('#verifyPaymentModal').modal('show'); // Show the modal();
});

// Click handler for confirm button in modal
$(document).on('click', '#confirmVerifyPayment', function (e) {


    if (selectedSubscriptionId) {
        $.ajax({
            url: `/superadmin/subscriptions/${selectedSubscriptionId}/verify-payment`,
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.error) {
                    // Show error message
                    toastr.error('Failed to verify payment');
                    setTimeout(function () {
                        location.reload();
                    }, 2000);

                } else {
                    // Show success message
                    toastr.success(response.message);
                    setTimeout(function () {
                        window.location.href = response.redirect_url;
                    }, 2000);

                }

                // Hide modal
                $('#verifyPaymentModal').modal('hide');
            },
            error: function (response, error, data) {
                // Show error message
                toastr.error(response.message || 'Failed to verify payment');

                // Hide modal
                $('#verifyPaymentModal').modal('hide');

            }
        });
    }
});

// Reset selectedSubscriptionId when modal is hidden
$('#verifyPaymentModal').on('hidden.bs.modal', function () {
    selectedSubscriptionId = null;
});

// Function to update price and discounted price based on selected tenure
function updatePrices(planId, tenure) {
    var selectedPlan = plans.find(plan => plan.id == planId);
    if (!selectedPlan) {
        console.error('Selected plan not found');
        return;
    }
    var price, discountedPrice;
    // Update price and discounted price based on the selected tenure
    switch (tenure) {
        case 'monthly':
            price = selectedPlan.monthly_price;
            discountedPrice = selectedPlan.monthly_discounted_price;
            break;
        case 'yearly':
            price = selectedPlan.yearly_price;
            discountedPrice = selectedPlan.yearly_discounted_price;
            break;
        case 'lifetime':
            price = selectedPlan.lifetime_price;
            discountedPrice = selectedPlan.lifetime_discounted_price;
            break;
        default:
            console.error('Invalid tenure:', tenure);
            return;
    }

    // Ensure price and discountedPrice are valid numbers
    price = parseFloat(price);
    discountedPrice = parseFloat(discountedPrice);
    // Check if price and discountedPrice are valid numbers
    if (!isNaN(price) && !isNaN(discountedPrice)) {
        // Update the input fields with the calculated prices, formatted to two decimal places
        $('#price').val(price.toFixed(2));
        $('#discounted_price').val(discountedPrice.toFixed(2));
        // Calculate charging price based on conditions
        var chargingPrice = (discountedPrice === 0 || discountedPrice >= price) ? price.toFixed(2) : discountedPrice.toFixed(2);
        // Display charging price with currency symbol
        $('#charging_price').text(currency_symbol + ' ' + chargingPrice);
    } else {
        console.error('Invalid price or discountedPrice:', price, discountedPrice);
        // Handle case where price or discountedPrice is not a valid number
        // Optionally show an error message or handle the scenario as needed
    }
}
// Function to handle plan selection change
$('#plan_id').change(function () {
    var selectedTenure = $('#tenure').val();
    var selectedPlanId = $(this).val();
    updatePrices(selectedPlanId, selectedTenure);
});
// Bind change event to the tenure select dropdown to update prices dynamically
$('#tenure').change(function () {
    var selectedPlanId = $('#plan_id').val();
    var selectedTenure = $(this).val();
    updatePrices(selectedPlanId, selectedTenure);
});
// Call the updatePrices function initially with the default plan and tenure
var defaultPlanId = $('#plan_id').val();
var defaultTenure = $('#tenure').val();
updatePrices(defaultPlanId, defaultTenure);
function calculateEndDate(startDate, tenure) {
    // Parse the start date using Moment.js, specifying the input format
    var momentStartDate = moment(startDate, js_date_format);

    // Check if the date parsing was successful
    if (!momentStartDate.isValid()) {
        console.error('Invalid start date:', startDate);
        return '';
    }

    // Calculate the end date based on the tenure
    switch (tenure) {
        case 'monthly':
            return momentStartDate.add(1, 'months').format(js_date_format);
        case 'yearly':
            return momentStartDate.add(1, 'years').format(js_date_format);
        case 'lifetime':
            return momentStartDate.add(100, 'years').format(js_date_format);
        default:
            return ''; // Return an empty string for unknown tenures
    }
}

function updateEndDate(startDate, tenure) {
    var endDate = calculateEndDate(startDate, tenure);
    $('#subscription_end_date').val(endDate);
}
// Event listener for changes in the start date or tenure
$('#subscription_start_date, #tenure').change(function () {
    var startDate = $('#subscription_start_date').val();
    var tenure = $('#tenure').val();
    updateEndDate(startDate, tenure);
});
$(document).ready(function () {
    const today = new Date();
    const formattedDate = moment(today).format(js_date_format);
    $("#subscription_start_date").val(formattedDate).prop("readonly", true);
    var startDate = $('#subscription_start_date').val();
    var tenure = $('#tenure').val();
    var endDate = calculateEndDate(startDate, tenure);
    $('#subscription_end_date').val(endDate);
});
//plan features
function updateFeatures(planId) {
    var selectedPlan = plans.find(plan => plan.id == planId);
    var features = '';
    var plan_features = {
        max_clients: selectedPlan.max_clients,
        max_projects: selectedPlan.max_projects,
        max_team_members: selectedPlan.max_team_members,
        max_workspaces: selectedPlan.max_workspaces,
        modules: JSON.parse(selectedPlan.modules),
    }
    if (selectedPlan) {
        // Parse the modules JSON string into an array
        var modules = JSON.parse(selectedPlan.modules);
        // Construct the HTML for displaying the features
        var featuresHTML = '<ul>';
        featuresHTML += '<li>Max Projects: ' + selectedPlan.max_projects + '</li>';
        featuresHTML += '<li>Max Team Members: ' + selectedPlan.max_team_members + '</li>';
        featuresHTML += '<li>Max Workspaces: ' + selectedPlan.max_worksapces + '</li>';
        featuresHTML += '<li>Max Clients: ' + selectedPlan.max_clients + '</li>';
        featuresHTML += '<li>Modules:</li>';
        featuresHTML += '<ul>';
        modules.forEach(module => {
            var capitalizedModule = module.charAt(0).toUpperCase() + module.slice(1);
            featuresHTML += '<li>' + capitalizedModule + '</li>';
        });
        featuresHTML += '</ul>';
        featuresHTML += '</ul>';
        // Update the content of the plan_features div with the features HTML
        $('#plan_features').html(featuresHTML);
    } else {
        // If the selected plan is not found, display an error message
        $('#plan_features').html('<p>Error: Plan not found</p>');
    }
}
// Event listener for the plan dropdown change event
$('#plan_id').change(function () {
    var selectedPlanId = $(this).val();
    updateFeatures(selectedPlanId);
});
var defaultPlanId = $('#plan_id').val();
updateFeatures(defaultPlanId);
// Validate subscription form before submission
function validateSubscriptionForm() {
    var planId = $('#plan_id').val();
    var userId = $('#user_id').val();
    var tenure = $('#tenure').val();
    var startDate = $('#subscription_start_date').val();
    var endDate = $('#subscription_end_date').val();
    var paymentMethod = $('#payment_method').val();
    if (!planId || !userId || !tenure || !startDate || !endDate || !paymentMethod) {
        toastr.error('Please fill in all required fields.');
        return false;
    }
    return true;
}
// Submit the form after validating
$('#create_subscription_form').on('submit', function (event) {
    event.preventDefault();
    if (validateSubscriptionForm()) {
        var selectedPlan = plans.find(plan => plan.id == $('#plan_id').val());
        var plan_features = {
            max_clients: selectedPlan.max_clients,
            max_projects: selectedPlan.max_projects,
            max_team_members: selectedPlan.max_team_members,
            max_workspaces: selectedPlan.max_worksapces,
            modules: JSON.parse(selectedPlan.modules),
        }
        var chargingPriceText = $('#charging_price').text();
        var chargingPriceParts = chargingPriceText.split(currency_symbol);
        var chargingPrice = chargingPriceParts[1].trim();
        var data = {
            plan_id: $('#plan_id').val(),
            user_id: $('#user_id').val(),
            tenure: $('#tenure').val(),
            payment_method: $('#payment_method').val(),
            charging_price: chargingPrice,
            charging_currency: currency_symbol,
            start_date: $('#subscription_start_date').val(),
            end_date: $('#subscription_end_date').val(),
            features: JSON.stringify(plan_features),
            transaction_id: $('#transaction_id').val(),
        }
        $.ajax({
            url: $(this).attr('action'),
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').attr('value') // Replace with your method of getting the CSRF token
            },
            method: 'POST',
            data: data,
            dataType: 'json', // Specify the expected data type of the response
            success: function (response) {
                console.log(response);
                // Handle success response
                toastr.success(response.success);
                setTimeout(function () {
                    window.location = response.redirect_url;
                }, 2000);
            },
            error: function (xhr, status, error) {
                // Handle error response
                var errors = xhr.responseJSON.errors;
                // Check if there are any validation errors
                if (errors) {
                    // Loop through each error and display it using toastr
                    $.each(errors, function (key, value) {
                        toastr.error(value);
                    });
                }
                else {
                    if (xhr.responseJSON.error) {
                        console.log(xhr.responseJSON.error);
                        toastr.error(xhr.responseJSON.error);
                    }
                    else {
                        // If there are no validation errors, display a generic error message
                        toastr.error('An error occurred. Please try again.');
                    }
                }
            }
        });
    }
});
$('#upgrade_subscription_form').on("submit", function (event) {
    event.preventDefault();
    if (validateSubscriptionForm()) {
        var selectedPlan = plans.find(plan => plan.id == $('#plan_id').val());
        console.log(selectedPlan);
        var plan_features = {
            max_clients: selectedPlan.max_clients,
            max_projects: selectedPlan.max_projects,
            max_team_members: selectedPlan.max_team_members,
            max_workspaces: selectedPlan.max_worksapces,
            modules: JSON.parse(selectedPlan.modules),
        }

        var chargingPriceText = $('#charging_price').text();
        var chargingPriceParts = chargingPriceText.split(currency_symbol);
        var chargingPrice = chargingPriceParts[1].trim();
        var data = {
            plan_id: $('#plan_id').val(),
            user_id: $('#user_id').val(),
            tenure: $('#tenure').val(),
            payment_method: $('#payment_method').val(),
            charging_price: chargingPrice,
            charging_currency: currency_symbol,
            start_date: $('#subscription_start_date').val(),
            end_date: $('#subscription_end_date').val(),
            features: JSON.stringify(plan_features),
            transaction_id: $('#transaction_id').val(),
        }
        $.ajax({
            url: $(this).attr('action'),
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').attr('value') // Replace with your method of getting the CSRF token
            },
            method: 'POST',
            data: data,
            dataType: 'json', // Specify the expected data type of the response
            success: function (response) {
                console.log(response);
                // Handle success response
                toastr.success(response.success);
                setTimeout(function () {
                    window.location = response.redirect_url;
                }, 2000);
            },
            error: function (xhr, status, error) {
                // Handle error response
                var errors = xhr.responseJSON.errors;
                // Check if there are any validation errors
                if (errors) {
                    // Loop through each error and display it using toastr
                    $.each(errors, function (key, value) {
                        toastr.error(value);
                    });
                }
                else {
                    if (xhr.responseJSON.error) {
                        console.log(xhr.responseJSON.error);
                        toastr.error(xhr.responseJSON.error);
                    }
                    else {
                        // If there are no validation errors, display a generic error message
                        toastr.error('An error occurred. Please try again.');
                    }
                }
            }
        });
    }
})

