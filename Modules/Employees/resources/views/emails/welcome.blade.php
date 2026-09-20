<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to HRFlow</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 20px;
            color: #333333;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #2563eb;
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
            line-height: 1.6;
        }
        .credentials-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #2563eb;
            padding: 16px 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .credentials-item {
            margin-bottom: 8px;
            font-size: 15px;
        }
        .credentials-item:last-child {
            margin-bottom: 0;
        }
        .credentials-label {
            font-weight: bold;
            color: #475569;
        }
        .footer {
            background-color: #f1f5f9;
            text-align: center;
            padding: 16px;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>

<div class="email-container">
    <div class="header">
        <h1>Welcome to HRFlow</h1>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $employeeName }}</strong>,</p>

        <p>Welcome to the team! Your employee account has been successfully created in the <strong>HRFlow</strong> system.</p>

        <p>You can use the following temporary login credentials to access your account for the first time:</p>

        <div class="credentials-box">
            <div class="credentials-item">
                <span class="credentials-label">Email:</span> {{ $email }}
            </div>
            <div class="credentials-item">
                <span class="credentials-label">Temporary Password:</span> <code>{{ $password }}</code>
            </div>
        </div>

        <p>For security purposes, please log in and change your password immediately after your first sign-in.</p>

        <p>Best regards,<br>
        <strong>HR Department</strong></p>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} HRFlow System. All rights reserved.
    </div>
</div>

</body>
</html>
