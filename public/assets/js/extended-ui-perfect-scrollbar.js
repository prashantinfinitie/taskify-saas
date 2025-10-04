/**
 * Perfect Scrollbar
 */
'use strict';
document.addEventListener('DOMContentLoaded', function () {
    (function () {
        const verticalExample = document.getElementById('vertical-example'),
            taskStatistics = document.getElementById('task-statistics'),
            projectStatistics = document.getElementById('project-statistics'),
            todoStatistics = document.getElementById('todos-statistics'),
            languageDropdown = document.getElementById('languageDropdown'),
            horizontalExample = document.getElementById('horizontal-example'),
            horizVertExample = document.getElementById('both-scrollbars-example'),
            kanbanColumnBodies = document.querySelectorAll('.kanban-column-body'),// Akanbdd Kanban column bodies
            kabanBoard = document.querySelector('.kaban-board'),
            recentActivity = document.getElementById('recent-activity'),
            searchResults = document.getElementById('searchResultsList');
        // Vertical Example
        // --------------------------------------------------------------------
        if (verticalExample) {
            new PerfectScrollbar(verticalExample, {
                wheelPropagation: false
            });
        }
        // Horizontal Example
        // --------------------------------------------------------------------
        if (horizontalExample) {
            new PerfectScrollbar(horizontalExample, {
                wheelPropagation: false,
                suppressScrollY: true
            });
        }
        // Both vertical and Horizontal Example
        // --------------------------------------------------------------------
        if (horizVertExample) {
            new PerfectScrollbar(horizVertExample, {
                wheelPropagation: false
            });
        }
        if (taskStatistics) {
            new PerfectScrollbar(taskStatistics, {
                wheelPropagation: false
            });
        }
        if (projectStatistics) {
            new PerfectScrollbar(projectStatistics, {
                wheelPropagation: false
            });
        }
        if (todoStatistics) {
            new PerfectScrollbar(todoStatistics, {
                wheelPropagation: false
            });
        }
        if (recentActivity) {
            new PerfectScrollbar(recentActivity, {
                wheelPropagation: false
            });
        }
        if (searchResults) {
            new PerfectScrollbar(searchResults, {
                wheelPropagation: false
            });
        }
        if (languageDropdown) {
            new PerfectScrollbar(languageDropdown, {
                wheelPropagation: false
            });
        }
        // Initialize Perfect Scrollbar for each Kanban column body
        kanbanColumnBodies.forEach(body => {
            new PerfectScrollbar(body, {
                suppressScrollX: true // Disable horizontal scrolling if not needed
            });
        });
        // Initialize Perfect Scrollbar for Kanban board
        if (kabanBoard) {
            new PerfectScrollbar(kabanBoard, {
                suppressScrollY: true // Disable vertical scrolling if not needed
            });
        }
    })();
});
