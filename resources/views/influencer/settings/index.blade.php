<!-- File: resources/views/influencer/settings/index.blade.php -->
@extends('admin.layout')

@section('pagecontent')
@include('alert')

<div class="container-fluid">
  <!-- Page Header -->
  <div class="row mb-4">
    <div class="col-sm-12">
      <div class="page-title-box">
        <div class="row align-items-center">
          <div class="col">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('influencer.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Paramètres</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Menu de navigation -->
    <div class="col-md-3 mb-4 mb-md-0">
      <div class="card">
        <div class="card-body p-0">
          <div class="nav flex-column nav-pills" id="settings-tab" role="tablist">
            <a class="nav-link active" id="general-tab" data-bs-toggle="pill" href="#general" role="tab" aria-selected="true">
              <i class="fa fa-cog me-2"></i> Paramètres généraux
            </a>
            <a class="nav-link" id="notification-tab" data-bs-toggle="pill" href="#notification" role="tab" aria-selected="false">
              <i class="fa fa-bell me-2"></i> Notifications
            </a>
            <a class="nav-link" id="security-tab" data-bs-toggle="pill" href="#security" role="tab" aria-selected="false">
              <i class="fa fa-shield-alt me-2"></i> Sécurité
            </a>
            <a class="nav-link" id="payment-tab" data-bs-toggle="pill" href="#payment" role="tab" aria-selected="false">
              <i class="fa fa-money-bill-wave me-2"></i> Méthodes de paiement
            </a>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Contenu des paramètres -->
    <div class="col-md-9">
      <div class="card">
        <div class="card-body">
          <div class="tab-content" id="settings-content">
            <!-- Paramètres généraux -->
            <div class="tab-pane fade show active" id="general" role="tabpanel">
              <h5 class="card-title mb-4">Paramètres généraux</h5>
              
              <form method="post" action="{{ route('influencer.settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="settings_type" value="general">
                
                <div class="row mb-3">
                  <label class="col-md-3 col-form-label">Langue</label>
                  <div class="col-md-9">
                    <select class="form-select" name="language">
                      <option value="fr" selected>Français</option>
                      <option value="en">English</option>
                    </select>
                  </div>
                </div>
                
                <div class="row mb-3">
                  <label class="col-md-3 col-form-label">Fuseau horaire</label>
                  <div class="col-md-9">
                    <select class="form-select" name="timezone">
                      <option value="UTC+0" selected>UTC+0 (GMT)</option>
                      <option value="UTC+1">UTC+1 (Paris, Berlin, Madrid)</option>
                      <option value="UTC+0">UTC+0 (Londres, Dublin)</option>
                      <option value="UTC-5">UTC-5 (New York, Toronto)</option>
                    </select>
                  </div>
                </div>
                
                <div class="row mb-3">
                  <label class="col-md-3 col-form-label">Format de date</label>
                  <div class="col-md-9">
                    <select class="form-select" name="date_format">
                      <option value="DD/MM/YYYY" selected>DD/MM/YYYY</option>
                      <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                      <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                    </select>
                  </div>
                </div>
                
                <div class="row mb-3">
                  <div class="col-md-9 offset-md-3">
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" role="switch" id="darkModeSwitch" name="dark_mode">
                      <label class="form-check-label" for="darkModeSwitch">Activer le mode sombre</label>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-9 offset-md-3">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                  </div>
                </div>
              </form>
            </div>
            
            <!-- Paramètres de notification -->
            <div class="tab-pane fade" id="notification" role="tabpanel">
              <h5 class="card-title mb-4">Paramètres de notification</h5>
              
              <form method="post" action="{{ route('influencer.settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="settings_type" value="notifications">
                
                <h6 class="text-uppercase text-muted mb-3">Notifications Email</h6>
                
                <div class="row mb-3">
                  <div class="col-md-9 offset-md-3">
                    <div class="form-check form-switch mb-2">
                      <input class="form-check-input" type="checkbox" role="switch" id="emailCampaignSwitch" name="email_campaign_invitation" checked>
                      <label class="form-check-label" for="emailCampaignSwitch">Invitations à des campagnes</label>
                    </div>
                    
                    <div class="form-check form-switch mb-2">
                      <input class="form-check-input" type="checkbox" role="switch" id="emailStatusSwitch" name="email_status_update" checked>
                      <label class="form-check-label" for="emailStatusSwitch">Changements de statut des missions</label>
                    </div>
                    
                    <div class="form-check form-switch mb-2">
                      <input class="form-check-input" type="checkbox" role="switch" id="emailMessageSwitch" name="email_new_message" checked>
                      <label class="form-check-label" for="emailMessageSwitch">Nouveaux messages</label>
                    </div>
                    
                    <div class="form-check form-switch mb-2">
                      <input class="form-check-input" type="checkbox" role="switch" id="emailPaymentSwitch" name="email_payment_confirmation" checked>
                      <label class="form-check-label" for="emailPaymentSwitch">Confirmation de paiement</label>
                    </div>
                    
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" role="switch" id="emailPromoSwitch" name="email_promotional">
                      <label class="form-check-label" for="emailPromoSwitch">Offres promotionnelles et actualités</label>
                    </div>
                  </div>
                </div>
                
                <hr class="my-4">
                
                <h6 class="text-uppercase text-muted mb-3">Notifications WhatsApp</h6>
                
                <div class="row mb-3">
                  <div class="col-md-9 offset-md-3">
                    <div class="form-check form-switch mb-2">
                      <input class="form-check-input" type="checkbox" role="switch" id="whatsappCampaignSwitch" name="whatsapp_campaign_invitation" checked>
                      <label class="form-check-label" for="whatsappCampaignSwitch">Invitations à des campagnes</label>
                    </div>
                    
                    <div class="form-check form-switch mb-2">
                      <input class="form-check-input" type="checkbox" role="switch" id="whatsappStatusSwitch" name="whatsapp_status_update" checked>
                      <label class="form-check-label" for="whatsappStatusSwitch">Changements de statut des missions</label>
                    </div>
                    
                    <div class="form-check form-switch mb-2">
                      <input class="form-check-input" type="checkbox" role="switch" id="whatsappMessageSwitch" name="whatsapp_new_message">
                      <label class="form-check-label" for="whatsappMessageSwitch">Nouveaux messages</label>
                    </div>
                    
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" role="switch" id="whatsappPaymentSwitch" name="whatsapp_payment_confirmation" checked>
                      <label class="form-check-label" for="whatsappPaymentSwitch">Confirmation de paiement</label>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-9 offset-md-3">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                  </div>
                </div>
              </form>
            </div>
            
            <!-- Sécurité -->
            <div class="tab-pane fade" id="security" role="tabpanel">
              <h5 class="card-title mb-4">Sécurité</h5>
              
              <form method="post" action="{{ route('influencer.settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="settings_type" value="security">
                
                <div class="row mb-4">
                  <label class="col-md-3 col-form-label">Modifier le mot de passe</label>
                  <div class="col-md-9">
                    <input type="password" class="form-control mb-2" name="current_password" placeholder="Mot de passe actuel">
                    <input type="password" class="form-control mb-2" name="new_password" placeholder="Nouveau mot de passe">
                    <input type="password" class="form-control" name="new_password_confirmation" placeholder="Confirmez le mot de passe">
                    <div class="form-text mt-1">
                      Le mot de passe doit contenir au moins 8 caractères, incluant des lettres majuscules, minuscules, des chiffres et des caractères spéciaux.
                    </div>
                  </div>
                </div>
                
                <div class="row mb-4">
                  <label class="col-md-3 col-form-label">Authentification à deux facteurs</label>
                  <div class="col-md-9">
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" role="switch" id="twoFactorSwitch" name="two_factor_enabled">
                      <label class="form-check-label" for="twoFactorSwitch">Activer l'authentification à deux facteurs</label>
                    </div>
                    <div class="form-text mt-1">
                      Renforcez la sécurité de votre compte en exigeant une vérification à deux facteurs lors de la connexion.
                    </div>
                  </div>
                </div>
                
                <div class="row mb-4">
                  <label class="col-md-3 col-form-label">Sessions actives</label>
                  <div class="col-md-9">
                    <div class="list-group">
                      <div class="list-group-item">
                        <div class="d-flex align-items-center">
                          <div class="flex-grow-1">
                            <h6 class="mb-0">Chrome sur Android</h6>
                            <small class="text-muted">Cotonou, Bénin - IP: 154.72.XX.XX</small>
                            <small class="d-block text-success">Session actuelle</small>
                          </div>
                          <button type="button" class="btn btn-sm btn-light" disabled>Actif</button>
                        </div>
                      </div>
                      <div class="list-group-item">
                        <div class="d-flex align-items-center">
                          <div class="flex-grow-1">
                            <h6 class="mb-0">Firefox sur Windows</h6>
                            <small class="text-muted">Cotonou, Bénin - IP: 102.36.XX.XX</small>
                            <small class="d-block text-muted">Dernière connexion: 25 Oct 2025, 18:30</small>
                          </div>
                          <button type="button" class="btn btn-sm btn-danger">Déconnecter</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="row mb-4">
                  <div class="col-md-9 offset-md-3">
                    <button type="button" class="btn btn-danger">Déconnecter toutes les autres sessions</button>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-9 offset-md-3">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                  </div>
                </div>
              </form>
            </div>
            
            <!-- Méthodes de paiement -->
            <div class="tab-pane fade" id="payment" role="tabpanel">
              <h5 class="card-title mb-4">Méthodes de paiement</h5>
              
              <div class="alert alert-info mb-4">
                <h6>Comment recevez-vous vos paiements</h6>
                <p class="mb-0">Configurez vos méthodes de paiement préférées pour recevoir vos gains.</p>
              </div>
              
              <form method="post" action="{{ route('influencer.settings.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="settings_type" value="payment">
                
                <div class="row mb-4">
                  <label class="col-md-3 col-form-label">Méthode par défaut</label>
                  <div class="col-md-9">
                    <select class="form-select" name="default_payment_method">
                      <option value="mobile_money" selected>Mobile Money</option>
                      <option value="bank">Virement bancaire</option>
                    </select>
                  </div>
                </div>
                
                <!-- Mobile Money -->
                <h6 class="text-uppercase text-muted mb-3">Mobile Money</h6>
                
                <div class="row mb-3">
                  <label class="col-md-3 col-form-label">Opérateur</label>
                  <div class="col-md-9">
                    <select class="form-select" name="mobile_money_operator">
                      <option value="mtn" selected>MTN Mobile Money</option>
                      <option value="moov">Moov Money</option>
                      <option value="orange">Orange Money</option>
                    </select>
                  </div>
                </div>
                
                <div class="row mb-4">
                  <label class="col-md-3 col-form-label">Numéro</label>
                  <div class="col-md-9">
                    <input type="tel" class="form-control" name="mobile_money_number" placeholder="Ex: 97 12 34 56">
                  </div>
                </div>
                
                <!-- Compte bancaire -->
                <h6 class="text-uppercase text-muted mb-3">Compte bancaire</h6>
                
                <div class="row mb-3">
                  <label class="col-md-3 col-form-label">Nom de la banque</label>
                  <div class="col-md-9">
                    <input type="text" class="form-control" name="bank_name" placeholder="Ex: ECOBANK">
                  </div>
                </div>
                
                <div class="row mb-3">
                  <label class="col-md-3 col-form-label">Titulaire du compte</label>
                  <div class="col-md-9">
                    <input type="text" class="form-control" name="bank_account_holder" placeholder="Nom et prénom du titulaire">
                  </div>
                </div>
                
                <div class="row mb-3">
                  <label class="col-md-3 col-form-label">Numéro IBAN</label>
                  <div class="col-md-9">
                    <input type="text" class="form-control" name="bank_iban" placeholder="Ex: BJ00 1234 5678 9012 3456 7890 123">
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-9 offset-md-3">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection