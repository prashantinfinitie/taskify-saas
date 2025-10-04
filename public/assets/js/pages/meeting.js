var baseUrl = "{{ url('/') }}";
var meeting_id = $('#meeting_id').val();
var meeting_page = $('#meeting_page').val();
var is_meeting_admin = $('#is_meeting_admin').val();
const domain = 'meet.guifi.net';
const options = {
    roomName: $('#room_name').val(),
    parentNode: document.querySelector('#meet'),
    userInfo: {
        email: $('#user_email').val(),
        displayName: $('#user_name').val()
    },
    SHOW_PROMOTIONAL_CLOSE_PAGE: false,
    configOverwrite: {
        defaultLanguage: 'en'
    }
};
const api = new JitsiMeetExternalAPI(domain, options);
api.addEventListener('readyToClose', () => {
    window.location.href = meeting_page;
});
setTimeout(() => {
    $('.eXO-cfa-cont').hide(); // or the JavaScript method
    $('#eXO-cfa-cont').hide();
}, 1000);
