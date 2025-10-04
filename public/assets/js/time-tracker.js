"use strict";

let timer; // Variable to store the setTimeout reference

// Event listener for changes in the time tracker message input
$("#time_tracker_message").on("change keyup paste", function () {
    localStorage.setItem("msg", $(this).val());
});

// Event listener for changes in project selection
$("#time_tracker_project").on("change", function () {
    let projectSelected = $(this).val();
    localStorage.setItem("project_id", projectSelected);

    if (projectSelected) {
        $("#task-section").show(); // Show task section if a project is selected
    } else {
        $("#task-section").hide(); // Hide task section if no project is selected
    }

    $("#time_tracker_task").val(null).trigger("change"); // Reset task selection
});

// Event listener for changes in task selection
$("#time_tracker_task").on("change", function () {
    var taskId = $(this).val();
    localStorage.setItem("task_id", taskId);
});

// Function to fetch tasks for a project
function fetchTasks(projectId, urlPrefix) {
    $.ajax({
        url: "/" + urlPrefix + "/time-tracker/get-tasks",
        type: "POST",
        dataType: "json",
        data: {
            project_id: projectId,
        },
        headers: {
            "X-CSRF-TOKEN": $('input[name="_token"]').val(),
        },
        beforeSend: function () {
            $("#time_tracker_task").html(
                '<option value="">Loading tasks...</option>'
            );
            $("#task-section").show();
        },
        success: function (result) {
            var taskSelect = $("#time_tracker_task");
            taskSelect
                .empty()
                .append('<option value="">Select a Task</option>');

            if (result.error === false && result.tasks?.length > 0) {
                // Populate tasks dynamically
                $.each(result.tasks, function (index, task) {
                    taskSelect.append(
                        $("<option>", {
                            value: task.id,
                            text: task.title,
                        })
                    );
                });
                $("#task-section").show();
            } else {
                $("#task-section").hide();
            }
        },
        error: function () {
            $("#task-section").hide();
            toastr.error("Failed to load tasks");
        },
    });
}

// Function to open timer section
function open_timer_section() {
    // Check if timer is running based on localStorage
    const isTimerRunning = !is_timer_stopped || 
                          (localStorage.getItem("Seconds") > "00" && 
                           localStorage.getItem("Pause") !== "0");

    if (is_timer_stopped) {
        $("#pause").attr("disabled", true);
        $("#end").attr("disabled", true);
    }

    $("#time_tracker_message").val(localStorage.getItem("msg"));

    // Check if there's a saved project
    if (localStorage.getItem("project_id")) {
        var projectId = localStorage.getItem("project_id");
        var taskId = localStorage.getItem("task_id");
        var urlPrefix = window.location.pathname.split("/")[1];
        
        // Set the project value
        $("#time_tracker_project").val(projectId);
        
        // Disable dropdowns if timer is running
        if (isTimerRunning) {
            $("#time_tracker_project").prop("disabled", true);
            $("#time_tracker_task").prop("disabled", true);
        } else {
            $("#time_tracker_project").prop("disabled", false);
            $("#time_tracker_task").prop("disabled", false);
        }
        
        // Always show task section when a project is selected
        $("#task-section").show();
        
        // Load tasks for the selected project
        $.ajax({
            url: "/" + urlPrefix + "/time-tracker/get-tasks",
            type: "POST",
            dataType: "json",
            data: {
                project_id: projectId,
            },
            headers: {
                "X-CSRF-TOKEN": $('input[name="_token"]').val(),
            },
            beforeSend: function () {
                $("#time_tracker_task").html(
                    '<option value="">Loading tasks...</option>'
                );
            },
            success: function (result) {
                var taskSelect = $("#time_tracker_task");
                taskSelect.empty().append('<option value="">Select a Task</option>');

                if (result.error === false && result.tasks && result.tasks.length > 0) {
                    // Populate tasks
                    $.each(result.tasks, function (index, task) {
                        taskSelect.append(
                            $("<option>", {
                                value: task.id,
                                text: task.title,
                            })
                        );
                    });
                    
                    // Set the previously selected task after tasks are loaded
                    if (taskId) {
                        taskSelect.val(taskId);
                        
                        // If using Select2, refresh the Select2 instance
                        if ($.fn.select2 && taskSelect.hasClass("select2-hidden-accessible")) {
                            taskSelect.trigger('change');
                        }
                    }
                }
            },
            error: function () {
                $("#task-section").show(); // Still show the section even on error
                toastr.error("Failed to load tasks");
            },
        });
    } else {
        // No project selected, hide task section
        $("#task-section").hide();
        $("#time_tracker_project").prop("disabled", false);
        $("#time_tracker_task").prop("disabled", false);
    }

    if (!isTimerRunning) {
        timerCycle();
    }
}

