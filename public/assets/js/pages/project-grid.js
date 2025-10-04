'use strict';
$('#status_filter').on('change', function (e) {
    var status = $(this).val();
    location.href = setUrlParameter(location.href, 'status', status);
});
$('#sort').on('change', function (e) {
    var sort = $(this).val();
    location.href = setUrlParameter(location.href, 'sort', sort);
});
$(document).ready(function () {
    $('#sort').select2();
});

$('#tags_filter').on("click", function () {
    var routePrefix = $(this).data('routePrefix');
    // Get the selected values from status select and other filters
    var status = $('#status_filter').val();
    var sort = $('#sort').val();
    // Get selected tags using Select2
    var selectedTags = $('#selected_tags').val();


    // Form the URL with the selected filters
    // Initialize the base URL
    var url = "/master-panel/projects";

    // Check the current URL
    if (window.location.pathname.includes("/master-panel/projects/kanban-view")) {
        url = "/master-panel/projects/kanban-view"; // Set URL to kanbanview if the condition is met
    }

    // Log the final URL for debugging purposes
    console.log(url);

    var params = [];

    if (status) {
        params.push("status=" + status);
    }

    if (sort) {
        params.push("sort=" + sort);
    }

    if (selectedTags && selectedTags.length > 0) {
        params.push("tags[]=" + selectedTags.join("&tags[]="));
    }

    if (params.length > 0) {
        url += "?" + params.join("&");
    }
    // Redirect to the URL
    window.location.href = url;
});


function setUrlParameter(url, paramName, paramValue) {
    paramName = paramName.replace(/\s+/g, '-');
    if (paramValue == null || paramValue == '') {
        return url.replace(new RegExp('[?&]' + paramName + '=[^&#]*(#.*)?$'), '$1')
            .replace(new RegExp('([?&])' + paramName + '=[^&]*&'), '$1');
    }
    var pattern = new RegExp('\\b(' + paramName + '=).*?(&|#|$)');
    if (url.search(pattern) >= 0) {
        return url.replace(pattern, '$1' + paramValue + '$2');
    }
    url = url.replace(/[?#]$/, '');
    return url + (url.indexOf('?') > 0 ? '&' : '?') + paramName + '=' + paramValue;
}

function userFormatter(value, row, index) {
    return '<div class="d-flex">' +
        row.profile +
        '</div>';

}

function clientFormatter(value, row, index) {
    return '<div class="d-flex">' +
        row.profile +
        '</div>';

}
document.addEventListener('DOMContentLoaded', function () {
    const columns = Array.from(document.querySelectorAll('.kanban-column-body'));

    // Get the create project button
    const createProjectBtns = document.querySelectorAll('.create-project-btn');

    // Add a click event to each "Create Project" button
    createProjectBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const defaultStatusId = btn.closest('.kanban-column').dataset.statusId;
            const statusSelect = $('.statusDropdown');

            if (statusSelect.length) {
                statusSelect.val(defaultStatusId).trigger('change');
            }
        });
    });

    function formatStatus(status) {
        if (!status.id) {
            return status.text;
        }
        var color = $(status.element).data('color');
        var $status = $('<span class="badge bg-label-' + color + '">' + status.text + '</span>');
        return $status;
    }

    // Initialize Select2 for the status dropdown
    $('.statusDropdown').each(function () {
        var $this = $(this);
        $this.select2({
            dropdownParent: $this.closest('.modal'),
            templateResult: formatStatus,
            templateSelection: formatStatus,
            escapeMarkup: function (markup) {
                return markup;
            }
        });
    });

    // Store references to source columns and elements for potential revert
    let sourceColumn = null;
    let sourceSibling = null;

    const drake = dragula(columns, {
        direction: 'vertical',
        moves: function (el, container, handle) {
            return !el.classList.contains('create-project-btn');
        },
        accepts: function (el, target) {
            return !el.classList.contains('create-project-btn');
        },
        invalid: function (el, handle) {
            return el.classList.contains('create-project-btn');
        }
    });

    // Event when dragging starts
    drake.on('drag', function (el, source) {
        el.classList.add('dragging');
        el.dataset.originalStatusId = el.closest('.kanban-column').dataset.statusId;

        // Store the source column and the next sibling for potential revert
        sourceColumn = source;
        sourceSibling = el.nextElementSibling;
    });

    drake.on('dragend', function (el) {
        el.classList.remove('dragging');
        el.classList.add('dropped');
        document.querySelectorAll('.drop-target').forEach(target => {
            target.classList.remove('drop-target');
        });
    });

    drake.on('over', function (el, container) {
        container.classList.add('drop-target');
    });

    drake.on('out', function (el, container) {
        container.classList.remove('drop-target');
    });

    // Function to revert the card to its original position
    function revertCard(el) {
        if (sourceColumn && el.parentElement !== sourceColumn) {
            drake.cancel();  // Cancel the current drag operation

            // Remove the card from its current position
            el.parentElement.removeChild(el);

            // Insert the card back to its original position
            if (sourceSibling) {
                sourceColumn.insertBefore(el, sourceSibling);
            } else {
                sourceColumn.appendChild(el);
            }

            // Update column counts after reverting
            updateColumnCounts();

            // Reset stored references
            sourceColumn = null;
            sourceSibling = null;
        }
    }

    drake.on('drop', function (el, target, source, sibling) {
        const newStatus = target.closest('.kanban-column').dataset.statusId;
        const originalStatus = el.dataset.originalStatusId;
        const cardId = el.dataset.cardId;

        if (newStatus !== originalStatus) {
            $.ajax({
                url: '/master-panel/update-project-status',
                type: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                data: JSON.stringify({
                    id: cardId,
                    statusId: newStatus
                }),
                success: function (response) {
                    if (response.error === false) {
                        toastr.success(response.message);
                        updateColumnCounts();
                    } else {
                        toastr.error(response.message);
                        revertCard(el);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                    toastr.error('Failed to update status. Please try again.');
                    revertCard(el);
                }
            });
        } else {
            toastr.warning(label_status_not_changed_warning);
        }
    });

    function updateColumnCounts() {
        const totalProjectsCount = document.querySelectorAll('.kanban-card').length;
        document.querySelectorAll('.kanban-column').forEach(column => {
            const count = column.querySelectorAll('.kanban-card').length;
            column.querySelector('.column-count').textContent = `${count}/${totalProjectsCount}`;
        });
    }
});



$(document).ready(function () {
    const urlParams = new URLSearchParams(window.location.search);
    const selectedStatus = urlParams.get('status');
    let initialSelection = true;  // Flag to prevent reload on initial selection
    // alert(selectedStatus);
    initSelect2Ajax(
        '#status_filter',
        '/master-panel/status/search',
        label_filter_status,
        true,                                  // Allow clear
        0,                                     // Minimum input length
        true                                   // Allow Initials Options
    );
    if (selectedStatus) {
        $.ajax({
            url: '/master-panel/status/search', // Reuse the same route for fetching the single status
            data: { q: '', page: 1 },
            dataType: 'json',
            success: function (data) {
                // Look for the status that matches the selectedStatus from URL
                const matchedStatus = data.items.find(item => item.id == selectedStatus);

                if (matchedStatus && initialSelection == true) {
                    // Create a new option dynamically and set it as selected
                    let option = new Option(matchedStatus.text, matchedStatus.id, true, true);
                    $('#status_filter').append(option);

                    // Only trigger change if it's not the initial selection
                    initialSelection = false;  // Reset flag after setting initial option
                }
            }
        });
    }
});

