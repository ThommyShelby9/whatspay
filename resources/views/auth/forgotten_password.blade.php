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
            <form class="theme-form" id="forgottenpassword_form" method="post" enctype="multipart/form-data">
              <h3>Mot de passe oubli&eacute;</h3>
              <p>Entrez votre adresse mail pour recevoir le lien de r&eacute;initialisation</p>
              <div class="form-group">
                <label class="col-form-label">Adresse mail</label>
                <input class="form-control" id="email" name="email" type="email" required="" placeholder="votre@mail.com">
              </div>
              <div class="form-group mb-0">
                <div class="text-end mt-3">
                  <button class="btn btn-primary btn-block w-100" type="button" id="forgottenpassword_submit_button" name="forgottenpassword_submit_button">Enregistrer</button>
                </div>
              </div>
              <p class="mt-4 mb-0 text-center">Mot de passe retrouv&eacute;?<a class="ms-2" href="/admin/login">Connexion</a>
                <br>Pas inscrit(e)?<a class="ms-2" href="/admin/registration">Inscrivez vous</a>
              </p>
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
