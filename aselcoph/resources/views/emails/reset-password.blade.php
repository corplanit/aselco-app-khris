<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ASELCO Portal Password Reset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f9fc;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .email-header {
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .email-header img {
            height: 40px;
            margin-bottom: 10px;
        }
        .email-content {
            padding: 30px;
        }
        .email-content p {
            margin: 1em 0;
            font-size: 15px;
            line-height: 1.6;
        }
        .email-content strong {
            color: #1e3a8a;
        }
        .email-footer {
            background-color: #f1f5f9;
            text-align: center;
            padding: 15px;
            font-size: 13px;
            color: #666;
        }
        .email-footer a {
            color: #1e3a8a;
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <img src="https://aselco.ckent.dev/assets/logo.png" alt="ASELCO Logo">
            <h2>Password Reset Notification</h2>
        </div>

        <!-- Body -->
        <div class="email-content">
            <p>Hello <strong>{{ $name }}</strong>,</p>

            <p>Your ASELCO portal password has been reset as requested by an administrator.</p>

            <p>
                <strong>Email:</strong> {{ $email }}<br>
                <strong>New Password:</strong> {{ $password }}
            </p>

            <p>Please log in using the credentials above and change your password immediately for security purposes.</p>

            <p>
                👉 <a href="https://aselco.ckent.dev/" target="_blank">Click here to access the portal</a>
            </p>

            <p>If you did not request this reset, please contact support immediately.</p>

            <p>Thank you,<br>The ASELCO Support Team</p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            This is an automated message. Please do not reply.<br>
            Visit our portal at <a href="https://aselco.ckent.dev/">aselco.ckent.dev</a>
        </div>
    </div>
</body>
</html>
