<?php

namespace Database\Seeders;

use App\Models\DigitalProduct;
use Illuminate\Database\Seeder;

class DigitalProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'title' => 'Módulo 1',
                'description' => 'Fundamentos do design digital',
                'category' => 'module',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Bônus 1',
                'description' => 'Arquivos PSD exclusivos',
                'category' => 'bonus',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Bônus 2',
                'description' => 'Coleção de fontes premium',
                'category' => 'bonus',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Bônus 3',
                'description' => 'Templates profissionais',
                'category' => 'bonus',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'title' => 'Contato',
                'description' => 'Suporte e contato',
                'category' => 'contact',
                'order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            DigitalProduct::create($product);
        }
    }
}
