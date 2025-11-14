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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('specialty_id')->constrained()->onDelete('restrict');
            $table->string('crm')->unique();
            $table->string('crm_state', 2);
            $table->text('bio')->nullable();
            $table->decimal('consultation_price', 8, 2)->nullable();
            $table->integer('consultation_duration_minutes')->default(60);
            $table->json('available_days')->nullable(); // [1,2,3,4,5] para seg-sex
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('accepts_insurance')->default(false);
            $table->json('insurance_plans')->nullable(); // Planos aceitos
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};