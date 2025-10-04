$(document).ready(function () {
    var $iconOpen = $(this).find(".collapse-open").hide();
    $(".accordion-button").click(function () {
        var $iconClose = $(this).find(".collapse-close");
        var $iconOpen = $(this).find(".collapse-open");
        console.log($iconOpen);
        console.log($iconClose);
        if ($(this).attr("aria-expanded") === "true") {
            $iconClose.hide();
            $iconOpen.show();
        } else {
            $iconClose.show();
            $iconOpen.hide();
        }
    });
});
$(document).ready(function () {
    $("#contactUsSubmit").on("click", function () {
        var formData = $("#contact_us_form").serialize();
        // Show loading indicator
        // $('#loading-overlay').fadeIn();
        // Send AJAX request
        $.ajax({
            url: $("#contact_us_form").attr("action"),
            type: "POST",
            data: formData,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                // Hide loading indicator
                // $('#loading-overlay').fadeOut();
                // $('#loading-overlay').fadeOut();
                // Handle success response
                toastr.success(response.message);
                // Clear form fields if needed
                $("#contact_us_form")[0].reset();
            },
            error: function (xhr, status, error) {
                // Hide loading indicator
                // $('#loading-overlay').fadeOut();
                // Handle error response
                var errors = xhr.responseJSON.errors;
                $.each(errors, function (key, value) {
                    toastr.error(value);
                });
            },
        });
    });
});

$(document).ready(function () {
    $("#eyeicon").on("click", function () {
        const passwordField = $("#password");
        const eyeIcon = $(this).find("i"); // Find the <i> inside the #eyeicon span

        // Toggle password field type
        if (passwordField.attr("type") === "password") {
            passwordField.attr("type", "text");
        } else {
            passwordField.attr("type", "password");
        }

        // Toggle eye icon class
        eyeIcon.toggleClass("fa-eye fa-eye-slash");
    });
});

// $(document).ready(function () {
//     $('#loginBtn').on('click', function (e) {
//         alert('login clicked');
//         return;
//         e.preventDefault();
//         var formData = $('#formAuthentication').serialize();

//         $.ajax({
//             url: $('#formAuthentication').attr('action'),
//             type: 'POST',
//             data: formData,
//             headers: {
//                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//             },
//             success: function (response) {
//                 if (response.error) {
//                     console.log(response);

//                     toastr.error(response.message);

//                 }
//                 else {

//                     toastr.success(response.message);
//                     window.location.href = response.redirect_url;

//                 }

//             },
//             error: function (xhr, status, error) {
//                 // Handle error response

//                 var errors = xhr.responseJSON.errors;
//                 console.log(errors);
//                 // Check if there are any validation errors
//                 if (errors) {
//                     // Loop through each error and display it using toastr
//                     $.each(errors, function (key, value) {
//                         toastr.error(value);
//                     });
//                 } else {
//                     if (xhr.responseJSON.error) {

//                         console.log(xhr.responseJSON);
//                         $.each(xhr.responseJSON.message, function (key, value) {

//                             toastr.error(value);
//                         })

//                     } else {
//                         // If there are no validation errors, display a generic error message
//                         toastr.error('An error occurred. Please try again.');
//                     }
//                 }

//             }

//         });
//     });
// });
$(document).ready(function () {
    $("#registerCustomer").on("click", function (e) {
        e.preventDefault(); // Prevent the default form submission

        var $submitBtn = $(this); // Cache the submit button reference
        var originalText = $submitBtn.text(); // Store the original button text

        $submitBtn.text(label_please_wait); // Change button text
        $submitBtn.prop("disabled", true); // Disable the button

        var formData = $("#formRegister").serialize(); // Serialize form data

        $.ajax({
            url: $("#formRegister").attr("action"),
            type: "POST",
            data: formData,
            dataType: "json",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                toastr.success(response.message);
                setTimeout(function () {
                    window.location = response.redirect_url; // Redirect after 2 seconds
                }, 2000);
            },
            error: function (xhr) {
                // Check if there are any validation errors
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function (key, value) {
                        toastr.error(value); // Display each validation error
                    });
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    // Handle any other error messages
                    $.each(xhr.responseJSON.message, function (key, value) {
                        toastr.error(value); // Display each error message
                    });
                } else {
                    // Generic error message
                    toastr.error("An error occurred. Please try again.");
                }
            },
            complete: function () {
                // Restore button text and enable it after success or error
                $submitBtn.text(originalText);
                $submitBtn.prop("disabled", false);
            },
        });
    });
});

