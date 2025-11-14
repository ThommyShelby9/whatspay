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

<!-- Modal Vérifier un numéro -->
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
        
        <form id="verifyForm" method="post" action="{{ route('influencer.whatsapp.verify') }}">
          @csrf
          
          <input type="hidden" name="phone_id" id="verify-phone-id">
          
          <div class="mb-3">
            <label class="form-label">Code de vérification reçu sur WhatsApp</label>
            <div class="verification-code-input">
              <input type="text" maxlength="1" class="form-control code-input" data-index="1">
              <input type="text" maxlength="1" class="form-control code-input" data-index="2">
              <input type="text" maxlength="1" class="form-control code-input" data-index="3">
              <input type="text" maxlength="1" class="form-control code-input" data-index="4">
              <input type="text" maxlength="1" class="form-control code-input" data-index="5">
              <input type="text" maxlength="1" class="form-control code-input" data-index="6">
            </div>
            <input type="hidden" name="verification_code" id="verification-code">
          </div>
          
          <div class="text-center mb-3">
            <span id="timer" class="text-muted">00:59</span>
          </div>
          
          <div class="text-center">
            <button type="button" class="btn btn-link" id="resendCode" disabled>
              Renvoyer le code
            </button>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary" id="verifySubmit" form="verifyForm">Vérifier</button>
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
  .verification-code-input {
    display: flex;
    justify-content: space-between;
    gap: 10px;
  }
  
  .verification-code-input input {
    text-align: center;
    font-size: 20px;
    width: 50px;
    height: 50px;
    padding: 0;
  }
  
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
        
        // Focus sur le premier champ après un court délai
        setTimeout(() => {
          $('.code-input:first').focus();
        }, 500);
        
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
      
      // Start timer
      let seconds = 59;
      const timerInterval = setInterval(function() {
        if (seconds <= 0) {
          clearInterval(timerInterval);
          $('#resendCode').prop('disabled', false);
          $('#timer').text('');
        } else {
          seconds--;
          $('#timer').text(`00:${seconds.toString().padStart(2, '0')}`);
        }
      }, 1000);
      
      // Reset code inputs
      $('.code-input').val('');
      $('.code-input:first').focus();
    });
    
    // Verification code input
    $('.code-input').on('input', function() {
      const index = parseInt($(this).data('index'));
      const value = $(this).val();
      
      if (value && index < 6) {
        $(`.code-input[data-index="${index + 1}"]`).focus();
      }
      
      // Update hidden input with full code
      let code = '';
      $('.code-input').each(function() {
        code += $(this).val();
      });
      
      $('#verification-code').val(code);
      
      // Si le code est complet, activer le bouton de vérification
      if (code.length === 6) {
        $('#verifySubmit').prop('disabled', false);
      } else {
        $('#verifySubmit').prop('disabled', true);
      }
    });
    
    $('.code-input').on('keydown', function(e) {
      if (e.key === 'Backspace') {
        const index = parseInt($(this).data('index'));
        const value = $(this).val();
        
        if (!value && index > 1) {
          $(`.code-input[data-index="${index - 1}"]`).focus();
        }
      }
    });
    
    // Resend code
    $('#resendCode').click(function() {
      const phoneId = $('#verify-phone-id').val();
      
      // Désactiver le bouton et ajouter un indicateur de chargement
      $(this).prop('disabled', true);
      $(this).html('<i class="fa fa-spinner fa-spin"></i> Envoi en cours...');
      
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
            let seconds = 59;
            $('#resendCode').html('Renvoyer le code');
            
            const timerInterval = setInterval(function() {
              if (seconds <= 0) {
                clearInterval(timerInterval);
                $('#resendCode').prop('disabled', false);
                $('#timer').text('');
              } else {
                seconds--;
                $('#timer').text(`00:${seconds.toString().padStart(2, '0')}`);
              }
            }, 1000);
            
            // Focus sur le premier champ
            $('.code-input:first').focus();
          } else {
            // Afficher un message d'erreur
            $('#verification-status-messages').html(`
              <div class="alert alert-danger">
                <i class="fa fa-exclamation-circle me-2"></i>
                ${response.message || 'Une erreur est survenue lors du renvoi du code'}
              </div>
            `);
            $('#resendCode').prop('disabled', false);
            $('#resendCode').html('Renvoyer le code');
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
          $('#resendCode').prop('disabled', false);
          $('#resendCode').html('Renvoyer le code');
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
        return false;
      }
      
      // Afficher un indicateur de chargement
      $('#verifySubmit').prop('disabled', true);
      $('#verifySubmit').html('<i class="fa fa-spinner fa-spin me-1"></i> Vérification...');
      
      return true;
    });
  });
</script>
@endpush
@endsection