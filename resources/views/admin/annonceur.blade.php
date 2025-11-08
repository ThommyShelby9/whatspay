@extends('admin.layout')

@section('pagecontent')
@include('alert')

<div class="container-fluid">
  <!-- Page Header -->
  <div class="row">
    <div class="col-sm-12">
      <div class="page-title-box">
        <div class="row">
          <div class="col">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
              <li class="breadcrumb-item active">{{$pagetilte}}</li>
            </ol>
          </div>
          <div class="col-auto">
            <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilters" aria-expanded="false" aria-controls="collapseFilters">
              <i class="fa fa-filter me-2"></i>Filtres
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filtres (Collapsible) -->
  <div class="collapse mb-4" id="collapseFilters">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Options de filtrage</h5>
      </div>
      <div class="card-body">
        <form class="form theme-form" method="post" action="" enctype="multipart/form-data">
          <div class="row">
            <div class="col-md-3">
              <div class="mb-3">
                <label class="form-label">Pays de résidence</label>
                <select class="form-select select2" id="filtre_country" name="filtre_country">
                  <option value="all">Tous les pays</option>
                  @foreach($viewData["countries"] as $item)
                  <option value="{{$item->id}}"
                          @if($item->id == $viewData["filtre_country"]) selected @endif
                  >{{$item->name}} {{$item->emoji}}</option>
                  @endforeach
                </select>
              </div>
            </div>
            
            <div class="col-md-3">
              <div class="mb-3">
                <label class="form-label">Localité de résidence</label>
                <select class="form-select select2" id="filtre_locality" name="filtre_locality">
                  <option value="all">Toutes les localités</option>
                  @if($viewData["filtre_country"] != "")
                    @foreach($viewData["localities"] as $item)
                      @if($item->country_id == $viewData["filtre_country"])
                      <option value="{{$item->id}}"
                              @if($item->id == $viewData["filtre_locality"]) selected @endif
                        >{{$item->name}}</option>
                      @endif
                    @endforeach
                  @endif
                </select>
              </div>
            </div>
            
            <div class="col-md-3">
              <div class="mb-3">
                <label class="form-label">Profil</label>
                <select class="form-select" id="filtre_profile" name="filtre_profile">
                  <option value="all">Tous les profils</option>
                  <option value="DIFFUSEUR" @if(isset($viewData["filtre_profile"]) && $viewData["filtre_profile"] == "DIFFUSEUR") selected @endif>Diffuseur</option>
                  <option value="ANNONCEUR" @if(isset($viewData["filtre_profile"]) && $viewData["filtre_profile"] == "ANNONCEUR") selected @endif>Annonceur</option>
                  <option value="ADMIN" @if(isset($viewData["filtre_profile"]) && $viewData["filtre_profile"] == "ADMIN") selected @endif>Admin</option>
                </select>
              </div>
            </div>
            
            <div class="col-md-3">
              <div class="mb-3">
                <label class="form-label">Statut</label>
                <select class="form-select" id="filtre_status" name="filtre_status">
                  <option value="all">Tous les statuts</option>
                  <option value="1" @if(isset($viewData["filtre_status"]) && $viewData["filtre_status"] == "1") selected @endif>Actif</option>
                  <option value="0" @if(isset($viewData["filtre_status"]) && $viewData["filtre_status"] == "0") selected @endif>Inactif</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-12">
              <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary me-2">Appliquer les filtres</button>
                <a href="{{ route('admin.users', ['group' => 'all']) }}" class="btn btn-outline-secondary">Réinitialiser</a>
              </div>
            </div>
          </div>
          <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        </form>
      </div>
    </div>
  </div>

  <!-- Users Table -->
  <div class="card">
    <div class="card-header">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h5 class="card-title mb-0">{{$pagetilte}}</h5>
          <small class="text-muted">{{$pagecardtilte}}</small>
        </div>
        <div>
          <div class="btn-group">
            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fa fa-file-export me-1"></i>Exporter
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#" id="export-excel">Excel</a></li>
              <li><a class="dropdown-item" href="#" id="export-pdf">PDF</a></li>
              <li><a class="dropdown-item" href="#" id="export-csv">CSV</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="display table table-striped" id="items_datatable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Identité</th>
              <th>Profil</th>
              <th>Adresse</th>
              <th>Contact</th>
              <th>Statut</th>
              <th>Inscription</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($viewData["items"] as $item)
            <tr>
              <td>{{$item->id}}</td>
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar-sm bg-light-primary rounded-circle text-center me-2">
                    <span class="font-size-18">{{substr($item->firstname, 0, 1)}}</span>
                  </div>
                  <div>
                    <h6 class="mb-0">{{$item->firstname}} {{$item->lastname}}</h6>
                    @if(isset($item->profession))
                    <small class="text-muted">{{$item->profession}}</small>
                    @endif
                  </div>
                </div>
              </td>
              <td>
                @if(isset($item->profiles))
                  @if(strpos($item->profiles, 'DIFFUSEUR') !== false)
                    <span class="badge bg-primary">Diffuseur</span>
                  @elseif(strpos($item->profiles, 'ANNONCEUR') !== false)
                    <span class="badge bg-success">Annonceur</span>
                  @elseif(strpos($item->profiles, 'ADMIN') !== false)
                    <span class="badge bg-danger">Admin</span>
                  @else
                    <span class="badge bg-secondary">{{$item->profiles}}</span>
                  @endif
                @endif
              </td>
              <td>
                @if(isset($item->country))
                <div class="mb-1">
                  <i class="fa fa-globe-africa text-muted me-1"></i> {{$item->country}}
                </div>
                @endif
                @if(isset($item->locality))
                <div>
                  <i class="fa fa-map-marker-alt text-muted me-1"></i> {{$item->locality}}
                </div>
                @endif
              </td>
              <td>
                <div class="mb-1">
                  <i class="fa fa-envelope text-muted me-1"></i> {{$item->email}}
                </div>
                <div>
                  <i class="fa fa-phone text-muted me-1"></i> {{$item->phone}}
                </div>
              </td>
              <td>
                <div class="form-check form-switch">
                  <input class="form-check-input toggle-status" type="checkbox" role="switch" 
                         data-user-id="{{$item->id}}" 
                         @if(isset($item->enabled) && $item->enabled) checked @endif>
                  <label class="form-check-label status-label" for="status-{{$item->id}}">
                    @if(isset($item->enabled) && $item->enabled)
                      <span class="text-success">Actif</span>
                    @else
                      <span class="text-danger">Inactif</span>
                    @endif
                  </label>
                </div>
              </td>
              <td>
                @if(isset($item->created_at))
                {{date('d/m/Y', strtotime($item->created_at))}}
                @endif
              </td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton-{{$item->id}}" data-bs-toggle="dropdown" aria-expanded="false">
                    Actions
                  </button>
                  <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{$item->id}}">
                    <li><a class="dropdown-item" href="{{ route('admin.users', ['group' => 'all', 'action' => 'view', 'id' => $item->id]) }}"><i class="fa fa-eye me-2"></i>Voir</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.users', ['group' => 'all', 'action' => 'edit', 'id' => $item->id]) }}"><i class="fa fa-edit me-2"></i>Modifier</a></li>
                    @if(strpos($item->profiles ?? '', 'DIFFUSEUR') !== false)
                    <li><a class="dropdown-item" href="#"><i class="fa fa-chart-line me-2"></i>Statistiques</a></li>
                    @endif
                    @if(strpos($item->profiles ?? '', 'ANNONCEUR') !== false)
                    <li><a class="dropdown-item" href="#"><i class="fa fa-tasks me-2"></i>Campagnes</a></li>
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal" data-user-id="{{$item->id}}" data-user-name="{{$item->firstname}} {{$item->lastname}}"><i class="fa fa-trash me-2"></i>Supprimer</a></li>
                  </ul>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th>ID</th>
              <th>Identité</th>
              <th>Profil</th>
              <th>Adresse</th>
              <th>Contact</th>
              <th>Statut</th>
              <th>Inscription</th>
              <th>Actions</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Êtes-vous sûr de vouloir supprimer l'utilisateur <span id="delete-user-name" class="fw-bold"></span> ?</p>
        <p class="text-danger">Cette action est irréversible.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <form id="delete-user-form" action="" method="POST" class="d-inline">
          @csrf
          <input type="hidden" name="action" value="delete_user">
          <input type="hidden" name="user_id" id="delete-user-id">
          <button type="submit" class="btn btn-danger">Supprimer</button>
        </form>
      </div>
    </div>
  </div>
