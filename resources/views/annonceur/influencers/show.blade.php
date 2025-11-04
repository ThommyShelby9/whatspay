<!-- File: resources/views/announcer/influencers/show.blade.php -->
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
                <li class="breadcrumb-item"><a href="{{ route('announcer.influencers.index') }}">Diffuseurs</a></li>
                <li class="breadcrumb-item active">{{ $viewData["influencer"]->firstname ?? '' }} {{ $viewData["influencer"]->lastname ?? '' }}</li>
              </ol>
            </nav>
          </div>
          <div class="col-auto">
            <a href="{{ route('announcer.influencers.index') }}" class="btn btn-secondary">
              <i class="fa fa-arrow-left me-2"></i>Retour
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Profil du diffuseur -->
    <div class="col-xl-4">
      <div class="card">
        <div class="card-body">
          <div class="text-center mb-4">
            <div class="avatar-xl mx-auto mb-3 bg-primary bg-opacity-10 rounded-circle">
              <span class="avatar-title text-primary font-size-40">
                {{ substr($viewData["influencer"]->firstname ?? 'U', 0, 1) }}
              </span>
            </div>
            <h4>{{ $viewData["influencer"]->firstname ?? '' }} {{ $viewData["influencer"]->lastname ?? '' }}</h4>
            <p class="text-muted">Diffuseur de contenu</p>
          </div>
          
          <ul class="list-unstyled mb-0">
            <li class="py-2 border-bottom">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <i class="fa fa-globe text-primary me-2"></i>
                </div>
                <div class="flex-grow-1">
                  <p class="text-muted mb-0">Pays</p>
                  <h6 class="mb-0">{{ $viewData["influencer"]->country ?? 'Non spécifié' }}</h6>
                </div>
              </div>
            </li>
            <li class="py-2 border-bottom">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <i class="fa fa-map-marker-alt text-primary me-2"></i>
                </div>
                <div class="flex-grow-1">
                  <p class="text-muted mb-0">Localité</p>
                  <h6 class="mb-0">{{ $viewData["influencer"]->locality ?? 'Non spécifié' }}</h6>
                </div>
              </div>
            </li>
            <li class="py-2 border-bottom">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <i class="fa fa-graduation-cap text-primary me-2"></i>
                </div>
                <div class="flex-grow-1">
                  <p class="text-muted mb-0">Niveau d'études</p>
                  <h6 class="mb-0">{{ $viewData["influencer"]->study ?? 'Non spécifié' }}</h6>
                </div>
              </div>
            </li>
            <li class="py-2 border-bottom">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <i class="fa fa-language text-primary me-2"></i>
                </div>
                <div class="flex-grow-1">
                  <p class="text-muted mb-0">Langue</p>
                  <h6 class="mb-0">{{ $viewData["influencer"]->lang ?? 'Non spécifié' }}</h6>
                </div>
              </div>
            </li>
            <li class="py-2">
              <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                  <i class="fa fa-briefcase text-primary me-2"></i>
                </div>
                <div class="flex-grow-1">
                  <p class="text-muted mb-0">Profession</p>
                  <h6 class="mb-0">{{ $viewData["influencer"]->profession ?? $viewData["influencer"]->occupation ?? 'Non spécifié' }}</h6>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
      
      <!-- Statistiques -->
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Statistiques</h5>
        </div>
        <div class="card-body">
          <div class="text-center mb-4">
            <h2 class="mb-0 text-primary">{{ number_format($viewData["influencer"]->vuesmoyen ?? 0) }}</h2>
            <p class="text-muted mb-0">Vues moyennes par publication</p>
          </div>
          
          <!-- Catégories -->
          <h6 class="mb-3">Catégories</h6>
          <div class="d-flex flex-wrap mb-4">
            @if(!empty($viewData["influencer"]->category))
              @foreach(explode(', ', $viewData["influencer"]->category) as $cat)
              <span class="badge bg-info me-1 mb-1">{{ $cat }}</span>
              @endforeach
            @else
              <span class="text-muted">Aucune catégorie</span>
            @endif
          </div>
          
          <!-- Types de contenus -->
          <h6 class="mb-3">Types de contenus</h6>
          <div class="d-flex flex-wrap">
            @if(!empty($viewData["influencer"]->contenttype))
              @foreach(explode(', ', $viewData["influencer"]->contenttype) as $type)
              <span class="badge bg-primary me-1 mb-1">{{ $type }}</span>
              @endforeach
            @else
              <span class="text-muted">Aucun type de contenu spécifié</span>
            @endif
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-xl-8">
      <!-- Actions -->
      <div class="card mb-4">
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3 mb-md-0">
              <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#inviteCampaignModal">
                <i class="fa fa-plus-circle me-2"></i>Inviter à une campagne
              </button>
            </div>
            <div class="col-md-6">
              <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#sendMessageModal">
                <i class="fa fa-envelope me-2"></i>Envoyer un message
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Performances -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0">Performances historiques</h5>
        </div>
        <div class="card-body">
          <div id="performance-chart" style="height: 300px;"></div>
        </div>
      </div>
      
      <!-- Campagnes passées -->
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Campagnes collaboratives</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table" id="campaigns-table">
              <thead>
                <tr>
                  <th>Campagne</th>
                  <th>Date</th>
                  <th>Vues</th>
                  <th>Statut</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="4" class="text-center">
                    <div class="alert alert-info mb-0">
                      Aucune campagne collaborative pour le moment.
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Invite Campaign Modal -->
<div class="modal fade" id="inviteCampaignModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Inviter à une campagne</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="inviteCampaignForm">
          <div class="mb-3">
            <label class="form-label">Sélectionner une campagne</label>
            <select class="form-select" name="campaign_id" required>
              <option value="">Choisir une campagne</option>
              <!-- Dynamically load campaigns here -->
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Message personnalisé</label>
            <textarea class="form-control" name="message" rows="4" placeholder="Écrivez un message personnalisé pour ce diffuseur..."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-primary" id="sendInvitation">Envoyer l'invitation</button>
      </div>
    </div>
  </div>
</div>

<!-- Send Message Modal -->
<div class="modal fade" id="sendMessageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Envoyer un message</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="sendMessageForm">
          <div class="mb-3">
            <label class="form-label">Objet</label>
            <input type="text" class="form-control" name="subject" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea class="form-control" name="message" rows="6" required></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-primary" id="sendMessage">Envoyer</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
  $(document).ready(function() {
    // Performance chart
    var performanceOptions = {
      series: [{
        name: 'Vues',
        data: [0, 0, 0, 0, 0, 0] // Placeholder data
      }],
      chart: {
        height: 300,
        type: 'area',
        toolbar: {
          show: false
        }
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'smooth',
        width: 2
      },
      xaxis: {
        categories: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
      },
      tooltip: {
        y: {
          formatter: function (val) {
            return val.toLocaleString() + " vues";
          }
        }
      },
      colors: ['#3b5de7'],
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.7,
          opacityTo: 0.3,
          stops: [0, 90, 100]
        }
      }
    };

    var performanceChart = new ApexCharts(document.querySelector("#performance-chart"), performanceOptions);
    performanceChart.render();
    
    // Placeholders for the invite form
    $('#sendInvitation').click(function() {
      alert('Fonctionnalité à implémenter: Inviter à une campagne');
      $('#inviteCampaignModal').modal('hide');
    });
    
    // Placeholders for the message form
    $('#sendMessage').click(function() {
      alert('Fonctionnalité à implémenter: Envoyer un message');
      $('#sendMessageModal').modal('hide');
    });
  });
</script>
@endpush
@endsection