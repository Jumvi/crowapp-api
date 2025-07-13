<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="CrowApp API Documentation",
 *     version="1.0.0",
 *     description="Documentation de l'API CrowApp pour l'authentification et la gestion des utilisateurs.

## Authentification
Cette API utilise JWT (JSON Web Token) pour l'authentification.

### Comment utiliser l'authentification:
1. Utilisez l'endpoint `/auth/login` pour obtenir un token
2. Incluez le token dans l'en-tête Authorization: `Bearer {token}`
3. Utilisez ce token pour accéder aux endpoints protégés",
 *     @OA\Contact(
 *         name="CrowApp API Support",
 *         email="support@crowapp.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Serveur de développement local"
 * )
 * 
 * @OA\Server(
 *     url="https://api.crowapp.com",
 *     description="Serveur de production"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="JWT token obtenu via l'endpoint /auth/login

Format: `Bearer {token}`

Exemple: `Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...`"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="Endpoints pour l'authentification des utilisateurs"
 * )
 * 
 * @OA\Tag(
 *     name="User",
 *     description="Endpoints pour la gestion des utilisateurs"
 * )
 */
abstract class Controller
{
    //
}
