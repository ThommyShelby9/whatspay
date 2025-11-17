<!-- File: resources/views/influencer/campaigns/available.blade.php -->
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
                                    <li class="breadcrumb-item"><a href="{{ route('influencer.dashboard') }}">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item active">Campagnes Disponibles</li>
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
                        <h5 class="card-title mb-0">Filtrer les campagnes</h5>
                        <a class="btn btn-sm btn-link" data-bs-toggle="collapse" href="#collapseFilter" role="button"
                            aria-expanded="false">
                            <i class="fa fa-chevron-down"></i>
                        </a>
                    </div>
                    <div class="collapse" id="collapseFilter">
                        <div class="card-body">
                            <form method="get" action="{{ route('influencer.campaigns.available') }}">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Catégorie</label>
                                            <select class="form-select" name="filtre_category">
                                                <option value="">Toutes les catégories</option>
                                                @foreach ($viewData['categories'] ?? [] as $category)
                                                    <option value="{{ $category->id }}"
                                                        {{ request()->get('filtre_category') == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    {{-- <div class="col-md-3">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Budget minimum</label>
                                            <input type="number" class="form-control" name="filtre_budget_min"
                                                value="{{ request()->get('filtre_budget_min') }}" placeholder="Ex: 5000">
                                        </div>
                                    </div> --}}
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Période</label>
                                            <div class="input-group">
                                                <input type="date" class="form-control" name="filtre_start_date"
                                                    value="{{ request()->get('filtre_start_date') }}">
                                                <span class="input-group-text">au</span>
                                                <input type="date" class="form-control" name="filtre_end_date"
                                                    value="{{ request()->get('filtre_end_date') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-center mt-2">
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

        <!-- Liste des campagnes -->
        <div class="row">
            @forelse($viewData["availableTasks"] ?? [] as $task)
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">{{ $task->name ?? 'Sans titre' }}</h5>
                                <span class="badge bg-primary">{{ number_format($task->budget ?? 0) }} F</span>
                            </div>

                            <p class="text-muted mb-3">
                                {{ Str::limit($task->descriptipon ?? 'Aucune description disponible', 100) }}
                            </p>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted">Période</span>
                                    <span class="fw-bold">
                                        {{ isset($task->startdate) ? date('d/m/Y', strtotime($task->startdate)) : 'N/A' }}
                                        -
                                        {{ isset($task->enddate) ? date('d/m/Y', strtotime($task->enddate)) : 'N/A' }}
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <span class="text-muted d-block mb-2">Catégories</span>
                                <div class="d-flex flex-wrap">
                                    @if (!empty($task->categories))
                                        @foreach ($task->categories as $cat)
                                            <span class="badge bg-info me-1 mb-1">{{ $cat->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">Aucune catégorie</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-3 text-center">
                                <button type="button" class="btn btn-success btn-sm w-100 apply-btn"
                                    data-task-id="{{ $task->id }}" data-bs-toggle="modal" data-bs-target="#applyModal">
                                    <i class="fa fa-check-circle me-1"></i>Postuler
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <img src="{{ asset('images/empty-state.svg') }}" alt="Aucune campagne" height="120"
                                class="mb-4">
                            <h5>Aucune campagne disponible</h5>
                            <p class="text-muted">Il n'y a actuellement aucune campagne disponible correspondant à vos
                                critères.</p>
                            <a href="{{ route('influencer.campaigns.available') }}"
                                class="btn btn-primary mt-2">Rafraîchir</a>
                        </div>
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

    <!-- Modal pour postuler -->
    <div class="modal fade" id="applyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Postuler à la campagne</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="applicationForm" method="post">
                        @csrf
                        <input type="hidden" name="task_id" id="taskIdInput">

                        <div class="mb-3">
                            <label class="form-label">Message de candidature (optionnel)</label>
                            <textarea class="form-control" name="message" rows="4"
                                placeholder="Expliquez pourquoi vous êtes le meilleur diffuseur pour cette campagne..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Vues estimées</label>
                            <input type="number" class="form-control" name="estimated_views" required min="0"
                                placeholder="Ex: 1000">
                            <div class="form-text">
                                Indiquez une estimation réaliste du nombre de vues que vous pouvez générer.
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="submitApplication">Envoyer ma candidature</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                // Mettre à jour l'ID de la tâche dans le modal
                $('.apply-btn').click(function() {
                    var taskId = $(this).data('task-id');
                    $('#taskIdInput').val(taskId);
                });

                // Soumettre la candidature
                $('#submitApplication').click(function() {
                    // Placeholder for application submission logic
                    // You would typically submit the form via AJAX here

                    // For demo purposes, show success message and close modal
                    alert('Votre candidature a été soumise avec succès !');
                    $('#applyModal').modal('hide');
                });
            });
        </script>
    @endpush
@endsection
