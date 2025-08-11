<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Get(
 *     path="/user",
 *     tags={"User"},
 *     summary="Obtenir les informations de l'utilisateur (route alternative)",
 *     description="Route alternative pour récupérer les données de l'utilisateur connecté",
 *     operationId="getUser",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Informations utilisateur récupérées avec succès",
 *         @OA\JsonContent(ref="#/components/schemas/User")
 *     ),
 *     @OA\Response(response=401, description="Token invalide ou expiré")
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/user/profile",
     *     tags={"User"},
     *     summary="Obtenir le profil complet de l'utilisateur connecté",
     *     description="Récupérer le profil de l'utilisateur avec toutes les URLs des médias",
     *     operationId="getUserProfile",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profil utilisateur récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="profil", ref="#/components/schemas/Profil"),
     *             @OA\Property(property="image_urls", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Token invalide ou expiré"),
     *     @OA\Response(response=404, description="Utilisateur ou profil non trouvé")
     * )
     */
    public function getUserProfile()
    {
        // ✅ Vérification d'authentification
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        // ✅ Eager loading pour éviter les requêtes N+1
        $user->load(['profil.media']);

        // ✅ Vérification de l'existence du profil
        if (!$user->profil) {
            return response()->json([
                'success' => false,
                'message' => 'Profil non trouvé'
            ], 404);
        }

        // ✅ Appel correct de l'accessor
        $imageUrls = $user->profil->image_urls;

        return response()->json([
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
                'profil' => $user->profil,
                'avatar' => $imageUrls['avatar'] ?? null,
                'cover' => $imageUrls['cover'] ?? null,
                'gallery' => $imageUrls['gallery'] ?? [],
                'verification_document' => $imageUrls['verification_document'] ?? null,
                'project_images' => $imageUrls['project_image'] ?? [],
                'project_documents' => $imageUrls['project_document'] ?? [],
                'completeness_score' => $user->profil->completeness_score,
                'is_complete' => $user->profil->is_complete
            ]
        ], 200);
    }

    //swagger for updateUserProfile
    /**
     * @OA\Put(
     *     path="/user/profile",
     *     tags={"User"},
     *     summary="Mettre à jour le profil de l'utilisateur connecté",     
     *    description="Met à jour les informations du profil de l'utilisateur connecté",
     *    operationId="updateUserProfile",
     *    security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *        required=true,
     *       @OA\JsonContent(
     *           type="object",
     *          @OA\Property(property="bio", type="string", description="Biographie de l'utilisateur", maxLength=500, nullable=true),
     *          @OA\Property(property="profession", type="string", description="Profession de l'utilisateur", maxLength=100, nullable=true),
     *         @OA\Property(property="company", type="string", description="Entreprise de l'utilisateur", maxLength=100, nullable=true),
     *         @OA\Property(property="birth_date", type="string", format="date", description="Date de naissance de l'utilisateur", nullable=true),
     *        @OA\Property(property="location", type="string", description="Localisation de l'utilisateur", maxLength=100, nullable=true),
     *       @OA\Property(property="city", type="string", description="Ville de l'utilisateur", maxLength=100, nullable=true),
     *      @OA\Property(property="country", type="string", description="Pays de l'utilisateur", maxLength=100, nullable=true),
     *      @OA\Property(property="interests", type="array", @OA\Items(type="string"), description="Centres d'intérêt de l'utilisateur", nullable=true),
     *    @OA\Property(property="gender", type="string", description="Genre de l'utilisateur", enum={"male","female","other"}, nullable=true),
     *    @OA\Property(property="expertise_areas", type="array", @OA\Items(type="string"), description="Domaines d'expertise de l'utilisateur", nullable=true),
     *    @OA\Property(property="investment_preferences", type="array", @OA\Items(type="string"), description="Préférences d'investissement de l'utilisateur", nullable=true),
     *    @OA\Property(property="risk_tolerance", type="string", description="Tolérance au risque de l'utilisateur", enum={"low","medium","high"}, nullable=true),
     *    @OA\Property(property="experience_level", type="string", description="Niveau d'expérience de l'utilisateur", enum={"beginner","intermediate","advanced","expert"}, nullable=true),
     *    @OA\Property(property="notification_preferences", type="array", @OA\Items(type="string"), description="Préférences de notification de l'utilisateur", nullable=true),
     *    @OA\Property(property="privacy_settings", type="array", @OA\Items(type="string"), description="Paramètres de confidentialité de l'utilisateur", nullable=true),
     * */

    public function updateUserProfile(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        
        $profil = $user->profil;

        if (!$profil) {
            return response()->json([
                'success' => false,
                'message' => 'Profil non trouvé'
            ], 404);
        }

        // ✅ Vérification des permissions
        $this->authorize('update', $profil);
        
        // ✅ Validation corrigée
        $validator = Validator::make($request->all(), [
            'bio' => 'nullable|string|max:500',
            'profession' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date|before:today',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'interests' => 'nullable|array',
            'interests.*' => 'string|max:50',
            'gender' => 'nullable|string|in:male,female,other,prefer_not_to_say',
            'expertise_areas' => 'nullable|array',
            'expertise_areas.*' => 'string|max:50',
            'investment_preferences' => 'nullable|array',
            'investment_preferences.*' => 'string|max:50',
            'risk_tolerance' => 'nullable|string|in:low,medium,high',
            'experience_level' => 'nullable|string|in:beginner,intermediate,advanced,expert',
            'notification_preferences' => 'nullable|array',
            'privacy_settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // ✅ Mise à jour avec eager loading pour le retour
        $profil->update($request->only([
            'bio',
            'profession',
            'company',
            'birth_date',
            'city',
            'country',
            'interests',
            'gender',
            'expertise_areas',
            'investment_preferences',
            'risk_tolerance',
            'experience_level',
            'notification_preferences',
            'privacy_settings',
        ]));

        // ✅ Recharger avec les relations pour la réponse
        $profil->load('media');

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès',
            'data' => [
                'profil' => $profil,
                'image_urls' => $profil->image_urls,
                'completeness_score' => $profil->completeness_score,
                'is_complete' => $profil->is_complete
            ]
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/user/medias",
     *     tags={"User"},
     *     summary="Obtenir les médias de l'utilisateur connecté",
     *     description="Récupérer tous les médias associés à l'utilisateur connecté",
     *     operationId="getUserMedias",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Médias récupérés avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Media")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Token invalide ou expiré")
     * )
     */
    public function getUserMedias()
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        // ✅ Vérification du profil
        if (!$user->profil) {
            return response()->json([
                'success' => false,
                'message' => 'Profil non trouvé'
            ], 404);
        }

        // ✅ Récupération des médias avec URLs
        $medias = $user->profil->media;
        
        // ✅ Transformation avec URLs complètes
        $mediasWithUrls = $medias->map(function ($media) {
            return [
                'id' => $media->id,
                'type' => $media->type,
                'filename' => $media->filename,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'path' => $media->path,
                'url' => $media->url, // Utilise l'accessor du modèle Media
                'created_at' => $media->created_at,
                'updated_at' => $media->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $mediasWithUrls
        ], 200);
    }
}
