<!-- File: resources/views/announcer/settings/index.blade.php -->
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
                <li class="breadcrumb-item"><a href="{{ route('admin.client.dashboard') }}">Dashboard</a></li>
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
            <a class="nav-link" id="profile-tab" data-bs-toggle="pill" href="#profile" role="tab" aria-selected="false">
              <i class="fa fa-user me-2"></i> Profil d'entreprise
            </a>
            <a class="nav-link" id="notification-tab" data-bs-toggle="pill" href="#notification" role="tab" aria-selected="false">
              <i class="fa fa-bell me-2"></i> Notifications
            </a>
            <a class="nav-link" id="security-tab" data-bs-toggle="pill" href="#security" role="tab" aria-selected="false">
              <i class="fa fa-shield-alt me-2"></i> Sécurité
            </a>
            <a class="nav-link" id="whatsapp-tab" data-bs-toggle="pill" href="#whatsapp" role="tab" aria-selected="false">
              <i class="fab fa-whatsapp me-2"></i> Configuration WhatsApp
            </a>
            <a class="nav-link" id="api-tab" data-bs-toggle="pill" href="#api" role="tab" aria-selected="false">
              <i class="fa fa-code me-2"></i> API & Intégrations
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
              
              <form method="post" action="{{ route('announcer.settings.update') }}">
                @csrf
                @method('PUT')
                
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
                  <label class="col-md-3 col-form-label">Format monétaire</label>
                  <div class="col-md-9">
                    <select class="form-select" name="currency_format">
                      <option value="F" selected>F CFA (123 456 F)</option>
                      <option value="XOF">XOF (123 456 XOF)</option>
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
            
            <!-- Profil d'entreprise -->
            <div class="tab-pane fade" id="profile" role="tabpanel">
              <h5 class="card-title mb-4">Profil d'entreprise</h5>
              
              <form method="post" action="{{ route('announcer.settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="mb-4 text-center">
                  <div class="position-relative d-inline-block">
                    <img src="{{ asset('images/company-placeholder.png') }}" alt="Logo" class="rounded-circle avatar-xl">
                    <div class="avatar-xs position-absolute bottom-0 end-0">
                      <span class="avatar-title rounded-circle bg-light border border-white">
                        <i class="fa fa-camera text-primary"></i>
                      </span>
                    </div>
                  </div>
                  <input type="file" id="companyLogo" name="company_logo" class="d-none">
                </div>
                
                <div class="row mb-3">
                  <label class="col-md-3 col-form-label">Nom de l'entreprise</label>
                  <div class="col-md-9">
                    <input type="text" class="form-control" name="company_name" value="Acme Corporation">
                  </div>
                </div>
                
                <div class="row mb-3">
                  <label class="col-md-3 col-form-label">Secteur d'activité</label>
                  <div class="col-md-9">
                    <select class="form-select" name="industry">
                      <option value="retail" selected>Commerce de détail</option>
                      <option value="technology">Technologie</option>
                      <option value="healthcare">Santé</option>
                      <option value="finance">Finance</option>
                      <option value="education">Éducation</option>
                      <option value="other">Autre</option>
                    </select>
                  </div>
                </div>
                
                <div class="row mb-3">
                  <label class="col-md-3 col-form-label">Site web</label>
                  <div class="col-md-9">
                    <input type="url" class="form-control" name="website" value="https://www.acmecorp.com">
                  </div>
                </div>
                
                <div class="row mb-3">
                  <label class="col-md-3 col-form-label">Adresse</label>
                  <div class="col-md-9">
                    <textarea class="form-control" name="address" rows="3">123 Avenue Principale, Quartier des Affaires, Cotonou, Bénin</textarea>
                  </div>
                </div>
                
                <div class="row mb-3">
                  <label class="col-md-3 col-form-label">Téléphone</label>
                  <div class="col-md-9">
                    <input type="tel" class="form-control" name="phone" value="+229 97 12 34 56">
                  </div>
                </div>
                
                <div class="row mb-3">
                  <label class="col-md-3 col-form-label">Email</label>
                  <div class="col-md-9">
                    <input type="email" class="form-control" name="email" value="contact@acmecorp.com">
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
              
              <form method="post" action="{{ route('announcer.settings.update') }}">
                @csrf
                @method('PUT')
                
                <h6 class="text-uppercase text-muted mb-3">Notifications Email</h6>
                
                <div class="row mb-3">
                  <div class="col-md-9 offset-md-3">
                    <div class="form-check form-switch mb-2">
                      <input class="form-check-input" type="checkbox" role="switch" id="emailCampaignSwitch" name="email_campaign_status" checked>
                      <label class="form-check-label" for="emailCampaignSwitch">Changement de statut des campagnes</label>
                    </div>
                    
                    <div class="form-check form-switch mb-2">
                      <input class="form-check-input" type="checkbox" role="switch" id="emailInfluencerSwitch" name="email_influencer_submission" checked>
                      <label class="form-check-label" for="emailInfluencerSwitch">Soumissions des diffuseurs</label>
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
                      <input class="form-check-input" type="checkbox" role="switch" id="whatsappCampaignSwitch" name="whatsapp_campaign_status" checked>
                      <label class="form-check-label" for="whatsappCampaignSwitch">Changement de statut des campagnes</label>
                    </div>
                    
                    <div class="form-check form-switch mb-2">
                      <input class="form-check-input" type="checkbox" role="switch" id="whatsappInfluencerSwitch" name="whatsapp_influencer_submission" checked>
                      <label class="form-check-label" for="whatsappInfluencerSwitch">Soumissions des diffuseurs</label>
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
              
              <form method="post" action="{{ route('announcer.settings.update') }}">
                @csrf
                @method('PUT')
                
                <div class="row mb-4">
                  <label class="col-md-3 col-form-label">Modifier le mot de passe</label>
                  <div class="col-md-9">
                    <input type="password" class="form-control mb-2" name="current_password" placeholder="Mot de passe actuel">
                    <input type="password" class="form-control mb-2" name="new_password" placeholder="Nouveau mot de passe">
                    <input type="password" class="form-control" name="confirm_password" placeholder="Confirmez le mot de passe">
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
                            <h6 class="mb-0">Chrome sur Windows</h6>
                            <small class="text-muted">Cotonou, Bénin - IP: 154.72.XX.XX</small>
                            <small class="d-block text-success">Session actuelle</small>
                          </div>
                          <button type="button" class="btn btn-sm btn-light" disabled>Actif</button>
                        </div>
                      </div>
                      <div class="list-group-item">
                        <div class="d-flex align-items-center">
                          <div class="flex-grow-1">
                            <h6 class="mb-0">Safari sur iPhone</h6>
                            <small class="text-muted">Cotonou, Bénin - IP: 102.36.XX.XX</small>
                            <small class="d-block text-muted">Dernière connexion: 28 Oct 2025, 12:42</small>
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
            
            <!-- Configuration WhatsApp -->
            <div class="tab-pane fade" id="whatsapp" role="tabpanel">
              <h5 class="card-title mb-4">Configuration WhatsApp</h5>
              
              <div class="alert alert-info mb-4">
                <h6>Numéros WhatsApp associés</h6>
                <p class="mb-0">Gérez les numéros WhatsApp utilisés pour vos campagnes marketing.</p>
              </div>
              
              <div class="mb-4">
                <div class="d-flex justify-content-between mb-3">
                  <h6>Numéros actifs</h6>
                  <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addWhatsAppModal">
                    <i class="fa fa-plus me-1"></i> Ajouter un numéro
                  </button>
                </div>
                
                <div class="list-group">
                  <div class="list-group-item">
                    <div class="d-flex align-items-center">
                      <div class="flex-shrink-0">
                        <i class="fab fa-whatsapp text-success fa-2x"></i>
                      </div>
                      <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">+229 97 12 34 56</h6>
                        <small class="text-success">Vérifié</small>
                      </div>
                      <div class="dropdown">
                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                          <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu">
                          <li><a class="dropdown-item" href="#">Définir comme principal</a></li>
                          <li><a class="dropdown-item" href="#">Modifier</a></li>
                          <li><a class="dropdown-item text-danger" href="#">Supprimer</a></li>
                        </ul>
                      </div>
                    </div>
                  </div>
                  
                  <div class="list-group-item">
                    <div class="d-flex align-items-center">
                      <div class="flex-shrink-0">
                        <i class="fab fa-whatsapp text-success fa-2x"></i>
                      </div>
                      <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">+229 66 98 76 54</h6>
                        <small class="text-success">Vérifié</small>
                        <small class="badge bg-primary ms-2">Principal</small>
                      </div>
                      <div class="dropdown">
                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                          <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu">
                          <li><a class="dropdown-item" href="#">Modifier</a></li>
                          <li><a class="dropdown-item text-danger" href="#">Supprimer</a></li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="mb-4">
                <h6 class="mb-3">Configuration des messages</h6>
                
                <form method="post" action="{{ route('announcer.settings.update') }}">
                  @csrf
                  @method('PUT')
                  
                  <div class="row mb-3">
                    <label class="col-md-3 col-form-label">Modèle de signature</label>
                    <div class="col-md-9">