// Add this code in your navigation handling section:
if (localStorage.getItem("project_id")) {
    var projectId = localStorage.getItem("project_id");
    var taskId = localStorage.getItem("task_id");
    var urlPrefix = window.location.pathname.split("/")[1];
    
    // Set project value without triggering change
    $("#time_tracker_project").val(projectId);
    
    // Show task section
    $("#task-section").show();
    
    // Manually load tasks and set the task value
    $.ajax({
        url: "/" + urlPrefix + "/time-tracker/get-tasks",
        type: "POST",
        dataType: "json",
        data: {
            project_id: projectId,
        },
        headers: {
            "X-CSRF-TOKEN": $('input[name="_token"]').val(),
        },
        success: function (result) {
            var taskSelect = $("#time_tracker_task");
            taskSelect.empty().append('<option value="">Select a Task</option>');

            if (result.error === false && result.tasks && result.tasks.length > 0) {
                // Populate tasks
                $.each(result.tasks, function (index, task) {
                    taskSelect.append(
                        $("<option>", {
                            value: task.id,
                            text: task.title,
                        })
                    );
                });
                
                // Set the task value after tasks are loaded
                if (taskId) {
                    taskSelect.val(taskId);
                    
                    // If using Select2, refresh the Select2 instance
                    if ($.fn.select2 && taskSelect.hasClass("select2-hidden-accessible")) {
                        taskSelect.trigger('change');
                    }
                }
            }
        },
    });
}
// Variables to store hours, minutes, and seconds
var hr = parseInt($("#hour").length > 0 ? $("#hour").val() : 0);
var min = parseInt($("#minute").length > 0 ? $("#minute").val() : 0);
var sec = parseInt($("#second").length > 0 ? $("#second").val() : 0);
var is_timer_stopped = true;
let recorded_id = "00";

// Check if the timer was paused and has a recorded ID
if (
    parseInt(localStorage.getItem("Pause")) == 1 &&
    parseInt(localStorage.getItem("recorded_id")) > 0
) {
    is_timer_stopped = true;
    time_tracker_img();
}

// Function to start the timer
function startTimer() {
    var urlPrefix = window.location.pathname.split("/")[1];
    var projectId = $("#time_tracker_project").val();
    var taskId = $("#time_tracker_task").val();

    if (is_timer_stopped == true) {
        $.ajax({
            url: "/" + urlPrefix + "/time-tracker/store",
            type: "POST",
            dataType: "json",
            data: {
                message: $("#time_tracker_message").val(),
                project_id: projectId,
                task_id: taskId,
            },
            headers: {
                "X-CSRF-TOKEN": $('input[name="_token"]').val(),
            },
            beforeSend: function () {
                $("#start").attr("disabled", true);
                time_tracker_img();
            },
            success: function (result) {
                if (result["error"] == false) {
                    recorded_id = result["id"];
                    localStorage.setItem("recorded_id", recorded_id);
                    localStorage.setItem(
                        "msg",
                        $("#time_tracker_message").val()
                    );

                    // Disable project and task dropdowns when timer starts
                    $("#time_tracker_project").prop("disabled", true);
                    $("#time_tracker_task").prop("disabled", true);

                    if (localStorage.getItem("Seconds") > "00") {
                        is_timer_stopped = false;
                        timerCycle();
                    } else {
                        hr = "00";
                        sec = "00";
                        min = "00";
                        is_timer_stopped = false;
                        timerCycle();
                        time_tracker_img();
                    }
                    $("#start").attr("disabled", true);
                    time_tracker_img();
                    $("#pause").attr("disabled", false);
                    $("#end").attr("disabled", false);
                    if (
                        localStorage.getItem("Pause") == "0" ||
                        localStorage.getItem("Pause") == "00"
                    ) {
                        localStorage.setItem("Pause", "1");
                    }

                    toastr.success(result["message"]);
                } else {
                    toastr.error(result["message"]);
                }
            },
        });
    }
}


