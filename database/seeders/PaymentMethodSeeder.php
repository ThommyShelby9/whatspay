<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Carte bancaire',
                'code' => 'card',
                'description' => 'Paiement par carte bancaire (Visa, Mastercard)',
                'icon' => 'fa-credit-card',
                'status' => 'ACTIVE',
                'config' => json_encode([])
            ],
            [
                'name' => 'Mobile Money',
                'code' => 'mobile_money',
                'description' => 'Paiement via Mobile Money',
                'icon' => 'fa-mobile-alt',
                'status' => 'ACTIVE',
                'config' => json_encode([])
            ],
            [
                'name' => 'Virement bancaire',
                'code' => 'bank',
                'description' => 'Paiement par virement bancaire',
                'icon' => 'fa-university',
                'status' => 'ACTIVE',
                'config' => json_encode([])
            ]
        ];

        foreach ($methods as $method) {
            PaymentMethod::create([
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'name' => $method['name'],
                'code' => $method['code'],
                'description' => $method['description'],
                'icon' => $method['icon'],
                'status' => $method['status'],
                'config' => $method['config']
            ]);
        }
    }
}