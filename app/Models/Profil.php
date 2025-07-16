<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @OA\Schema(
 *     schema="Profil",
 *     type="object",
 *     title="Profil",
 *     description="Modèle de profil utilisateur pour l'application de crowdfunding",
 *     required={"id", "user_id", "created_at", "updated_at"},
 *     @OA\Property(property="id", type="integer", description="Identifiant unique du profil", example=1, readOnly=true),
 *     @OA\Property(property="user_id", type="integer", description="Identifiant de l'utilisateur", example=1),
 *     @OA\Property(property="bio", type="string", description="Biographie de l'utilisateur", example="Passionné d'innovation et d'entrepreneuriat", nullable=true),
 *     @OA\Property(property="profession", type="string", description="Profession de l'utilisateur", example="Développeur Full-Stack", nullable=true),
 *     @OA\Property(property="company", type="string", description="Entreprise actuelle", example="Tech Startup Inc.", nullable=true),
 *     @OA\Property(property="birth_date", type="string", format="date", description="Date de naissance", example="1990-05-15", nullable=true),
 *     @OA\Property(property="gender", type="string", description="Genre", enum={"male", "female", "other", "prefer_not_to_say"}, example="male", nullable=true),
 *     @OA\Property(property="city", type="string", description="Ville", example="Paris", nullable=true),
 *     @OA\Property(property="country", type="string", description="Pays", example="France", nullable=true),
 *     @OA\Property(property="interests", type="array", description="Centres d'intérêt (JSON)", @OA\Items(type="string"), example={"technologie", "startup", "innovation"}, nullable=true),
 *     @OA\Property(property="expertise_areas", type="array", description="Domaines d'expertise (JSON)", @OA\Items(type="string"), example={"développement web", "marketing digital"}, nullable=true),
 *     @OA\Property(property="investment_preferences", type="array", description="Préférences d'investissement (JSON)", @OA\Items(type="string"), example={"tech", "green energy", "healthcare"}, nullable=true),
 *     @OA\Property(property="risk_tolerance", type="string", description="Tolérance au risque", enum={"low", "medium", "high"}, example="medium", nullable=true),
 *     @OA\Property(property="experience_level", type="string", description="Niveau d'expérience", enum={"beginner", "intermediate", "advanced", "expert"}, example="intermediate", nullable=true),
 *     @OA\Property(property="notification_preferences", type="object", description="Préférences de notification (JSON)", nullable=true),
 *     @OA\Property(property="privacy_settings", type="object", description="Paramètres de confidentialité (JSON)", nullable=true),
 *     @OA\Property(property="is_verified", type="boolean", description="Profil vérifié", example=false),
 *     @OA\Property(property="verification_documents", type="array", description="Documents de vérification (JSON)", @OA\Items(type="string"), nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Date de création", example="2024-01-15T10:30:00.000000Z", readOnly=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Date de mise à jour", example="2024-01-15T10:30:00.000000Z", readOnly=true)
 * )
 */
class Profil extends Model
{
    protected $table = 'profils';

    protected $fillable = [
        'user_id',
        'bio',
        'profession',
        'company',
        'birth_date',
        'gender',
        'interests',
        'expertise_areas',
        'city',
        'country',
        'investment_preferences',
        'risk_tolerance',
        'experience_level',
        'notification_preferences',
        'privacy_settings',
        'is_verified',
        'verification_documents'
    ];

