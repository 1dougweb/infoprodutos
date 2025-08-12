<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tornar o usuário Douglas um administrador
        $user = User::where('email', 'douglas@test.com')->first();
        
        if ($user) {
            $user->assignRole('admin');
            $this->command->info('Usuário Douglas agora é administrador!');
        } else {
            $this->command->error('Usuário Douglas não encontrado. Execute primeiro o TestDataSeeder.');
        }

        // Atribuir role member para todos os outros usuários
        $otherUsers = User::where('email', '!=', 'douglas@test.com')->get();
        foreach ($otherUsers as $user) {
            if (!$user->hasRole('admin')) {
                $user->assignRole('member');
            }
        }
    }
}
