<!-- File: resources/views/influencer/whatsapp/index.blade.php -->
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
          @php
            // Données d'exemple - à remplacer par les données réelles
            $whatsappNumbers = [
              (object) [
                'id' => 1,
                'number' => '+229 97 12 34 56',
                'status' => 'VERIFIED',
                'is_primary' => true,
                'created_at' => '2025-07-15 10:30:00'
              ],
              (object) [
                'id' => 2,
                'number' => '+229 66 78 90 12',
                'status' => 'PENDING',
                'is_primary' => false,
                'created_at' => '2025-10-20 15:45:00'
              ]
            ];
          @endphp
          
          @if(!empty($whatsappNumbers))
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
                  @foreach($whatsappNumbers as $number)
                  <tr>
                    <td>
                      <div class="d-flex align-items-center">
                        <i class="fab fa-whatsapp text-success me-2"></i>
                        <span>{{ $number->number }}</span>
                        @if($number->is_primary)
                        <span class="badge bg-primary ms-2">Principal</span>
                        @endif
                      </div>
                    </td>
                    <td>
                      @if($number->status == 'VERIFIED')
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
                        @if(!$number->is_primary)
                        <button type="button" class="btn btn-primary btn-sm set-primary" data-id="{{ $number->id }}">
                          <i class="fa fa-check-circle"></i> Définir comme principal
                        </button>
                        @endif
                        
                        @if($number->status == 'PENDING')
                        <button type="button" class="btn btn-success btn-sm verify-number" data-id="{{ $number->id }}" data-bs-toggle="modal" data-bs-target="#verifyModal">
                          <i class="fa fa-check"></i> Vérifier
                        </button>
                        @endif
                        
                        <button type="button" class="btn btn-danger btn-sm delete-number" data-id="{{ $number->id }}" data-bs-toggle="modal" data-bs-target="#deleteModal">
                          <i class="fa fa-trash"></i>
                        </button>
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
                  <p class="text-muted mb-0">Saisissez le code de vérification reçu par SMS ou WhatsApp pour confirmer votre numéro.</p>
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
                  <h5>Définir un numéro principal</h5>
                  <p class="text-muted mb-0">Vous pouvez définir un numéro comme principal pour recevoir en priorité les notifications importantes.</p>
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
                  <h5>Utiliser WhatsApp pour les campagnes</h5>
                  <p class="text-muted mb-0">Vous recevrez des notifications sur votre WhatsApp pour les nouvelles campagnes et les mises à jour importantes.</p>
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
        <form id="addPhoneForm" method="post" action="/admin/whatsappnumbers/add">
          @csrf
          
          <div class="mb-3">
            <label class="form-label">Indicatif</label>
            <select class="form-select" name="country_code" required>
              <option value="+229" selected>Bénin (+229)</option>
              <option value="+225">Côte d'Ivoire (+225)</option>
              <option value="+233">Ghana (+233)</option>
              <option value="+234">Nigeria (+234)</option>
              <option value="+228">Togo (+228)</option>
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
              Un code de vérification sera envoyé par SMS à ce numéro. Assurez-vous que WhatsApp est installé et activé sur ce numéro.
            </small>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary" form="addPhoneForm">Ajouter le numéro</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Vérifier un numéro -->
<div class="modal fade" id="verifyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Vérifier votre numéro</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="verifyForm" method="post" action="/admin/verify-phone">
          @csrf
          
          <input type="hidden" name="phone_id" id="verify-phone-id">
          
          <div class="mb-3">
            <label class="form-label">Code de vérification</label>
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
        <button type="submit" class="btn btn-primary" form="verifyForm">Vérifier</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Supprimer un numéro -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Supprimer le numéro</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Êtes-vous sûr de vouloir supprimer ce numéro WhatsApp ?</p>
        <div class="alert alert-warning">
          <i class="fa fa-exclamation-triangle me-2"></i>
          Si vous supprimez ce numéro, vous ne pourrez plus recevoir de notifications sur celui-ci.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-danger" id="confirmDelete">Supprimer</button>
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
</style>
@endpush

@push('scripts')
<script>
  $(document).ready(function() {
    // Verify number modal
    $('.verify-number').click(function() {
      const phoneId = $(this).data('id');
      $('#verify-phone-id').val(phoneId);
      
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
    
    // Set primary number
    $('.set-primary').click(function() {
      const phoneId = $(this).data('id');
      
      // For demo purposes only
      alert(`Numéro ${phoneId} défini comme principal`);
      location.reload();
    });
    
    // Delete number
    $('.delete-number').click(function() {
      const phoneId = $(this).data('id');
      
      $('#confirmDelete').off('click').on('click', function() {
        // For demo purposes only
        alert(`Numéro ${phoneId} supprimé`);
        $('#deleteModal').modal('hide');
        location.reload();
      });
    });
    
    // Resend code
    $('#resendCode').click(function() {
      $(this).prop('disabled', true);
      
      // Reset timer
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
      
      // For demo purposes only
      alert('Code renvoyé');
    });
  });
</script>
@endpush
@endsection