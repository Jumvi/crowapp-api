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
        Schema::create('profils', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('bio')->nullable();
            $table->string('profession')->nullable();
            $table->string('company')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->json('interests')->nullable(); // Centres d'intérêt
            $table->json('expertise_areas')->nullable(); // Domaines d'expertise
            $table->json('investment_preferences')->nullable(); // Préférences d'investissement
            $table->enum('risk_tolerance', ['low', 'medium', 'high'])->nullable();
            $table->enum('experience_level', ['beginner', 'intermediate', 'advanced', 'expert'])->nullable();
            $table->json('notification_preferences')->nullable(); // Préférences de notification
            $table->json('privacy_settings')->nullable(); // Paramètres de confidentialité
            $table->boolean('is_verified')->default(false);
            $table->json('verification_documents')->nullable(); // IDs des documents de vérification
            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index('user_id');
            $table->index('city');
            $table->index('country');
            $table->index('profession');
            $table->index('is_verified');
            $table->index(['risk_tolerance', 'experience_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profils');
    }
};