    protected $hidden = [
        'verification_documents'
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'interests' => 'array',
            'expertise_areas' => 'array',
            'investment_preferences' => 'array',
            'notification_preferences' => 'object',
            'privacy_settings' => 'object',
            'verification_documents' => 'array',
            'is_verified' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // Relations

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation polymorphique avec les médias
     * Les images de profil, documents, etc. seront stockés dans la table media
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Récupère l'avatar principal
     */
    public function avatar()
    {
        return $this->media()->where('type', 'avatar')->first();
    }

    /**
     * Récupère la photo de couverture
     */
    public function coverPhoto()
    {
        return $this->media()->where('type', 'cover')->first();
    }

    /**
     * Récupère les documents de vérification
     */
    public function verificationDocuments()
    {
        return $this->media()->where('type', 'verification_document');
    }

    /**
     * Récupère les images de la galerie
     */
    public function galleryImages()
    {
        return $this->media()->where('type', 'gallery');
    }

    // Méthodes pour récupérer les URLs des images

    /**
     * Récupère l'URL de l'avatar
     */
    public function getAvatarUrlAttribute(): ?string
    {
        $avatar = $this->avatar();
        return $avatar ? asset('storage/' . $avatar->path) : null;
    }

    /**
     * Récupère l'URL de la photo de couverture
     */
    public function getCoverPhotoUrlAttribute(): ?string
    {
        $coverPhoto = $this->coverPhoto();
        return $coverPhoto ? asset('storage/' . $coverPhoto->path) : null;
    }

    /**
     * Récupère les URLs des images de la galerie
     */
    public function getGalleryImageUrlsAttribute(): array
    {
        return $this->galleryImages()->get()->map(function ($image) {
            return asset('storage/' . $image->path);
        })->toArray();
    }

    /**
     * Récupère les URLs des documents de vérification
     */
    public function getVerificationDocumentUrlsAttribute(): array
    {
        return $this->verificationDocuments()->get()->map(function ($document) {
            return asset('storage/' . $document->path);
        })->toArray();
    }

    /**
     * Récupère l'URL de l'avatar avec une taille par défaut si pas d'avatar
     */
    public function getAvatarUrlOrDefaultAttribute(): string
    {
        $avatar = $this->avatar();
        if ($avatar) {
            return asset('storage/' . $avatar->path);
        }
        
        // Avatar par défaut basé sur les initiales ou genre
        $initials = strtoupper(substr($this->user->name ?? 'U', 0, 1));
        return "https://ui-avatars.com/api/?name={$initials}&background=3B82F6&color=fff&size=200";
    }

    /**
     * Récupère toutes les URLs d'images du profil pour l'API
     */
    public function getImageUrlsAttribute(): array
    {
        return [
            'avatar' => $this->avatar_url,
            'avatar_or_default' => $this->avatar_url_or_default,
            'cover_photo' => $this->cover_photo_url,
            'gallery' => $this->gallery_image_urls,
            'verification_documents' => $this->verification_document_urls
        ];
    }

    // Accesseurs et Mutateurs

    /**
     * Vérifie si le profil est complet
     */
    public function getIsCompleteAttribute(): bool
    {
        $requiredFields = ['bio', 'profession', 'city', 'country'];
        
        foreach ($requiredFields as $field) {
            if (empty($this->$field)) {
                return false;
            }
        }
        
        return $this->avatar() !== null;
    }

    /**
     * Calcule le score de complétude du profil
     */
    public function getCompletenessScoreAttribute(): int
    {
        $fields = [
            'bio' => 20,
            'profession' => 15,
            'company' => 10,
            'birth_date' => 5,
            'gender' => 5,
            'city' => 15,
            'country' => 10,
            'interests' => 10,
            'expertise_areas' => 10
        ];
        
        $score = 0;
        foreach ($fields as $field => $points) {
            if (!empty($this->$field)) {
                $score += $points;
            }
        }
        
        // Avatar
        if ($this->avatar()) {
            $score += 10;
        }
        
        return min($score, 100);
    }

    // Scopes

    /**
     * Profils vérifiés
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Profils par niveau d'expérience
     */
    public function scopeByExperienceLevel($query, $level)
    {
        return $query->where('experience_level', $level);
    }

    /**
     * Profils par tolérance au risque
     */
    public function scopeByRiskTolerance($query, $tolerance)
    {
        return $query->where('risk_tolerance', $tolerance);
    }

    /**
     * Profils par ville
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', 'like', "%{$city}%");
    }

    /**
     * Profils par pays
     */
    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Profils par profession
     */
    public function scopeByProfession($query, $profession)
    {
        return $query->where('profession', 'like', "%{$profession}%");
    }

    /**
     * Profils ayant des centres d'intérêt spécifiques
     */
    public function scopeWithInterests($query, $interests)
    {
        if (is_string($interests)) {
            $interests = [$interests];
        }
        
        foreach ($interests as $interest) {
            $query->whereJsonContains('interests', $interest);
        }
        
        return $query;
    }

    /**
     * Profils ayant des domaines d'expertise spécifiques
     */
    public function scopeWithExpertise($query, $expertiseAreas)
    {
        if (is_string($expertiseAreas)) {
            $expertiseAreas = [$expertiseAreas];
        }
        
        foreach ($expertiseAreas as $expertise) {
            $query->whereJsonContains('expertise_areas', $expertise);
        }
        
        return $query;
    }

    /**
     * Profils ayant des préférences d'investissement spécifiques
     */
    public function scopeWithInvestmentPreferences($query, $preferences)
    {
        if (is_string($preferences)) {
            $preferences = [$preferences];
        }
        
        foreach ($preferences as $preference) {
            $query->whereJsonContains('investment_preferences', $preference);
        }
        
        return $query;
    }

    /**
     * Profils complets (ayant tous les champs requis)
     */
    public function scopeComplete($query)
    {
        return $query->whereNotNull(['bio', 'profession', 'city', 'country'])
                    ->where('bio', '!=', '')
                    ->where('profession', '!=', '')
                    ->where('city', '!=', '')
                    ->where('country', '!=', '');
    }

    /**
     * Profils par genre
     */
    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }
    
}
