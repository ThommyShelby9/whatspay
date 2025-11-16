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
                <li class="breadcrumb-item active">WhatsApp</li>
              </ol>
            </nav>
          </div>
          <div class="col-auto">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPhoneModal">
              <i class="fa fa-plus me-1"></i> Ajouter un numéro
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Informations WhatsApp -->
  <div class="row mb-4">
    <div class="col-xl-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="avatar-lg bg-soft-primary rounded">
                <i class="fab fa-whatsapp text-primary font-size-24 m-4"></i>
              </div>
            </div>
            <div class="flex-grow-1 ms-4">
              <h4>Configuration WhatsApp</h4>
              <p class="text-muted mb-3">
                WhatsApp est utilisé pour recevoir des notifications sur vos campagnes et pour communiquer avec les annonceurs.
              </p>
              <div class="alert alert-info mb-0">
                <i class="fa fa-info-circle me-2"></i>
                Pour recevoir des missions et des notifications, vous devez avoir au moins un numéro WhatsApp vérifié.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Liste des numéros WhatsApp -->
  <div class="row">
    <div class="col-xl-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Mes numéros WhatsApp</h5>
        </div>
        <div class="card-body">
          @if(!empty($viewData["whatsappNumbers"]) && $viewData["whatsappNumbers"]->count() > 0)
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>Numéro</th>
                    <th>Statut</th>
                    <th>Ajouté le</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($viewData["whatsappNumbers"] as $number)
                  <tr>
                    <td>
                      <div class="d-flex align-items-center">
                        <i class="fab fa-whatsapp text-success me-2"></i>
                        <span>{{ $number->phone_code ?? '' }} {{ $number->phone }}</span>
                      </div>
                    </td>
                    <td>
                      @if($number->status == 'ACTIVE')
                      <span class="badge bg-success">Vérifié</span>
                      @elseif($number->status == 'PENDING')
                      <span class="badge bg-warning">En attente</span>
                      @else
                      <span class="badge bg-danger">Non vérifié</span>
                      @endif
                    </td>
                    <td>{{ date('d/m/Y H:i', strtotime($number->created_at)) }}</td>
                    <td>
                      <div class="btn-group">
                        @if($number->status == 'PENDING')
                        <button type="button" class="btn btn-success btn-sm verify-number me-2" data-id="{{ $number->id }}" data-bs-toggle="modal" data-bs-target="#verifyModal">
                          <i class="fa fa-check"></i> Vérifier
                        </button>
                        @endif
                        
                        <form method="post" action="{{ route('influencer.whatsapp.delete', $number->id) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce numéro?');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fa fa-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="text-center py-4">
              <img src="{{ asset('images/empty-state.svg') }}" alt="Aucun numéro" height="120" class="mb-3">
              <h5>Aucun numéro WhatsApp</h5>
              <p class="text-muted mb-4">Vous n'avez pas encore ajouté de numéro WhatsApp.</p>
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPhoneModal">
                <i class="fa fa-plus me-1"></i> Ajouter un numéro
              </button>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <!-- Guide d'utilisation -->
  <div class="row">
    <div class="col-xl-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Guide d'utilisation WhatsApp</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="d-flex mb-4">
                <div class="flex-shrink-0">
                  <div class="avatar-sm bg-soft-primary rounded-circle">
                    <span class="avatar-title bg-soft-primary text-primary">1</span>
                  </div>
                </div>
                <div class="flex-grow-1 ms-3">
                  <h5>Ajouter un numéro</h5>
                  <p class="text-muted mb-0">Cliquez sur "Ajouter un numéro" et suivez les instructions pour enregistrer votre numéro WhatsApp.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex mb-4">
                <div class="flex-shrink-0">
                  <div class="avatar-sm bg-soft-primary rounded-circle">
                    <span class="avatar-title bg-soft-primary text-primary">2</span>
                  </div>
                </div>
                <div class="flex-grow-1 ms-3">
                  <h5>Vérifier votre numéro</h5>
                  <p class="text-muted mb-0">Saisissez le code de vérification reçu sur WhatsApp pour confirmer votre numéro.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex mb-4">
                <div class="flex-shrink-0">
                  <div class="avatar-sm bg-soft-primary rounded-circle">
                    <span class="avatar-title bg-soft-primary text-primary">3</span>
                  </div>
                </div>
                <div class="flex-grow-1 ms-3">
                  <h5>Utiliser WhatsApp pour les campagnes</h5>
                  <p class="text-muted mb-0">Vous recevrez des notifications sur votre WhatsApp pour les nouvelles campagnes et les mises à jour importantes.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex mb-4">
                <div class="flex-shrink-0">
                  <div class="avatar-sm bg-soft-primary rounded-circle">
                    <span class="avatar-title bg-soft-primary text-primary">4</span>
                  </div>
                </div>
                <div class="flex-grow-1 ms-3">
                  <h5>Gérer vos numéros</h5>
                  <p class="text-muted mb-0">Vous pouvez ajouter plusieurs numéros WhatsApp pour une plus grande flexibilité dans la réception des notifications.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Ajouter un numéro -->