// $(document).on('click', '.superadmin-login', function (e) {
//     e.preventDefault();
//     $('#email').val('superadmin@gmail.com');
//     $('#password').val('12345678');
// });
// $(document).on('click', '.admin-login', function (e) {
//     e.preventDefault();
//     $('#email').val('admin@gmail.com');
//     $('#password').val('12345678');
// });
// $(document).on('click', '.member-login', function (e) {
//     e.preventDefault();
//     $('#email').val('teammember@gmail.com');
//     $('#password').val('12345678');
// });
// $(document).on('click', '.client-login', function (e) {
//     e.preventDefault();
//     $('#email').val('client@gmail.com');
//     $('#password').val('12345678');
// });

if (document.getElementById("state1")) {
    const countUp = new CountUp(
        "state1",
        document.getElementById("state1").getAttribute("countTo")
    );
    if (!countUp.error) {
        countUp.start();
    } else {
        console.error(countUp.error);
    }
}
if (document.getElementById("state2")) {
    const countUp1 = new CountUp(
        "state2",
        document.getElementById("state2").getAttribute("countTo")
    );
    if (!countUp1.error) {
        countUp1.start();
    } else {
        console.error(countUp1.error);
    }
}
if (document.getElementById("state3")) {
    const countUp2 = new CountUp(
        "state3",
        document.getElementById("state3").getAttribute("countTo")
    );
    if (!countUp2.error) {
        countUp2.start();
    } else {
        console.error(countUp2.error);
    }
}

if (document.querySelector(".datepicker-1")) {
    flatpickr(".datepicker-1", {}); // flatpickr
}

if (document.querySelector(".datepicker-2")) {
    flatpickr(".datepicker-2", {}); // flatpickr
}
if (document.getElementById("typed")) {
    var typed = new Typed("#typed", {
        stringsElement: "#typed-strings",
        typeSpeed: 70,
        backSpeed: 50,
        backDelay: 200,
        startDelay: 500,
        loop: true,
    });
}
// Bootstrap's Carousel instance required
document.addEventListener("DOMContentLoaded", function () {
    var carouselEl = document.getElementById("featuresCarousel");
    var progressBar = document.getElementById("carouselProgressBar");
    var carousel = bootstrap.Carousel.getOrCreateInstance(carouselEl);
    var totalSlides = carouselEl.querySelectorAll(".carousel-item").length;

    // Progress bar update function
    function updateProgressBar() {
        var activeIndex = [
            ...carouselEl.querySelectorAll(".carousel-item"),
        ].findIndex((item) => item.classList.contains("active"));
        var progress = ((activeIndex + 1) / totalSlides) * 100;
        progressBar.style.width = progress + "%";
    }

    carouselEl.addEventListener("slid.bs.carousel", updateProgressBar);
    updateProgressBar();

    // Make image/text area clickable/swipeable
    carouselEl
        .querySelectorAll(
            ".carousel-content, .carousel-image img, .carousel-text"
        )
        .forEach(function (el) {
            el.addEventListener("click", function () {
                carousel.next();
            });
        });

    // Touch swipe support for carousel-content area (for mobile)
    let startX = null;

    carouselEl
        .querySelectorAll(".carousel-content")
        .forEach(function (content) {
            content.addEventListener(
                "touchstart",
                function (e) {
                    startX = e.touches[0].clientX;
                },
                { passive: true }
            );

            content.addEventListener(
                "touchend",
                function (e) {
                    if (startX === null) return;
                    let endX = e.changedTouches[0].clientX;
                    let diff = endX - startX;
                    if (Math.abs(diff) > 40) {
                        if (diff < 0) {
                            carousel.next();
                        } else {
                            carousel.prev();
                        }
                    }
                    startX = null;
                },
                { passive: true }
            );
        });
});

