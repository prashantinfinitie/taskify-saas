<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{subject}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #DED0B6;
            background-image: url("data:image/svg+xml,%3Csvg width='52' height='26' viewBox='0 0 52 26' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.2'%3E%3Cpath d='M10 10c0-2.21-1.79-4-4-4-3.314 0-6-2.686-6-6h2c0 2.21 1.79 4 4 4 3.314 0 6 2.686 6 6 0 2.21 1.79 4 4 4 3.314 0 6 2.686 6 6 0 2.21 1.79 4 4 4v2c-3.314 0-6-2.686-6-6 0-2.21-1.79-4-4-4-3.314 0-6-2.686-6-6zm25.464-1.95l8.486 8.486-1.414 1.414-8.486-8.486 1.414-1.414z' /%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            margin: 0;
            padding: 40px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #28a745;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .header img {
            max-width: 150px;
            margin-bottom: 15px;
        }
        .content {
            padding: 30px;
        }
        h1 {
            font-size: 24px;
            color: #ffffff;
            margin: 0;
        }
        h2 {
            font-size: 20px;
            color: #333333;
            margin-top: 0;
        }
        p {
            font-size: 16px;
            color: #555555;
            line-height: 1.6;
        }
        .ticket-details {
            background-color: #f0f4f8;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .ticket-details p {
            margin: 10px 0;
        }
        strong {
            color: #333333;
        }
        .footer {
            background-color: #f0f4f8;
            text-align: center;
            padding: 20px;
            font-size: 14px;
            color: #777777;
        }
        .footer a {
            color: #28a745;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            {COMPANY_LOGO}
            <h1>{subject}</h1>
        </div>
        <div class="content">
            <h2>Ticket Submission Confirmation</h2>
            <p>Dear {FIRST_NAME} {LAST_NAME},</p>
            <p>Your ticket has been successfully submitted with the following details:</p>
            <div class="ticket-details">
                <p><strong>Ticket ID:</strong> #{TICKET_ID}</p>
                <p><strong>Title:</strong> {TICKET_TITLE}</p>
                <p><strong>Description:</strong> {TICKET_DESCRIPTION}</p>
            </div>
            <p>We have received your ticket and our team will review it shortly. Thank you for using our system.</p>
            <p>Best regards,<br><strong>{COMPANY_TITLE}</strong></p>
        </div>
        <div class="footer">
            &copy; {CURRENT_YEAR} {COMPANY_TITLE}. All rights reserved.<br>
            <a href="{SITE_URL}">{COMPANY_TITLE}</a>
        </div>
    </div>
</body>
</html>