<div class="modal fade" id="addPhoneModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ajouter un numéro WhatsApp</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addPhoneForm" method="post" action="{{ route('influencer.whatsapp.add') }}">
          @csrf
          
          <div class="mb-3">
            <label class="form-label">Indicatif</label>
            <select class="form-select" name="country_code" required>
              @foreach($viewData["countries"] as $country)
                <option value="{{ $country->phone_code }}">{{ $country->name }} ({{ $country->phone_code }})</option>
              @endforeach
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Numéro de téléphone</label>
            <input type="tel" class="form-control" name="phone_number" placeholder="97 12 34 56" required>
            <div class="form-text">
              Entrez uniquement les chiffres, sans espaces ni tirets.
            </div>
          </div>
          
          <div class="alert alert-info">
            <small>
              Un code de vérification sera envoyé sur votre WhatsApp. Assurez-vous que WhatsApp est bien installé et activé sur ce numéro.
            </small>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary" id="addPhoneSubmit" form="addPhoneForm">Ajouter le numéro</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Vérifier un numéro - Version améliorée -->
<div class="modal fade" id="verifyModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Vérifier votre numéro WhatsApp</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="verification-status-messages">
          <!-- Les messages de statut seront insérés ici par JavaScript -->
        </div>
        
        <div class="text-center mb-4">
          <p class="text-muted mb-2">Saisissez le code à 6 chiffres envoyé sur WhatsApp</p>
        </div>
        
        <form id="verifyForm" method="post" action="{{ route('influencer.whatsapp.verify') }}">
          @csrf
          
          <input type="hidden" name="phone_id" id="verify-phone-id">
          
<div class="mb-4">
  <label class="form-label text-muted mb-2">Code de vérification reçu sur WhatsApp</label>
  
  <div class="text-center mt-3">
    <div class="d-inline-flex gap-2 flex-nowrap justify-content-center">
      <input type="text" maxlength="1" class="form-control code-input text-center" data-index="0" 
             style="width: 45px !important; height: 55px !important; font-size: 1.4rem !important; border-radius: 8px;">
      <input type="text" maxlength="1" class="form-control code-input text-center" data-index="1" 
             style="width: 45px !important; height: 55px !important; font-size: 1.4rem !important; border-radius: 8px;">
      <input type="text" maxlength="1" class="form-control code-input text-center" data-index="2" 
             style="width: 45px !important; height: 55px !important; font-size: 1.4rem !important; border-radius: 8px;">
      <input type="text" maxlength="1" class="form-control code-input text-center" data-index="3" 
             style="width: 45px !important; height: 55px !important; font-size: 1.4rem !important; border-radius: 8px;">
      <input type="text" maxlength="1" class="form-control code-input text-center" data-index="4" 
             style="width: 45px !important; height: 55px !important; font-size: 1.4rem !important; border-radius: 8px;">
      <input type="text" maxlength="1" class="form-control code-input text-center" data-index="5" 
             style="width: 45px !important; height: 55px !important; font-size: 1.4rem !important; border-radius: 8px;">
    </div>
  </div>
  
  <input type="hidden" name="verification_code" id="verification-code">
</div>
          
          <div class="text-center mb-3">
            <div class="timer-container">
              <i class="fas fa-clock text-muted me-1"></i>
              <span id="timer" class="timer-display">01:00</span>
            </div>
          </div>
          
          <div class="text-center">
            <button type="button" class="btn btn-link text-decoration-none" id="resendCode" disabled>
              <i class="fas fa-paper-plane me-1"></i>
              Renvoyer le code
            </button>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary" id="verifySubmit" form="verifyForm" disabled>
          <i class="fas fa-check me-1"></i>
          Vérifier
        </button>
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
/* Styles pour le conteneur de vérification */
.verification-code-container {
  display: flex;
  justify-content: center;
  margin: 1.5rem 0;
}

.verification-code-input {
  display: flex;
  gap: 12px;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
}

