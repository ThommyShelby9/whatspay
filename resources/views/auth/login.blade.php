<!DOCTYPE html>
<html lang="en">
@include('admin.header')
<body>
<!-- login page start-->
<div class="container-fluid p-0">
  <div class="row m-0">
    <div class="col-12 p-0">
      <div class="login-card login-dark">
        <div>
          <div class="login-main">
            <div><a class="logo" href="/"><img class="img-fluid for-light" src="/design/logo/logo_large.png" alt="looginpage" style="height: 50px; width: 150px;"><img class="img-fluid for-dark" src="/design/logo/logo_large.png" alt="looginpage" style="height: 50px; width: 150px;"></a></div>
            @include('alert')
            <form class="theme-form" id="login_form" method="post" enctype="multipart/form-data">
              <h3>Connectez vous</h3>
              <p>Entrez vos identifiants pour vous connecter</p>
              <div class="form-group">
                <label class="col-form-label">Profil</label>
                <select class="form-select form-control" id="profil" name="profil">
                  <option value="">Indiquez votre profil</option>
                  @foreach($viewData['typeroles'] as $item)
                  <option value="{{$item}}">{{$item}}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group">
                <label class="col-form-label">Adresse mail</label>
                <input class="form-control" id="email" name="email" type="email" required="" placeholder="votre@mail.com">
              </div>
              <div class="form-group">
                <label class="col-form-label">Mot de passe</label>
                <div class="form-input position-relative">
                  <input class="form-control" type="password" name="password" id="password" required="" placeholder="*********">
                  <div class="show-hide"><span class="show" data-index="password"></span></div>
                </div>
              </div>
              <div class="form-group mb-0">
                <div class="checkbox p-0">
                  <input id="rememberMe" name="rememberMe" type="checkbox">
                  <label class="text-muted" for="rememberMe">Souvenez-vous de moi</label>
                </div><a class="link" href="/admin/forgotten_password">Mot de passe oubli&eacute;?</a>
                <div class="text-end mt-3">
                  <button class="btn btn-primary btn-block w-100" type="button" id="login_submit_button" name="login_submit_button">Connexion</button>
                </div>
              </div>
              <p class="mt-4 mb-0 text-center">Pas inscrit(e)?<a class="ms-2" href="/admin/registration">Inscrivez vous</a></p>
              <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@include('admin.js')
</div>
</body>
</html>
