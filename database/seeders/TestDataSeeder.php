<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserPurchase;
use App\Models\DigitalProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar usuário de teste
        $user = User::create([
            'name' => 'Douglas',
            'email' => 'douglas@test.com',
            'password' => Hash::make('password'),
        ]);

        // Criar algumas compras para o usuário
        $products = DigitalProduct::take(3)->get(); // Primeiros 3 produtos
        
        foreach ($products as $product) {
            UserPurchase::create([
                'user_id' => $user->id,
                'digital_product_id' => $product->id,
                'purchased_at' => now(),
                'status' => 'completed',
                'amount' => 99.90,
            ]);
        }
    }
}
