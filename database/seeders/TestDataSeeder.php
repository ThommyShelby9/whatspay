<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Task;
use App\Models\Assignment;
use App\Models\Role;
use App\Models\Category;
use App\Models\Locality;
use App\Models\Occupation;
use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Crée des données de test pour :
     * - 3 comptes annonceurs avec 2-5 campagnes chacun
     * - 5 comptes diffuseurs avec différents profils
     * - Assignments automatiques basés sur les critères d'éligibilité
     */
    public function run(): void
    {
        $this->command->info('🚀 Début du seeding des données de test...');

        // Récupérer les données de référence nécessaires
        $annonceurRole = Role::where('typerole', 'ANNONCEUR')->first();
        $diffuseurRole = Role::where('typerole', 'DIFFUSEUR')->first();
        $adminRole = Role::where('typerole', 'ADMIN')->first();

        $country = Country::where('name', 'Benin')->first() ?? Country::first();
        $localities = Locality::where('type', 2)->limit(5)->get();
        $categories = Category::where('enabled', true)->limit(10)->get();
        $occupations = Occupation::where('enabled', true)->limit(10)->get();

        if (!$annonceurRole || !$diffuseurRole) {
            $this->command->error('❌ Les rôles ANNONCEUR et DIFFUSEUR doivent exister. Exécutez TablesSeeder d\'abord.');
            return;
        }

        // ========================================
        // 1. CRÉATION DES ANNONCEURS
        // ========================================
        $this->command->info('👔 Création des 3 annonceurs...');

        $annonceurs = [];
        $annonceurData = [
            [
                'firstname' => 'Jean',
                'lastname' => 'Dupont',
                'email' => 'jean.dupont@example.com',
                'company' => 'Tech Solutions BJ',
                'domain' => 'Technologie',
            ],
            [
                'firstname' => 'Marie',
                'lastname' => 'Kouassi',
                'email' => 'marie.kouassi@example.com',
                'company' => 'Fashion Store Africa',
                'domain' => 'Mode & Beauté',
            ],
            [
                'firstname' => 'Pierre',
                'lastname' => 'Mensah',
                'email' => 'pierre.mensah@example.com',
                'company' => 'Agro Business',
                'domain' => 'Agriculture',
            ],
        ];

        foreach ($annonceurData as $data) {
            // Vérifier si l'utilisateur existe déjà
            $annonceur = User::where('email', $data['email'])->first();

            if (!$annonceur) {
                $annonceur = User::create([
                    'id' => Str::uuid(),
                    'firstname' => $data['firstname'],
                    'lastname' => $data['lastname'],
                    'email' => $data['email'],
                    'password' => Hash::make('Password123!'),
                    'enabled' => true,
                    'email_verified_at' => now(),
                    'country_id' => $country->id,
                    'locality_id' => $localities->random()->id ?? null,
                    'phone' => '+229' . rand(60000000, 99999999),
                    'vuesmoyen' => 0, // Les annonceurs ne diffusent pas
                ]);

                $annonceur->roles()->sync([$annonceurRole->id]);
                $annonceur->categories()->sync($categories->random(rand(2, 4))->pluck('id'));

                $this->command->info("   ✓ Créé: {$data['firstname']} {$data['lastname']} - {$data['company']}");
            } else {
                $this->command->info("   ⟳ Existe déjà: {$data['firstname']} {$data['lastname']} - {$data['company']}");
            }

            $annonceurs[] = $annonceur;
        }

        // ========================================
        // 2. CRÉATION DES DIFFUSEURS
        // ========================================
        $this->command->info('📱 Création des 5 diffuseurs...');

        /* $testLocality = Locality::where('country_id', $country->id)->first();
        $testOccupation = Occupation::where('enabled', true)->first(); */

        $diffuseurs = [];
        $diffuseurData = [
            [
                'firstname' => 'Aïcha',
                'lastname' => 'Diallo',
                'email' => 'aicha.diallo@example.com',
                'vuesmoyen' => 500,
                'occupation' => 'Influenceur',
                'profile' => 'Influenceuse mode et lifestyle',
            ],
            [
                'firstname' => 'Kofi',
                'lastname' => 'Tognon',
                'email' => 'kofi.tognon@example.com',
                'vuesmoyen' => 350,
                'occupation' => 'Entrepreneur',
                'profile' => 'Entrepreneur tech, communauté startup',
            ],
            [
                'firstname' => 'Fatou',
                'lastname' => 'Sow',
                'email' => 'fatou.sow@example.com',
                'vuesmoyen' => 800,
                'occupation' => 'Influenceur',
                'profile' => 'Influenceuse beauté et santé',
            ],
            [
                'firstname' => 'Moussa',
                'lastname' => 'Traoré',
                'email' => 'moussa.traore@example.com',
                'vuesmoyen' => 200,
                'occupation' => 'Étudiant',
                'profile' => 'Étudiant en communication',
            ],
            [
                'firstname' => 'Awa',
                'lastname' => 'Bah',
                'email' => 'awa.bah@example.com',
                'vuesmoyen' => 650,
                'occupation' => 'Commerçant',
                'profile' => 'Commerçante avec large réseau',
            ],
        ];

        foreach ($diffuseurData as $data) {
            // Vérifier si l'utilisateur existe déjà
            $diffuseur = User::where('email', $data['email'])->first();

            if (!$diffuseur) {
                $occupation = $occupations->firstWhere('name', $data['occupation']) ?? $occupations->random();

                $diffuseur = User::create([
                    'id' => Str::uuid(),
                    'firstname' => $data['firstname'],
                    'lastname' => $data['lastname'],
                    'email' => $data['email'],
                    'password' => Hash::make('Password123!'),
                    'enabled' => true,
                    'email_verified_at' => now(),
                    'country_id' => $country->id,
                    'locality_id' => $localities->random()->id,
                    'occupation_id' => $occupation->id,
                    'phone' => '+229' . rand(60000000, 99999999),
                    'vuesmoyen' => $data['vuesmoyen'],
                ]);

                $diffuseur->roles()->sync([$diffuseurRole->id]);
                $diffuseur->categories()->sync($categories->random(rand(3, 6))->pluck('id'));

                $this->command->info("   ✓ Créé: {$data['firstname']} {$data['lastname']} - {$data['vuesmoyen']} vues/jour - {$data['profile']}");
            } else {
                $this->command->info("   ⟳ Existe déjà: {$data['firstname']} {$data['lastname']} - {$data['vuesmoyen']} vues/jour");
            }

            $diffuseurs[] = $diffuseur;
        }

        // ========================================
        // 3. CRÉATION DES CAMPAGNES (TASKS)
        // ========================================
        /* $this->command->info('📢 Création des campagnes pour chaque annonceur...');

        $tasks = [];
        $taskTypes = ['URL', 'TXT', 'IMG', 'VID'];
        $taskStatuses = ['PENDING'];

        foreach ($annonceurs as $annonceur) {
            $numberOfCampaigns = 3; // Fixe volontairement pour les tests

            for ($i = 1; $i <= $numberOfCampaigns; $i++) {

                // Dates toujours actives
                $startDate = Carbon::now()->subDays(2);
                $endDate   = Carbon::now()->addDays(5);

                // Budget toujours > 0
                $budget = rand(100, 200) * 1000;

                $task = Task::create([
                    'id' => Str::uuid(),
                    'name' => "Campagne Test #{$i} - {$annonceur->firstname}",
                    'descriptipon' => "Campagne de test automatique.",
                    'type' => 'URL',
                    'url' => 'https://example.com/test',
                    'legend' => 'Partagez ce message',
                    'startdate' => $startDate,
                    'enddate' => $endDate,
                    'budget' => $budget,
                    'status' => $taskStatuses, // IMPORTANT
                    'client_id' => $annonceur->id,
                    'locality_id' => $localities->random()->id,
                    'occupation_id' => $occupations->random()->id,
                ]);

                // Ajouter des catégories
                $task->categories()->attach($categories->random(2)->pluck('id'));

                $tasks[] = $task;

                $this->command->info("   ➤ Task ACTIVE créée : {$task->name} ");
            }
        } */

        // ========================================
        // 4. CRÉATION DES ASSIGNMENTS
        // ========================================
        /* $this->command->info('🎯 Création des assignments (attribution des campagnes aux diffuseurs)...');

        $assignmentStatuses = ['PENDING', 'ACCEPTED', 'REJECTED', 'SUBMITED', 'SUBMISSION_ACCEPTED', 'PAID'];
        $admin = User::whereHas('roles', function($q) use ($adminRole) {
            $q->where('role_id', $adminRole->id);
        })->first();

        $assignmentCount = 0;
        foreach ($tasks as $task) {
            // Chaque campagne est assignée à 1-3 diffuseurs
            $numberOfAssignments = rand(1, 3);
            $selectedDiffuseurs = collect($diffuseurs)->random(min($numberOfAssignments, count($diffuseurs)));

            foreach ($selectedDiffuseurs as $diffuseur) {
                // Vérifier l'éligibilité (simulation de la logique de TaskAssignmentService)
                $activeAssignments = Assignment::where('agent_id', $diffuseur->id)
                    ->whereIn('status', ['PENDING', 'ACCEPTED'])
                    ->count();

                // Limite de 3 assignments actifs par diffuseur
                if ($activeAssignments >= 3) {
                    continue;
                }

                // Calculer le gain estimé (1 FR par vue)
                $estimatedGain = $diffuseur->vuesmoyen * 1; // 1 FR par vue

                // Choisir un statut aléatoire pour les tests
                $status = $assignmentStatuses[array_rand($assignmentStatuses)];

                $assignment = Assignment::create([
                    'id' => Str::uuid(),
                    'task_id' => $task->id,
                    'agent_id' => $diffuseur->id,
                    'assigner_id' => $admin->id ?? $task->client_id,
                    'assignment_date' => $task->startdate->copy()->addHours(rand(0, 24)),
                    'status' => $status,
                    'gain' => in_array($status, ['SUBMISSION_ACCEPTED', 'PAID']) ? $estimatedGain : 0,
                    'vues' => in_array($status, ['SUBMITED', 'SUBMISSION_ACCEPTED', 'PAID']) ? rand(intval($diffuseur->vuesmoyen * 0.8), intval($diffuseur->vuesmoyen * 1.2)) : 0,
                    'response_date' => in_array($status, ['ACCEPTED', 'REJECTED', 'SUBMITED', 'SUBMISSION_ACCEPTED', 'PAID']) ? now()->subDays(rand(0, 5)) : null,
                    'submission_date' => in_array($status, ['SUBMITED', 'SUBMISSION_ACCEPTED', 'PAID']) ? now()->subDays(rand(0, 3)) : null,
                    'payment_date' => $status === 'PAID' ? now()->subDays(rand(0, 2)) : null,
                ]);

                $assignmentCount++;
                $this->command->info("   ✓ Assignment #{$assignmentCount}: {$task->name} → {$diffuseur->firstname} {$diffuseur->lastname} (Statut: {$status})");
            }
        } */

        // ========================================
        // RÉSUMÉ
        // ========================================
        $this->command->info('');
        $this->command->info('✅ Seeding terminé avec succès !');
        $this->command->info('');
        $this->command->info('📊 RÉSUMÉ DES DONNÉES CRÉÉES:');
        $this->command->info('   • Annonceurs: ' . count($annonceurs));
        $this->command->info('   • Diffuseurs: ' . count($diffuseurs));
        //$this->command->info('   • Campagnes (Tasks): ' . count($tasks));
        //$this->command->info('   • Assignments: ' . $assignmentCount);
        $this->command->info('');
        $this->command->info('🔑 COMPTES DE TEST:');
        $this->command->info('');
        $this->command->info('   ANNONCEURS (password: Password123!):');
        foreach ($annonceurs as $a) {
            $campaignCount = Task::where('client_id', $a->id)->count();
            $this->command->info("      • {$a->email} ({$campaignCount} campagnes)");
        }
        $this->command->info('');
        $this->command->info('   DIFFUSEURS (password: Password123!):');
        foreach ($diffuseurs as $d) {
            $assignmentCount = Assignment::where('agent_id', $d->id)->count();
            $this->command->info("      • {$d->email} ({$assignmentCount} assignments, {$d->vuesmoyen} vues/jour)");
        }
        $this->command->info('');
        $this->command->info('💡 EXPLICATIONS:');
        $this->command->info('   • ANNONCEURS créent des campagnes (tasks/missions)');
        $this->command->info('   • DIFFUSEURS reçoivent des assignments pour exécuter les campagnes');
        $this->command->info('   • Gain = 1 FR par vue (basé sur vuesmoyen du diffuseur)');
        $this->command->info('   • Max 3 assignments actifs par diffuseur');
        //$this->command->info('   • Statuts: PENDING → ACCEPTED → SUBMITED → SUBMISSION_ACCEPTED → PAID');
        $this->command->info('');
    }
}