// Function to pause the timer
function pauseTimer() {
    var urlPrefix = window.location.pathname.split("/")[1];
    var projectId = $("#time_tracker_project").val();
    var taskId = $("#time_tracker_task").val();

    if (is_timer_stopped == false && $("#second").val() > "00") {
        is_timer_stopped = true;
        window.localStorage.setItem("Pause", "0");

        $("#start").attr("disabled", false);
        $("#pause").attr("disabled", true);
        $("#end").attr("disabled", true);
        clearTimeout(timer) // Clear the interval to avoid multiple executions
        time_tracker_img();

        var r_id = localStorage.getItem("recorded_id");
        var msg = $("#time_tracker_message").val();

        // Preserve project, task, and message in localStorage for restart
        localStorage.setItem("project_id", projectId);
        localStorage.setItem("task_id", taskId);
        localStorage.setItem("msg", msg);

        var input_body = {
            record_id: r_id,
            message: msg,
            project_id: projectId,
            task_id: taskId,
        };
        $.ajax({
            type: "POST",
            data: input_body,
            headers: {
                "X-CSRF-TOKEN": $('input[name="_token"]').val(),
            },
            url: "/" + urlPrefix + "/time-tracker/update",
            dataType: "json",
            success: function (result) {
                if (result["error"] == false) {
                    toastr.success("Timer has been paused successfully.");

                    // Optionally re-enable dropdowns when paused
                    $("#time_tracker_project").prop("disabled", false);
                    $("#time_tracker_task").prop("disabled", false);

                    if (
                        typeof total_records !== "undefined" &&
                        total_records == 0
                    ) {
                        location.reload();
                    } else {
                        $("#timesheet_table").bootstrapTable("refresh");
                    }
                } else {
                    toastr.error(result["message"]);
                }
            },
        });
    } else {
        toastr.warning("Please make sure the timer has started.");
    }
}


// Modify the page reload/navigation handling
if (
    window.performance.getEntriesByType("navigation")[0]["type"] == "reload" ||
    window.performance.getEntriesByType("navigation")[0]["type"] == "navigate"
) {
    hr = localStorage.getItem("Hour");
    sec = localStorage.getItem("Seconds");
    min = localStorage.getItem("Minutes");

    // Check if timer is paused or running
    let isPaused = localStorage.getItem("Pause");

    $("#hour").val("00");
    $("#minute").val("00");
    $("#second").val("00");

    if (hr) {
        $("#hour").val(hr);
    }
    if (min) {
        $("#minute").val(min);
    }
    if (sec) {
        $("#second").val(sec);
    }

    // Restore project and task selection if timer was running
    // In the page reload/navigation handling section
// if (localStorage.getItem("project_id")) {
//     $("#time_tracker_project").val(localStorage.getItem("project_id"));
//     $("#task-section").show(); // Explicitly show task section
    
//     // Then trigger change to load tasks
//     $("#time_tracker_project").trigger("change");
// }
    if (localStorage.getItem("task_id")) {
        $("#time_tracker_task").val(localStorage.getItem("task_id"));
    }

    // Restore message
    if (localStorage.getItem("msg")) {
        $("#time_tracker_message").val(localStorage.getItem("msg"));
    }

    // Continue timer if it was running and not explicitly paused
    if (localStorage.getItem("Seconds") > "00" && isPaused !== "0") {
        is_timer_stopped = false;
        clearTimeout(timer);
        timerCycle();
    }
}

// Function for the timer cycle
function timerCycle() {
    if (is_timer_stopped == false) {
        // Clear any existing timer before starting a new one
        if (timer) {
            clearTimeout(timer);
        }

        sec = parseInt(sec);
        min = parseInt(min);
        hr = parseInt(hr);
        sec = sec + 1;

        if (sec == 60) {
            min = min + 1;
            sec = 0;
        }
        if (min == 60) {
            hr = hr + 1;
            min = 0;
            sec = 0;
        }

        if (sec < 10) {
            sec = "0" + sec;
        } else {
            sec = "" + sec;
        }
        if (min < 10) {
            min = "0" + min;
        } else {
            min = "" + min;
        }
        if (hr < 10) {
            hr = "0" + hr;
        } else {
            hr = "" + hr;
        }
        window.localStorage.setItem("Hour", hr);
        window.localStorage.setItem("Minutes", min);
        window.localStorage.setItem("Seconds", sec);
        $("#hour").val(hr);
        $("#minute").val(min);
        $("#second").val(sec);
        timer = setTimeout(timerCycle, 1000); // Assign the new timeout to the timer variable
    }
}