// Adjust if your carousel has a different id
document.addEventListener("DOMContentLoaded", function () {
    var carouselEl = document.getElementById("featureMainCarousel");
    var progressBarContainer = document.getElementById("carouselDashProgress");
    var carousel = bootstrap.Carousel.getOrCreateInstance(carouselEl);
    var totalSlides = carouselEl.querySelectorAll(".carousel-item").length;

    // Build segments
    progressBarContainer.innerHTML = "";
    for (let i = 0; i < totalSlides; i++) {
        let segment = document.createElement("div");
        segment.className = "progress-segment";
        progressBarContainer.appendChild(segment);
    }
    var segments = progressBarContainer.querySelectorAll(".progress-segment");

    // Update segments on slide
    function updateProgressBar() {
        var activeIndex = [
            ...carouselEl.querySelectorAll(".carousel-item"),
        ].findIndex((item) => item.classList.contains("active"));
        segments.forEach((seg, i) =>
            seg.classList.toggle("active", i <= activeIndex)
        );
    }

    carouselEl.addEventListener("slid.bs.carousel", updateProgressBar);
    updateProgressBar();

    // (Your swipe/click code remains unchanged)
});

// Settings Drawer JavaScript
$(document).ready(function () {
    const $openBtn = $('#templateCustomizerOpenBtn');
    const $closeBtn = $('#templateCustomizerCloseBtn');
    const $customizer = $('#templateCustomizer');
    const $overlay = $('#customizerOverlay');

    function openCustomizer() {
        $customizer.addClass('show');
        $overlay.addClass('show');
        $openBtn.addClass('hide');
        $('body').css('overflow', 'hidden');
    }

    function closeCustomizer() {
        $customizer.removeClass('show');
        $overlay.removeClass('show');
        $openBtn.removeClass('hide');
        $('body').css('overflow', '');
    }

    $openBtn.on('click', openCustomizer);
    $closeBtn.on('click', closeCustomizer);
    $overlay.on('click', closeCustomizer);

    $(document).on('click', '.switch-theme-btn', function (e) {
        e.preventDefault();

        const selectedTheme = $(this).data('theme');
        const $clickedOption = $(this);

        if ($clickedOption.hasClass('active')) {
            return;
        }

        $('.theme-option').removeClass('active');
        $clickedOption.addClass('active');

        $.ajax({
            url: window.themeSwitchRoute, 
            type: 'POST',
            data: {
                theme: selectedTheme,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    $clickedOption.removeClass('active');
                    alert('Failed to switch theme');
                }
            },
            error: function () {
                $clickedOption.removeClass('active');
                alert('Failed to switch theme');
            }
        });
    });
});


