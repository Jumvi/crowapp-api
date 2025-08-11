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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('filename'); // Nom du fichier stocké
            $table->string('original_filename')->nullable(); // Nom original du fichier
            $table->string('path'); // Chemin dans storage
            $table->enum('type', [
                'avatar', 
                'cover', 
                'gallery', 
                'verification_document', 
                'project_image',
                'project_document'
            ]); // Type de média
            $table->string('mime_type')->nullable(); // Type MIME
            $table->bigInteger('size')->nullable(); // Taille en octets
            $table->morphs('mediable'); // mediable_type et mediable_id pour relation polymorphique
            $table->timestamps();

            // Index pour optimiser les requêtes (morphs crée déjà l'index pour mediable)
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
