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
        <h1 class="text-4xl md:text-6xl font-bold text-gray-900 mb-6">
          Gagne de l’<span class="text-green-600">argent</span> avec tes <span class="text-green-600">status WhatsApp</span>
        </h1>
        <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
          La première plateforme qui connecte les annonceurs avec les diffuseurs
          pour la distribution de publicités via les Status WhatsApp.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
<a href="{{ URL::route('admin.register.annonceur', [], true, config('app.url')) }}" class="bg-green-600 text-white px-8 py-4 rounded-xl hover:bg-green-700 transition-colors font-semibold text-lg flex items-center justify-center">
  Devenir annonceur
  <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
</a>
<a href="{{ URL::route('admin.register.diffuseur', [], true, config('app.url')) }}" class="bg-red-600 text-white px-8 py-4 rounded-xl hover:bg-red-700 transition-colors font-semibold text-lg flex items-center justify-center">
  Devenir diffuseur
  <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Benefits Section -->
  <section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
          Une solution gagnant-gagnant
        </h2>
        <p class="text-xl text-gray-600">
          Des avantages concrets pour tous les acteurs
        </p>
      </div>

      <div class="grid lg:grid-cols-2 gap-12">
        <!-- Advertisers Benefits -->
        <div class="bg-green-50 rounded-2xl p-8">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
              <i data-lucide="target" class="w-6 h-6 text-green-600"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 ml-4">
              Pour les Annonceurs
            </h3>
          </div>
          <ul class="space-y-4">
            <li class="flex items-start">
              <i data-lucide="check-circle" class="w-6 h-6 text-green-500 mt-1 mr-3 flex-shrink-0"></i>
              <div>
                <h4 class="font-semibold text-gray-900">Ciblage précis</h4>
                <p class="text-gray-600">Touchez exactement votre audience selon âge, ville ou centre d’intérêt.</p>
              </div>
            </li>
            <li class="flex items-start">
              <i data-lucide="check-circle" class="w-6 h-6 text-green-500 mt-1 mr-3 flex-shrink-0"></i>
              <div>
                <h4 class="font-semibold text-gray-900">Résultats mesurables</h4>
                <p class="text-gray-600">Suivez vos campagnes en temps réel.</p>
              </div>
            </li>
            <li class="flex items-start">
              <i data-lucide="check-circle" class="w-6 h-6 text-green-500 mt-1 mr-3 flex-shrink-0"></i>
              <div>
                <h4 class="font-semibold text-gray-900">Paiement équitable</h4>
                <p class="text-gray-600">Ne payez que pour les vues validées.</p>
              </div>
            </li>
            <li class="flex items-start">
              <i data-lucide="check-circle" class="w-6 h-6 text-green-500 mt-1 mr-3 flex-shrink-0"></i>
              <div>
                <h4 class="font-semibold text-gray-900">Diffusion authentique</h4>
                <p class="text-gray-600">Vos pubs partagées par de vrais utilisateurs.</p>
              </div>
            </li>
          </ul>
        </div>

        <!-- Broadcasters Benefits -->
        <div class="bg-red-50 rounded-2xl p-8">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
              <i data-lucide="dollar-sign" class="w-6 h-6 text-red-600"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 ml-4">
              Pour les Diffuseurs
            </h3>
          </div>
          <ul class="space-y-4">
            <li class="flex items-start">
              <i data-lucide="check-circle" class="w-6 h-6 text-red-500 mt-1 mr-3 flex-shrink-0"></i>
              <div>
                <h4 class="font-semibold text-gray-900">Revenus faciles</h4>
                <p class="text-gray-600">Publie et reçois tes revenus.</p>
              </div>
            </li>
            <li class="flex items-start">
              <i data-lucide="check-circle" class="w-6 h-6 text-red-500 mt-1 mr-3 flex-shrink-0"></i>
              <div>
                <h4 class="font-semibold text-gray-900">Annonces adaptées</h4>
                <p class="text-gray-600">tu reçois seulement des pubs qui collent à ton profil.</p>
              </div>
            </li>
            <li class="flex items-start">
              <i data-lucide="check-circle" class="w-6 h-6 text-red-500 mt-1 mr-3 flex-shrink-0"></i>
              <div>
                <h4 class="font-semibold text-gray-900">Paiement rapide</h4>
                <p class="text-gray-600">Encaisse dès validation de tes statuts.</p>
              </div>
            </li>
            <li class="flex items-start">
              <i data-lucide="check-circle" class="w-6 h-6 text-red-500 mt-1 mr-3 flex-shrink-0"></i>
              <div>
                <h4 class="font-semibold text-gray-900">Bonus de parrainage</h4>
                <p class="text-gray-600">Invite tes amis, gagne encore plus.</p>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- How it Works Section -->
  <section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
          Comment ça marche ?
        </h2>
        <p class="text-xl text-gray-600">
          Un processus simple en 3 étapes
        </p>
      </div>
      <div class="grid md:grid-cols-3 gap-8">
        <!-- Step 1 -->
        <div class="text-center">
          <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i data-lucide="users" class="w-10 h-10 text-green-600"></i>
          </div>
          <div class="bg-green-600 text-white w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-4 text-lg font-bold">
            1
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-4">
            Inscrivez-vous en un clic
          </h3>
          <p class="text-gray-600">Créez votre profil, on s’occupe du matching.</p>
        </div>

        <!-- Step 2 -->
        <div class="text-center">
          <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i data-lucide="play" class="w-10 h-10 text-red-600"></i>
          </div>
          <div class="bg-red-600 text-white w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-4 text-lg font-bold">
            2
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-4">
            Diffusez vos Status
          </h3>
          <p class="text-gray-600">Publiez les contenus pub dans vos Status WhatsApp.</p>
        </div>

        <!-- Step 3 -->
        <div class="text-center">
          <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i data-lucide="trending-up" class="w-10 h-10 text-purple-600"></i>
          </div>
          <div class="bg-purple-600 text-white w-8 h-8 rounded-full flex items-center justify-center mx-auto mb-4 text-lg font-bold">
            3
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-4">
            Recevez votre gain
          </h3>
          <p class="text-gray-600">Vérification + Paiement automatique.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Stats Section -->
  <section class="py-20 bg-green-600">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center text-white">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">
          Rejoignez la révolution du marketing mobile
        </h2>
        <p class="text-xl text-green-100 mb-12">
          Des milliers d'utilisateurs nous font déjà confiance
        </p>

        <div class="grid md:grid-cols-4 gap-8">
          <div>
            <div class="text-4xl font-bold mb-2">1000+</div>
            <div class="text-green-100">Diffuseurs actifs</div>
          </div>
          <div>
            <div class="text-4xl font-bold mb-2">500+</div>
            <div class="text-green-100">Annonceurs</div>
          </div>
          <div>
            <div class="text-4xl font-bold mb-2">50K+</div>
            <div class="text-green-100">Vues générées</div>
          </div>
          <div>
            <div class="text-4xl font-bold mb-2">98%</div>
            <div class="text-green-100">Satisfaction</div>
          </div>
        </div>
      </div>
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
