<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Completion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #FFF;
            padding: 40px;
            margin: 0;
            line-height: 1.6;
            height: 120vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .certificate {
            border: 5px solid #E9C874;
            padding: 20px;
            width: 600px;
            height: 450px;
            position: relative;
            background-color: #FFF;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }
        .logo {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 150px;
        }
        .header {
            font-family: Georgia, serif;
            text-align: center;
            margin-top: 30px;
        }
        .content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .participant-name {
            font-family: cursive;
            color: #A34343;
            font-size: 32px;
            margin: 10px 0;
        }
        h1, h3, p {
            color: #000;
            margin: 5px 0;
        }
        p{
            font-size: 18px;
        }


    </style>
</head>
<body>
<center>
<div class="certificate">
    <img src="{{ asset('storage/images/lernado.png') }}" alt="Learnado Logo" class="logo" >
    <br><br><br>
    <div class="header">
        <h1>Certificate of Completion</h1>
        <img src="https://www.iconpacks.net/icons/1/free-certificate-icon-1356-thumb.png" alt="certificate" width="70px" height="70px" />
    </div>
    <div class="content">
        <p>This is awarded to</p>
        <h2 class="participant-name">{{ $user_name }}</h2>
        <p>for completing the course of study in</p>
        <p><strong>{{ $title }}</strong></p>
        <p>at Learnado</p>
    </div>
</div>
</center>
</body>
</html>
