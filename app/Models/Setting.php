<?php

namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    class Setting extends Model
    {
        use HasFactory;

        protected $fillable = [
            'key',
            'value',
            'type',
            'group'
        ];

        protected $casts = [
            'value' => 'string'
        ];

        public static function get($key, $default = null)
        {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        }

        public static function set($key, $value, $type = 'string', $group = 'general')
        {
            return static::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => $type,
                    'group' => $group
                ]
            );
        }

        public static function getAppearanceSettings()
        {
            return [
                'primary_color' => static::get('primary_color', '#007bff'),
                'secondary_color' => static::get('secondary_color', '#0056b3'),
                'background_color' => static::get('background_color', '#0f0f0f'),
                'card_background' => static::get('card_background', '#2a2a2a'),
                'text_light' => static::get('text_light', '#ffffff'),
                'text_muted' => static::get('text_muted', '#b3b3b3'),
            ];
        }

        public static function getBrandingSettings()
        {
            return [
                'site_name' => static::get('site_name', 'Painel de Controle'),
                'site_description' => static::get('site_description', 'Plataforma de ferramentas para designers'),
                'logo_path' => static::get('logo_path'),
                'favicon_path' => static::get('favicon_path'),
            ];
        }

        public static function getMercadoPagoSettings()
        {
            return [
                'mercadopago_access_token' => static::get('mercadopago_access_token'),
                'mercadopago_public_key' => static::get('mercadopago_public_key'),
                'mercadopago_environment' => static::get('mercadopago_environment', 'sandbox'),
                'mercadopago_webhook_enabled' => static::get('mercadopago_webhook_enabled', '1'),
                'mercadopago_webhook_secret' => static::get('mercadopago_webhook_secret'),
            ];
        }
    }