// Function to stop the timer
function stopTimer() {
    var urlPrefix = window.location.pathname.split("/")[1];
    var taskId = $("#time_tracker_task").val();

    if (is_timer_stopped == false && $("#second").val() > "00") {
        $("#stopTimerModal").modal("show");
        $("#stopTimerModal").off("click", "#confirmStop");
        $("#stopTimerModal").on("click", "#confirmStop", function (e) {
            $("#confirmStop").html(label_please_wait).attr("disabled", true);
            is_timer_stopped = true;
            clearTimeout(timer);
            // Store recorded_id before removing localStorage items
            var r_id = localStorage.getItem("recorded_id");
            console.log("Recorded ID before stop:", r_id);

            // Clear all timer-related localStorage items
            localStorage.removeItem("Minutes");
            localStorage.removeItem("Seconds");
            localStorage.removeItem("Hour");
            localStorage.removeItem("msg");
            localStorage.removeItem("project_id");
            localStorage.removeItem("task_id");
            localStorage.removeItem("recorded_id");
            localStorage.removeItem("Pause");

            var msg = $("#time_tracker_message").val();
            $("#start").attr("disabled", false);
            $("#pause").attr("disabled", true);
            $("#end").attr("disabled", true);

            $("#hour").val("00");
            $("#minute").val("00");
            $("#second").val("00");
            $("#time_tracker_message").val("");
            $("#time_tracker_project").val(null).trigger("change");
            $("#time_tracker_task").val(null).trigger("change");

            time_tracker_img();
            clearInterval(timer); // Clear the interval before stopping the timer
            var input_body = {
                record_id: r_id, // Use the stored recorded_id
                message: msg,
                task_id: taskId,
            };

            $.ajax({
                type: "POST",
                data: input_body,
                headers: {
                    "X-CSRF-TOKEN": $('input[name="_token"]').val(),
                },
                url: "/" + urlPrefix + "/time-tracker/update",
                dataType: "json",
                success: function (result) {
                    $("#confirmStop").html(label_yes).attr("disabled", false);
                    if (result["error"] == false) {
                        toastr.success(result["message"]);
                        if (
                            typeof total_records !== "undefined" &&
                            total_records == 0
                        ) {
                            location.reload();
                        } else {
                            $("#stopTimerModal").modal("hide");
                            $("#timerModal").modal("hide");
                            $("#timesheet_table").bootstrapTable("refresh");
                        }
                    } else {
                        toastr.error(result["message"]);
                        $("#stopTimerModal").modal("hide");
                    }
                },
            });
        });
    } else {
        toastr.warning("Please make sure the timer has started.");
    }
}

// Function to change the timer image
function time_tracker_img() {
    if (!is_timer_stopped) {
        $("#timer-image").attr("src", "/storage/94150-clock.gif");
    } else {
        $("#timer-image").attr("src", "/storage/94150-clock.png");
    }
}

// Query parameters for timesheet table
function time_tracker_query_params(p) {
    return {
        user_id: $("#timesheet_user_filter").val(),
        start_date_from: $("#timesheet_start_date_from").val(),
        start_date_to: $("#timesheet_start_date_to").val(),
        end_date_from: $("#timesheet_end_date_from").val(),
        end_date_to: $("#timesheet_end_date_to").val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search,
    };
}

// Icons for table
window.icons = {
    refresh: "bx-refresh",
    toggleOff: "bx-toggle-left",
    toggleOn: "bx-toggle-right",
};

// Loading template for table
function loadingTemplate(message) {
    return '<i class="bx bx-loader-alt bx-spin bx-flip-vertical" ></i>';
}

// Formatter for user column in timesheet table
function timeSheetUserFormatter(value, row, index) {
    return (
        '<div class="d-flex">' +
        row.photo +
        '<div class="mx-2 mt-2"><h6 class="mb-1">' +
        row.user_name +
        "</div>"
    );
}

// Formatter for actions column in timesheet table
function timeSheetActionsFormatter(value, row, index) {
    return [
        "<button title=" +
            label_delete +
            ' type="button" class="btn delete" data-id=' +
            row.id +
            ' data-type="time-tracker" data-table="timesheet_table">' +
            '<i class="bx bx-trash text-danger mx-1"></i>' +
            "</button>",
    ];
}

// Document ready function
$(document).ready(function () {
    // Hide task section initially
    $("#task-section").hide();

    // Initialize project selection
    initSelect2Ajax(
        "#time_tracker_project",
        "/master-panel/tasks/search-projects",
        // label_filter_project
    );

    // Initialize task selection with dynamic project ID
    initSelect2Ajax(
        "#time_tracker_task",
        "/master-panel/tasks/search-tasks",
        "Search for tasks",
        true,
        0,
        true,
        () => {
            return { project_id: $("#time_tracker_project").val() || "" };
        }
    );
});

// User filter change event
$("#timesheet_user_filter").on("change", function (e) {
    e.preventDefault();
    $("#timesheet_table").bootstrapTable("refresh");
});
