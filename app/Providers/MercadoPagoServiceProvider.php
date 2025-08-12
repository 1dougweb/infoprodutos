<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use LivePixel\MercadoPago\MercadoPago;

class MercadoPagoServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('mercadopago', function ($app) {
            $accessToken = \App\Models\Setting::get('mercadopago_access_token');
            
            if ($accessToken) {
                MercadoPago::setAppId($accessToken);
                MercadoPago::setAppSecret($accessToken);
            }
            
            return new MercadoPago();
        });
    }

    public function boot()
    {
        //
    }
} 