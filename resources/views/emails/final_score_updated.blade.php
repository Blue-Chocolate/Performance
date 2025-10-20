<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Final Score Notification</title>
</head>
<body>
    <h2>Hello {{ $organization->name }},</h2>

    <p>Your final evaluation score has been updated.</p>

    <p><strong>Final Score:</strong> {{ $organization->final_score }}%</p>

    <p>Thank you for your participation.</p>

    <p>Best regards,<br>
    The Evaluation Team</p>
</body>
</html>
