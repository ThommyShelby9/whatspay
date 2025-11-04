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
            Hello {{$firstname}},<br>
            Le mot de passe de votre compte a &eacute;t&eacute; mis &agrave; jour avec succ&egrave;s.<br><br>
            Cordialement,<br>
            L'&eacute;quipe. <br><br>
        </p>
    </div>

</div>
</body>

</html>
