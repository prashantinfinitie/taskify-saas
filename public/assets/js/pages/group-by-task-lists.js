$(document).ready(function () {

    // task group toggle functionality
    $('.toggle-list').on('click', function () {
        const listId = $(this).data('list-id');
        const icon = $(this).find('i');
        const taskGroup = $(`.task-group[data-list-id="${listId}"]`);
        taskGroup.slideToggle(200);
        icon.toggleClass('bx-chevron-down bx-chevron-right');
    });
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
$(document).ready(function () {
    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;
    // Function to check if we're near the bottom of the page
    function isNearBottom() {
        return $(window).scrollTop() + $(window).height() >
            $(document).height() - 200; // 200px before bottom
    }
    // Function to load more data
    function loadMoreData() {
        if (isLoading || !hasMore) return;
        isLoading = true;
        currentPage++;
        $('#loadingIndicator').removeClass('d-none');
        $.ajax({
            url: '/master-panel/tasks/group-by-task-list',
            type: 'GET',
            data: {
                page: currentPage
            },
            success: function (response) {
                $('#taskListsContainer').append(response.html);
                hasMore = response.hasMorePages;
                isLoading = false;
                $('#loadingIndicator').addClass('d-none');
                // If there's no more data, remove the scroll event listener
                if (!hasMore) {
                    $(window).off('scroll', scrollHandler);
                }
            },
            error: function (xhr) {
                isLoading = false;
                $('#loadingIndicator').addClass('d-none');
                // Handle error - maybe show a toast notification
                console.error('Error loading more data:', xhr);
            }
        });
    }
    // Scroll event handler
    function scrollHandler() {
        if (isNearBottom()) {
            loadMoreData();
        }
    }
    // Attach scroll event listener
    $(window).on('scroll', scrollHandler);
});
function queryParamsUsersClients(p) {
    return {
        type: $('#type').val(),
        typeId: $('#typeId').val(),
        page: p.offset / p.limit + 1,
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}
