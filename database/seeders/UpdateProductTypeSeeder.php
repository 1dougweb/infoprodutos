<?php

namespace Database\Seeders;

use App\Models\DigitalProduct;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Atualizar todos os produtos existentes para ter o tipo 'course' por padrão
        DigitalProduct::whereNull('product_type')->update(['product_type' => 'course']);
        
        $this->command->info('Produtos existentes atualizados com tipo "course" por padrão.');
    }
}