/* Styles pour les champs individuels */
.code-input {
  width: 50px !important;
  height: 60px !important;
  text-align: center;
  font-size: 1.5rem;
  font-weight: 600;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  background-color: #f8f9fa;
  transition: all 0.2s ease;
  outline: none;
  box-shadow: none;
  padding: 0;
  margin: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* États des champs */
.code-input:focus {
  border-color: #0d6efd;
  background-color: #ffffff;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
  transform: translateY(-1px);
}

.code-input:hover:not(:focus) {
  border-color: #adb5bd;
  background-color: #ffffff;
}

.code-input.filled {
  border-color: #198754;
  background-color: #f8fff9;
  color: #198754;
}

.code-input.error {
  border-color: #dc3545;
  background-color: #fff8f8;
  color: #dc3545;
  animation: shake 0.5s ease-in-out;
}

/* Animation de secousse pour les erreurs */
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}

/* Animation de validation */
.code-input.success {
  border-color: #198754;
  background-color: #d1e7dd;
  animation: pulse-success 0.6s ease;
}

@keyframes pulse-success {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

/* Style du timer */
.timer-container {
  display: inline-flex;
  align-items: center;
  padding: 8px 16px;
  background-color: #f8f9fa;
  border-radius: 20px;
  border: 1px solid #dee2e6;
}

.timer-display {
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  font-weight: 600;
  font-size: 0.95rem;
  color: #495057;
}

.timer-display.warning {
  color: #fd7e14;
}

.timer-display.danger {
  color: #dc3545;
}

/* Responsive design */
@media (max-width: 576px) {
  .verification-code-input {
    gap: 8px;
  }
  
  .code-input {
    width: 40px !important;
    height: 50px !important;
    font-size: 1.3rem;
  }
}

/* Style pour le bouton de renvoi */
#resendCode:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

#resendCode:not(:disabled):hover {
  color: #0d6efd !important;
}

/* Loading state pour le bouton de vérification */
#verifySubmit.loading {
  position: relative;
  color: transparent !important;
}

#verifySubmit.loading::after {
  content: "";
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 16px;
  height: 16px;
  border: 2px solid transparent;
  border-top: 2px solid #ffffff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: translate(-50%, -50%) rotate(0deg); }
  100% { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Anciens styles conservés pour compatibilité */
.btn-spinner {
  display: inline-block;
  width: 1em;
  height: 1em;
  vertical-align: middle;
  border: 0.125em solid currentColor;
  border-right-color: transparent;
  border-radius: 50%;
  animation: spinner-border 0.75s linear infinite;
}

@keyframes spinner-border {
  to { transform: rotate(360deg); }
}

#verification-status-messages .alert {
  margin-bottom: 15px;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
  // Initialiser la gestion des codes de vérification
  initVerificationCode();
  
  // Ajout d'un spinner lors de la soumission du formulaire d'ajout
  $('#addPhoneForm').on('submit', function() {
    $('#addPhoneSubmit').prop('disabled', true);
    $('#addPhoneSubmit').html('<i class="fa fa-spinner fa-spin me-1"></i> Traitement...');
  });
  
  // Vérifier automatiquement l'état d'envoi du code après ajout de numéro
  @if(session('phone_id'))
    var phoneId = "{{ session('phone_id') }}";
    
    // Afficher immédiatement le modal de vérification
    $(document).ready(function() {
      // Ouvrir le modal
      $('#verifyModal').modal('show');
      
      // Définir l'ID du téléphone
      $('#verify-phone-id').val(phoneId);
      
      // Afficher un message d'attente
      $('#verification-status-messages').html(`
        <div class="alert alert-info">
          <i class="fa fa-info-circle me-2"></i>
          Veuillez patienter pendant l'envoi du code via WhatsApp...
        </div>
      `);
      
      // Afficher un message d'aide après 30 secondes
      setTimeout(() => {
        if ($('#verification-code').val().length === 0) {
          $('#verification-status-messages').html(`
            <div class="alert alert-warning">
              <i class="fa fa-exclamation-triangle me-2"></i>
              <strong>Vous n'avez pas reçu le code?</strong>
              <ul class="mb-0 mt-2">
                <li>Vérifiez que WhatsApp est bien installé sur votre téléphone</li>
                <li>Vérifiez que votre numéro est correct</li>
                <li>Essayez de cliquer sur "Renvoyer le code"</li>
              </ul>
            </div>
          `);
          
          // Activer le bouton de renvoi
          $('#resendCode').prop('disabled', false);
          $('#timer').text('');
        }
      }, 30000);
    });
  @endif
  
  // Verify number modal
  $('.verify-number').click(function() {
    const phoneId = $(this).data('id');
    $('#verify-phone-id').val(phoneId);
    
    // Réinitialiser les messages de statut
    $('#verification-status-messages').empty();
  });
});

