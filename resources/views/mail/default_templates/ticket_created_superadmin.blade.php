<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{subject}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f0f0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23000000' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
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
            background-color: #007bff;
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
            color: #007bff;
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
            <h2>New Ticket Notification</h2>
            <p>Dear Superadmin,</p>
            <p>A new ticket has been created by <strong>{ADMIN_NAME}</strong>. Please find the details below:</p>
            <div class="ticket-details">
                <p><strong>Ticket ID:</strong> #{TICKET_ID}</p>
                <p><strong>Title:</strong> {TICKET_TITLE}</p>
                <p><strong>Description:</strong> {TICKET_DESCRIPTION}</p>
            </div>
            <p>Your prompt attention to this matter would be appreciated.</p>
            <p>Best regards,<br><strong>{COMPANY_TITLE}</strong></p>
        </div>
        <div class="footer">
            &copy; {CURRENT_YEAR} {COMPANY_TITLE}. All rights reserved.<br>
            <a href="{SITE_URL}">{COMPANY_TITLE}</a>
        </div>
    </div>
</body>
</html>
