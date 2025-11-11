<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'name' => 'Mobile Money',
                'code' => 'mobile_money',
                'description' => 'Paiement via Orange Money, MTN Money, Moov Money',
                'icon' => 'fas fa-mobile-alt',
                'status' => 'ACTIVE',
                'config' => json_encode([
                    'supported_networks' => ['Orange', 'MTN', 'Moov'],
                    'min_amount' => 500,
                    'max_amount' => 500000,
                    'processing_time' => 'Instantané',
                ])
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'name' => 'Carte Bancaire',
                'code' => 'card',
                'description' => 'Paiement par carte Visa/MasterCard',
                'icon' => 'fas fa-credit-card',
                'status' => 'ACTIVE',
                'config' => json_encode([
                    'supported_cards' => ['Visa', 'MasterCard'],
                    'min_amount' => 1000,
                    'max_amount' => 1000000,
                    'processing_time' => '1-3 minutes',
                    'fees' => '2% + 100 FCFA',
                ])
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'name' => 'Virement Bancaire',
                'code' => 'bank',
                'description' => 'Virement bancaire direct',
                'icon' => 'fas fa-university',
                'status' => 'ACTIVE',
                'config' => json_encode([
                    'banks' => ['Ecobank', 'SGBCI', 'BICICI', 'UBA', 'Banque Atlantique'],
                    'min_amount' => 5000,
                    'max_amount' => 5000000,
                    'processing_time' => '24-72 heures',
                    'requires_proof' => true,
                ])
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'name' => 'PayPal',
                'code' => 'paypal',
                'description' => 'Paiement via PayPal (international)',
                'icon' => 'fab fa-paypal',
                'status' => 'INACTIVE',
                'config' => json_encode([
                    'min_amount' => 2000,
                    'max_amount' => 2000000,
                    'currency_conversion' => true,
                    'processing_time' => '5-10 minutes',
                    'fees' => '3.5% + frais de change',
                ])
            ]
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::create($method);
        }
    }
}