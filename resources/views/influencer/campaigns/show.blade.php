<!-- File: resources/views/influencer/campaigns/show.blade.php -->
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
                                    <li class="breadcrumb-item"><a href="{{ route('influencer.campaigns.accepted') }}">Mes
                                            Missions</a></li>
                                    <li class="breadcrumb-item active">{{ $viewData['assignment']->task_name ?? 'Détails' }}
                                    </li>
                                </ol>
                            </nav>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group" role="group">
                                @if (($viewData['assignment']->status ?? '') == 'PENDING')
                                    <a href="{{ route('influencer.campaigns.submit', ['id' => $viewData['assignment']->id]) }}"
                                        class="btn btn-success">
                                        <i class="fa fa-check-circle me-1"></i>Soumettre les résultats
                                    </a>
                                @endif
                                <a href="{{ route('influencer.campaigns.accepted') }}" class="btn btn-secondary">
                                    <i class="fa fa-arrow-left me-1"></i>Retour
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Informations de la mission -->
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title">Informations de la campagne</h5>
                            @if (($viewData['assignment']->status ?? '') == 'ASSIGNED')
                                <button type="button" class="btn btn-success btn-sm apply-btn"
                                    data-task-id="{{ $viewData['assignment']->id }}" data-bs-toggle="modal"
                                    data-bs-target="#applyModal">
                                    <i class="fa fa-check-circle me-1"></i>Participer
                                </button>
                            @endif
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="text-muted mb-1">Nom de la campagne</h6>
                                    <h5>{{ $viewData['assignment']->task->name ?? 'N/A' }}</h5>
                                </div>
                                <div class="mb-4">
                                    <h6 class="text-muted mb-1">Statut</h6>
                                    @if (($viewData['assignment']->status ?? '') == 'PENDING')
                                        <span class="badge bg-warning">En cours</span>
                                    @elseif(($viewData['assignment']->status ?? '') == 'ASSIGNED')
                                        <span class="badge bg-primary">Disponible</span>
                                    @elseif(($viewData['assignment']->status ?? '') == 'SUBMITED')
                                        <span class="badge bg-success">Terminée</span>
                                    @elseif($viewData['assignment']->status == 'SUBMISSION_ACCEPTED')
                                        <span class="badge bg-success">Résultat validé</span>
                                    @elseif($viewData['assignment']->status == 'SUBMISSION_REJECTED')
                                        <span class="badge bg-danger">Résultat rejeté</span>
                                    @elseif(($viewData['assignment']->status ?? '') == 'REJECTED')
                                        <span class="badge bg-danger">Rejetée</span>
                                    @else
                                        <span
                                            class="badge bg-secondary">{{ $viewData['assignment']->status ?? 'N/A' }}</span>
                                    @endif
                                </div>
                                <div class="mb-4">
                                    <h6 class="text-muted mb-1">Période</h6>
                                    <p>
                                        Du
                                        {{ isset($viewData['assignment']->task->startdate) ? date('d/m/Y', strtotime($viewData['assignment']->task->startdate)) : 'N/A' }}
                                        au
                                        {{ isset($viewData['assignment']->task->enddate) ? date('d/m/Y', strtotime($viewData['assignment']->task->enddate)) : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                {{-- <div class="mb-4">
                                    <h6 class="text-muted mb-1">Vues attendues</h6>
                                    <h5>{{ number_format($viewData['assignment']->task->total_views_estimated ?? 0) }}</h5>
                                </div> --}}
                                <div class="mb-4">
                                    <h6 class="text-muted mb-1">Date d'assignation</h6>
                                    <p>
                                        {{ isset($viewData['assignment']->created_at)
                                            ? \Carbon\Carbon::parse($viewData['assignment']->created_at)->addHours(24)->format('d/m/Y H:i')
                                            : 'N/A' }}
                                    </p>
                                </div>
                                <div class="mb-4">
                                    <h6 class="text-muted mb-1">Annonceur</h6>
                                    <h6>{{ $viewData['assignment']->task->client->firstname . ' ' . $viewData['assignment']->task->client->lastname ?? 'N/A' }}
                                    </h6>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-muted mb-1">Description</h6>
                            <p>{{ $viewData['assignment']->task->descriptipon ?? 'Aucune description disponible' }}</p>
                        </div>

                        @if ($viewData['assignment']->status !== 'ASSIGNED')
                            <div class="mb-4">
                                <h6 class="text-muted mb-1">Instructions</h6>
                                <div class="alert alert-info">
                                    <p class="mb-0">
                                        {{ $viewData['assignment']->task->legend . ' ' . $viewData['assignment']->task->url ?? 'Aucune instruction spécifique pour cette campagne. Veuillez diffuser le contenu sur vos plateformes habituelles.' }}
                                    </p>
                                </div>
                            </div>

                            @if (!empty($viewData['assignment']->task->files))
                                @php
                                    $files = json_decode($viewData['assignment']->task->files);
                                @endphp

                                <div class="mb-4">
                                    <h6 class="text-muted mb-2">Fichiers de la campagne</h6>

                                    <div class="row g-3">

                                        @foreach ($files as $file)
                                            @php
                                                $fileName = $file->name;
                                                $fileUrl = asset('storage/' . $fileName);

                                                // Extension pour savoir le type
                                                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                            @endphp

                                            <div class="col-md-4">
                                                <div class="card">
                                                    <div class="card-body p-3">

                                                        {{-- Aperçu image --}}
                                                        @if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                            <img src="{{ asset($fileUrl) }}" class="img-fluid rounded mb-2"
                                                                alt="Image">

                                                            {{-- Aperçu vidéo --}}
                                                        @elseif (in_array($ext, ['mp4', 'mov', 'avi', 'mkv']))
                                                            <video class="w-100 mb-2 rounded" controls>
                                                                <source src="{{ asset($fileUrl) }}"
                                                                    type="video/{{ $ext }}">
                                                            </video>

                                                            {{-- Aperçu fichier générique --}}
                                                        @else
                                                            <i class="fa fa-file fa-3x text-primary mb-2"></i>
                                                        @endif

                                                        <p class="mb-1 text-truncate">{{ $fileName }}</p>

                                                        <a href="{{ asset($fileUrl) }}" download target="_blank"
                                                            class="btn btn-sm btn-light">
                                                            <i class="fa fa-download me-1"></i>
                                                            Télécharger
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- Progression et résumé -->
            <div class="col-xl-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Progression</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $progress = 0;
                            if ($viewData['assignment']->status == 'ASSIGNED') {
                                $progress = 25;
                            } elseif ($viewData['assignment']->status == 'PENDING') {
                                $progress = 50;
                            } elseif (
                                $viewData['assignment']->status == 'SUBMITED' ||
                                $viewData['assignment']->status == 'SUBMISSION_ACCEPTED' ||
                                $viewData['assignment']->status == 'SUBMISSION_REJECTED'
                            ) {
                                $progress = 100;
                            }
                        @endphp

                        <div class="progress-wizard">
                            <div class="progress-wizard-bar">
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <ul class="progress-wizard-steps d-flex justify-content-between ps-0">
                                <li class="{{ $progress >= 25 ? 'active' : '' }} text-center">
                                    <div class="step">
                                        <div class="step-icon">
                                            <i class="fa fa-check-circle"></i>
                                        </div>
                                    </div>
                                    <p class="mt-2">Assignation</p>
                                </li>
                                <li class="{{ $progress >= 50 ? 'active' : '' }} text-center">
                                    <div class="step">
                                        <div class="step-icon">
                                            <i class="fa fa-play-circle"></i>
                                        </div>
                                    </div>
                                    <p class="mt-2">En cours</p>
                                </li>
                                <li class="{{ $progress >= 100 ? 'active' : '' }} text-center">
                                    <div class="step">
                                        <div class="step-icon">
                                            <i class="fa fa-check-double"></i>
                                        </div>
                                    </div>
                                    <p class="mt-2">Terminée</p>
                                </li>
                            </ul>
                        </div>

                        @php
                            use Carbon\Carbon;
                            $end = Carbon::parse($viewData['assignment']->task_enddate);
                            $now = now();
                            // Différence en heures avec signe
                            $hours = $now->diffInHours($end, false);
                        @endphp

                        @if ($viewData['assignment']->status == 'PENDING')
                            <div class="alert alert-warning mt-4">
                                <p class="mb-0">
                                    <i class="fa fa-clock me-2"></i>
                                    @if ($hours >= 0)
                                        Il vous reste {{ number_format($hours) }} heure(s) pour terminer cette mission.
                                    @else
                                        Vous avez {{ number_format(abs($hours)) }} heure(s) de retard sur cette mission.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                @if ($viewData['assignment']->status == 'COMPLETED')
                    <div class="card">
                        <div class="card-header bg-success bg-soft">
                            <h5 class="card-title text-success mb-0">Résultats</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <div class="avatar-lg mx-auto mb-3 bg-success bg-opacity-10 rounded-circle">
                                    <span class="avatar-title text-success font-size-24">
                                        <i class="fa fa-chart-line"></i>
                                    </span>
                                </div>
                                <h4 class="mb-0">{{ number_format($viewData['assignment']->vues ?? 0) }}</h4>
                                <p class="text-muted mb-0">Vues totales</p>
                            </div>

                            <div class="mb-4">
                                <h6 class="text-muted mb-1">Date de soumission</h6>
                                <p>{{ isset($viewData['assignment']->completion_date) ? date('d/m/Y H:i', strtotime($viewData['assignment']->completion_date)) : 'N/A' }}
                                </p>
                            </div>

                            @if (isset($viewData['assignment']->rating) && $viewData['assignment']->rating > 0)
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2">Évaluation</h6>
                                    <div class="text-center">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= $viewData['assignment']->rating)
                                                <i class="fa fa-star fa-2x text-warning"></i>
                                            @else
                                                <i class="fa fa-star fa-2x text-muted"></i>
                                            @endif
                                        @endfor
                                    </div>
                                </div>
                            @endif

                            @if (!empty($viewData['assignment']->feedback))
                                <div class="mb-0">
                                    <h6 class="text-muted mb-2">Commentaires de l'annonceur</h6>
                                    <div class="border rounded p-3 bg-light">
                                        <p class="mb-0 fst-italic">{{ $viewData['assignment']->feedback }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal pour participer -->
    <div class="modal fade" id="applyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Partciper à la campagne</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="applicationForm" method="post">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="task_id" id="taskIdInput">
                        <p>En participant à cette campagne, vous vous engagez à atteindre le nombre de vues estimé indiqué
                            sur votre profil.</p>

                        {{-- <div class="mb-3">
                            <label class="form-label">Message de candidature (optionnel)</label>
                            <textarea class="form-control" name="message" rows="4"
                                placeholder="Expliquez pourquoi vous êtes le meilleur diffuseur pour cette campagne..."></textarea>
                        </div> --}}

                        {{-- <div class="mb-3">
                            <label class="form-label">Vues estimées</label>
                            <input type="number" class="form-control" name="estimated_views" required min="0"
                                placeholder="Ex: 1000">
                            <div class="form-text">
                                Indiquez une estimation réaliste du nombre de vues que vous pouvez générer.
                            </div>
                        </div> --}}
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="submitApplication">Participer</button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .progress-wizard-steps {
                list-style: none;
                margin-top: 30px;
            }

            .progress-wizard-steps li {
                position: relative;
            }

            .progress-wizard-steps li .step {
                width: 50px;
                height: 50px;
                margin: 0 auto;
                border-radius: 50%;
                background-color: #f8f9fa;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                z-index: 10;
            }

            .progress-wizard-steps li .step .step-icon {
                font-size: 24px;
                color: #adb5bd;
            }

            .progress-wizard-steps li.active .step {
                background-color: #d4edda;
            }

            .progress-wizard-steps li.active .step .step-icon {
                color: #28a745;
            }

            .progress-wizard-bar {
                position: relative;
                margin: 30px 0;
                height: 10px;
            }

            .progress-wizard-bar .progress {
                height: 8px;
                border-radius: 4px;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            $(document).on('click', '.apply-btn', function() {

                let taskId = $(this).data('task-id'); // <-- tu avais oublié ça

                console.log("Task ID =", taskId);

                $('#applicationForm').attr('action', '/admin/influencer/agent/campaigns/' + taskId + '/accepte');
            });

            // Soumission du formulaire
            $('#submitApplication').on('click', function() {
                console.log('SUBMITTED');
                $('#applicationForm').submit();
            });
        </script>
    @endpush
@endsection
