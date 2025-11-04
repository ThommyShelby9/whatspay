<!-- File: resources/views/announcer/messages/index.blade.php -->
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
                <li class="breadcrumb-item active">Messages</li>
              </ol>
            </nav>
          </div>
          <div class="col-auto">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newMessageModal">
              <i class="fa fa-plus me-1"></i> Nouveau message
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Liste des conversations -->
    <div class="col-md-4 col-lg-3">
      <div class="card">
        <div class="card-header">
          <div class="input-group">
            <input type="text" class="form-control" placeholder="Rechercher...">
            <button class="btn btn-light" type="button">
              <i class="fa fa-search"></i>
            </button>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="chat-list" style="height: 650px; overflow-y: auto;">
            <div class="list-group list-group-flush">
              <a href="#" class="list-group-item list-group-item-action active py-3 px-3">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <div class="avatar-sm bg-primary rounded-circle">
                      <span class="avatar-title text-white">JD</span>
                    </div>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1">Jean Dupont</h6>
                    <p class="text-truncate mb-0">Bonjour, est-ce que vous avez...</p>
                    <small class="text-muted">Aujourd'hui, 10:30</small>
                  </div>
                  <div class="flex-shrink-0">
                    <span class="badge bg-danger rounded-pill">2</span>
                  </div>
                </div>
              </a>
              
              <a href="#" class="list-group-item list-group-item-action py-3 px-3">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <div class="avatar-sm bg-info rounded-circle">
                      <span class="avatar-title text-white">ML</span>
                    </div>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1">Marie Laforet</h6>
                    <p class="text-truncate mb-0">Merci pour la confirmation.</p>
                    <small class="text-muted">Hier, 16:45</small>
                  </div>
                </div>
              </a>
              
              <a href="#" class="list-group-item list-group-item-action py-3 px-3">
                <div class="d-flex align-items-center">
                  <div class="flex-shrink-0">
                    <div class="avatar-sm bg-success rounded-circle">
                      <span class="avatar-title text-white">PK</span>
                    </div>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1">Pierre Kouassi</h6>
                    <p class="text-truncate mb-0">La campagne a eu un excellent...</p>
                    <small class="text-muted">15/10/2025, 09:12</small>
                  </div>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Conversation active -->
    <div class="col-md-8 col-lg-9">
      <div class="card">
        <div class="card-header">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="avatar-md bg-primary rounded-circle">
                <span class="avatar-title text-white">JD</span>
              </div>
            </div>
            <div class="flex-grow-1 ms-3">
              <h5 class="mb-0">Jean Dupont</h5>
              <p class="text-muted mb-0">En ligne</p>
            </div>
            <div class="flex-shrink-0">
              <div class="dropdown">
                <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown">
                  <i class="fa fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#">Voir profil</a></li>
                  <li><a class="dropdown-item" href="#">Marquer comme lu</a></li>
                  <li><a class="dropdown-item" href="#">Archiver</a></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        
        <div class="card-body">
          <div class="chat-conversation" style="height: 550px; overflow-y: auto;">
            <!-- Date separator -->
            <div class="text-center mb-4">
              <span class="badge bg-light text-dark">Aujourd'hui</span>
            </div>
            
            <!-- Received message -->
            <div class="d-flex mb-4">
              <div class="flex-shrink-0">
                <div class="avatar-sm bg-primary rounded-circle">
                  <span class="avatar-title text-white">JD</span>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <div class="bg-light p-3 rounded">
                  <p class="mb-0">Bonjour, est-ce que vous avez des disponibilités pour discuter de la nouvelle campagne de promotion ?</p>
                </div>
                <small class="text-muted mt-1">10:30</small>
              </div>
            </div>
            
            <!-- Sent message -->
            <div class="d-flex justify-content-end mb-4">
              <div class="flex-grow-1 me-3" style="max-width: 70%;">
                <div class="bg-primary text-white p-3 rounded">
                  <p class="mb-0">Bonjour Jean ! Oui, je suis disponible cet après-midi entre 14h et 16h. Est-ce que ça vous conviendrait ?</p>
                </div>
                <div class="text-end mt-1">
                  <small class="text-muted">10:35</small>
                  <i class="fa fa-check-double text-info ms-1"></i>
                </div>
              </div>
            </div>
            
            <!-- Received message with file -->
            <div class="d-flex mb-4">
              <div class="flex-shrink-0">
                <div class="avatar-sm bg-primary rounded-circle">
                  <span class="avatar-title text-white">JD</span>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <div class="bg-light p-3 rounded">
                  <p class="mb-2">Parfait pour 14h ! J'ai préparé un document avec quelques idées. Jetez-y un coup d'œil avant notre appel.</p>
                  <div class="d-flex align-items-center p-2 bg-white rounded border">
                    <i class="fa fa-file-pdf text-danger fs-4 me-2"></i>
                    <div>
                      <p class="mb-0 fw-bold">idees_campagne_2025.pdf</p>
                      <small class="text-muted">1.2 MB</small>
                    </div>
                    <a href="#" class="btn btn-sm btn-light ms-auto">
                      <i class="fa fa-download"></i>
                    </a>
                  </div>
                </div>
                <small class="text-muted mt-1">10:42</small>
              </div>
            </div>
          </div>
        </div>
        
        <div class="card-footer bg-light">
          <div class="d-flex">
            <div class="btn-group me-2">
              <button type="button" class="btn btn-light">
                <i class="fa fa-paperclip"></i>
              </button>
              <button type="button" class="btn btn-light">
                <i class="fa fa-smile"></i>
              </button>
            </div>
            <input type="text" class="form-control" placeholder="Écrivez votre message...">
            <button type="button" class="btn btn-primary ms-2">
              <i class="fa fa-paper-plane"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Nouveau Message -->
<div class="modal fade" id="newMessageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nouveau Message</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="newMessageForm">
          <div class="mb-3">
            <label class="form-label">Destinataire</label>
            <select class="form-select" required>
              <option value="">Sélectionner un diffuseur</option>
              <option value="1">Jean Dupont</option>
              <option value="2">Marie Laforet</option>
              <option value="3">Pierre Kouassi</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Objet</label>
            <input type="text" class="form-control" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea class="form-control" rows="5" required></textarea>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Pièces jointes (optionnel)</label>
            <input type="file" class="form-control" multiple>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-primary">Envoyer</button>
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
  .chat-conversation::-webkit-scrollbar {
    width: 6px;
  }
  
  .chat-conversation::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 3px;
  }
  
  .chat-list::-webkit-scrollbar {
    width: 4px;
  }
  
  .chat-list::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.1);
    border-radius: 3px;
  }
</style>
@endpush
@endsection