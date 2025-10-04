function queryParamsEmailHistory(params) {
    console.log("Query Params:", params);
    return {
        search: params.search,
        limit: params.limit,
        offset: params.offset,
        sort: params.sort,
        order: params.order,
    };
}

function emailHistoryActionsFormatter(value, row) {
    return `
    <button class="btn btn-sm btn-outline-secondary preview-history-btn"
        data-body="${encodeURIComponent(row.body)}">
        <span>Preview</span>
    </button>`;
}

// CSRF setup if not already done
$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
    },
});

// Email History Preview Button click
$(document).on("click", ".preview-history-btn", function () {
    let body = decodeURIComponent($(this).data("body"));

    // Optional cleanup (remove background-color inline styles)
    body = body.replace(/background-color:\s*[^;]+;?/gi, "");

    $("#previewContent").html(`<div>${body}</div>`);
    $("#previewModal").modal("show");
});
