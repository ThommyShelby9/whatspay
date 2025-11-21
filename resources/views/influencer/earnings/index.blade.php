<!-- File: resources/views/influencer/earnings/index.blade.php (Enhanced) -->
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
                                    <li class="breadcrumb-item active">Mes Gains</li>
                                </ol>
                            </nav>
                        </div>
                        {{-- <div class="col-auto">
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-download me-1"></i>Exporter
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" data-export="csv">
                                            <i class="fa fa-file-csv me-2"></i>Fichier CSV
                                        </a></li>
                                    <li><a class="dropdown-item" href="#" data-export="pdf">
                                            <i class="fa fa-file-pdf me-2"></i>Fichier PDF
                                        </a></li>
                                </ul>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>

        <!-- Résumé des gains -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-lg bg-soft-success rounded-circle text-center">
                                    <i class="fa fa-coins text-success font-size-24 mt-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-4">
                                <h3 class="fw-bold">Solde disponible</h3>
                                <h1 class="mb-3 text-success" id="available-balance">
                                    {{ number_format($viewData['earningsStats']['available_for_withdrawal'] ?? 0, 0, ',', ' ') }}
                                    F
                                </h1>
                                <div class="row mb-3">
                                    <div class="col-4">
                                        <h6 class="text-muted mb-1">Gains totaux</h6>
                                        <h5 class="text-primary">
                                            {{ number_format($viewData['earningsStats']['total_earnings'] ?? 0, 0, ',', ' ') }}
                                            F</h5>
                                    </div>
                                    <div class="col-4">
                                        <h6 class="text-muted mb-1">Ce mois-ci</h6>
                                        <h5>{{ number_format($viewData['earningsStats']['current_month'] ?? 0, 0, ',', ' ') }}
                                            F</h5>
                                    </div>
                                    <div class="col-4">
                                        <h6 class="text-muted mb-1">Mois précédent</h6>
                                        <h5>{{ number_format($viewData['earningsStats']['last_month'] ?? 0, 0, ',', ' ') }}
                                            F</h5>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                        data-bs-target="#withdrawalModal"
                                        {{ ($viewData['earningsStats']['available_for_withdrawal'] ?? 0) < 500 ? 'disabled' : '' }}>
                                        <i class="fa fa-money-bill-wave me-1"></i> Effectuer un retrait
                                    </button>
                                    <button type="button" class="btn btn-outline-info" id="refresh-earnings">
                                        <i class="fa fa-sync-alt me-1"></i> Actualiser
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-soft-warning">
                        <h5 class="card-title text-warning mb-0">
                            <i class="fa fa-clock me-2"></i>Paiements en attente
                        </h5>
                    </div>
                    <div class="card-body">
                        <h4 class="mb-1 text-warning">
                            {{ number_format($viewData['earningsStats']['pending_payment'] ?? 0, 0, ',', ' ') }} F</h4>
                        <p class="text-muted mb-3">En cours de validation</p>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Prochain paiement automatique</span>
                            <span class="fw-bold">{{ date('d/m/Y', strtotime('+1 day')) }}</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Heure de traitement</span>
                            <span class="fw-bold">02:00</span>
                        </div>

                        <div class="progress mb-2" style="height: 8px;">
                            @php
                                $now = \Carbon\Carbon::now();
                                $nextPayment = \Carbon\Carbon::tomorrow()->setTime(2, 0);
                                $totalMinutes = 24 * 60;
                                $elapsed = $now->diffInMinutes($now->copy()->startOfDay());
                                $progress = ($elapsed / $totalMinutes) * 100;
                            @endphp
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $progress }}%"
                                aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                        <small class="text-muted">
                            <i class="fa fa-info-circle me-1"></i>
                            Les paiements sont traités automatiquement chaque jour à 2h00
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques détaillées -->
        @if (isset($viewData['assignmentStats']))
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="text-primary">{{ $viewData['assignmentStats']['completed'] ?? 0 }}</h3>
                            <p class="mb-0 text-muted">Campagnes terminées</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="text-success">
                                {{ number_format($viewData['assignmentStats']['total_vues'] ?? 0, 0, ',', ' ') }}</h3>
                            <p class="mb-0 text-muted">Vues totales</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="text-warning">{{ $viewData['assignmentStats']['pending'] ?? 0 }}</h3>
                            <p class="mb-0 text-muted">En cours</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            @php $avgPerView = ($viewData['assignmentStats']['total_vues'] ?? 0) > 0 ? 1 : 0; @endphp
                            <h3 class="text-info">{{ $avgPerView }} F</h3>
                            <p class="mb-0 text-muted">Par vue</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Graphique des gains -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-chart-line me-2"></i>Évolution des gains
                        </h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-light period-btn active" data-period="6months">6
                                mois</button>
                            <button type="button" class="btn btn-sm btn-light period-btn" data-period="year">1
                                an</button>
                            <button type="button" class="btn btn-sm btn-light period-btn" data-period="all">Tout</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="earnings-chart" style="height: 350px;">
                            <div class="text-center py-5" id="chart-loading">
                                <i class="fa fa-spinner fa-spin fa-2x"></i>
                                <p class="mt-2">Chargement des données...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique des transactions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-history me-2"></i>Historique des gains
                        </h5>
                    </div>
                    <div class="card-body">
                        @if (isset($viewData['earningsHistory']) && count($viewData['earningsHistory']) > 0)
                            <div class="table-responsive">
                                <table class="table" id="transactions-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th>Type</th>
                                            <th>Montant</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($viewData['earningsHistory'] as $transaction)
                                            <tr>
                                                <td>{{ date('d/m/Y H:i', strtotime($transaction->created_at)) }}</td>
                                                <td>{{ $transaction->description }}</td>
                                                <td>
                                                    @php
                                                        $typeConfig = [
                                                            'Crédit' => ['class' => 'success', 'icon' => 'plus'],
                                                            'Débit' => ['class' => 'danger', 'icon' => 'minus'],
                                                            'BUDGET_RESERVE' => [
                                                                'class' => 'warning',
                                                                'icon' => 'lock',
                                                            ],
                                                            'PLATFORM_COMMISSION' => [
                                                                'class' => 'info',
                                                                'icon' => 'percentage',
                                                            ],
                                                        ];
                                                        $config = $typeConfig[$transaction->type] ?? [
                                                            'class' => 'secondary',
                                                            'icon' => 'circle',
                                                        ];
                                                    @endphp
                                                    <span class="badge bg-{{ $config['class'] }}">
                                                        <i
                                                            class="fa fa-{{ $config['icon'] }} me-1"></i>{{ $transaction->type }}
                                                    </span>
                                                </td>
                                                <td
                                                    class="{{ $transaction->type == 'Crédit' ? 'text-success' : 'text-danger' }}">
                                                    <strong>
                                                        {{ $transaction->type == 'Crédit' ? '+' : '-' }}{{ number_format($transaction->amount, 0, ',', ' ') }}
                                                        F
                                                    </strong>
                                                </td>
                                                <td>
                                                    @php
                                                        $statusConfig = [
                                                            'COMPLETED' => ['class' => 'success', 'text' => 'Complété'],
                                                            'PENDING' => ['class' => 'warning', 'text' => 'En attente'],
                                                            'PROCESSING' => ['class' => 'info', 'text' => 'En cours'],
                                                            'FAILED' => ['class' => 'danger', 'text' => 'Échoué'],
                                                        ];
                                                        $sConfig = $statusConfig[$transaction->status] ?? [
                                                            'class' => 'secondary',
                                                            'text' => $transaction->status,
                                                        ];
                                                    @endphp
                                                    <span
                                                        class="badge bg-{{ $sConfig['class'] }}">{{ $sConfig['text'] }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <div class="avatar-lg mx-auto mb-3">
                                    <i class="fa fa-chart-line fa-3x text-muted"></i>
                                </div>
                                <h5>Aucun gain encore</h5>
                                <p class="text-muted mb-0">Vos premiers gains apparaîtront ici une fois vos campagnes
                                    validées.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Demande de retrait -->
    <div class="modal fade" id="withdrawalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa fa-money-bill-wave me-2"></i>Demande de retrait
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="withdrawalForm">
                        @csrf

                        <!-- Informations de solde -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Solde disponible:</strong>
                                            <span
                                                class="text-success">{{ number_format($viewData['earningsStats']['available_for_withdrawal'] ?? 0, 0, ',', ' ') }}
                                                F</span>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Minimum de retrait:</strong> 500 F
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Montant à retirer *</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="amount" id="withdrawal-amount"
                                            min="500"
                                            max="{{ $viewData['earningsStats']['available_for_withdrawal'] ?? 0 }}"
                                            step="100" placeholder="Montant" required>
                                        <span class="input-group-text">F</span>
                                    </div>
                                    <div class="form-text">
                                        <div class="d-flex justify-content-between">
                                            <span>Minimum: 500 F</span>
                                            <span>Maximum:
                                                {{ number_format($viewData['earningsStats']['available_for_withdrawal'] ?? 0, 0, ',', ' ') }}
                                                F</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Méthode de retrait *</label>
                                    <select class="form-select" name="withdrawal_method" id="withdrawal-method" required>
                                        <option value="">Sélectionner une méthode</option>
                                        <option value="mobile_money">Mobile Money</option>
                                        <option value="bank">Virement bancaire</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Champs conditionnels pour Mobile Money -->
                        <div id="mobile-money-fields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Numéro Mobile Money *</label>
                                        <input type="tel" class="form-control" name="phone" id="withdrawal-phone"
                                            placeholder="Ex: 0701234567" pattern="[0-9]{8,15}">
                                        <div class="form-text">Orange Money, MTN Money, Moov Money</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Réseau</label>
                                        <select class="form-select" name="network">
                                            <option value="">Détection automatique</option>
                                            <option value="orange">Orange Money</option>
                                            <option value="mtn">MTN Money</option>
                                            <option value="moov">Moov Money</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Champs conditionnels pour virement bancaire -->
                        <div id="bank-fields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Nom de la banque *</label>
                                        <select class="form-select" name="bank_name">
                                            <option value="">Sélectionner une banque</option>
                                            <option value="SGBCI">SGBCI</option>
                                            <option value="BICICI">BICICI</option>
                                            <option value="Ecobank">Ecobank</option>
                                            <option value="UBA">UBA</option>
                                            <option value="Banque Atlantique">Banque Atlantique</option>
                                            <option value="CORIS Bank">CORIS Bank</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Numéro de compte *</label>
                                        <input type="text" class="form-control" name="bank_account"
                                            placeholder="Ex: CI00 1234 5678 9012 3456 7890 12">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label">Titulaire du compte</label>
                                        <input type="text" class="form-control" name="account_holder"
                                            placeholder="Nom du titulaire du compte">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Montants suggérés -->
                        <div class="mb-4">
                            <label class="form-label">Montants suggérés</label>
                            <div class="row">
                                @php
                                    $balance = $viewData['earningsStats']['available_for_withdrawal'] ?? 0;
                                    $suggestions = [];
                                    if ($balance >= 500) {
                                        $suggestions[] = 500;
                                    }
                                    if ($balance >= 1000) {
                                        $suggestions[] = 1000;
                                    }
                                    if ($balance >= 5000) {
                                        $suggestions[] = 5000;
                                    }
                                    if ($balance >= 10000) {
                                        $suggestions[] = 10000;
                                    }
                                    if ($balance >= 25000) {
                                        $suggestions[] = 25000;
                                    }
                                    if ($balance > 50000) {
                                        $suggestions[] = floor($balance / 2);
                                    }
                                    if ($balance >= 1000) {
                                        $suggestions[] = $balance;
                                    } // Tout retirer
                                @endphp

                                @foreach (array_unique($suggestions) as $amount)
                                    <div class="col-md-4 col-sm-6 mb-2">
                                        <button type="button" class="btn btn-light w-100 suggested-withdrawal-amount"
                                            data-amount="{{ $amount }}">
                                            @if ($amount == $balance)
                                                Tout retirer
                                            @else
                                                {{ number_format($amount, 0, ',', ' ') }} F
                                            @endif
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Informations importantes -->
                        <div class="alert alert-warning">
                            <h6><i class="fa fa-exclamation-triangle me-2"></i>Informations importantes</h6>
                            <ul class="mb-0">
                                <li><strong>Mobile Money:</strong> Traitement instantané</li>
                                <li><strong>Virement bancaire:</strong> Traitement dans 2-3 jours ouvrables</li>
                                <li><strong>Frais:</strong> Aucun frais de retrait</li>
                                <li><strong>Minimum:</strong> 500 FCFA</li>
                            </ul>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-success" id="submit-withdrawal">
                        <i class="fa fa-paper-plane me-1"></i>Confirmer le retrait
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @push('scripts')
        <script>
            $(document).ready(function() {
                let earningsChart;

                // Initialize DataTable
                $('#transactions-table').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
                    },
                    order: [
                        [0, 'desc']
                    ],
                    pageLength: 10,
                    responsive: true
                });

                // Initialize chart with default data
                initializeChart();

                // Period buttons for chart
                $('.period-btn').click(function() {
                    $('.period-btn').removeClass('active');
                    $(this).addClass('active');

                    var period = $(this).data('period');
                    updateChart(period);
                });

                // Withdrawal method selection
                $('#withdrawal-method').change(function() {
                    var method = $(this).val();

                    $('#mobile-money-fields, #bank-fields').hide();

                    if (method === 'mobile_money') {
                        $('#mobile-money-fields').show();
                        $('#withdrawal-phone').attr('required', true);
                        $('select[name="bank_name"], input[name="bank_account"]').removeAttr('required');
                    } else if (method === 'bank') {
                        $('#bank-fields').show();
                        $('select[name="bank_name"], input[name="bank_account"]').attr('required', true);
                        $('#withdrawal-phone').removeAttr('required');
                    }
                });

                // Suggested withdrawal amounts
                $('.suggested-withdrawal-amount').click(function() {
                    var amount = $(this).data('amount');
                    $('#withdrawal-amount').val(amount);
                });

                // Submit withdrawal request
                $('#submit-withdrawal').click(function() {
                    var form = $('#withdrawalForm');
                    var formData = new FormData(form[0]);

                    // Validation
                    var amount = parseInt($('#withdrawal-amount').val());
                    var method = $('#withdrawal-method').val();
                    var maxAmount = {{ $viewData['earningsStats']['available_for_withdrawal'] ?? 0 }};

                    if (!amount || amount < 500) {
                        alert('Le montant minimum de retrait est de 500 FCFA');
                        return;
                    }

                    if (amount > maxAmount) {
                        alert('Montant supérieur au solde disponible');
                        return;
                    }

                    if (!method) {
                        alert('Veuillez sélectionner une méthode de retrait');
                        return;
                    }

                    if (method === 'mobile_money' && !$('#withdrawal-phone').val()) {
                        alert('Veuillez saisir votre numéro de téléphone');
                        return;
                    }

                    if (method === 'bank' && (!$('select[name="bank_name"]').val() || !$(
                            'input[name="bank_account"]').val())) {
                        alert('Veuillez compléter les informations bancaires');
                        return;
                    }

                    // Disable button and show loading
                    $(this).html('<i class="fa fa-spinner fa-spin me-1"></i>Traitement...');
                    $(this).prop('disabled', true);

                    // Submit request
                    $.ajax({
                        url: '{{ route('influencer.earnings.withdraw') }}',
                        method: 'POST',
                        data: Object.fromEntries(formData),
                        success: function(response) {
                            if (response.success) {
                                alert(response.message);
                                location.reload();
                            } else {
                                alert(response.message);
                                resetSubmitButton();
                            }
                        },
                        error: function(xhr) {
                            var message = 'Une erreur est survenue';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            alert(message);
                            resetSubmitButton();
                        }
                    });
                });

                function resetSubmitButton() {
                    $('#submit-withdrawal').html('<i class="fa fa-paper-plane me-1"></i>Confirmer le retrait');
                    $('#submit-withdrawal').prop('disabled', false);
                }

                // Export functionality
                $('[data-export]').click(function(e) {
                    e.preventDefault();
                    var format = $(this).data('export');
                    var period = $('.period-btn.active').data('period');

                    window.location = '{{ route('influencer.earnings.export') }}?format=' + format +
                        '&period=' + period;
                });

                // Refresh earnings
                $('#refresh-earnings').click(function() {
                    $(this).find('i').addClass('fa-spin');

                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                });

                // Chart functions
                function initializeChart() {
                    var options = {
                        series: [{
                            name: 'Gains',
                            data: @json($viewData['chartData']['data'] ?? [])
                        }],
                        chart: {
                            height: 350,
                            type: 'area',
                            toolbar: {
                                show: false
                            },
                            zoom: {
                                enabled: false
                            }
                        },
                        plotOptions: {
                            area: {
                                fillTo: 'origin'
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 3
                        },
                        xaxis: {
                            categories: @json($viewData['chartData']['categories'] ?? []),
                        },
                        yaxis: {
                            title: {
                                text: 'Montant (FCFA)'
                            },
                            labels: {
                                formatter: function(value) {
                                    return new Intl.NumberFormat('fr-FR').format(value) + ' F';
                                }
                            }
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.7,
                                opacityTo: 0.3,
                                stops: [0, 100]
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: function(val) {
                                    return new Intl.NumberFormat('fr-FR').format(val) + " F";
                                }
                            }
                        },
                        colors: ['#45cb85']
                    };

                    earningsChart = new ApexCharts(document.querySelector("#earnings-chart"), options);
                    earningsChart.render();

                    // Hide loading
                    $('#chart-loading').hide();
                }

                function updateChart(period) {
                    $('#chart-loading').show();

                    $.get('{{ route('influencer.earnings.chart-data') }}', {
                            period: period
                        })
                        .done(function(response) {
                            if (response.success) {
                                earningsChart.updateOptions({
                                    xaxis: {
                                        categories: response.data.categories
                                    },
                                    series: [{
                                        name: 'Gains',
                                        data: response.data.data
                                    }]
                                });
                            }
                        })
                        .fail(function() {
                            console.log('Erreur lors du chargement des données du graphique');
                        })
                        .always(function() {
                            $('#chart-loading').hide();
                        });
                }
            });
        </script>
    @endpush
@endsection
