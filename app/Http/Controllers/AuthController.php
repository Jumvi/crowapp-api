<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Enums\UserType;
use Illuminate\Validation\Rules\Enum;

use App\Services\SmsService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use AuthorizesRequests;

    /**
     * Create a new AuthController instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get a JWT via given credentials.
     *
     * @OA\Post(
     *     path="/auth/login",
     *     tags={"Authentication"},
     *     summary="Connexion utilisateur",
     *     description="Authentifier un utilisateur et obtenir un token JWT",
     *     operationId="login",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Identifiants invalides"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token);
    }

    /**
     * Register a User.
     *
     * @OA\Post(
     *     path="/auth/register",
     *     tags={"Authentication"},
     *     summary="Inscription d'un nouvel utilisateur",
     *     description="Créer un nouveau compte utilisateur avec email et mot de passe",
     *     operationId="register",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(property="phone", type="string", example="+33123456789", description="Numéro de téléphone (optionnel)"),
     *             @OA\Property(property="location", type="string", example="Paris, France", description="Localisation (optionnel)"),
     *             @OA\Property(property="userType", type="string", example="investor", description="Type d'utilisateur (optionnel)"),
     *             @OA\Property(property="investmentTotal", type="number", format="float", example=10000.50, description="Montant total des investissements (optionnel)"),
     *             @OA\Property(property="projectSupported", type="number", format="float", example=5.0, description="Nombre de projets supportés (optionnel)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User successfully registered"),
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function register(Request $request): JsonResponse
    {
      $validator = Validator::make($request->all(),[
        'name'=> 'required|string|max:255',
        'email'=> 'required|string|email|max:40|unique:users',
        'password'=> 'required|string|min:6|confirmed',
        'phone'=> 'nullable|string|max:20',
        'location'=> 'nullable|string|max:255',
        'role'=> ['nullable', 'string', 'max:50', new Enum(UserType::class)]
      ]);
      if ($validator->fails()) {
          return response()->json($validator->errors(), 422);
      }

      $otp = rand(100000, 999999); // Generate a random OTP
      $request->merge(['secureOtp' => $otp]); // Add OTP to request data

      // Utiliser une transaction pour s'assurer que l'user et le profil sont créés ensemble
      DB::beginTransaction();

      try {
          $user = User::create([
              'name' => $request->name,
              'email' => $request->email,
              'password' => Hash::make($request->password),
              'phone' => $request->phone,
              'location' => $request->location,
              'role' => $request->role,
              'secureOtp' => $request->secureOtp,
          ]);

          // ✅ Créer automatiquement le profil vide
          $user->profil()->create([
              'notification_preferences' => [
                  'email_notifications' => true,
                  'push_notifications' => true,
                  'sms_notifications' => false
              ],
              'privacy_settings' => [
                  'profile_visibility' => 'public',
                  'contact_visibility' => 'public'
              ]
          ]);

          // Envoi de l'OTP par SMS
          $smsEnvoye = SmsService::envoyerSms($user->phone, $user->secureOtp);

          DB::commit();

      } catch (\Exception $e) {
          DB::rollback();
          return response()->json([
              'error' => 'Erreur lors de la création du compte',
              'message' => $e->getMessage()
          ], 500);
      }

      return response()->json([
          'message' => 'Utilisateur créé avec succès',
          'user' => [
              'id' => $user->id,
              'name' => $user->name,
              'email' => $user->email,
              'phone' => $user->phone,
              'location' => $user->location,
              'role' => $user->role,
          ],
          'sms_envoye' => $smsEnvoye,
          'otp_generated' => true, // Temporaire pour le développement
          'otp_code' => $user->secureOtp // À supprimer en production
      ], 201);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @OA\Post(
     *     path="/auth/logout",
     *     tags={"Authentication"},
     *     summary="Déconnexion utilisateur",
     *     description="Invalider le token JWT actuel et déconnecter l'utilisateur",
     *     operationId="logout",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Token invalide ou expiré")
     * )
     */

    //swagger code for verifying user otp

    /**
     * @OA\Post(
     *     path="/auth/verify-otp",
     *     tags={"Authentication"},
     *     summary="Vérifier le code OTP",
     *     description="Vérifier le code OTP envoyé à l'utilisateur",
     *     operationId="verifyOtp",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="otp", type="integer", example=123456)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP vérifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="OTP verified successfully"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="OTP invalide ou expiré")
     * )
     */

    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'otp' => 'required|integer|digits:6',
        ]);



        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('secureOtp', $request->otp)->first();
        if (!$user) {
            return response()->json(['error' => 'Invalid OTP'], 401);
        }

        $user->secureOtp = null;
        $user->save();

        // Créer le profil associé à l'utilisateur
          $user->profil()->create([
            'bio' => 'This is a sample bio',
            'location' => 'Unknown',

          ]);


        // Générer le token JWT avec auth()->login()
        try {
            Log::debug('jwt.ttl debug', [
    'value' => config('jwt.ttl'),
    'type'  => gettype(config('jwt.ttl')),
]);

            $token = auth('api')->login($user);
            dump($token);

            return response()->json([
                'message' => 'OTP verified successfully',
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ], 200);
        } catch (\Exception $e) {
             Log::error('JWT login failed', ['error' => $e->getMessage()]);
            // Si JWT échoue, on génère un token simple pour l'instant
            return response()->json([
                 Log::error('JWT login failed', ['error' => $e->getMessage()]),
                'message' => 'OTP not verified successfully',
                'error' => 'JWT generation failed',
                'jwt_error' => $e->getMessage()
            ], 200);
        }
    }

    public function logout(): JsonResponse
    {
        $user = auth()->user();
        auth()->logout($user);

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @OA\Post(
     *     path="/auth/refresh",
     *     tags={"Authentication"},
     *     summary="Renouveler le token",
     *     description="Obtenir un nouveau token JWT en utilisant le token actuel",
     *     operationId="refresh",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token renouvelé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Token invalide ou expiré")
     * )
     */
    public function refresh(): JsonResponse
    {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @OA\Get(
     *     path="/auth/me",
     *     tags={"Authentication"},
     *     summary="Obtenir les informations de l'utilisateur connecté",
     *     description="Récupérer les données de l'utilisateur actuellement authentifié",
     *     operationId="me",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Informations utilisateur récupérées avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=401, description="Token invalide ou expiré")
     * )
     */
    public function me(): JsonResponse
    {
       echo('Fetching authenticated user data');
        return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     */
    protected function createNewToken($token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }

    /**
     * @OA\Put(
     *     path="/auth/user",
     *     tags={"User"},
     *     summary="Mettre à jour les informations de l'utilisateur",
     *     description="Mettre à jour les informations de l'utilisateur connecté",
     *     operationId="updateUser",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="phone", type="string", example="+123456789"),
     *             @OA\Property(property="location", type="string", example="New York, USA"),
     *             @OA\Property(property="role", type="string", example="user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Informations utilisateur mises à jour avec succès",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=401, description="Token invalide ou expiré"),
     *     @OA\Response(response=403, description="Accès refusé")
     * )
     */

    public function updateUser(Request $request): JsonResponse
    {
        $user = auth()->user();

        // Vérifier que l'utilisateur est connecté
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Autoriser seulement la modification de son propre profil (ou admin)
        $this->authorize('update', $user);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'role' => ['nullable', new Enum(UserType::class)]
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // récupérer uniquement les champs définis dans les règles de validation

        $dataToUpdate = [];

        foreach(['name','email','phone','location','role'] as $field){
            if(isset($request->$field)){
                $value = $request->input($field);
                if($value == null){
                    continue;
                }
                if($field === "role" && !is_null($value)){
                    $value = UserType::from($value);
                    $dataToUpdate[$field] = $value;
                }

                $dataToUpdate[$field] = $value;
            }
        }

        // Mettre à jour les données utilisateur
        $user->update($dataToUpdate);

        return response()->json([
            'message' => 'User successfully updated',
            'user' => $user->fresh() // Récupérer les données fraîches de la DB
        ]);
    }

    public function deleteUser($id): JsonResponse
    {
        $user = auth()->user();
        $userToDelete = User::findOrFail($id);

        // Vérifier l'autorisation avec la Policy
        $this->authorize('delete', $userToDelete);

        $userToDelete->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}
