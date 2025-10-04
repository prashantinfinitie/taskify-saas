$(document).ready(function () {
    var options = {
        container: 'mind-map',
        editable: false,
        theme: 'taskify',
        mode: 'full',
        support_html: true,
    };
    // Create a new jsMind instance
    var jm = new jsMind(options);
    jm.show(mindMapData);
    $('.export-mindmap-btn').on('click', function () {
        try {
            jm.shoot(); // Trigger the export
            setTimeout(function () {
                toastr.success('Mind map exported successfully!');
            }, 2000); // Adjust the timeout as needed
        } catch (error) {
            toastr.error('Failed to export mind map. Please try again.');
            console.log(error);

        }
        return;
    });





    $('jmnode').on('click', function () {
        var nodeId = $(this).attr('nodeid');
        console.log(nodeId);
        // Parse the nodeId to extract type and id
        var parts = nodeId.split('_'); // Assumes nodeId is in the format 'type_id'
        var type = parts[0];
        var id = parts[1] || null; // Defaults to null if id is not present
        // Assume projectId is defined elsewhere in your script
        // Determine the URL based on the type and id
        var url = '';
        switch (type) {
            case 'project':
            case 'projects':
                if (id) {
                    url = '/master-panel/projects/information/' + id; // Specific project page
                } else {
                    url = '/master-panel/projects'; // Projects list page
                }
            case 'tasks':
            case 'task':
                if (id) {
                    url = '/master-panel/tasks/information/' + id; // Specific task page
                } else {
                    url = '/master-panel/tasks'; // Tasks list page
                }
                break;

            case 'comment':
            case 'reply':
                url = '/master-panel/projects/information/' + projectId + '#navs-top-discussions'; // Specific comment or reply page
                break;
            case 'milestone':
            case 'milestones':
            case 'media':
                url = '/master-panel/projects/information/' + projectId; // Specific milestone or media page
                break;
            case 'user':
            case 'users':
                if (id) {
                    url = '/master-panel/users/profile/' + id; // Specific user page
                } else {
                    url = '/master-panel/users'; // Users list page
                }
                break;
            case 'client':
            case 'clients':
                if (id) {
                    url = '/master-panel/clients/profile/' + id; // Specific client page
                } else {
                    url = '/master-panel/clients'; // Clients list page
                }
                break;
            default:
                console.error('Unknown type:', type);
                return; // Exit if type is unknown
        }
        // Redirect to the constructed URL
        window.open(url, '_blank');
    });

});
