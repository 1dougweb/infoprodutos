<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Configurações de Aparência
        Setting::set('primary_color', '#007bff', 'color', 'appearance');
        Setting::set('secondary_color', '#0056b3', 'color', 'appearance');
        Setting::set('background_color', '#0f0f0f', 'color', 'appearance');
        Setting::set('card_background', '#2a2a2a', 'color', 'appearance');
        Setting::set('text_light', '#ffffff', 'color', 'appearance');
        Setting::set('text_muted', '#b3b3b3', 'color', 'appearance');

        // Configurações de Marca
        Setting::set('site_name', 'Painel de Controle', 'string', 'branding');
        Setting::set('site_description', 'Plataforma de ferramentas para designers', 'string', 'branding');

        // Configurações do Mercado Pago
        Setting::set('mercadopago_access_token', '', 'string', 'mercadopago');
        Setting::set('mercadopago_public_key', '', 'string', 'mercadopago');
        Setting::set('mercadopago_environment', 'sandbox', 'string', 'mercadopago');

        // Configurações Gerais
        Setting::set('homepage_type', 'login', 'string', 'general');
        Setting::set('homepage_url', '', 'string', 'general');
        Setting::set('homepage_enabled', '1', 'boolean', 'general');

        $this->command->info('Configurações padrão criadas com sucesso!');
    }
}
