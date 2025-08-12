<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('digital_products', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->default(0.00)->after('is_active');
            $table->string('mercadopago_id')->nullable()->after('price');
            $table->boolean('is_free')->default(false)->after('mercadopago_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('digital_products', function (Blueprint $table) {
            $table->dropColumn(['price', 'mercadopago_id', 'is_free']);
        });
    }
};
