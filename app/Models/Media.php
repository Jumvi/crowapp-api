<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @OA\Schema(
 *     schema="Media",
 *     type="object",
 *     title="Media",
 *     description="Modèle de média pour gérer les fichiers (images, documents, etc.)",
 *     required={"id", "filename", "path", "type", "mediable_type", "mediable_id"},
 *     @OA\Property(property="id", type="integer", description="Identifiant unique du média", example=1, readOnly=true),
 *     @OA\Property(property="filename", type="string", description="Nom du fichier", example="avatar.jpg"),
 *     @OA\Property(property="original_filename", type="string", description="Nom original du fichier", example="ma_photo.jpg", nullable=true),
 *     @OA\Property(property="path", type="string", description="Chemin du fichier dans le storage", example="media/avatars/1234567890_avatar.jpg"),
 *     @OA\Property(property="type", type="string", description="Type de média", enum={"avatar", "cover", "gallery", "verification_document", "project_image"}, example="avatar"),
 *     @OA\Property(property="mime_type", type="string", description="Type MIME du fichier", example="image/jpeg", nullable=true),
 *     @OA\Property(property="size", type="integer", description="Taille du fichier en octets", example=1024000, nullable=true),
 *     @OA\Property(property="url", type="string", description="URL complète du fichier", example="https://monapi.com/storage/media/avatars/1234567890_avatar.jpg", readOnly=true),
 *     @OA\Property(property="mediable_type", type="string", description="Type du modèle parent", example="App\\Models\\Profil"),
 *     @OA\Property(property="mediable_id", type="integer", description="ID du modèle parent", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Date de création", example="2024-01-15T10:30:00.000000Z", readOnly=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Date de mise à jour", example="2024-01-15T10:30:00.000000Z", readOnly=true)
 * )
 */
class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'filename',
        'original_filename',
        'path',
        'type',
        'mime_type',
        'size',
        'mediable_type',
        'mediable_id'
    ];

    protected $appends = ['url'];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Relations

    /**
     * Relation polymorphique avec le modèle parent
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    // Accesseurs

    /**
     * Récupère l'URL complète du fichier
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    /**
     * Récupère l'URL avec le domaine complet (pour l'API)
     */
    public function getFullUrlAttribute(): string
    {
        return url('storage/' . $this->path);
    }

    // Scopes

    /**
     * Médias par type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Images uniquement
     */
    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    /**
     * Documents uniquement
     */
    public function scopeDocuments($query)
    {
        return $query->where('mime_type', 'not like', 'image/%');
    }

    // Méthodes utilitaires

    /**
     * Vérifie si le fichier est une image
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    /**
     * Récupère la taille formatée
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->size) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->size;
        $unitIndex = 0;

        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }

        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}
