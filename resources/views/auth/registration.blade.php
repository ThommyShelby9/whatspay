<!DOCTYPE html>
<html lang="en">
@include('admin.header')
<body>
<!-- login page start-->
<div class="container-fluid p-0">
  <div class="row m-0">
    <div class="col-xl-7 p-0"><img class="bg-img-cover bg-center" src="/design/admin/assets/images/login/1.jpg" alt="looginpage"></div>
    <div class="col-xl-5 p-0">
      <div class="login-card login-dark login-bg">
        <div>
          <div class="login-main">
            <div><a class="logo text-start" href="/"><img class="img-fluid for-light" src="/design/logo/logo_large.png" alt="looginpage" style="height: 50px; width: 150px;"><img class="img-fluid for-dark" src="/design/logo/logo_large.png" alt="looginpage" style="height: 50px; width: 150px;"></a></div>
            @include('alert')
            
            <div class="text-center">
              <h4>Choisissez votre profil</h4>
              <p>Sélectionnez le type de compte que vous souhaitez créer</p>
            </div>
            
            <div class="row mt-5">
              <div class="col-md-6">
                <a href="{{ route('admin.register.diffuseur') }}" class="btn btn-primary btn-lg w-100 mb-3">
                  <i class="fa fa-bullhorn me-2"></i>Diffuseur
                </a>
                <p class="text-center small">Pour les influenceurs WhatsApp qui souhaitent diffuser du contenu</p>
              </div>
              <div class="col-md-6">
                <a href="{{ route('admin.register.annonceur') }}" class="btn btn-success btn-lg w-100 mb-3">
                  <i class="fa fa-briefcase me-2"></i>Annonceur
                </a>
                <p class="text-center small">Pour les entreprises qui souhaitent promouvoir leurs produits/services</p>
              </div>
            </div>
            
            <p class="mt-5 mb-0 text-center">Déjà inscrit(e)?<a class="ms-2" href="{{ route('admin.login') }}">Connexion</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@include('admin.js')
</body>
</html>