<!DOCTYPE html>
<html lang="en">
@include('admin.header')
<body>
<!-- password reset page start-->
<div class="container-fluid p-0">
  <div class="row m-0">
    <div class="col-xl-7 p-0"><img class="bg-img-cover bg-center" src="/design/admin/assets/images/login/1.jpg" alt="looginpage"></div>
    <div class="col-xl-5 p-0">
      <div class="login-card login-dark login-bg">
        <div>
          <div class="login-main">
            <div><a class="logo text-start" href="/"><img class="img-fluid for-light" src="/design/logo/logo_large.png" alt="looginpage" style="height: 50px; width: 150px;"><img class="img-fluid for-dark" src="/design/logo/logo_large.png" alt="looginpage" style="height: 50px; width: 150px;"></a></div>
            @include('alert')
            <form class="theme-form" id="reset_password_form" action="{{ route('admin.reset_password.post') }}" method="POST">
              @csrf
              <h4>Réinitialisation de mot de passe</h4>
              <p>Entrez votre email, le code reçu et votre nouveau mot de passe</p>
              <div class="form-group">
                <label class="col-form-label">Email</label>
                <input class="form-control" type="email" id="email" name="email" required="" placeholder="votre@email.com" value="{{ old('email') }}">
              </div>
              <div class="form-group">
                <label class="col-form-label">Code de réinitialisation</label>
                <div class="form-input position-relative">
                  <input class="form-control" type="text" id="reset_code" name="reset_code" required="" placeholder="Code à 8 caractères" value="{{ old('reset_code') }}" maxlength="8">
                </div>
              </div>
              <div class="form-group">
                <label class="col-form-label">Nouveau mot de passe</label>
                <div class="form-input position-relative">
                  <input class="form-control" type="password" id="password" name="password" required="" placeholder="*********">
                  <div class="show-hide"><span class="show" data-index="password"></span></div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-form-label">Confirmation du mot de passe</label>
                <div class="form-input position-relative">
                  <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" required="" placeholder="*********">
                  <div class="show-hide"><span class="show" data-index="password_confirmation"></span></div>
                </div>
              </div>
              <div class="form-group mb-0">
                <button class="btn btn-primary btn-block w-100" type="submit">Réinitialiser mon mot de passe</button>
              </div>
              <p class="mt-4 mb-0 text-center">Vous n'avez pas reçu de code? <a class="ms-2" href="{{ route('admin.forgotten_password') }}">Réessayer</a></p>
              <p class="mt-4 mb-0 text-center">Retour à la <a class="ms-2" href="{{ route('admin.login') }}">Connexion</a></p>
              <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@include('admin.js')
</body>
</html>