<textarea class="form-control" name="whatsapp_signature" rows="2">Envoyé via WhatsPAY - @{{company_name}}</textarea>
<div class="form-text">
    Variables disponibles: @{{company_name}}, @{{website}}, @{{phone}}
</div>
                    </div>
                  </div>
                  
                  <div class="row mb-3">
                    <div class="col-md-9 offset-md-3">
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="whatsappAutoReplySwitch" name="whatsapp_auto_reply" checked>
                        <label class="form-check-label" for="whatsappAutoReplySwitch">Activer les réponses automatiques</label>
                      </div>
                    </div>
                  </div>
                  
                  <div class="row mb-3">
                    <label class="col-md-3 col-form-label">Message de réponse automatique</label>
                    <div class="col-md-9">
                      <textarea class="form-control" name="whatsapp_auto_reply_message" rows="3">Merci de nous avoir contacté. Un membre de notre équipe vous répondra dès que possible.</textarea>
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
            
            <!-- API & Intégrations -->
            <div class="tab-pane fade" id="api" role="tabpanel">
              <h5 class="card-title mb-4">API & Intégrations</h5>
              
              <div class="alert alert-warning mb-4">
                <h6>Fonctionnalité en bêta</h6>
                <p class="mb-0">L'accès à l'API WhatsPAY est actuellement en version bêta. Veuillez contacter notre support pour plus d'informations.</p>
              </div>
              
              <div class="mb-4">
                <h6 class="mb-3">Clés API</h6>
                
                <div class="card bg-light">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <div>
                        <h6 class="mb-0">Clé API de production</h6>
                        <small class="text-muted">Créée le 15/09/2025</small>
                      </div>
                      <button type="button" class="btn btn-sm btn-primary">Générer une nouvelle clé</button>
                    </div>
                    
                    <div class="input-group">
                      <input type="text" class="form-control" value="sk_live_*************************" readonly>
                      <button class="btn btn-outline-secondary" type="button" id="showApiKey">
                        <i class="fa fa-eye"></i>
                      </button>
                      <button class="btn btn-outline-secondary" type="button" id="copyApiKey">
                        <i class="fa fa-copy"></i>
                      </button>
                    </div>
                    
                    <div class="form-text mt-1">
                      <i class="fa fa-exclamation-circle text-warning me-1"></i>
                      Ne partagez jamais votre clé API avec des tiers. Cette clé donne un accès complet à votre compte.
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="mb-4">
                <h6 class="mb-3">Webhooks</h6>
                
                <form method="post" action="{{ route('announcer.settings.update') }}">
                  @csrf
                  @method('PUT')
                  
                  <div class="row mb-3">
                    <label class="col-md-3 col-form-label">URL du webhook</label>
                    <div class="col-md-9">
                      <input type="url" class="form-control" name="webhook_url" placeholder="https://votre-site.com/api/webhook">
                    </div>
                  </div>
                  
                  <div class="row mb-3">
                    <label class="col-md-3 col-form-label">Événements à envoyer</label>
                    <div class="col-md-9">
                      <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="campaignStatusCheck" name="webhook_campaign_status">
                        <label class="form-check-label" for="campaignStatusCheck">Changement de statut des campagnes</label>
                      </div>
                      <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="influencerSubmissionCheck" name="webhook_influencer_submission">
                        <label class="form-check-label" for="influencerSubmissionCheck">Soumissions des diffuseurs</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="paymentConfirmationCheck" name="webhook_payment_confirmation">
                        <label class="form-check-label" for="paymentConfirmationCheck">Confirmation de paiement</label>
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
              
              <div>
                <h6 class="mb-3">Intégrations disponibles</h6>
                
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="card h-100">
                      <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                          <img src="{{ asset('images/integrations/google-analytics.png') }}" alt="Google Analytics" height="40" class="me-3">
                          <h6 class="mb-0">Google Analytics</h6>
                        </div>
                        <p class="card-text">Connectez vos campagnes à Google Analytics pour suivre les performances.</p>
                        <button class="btn btn-outline-primary btn-sm">Configurer</button>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="card h-100">
                      <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                          <img src="{{ asset('images/integrations/zapier.png') }}" alt="Zapier" height="40" class="me-3">
                          <h6 class="mb-0">Zapier</h6>
                        </div>
                        <p class="card-text">Connectez WhatsPAY à plus de 3000 applications via Zapier.</p>
                        <button class="btn btn-outline-primary btn-sm">Configurer</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Ajouter WhatsApp -->