</div>

<input type="hidden" name="bjId" id="bjId" value="{{ $viewData["bjId"] }}">
<input type="hidden" name="localitiesJson" id="localitiesJson" value="{{ $viewData["localitiesJson"] }}">
<input type="hidden" name="countriesJson" id="countriesJson" value="{{ $viewData["countriesJson"] }}">
<input type="hidden" name="contenttypesJson" id="contenttypesJson" value="{{ $viewData["contenttypesJson"] }}">
<input type="hidden" name="studiesJson" id="studiesJson" value="{{ $viewData["studiesJson"] }}">
<input type="hidden" name="categoriesJson" id="categoriesJson" value="{{ $viewData["categoriesJson"] }}">

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize DataTable
  const dataTable = $('#items_datatable').DataTable({
    responsive: true,
    dom: 'Bfrtip',
    buttons: [
      'copyHtml5',
      'excelHtml5',
      'csvHtml5',
      'pdfHtml5'
    ],
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, 'Tous']
    ],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
    }
  });

  // Export buttons
  document.getElementById('export-excel').addEventListener('click', function() {
    dataTable.button('.buttons-excel').trigger();
  });
  
  document.getElementById('export-pdf').addEventListener('click', function() {
    dataTable.button('.buttons-pdf').trigger();
  });
  
  document.getElementById('export-csv').addEventListener('click', function() {
    dataTable.button('.buttons-csv').trigger();
  });

  // Country and locality filter interaction
  const countriesJson = JSON.parse(document.getElementById('countriesJson').value);
  const localitiesJson = JSON.parse(document.getElementById('localitiesJson').value);
  const countrySelect = document.getElementById('filtre_country');
  const localitySelect = document.getElementById('filtre_locality');
  
  countrySelect.addEventListener('change', function() {
    const selectedCountryId = this.value;
    
    // Clear the locality dropdown
    localitySelect.innerHTML = '<option value="all">Toutes les localités</option>';
    
    if (selectedCountryId !== 'all') {
      // Filter localities by country and add them to the dropdown
      const filteredLocalities = localitiesJson.filter(locality => locality.country_id == selectedCountryId);
      
      filteredLocalities.forEach(locality => {
        const option = document.createElement('option');
        option.value = locality.id;
        option.textContent = locality.name;
        localitySelect.appendChild(option);
      });
    }
  });
  
  // Status toggle
  const toggleStatusElements = document.querySelectorAll('.toggle-status');
  toggleStatusElements.forEach(element => {
    element.addEventListener('change', function() {
      const userId = this.dataset.userId;
      const statusLabel = this.nextElementSibling;
      
      if (this.checked) {
        statusLabel.innerHTML = '<span class="text-success">Actif</span>';
      } else {
        statusLabel.innerHTML = '<span class="text-danger">Inactif</span>';
      }
      
      // Send AJAX request to update status
      fetch(window.location.pathname, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          action: 'toggle_status',
          user_id: userId,
          enabled: this.checked ? 1 : 0
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Show success toast or notification
          alert(data.message);
        } else {
          // Show error toast or notification
          alert(data.message);
          // Revert the toggle if the request failed
          this.checked = !this.checked;
          if (this.checked) {
            statusLabel.innerHTML = '<span class="text-success">Actif</span>';
          } else {
            statusLabel.innerHTML = '<span class="text-danger">Inactif</span>';
          }
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue lors de la modification du statut');
        // Revert the toggle if the request failed
        this.checked = !this.checked;
        if (this.checked) {
          statusLabel.innerHTML = '<span class="text-success">Actif</span>';
        } else {
          statusLabel.innerHTML = '<span class="text-danger">Inactif</span>';
        }
      });
    });
  });
  
  // Delete user modal
  const deleteModal = document.getElementById('deleteModal');
  if (deleteModal) {
    deleteModal.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      const userId = button.getAttribute('data-user-id');
      const userName = button.getAttribute('data-user-name');
      
      document.getElementById('delete-user-id').value = userId;
      document.getElementById('delete-user-name').textContent = userName;
    });
  }
  
  // Initialize Select2 for dropdowns if available
  if (typeof $.fn.select2 !== 'undefined') {
    $('.select2').select2({
      theme: 'bootstrap-5'
    });
  }
});

