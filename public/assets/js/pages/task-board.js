'use strict';

(function () {
    console.log("✅ Script Loaded");

    // ✅ Step 1: Validate and parse statusArray
    if (typeof statusArray === 'undefined') {
        // console.error("❌ statusArray is NOT defined. Drag & drop will not work.");
        return;
    }

    // console.log("✅ Raw statusArray value:", statusArray);

    // ✅ Step 2: If statusArray is a string, parse it
    if (typeof statusArray === 'string') {
        // console.warn("⚠️ statusArray is a string. Parsing JSON...");
        try {
            statusArray = JSON.parse(statusArray);
            // console.log("✅ Parsed statusArray:", statusArray);
        } catch (e) {
            console.error("❌ Failed to parse statusArray:", e);
            return;
        }
    } else {
        // console.log("✅ statusArray is already an array");
    }

    // ✅ Step 3: Validate it's an array
    if (!Array.isArray(statusArray)) {
        // console.error("❌ statusArray is not an array after parsing. Value:", statusArray);
        return;
    }

    // ✅ Step 4: Collect containers
    var elements = [];
    for (var i = 0; i < statusArray.length; i++) {
        var sts = statusArray[i];

        // console.log(`🔍 Checking index ${i}:`, sts);

        if (!sts || typeof sts !== 'object') {
            // console.warn(`⚠️ Skipping index ${i} because it's not an object:`, sts);
            continue;
        }

        if (!sts.slug) {
            // console.warn(`⚠️ Skipping index ${i} because slug is missing:`, sts);
            continue;
        }

        var slug = sts.slug.trim();
        // console.log(`🔍 Looking for container with ID: #${slug}`);
        var element = document.getElementById(slug);

        if (element) {
            // console.log(`✅ Found container for slug "${slug}"`);
            elements.push(element);
        } else {
            // console.warn(`⚠️ Container NOT found for slug "${slug}"`);
        }
    }

    // ✅ Step 5: Check elements
    if (elements.length === 0) {
        // console.error("❌ No valid containers found. Dragula cannot initialize.");
        return;
    }

    // console.log("✅ Containers for Dragula:", elements);

    // ✅ Step 6: Initialize Dragula
    $(function () {
        var drake;
        try {
            drake = dragula(elements, { revertOnSpill: true });
            // console.log("✅ Dragula initialized successfully");
        } catch (e) {
            // console.error("❌ Dragula initialization failed:", e);
            return;
        }

        var oldParent;

        drake.on('drag', function (el, source) {
            oldParent = source;
            // console.log(`🚀 Drag started | Task ID: ${el.getAttribute('data-task-id')} | From Status: ${source.getAttribute('data-status')}`);
        });

        drake.on('drop', function (el, target) {
            if (!target) {
                // console.warn("⚠️ Drop target is null (probably outside any container)");
                return;
            }

            var taskId = el.getAttribute('data-task-id');
            var newStatus = target.getAttribute('data-status');

            // console.log(`✅ Drop event | Task ID: ${taskId} → New Status: ${newStatus}`);

            if (!taskId || !newStatus) {
                // console.error("❌ Missing Task ID or New Status. Skipping AJAX.");
                return;
            }

            // ✅ AJAX request
            $.ajax({
                method: "PUT",
                url: `/master-panel/tasks/${taskId}/update-status/${newStatus}`,
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    flash_message_only: 1
                },
                success: function (response) {
                    console.log("✅ AJAX Success:", response);
                    if (response.error === false) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                        // console.warn("⚠️ Server returned error, reverting element");
                        drake.cancel(true);
                        $(oldParent).append(el);
                    }
                },
                error: function (xhr, status, error) {
                    // console.error("❌ AJAX Error:", status, error);
                    toastr.error("An error occurred during the AJAX request");
                    drake.cancel(true);
                    $(oldParent).append(el);
                }
            });
        });

        drake.on('cancel', function (el) {
            console.log(`↩️ Drag cancelled | Task ID: ${el.getAttribute('data-task-id')}`);
        });
    });
})();


function userFormatter(value, row, index) {
    return '<div class="d-flex">' + row.photo + '<div class="mx-2 mt-2"><h6 class="mb-1">' + row.first_name + ' ' + row.last_name +
        (row.status === 1 ? ' <span class="badge bg-success">Active</span>' : ' <span class="badge bg-danger">Deactive</span>') +
        '</h6><p class="text-muted">' + row.email + '</p></div>' +
        '</div>';

}

function clientFormatter(value, row, index) {
    return '<div class="d-flex">' + row.profile + '<div class="mx-2 mt-2"><h6 class="mb-1">' + row.first_name + ' ' + row.last_name +
        (row.status === 1 ? ' <span class="badge bg-success">Active</span>' : ' <span class="badge bg-danger">Deactive</span>') +
        '</h6><p class="text-muted">' + row.email + '</p></div>' +
        '</div>';

}

