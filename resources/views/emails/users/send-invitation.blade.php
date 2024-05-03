<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $course->name }} - Invitation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .meeting-details {
            margin-bottom: 20px;
        }

        .button {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            color: #ffffff !important;
            background-color: #4285f4;
            border-radius: 5px;
            text-decoration: none;
        }

        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            color: #777777;
        }
    </style>
</head>
<body>
<div class="meeting-details">
    <h2>{{ $course->name }} - Invitation</h2>
    <p><strong>Date:</strong> {{ date('Y-m-d', strtotime($course->start_time)) }}</p>
    <p><strong>Time:</strong> {{ date('H:i', strtotime($course->start_time)) }}
        - {{ date('H:i', strtotime($course->end_time)) }}</p>
    <a href="{{ $googleMeetLink }}" class="button">Join Google Meet</a>
</div>
<div class="footer">
    <p>This event has been created in your calendar. You can also join using this link: {{ $googleMeetLink }}</p>
    <p>If you're having trouble with the button above, copy and paste the URL below into your web browser.</p>
    <p>{{ $googleMeetLink }}</p>
</div>
</body>
</html>
