<!-- File: resources/views/announcer/influencers/index.blade.php -->
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
                <li class="breadcrumb-item active">Diffuseurs</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filtres de recherche -->
  <div class="row mb-4">
    <div class="col-xl-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Filtrer les diffuseurs</h5>
          <a class="btn btn-sm btn-link" data-bs-toggle="collapse" href="#collapseFilter" role="button" aria-expanded="false">
            <i class="fa fa-chevron-down"></i>
          </a>
        </div>
        <div class="collapse" id="collapseFilter">
          <div class="card-body">
            <form method="get" action="{{ route('announcer.influencers.index') }}">
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group mb-3">
                    <label class="form-label">Pays</label>
                    <select class="form-select" name="filtre_country">
                      <option value="">Tous les pays</option>
                      <!-- Dynamically load countries here -->
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group mb-3">
                    <label class="form-label">Localité</label>
                    <select class="form-select" name="filtre_locality">
                      <option value="">Toutes les localités</option>
                      <!-- Dynamically load localities here -->
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group mb-3">
                    <label class="form-label">Catégorie</label>
                    <select class="form-select" name="filtre_category">
                      <option value="">Toutes les catégories</option>
                      @foreach($viewData["categories"] ?? [] as $category)
                      <option value="{{ $category->id }}" {{ request()->get('filtre_category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                      </option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary w-100">
                    <i class="fa fa-search me-2"></i>Filtrer
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Liste des diffuseurs -->
  <div class="row">
    @forelse($viewData["influencers"] ?? [] as $influencer)
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-3">
            <div class="avatar-md bg-light rounded-circle text-center me-3">
              <span class="avatar-title text-primary font-size-24">
                {{ substr($influencer->firstname ?? 'U', 0, 1) }}
              </span>
            </div>
            <div>
              <h5 class="mb-0">{{ $influencer->firstname }} {{ $influencer->lastname }}</h5>
              <p class="text-muted mb-0">{{ $influencer->locality ?? 'N/A' }}, {{ $influencer->country ?? 'N/A' }}</p>
            </div>
          </div>
          
          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="text-muted">Vues moyennes</span>
              <span class="fw-bold">{{ number_format($influencer->vuesmoyen ?? 0) }}</span>
            </div>
            <div class="progress" style="height: 5px;">
              @php
                $viewsPercent = min(100, (($influencer->vuesmoyen ?? 0) / 10000) * 100);
              @endphp
              <div class="progress-bar bg-success" role="progressbar" style="width: {{ $viewsPercent }}%" aria-valuenow="{{ $viewsPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </div>
          
          <div class="mb-3">
            <span class="text-muted d-block mb-2">Catégories</span>
            <div class="d-flex flex-wrap">
              @if(!empty($influencer->category))
                @foreach(explode(', ', $influencer->category) as $cat)
                <span class="badge bg-info me-1 mb-1">{{ $cat }}</span>
                @endforeach
              @else
                <span class="text-muted">Aucune catégorie</span>
              @endif
            </div>
          </div>
          
          <div class="mt-3 text-center">
            <a href="{{ route('announcer.influencers.show', ['id' => $influencer->id]) }}" class="btn btn-primary btn-sm w-100">
              <i class="fa fa-user me-1"></i>Voir le profil
            </a>
          </div>
        </div>
      </div>
    </div>
    @empty
    <div class="col-12">
      <div class="alert alert-info">
        Aucun diffuseur trouvé selon vos critères de recherche.
      </div>
    </div>
    @endforelse
  </div>
  
  <!-- Pagination -->
  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-center">
        <!-- Pagination would go here if implemented -->
      </div>
    </div>
  </div>
</div>
@endsection