@extends('admin.layout')

@section('pagecontent')
    @include('alert')

    <div class="container-fluid">

        <!-- Breadcrumb -->
        <div class="row mb-4">
            <div class="col-sm-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.campaigns.show', $viewData['assignment']->task->id) }}">{{ $viewData['assignment']->task->name }}</a>
                        </li>
                        <li class="breadcrumb-item active">Résultat de la soumission</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Card -->
        <div class="card">
            <div class="card-body">

                <div class="row">
                    <!-- Image / Preuve -->
                    <div class="col-md-5 text-center mb-4">
                        <h6 class="fw-bold">Preuve fournie</h6>

                        @php
                            // Décoder le JSON des fichiers
                            $files = json_decode($viewData['assignment']->files, true);

                            // Récupérer le premier fichier
                            $file = is_array($files) && count($files) > 0 ? $files[0] : null;
                        @endphp

                        <div class="p-3 border rounded shadow-sm" style="width: 100%; max-width: 700px; margin: auto;">

                            @if ($file && isset($file['name']))
                                @php
                                    // Extraire l’extension du nom de fichier
                                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                                    $filePath = 'storage/' . $file['name']; // car path = false
                                @endphp

                                @if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                    <img src="{{ asset($filePath) }}" class="img-fluid rounded shadow" alt="Preuve"
                                        style="min-height: 350px; width: 100%; object-fit: cover;">
                                @elseif (in_array(strtolower($ext), ['mp4', 'mov', 'avi']))
                                    <video controls class="w-100 rounded shadow" style="min-height: 350px;">
                                        <source src="{{ asset($filePath) }}">
                                    </video>
                                @else
                                    <a href="{{ asset($filePath) }}" target="_blank" class="btn btn-outline-primary w-100">
                                        Voir le fichier
                                    </a>
                                @endif
                            @else
                                <p class="text-muted text-center">Aucun fichier disponible</p>
                            @endif
                        </div>

                    </div>

                    <!-- Informations -->
                    <div class="col-md-7">
                        <ul class="list-group list-group-flush">

                            <li class="list-group-item d-flex justify-content-between">
                                <span class="fw-bold">Campagne :</span>
                                <span>{{ $viewData['assignment']->task->name }}</span>
                            </li>

                            <li class="list-group-item d-flex justify-content-between">
                                <span class="fw-bold">Annonceur :</span>
                                <span>{{ $viewData['assignment']->task->client->firstname }}
                                    {{ $viewData['assignment']->task->client->lastname }}</span>
                            </li>

                            <li class="list-group-item d-flex justify-content-between">
                                <span class="fw-bold">Vues obtenues :</span>
                                <span>{{ number_format($viewData['assignment']->vues) }}</span>
                            </li>

                            <li class="list-group-item d-flex justify-content-between">
                                <span class="fw-bold">Gain :</span>
                                <span class="text-success fw-bold">{{ number_format($viewData['assignment']->gain) }}
                                    F</span>
                            </li>
                        </ul>

                        <div class="mt-4 d-flex justify-content-end">
                            <a href="{{ route('admin.campaigns.show', $viewData['assignment']->task->id) }}"
                                class="btn btn-secondary me-2">
                                Retour
                            </a>
                            @if ($viewData['assignment']->status !== 'SUBMISSION_ACCEPTED')
                                <!-- Bouton modifier -->
                                <button class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#editViewsModal">
                                    Modifier les vues
                                </button>

                                <!-- Bouton valider -->
                                <form action="{{ route('admin.update.result', $viewData['assignment']->id) }}"
                                    method="post">
                                    @csrf
                                    @method('PUT')
                                    <button class="btn btn-success">
                                        Valider le résultat
                                    </button>
                                </form>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Modal Modifier les vues -->
        <div class="modal fade" id="editViewsModal" tabindex="-1">
            <div class="modal-dialog">
                <form class="modal-content" method="post"
                    action="{{ route('admin.update.result', $viewData['assignment']->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Modifier le nombre de vues</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <label class="form-label">Nombre de vues</label>
                        <input type="number" class="form-control" name="vues" required min="0"
                            value="{{ $viewData['assignment']->vues }}">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Modifier</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
