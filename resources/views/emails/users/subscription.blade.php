<!DOCTYPE html>
<html lang="en">
<head>
    <title>Course Subscription Confirmation</title>
</head>
<body>
<h1>You've Subscribed to a Course!</h1>
<p>Hello,</p>
<p>You have successfully subscribed to the course: <strong>{{ $courseTitle }}</strong>.</p>
<p>You can access the course details and materials through the following link:</p>
<p><a href="{{ $courseUrl }}">Access Course</a></p>
<p>Thank you for subscribing!</p>
</body>
</html>
