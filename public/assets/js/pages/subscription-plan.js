function queryParams(p) {
    return {
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}
function SubscriptionHistory(p) {
    return {
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}
var paypalButtonsRendered = false;
$(document).ready(function () {
    $('a[data-toggle="content"]').on('click', function (e) {
        e.preventDefault();

        var target = $(this).attr('href');
        $('.content').removeClass('active');
        $(target).addClass('active');
    });
});

$(document).ready(function () {
    // Store the original card visibility state
    var originalCardState = {};

    // Save the original visibility of each card
    $('.card').each(function() {
        var cardId = $(this).data('plan-id');
        originalCardState[cardId] = {
            element: $(this),
            visible: true,
            parentColumn: $(this).parent()
        };
    });

    // Function to rearrange cards when some are hidden
    function rearrangeCards() {
        var $row = $('.row.gy-3.px-lg-5.mx-0');
        var visibleCards = $row.find('.card:visible').parent();

        if (visibleCards.length === 0) return;

        // Reset all columns to their original class
        $('.card').parent().attr('class', 'col-lg-3 mb-md-0 mb-4 mt-4');

        // Always use col-lg-3 for consistency with 4-card layout
        visibleCards.attr('class', 'col-lg-3 mb-md-0 mb-4 mt-4');
    }

    // Initial execution
    rearrangeCards();

    // Price toggle event handler
    $('input[name="priceToggle"]').change(function () {
        var selectedTenure = $(this).attr('id');
        $('.monthly-price, .yearly-price, .lifetime-price').addClass('d-none');
        $('.' + selectedTenure + '-price').removeClass('d-none');

        // First, reset all cards to their original visible state
        Object.keys(originalCardState).forEach(function(cardId) {
            originalCardState[cardId].parentColumn.show();
        });

        // Then, hide cards that don't apply to the selected tenure
        $('.card').each(function () {
            var $card = $(this);
            var planType = $card.data('plan-type');
            var yearlyPrice = parseFloat($card.data('yearly-price'));
            var yearlyDiscountedPrice = parseFloat($card.data('yearly-discounted-price'));
            var lifetimePrice = parseFloat($card.data('lifetime-price'));
            var lifetimeDiscountedPrice = parseFloat($card.data('lifetime-discounted-price'));

            if (planType === 'paid') {
                if (selectedTenure === 'yearly' && yearlyPrice === 0 && yearlyDiscountedPrice === 0) {
                    $card.parent().hide(); // Hide the parent column
                } else if (selectedTenure === 'lifetime' && lifetimePrice === 0 && lifetimeDiscountedPrice === 0) {
                    $card.parent().hide(); // Hide the parent column
                }
            }
        });

        // Rearrange cards after toggling visibility
        setTimeout(rearrangeCards, 50); // Small delay to ensure DOM is updated
    });
});

$(document).ready(function () {
    $('.checkout_btn').on('click', function () {
        var plan_id = $(this).data('planid'); // Use $(this) to reference the clicked button
        var tenure = $('input[name=priceToggle]:checked').attr('id');
        window.location = 'checkout/' + plan_id + '/' + tenure;
    });
});


$(document).ready(function () {
    // Get the total price
    var totalPrice = parseFloat($('input[name="total_price"]').val());
    var planName = $('input[name="plan_name"]').val();
    var planId = $('input[name="plan_id"]').val();
    var tenure = $('input[name="tenure"]').val();
    var currencySymbol = $('input[name="currency_symbol"]').val();

    if (totalPrice === 0) {
        // Show the order summary and proceed button
        $('#paymentMethod').text('Payment Method: Free Plan ');
        $('#finalPlan').text('Plan Name: ' + planName);
        $('#finalPrice').text('Total Price: ' + currencySymbol + totalPrice);
        $('#orderSummaryDiv').addClass('bg-label-primary');
        $('#proceedPaymentBtn').removeClass('d-none');
        $('#changePlanBtn').removeClass('d-none');
    }
});

$(document).ready(function () {
    // Add event listener to payment method radio buttons
    $('input[name="options"]').change(function () {
        // Get the selected payment method
        var paymentMethod = $('input[name="options"]:checked').val();
        var formattedPaymentMethod = paymentMethod
            .split('_')                      // Split the string by underscores
            .map(word => word.charAt(0).toUpperCase() + word.slice(1)) // Capitalize each word
            .join(' ');
        var planName = $('input[name="plan_name"]').val();
        var planId = $('input[name="plan_id"]').val();
        var tenure = $('input[name="tenure"]').val();
        var currencySymbol = $('input[name="currency_symbol"]').val();
        var totalPrice = $('input[name="total_price"]').val();



        // Update the order summary dynamically
        $('#paymentMethod').text('Payment Method: ' + formattedPaymentMethod);
        $('#finalPlan').text('Plan Name: ' + planName);
        $('#finalPrice').text('Total Price: ' + currencySymbol + totalPrice);
        $('#orderSummaryDiv').addClass('bg-label-primary');
        $('#proceedPaymentBtn').removeClass('d-none');
        $('#changePlanBtn').removeClass('d-none');


        switch (paymentMethod) {
            case (PaymentMethod = "stripe"): {
                $('#paypal-button-container').empty();
                paypalButtonsRendered = false;
                $('#stripe_checkout').show();
            } break;
            case (PaymentMethod = "paystack"): {
                $('#paypal-button-container').empty();
                paypalButtonsRendered = false;
                $('#stripe_checkout').hide();
            } break;
            case (PaymentMethod = "phonepe"): {
                $('#paypal-button-container').empty();
                paypalButtonsRendered = false;
                $('#stripe_checkout').hide();
            } break;
            case (PaymentMethod = "pay_pal"): {
                $('#stripe_checkout').hide();
            } break;
            case (PaymentMethod = "bank_transfer"): {
                $('#paypal-button-container').empty();
                paypalButtonsRendered = false;
                $('#stripe_checkout').hide();

            } break;
        };

    });
});


// Define the PayPal buttons rendering function
function renderPaypalButtons(finalPrice, transaction_id, successUrl) {
    paypal.Buttons({
        createOrder: function (data, actions) {
            // Set up the transaction details
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: finalPrice, // The amount to be charged
                    },
                    reference_id: transaction_id,
                }]
            });
        },
        onApprove: function (data, actions) {
            // Capture the funds when the customer approves the payment

            return actions.order.capture().then(function (details) {
                // Handle the successful payment

                toastr.success('Transaction completed by ' + details.payer.name.given_name);

                $.ajax({
                    url: successUrl, // Replace with your endpoint URL
                    method: 'POST', // or 'GET', depending on your requirements
                    data: details,
                    success: function (response) {
                        // Handle the successful AJAX response
                        window.location.href = response.redirectUrl;
                    },
                    error: function (xhr, status, error) {
                        // Handle the AJAX error
                        console.error('AJAX request failed:', error);
                    }
                });

            });
        },
        onCancel: function (data) {
            // Handle the cancellation of the payment
            toastr.error('Payment canceled by the user.');
            setTimeout(function () {
                location.reload();
            }, 2000);

        }
    }).render('#paypal-button-container');
}
function renderStripePopup(clientSecret, publicKey) {
    // Initialize Stripe.js with your publishable key
    var stripe = Stripe(publicKey);

    // Create the checkout instance
    stripe.initEmbeddedCheckout({
        clientSecret,
    }).then(function (checkout) {
        // Mount the checkout instance
        checkout.mount('#stripe_checkout');

        // Get the iframe element
        var iframe = document.querySelector('#stripe_checkout iframe');

        // Add event listener for iframe load event
        iframe.addEventListener('load', function () {
            // Get the contentWindow from the iframe
            var iframeWindow = iframe.contentWindow;

            // Add event listener for message event to detect checkout closure
            iframeWindow.addEventListener('message', function (event) {
                // Check if the event data indicates checkout closure
                if (event.data === 'embedded_checkout.closed') {
                    // Checkout closed
                    console.log('Checkout closed');
                    // Handle checkout closure
                }
            });
        });
    }).catch(function (error) {
        // Handle initialization errors
        console.error('Initialization error:', error);
    });
}
function initiatePaystackCheckout(response) {
    var handler = PaystackPop.setup({
        key: response.publicKey, // Replace with your Paystack public key
        email: response.email,
        amount: response.amount, // Amount in kobo
        currency: response.currency,
        ref: response.reference,
        metadata: JSON.parse(response.metadata),
        callback: function (response) {
            // Handle successful payment here
            console.log(response);
            if (response.status == "success" && response.message == "Approved") {

                toastr.success('Paystack Payment Success');
                setTimeout(function () {
                    window.location.href = response.redirecturl;
                }, 2000);

            }

            // Send data to callback URL for verification

        },
        onClose: function () {
            // Handle when the Paystack dialog is closed
            toastr.error('Paystack dialog closed');
        }
    });
    handler.openIframe();
}
function handleBankTransfer(response) {
    const bank_details = JSON.parse(response.bank_transfer_settings);
    const amount = response.finalPrice;
    const currency = response.currency;
    const transaction_id = response.transaction_id;
    console.log(bank_details);

    // Create modal for displaying bank details
    const modalHtml = `
            <div class="modal fade" id="bankTransferModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Bank Transfer Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                Please transfer ${currency}${amount} using the following bank details:
                            </div>
                            <div class="alert alert-primary">
                                ${bank_details.extra_notes}
                            </div>

                            <div class="bank-details">
                                <p><strong>Bank Name:</strong> ${bank_details.bank_name}</p>
                                <p><strong>Bank Code:</strong> ${bank_details.bank_code}</p>
                                <p><strong>Account Name:</strong> ${bank_details.account_name}</p>
                                <p><strong>Account Number:</strong> ${bank_details.account_number}</p>
                                <p><strong>Swift Code:</strong> ${bank_details.swift_code}</p>

                                <p><strong>Transaction Id Reference:</strong> ${transaction_id}</p>
                            </div>

                            <div class="alert alert-warning mt-3">
                                Important: Please include the transaction id  reference number in your transfer details.
                                Your subscription will be activated once the payment is verified.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="copyBankDetails()">Copy Details</button>
                        </div>
                    </div>
                </div>
            </div>`;

    // Append modal to body if it doesn't exist
    if (!document.getElementById('bankTransferModal')) {
        $('body').append(modalHtml);
    }

    // Show the modal
    $('#bankTransferModal').modal('show');

    // Store transaction details in localStorage for reference
    localStorage.setItem('bankTransfer_' + transaction_id, JSON.stringify({
        amount,
        currency,
        transaction_id,
        timestamp: new Date().toISOString()
    }));
    window.copyBankDetails = function () {
        const bankDetails = document.querySelector('.bank-details').innerText;
        navigator.clipboard.writeText(bankDetails).then(() => {
            toastr.success('Bank details copied to clipboard');
        }).catch(() => {
            toastr.error('Failed to copy bank details');
        });



    }
    toastr.success('Please complete the bank transfer using the provided details');
}
$(document).ready(function () {

    $('#paymentIntializeBtn').on("click", function () {
        // Get necessary data
        var finalPrice = parseFloat($('input[name="total_price"]').val());
        var paymentMethod = '';
        if (finalPrice > 0) {
            paymentMethod = $('input[name="options"]:checked').val();
        } else {
            paymentMethod = 'free_plan'; // Set payment method to 'free_plan' if price is 0
        }

        if (paymentMethod == 'pay_pal') {
            var paymentMethod = 'paypal';
        }

        // return;
        var planId = $('input[name="plan_id"]').val();
        var tenure = $('input[name="tenure"]').val();
        var user_id = $('input[name="user_id"]').val();
        var currency_symbol = $('input[name="currency_symbol"]').val();
        var url = $(this).data('url');


        // Ajax request
        $.ajax({
            url: url,
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').attr('value')
            },
            data: {
                payment_method: paymentMethod,
                plan_id: planId,
                tenure: tenure,
                user_id: user_id,
                currency_symbol: currency_symbol
            },
            success: function (response) {
                // Handle success response
                if (response) {
                    if (response.payment_method === 'free_plan') {
                        toastr.success(response.message);
                        var redirectUrl = response.redirect_url;
                        setTimeout(function () {
                            window.location.href = redirectUrl;
                        }, 2000);
                    }
                    if (response.payment_method === 'phonepe') {
                        toastr.success('Payment initiation successful.');

                        PhonePeCheckout.transact({
                            tokenUrl: response.redirectUrl, // this should be from the backend
                            type: 'IFRAME',
                            callback: function (status) {
                                if (status === 'CONCLUDED') {
                                    window.location.href = `/master-panel/subscription-plan/checkout/phone_pe-redirect?moid=${response.transaction_id}`;
                                } else {
                                    toastr.error('Payment was not completed.');
                                }

                            }
                        });
                    }
                    if (response.payment_method === 'paystack') {
                        initiatePaystackCheckout(response)
                    }
                    if (response.payment_method === 'stripe') {
                        renderStripePopup(response.client_secret, response.publicKey);

                    }
                    if (response.payment_method === 'paypal') {

                        if (!paypalButtonsRendered) {
                            renderPaypalButtons(response.finalPrice, response.transaction_id, response.success_url);
                            paypalButtonsRendered = true;
                        }
                    }
                    if (response.payment_method === 'bank_transfer') {
                        console.log(response);
                        handleBankTransfer(response);
                        setTimeout(function () {
                            window.location.href = response.redirect_url;
                        }, 5000);
                    }


                } else {
                    toastr.error('Empty response received.');
                }
            },
            error: function (xhr, status, error) {
                // Handle error response
                var errors = xhr.responseJSON.errors;
                if (errors) {
                    $.each(errors, function (key, value) {
                        toastr.error(value);
                    });
                } else {
                    if (xhr.responseJSON.error) {
                        console.log(xhr.responseJSON.error);
                        toastr.error(xhr.responseJSON.error);
                    } else {
                        toastr.error('An error occurred. Please try again.');
                    }
                }
            }
        });
    });
});
$(document).ready(function () {
    $('#uploadDocumentModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var subscriptionId = button.data('subscription-id'); // Extract info from data-* attributes

        var modal = $(this); // Get the modal
        modal.find('#subscription_id').val(subscriptionId); // Set the subscription ID in the hidden input field
    });
});