document.addEventListener('DOMContentLoaded', function() {
    // Récupérer le jeton CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="_token"]')?.value;
    
    // 1. Gestion des boutons du dropdown
    document.querySelectorAll('.dropdown-menu a.dropdown-item').forEach(item => {
        // Pour les boutons Statistiques et Campagnes, rediriger vers la bonne URL
        if (item.innerHTML.includes('Statistiques') || item.innerHTML.includes('Campagnes')) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Récupérer l'ID de l'utilisateur
                const dropdown = this.closest('.dropdown');
                const button = dropdown.querySelector('button');
                const userId = button.id.split('-')[1] || button.getAttribute('data-user-id');
                
                // Déterminer l'action
                const action = this.innerHTML.includes('Statistiques') ? 'stats' : 'campaigns';
                
                // Rediriger vers l'URL avec l'action et l'ID
                const currentUrl = new URL(window.location.href);
                const baseUrl = currentUrl.pathname.split('?')[0];
                window.location.href = `${baseUrl}?action=${action}&id=${userId}`;
            });
        }
    });
    
    // 2. Gestion du changement de statut (toggle)
    document.querySelectorAll('.toggle-status').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const userId = this.getAttribute('data-user-id');
            const enabled = this.checked ? 1 : 0;
            const statusLabel = this.closest('.form-check').querySelector('.status-label') || 
                               this.nextElementSibling;
            
            // Mise à jour visuelle immédiate
            if (statusLabel) {
                if (this.checked) {
                    statusLabel.innerHTML = '<span class="text-success">Actif</span>';
                } else {
                    statusLabel.innerHTML = '<span class="text-danger">Inactif</span>';
                }
            }
            
            // Envoyer la requête AJAX
            fetch(window.location.pathname, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    action: 'toggle_status',
                    user_id: userId,
                    enabled: enabled
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Réinitialiser le toggle en cas d'erreur
                    this.checked = !this.checked;
                    if (statusLabel) {
                        if (this.checked) {
                            statusLabel.innerHTML = '<span class="text-success">Actif</span>';
                        } else {
                            statusLabel.innerHTML = '<span class="text-danger">Inactif</span>';
                        }
                    }
                    
                    // Afficher l'erreur
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Réinitialiser le toggle en cas d'erreur
                this.checked = !this.checked;
                if (statusLabel) {
                    if (this.checked) {
                        statusLabel.innerHTML = '<span class="text-success">Actif</span>';
                    } else {
                        statusLabel.innerHTML = '<span class="text-danger">Inactif</span>';
                    }
                }
                
                alert('Une erreur est survenue lors de la modification du statut');
            });
        });
    });
    
    // 3. Configuration du modal de suppression
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const userName = button.getAttribute('data-user-name');
            
            const userNameSpan = this.querySelector('#delete-user-name');
            const userIdInput = this.querySelector('#delete-user-id');
            
            if (userNameSpan) userNameSpan.textContent = userName;
            if (userIdInput) userIdInput.value = userId;
            
            // Configurer l'action du formulaire
            const form = this.querySelector('form#delete-user-form');
            if (form) form.action = window.location.pathname;
        });
    }
    
    // 4. Interaction entre les filtres pays et localités
    const countrySelect = document.getElementById('filtre_country');
    const localitySelect = document.getElementById('filtre_locality');
    
    if (countrySelect && localitySelect) {
        countrySelect.addEventListener('change', function() {
            const selectedCountryId = this.value;
            
            // Vider la liste des localités
            localitySelect.innerHTML = '<option value="all">Toutes les localités</option>';
            
            if (selectedCountryId !== 'all' && selectedCountryId !== '') {
                try {
                    // Récupérer les données de localités depuis l'élément caché
                    const localitiesJson = document.getElementById('localitiesJson');
                    if (localitiesJson) {
                        const localities = JSON.parse(localitiesJson.value);
                        
                        // Filtrer et ajouter les localités correspondant au pays sélectionné
                        localities
                            .filter(locality => locality.country_id == selectedCountryId)
                            .forEach(locality => {
                                const option = document.createElement('option');
                                option.value = locality.id;
                                option.textContent = locality.name;
                                localitySelect.appendChild(option);
                            });
                    }
                } catch (error) {
                    console.error('Erreur lors du chargement des localités:', error);
                }
            }
        });
    }
});
</script>
@endpush

@endsection