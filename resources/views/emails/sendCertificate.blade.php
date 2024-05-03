<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Course Certificate</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #f8f8f8;
            padding: 20px;
            border: 1px solid #ddd;
        }
        h1 {
            color: #444;
        }
    </style>
</head>
<body>
<div class="container">
   <center>
       <img src="https://www.iconpacks.net/icons/1/free-certificate-icon-1356-thumb.png" alt="certificate" width="70px" height="70px" />
    <br>
    <h1>Course Completion Certificate</h1>
   </center>
    <p>Hi {{ $user->first_name }},</p>
    <p>Congratulations on successfully completing the course! Your dedication and effort have paid off, and now you have the certificate to prove it.</p>
    <p>You can download your certificate from the link below:</p>
    <p><a href="{{ asset('storage/' . $pdfPath) }}">Download Certificate</a></p>
    <p>Thank you for participating in our training program. We hope to see you in future courses!</p>
</div>
</body>
</html>
