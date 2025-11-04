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
              <h1 class="mb-3 text-primary">500 000 F</h1>
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
          <h4 class="mb-1">Premium Business</h4>
          <p class="text-muted">Valide jusqu'au 31/12/2025</p>
          <hr>
          <ul class="list-unstyled mb-3">
            <li class="mb-2"><i class="fa fa-check-circle text-success me-2"></i> Campagnes illimitées</li>
            <li class="mb-2"><i class="fa fa-check-circle text-success me-2"></i> Support prioritaire</li>
            <li class="mb-2"><i class="fa fa-check-circle text-success me-2"></i> Analytics avancés</li>
          </ul>
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
                <tr>
                  <td>10/10/2025</td>
                  <td>Paiement campagne "Promo Automne"</td>
                  <td>Débit</td>
                  <td class="text-danger">-75 000 F</td>
                  <td><span class="badge bg-success">Complété</span></td>
                  <td><a href="#" class="btn btn-sm btn-light"><i class="fa fa-download"></i></a></td>
                </tr>
                <tr>
                  <td>05/10/2025</td>
                  <td>Rechargement par carte bancaire</td>
                  <td>Crédit</td>
                  <td class="text-success">+200 000 F</td>
                  <td><span class="badge bg-success">Complété</span></td>
                  <td><a href="#" class="btn btn-sm btn-light"><i class="fa fa-download"></i></a></td>
                </tr>
                <tr>
                  <td>28/09/2025</td>
                  <td>Paiement campagne "Back to School"</td>
                  <td>Débit</td>
                  <td class="text-danger">-120 000 F</td>
                  <td><span class="badge bg-success">Complété</span></td>
                  <td><a href="#" class="btn btn-sm btn-light"><i class="fa fa-download"></i></a></td>
                </tr>
              </tbody>
            </table>
          </div>
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
        <form id="addFundsForm">
          <div class="mb-3">
            <label class="form-label">Méthode de paiement</label>
            <select class="form-select" required>
              <option value="">Sélectionner une méthode</option>
              <option value="card">Carte bancaire</option>
              <option value="mobile_money">Mobile Money</option>
              <option value="bank">Virement bancaire</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Montant (F)</label>
            <input type="number" class="form-control" min="5000" step="1000" required>
            <div class="form-text">Montant minimum: 5 000 F</div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-primary">Procéder au paiement</button>
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