// about us curousel
document.addEventListener("DOMContentLoaded", function () {
    var carouselEl = document.getElementById("featureMainCarousel");
    var progressBarContainer = document.getElementById("carouselDashProgress");
    var carousel = bootstrap.Carousel.getOrCreateInstance(carouselEl);
    var totalSlides = carouselEl.querySelectorAll(".carousel-item").length;
    var carouselInner = carouselEl.querySelector(".carousel-inner");

    // Build segments (your existing code)
    progressBarContainer.innerHTML = "";
    for (let i = 0; i < totalSlides; i++) {
        let segment = document.createElement("div");
        segment.className = "progress-segment";
        progressBarContainer.appendChild(segment);
    }
    var segments = progressBarContainer.querySelectorAll(".progress-segment");

    // Update segments on slide (your existing code)
    function updateProgressBar() {
        var activeIndex = [
            ...carouselEl.querySelectorAll(".carousel-item"),
        ].findIndex((item) => item.classList.contains("active"));
        segments.forEach((seg, i) =>
            seg.classList.toggle("active", i <= activeIndex)
        );
    }

    carouselEl.addEventListener("slid.bs.carousel", updateProgressBar);
    updateProgressBar();

    // === SMOOTH DRAG/SWIPE FUNCTIONALITY ===
    let isDragging = false;
    let startX = 0;
    let startY = 0;
    let currentTranslate = 0;
    let dragThreshold = 50; // Minimum distance to trigger slide change
    let maxDragDistance = 200; // Maximum visual drag distance
    let isHorizontalDrag = false;
    let dragStarted = false;

    // Helper function to get X position from mouse or touch event
    function getPositionX(event) {
        return event.type.includes("mouse")
            ? event.clientX
            : event.touches[0].clientX;
    }

    // Helper function to get Y position from mouse or touch event
    function getPositionY(event) {
        return event.type.includes("mouse")
            ? event.clientY
            : event.touches[0].clientY;
    }

    // Start drag/swipe
    function dragStart(event) {
        startX = getPositionX(event);
        startY = getPositionY(event);
        currentTranslate = 0;
        isHorizontalDrag = false;
        dragStarted = true;

        // For mouse events, start dragging immediately
        if (event.type.includes("mouse")) {
            isDragging = true;
            carouselInner.classList.add("dragging");
            carousel.pause();
            event.preventDefault();
        }
    }

    // During drag/swipe
    function dragMove(event) {
        if (!dragStarted) return;

        const currentX = getPositionX(event);
        const currentY = getPositionY(event);
        const deltaX = currentX - startX;
        const deltaY = currentY - startY;

        // For touch events, determine if this is a horizontal or vertical gesture
        if (event.type.includes("touch") && !isDragging) {
            const absX = Math.abs(deltaX);
            const absY = Math.abs(deltaY);

            // If movement is more horizontal than vertical, start horizontal drag
            if (absX > absY && absX > 10) {
                isHorizontalDrag = true;
                isDragging = true;
                carouselInner.classList.add("dragging");
                carousel.pause();
            }
            // If movement is more vertical, allow normal scrolling
            else if (absY > absX && absY > 10) {
                // Reset and allow vertical scrolling
                dragStarted = false;
                return;
            }
            // If movement is too small, continue waiting
            else if (absX < 10 && absY < 10) {
                return;
            }
        }

        if (!isDragging) return;

        // Limit drag distance for smooth feel
        currentTranslate = Math.max(
            Math.min(deltaX, maxDragDistance),
            -maxDragDistance
        );

        // Apply smooth resistance at extremes
        if (Math.abs(deltaX) > maxDragDistance) {
            const resistance = 0.3;
            currentTranslate =
                deltaX > 0
                    ? maxDragDistance + (deltaX - maxDragDistance) * resistance
                    : -maxDragDistance +
                      (deltaX + maxDragDistance) * resistance;
        }

        // Apply visual translation
        carouselInner.style.transform = `translateX(${currentTranslate}px)`;

        // Only prevent default for horizontal drag
        if (isHorizontalDrag || event.type.includes("mouse")) {
            event.preventDefault();
        }
    }

    // End drag/swipe
    function dragEnd(event) {
        if (!isDragging) {
            dragStarted = false;
            return;
        }

        isDragging = false;
        dragStarted = false;
        isHorizontalDrag = false;
        carouselInner.classList.remove("dragging");

        // Reset visual translation
        carouselInner.style.transform = "";

        // Check if drag distance exceeds threshold
        if (Math.abs(currentTranslate) > dragThreshold) {
            if (currentTranslate > 0) {
                // Dragged right - go to previous slide
                carousel.prev();
            } else {
                // Dragged left - go to next slide
                carousel.next();
            }
        }

        // Reset values
        currentTranslate = 0;

        // Resume auto-play after a short delay
        setTimeout(() => {
            carousel.cycle();
        }, 300);
    }

    // Mouse events
    carouselInner.addEventListener("mousedown", dragStart);
    document.addEventListener("mousemove", dragMove);
    document.addEventListener("mouseup", dragEnd);

    // Touch events - now with passive: true to allow scrolling
    carouselInner.addEventListener("touchstart", dragStart, { passive: true });
    carouselInner.addEventListener("touchmove", dragMove, { passive: false });
    carouselInner.addEventListener("touchend", dragEnd, { passive: true });

    // Prevent image drag and context menu
    carouselInner.addEventListener("dragstart", (e) => e.preventDefault());
    carouselInner.addEventListener("contextmenu", (e) => e.preventDefault());

    // Handle mouse leave to end drag
    carouselInner.addEventListener("mouseleave", () => {
        if (isDragging) {
            dragEnd();
        }
    });

    // Clean up on window resize
    window.addEventListener("resize", () => {
        if (isDragging) {
            dragEnd();
        }
    });
});