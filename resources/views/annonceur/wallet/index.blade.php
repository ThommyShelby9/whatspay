<!-- File: resources/views/announcer/wallet/index.blade.php (Enhanced) -->
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
                <li class="breadcrumb-item active">Portefeuille</li>
              </ol>
            </nav>
          </div>
          <div class="col-auto">
            <button type="button" class="btn btn-outline-primary" id="refreshBalance">
              <i class="fa fa-sync-alt me-1"></i> Actualiser
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Alerte solde faible -->
  @if(isset($viewData['lowBalanceAlert']))
    <div class="row mb-4">
      <div class="col-12">
        <div class="alert alert-{{ $viewData['lowBalanceAlert']['type'] }} alert-dismissible fade show" role="alert">
          <i class="fa fa-exclamation-triangle me-2"></i>
          {{ $viewData['lowBalanceAlert']['message'] }}
          @if($viewData['lowBalanceAlert']['action_required'])
            <button type="button" class="btn btn-sm btn-light ms-2" data-bs-toggle="modal" data-bs-target="#addFundsModal">
              Recharger maintenant
            </button>
          @endif
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      </div>
    </div>
  @endif

  <!-- Solde et Actions -->
  <div class="row mb-4">
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="avatar-lg bg-soft-primary rounded-circle text-center">
                <i class="fa fa-wallet text-primary font-size-24 mt-4"></i>
              </div>
            </div>
            <div class="flex-grow-1 ms-4">
              <h3 class="fw-bold">Solde actuel</h3>
              <h1 class="mb-3 text-primary" id="current-balance">
                @if(isset($viewData['balance']))
                  {{ number_format($viewData['balance'], 0, ',', ' ') }} F
                @else
                  0 F
                @endif
              </h1>
              
              <!-- Statistiques rapides -->
              @if(isset($viewData['spendingStats']))
                <div class="row mb-3">
                  <div class="col-6">
                    <small class="text-muted">Budget réservé</small>
                    <div class="fw-semibold">{{ number_format($viewData['spendingStats']['total_budget_reserved'] ?? 0, 0, ',', ' ') }} F</div>
                  </div>
                  <div class="col-6">
                    <small class="text-muted">En attente</small>
                    <div class="fw-semibold text-warning">{{ number_format($viewData['spendingStats']['pending_payments'] ?? 0, 0, ',', ' ') }} F</div>
                  </div>
                </div>
              @endif
              
              <div class="btn-group">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFundsModal">
                  <i class="fa fa-plus me-1"></i> Ajouter des fonds
                </button>
                <a href="{{ route('announcer.wallet.history') }}" class="btn btn-outline-primary">
                  <i class="fa fa-history me-1"></i> Historique complet
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Statistiques détaillées -->
  @if(isset($viewData['transactionStats']))
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="card">
          <div class="card-body text-center">
            <h3 class="text-primary">{{ $viewData['transactionStats']['total_transactions'] }}</h3>
            <p class="mb-0 text-muted">Transactions totales</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body text-center">
            <h3 class="text-success">{{ number_format($viewData['transactionStats']['this_month_credits'], 0, ',', ' ') }} F</h3>
            <p class="mb-0 text-muted">Dépôts ce mois-ci</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body text-center">
            <h3 class="text-danger">{{ number_format($viewData['transactionStats']['this_month_debits'], 0, ',', ' ') }} F</h3>
            <p class="mb-0 text-muted">Dépenses ce mois-ci</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card">
          <div class="card-body text-center">
            <h3 class="text-info">{{ isset($viewData['spendingStats']) ? $viewData['spendingStats']['active_campaigns'] : 0 }}</h3>
            <p class="mb-0 text-muted">Campagnes actives</p>
          </div>
        </div>
      </div>
    </div>
  @endif

  <!-- Transactions récentes -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Transactions récentes</h5>
        </div>
        <div class="card-body">
          @if(isset($viewData['transactions']) && count($viewData['transactions']) > 0)
            <div class="table-responsive">
              <table class="table" id="transactions-table">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Reçu</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($viewData['transactions'] as $transaction)
                    <tr>
                      <td>{{ date('d/m/Y H:i', strtotime($transaction->created_at)) }}</td>
                      <td>{{ $transaction->description }}</td>
                      <td>
                        <span class="badge bg-{{ $transaction->type == 'Crédit' ? 'success' : 'primary' }}">
                          {{ $transaction->type }}
                        </span>
                      </td>
                      <td class="{{ $transaction->type == 'Crédit' ? 'text-success' : 'text-danger' }}">
                        {{ $transaction->type == 'Crédit' ? '+' : '-' }}{{ number_format($transaction->amount, 0, ',', ' ') }} F
                      </td>
                      <td>
                        @php
                          $statusConfig = [
                            'COMPLETED' => ['class' => 'success', 'text' => 'Complété'],
                            'PENDING' => ['class' => 'warning', 'text' => 'En attente'],
                            'PROCESSING' => ['class' => 'info', 'text' => 'En cours'],
                            'FAILED' => ['class' => 'danger', 'text' => 'Échoué'],
                            'CANCELLED' => ['class' => 'secondary', 'text' => 'Annulé']
                          ];
                          $config = $statusConfig[$transaction->status] ?? ['class' => 'secondary', 'text' => $transaction->status];
                        @endphp
                        <span class="badge bg-{{ $config['class'] }}">{{ $config['text'] }}</span>
                      </td>
                      <td>
                        @if($transaction->receipt_url)
                          <a href="{{ $transaction->receipt_url }}" class="btn btn-sm btn-light" target="_blank">
                            <i class="fa fa-download"></i>
                          </a>
                        @else
                          <button class="btn btn-sm btn-light" disabled>
                            <i class="fa fa-download text-muted"></i>
                          </button>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="text-center py-4">
              <div class="avatar-lg mx-auto mb-3">
                <i class="fa fa-receipt fa-3x text-muted"></i>
              </div>
              <h5>Aucune transaction</h5>
              <p class="text-muted mb-0">Vous n'avez pas encore effectué de transaction.</p>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Ajouter des fonds -->
