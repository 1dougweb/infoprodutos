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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payer_first_name')->nullable()->after('payment_method');
            $table->string('payer_last_name')->nullable()->after('payer_first_name');
            $table->string('payer_email')->nullable()->after('payer_last_name');
            $table->string('payer_identification_type')->nullable()->after('payer_email');
            $table->string('payer_identification_number')->nullable()->after('payer_identification_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payer_first_name',
                'payer_last_name',
                'payer_email',
                'payer_identification_type',
                'payer_identification_number'
            ]);
        });
    }
};
