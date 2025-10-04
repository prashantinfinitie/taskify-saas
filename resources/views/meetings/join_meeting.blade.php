<div class="card">
    <div class="card-body">
        <div id="meet" />
        </body>
        <input type="hidden" name="room_name" id="room_name" value="<?= $room_name ?>">
        <input type="hidden" name="user_name" id="user_name" value="<?= $user_display_name ?>">
        <input type="hidden" name="user_email" id="user_email" value="<?= $user_email ?>">
        <input type="hidden" name="meeting_id" id="meeting_id" value="<?= $meeting_id ?>">
        <input type="hidden" name="is_meeting_admin" id="is_meeting_admin" value="<?= $is_meeting_admin ?>">
        <input type="hidden" name="meeting_page" id="meeting_page" value="{{route('meetings.index')}}">


    </div>
</div>
</div>
<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets/js/jitsi.js') }}"></script>
<script src="{{ asset('assets/js/pages/meeting.js') }}"></script>

