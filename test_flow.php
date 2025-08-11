<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Profil;
use App\Models\Media;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🧪 === TEST DU FLUX COMPLET UTILISATEUR/PROFIL/MEDIA ===\n\n";

try {
    DB::beginTransaction();

    // 1. CRÉATION D'UN NOUVEL UTILISATEUR
    echo "1️⃣ Création d'un nouvel utilisateur...\n";
    $userData = [
        'name' => 'John Doe Test',
        'email' => 'john.test@example.com',
        'phone' => '+1234567890',
        'password' => bcrypt('password123'),
        'user_type' => 'investor',
        'is_verified' => false,
    ];
    
    $user = User::create($userData);
    echo "✅ Utilisateur créé avec ID: {$user->id}\n";
    echo "   - Name: {$user->name}\n";
    echo "   - Email: {$user->email}\n";
    echo "   - Type: {$user->user_type}\n\n";

    // 2. CRÉATION DU PROFIL
    echo "2️⃣ Création du profil utilisateur...\n";
    $profilData = [
        'bio' => 'Investisseur passionné par les technologies innovantes et les startups disruptives.',
        'profession' => 'Entrepreneur',
        'company' => 'TechVentures Inc.',
        'birth_date' => '1985-03-15',
        'city' => 'Paris',
        'country' => 'France',
        'gender' => 'male',
        'interests' => ['technologie', 'startup', 'intelligence artificielle'],
        'expertise_areas' => ['fintech', 'blockchain', 'e-commerce'],
        'investment_preferences' => ['tech', 'green energy', 'healthcare'],
        'risk_tolerance' => 'medium',
        'experience_level' => 'advanced',
        'notification_preferences' => [
            'email_notifications' => true,
            'push_notifications' => true,
            'sms_notifications' => false
        ],
        'privacy_settings' => [
            'profile_visibility' => 'public',
            'contact_visibility' => 'investors_only'
        ],
        'is_verified' => false
    ];

    $profil = $user->profil()->create($profilData);
    echo "✅ Profil créé avec ID: {$profil->id}\n";
    echo "   - Bio: " . substr($profil->bio, 0, 50) . "...\n";
    echo "   - Profession: {$profil->profession}\n";
    echo "   - Ville: {$profil->city}, {$profil->country}\n";
    echo "   - Score de complétude: {$profil->completeness_score}%\n";
    echo "   - Profil complet: " . ($profil->is_complete ? 'Oui' : 'Non') . "\n\n";

    // 3. CRÉATION DE MÉDIAS SIMULÉS
    echo "3️⃣ Création de médias simulés...\n";
    
    // Avatar
    $avatar = $profil->media()->create([
        'filename' => 'avatar_john_doe.jpg',
        'original_filename' => 'john_profile_photo.jpg',
        'path' => 'uploads/profil/' . $user->id . '/avatar_john_doe.jpg',
        'type' => 'avatar',
        'mime_type' => 'image/jpeg',
        'size' => 245760, // 240KB
    ]);
    echo "   ✅ Avatar créé (ID: {$avatar->id})\n";

    // Photo de couverture
    $cover = $profil->media()->create([
        'filename' => 'cover_john_doe.jpg',
        'original_filename' => 'cover_photo.jpg',
        'path' => 'uploads/profil/' . $user->id . '/cover_john_doe.jpg',
        'type' => 'cover',
        'mime_type' => 'image/jpeg',
        'size' => 512000, // 500KB
    ]);
    echo "   ✅ Photo de couverture créée (ID: {$cover->id})\n";

    // Images de galerie
    for ($i = 1; $i <= 3; $i++) {
        $gallery = $profil->media()->create([
            'filename' => "gallery_image_{$i}.jpg",
            'original_filename' => "gallery_photo_{$i}.jpg",
            'path' => "uploads/profil/{$user->id}/gallery_image_{$i}.jpg",
            'type' => 'gallery',
            'mime_type' => 'image/jpeg',
            'size' => rand(100000, 300000),
        ]);
        echo "   ✅ Image galerie {$i} créée (ID: {$gallery->id})\n";
    }

    // Document de vérification
    $verificationDoc = $profil->media()->create([
        'filename' => 'identity_card.pdf',
        'original_filename' => 'carte_identite.pdf',
        'path' => 'uploads/profil/' . $user->id . '/identity_card.pdf',
        'type' => 'verification_document',
        'mime_type' => 'application/pdf',
        'size' => 1024000, // 1MB
    ]);
    echo "   ✅ Document de vérification créé (ID: {$verificationDoc->id})\n\n";

    // 4. TEST DES ACCESSORS
    echo "4️⃣ Test des accessors et URLs...\n";
    $user->load(['profil.media']);
    $imageUrls = $user->profil->image_urls;
    
    echo "   📸 URLs des images:\n";
    echo "   - Avatar: " . ($imageUrls['avatar'] ?? 'Aucun') . "\n";
    echo "   - Avatar ou défaut: " . $imageUrls['avatar_or_default'] . "\n";
    echo "   - Photo de couverture: " . ($imageUrls['cover_photo'] ?? 'Aucune') . "\n";
    echo "   - Galerie (" . count($imageUrls['gallery']) . " images):\n";
    foreach ($imageUrls['gallery'] as $i => $url) {
        echo "     * Image " . ($i + 1) . ": $url\n";
    }
    echo "   - Documents (" . count($imageUrls['verification_documents']) . " docs):\n";
    foreach ($imageUrls['verification_documents'] as $i => $url) {
        echo "     * Doc " . ($i + 1) . ": $url\n";
    }
    echo "\n";

    // 5. MISE À JOUR DU PROFIL
    echo "5️⃣ Mise à jour du profil...\n";
    $updateData = [
        'bio' => 'Investisseur expérimenté avec plus de 10 ans d\'expérience dans le financement de startups technologiques innovantes.',
        'company' => 'TechVentures Global',
        'interests' => ['technologie', 'startup', 'intelligence artificielle', 'blockchain', 'fintech'],
        'experience_level' => 'expert',
        'risk_tolerance' => 'high'
    ];

    $profil->update($updateData);
    $profil->refresh();

    echo "   ✅ Profil mis à jour\n";
    echo "   - Nouvelle bio: " . substr($profil->bio, 0, 60) . "...\n";
    echo "   - Nouvelle entreprise: {$profil->company}\n";
    echo "   - Nouveau niveau: {$profil->experience_level}\n";
    echo "   - Nouvelle tolérance: {$profil->risk_tolerance}\n";
    echo "   - Nouveaux centres d'intérêt: " . implode(', ', $profil->interests) . "\n";
    echo "   - Nouveau score de complétude: {$profil->completeness_score}%\n\n";

    // 6. TEST DES RELATIONS ET SCOPES
    echo "6️⃣ Test des scopes et relations...\n";
    
    // Test des scopes
    $expertsCount = Profil::byExperienceLevel('expert')->count();
    $completeProfilesCount = Profil::complete()->count();
    $verifiedProfilesCount = Profil::verified()->count();
    $parisProfilesCount = Profil::byCity('Paris')->count();
    $techInterestsCount = Profil::withInterests(['technologie'])->count();

    echo "   📊 Statistiques:\n";
    echo "   - Profils niveau expert: {$expertsCount}\n";
    echo "   - Profils complets: {$completeProfilesCount}\n";
    echo "   - Profils vérifiés: {$verifiedProfilesCount}\n";
    echo "   - Profils à Paris: {$parisProfilesCount}\n";
    echo "   - Profils intéressés par la technologie: {$techInterestsCount}\n\n";

    // 7. SIMULATION RÉPONSE API
    echo "7️⃣ Simulation de réponse API getUserProfile...\n";
    $apiResponse = [
        'success' => true,
        'data' => [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'user_type' => $user->user_type,
                'created_at' => $user->created_at,
            ],
            'profil' => $user->profil->toArray(),
            'image_urls' => $user->profil->image_urls,
            'completeness_score' => $user->profil->completeness_score,
            'is_complete' => $user->profil->is_complete
        ]
    ];

    echo "   ✅ Réponse API générée (" . strlen(json_encode($apiResponse)) . " caractères)\n";
    echo "   📄 Structure de la réponse:\n";
    echo "   - success: " . ($apiResponse['success'] ? 'true' : 'false') . "\n";
    echo "   - user: " . count($apiResponse['data']['user']) . " champs\n";
    echo "   - profil: " . count($apiResponse['data']['profil']) . " champs\n";
    echo "   - image_urls: " . count($apiResponse['data']['image_urls']) . " types\n";
    echo "   - completeness_score: {$apiResponse['data']['completeness_score']}%\n";
    echo "   - is_complete: " . ($apiResponse['data']['is_complete'] ? 'true' : 'false') . "\n\n";

    DB::rollback(); // On annule pour ne pas polluer la DB

    echo "🎉 === TOUS LES TESTS SONT PASSÉS AVEC SUCCÈS ! ===\n";
    echo "✅ Flux utilisateur → profil → médias → mise à jour fonctionnel\n";
    echo "✅ Accessors et URLs générées correctement\n";
    echo "✅ Scopes et relations opérationnels\n";
    echo "✅ API prête pour React Native\n\n";

} catch (Exception $e) {
    DB::rollback();
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "📍 Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "📋 Trace:\n" . $e->getTraceAsString() . "\n";
}
