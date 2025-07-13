<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Enums\UserType;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="Modèle de données utilisateur",
 *     required={"id", "name", "email", "created_at", "updated_at"},
 *     @OA\Property(property="id", type="integer", description="Identifiant unique de l'utilisateur", example=1, readOnly=true),
 *     @OA\Property(property="name", type="string", description="Nom complet de l'utilisateur", example="John Doe", maxLength=255),
 *     @OA\Property(property="email", type="string", format="email", description="Adresse email unique de l'utilisateur", example="john.doe@example.com", maxLength=255),
 *     @OA\Property(property="phone", type="string", description="Numéro de téléphone de l'utilisateur", example="+33123456789", nullable=true),
 *     @OA\Property(property="location", type="string", description="Localisation de l'utilisateur", example="Paris, France", nullable=true),
 *     @OA\Property(property="userType", type="string", description="Type d'utilisateur", example="investor", nullable=true),
 *     @OA\Property(property="investmentTotal", type="number", format="float", description="Montant total des investissements", example=10000.50, nullable=true),
 *     @OA\Property(property="projectSupported", type="number", format="float", description="Nombre de projets supportés", example=5.0, nullable=true),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", description="Date et heure de vérification de l'email", example="2024-01-15T10:30:00.000000Z", nullable=true, readOnly=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Date et heure de création du compte", example="2024-01-15T10:30:00.000000Z", readOnly=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Date et heure de dernière mise à jour", example="2024-01-15T10:30:00.000000Z", readOnly=true)
 * )
 */
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'location',
        'role',
        'secureOtp'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserType::class,
            'secureOtp' => 'number',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
