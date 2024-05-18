<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attestation of Completion</title>
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
        .attestation {
            border: 5px solid #C71585;
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
            color: #DB7093;
            font-size: 32px;
            margin: 10px 0;
        }
        h1, h3, p {
            color: #000;
            margin: 5px 0;
        }
        p {
            font-size: 18px;
        }
    </style>
</head>
<body>
<center>
    <div class="attestation">
        <img src="{{ asset('storage/images/lernado.png') }}" alt="Learnado Logo" class="logo">
        <br><br><br>
        <div class="header">
            <h1>Attestation of Completion</h1>
            <img src="https://cdn-icons-png.flaticon.com/512/3965/3965050.png" alt="attestation" width="70px" height="70px">
        </div>
        <div class="content">
            <p>This is awarded to</p>
            <h2 class="participant-name">{{ $user_name }}</h2>
            <p>for successfully completing the learning path</p>
            <p><strong>{{ $learning_path_title }}</strong></p>
            <p>at Learnado on {{ $date }}</p>
        </div>
    </div>
</center>
</body>
</html>