<div class="modal fade" id="addWhatsAppModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ajouter un numéro WhatsApp</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addWhatsAppForm">
          <div class="mb-3">
            <label class="form-label">Indicatif</label>
            <select class="form-select" required>
              <option value="+229" selected>Bénin (+229)</option>
              <option value="+225">Côte d'Ivoire (+225)</option>
              <option value="+233">Ghana (+233)</option>
              <option value="+234">Nigeria (+234)</option>
              <option value="+228">Togo (+228)</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Numéro de téléphone</label>
            <input type="tel" class="form-control" placeholder="97 12 34 56" required>
            <div class="form-text">
              Entrez uniquement les chiffres, sans espaces ni tirets.
            </div>
          </div>
          
          <div class="alert alert-info">
            <small>
              Un code de vérification sera envoyé par SMS à ce numéro. Assurez-vous que WhatsApp est installé et activé sur ce numéro.
            </small>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-primary">Vérifier le numéro</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  $(document).ready(function() {
    // Pour le changement de logo
    $('.avatar-xs').click(function() {
      $('#companyLogo').click();
    });
    
    // Pour afficher la clé API complète
    $('#showApiKey').click(function() {
      const apiKeyInput = $(this).closest('.input-group').find('input');
      
      if (apiKeyInput.val().includes('*')) {
        apiKeyInput.val('sk_live_9a8b7c6d5e4f3g2h1i0j9k8l7m6n5o4p');
        $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
      } else {
        apiKeyInput.val('sk_live_*************************');
        $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
      }
    });
    
    // Pour copier la clé API
    $('#copyApiKey').click(function() {
      const apiKeyInput = $(this).closest('.input-group').find('input');
      
      if (apiKeyInput.val().includes('*')) {
        apiKeyInput.val('sk_live_9a8b7c6d5e4f3g2h1i0j9k8l7m6n5o4p');
      }
      
      apiKeyInput.select();
      document.execCommand('copy');
      
      $(this).find('i').removeClass('fa-copy').addClass('fa-check');
      setTimeout(() => {
        $(this).find('i').removeClass('fa-check').addClass('fa-copy');
      }, 2000);
    });
  });
</script>
@endpush
@endsection