function initVerificationCode() {
  const codeInputs = $('.code-input');
  const hiddenCodeInput = $('#verification-code');
  const verifyButton = $('#verifySubmit');
  const resendButton = $('#resendCode');
  let timer = null;
  let timeLeft = 60; // 60 secondes
  
  // Initialiser les événements pour chaque champ
  codeInputs.each(function(index) {
    const input = $(this);
    
    // Événement de saisie
    input.on('input', function(e) {
      handleInput(e, index);
    });
    
    // Événement de touches spéciales
    input.on('keydown', function(e) {
      handleKeydown(e, index);
    });
    
    // Événement de collage
    input.on('paste', function(e) {
      handlePaste(e, index);
    });
    
    // Focus et blur pour les animations
    input.on('focus', function() {
      this.select();
    });
    
    input.on('blur', function() {
      if (this.value) {
        $(this).addClass('filled');
      } else {
        $(this).removeClass('filled');
      }
    });
  });
  
  // Gérer la saisie
  function handleInput(e, index) {
    const input = $(e.target);
    const value = input.val();
    
    // Permettre seulement les chiffres
    if (!/^\d*$/.test(value)) {
      input.val('');
      showInputError(input);
      return;
    }
    
    // Si un chiffre est saisi
    if (value.length === 1) {
      input.addClass('filled');
      input.removeClass('error');
      
      // Passer au champ suivant
      if (index < codeInputs.length - 1) {
        codeInputs.eq(index + 1).focus();
      }
    }
    
    updateHiddenInput();
    updateVerifyButton();
  }
  
  // Gérer les touches spéciales
  function handleKeydown(e, index) {
    const input = $(e.target);
    
    switch(e.key) {
      case 'Backspace':
        if (input.val() === '' && index > 0) {
          // Revenir au champ précédent
          codeInputs.eq(index - 1).focus();
          codeInputs.eq(index - 1).val('');
          codeInputs.eq(index - 1).removeClass('filled');
        } else {
          input.removeClass('filled error');
        }
        updateHiddenInput();
        updateVerifyButton();
        break;
        
      case 'ArrowLeft':
        if (index > 0) {
          codeInputs.eq(index - 1).focus();
        }
        e.preventDefault();
        break;
        
      case 'ArrowRight':
        if (index < codeInputs.length - 1) {
          codeInputs.eq(index + 1).focus();
        }
        e.preventDefault();
        break;
        
      case 'Delete':
        input.val('');
        input.removeClass('filled error');
        updateHiddenInput();
        updateVerifyButton();
        break;
    }
  }
  
  // Gérer le collage de code complet
  function handlePaste(e, index) {
    e.preventDefault();
    const pastedText = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
    const digits = pastedText.replace(/\D/g, '').slice(0, 6);
    
    if (digits.length === 6) {
      // Remplir tous les champs
      digits.split('').forEach((digit, i) => {
        if (i < codeInputs.length) {
          codeInputs.eq(i).val(digit);
          codeInputs.eq(i).addClass('filled');
          codeInputs.eq(i).removeClass('error');
        }
      });
      
      // Focuser le dernier champ
      codeInputs.eq(codeInputs.length - 1).focus();
      
      updateHiddenInput();
      updateVerifyButton();
    }
  }
  
  // Mettre à jour le champ caché
  function updateHiddenInput() {
    let code = '';
    codeInputs.each(function() {
      code += $(this).val();
    });
    hiddenCodeInput.val(code);
  }
  
  // Mettre à jour le bouton de vérification
  function updateVerifyButton() {
    let code = '';
    codeInputs.each(function() {
      code += $(this).val();
    });
    verifyButton.prop('disabled', code.length !== 6);
  }
  
  // Afficher une erreur de saisie
  function showInputError(input) {
    input.addClass('error');
    setTimeout(() => {
      input.removeClass('error');
    }, 500);
  }
  
  // Démarrer le timer
  function startTimer() {
    const timerDisplay = $('#timer');
    resendButton.prop('disabled', true);
    
    timer = setInterval(function() {
      timeLeft--;
      
      const minutes = Math.floor(timeLeft / 60);
      const seconds = timeLeft % 60;
      const formattedTime = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
      
      timerDisplay.text(formattedTime);
      
      // Changer la couleur selon le temps restant
      if (timeLeft <= 10) {
        timerDisplay.addClass('danger');
        timerDisplay.removeClass('warning');
      } else if (timeLeft <= 30) {
        timerDisplay.addClass('warning');
        timerDisplay.removeClass('danger');
      } else {
        timerDisplay.removeClass('warning danger');
      }
      
      if (timeLeft <= 0) {
        clearInterval(timer);
        resendButton.prop('disabled', false);
        timerDisplay.text("00:00");
      }
    }, 1000);
  }
  
  // Afficher erreur sur tous les champs
  function showAllInputsError() {
    codeInputs.each(function() {
      $(this).addClass('error');
    });
    
    setTimeout(() => {
      codeInputs.each(function() {
        $(this).removeClass('error');
      });
    }, 500);
  }
  
  // Gestion des événements du modal
  $('#verifyModal').on('shown.bs.modal', function() {
    // Reset au début
    codeInputs.each(function() {
      $(this).val('');
      $(this).removeClass('filled error success');
    });
    
    updateHiddenInput();
    updateVerifyButton();
    
    // Démarrer le timer
    timeLeft = 60;
    startTimer();
    
    // Focuser le premier champ avec un léger délai
    setTimeout(() => {
      codeInputs.eq(0).focus();
    }, 300);
  });
  
  $('#verifyModal').on('hidden.bs.modal', function() {
    if (timer) {
      clearInterval(timer);
    }
    verifyButton.removeClass('loading');
    verifyButton.prop('disabled', false);
  });
  
  // Resend code
  resendButton.on('click', function() {
    const phoneId = $('#verify-phone-id').val();
    
    // Reset des champs
    codeInputs.each(function() {
      $(this).val('');
      $(this).removeClass('filled error success');
    });
    
    updateHiddenInput();
    updateVerifyButton();
    
    // Désactiver le bouton et ajouter un indicateur de chargement
    resendButton.prop('disabled', true);
    resendButton.html('<i class="fa fa-spinner fa-spin"></i> Envoi en cours...');
    
    // Afficher un message d'attente
    $('#verification-status-messages').html(`
      <div class="alert alert-info">
        <i class="fa fa-info-circle me-2"></i>
        Envoi d'un nouveau code en cours...
      </div>
    `);
    
    // AJAX call to resend code
    $.ajax({
      url: "{{ route('influencer.whatsapp.resend') }}",
      type: "POST",
      data: {
        _token: "{{ csrf_token() }}",
        phone_id: phoneId
      },
      success: function(response) {
        if (response.success) {
          // Afficher un message de succès
          $('#verification-status-messages').html(`
            <div class="alert alert-success">
              <i class="fa fa-check-circle me-2"></i>
              Code renvoyé avec succès. Veuillez vérifier votre WhatsApp.
            </div>
          `);
          
          // Reset timer
          timeLeft = 60;
          resendButton.html('<i class="fas fa-paper-plane me-1"></i> Renvoyer le code');
          startTimer();
          
          // Focuser le premier champ
          codeInputs.eq(0).focus();
        } else {
          // Afficher un message d'erreur
          $('#verification-status-messages').html(`
            <div class="alert alert-danger">
              <i class="fa fa-exclamation-circle me-2"></i>
              ${response.message || 'Une erreur est survenue lors du renvoi du code'}
            </div>
          `);
          resendButton.prop('disabled', false);
          resendButton.html('<i class="fas fa-paper-plane me-1"></i> Renvoyer le code');
        }
      },
      error: function(xhr) {
        // Afficher un message d'erreur
        $('#verification-status-messages').html(`
          <div class="alert alert-danger">
            <i class="fa fa-exclamation-circle me-2"></i>
            Erreur de connexion. Veuillez réessayer.
          </div>
        `);
        resendButton.prop('disabled', false);
        resendButton.html('<i class="fas fa-paper-plane me-1"></i> Renvoyer le code');
      }
    });
  });
  
  // Validation et soumission du formulaire
  $('#verifyForm').on('submit', function() {
    const code = $('#verification-code').val();
    
    if (code.length < 6) {
      $('#verification-status-messages').html(`
        <div class="alert alert-danger">
          <i class="fa fa-exclamation-circle me-2"></i>
          Veuillez saisir un code de vérification à 6 chiffres.
        </div>
      `);
      showAllInputsError();
      return false;
    }
    
    // Afficher un indicateur de chargement
    verifyButton.addClass('loading');
    verifyButton.prop('disabled', true);
    
    return true;
  });
}
</script>
@endpush
@endsection