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
            
            <!-- Circles which indicates the steps of the form:-->
            <div class="text-center"><span class="step"></span><span class="step"></span></div>
            <form class="form-wizard" id="registration_form" action="https://whatspay.africa{{ str_replace('http://whatspay.africa', '', route('admin.register.annonceur.post')) }}" method="POST">
              @csrf
              <h4>Inscription Annonceur</h4>
              <p>Entrez vos informations personnelles pour vous inscrire en tant qu'annonceur</p>
              
              <!-- Première étape: Informations personnelles -->
              <div class="tab">
                <div class="theme-form">
                  <div class="form-group">
                    <label class="col-form-label pt-0">Identité</label>
                    <div class="row g-2">
                      <div class="col-6">
                        <input class="form-control" type="text" id="prenom" name="prenom" required placeholder="Prénom(s)" value="{{ old('prenom') }}">
                      </div>
                      <div class="col-6">    
                        <input class="form-control" type="text" id="nom" name="nom" required placeholder="Nom" value="{{ old('nom') }}">
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-form-label">Contact</label>
                    <div class="input-group">
                      <select class="form-select" id="phonecountry" name="phonecountry">
                        @foreach($viewData["countries"] as $item)
                        <option value="{{$item->id}}"
                                @if($item->id == $viewData["bjId"] || old('phonecountry') == $item->id) selected @endif
                          >{{$item->name}} {{$item->emoji}} ({{$item->phone_code}})</option>
                        @endforeach
                      </select>
                      <input class="form-control" type="number" id="phone" name="phone" required placeholder="0197******" value="{{ old('phone') }}">
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-form-label">Adresse mail</label>
                    <input class="form-control" type="email" id="email" name="email" required placeholder="votre@mail.com" value="{{ old('email') }}">
                  </div>
<div class="form-group">
  <label class="col-form-label">Mot de passe</label>
  <div class="form-input position-relative">
    <input class="form-control" type="password" id="password" name="password" required placeholder="*********">
    <div class="show-hide"><span class="show" data-index="password"></span></div>
  </div>
  <small class="form-text text-muted mt-1">
    Au moins 8 caractères, dont 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial.
  </small>
</div>
<div class="form-group">
  <label class="col-form-label">Confirmation mot de passe</label>
  <div class="form-input position-relative">
    <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" required placeholder="*********">
    <div class="show-hide"><span class="show" data-index="password_confirmation"></span></div>
  </div>
</div>
                </div>
              </div>
              
              <!-- Deuxième étape: Informations complémentaires -->
              <div class="tab">
                <div class="theme-form">
                  <div class="form-group">
                    <label class="col-form-label">Pays de résidence</label>
                    <select class="form-select" id="country" name="country">
                      @foreach($viewData["countries"] as $item)
                      <option value="{{$item->id}}"
                              @if($item->id == $viewData["bjId"] || old('country') == $item->id) selected @endif
                        >{{$item->name}} {{$item->emoji}} ({{$item->phone_code}})</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group">
                    <label class="col-form-label">Localité de résidence</label>
                    <select class="form-select" id="locality" name="locality">
                      @foreach($viewData["localities"] as $item)
                      <option value="{{$item->id}}" @if(old('locality') == $item->id) selected @endif>{{$item->name}}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group">
                    <label class="col-form-label">Profession</label>
                    <div class="form-input position-relative">
                      <select class="form-select" id="occupation" name="occupation">
                        @foreach($viewData["occupations"] as $item)
                        <option value="{{$item->id}}" @if(old('occupation') == $item->id) selected @endif>{{$item->name}}</option>
                        @endforeach
                        <option value="" @if(old('occupation') == '') selected @endif>Autre</option>
                      </select>
                      <div id="autre_occupationDiv" style="margin-top: 10px; display: none;">
                        <input class="form-control" type="text" id="autre_occupation" name="autre_occupation" placeholder="Saisissez votre profession" value="{{ old('autre_occupation') }}">
                      </div>
                    </div>
                  </div>
                  
                  <div class="form-group mb-0">
                    <div class="checkbox p-0">
                      <input id="termes" name="termes" type="checkbox" required @if(old('termes')) checked @endif>
                      <label class="text-muted" for="termes">J'accepte les <a class="ms-2" href="/page/mentions">Termes</a> et <a class="ms-2" href="/page/conditions">Conditions</a></label>
                    </div>
                  </div>
                </div>
              </div>
              
              <div>
                <div class="text-end pt-3">
                  <button class="btn btn-secondary" id="prevBtn" type="button" onclick="nextPrev(-1, 'registration_form')" style="display: none">Précédent</button>
                  <button class="btn btn-primary" id="nextBtn" type="button" onclick="nextPrev(1, 'registration_form')">Suivant</button>
                </div>
              </div>
              
              <p class="mt-4 mb-0 text-center">Déjà inscrit(e)?<a class="ms-2" href="{{ route('admin.login') }}">Connexion</a></p>
              
              <!-- Champs cachés -->
              <input type="hidden" name="profil" value="ANNONCEUR">
              <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  
<input type="hidden" name="form_type" id="form_type" value="registration">
  <input type="hidden" name="bjId" id="bjId" value="{{ $viewData["bjId"] }}">
  <input type="hidden" name="localitiesJson" id="localitiesJson" value="{{ $viewData["localitiesJson"] }}">
  <input type="hidden" name="countriesJson" id="countriesJson" value="{{ $viewData["countriesJson"] }}">
  
@include('admin.js')
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Gestion de "Autre profession"
  const occupationSelect = document.getElementById('occupation');
  const autreOccupationDiv = document.getElementById('autre_occupationDiv');
  const autreOccupation = document.getElementById('autre_occupation');
  
  // Initialiser les états
  checkOccupationOther();
  
  // Ajouter l'écouteur d'événement
  occupationSelect.addEventListener('change', checkOccupationOther);
  
  function checkOccupationOther() {
    if (occupationSelect.value === '') {
      autreOccupationDiv.style.display = 'block';
      autreOccupation.required = true;
    } else {
      autreOccupationDiv.style.display = 'none';
      autreOccupation.required = false;
    }
  }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Gestion du formulaire multi-étapes
  var currentTab = 0; // La première étape est visible par défaut
  
  // Fonction pour afficher correctement le premier onglet au chargement
  function showInitialTab() {
    // Récupérer toutes les étapes
    var tabs = document.getElementsByClassName("tab");
    
    // Masquer toutes les étapes
    for (var i = 0; i < tabs.length; i++) {
      tabs[i].style.display = "none";
    }
    
    // Afficher uniquement la première étape
    if (tabs.length > 0) {
      tabs[0].style.display = "block";
    }
    
    // Mettre à jour les boutons
    updateButtons();
  }
  
  // Mettre à jour l'affichage des boutons
  function updateButtons() {
    var tabs = document.getElementsByClassName("tab");
    var prevBtn = document.getElementById("prevBtn");
    var nextBtn = document.getElementById("nextBtn");
    
    // Afficher/masquer le bouton Précédent
    if (currentTab === 0) {
      prevBtn.style.display = "none";
    } else {
      prevBtn.style.display = "inline";
    }
    
    // Changer le texte du bouton Suivant si on est à la dernière étape
    if (currentTab === (tabs.length - 1)) {
      nextBtn.innerHTML = "Soumettre";
    } else {
      nextBtn.innerHTML = "Suivant";
    }
  }
  
  // Initialiser le formulaire
  showInitialTab();
});
</script>
</body>
</html>