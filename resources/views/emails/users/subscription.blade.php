<!DOCTYPE html>
<html lang="en">
<head>
    <title>Subscription Confirmation</title>
</head>
<body>
@if($isCourse)
    <h1>You've Subscribed to a Course!</h1>
    <p>Hello,</p>
    <p>You have successfully subscribed to the course: <strong>{{ $title }}</strong>.</p>
    <p>You can access the course details and materials through the following link:</p>
    <p><a href="{{ $url }}">Access Course</a></p>
@else
    <h1>You've Subscribed to a Learning Path!</h1>
    <p>Hello,</p>
    <p>You have successfully subscribed to the learning path: <strong>{{ $title }}</strong>, which includes multiple
        courses.</p>
    <p>You can access the learning path and its courses through the following link:</p>
    <p><a href="{{ $url }}">Access Learning Path</a></p>
@endif
<p>Thank you for subscribing!</p>
</body>
</html>
