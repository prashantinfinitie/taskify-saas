'use strict';
// $(document).ready(function () {
//     var $sortable = $('#sortable-menu');

//     // Initialize main menu sortable
//     Sortable.create($sortable[0], {
//         animation: 150,
//         handle: '.handle'
//     });

//     // Initialize submenu sortable
//     $('.submenu').each(function () {
//         var $submenu = $(this);
//         Sortable.create($submenu[0], {
//             animation: 150,
//             handle: '.handle'
//         });
//     });

//     // Handle form submission
//     $('#menu-order-form').on('submit', function (e) {
//         e.preventDefault(); // Prevent default form submission
//         var $submitButton = $('#btnSaveMenuOrder');
//         $submitButton.attr('disabled', true).html(label_please_wait);
//         var menuOrder = [];
//         $('#sortable-menu li').each(function () {
//             var menuId = $(this).data('id');
//             var submenus = [];

//             // Check if there are submenus
//             $(this).find('.submenu li').each(function () {
//                 submenus.push({ id: $(this).data('id') });
//             });

//             menuOrder.push({ id: menuId, submenus: submenus });
//         });

//         // Send the sorted IDs to your backend via AJAX
//         $.ajax({
//             url:  '/master-panel/save-menu-order',
//             method: 'POST',
//             data: {
//                 menu_order: menuOrder
//             },
//             headers: {
//                 'X-CSRF-TOKEN': $('input[name="_token"]').attr('value')
//             },
//             success: function (response) {
//                 if (response.error == false) {
//                     toastr.success(response['message']);
//                     setTimeout(function () {
//                         location.reload();
//                     }, parseFloat(toastTimeOut) * 1000);
//                 } else {
//                     toastr.error(response.message);
//                 }
//             },
//             error: function (xhr, status, error) {
//                 toastr.error(label_something_went_wrong);
//             },
//             complete: function () {
//                 $submitButton.attr('disabled', false).html(label_update);
//             }
//         });
//     });
// });


$(document).ready(function () {
    // First, restructure the DOM to group categories properly
    var $sortable = $('#sortable-menu');
    
    // Create category containers for each group
    var currentContainer = null;
    
    // Iterate through all children and group them by category
    $sortable.children().each(function() {
        if ($(this).hasClass('category-header')) {
            // Create a new container for this category
            currentContainer = $('<div class="category-wrapper"></div>');
            $(this).before(currentContainer);
            currentContainer.append($(this)); // Move the header into the container
        } else if (currentContainer) {
            // Add this menu item to the current category container
            currentContainer.append($(this));
        }
    });
    
    // Make the main container sortable (for category order)
    Sortable.create($sortable[0], {
        animation: 150,
        handle: '.category-header',
        draggable: '.category-wrapper',
        ghostClass: 'sortable-ghost'
    });
    
    // Make menu items within each category sortable
    $('.category-wrapper').each(function() {
        var $categoryItems = $(this).children('li:not(.category-header)');
        
        // Only initialize sortable if there are items
        if ($categoryItems.length > 0) {
            // Create a container for the menu items
            var $menuContainer = $('<div class="menu-items-container submenu"></div>');
            $(this).append($menuContainer);
            
            // Move all non-header items into this container
            $categoryItems.each(function() {
                $menuContainer.append($(this));
            });
            
            // Make menu items sortable within their category
            Sortable.create($menuContainer[0], {
                animation: 150,
                handle: '.handle',
                draggable: 'li', // Top-level menu items
                ghostClass: 'sortable-ghost',
                group: {
                    name: 'menuItems-' + Math.random().toString(36).substring(2, 8), // Unique per container
                    pull: false,
                    put: false
                },
                // onMove: function (evt) {
                //     const draggedElement = evt.dragged;
                //     // Prevent dragging if the item has a submenu (optional, to avoid breaking hierarchy)
                //     if (draggedElement.querySelector('.submenu')) {
                //         return false;
                //     }
                //     return true;
                // }
            });
        }
    });

    // Handle submenus separately with restricted movement
    $('.submenu').each(function () {
        Sortable.create(this, {
            animation: 150,
            handle: '.handle',
            draggable: 'li', // Allow reordering within submenu
            ghostClass: 'sortable-ghost',
            group: {
                name: 'submenus-' + Math.random().toString(36).substring(2, 8), // Unique per submenu
                pull: false,
                put: false
            },
            // onMove: function (evt) {
            //     const draggedElement = evt.dragged;
            //     const relatedElement = evt.related;
            //     // Prevent moving submenu items outside their parent submenu
            //     if (relatedElement && draggedElement.closest('.submenu') !== relatedElement.closest('.submenu')) {
            //         return false; // Block cross-submenu moves
            //     }
            //     return true;
            // }
        });
    });

    // Handle form submission
    $('#menu-order-form').on('submit', function (e) {
        e.preventDefault();
        var $submitButton = $(this).find('button[type="submit"]');
        $submitButton.attr('disabled', true).html(label_please_wait || 'Please wait...');
        
        var menuOrder = [];
        
        // Process each category
        $('.category-wrapper').each(function() {
            var $header = $(this).find('.category-header');
            var categoryName = $header.find('strong').text().trim();
            var categoryMenus = [];
            
            // Process all menu items in this category
            $(this).find('.menu-items-container > li').each(function() {
                var menuId = $(this).data('id');
                var submenus = [];
                
                // Process submenus
                $(this).find('.submenu li').each(function() {
                    submenus.push({ id: $(this).data('id') });
                });
                
                categoryMenus.push({ id: menuId, submenus: submenus });
            });
            
            // Add this category to the order
            menuOrder.push({
                category: categoryName,
                menus: categoryMenus
            });
        });

        // Send the sorted data to your backend
        $.ajax({
            url: '/master-panel/save-menu-order',
            method: 'POST',
            data: {
                menu_order: menuOrder
            },
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').attr('value')
            },
            success: function (response) {
                if (response.error == false) {
                    toastr.success(response.message);
                    setTimeout(function () {
                        location.reload();
                    }, parseFloat(toastTimeOut || 2) * 1000);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                toastr.error(label_something_went_wrong || 'Something went wrong');
            },
            complete: function () {
                $submitButton.attr('disabled', false).html(label_update || 'Update');
            }
        });
    });
});
$(document).on('click', '#btnResetDefaultMenuOrder', function (e) {
    e.preventDefault();
    $('#confirmResetDefaultMenuOrderModal').modal('show'); // show the confirmation modal
    $('#confirmResetDefaultMenuOrderModal').off('click', '#btnconfirmResetDefaultMenuOrder');
    $('#confirmResetDefaultMenuOrderModal').on('click', '#btnconfirmResetDefaultMenuOrder', function (e) {
        $('#btnconfirmResetDefaultMenuOrder').html(label_please_wait).attr('disabled', true);
        $.ajax({
            url:  '/master-panel/reset-default-menu-order',
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').attr('value')
            },
            success: function (response) {
                if (response.error == false) {
                    toastr.success(response['message']);
                    setTimeout(function () {
                        location.reload();
                    }, parseFloat(toastTimeOut) * 1000);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (data) {
                toastr.error(label_something_went_wrong);
            },
            complete: function () {
                $('#confirmResetDefaultMenuOrderModal').modal('hide');
                $('#btnconfirmResetDefaultMenuOrder').attr('disabled', false).html(label_yes);
            }
        });
    });
});
