<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mise à jour de votre profil WhatsPAY</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            margin: 20px 0;
            border-radius: 4px;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <p>Bonsoir {{ $prenom }},</p>
        
        <p>Merci de vous connecter à votre compte WhatsPAY pour mettre à jour vos informations de profil en cliquant sur ce lien :</p>
        
        <div style="text-align: center;">
            <a href="{{ $profileUrl }}" class="btn">ACCEDER À MON PROFIL</a>
        </div>
        
        <p>Les informations à renseigner ou à vérifier concernent notamment :</p>
        
        <p>👉 <strong>Localité de résidence</strong> : indiquez la ville où vous habitez.</p>
        <p>👉 <strong>Nombre de vues moyen journalier</strong> : précisez le nombre moyen de vues que vous obtenez sur un statut en 24h.</p>
        <p>👉 <strong>Catégories de publications</strong> : choisissez les types de contenus qui vous intéressent et que vous souhaitez diffuser.</p>
        
        <p>Ces informations nous permettent de mieux vous connecter aux campagnes adaptées à votre profil.</p>
        
        <p><strong>Merci de le faire dès maintenant !</strong></p>
        
        <div class="footer">
            <p>Bien cordialement,<br>
            L'équipe Whatspay</p>
        </div>
    </div>
</body>
</html>