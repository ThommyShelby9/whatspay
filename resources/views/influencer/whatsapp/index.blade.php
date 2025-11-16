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
// Variables globales pour le timer
let verificationTimer = null;
let verificationTimeLeft = 60;

$(document).ready(function() {
  // Ajout d'un spinner lors de la soumission du formulaire d'ajout
  $('#addPhoneForm').on('submit', function() {
    $('#addPhoneSubmit').prop('disabled', true);
    $('#addPhoneSubmit').html('<i class="fa fa-spinner fa-spin me-1"></i> Traitement...');
  });

  // Vérifier automatiquement l'état d'envoi du code après ajout de numéro
  @if(session('phone_id'))
    var phoneId = "{{ session('phone_id') }}";

    // Afficher immédiatement le modal de vérification
    setTimeout(function() {
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
          $('#timer').text('00:00');
        }
      }, 30000);
    }, 500);
  @endif

  // Verify number modal
  $('.verify-number').click(function() {
    const phoneId = $(this).data('id');
    $('#verify-phone-id').val(phoneId);

    // Réinitialiser les messages de statut
    $('#verification-status-messages').empty();
  });

  // Initialiser la gestion des codes de vérification
  initVerificationCode();
});

function initVerificationCode() {
  // ✅ Fonction pour mettre à jour le code caché et le bouton
  function updateCodeAndButton() {
    let code = '';
    $('.code-input').each(function() {
      const val = $(this).val();
      if (val) {
        code += val;
      }
    });

    $('#verification-code').val(code);

    console.log('Code actuel:', code, 'Longueur:', code.length);

    // ✅ Activer/désactiver le bouton selon la longueur du code
    if (code.length === 6) {
      $('#verifySubmit').prop('disabled', false).removeClass('btn-secondary').addClass('btn-primary');
      console.log('✅ Bouton activé - Code complet!');
    } else {
      $('#verifySubmit').prop('disabled', true).removeClass('btn-primary').addClass('btn-secondary');
      console.log('⏸️ Bouton désactivé - Code incomplet');
    }
  }

  // Gestion des champs de code - AUTO FOCUS SUIVANT
  $(document).off('input', '.code-input').on('input', '.code-input', function(e) {
    const $this = $(this);
    let value = $this.val();
    const index = parseInt($this.data('index'));

    // Nettoyer la valeur - garder seulement les chiffres ET lettres (alphanumériques)
    value = value.replace(/[^A-Za-z0-9]/g, '').toUpperCase();

    // Si plus d'un caractère a été collé, ne garder que le premier
    if (value.length > 1) {
      value = value.charAt(0);
    }

    // Mettre à jour la valeur nettoyée
    $this.val(value);

    // Permettre seulement les caractères alphanumériques
    if (value.length === 0) {
      $this.removeClass('filled error');
      updateCodeAndButton();
      return;
    }

    if (!/^[A-Z0-9]$/.test(value)) {
      $this.val('');
      $this.addClass('error');
      setTimeout(() => $this.removeClass('error'), 500);
      updateCodeAndButton();
      return;
    }

    // Si un caractère alphanumérique est saisi
    if (value.length === 1) {
      $this.addClass('filled').removeClass('error');

      // ✅ DÉPLACEMENT AUTOMATIQUE vers le champ suivant
      if (index < 5) {
        const $nextInput = $(`.code-input[data-index="${index + 1}"]`);
        setTimeout(() => {
          $nextInput.focus();
        }, 10);
      } else {
        // Dernier champ - enlever le focus
        $this.blur();
      }
    }

    updateCodeAndButton();
  });
  
  // Gestion des touches spéciales
  $(document).off('keydown', '.code-input').on('keydown', '.code-input', function(e) {
    const $this = $(this);
    const index = parseInt($this.data('index'));
    
    if (e.key === 'Backspace') {
      if ($this.val() === '' && index > 0) {
        // Revenir au champ précédent
        const $prev = $(`.code-input[data-index="${index - 1}"]`);
        $prev.focus().val('').removeClass('filled');
      } else {
        $this.removeClass('filled error');
      }
      setTimeout(updateCodeAndButton, 10);
      
    } else if (e.key === 'ArrowLeft' && index > 0) {
      $(`.code-input[data-index="${index - 1}"]`).focus();
      e.preventDefault();
      
    } else if (e.key === 'ArrowRight' && index < 5) {
      $(`.code-input[data-index="${index + 1}"]`).focus();
      e.preventDefault();
      
    } else if (e.key === 'Delete') {
      $this.val('').removeClass('filled error');
      setTimeout(updateCodeAndButton, 10);
    }
  });
  
  // Gestion du collage
  $(document).off('paste', '.code-input').on('paste', '.code-input', function(e) {
    e.preventDefault();
    const pastedText = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
    const digits = pastedText.replace(/\D/g, '').slice(0, 6);
    
    if (digits.length === 6) {
      // Remplir tous les champs
      for (let i = 0; i < 6; i++) {
        const $input = $(`.code-input[data-index="${i}"]`);
        if (i < digits.length) {
          $input.val(digits[i]).addClass('filled').removeClass('error');
        } else {
          $input.val('').removeClass('filled error');
        }
      }
      
      // Focuser le dernier champ
      $(`.code-input[data-index="5"]`).focus();
      updateCodeAndButton();
    }
  });
  
  // Focus et blur
  $(document).off('focus blur', '.code-input').on('focus', '.code-input', function() {
    $(this).select();
  }).on('blur', '.code-input', function() {
    if ($(this).val()) {
      $(this).addClass('filled');
    } else {
      $(this).removeClass('filled');
    }
  });
  
  // ✅ TIMER CORRIGÉ - Démarrer le timer
  function startTimer() {
    console.log('⏱️ Démarrage du timer');
    const $timer = $('#timer');
    const $resendBtn = $('#resendCode');

    // Vérifier que les éléments existent
    if ($timer.length === 0) {
      console.error('❌ Element #timer non trouvé!');
      return;
    }
    if ($resendBtn.length === 0) {
      console.error('❌ Element #resendCode non trouvé!');
      return;
    }

    // Arrêter le timer existant s'il y en a un
    if (verificationTimer) {
      console.log('⏹️ Arrêt du timer précédent');
      clearInterval(verificationTimer);
      verificationTimer = null;
    }

    // Réinitialiser
    $resendBtn.prop('disabled', true);
    verificationTimeLeft = 60;

    // Afficher le temps initial
    $timer.text('01:00').removeClass('warning danger');
    console.log('✅ Timer initialisé à 01:00');

    // Démarrer le nouveau timer
    verificationTimer = setInterval(function() {
      verificationTimeLeft--;
      console.log('⏲️ Timer:', verificationTimeLeft, 'secondes restantes');

      const minutes = Math.floor(verificationTimeLeft / 60);
      const seconds = verificationTimeLeft % 60;
      const formattedTime = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

      $timer.text(formattedTime);

      // Changer la couleur selon le temps restant
      $timer.removeClass('warning danger');
      if (verificationTimeLeft <= 10) {
        $timer.addClass('danger');
      } else if (verificationTimeLeft <= 30) {
        $timer.addClass('warning');
      }

      if (verificationTimeLeft <= 0) {
        console.log('⏰ Timer terminé!');
        clearInterval(verificationTimer);
        verificationTimer = null;
        $resendBtn.prop('disabled', false);
        $timer.text('00:00');
      }
    }, 1000);
  }
  
  // ✅ Reset des champs
  function resetFields() {
    console.log('🔄 Reset des champs');
    $('.code-input').each(function() {
      $(this).val('').removeClass('filled error success');
    });
    $('#verification-code').val('');
    $('#verifySubmit').prop('disabled', true);
    updateCodeAndButton();
  }

  // ✅ Gestion des événements du modal
  $('#verifyModal').on('shown.bs.modal', function() {
    console.log('📱 Modal ouvert - Initialisation');
    resetFields();
    startTimer();

    // Focuser le premier champ avec un délai
    setTimeout(() => {
      console.log('🎯 Focus sur le premier champ');
      $('.code-input[data-index="0"]').focus();
    }, 300);
  });
  
  $('#verifyModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
    // ✅ Arrêter le timer global
    if (verificationTimer) {
      clearInterval(verificationTimer);
      verificationTimer = null;
    }
    $('#verifySubmit').removeClass('loading').prop('disabled', true);
    resetFields();
  });
  
  // Bouton renvoyer le code
  $('#resendCode').off('click').on('click', function() {
    const $this = $(this);
    const phoneId = $('#verify-phone-id').val();
    
    if (!phoneId) {
      alert('Erreur: ID du téléphone manquant');
      return;
    }
    
    resetFields();
    
    // Désactiver le bouton et ajouter un indicateur de chargement
    $this.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Envoi en cours...');
    
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
          $('#verification-status-messages').html(`
            <div class="alert alert-success">
              <i class="fa fa-check-circle me-2"></i>
              Code renvoyé avec succès. Veuillez vérifier votre WhatsApp.
            </div>
          `);
          
          // Redémarrer le timer
          startTimer();
          $this.html('<i class="fas fa-paper-plane me-1"></i> Renvoyer le code');
          
          // Focuser le premier champ
          $('.code-input[data-index="0"]').focus();
        } else {
          $('#verification-status-messages').html(`
            <div class="alert alert-danger">
              <i class="fa fa-exclamation-circle me-2"></i>
              ${response.message || 'Une erreur est survenue lors du renvoi du code'}
            </div>
          `);
          $this.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Renvoyer le code');
        }
      },
      error: function(xhr) {
        $('#verification-status-messages').html(`
          <div class="alert alert-danger">
            <i class="fa fa-exclamation-circle me-2"></i>
            Erreur de connexion. Veuillez réessayer.
          </div>
        `);
        $this.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Renvoyer le code');
      }
    });
  });
  
  // Soumission du formulaire
  $('#verifyForm').off('submit').on('submit', function(e) {
    const code = $('#verification-code').val();
    
    if (code.length !== 6) {
      e.preventDefault();
      
      $('#verification-status-messages').html(`
        <div class="alert alert-danger">
          <i class="fa fa-exclamation-circle me-2"></i>
          Veuillez saisir un code de vérification à 6 chiffres.
        </div>
      `);
      
      // Animation d'erreur sur tous les champs
      $('.code-input').addClass('error');
      setTimeout(() => {
        $('.code-input').removeClass('error');
      }, 500);
      
      return false;
    }
    
    // Afficher un indicateur de chargement
    $('#verifySubmit').addClass('loading').prop('disabled', true);
    
    return true;
  });
}

// ✅ Test pour vérifier que tout est chargé
console.log('✅ Verification Code JavaScript loaded successfully');
console.log('📋 Variables globales:', {
  verificationTimer: verificationTimer,
  verificationTimeLeft: verificationTimeLeft
});

// Test immédiat des éléments
$(document).ready(function() {
  console.log('🔍 Vérification des éléments du DOM:');
  console.log('  - #timer existe?', $('#timer').length > 0);
  console.log('  - #resendCode existe?', $('#resendCode').length > 0);
  console.log('  - #verifySubmit existe?', $('#verifySubmit').length > 0);
  console.log('  - .code-input nombre:', $('.code-input').length);
});
</script>
@endpush
@endsection