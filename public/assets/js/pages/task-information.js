/**
 * Dashboard Analytics
 */
'use strict';

function queryParamsTaskMedia(p) {
    return {
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}
function queryParamsTaskTimeEntries(p) {

    return {
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}
function sumTotalDuration(data) {
    let totalMinutes = 0;

    // Get all rows currently displayed in the table
    const rows = $('#task-time-entries').bootstrapTable('getData');

    rows.forEach(row => {
        // Parse the duration string (e.g., "2 hours 30 minutes")
        const durationStr = row.total_duration;
        const hourMatch = durationStr.match(/(\d+)\s*hours?/);
        const minuteMatch = durationStr.match(/(\d+)\s*minutes?/);

        // Add hours to total (converting to minutes)
        if (hourMatch) {
            totalMinutes += parseInt(hourMatch[1]) * 60;
        }

        // Add minutes to total
        if (minuteMatch) {
            totalMinutes += parseInt(minuteMatch[1]);
        }
    });

    // Convert total minutes back to hours and minutes
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;

    return `Total: ${hours} hours ${minutes} minutes`;
}
function queryParams(p) {
    return {
        "user_id": $('#user_filter').val(),
        "client_id": $('#client_filter').val(),
        "activity": $('#activity_filter').val(),
        "type": 'task',
        "type_id": $('#type_id').val(),
        "date_from": $('#activity_log_between_date_from').val(),
        "date_to": $('#activity_log_between_date_to').val(),
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

function actionsFormatter(value, row, index) {
    return [
        '<button title=' + label_delete + ' type="button" class="btn delete" data-id=' + row.id + ' data-type="activity-log" data-table="activity_log_table">' +
        '<i class="bx bx-trash text-danger mx-1"></i>' +
        '</button>'
    ]
}




$('#activity_log_between_date').on('apply.daterangepicker', function (ev, picker) {
    var startDate = picker.startDate.format('YYYY-MM-DD');
    var endDate = picker.endDate.format('YYYY-MM-DD');

    $('#activity_log_between_date_from').val(startDate);
    $('#activity_log_between_date_to').val(endDate);

    $('#activity_log_table').bootstrapTable('refresh');
});

$('#activity_log_between_date').on('cancel.daterangepicker', function (ev, picker) {
    $('#activity_log_between_date_from').val('');
    $('#activity_log_between_date_to').val('');
    $('#activity_log_table').bootstrapTable('refresh');
    $('#activity_log_between_date').val('');
});


$('#user_filter,#client_filter,#activity_filter').on('change', function (e) {
    e.preventDefault();
    $('#activity_log_table').bootstrapTable('refresh');
});

$(document).ready(function () {
    // Constants and cache DOM elements
    const imageBaseUrl = window.location.origin;
    const $commentModal = new bootstrap.Modal($('#task_commentModal')[0]);
    const $replyModal = new bootstrap.Modal($('#task-reply-modal')[0]);
    const $commentForm = $('#comment-form');
    const $replyForm = $('#replyForm');
    const $commentThread = $('.comment-thread');
    const $loadMoreButton = $('#load-more-comments');
    const $hideButton = $('#hide-comments');
    let visibleCommentsCount = 5;

    // Event Handlers
    $(document).on('click', '.open-task-reply-modal', openReplyModal);
    $commentForm.on('submit', handleCommentSubmit);
    $replyForm.on('submit', handleReplySubmit);
    $(document).on('click', '#cancel-comment-btn', () => cancelForm($commentForm, $commentModal));
    $(document).on('click', '#cancel-reply-btn', () => cancelForm($replyForm, $replyModal));
    $(document).on('mouseenter', '.attachment-link', function () { togglePreview($(this), true); });
    $(document).on('mouseleave', '.attachment-link', function () { togglePreview($(this), false); });
    $loadMoreButton.on('click', loadMoreComments);
    $hideButton.on('click', hideComments);

    // Initialize comment visibility
    initializeCommentVisibility();

    function openReplyModal() {
        const parentId = $(this).data('comment-id');
        $replyForm.find('input[name="parent_id"]').val(parentId);
        $replyModal.show();
    }

    function handleCommentSubmit(event) {
        event.preventDefault();
        submitForm($(this), $commentModal, prependNewComment);
    }

    function handleReplySubmit(event) {
        event.preventDefault();
        submitForm($(this), $replyModal, prependNewReply);
    }

    function submitForm($form, modal, successCallback) {
        // Select the submit button
        const $submitButton = $form.find('button[type="submit"]');
        const originalButtonText = $submitButton.html();
        // Disable the button and change its text
        $submitButton.prop("disabled", true).html(label_please_wait);

        $.ajax({
            url: $form.attr("action"),
            type: "POST",
            data: new FormData($form[0]),
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.success) {
                    modal.hide();
                    successCallback(data);
                    $(".no_comments").hide();
                    toastr.success(data.message);
                    $form[0].reset();
                }
                // Re-enable the button and restore original text
                $submitButton.prop("disabled", false).html(originalButtonText);
            },
            error: function (xhr) {
                console.error("An error occurred:", xhr.responseText);

                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(function (field) {
                        if (Array.isArray(errors[field])) {
                            errors[field].forEach(function (message) {
                                toastr.error(message);
                            });
                        } else if (typeof errors[field] === "object") {
                            Object.values(errors[field]).forEach(function (
                                nestedErrors
                            ) {
                                if (Array.isArray(nestedErrors)) {
                                    nestedErrors.forEach(function (message) {
                                        toastr.error(message);
                                    });
                                } else {
                                    toastr.error(nestedErrors);
                                }
                            });
                        } else {
                            toastr.error(errors[field]);
                        }
                    });
                } else {
                    toastr.error(
                        "An error occurred while processing your request."
                    );
                }

                // Re-enable the button and restore original text
                $submitButton.prop("disabled", false).html(originalButtonText);
            },
        });
    }

    function prependNewComment(data) {
        $commentThread.prepend(createCommentHTML(data, true));
    }

    function prependNewReply(data) {
        const $parentComment = $(`#comment-${data.comment.parent_id}`);
        let $repliesContainer = $parentComment.find('.replies');
        if ($repliesContainer.length === 0) {
            $repliesContainer = $('<div class="replies"></div>');
            $parentComment.append($repliesContainer);
        }
        $repliesContainer.prepend(createCommentHTML(data, false));
    }

    function createCommentHTML(data, isMainComment) {
        return `
            <details open class="comment" id="comment-${data.comment.id}">
                <a href="#comment-${data.comment.id
            }" class="comment-border-link">
                    <span class="sr-only">Jump to comment-${data.comment.id
            }</span>
                </a>
                <summary>
                    <div class="comment-heading">
                        <div class="comment-avatar">
                            <img src="${data.user.photo
                ? `${imageBaseUrl}/storage/${data.user.photo}`
                : `${imageBaseUrl}/storage/photos/no-image.jpg`
            }"
                                 alt="${data.user.first_name
            } ${data.user.last_name}"
                                 class="bg-footer-theme rounded-circle border" width="40">
                        </div>


                        <div class="comment-info">
                            <a href="${imageBaseUrl}/users/${data.user.id}"
                               class="comment-author ${isMainComment ? "fw-semibold" : "fw-light"
            } text-body">
                                ${data.user.first_name} ${data.user.last_name}
                            </a>
                            <p class="m-0">${data.created_at}</p>
                        </div>

                                       ${isAdminOrHasAllDataAccess ? `
                    <div class="comment-actions d-flex ms-5 p-0">
                        <a href="javascript:void(0);"
                           data-comment-id="${data.comment.id}"
                           class="btn btn-sm text-primary edit-comment p-0"
                           data-bs-toggle="tooltip"
                           data-bs-placement="top"
                           title="Edit">
                            <i class="bx bx-edit"></i>
                        </a>
                        <a href="javascript:void(0);"
                           data-comment-id="${data.comment.id}"
                           class="btn btn-sm text-danger delete-comment p-0"
                           data-bs-toggle="tooltip"
                           data-bs-placement="top"
                           title="Delete">
                            <i class="bx bx-trash"></i>
                        </a>
                    </div>
                ` : ''}

                    </div>
                </summary>
                <div class="comment-body">
                    <p ${!isMainComment ? 'class="text-secondary"' : ""
            }>${data.comment.content}</p>
                    ${createAttachmentsHTML(data.comment.attachments)}
                    ${isMainComment
                ? `<button type="button" class="open-reply-modal mt-3" data-comment-id="${data.comment.id}">Reply</button>`
                : ""
            }
                </div>
            </details>
        `;
    }

    function createAttachmentsHTML(attachments) {
        if (!attachments || attachments.length === 0) return "";
        return `
            <div class="attachments mt-2">
                ${attachments
                .map(
                    (att) => `
                    <div class="attachment-item d-flex align-items-center gap-3">
                        <div class="attachment-preview-container flex-grow-1">

                            <a href="${imageBaseUrl}/storage/${att.file_path}" target="_blank"
                               class="attachment-link" data-preview-url="${imageBaseUrl}/storage/${att.file_path}">
                                ${att.file_name}
                            </a>
                            <div class="attachment-preview"></div>
                        </div>
                        <div class="attachment-actions d-flex gap-2">

                        <a href="${imageBaseUrl}/storage/${att.file_path}"
                                                                download="${att.file_name}"
                                                                class="text-primary" title="Download">
                                                                <i class="bx bx-download fs-4"></i>
                                                            </a>
                        <a href="javascript:void(0);"
                                                                class="text-danger delete-attachment"
                                                                data-attachment-id="${att.id}"
                                                                title="Delete">
                                                                <i class="bx bx-trash fs-4"></i>
                                                            </a>

                    </div>
                `
                )
            .join("")}
            </div>
        `;
    }

    function cancelForm($form, modal) {
        $form[0].reset();
        modal.hide();
    }

    function togglePreview($link, show) {
        const $previewContainer = $link.next('.attachment-preview');
        if (show) {
            const previewUrl = $link.data('preview-url');
            $previewContainer.empty();
            if (previewUrl.match(/\.(jpeg|jpg|gif|png)$/i)) {
                $('<img>', { src: previewUrl, css: { maxWidth: '300px', maxHeight: '200px' } }).appendTo($previewContainer);
            } else if (previewUrl.match(/\.(pdf)$/i)) {
                $('<iframe>', { src: previewUrl, width: '250', height: '150' }).appendTo($previewContainer);
            } else {
                $previewContainer.text('Preview not available');
            }
            $previewContainer.show();
        } else {
            $previewContainer.hide();
        }
    }

    function initializeCommentVisibility() {
        const $comments = $commentThread.find('.comment');
        $comments.each(function (index) {
            $(this).toggle(index < visibleCommentsCount);
        });

        $hideButton.hide(); // Hide the "Hide" button initially
        $loadMoreButton.toggle($comments.length > visibleCommentsCount);
    }

    function loadMoreComments() {
        visibleCommentsCount += 5;
        const $comments = $commentThread.find('.comment');
        $comments.each(function (index) {
            $(this).toggle(index < visibleCommentsCount);
        });
        $hideButton.toggle(visibleCommentsCount > 5); // Show the "Hide" button if more than 5 comments are visible
        $loadMoreButton.toggle(visibleCommentsCount < $comments.length); // Hide the "Load More" button if all comments are visible
    }

    function hideComments() {
        visibleCommentsCount = 5;
        const $comments = $commentThread.find('.comment');
        $comments.each(function (index) {
            $(this).toggle(index < visibleCommentsCount);
        });
        $hideButton.hide(); // Hide the "Hide" button
        $loadMoreButton.show(); // Show the "Load More" button
    }
});

$(document).ready(function () {
    // Check if the URL contains the specific hash
    if (window.location.hash === "#navs-top-discussions") {
        // Select the tab trigger
        var discussionsTabTrigger = document.querySelector(
            '[data-bs-target="#navs-top-discussions"]'
        );
        if (discussionsTabTrigger) {
            // Activate the tab
            var tabInstance = new bootstrap.Tab(discussionsTabTrigger);
            tabInstance.show();

            // Scroll to the tab content after a slight delay
            setTimeout(function () {
                discussionsTabTrigger.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                });
            }, 100); // Small delay to ensure tab transition is complete
        } else {
            console.error("Discussions tab trigger not found.");
        }
    }
});
$(document).on('click', '.edit-task-comment', function () {
    var commentId = $(this).data('comment-id');
    $.ajax({
        type: "GET",
        url: "/master-panel/tasks/comments/get/" + commentId,
        dataType: "JSON",
        success: function (response) {
            console.log(response);
            $('#comment_id').val(response.comment.id);
            $('#task-comment-edit-content').val(stripHtml(response.comment.content));
            $('#TaskEditCommentModal').modal('show');
        }
    });
});
$(document).on('click', '.delete-task-comment', function () {
    var commentId = $(this).data('comment-id');
    $.ajax({
        type: "GET",
        url: "/master-panel/tasks/comments/get/" + commentId,
        dataType: "JSON",
        success: function (response) {
            console.log(response);
            $('#delete_comment_id').val(response.comment.id);
            $('#TaskDeleteCommentModal').modal('show');
        }
    });
});
$(document).ready(function () {
    // Initialize for different textareas
    initializeMentionTextarea($('#task-reply-content'));     // For reply textarea
    initializeMentionTextarea($('#task-comment-content'));     // For reply textarea
    initializeMentionTextarea($('#task-comment-edit-content'));     // For reply textarea
});
$(function () {
    let attachmentId; // Store attachmentId temporarily for deletion

    // When the delete button is clicked
    $(document).on('click', '.delete-attachment', function () {
        attachmentId = $(this).data('attachment-id'); // Get the attachment ID

        // Show the Bootstrap delete confirmation modal
        $('#deleteAttachmentModal').modal('show');
    });

    // When the confirmation button is clicked in the modal
    $('#confirmDeleteAttachment').on('click', function () {
        // Hide the modal
        $('#deleteAttachmentModal').modal('hide');

        // Proceed with the deletion
        $.ajax({
            type: 'DELETE',
            url: '/master-panel/projects/comments/destroy-attachment/' + attachmentId,
            dataType: 'JSON',
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            success: function (response) {
                if (response.error === false) {
                    toastr.success(response.message);
                    setTimeout(function () {
                        window.location.reload();
                    }, 2000);
                } else {
                    toastr.error(response.message);
                    setTimeout(function () {
                        window.location.reload();
                    }, 2000);
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    });
});

// Time Entry
$(document).ready(function () {
    // Function to handle field visibility
    function toggleFields() {
        var entryType = $('#entry_type').val(); // Get the selected entry type

        if (entryType === 'standard') {
            $('#standard_hours ,#standard_hours_div').show().prop('disabled', false);    // Enable and show
            $('#start_time, #end_time ,#start_time_div ,#end_time_div').hide().prop('disabled', true); // Disable and hide
        } else if (entryType === 'flexible') {
            $('#start_time, #end_time, #start_time_div, #end_time_div').show().prop('disabled', false); // Enable and show
            $('#standard_hours , #standard_hours_div').hide().prop('disabled', true);    // Disable and hide
        }
    }

    // On page load
    toggleFields();

    // On change of entry type
    $('#entry_type').on('change', function () {
        toggleFields();
    }).trigger('change');
    $('#entry_date').daterangepicker({
        alwaysShowCalendars: true,
        showCustomRangeLabel: true,
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: true,
        locale: {
            cancelLabel: 'Clear',
            format: js_date_format
        }

    });
});
