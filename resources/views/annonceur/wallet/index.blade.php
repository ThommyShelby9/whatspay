<!-- File: resources/views/announcer/wallet/index.blade.php -->
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
        </div>
      </div>
    </div>
  </div>

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
              <h1 class="mb-3 text-primary">
                @if(isset($viewData['balance']))
                  {{ number_format($viewData['balance'], 0, ',', ' ') }} F
                @else
                  0 F
                @endif
              </h1>
              <div class="btn-group">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFundsModal">
                  <i class="fa fa-plus me-1"></i> Ajouter des fonds
                </button>
                <button type="button" class="btn btn-outline-primary">
                  <i class="fa fa-file-invoice me-1"></i> Voir les factures
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="card">
        <div class="card-header bg-soft-success">
          <h5 class="card-title text-success mb-0">Plan actuel</h5>
        </div>
        <div class="card-body">
          @if(isset($viewData['currentPlan']))
            <h4 class="mb-1">{{ $viewData['currentPlan']->name ?? 'Aucun plan actif' }}</h4>
            @if(isset($viewData['currentPlan']->valid_until))
              <p class="text-muted">Valide jusqu'au {{ date('d/m/Y', strtotime($viewData['currentPlan']->valid_until)) }}</p>
            @endif
            <hr>
            <ul class="list-unstyled mb-3">
              @foreach($viewData['currentPlan']->features ?? [] as $feature)
                <li class="mb-2"><i class="fa fa-check-circle text-success me-2"></i> {{ $feature }}</li>
              @endforeach
            </ul>
          @else
            <h4 class="mb-1">Aucun plan actif</h4>
            <p class="text-muted">Choisissez un plan pour accéder à plus de fonctionnalités</p>
            <hr>
          @endif
          <button type="button" class="btn btn-outline-success btn-sm w-100">
            Voir les autres plans
          </button>
        </div>
      </div>
    </div>
  </div>

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
                      <td>{{ date('d/m/Y', strtotime($transaction->created_at)) }}</td>
                      <td>{{ $transaction->description }}</td>
                      <td>{{ $transaction->type }}</td>
                      <td class="{{ $transaction->type == 'Crédit' ? 'text-success' : 'text-danger' }}">
                        {{ $transaction->type == 'Crédit' ? '+' : '-' }}{{ number_format($transaction->amount, 0, ',', ' ') }} F
                      </td>
                      <td>
                        <span class="badge bg-{{ $transaction->status == 'COMPLETED' ? 'success' : ($transaction->status == 'PENDING' ? 'warning' : 'danger') }}">
                          {{ $transaction->status == 'COMPLETED' ? 'Complété' : ($transaction->status == 'PENDING' ? 'En attente' : 'Échoué') }}
                        </span>
                      </td>
                      <td>
                        @if($transaction->receipt_url)
                          <a href="{{ $transaction->receipt_url }}" class="btn btn-sm btn-light" target="_blank"><i class="fa fa-download"></i></a>
                        @else
                          <button class="btn btn-sm btn-light" disabled><i class="fa fa-download"></i></button>
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
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ajouter des fonds</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addFundsForm" method="post" action="{{ route('announcer.wallet.add-funds') }}">
          @csrf
          
          <div class="mb-3">
            <label class="form-label">Méthode de paiement</label>
            <select class="form-select" name="payment_method" required>
              <option value="">Sélectionner une méthode</option>
              @foreach($viewData['paymentMethods'] ?? [] as $method)
                <option value="{{ $method->id }}">{{ $method->name }}</option>
              @endforeach
              @if(!isset($viewData['paymentMethods']) || count($viewData['paymentMethods']) === 0)
                <option value="card">Carte bancaire</option>
                <option value="mobile_money">Mobile Money</option>
                <option value="bank">Virement bancaire</option>
              @endif
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Montant (F)</label>
            <input type="number" class="form-control" name="amount" min="5000" step="1000" required>
            <div class="form-text">Montant minimum: 5 000 F</div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" form="addFundsForm" class="btn btn-primary">Procéder au paiement</button>
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
      pageLength: 10
    });
  });
</script>
@endpush
@endsection