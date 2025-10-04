$(document).ready(function () {
    // Get all todo list containers as DOM elements
    let todoListContainers = document.querySelectorAll(".todo-list-container");

    // Initialize Sortable on each container with shared group
    todoListContainers.forEach(function (container) {
        new Sortable(container, {
            handle: ".todo-drag-handle", // Dragging allowed only on the move icon
            animation: 150,
            group: "todos", // Shared group name allows dragging between containers
            onEnd: function (evt) {
                // This function runs when an item is dropped
                const item = evt.item; // The dragged item
                const from = evt.from; // Source list
                const to = evt.to; // Destination list

                // Check if the todo was moved between different lists
                if (from !== to) {
                    // Get the todo ID from the item
                    const todoId = $(item).data("todo-id");

                    // Create a temporary checkbox element to simulate the onclick call
                    const tempCheckbox = $(item).find(".todo-check-input").get(0);
                    tempCheckbox.checked = $(to).closest(".todo-card").find(".todo-card-header").hasClass("todo-gradient-success");

                    // Trigger the existing update_status function
                    update_status(tempCheckbox);

                    // If item was moved to completed list
                    if (tempCheckbox.checked) {
                        // Add completed class to the item
                        $(item).addClass("todo-completed");
                        // Remove dashed style if present
                        $(item).removeClass("todo-title");

                        // Replace priority badge with completed tag
                        const metaContainer = $(item).find(".todo-meta");
                        metaContainer
                            .find(".todo-priority-badge")
                            .replaceWith(
                                '<span class="todo-completed-tag"><i class="bx bx-check-double me-1"></i>' +
                                    "Completed</span>"
                            );
                    }
                    // If item was moved to incomplete list
                    else {
                        // Uncheck the checkbox with slight delay (already handled in update_status via reload)
                        setTimeout(() => {
                            $(item)
                                .find(".todo-check-input")
                                .prop("checked", false);
                        }, 10);
                        // Get priority from data attribute
                        const priority = $(item).hasClass("todo-priority-high")
                            ? "high"
                            : $(item).hasClass("todo-priority-medium")
                            ? "medium"
                            : "low";

                        // Get proper color class based on priority
                        const colorClass =
                            priority === "high"
                                ? "danger"
                                : priority === "medium"
                                ? "warning"
                                : "success";

                        // Replace completed tag with priority badge
                        const metaContainer = $(item).find(".todo-meta");
                        metaContainer
                            .find(".todo-completed-tag")
                            .replaceWith(
                                '<span class="todo-priority-badge todo-bg-' +
                                    colorClass +
                                    '-subtle">' +
                                    priority.charAt(0).toUpperCase() +
                                    priority.slice(1) +
                                    "</span>"
                            );

                        // Remove completed class
                        $(item).removeClass("todo-completed");
                    }

                    // Update the counter on both containers (before potential reload)
                    updateCounters();
                }
            },
        });
    });

    // Handle inline todo form submissions
    $("#add-incomplete-todo-form, #add-completed-todo-form").on(
        "submit",
        function (e) {
            e.preventDefault();
            const form = $(this);
            const isCompleted = form.attr("id") === "add-completed-todo-form";
            const title = form.find("input[name='title']").val().trim();
            const priority =
                form.find("select[name='priority']").val() || "low"; // Add <select> to forms if priority selection is needed

            if (!title) {
                toastr.error("Todo title is required.");
                return;
            }

            $.ajax({
                url: "/master-panel/todos/store", 
                type: "POST",
                data: {
                    title: title,
                    priority: priority,
                    is_completed: isCompleted ? 1 : 0,
                    // Add other fields like description if you extend the forms
                    _token: $('meta[name="csrf-token"]').attr("content"),
                },
                success: function (response) {
                    if (response.error === false) {
                        // Matches your formatApiResponse structure
                        // Use the formatted todo data from response
                        const newTodo = response.data;
                        console.log(newTodo);
                        const priorityClass = `todo-priority-${newTodo.priority}`;
                        const colorClass =
                            newTodo.priority === "high"
                                ? "danger"
                                : newTodo.priority === "medium"
                                ? "warning"
                                : "success";
                        const checkedAttr = newTodo.is_completed
                            ? " checked"
                            : "";
                        const completedClass = newTodo.is_completed
                            ? " todo-completed"
                            : "";
                        const metaContent = newTodo.is_completed
                            ? '<span class="todo-completed-tag"><i class="bx bx-check-double me-1"></i>' +
                              get_label("completed", "Completed") +
                              "</span>"
                            : `<span class="todo-priority-badge todo-bg-${colorClass}-subtle">${
                                  newTodo.priority.charAt(0).toUpperCase() +
                                  newTodo.priority.slice(1)
                              }</span>`;

                        const newTodoHtml = `
                    <div class="todo-item ${priorityClass}${completedClass} d-flex align-items-center" data-todo-id="${newTodo.id}">
                        <div class="todo-drag-handle me-2">
                            <i class="bx bx-menu"></i>
                        </div>
                        <div class="todo-check me-3">
                            <input type="checkbox" class="todo-check-input border-2" id="${newTodo.id}" onclick='update_status(this)' name="${newTodo.id}"${checkedAttr}>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="todo-title">${newTodo.title}</h6>
                            <div class="todo-meta">
                        <span class="todo-meta-item"><i class="bx bx-calendar-alt"></i> ${newTodo.created_at.split(' ')[0]}</span>
                                ${metaContent}
                            </div>
                        </div>
                        <div class="todo-actions-container">
                            <div class="d-flex">
                                <a href="javascript:void(0);" class="edit-todo" data-bs-toggle="modal" data-bs-target="#edit_todo_modal" data-id="${newTodo.id}" title="<?= get_label('update', 'Update') ?>" class="card-link"><i class='bx bx-edit mx-1'></i></a>
                                <a href="javascript:void(0);" type="button" data-id="${newTodo.id}" data-type="todos" data-reload="true" title="<?= get_label('delete', 'Delete') ?>" class="card-link delete mx-4"><i class='bx bx-trash text-danger mx-1'></i></a>
                            </div>
                        </div>
                    </div>
                `;

                        // Prepend to the correct container (before the add-form div)
                        const containerId = isCompleted
                            ? "#completed-todo-list"
                            : "#incomplete-todo-list";
                        $(`${containerId} .todo-add-form`).before(newTodoHtml);

                        // Clear the input
                        form.find("input[name='title']").val("");

                        // Show success message (no reload)
                        toastr.success(response.message, "Success");

                        // Update counters and progress bar
                        updateCounters();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    let errorMsg = "Error adding todo.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    toastr.error(errorMsg);
                },
            });
        }
    );

    // Function to update the counters after drag and drop
    function updateCounters() {
        const incompleteContainer = document
            .querySelector(".todo-gradient-primary")
            .closest(".todo-card")
            .querySelector(".todo-list-container");
        const completeContainer = document
            .querySelector(".todo-gradient-success")
            .closest(".todo-card")
            .querySelector(".todo-list-container");

        // Count todos, excluding the add-form
        const incompleteCount = incompleteContainer.querySelectorAll(
            ".todo-item:not(.todo-add-form)"
        ).length;
        const completeCount = completeContainer.querySelectorAll(
            ".todo-item:not(.todo-add-form)"
        ).length;
        const totalCount = incompleteCount + completeCount;

        // Update counters
        document
            .querySelector(".todo-gradient-primary")
            .closest(".todo-card-header")
            .querySelector(".todo-counter").textContent = incompleteCount;
        document
            .querySelector(".todo-gradient-success")
            .closest(".todo-card-header")
            .querySelector(".todo-counter").textContent = completeCount;

        // Calculate progress
        let progress = totalCount > 0 ? (completeCount / totalCount) * 100 : 0;
        progress = progress.toFixed(2); // same formatting as PHP

        // Update progress text and bar
        $(".todo-progress-value").text(
            `${completeCount} / ${totalCount} (${progress}%)`
        );
        $(".progress-bar").css("width", `${progress}%`);
        $(".progress-bar").attr("aria-valuenow", progress);
    }
});
