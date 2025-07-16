<?php

namespace App\Http\Controllers;

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

}
