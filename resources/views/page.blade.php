<!DOCTYPE html>
<html lang="fr">
@include('head')
<body>
<div class="min-h-screen bg-white">
  @include('header')

  <!-- Hero Section -->
  <section class="bg-gradient-to-br from-green-50 to-indigo-100 py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center">
        <h1 class="text-4xl md:text-6xl font-bold text-gray-900 mb-6">{{$titlepage}}</h1>
        <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
          Bienvenue sur WhatsPAY, la première plateforme qui connecte les annonceurs avec les diffuseurs
          pour la distribution de publicités via les Status WhatsApp.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <a href="/admin/registration?profil=annonceur" class="bg-green-600 text-white px-8 py-4 rounded-xl hover:bg-green-700 transition-colors font-semibold text-lg flex items-center justify-center">
            Devenir annonceur
            <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
          </a>
          <a href="/admin/registration?profil=diffuseur" class="bg-red-600 text-white px-8 py-4 rounded-xl hover:bg-red-700 transition-colors font-semibold text-lg flex items-center justify-center">
            Devenir diffuseur
            <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- How it Works Section -->
  <section class="py-20 bg-green-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      @switch($page)
      @case('mentions')
      <div class="text-center">
        <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
        Éditeur du site<br>
        Le site Whatspay.africa est édité par ONE POINT COM,<br>
        Cotonou.<br><br>
        Directeur de publication : Aaron Arwing AGOSSOU<br>
        Contact : support@whatspay.africa<br>
        Téléphone (WhatsApp support) : +229 0196171300<br><br>
        Contacts officiels<br>
        TikTok : @whatspay.africa<br>
        Facebook : Whatspay<br>
        Instagram : Whatspay
        </p>
      </div>
      @break
      @case('politique')

      <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto" style="text-align: justify">
      La présente Politique de Confidentialité décrit la manière dont Whatspay.africa collecte, utilise, conserve et protège les données personnelles des utilisateurs, conformément aux lois et réglementations applicables en matière de protection des données personnelles.<br><br>
      <strong>1. Collecte des données personnelles</strong><br><br>
      Lors de l’inscription et de l’utilisation de la plateforme Whatspay.africa, nous collectons les données suivantes :<br>
      - Adresse e-mail (authentification et communication) ;<br>
      - Nom et prénom ;<br>
      - Région, âge, genre, langue, centres d’intérêts ;<br>
      - Nombre de vues moyen journalier déclaré par l’utilisateur (diffuseurs uniquement) ;<br>
      - Données techniques liées à l’utilisation (logs de connexion, appareil utilisé, heures d’activité).<br><br><br>

      <strong>2. Utilisation des données</strong><br><br>
      Les données collectées sont utilisées afin de :<br>
      - Gérer l’accès et l’utilisation de la plateforme ;<br>
      - Attribuer les campagnes de manière équitable et efficace ;<br>
      - Calculer et distribuer les revenus aux diffuseurs ;<br>
      - Permettre aux annonceurs de cibler leurs audiences ;<br>
      - Assurer le respect des règles de soumission et validation des preuves ;<br>
      - Gérer le programme de parrainage ;<br>
      - Communiquer avec les utilisateurs (notifications, rappels, informations légales).<br><br><br>

      <strong>3. Conservation des données</strong><br><br>
      Les données sont conservées aussi longtemps que le compte de l’utilisateur est actif. En cas de suppression du compte, les données sont supprimées dans un délai maximum de 24 mois, sauf obligations légales de conservation plus longues.<br><br><br>

      <strong>4. Partage des données</strong><br><br>
      Les données collectées ne sont pas revendues à des tiers. Elles sont partagées uniquement :<br>
      - Avec les annonceurs, sous forme de statistiques anonymisées de performance ;<br>
      - Avec les prestataires de paiement pour la gestion des dépôts et retraits ;<br>
      - Avec les autorités compétentes, lorsque la loi l’exige.<br><br><br>

      <strong>5. Droits des utilisateurs</strong><br><br>
      Conformément à la législation applicable, chaque utilisateur dispose des droits suivants :<br>
      - Droit d’accès à ses données personnelles ;<br>
      - Droit de rectification ou suppression ;<br>
      - Droit de limitation ou opposition au traitement ;<br>
      - Droit à la portabilité des données.<br><br>

      Ces droits peuvent être exercés en contactant : support@whatspay.africa.<br><br><br>

      <strong>6. Sécurité des données</strong><br><br>
      Whatspay.africa met en œuvre toutes les mesures techniques et organisationnelles nécessaires pour protéger les données personnelles contre la perte, l’accès non autorisé, la divulgation, l’altération ou la destruction.<br><br><br>

      <strong>7. Modification de la Politique</strong><br><br>
      Whatspay.africa se réserve le droit de modifier la présente Politique de Confidentialité à tout moment. Toute modification sera notifiée aux utilisateurs par e-mail ou via la plateforme.<br><br><br>

      <strong>8. Contact</strong><br><br>
      Pour toute question relative à la présente Politique de Confidentialité, vous pouvez nous contacter à :<br>
      - E-mail : support@whatspay.africa<br>
      - WhatsApp : +229 0196171300<br>
      - Réseaux sociaux officiels : Facebook, Instagram, TikTok (@whatspay.africa).<br><br><br>
      </p>

      @break
      @default
        &nbsp;&nbsp;&nbsp;
      @endswitch
    </div>
  </section>


  @include('cta')
  @include('footer')
</div>

<script>
  lucide.createIcons();
</script>
</body>
</html>
