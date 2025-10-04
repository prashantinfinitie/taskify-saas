


$('#filter_by_users').on('change', function () {
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
        user_id: $('#filter_by_users').val()
    };
}
