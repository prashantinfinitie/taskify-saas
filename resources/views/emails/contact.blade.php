<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us Inquiry</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            color: #333333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            border: 1px solid #e0e0e0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            max-width: 150px;
            height: auto;
        }

        h1 {
            font-size: 26px;
            color: #2c3e50;
            margin: 0 0 20px;
            text-align: center;
        }

        .card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .card h2 {
            font-size: 20px;
            color: #2c3e50;
            margin-top: 0;
        }

        p {
            margin: 0 0 10px;
            line-height: 1.6;
        }

        .info {
            font-weight: bold;

        }

        .message {
            white-space: pre-wrap;
            background-color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #bdc3c7;
            color: #333333;
        }

        hr {
            border: none;
            border-top: 1px solid #e0e0e0;
            margin: 20px 0;
        }

        .footer {
            font-size: 12px;
            color: #7f8c8d;
            text-align: center;
            margin-top: 20px;
        }

        .container p:last-child {
            margin-bottom: 0;
        }
    </style>
</head>

<body>
{{-- @dd(asset($general_settings['full_logo'])) --}}
    <div class="container">
        <div class="header">
            <!-- Logo Section -->
            <img src="{{ $general_settings['full_logo'] }}" alt="Company Logo">
        </div>
        <h1>Contact Us Inquiry</h1>
        <div class="card">
            <h2>Inquiry Details</h2>
            <p><span class="info">Name:</span> {{ $content['name'] }}</p>
            <p><span class="info">Email:</span> {{ $content['email'] }}</p>
            <p><span class="info">Message:</span></p>
            <p class="message">{{ $content['message'] }}</p>
        </div>
        <hr>
        <p>Sent from your website contact form.</p>
        <p class="footer">This email was sent automatically from your website's contact form. Please do not reply to this email.</p>
    </div>
</body>

</html>
