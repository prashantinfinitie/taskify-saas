<!DOCTYPE html>
<html>

<head>
    <title>{{ $email->subject }}</title>
</head>

<body>
    {!! $body !!}

    @if(!empty($email->attachments))
        <div style="margin-top: 20px; font-size: 12px; color: #666;">
            <p>Attachments:</p>
            <ul>
                @foreach($email->attachments as $attachment)
                    <li>{{ $attachment['original_name'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</body>

</html>