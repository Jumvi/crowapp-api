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
    public function getuserProfile()
    {
        $user = auth()->user();

        if(!$user){
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $avatar = $user->Profile ?
            $user->Profile->media()->where('type', 'avatar')->first() :
            null;

    }

}
