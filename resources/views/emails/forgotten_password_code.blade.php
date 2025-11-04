<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>{{ $subject }}</title>
    <style>
        /* Inline styles for simplicity, consider using CSS classes for larger templates */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
            background-color: #f1f1f1;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 200px;
        }

        .message {
            padding: 20px;
            background-color: #ffffff;
        }

        .message p {
            margin-bottom: 10px;
        }

        .reset-code {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            color: #3366ff;
            padding: 15px;
            margin: 20px 0;
            background-color: #f5f5f5;
            border-radius: 5px;
            letter-spacing: 3px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>
<div class="container">
    @include('emails.logo')
    <div class="message">
        <p>
            Bonjour {{$firstname}},<br>
            Vous avez demandé à réinitialiser votre mot de passe sur WhatsPAY.<br>
            Pour réinitialiser votre mot de passe, veuillez utiliser le code ci-dessous :
        </p>
        
        <div class="reset-code">
            {{ $reset_code }}
        </div>
        
        <p>
            Vous pouvez saisir ce code sur la page de réinitialisation de mot de passe en vous connectant à <a href="{{$url}}/admin/reset-password" target="_blank">WhatsPAY</a>.<br><br>
            Ce code est valable pendant 1 heure. Si vous n'êtes pas à l'origine de cette demande, veuillez ignorer cet email.<br><br>
            Cordialement,<br>
            L'équipe WhatsPAY <br><br>
        </p>
    </div>
</div>
</body>
</html>