<div class="modal fade" id="addFundsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ajouter des fonds à votre portefeuille</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addFundsForm" method="post" action="{{ route('announcer.wallet.add-funds') }}">
          @csrf
          
          <div class="row mb-4">
            <div class="col-12">
              <div class="alert alert-info">
                <i class="fa fa-info-circle me-2"></i>
                <strong>Montants:</strong> Minimum 1 000 FCFA - Maximum 1 000 000 FCFA
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Montant (FCFA) *</label>
                <div class="input-group">
                  <input type="number" class="form-control" name="amount" id="deposit-amount" 
                         min="1000" max="1000000" step="1000" placeholder="Montant" required>
                  <span class="input-group-text">F</span>
                </div>
                <div class="form-text">
                  <div class="d-flex justify-content-between">
                    <span>Montant minimum: 1 000 F</span>
                    <span>Montant maximum: 1 000 000 F</span>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Numéro de téléphone *</label>
                <input type="tel" class="form-control" name="phone" id="deposit-phone" 
                       placeholder="Ex: 0701234567" pattern="[0-9]{8,15}" required>
                <div class="form-text">Votre numéro mobile money</div>
              </div>
            </div>
          </div>
          
          <div class="mb-4">
            <label class="form-label">Méthode de paiement *</label>
            <div class="row">
              @foreach($viewData['paymentMethods'] ?? [] as $method)
                <div class="col-md-6 mb-3">
                  <div class="card payment-method-card" data-method="{{ $method->id }}">
                    <div class="card-body text-center">
                      <input type="radio" class="btn-check" name="payment_method" value="{{ $method->id }}" 
                             id="method_{{ $method->id }}" required>
                      <label class="btn btn-outline-primary w-100" for="method_{{ $method->id }}">
                        @if($method->icon)
                          <i class="{{ $method->icon }} me-2"></i>
                        @endif
                        {{ $method->name }}
                      </label>
                      @if($method->config)
                        @php $config = json_decode($method->config, true) @endphp
                        @if(isset($config['processing_time']))
                          <small class="text-muted d-block mt-2">{{ $config['processing_time'] }}</small>
                        @endif
                      @endif
                    </div>
                  </div>
                </div>
              @endforeach
              
              @if(!isset($viewData['paymentMethods']) || count($viewData['paymentMethods']) === 0)
                <!-- Méthodes par défaut si pas de données -->
                <div class="col-md-6 mb-3">
                  <div class="card payment-method-card">
                    <div class="card-body text-center">
                      <input type="radio" class="btn-check" name="payment_method" value="mobile_money" id="mobile_money" required>
                      <label class="btn btn-outline-primary w-100" for="mobile_money">
                        <i class="fas fa-mobile-alt me-2"></i>Mobile Money
                      </label>
                      <small class="text-muted d-block mt-2">Orange, MTN, Moov</small>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-6 mb-3">
                  <div class="card payment-method-card">
                    <div class="card-body text-center">
                      <input type="radio" class="btn-check" name="payment_method" value="card" id="card">
                      <label class="btn btn-outline-primary w-100" for="card">
                        <i class="fas fa-credit-card me-2"></i>Carte Bancaire
                      </label>
                      <small class="text-muted d-block mt-2">Visa, MasterCard</small>
                    </div>
                  </div>
                </div>
              @endif
            </div>
          </div>
          
          <!-- Montants suggérés -->
          <div class="mb-4">
            <label class="form-label">Montants suggérés</label>
            <div class="row">
              @foreach([5000, 10000, 25000, 50000, 100000, 250000] as $amount)
                <div class="col-md-4 col-sm-6 mb-2">
                  <button type="button" class="btn btn-light w-100 suggested-amount" data-amount="{{ $amount }}">
                    {{ number_format($amount, 0, ',', ' ') }} F
                  </button>
                </div>
              @endforeach
            </div>
          </div>
          
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" form="addFundsForm" class="btn btn-primary" id="submit-payment">
          <i class="fa fa-credit-card me-1"></i>Procéder au paiement
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#transactions-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
        },
        order: [[0, 'desc']],
        pageLength: 10,
        responsive: true
    });
    
    // Suggested amounts
    $('.suggested-amount').click(function() {
        var amount = $(this).data('amount');
        $('#deposit-amount').val(amount);
    });
    
    // Payment method selection styling
    $('input[name="payment_method"]').change(function() {
        $('.payment-method-card').removeClass('border-primary');
        $(this).closest('.payment-method-card').addClass('border-primary');
    });
    
    // Form validation
    $('#addFundsForm').on('submit', function(e) {
        var amount = parseInt($('#deposit-amount').val());
        var phone = $('#deposit-phone').val();
        var paymentMethod = $('input[name="payment_method"]:checked').val();
        
        // Validation du montant
        if (amount < 1000) {
            e.preventDefault();
            alert('Le montant minimum est de 1 000 FCFA');
            return false;
        }
        
        if (amount > 1000000) {
            e.preventDefault();
            alert('Le montant maximum est de 1 000 000 FCFA');
            return false;
        }
        
        // Validation du téléphone
        if (!phone || phone.length < 8) {
            e.preventDefault();
            alert('Veuillez saisir un numéro de téléphone valide');
            return false;
        }
        
        // Validation de la méthode de paiement
        if (!paymentMethod) {
            e.preventDefault();
            alert('Veuillez sélectionner une méthode de paiement');
            return false;
        }
        
        // Afficher un indicateur de chargement
        $('#submit-payment').html('<i class="fa fa-spinner fa-spin me-1"></i>Redirection...');
        $('#submit-payment').prop('disabled', true);
    });
    
    // Refresh balance
    $('#refreshBalance').click(function() {
        $(this).find('i').addClass('fa-spin');
        
        $.get('{{ route("announcer.wallet.stats") }}')
            .done(function(data) {
                if (data.success && data.wallet) {
                    $('#current-balance').text(new Intl.NumberFormat('fr-FR').format(data.wallet.wallet.balance) + ' F');
                }
            })
            .fail(function() {
                console.log('Erreur lors de l\'actualisation');
            })
            .always(function() {
                $('#refreshBalance').find('i').removeClass('fa-spin');
            });
    });
    
    // Auto-format phone number
    $('#deposit-phone').on('input', function() {
        var value = $(this).val().replace(/\D/g, ''); // Remove non-digits
        $(this).val(value);
    });
    
    // Real-time amount formatting
    $('#deposit-amount').on('input', function() {
        var value = $(this).val();
        if (value) {
            // Could add real-time formatting here
        }
    });
});
</script>
@endpush
@endsection