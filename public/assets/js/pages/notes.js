$(document).ready(function () {
    let editor = null;
    let drawingContainer = document.getElementById("drawing-container");
    let drawingDataInput = document.getElementById("drawing_data");

    // Debugging: Ensure jQuery is available
    console.log("jQuery Loaded:", typeof $ !== "undefined");

    // Ensure #noteType exists before binding event
    if ($("#noteType").length === 0) {
        console.error("Error: #noteType not found!");
    } else {
        console.log("Binding change event to #noteType...");
    }

    // Toggle note type for new notes
    $(document).on("change", "#noteType", function () {
        console.log("Note type changed to:", $(this).val());

        let selectedType = $(this).val();
        if (selectedType === "text") {
            $("#text-note-section").removeClass("d-none");
            $("#drawing-note-section").addClass("d-none");
        } else if (selectedType === "drawing") {
            $("#text-note-section").addClass("d-none");
            $("#drawing-note-section").removeClass("d-none");
            initDrawing();
        } else {
            console.warn("Unexpected note type:", selectedType);
        }
    });

    // Handle form submission
    $(".form-submit-event").on("submit", function (e) {
        if ($("#noteType").val() === "drawing" && editor) {
            e.preventDefault();
            console.log("Capturing drawing data before submit...");

            try {
                let drawingData = editor.toSVG().outerHTML;
                console.log("Raw SVG:", drawingData);

                let encodedDrawingData = btoa(
                    unescape(encodeURIComponent(drawingData))
                );
                $("#drawing_data").val(encodedDrawingData);

                console.log("Encoded length:", encodedDrawingData.length);
                console.log(
                    "Drawing data set in form field:",
                    $("#drawing_data").val().substring(0, 50) + "..."
                );

                // Submit via AJAX instead of form submit to ensure data is included
                $.ajax({
                    url: $(this).attr("action"),
                    type: "POST",
                    data: $(this).serialize(),
                    dataType: "json",
                    success: function (response) {
                        if (!response.error) {
                            $("#create_note_modal").modal("hide");
                            location.reload();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function (xhr) {
                        console.error(
                            "Error submitting form:",
                            xhr.responseText
                        );
                        let errorMsg = "An error occurred.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        alert(errorMsg);
                    },
                });
            } catch (e) {
                console.error("Error processing drawing data:", e);
                alert("Error processing drawing. Please try again.");
            }

            return false;
        }
        // For text notes, let the form submit normally
    });

    function initDrawing() {
        if (!editor && drawingContainer) {
            try {
                console.log("Initializing drawing editor...");
                editor = new jsdraw.Editor(drawingContainer);
                editor.getRootElement().style.height = "260px";
                // editor.zoomLevel('45%');
                const toolbar = editor.addToolbar();
                $(
                    ".toolbar-internalWidgetId--selection-tool-widget, .toolbar-internalWidgetId--text-tool-widget, .toolbar-internalWidgetId--document-properties-widget, .pipetteButton ,.toolbar-internalWidgetId--insert-image-widget"
                ).hide();

                setTimeout(() => {
                    $(".toolbar--pen-tool-toggle-buttons").hide();
                }, 500);
            } catch (e) {
                console.error("Error initializing jsDraw:", e);
            }
        }
    }

    // Prevent form submission on toolbar zoom button clicks
    $(document).on("click", "#drawing-container button", function (e) {
        if ($(this).closest(".toolbar-zoomLevelEditor").length > 0) {
            e.preventDefault();
            e.stopPropagation();
        }
    });

    $("#create_note_modal").on("hidden.bs.modal", function () {
        $("#text-note-section").removeClass("d-none");
        $("#drawing-note-section").addClass("d-none");
    });

    $("#edit_note_modal").on("hidden.bs.modal", function () {
        $("#edit-text-note-section").removeClass("d-none");
        $("#edit-drawing-note-section").addClass("d-none");
    });
});
