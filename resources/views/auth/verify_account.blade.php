<!DOCTYPE html>
<html lang="en">
@include('admin.header')
<body>
<!-- verification page start-->
<div class="container-fluid p-0">
  <div class="row m-0">
    <div class="col-xl-7 p-0"><img class="bg-img-cover bg-center" src="/design/admin/assets/images/login/1.jpg" alt="looginpage"></div>
    <div class="col-xl-5 p-0">
      <div class="login-card login-dark login-bg">
        <div>
          <div class="login-main">
            <div><a class="logo text-start" href="/"><img class="img-fluid for-light" src="/design/logo/logo_large.png" alt="looginpage" style="height: 50px; width: 150px;"><img class="img-fluid for-dark" src="/design/logo/logo_large.png" alt="looginpage" style="height: 50px; width: 150px;"></a></div>
            @include('alert')
            <form class="theme-form" id="verify_account_form" action="{{ route('admin.verify_account.post') }}" method="POST">
              @csrf
              <h4>Vérification de compte</h4>
              <p>Entrez votre email et le code de vérification reçu</p>
              <div class="form-group">
                <label class="col-form-label">Email</label>
                <input class="form-control" type="email" id="email" name="email" required="" placeholder="votre@email.com" value="{{ $email ?? old('email') }}">
              </div>
              <div class="form-group">
                <label class="col-form-label">Code de vérification</label>
                <div class="form-input position-relative">
                  <input class="form-control" type="text" id="verification_code" name="verification_code" required="" placeholder="Code à 8 caractères" value="{{ old('verification_code') }}" maxlength="8">
                </div>
              </div>
              <div class="form-group mb-0">
                <button class="btn btn-primary btn-block w-100" type="submit">Vérifier mon compte</button>
              </div>
              <p class="mt-4 mb-0 text-center">Vous n'avez pas reçu de code? <a class="ms-2" href="{{ route('admin.register') }}">Réessayer</a></p>
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