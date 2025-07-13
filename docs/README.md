# CrowApp API - Documentation Swagger

Ce dossier contient la documentation Swagger/OpenAPI pour l'API CrowApp.

## Structure

- `swagger.yaml` - Fichier de documentation OpenAPI au format YAML (manuel)
- `README.md` - Ce fichier de documentation

## Accès à la documentation

La documentation interactive Swagger UI est accessible à l'adresse :
```
http://localhost:8000/api/documentation
```

## Installation et Configuration

La documentation Swagger a été configurée avec le package `darkaonline/l5-swagger` compatible avec Laravel.

### Installation
```bash
composer require darkaonline/l5-swagger
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
```

### Configuration
- Configuration principale : `config/l5-swagger.php`
- Documentation générée : `storage/api-docs/api-docs.json`
- Interface Swagger UI : `http://localhost:8000/api/documentation`

## Endpoints documentés

### Authentification
- `POST /auth/register` - Inscription d'un nouvel utilisateur
- `POST /auth/login` - Connexion utilisateur
- `POST /auth/logout` - Déconnexion utilisateur (authentifié)
- `POST /auth/refresh` - Renouvellement du token JWT (authentifié)
- `GET /auth/me` - Informations de l'utilisateur connecté (authentifié)

### Utilisateur
- `GET /user` - Informations de l'utilisateur connecté (authentifié)

## Authentification JWT

L'API utilise l'authentification JWT (JSON Web Token). Pour accéder aux endpoints protégés :

1. Utilisez l'endpoint `POST /auth/login` avec vos identifiants
2. Récupérez le `access_token` de la réponse
3. Incluez le token dans l'en-tête Authorization : `Bearer {token}`

### Exemple d'utilisation

```bash
# 1. Connexion
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password"}'

# 2. Utilisation du token (remplacez {token} par le token reçu)
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer {token}"
```

## Modification de la documentation

Pour modifier la documentation :

1. **Méthode manuelle** : Modifiez le fichier `swagger.yaml` dans ce dossier
2. **Méthode automatique** : Ajoutez des annotations Swagger dans les contrôleurs PHP

### Annotations dans les contrôleurs

Exemple d'annotation dans un contrôleur :

```php
/**
 * @OA\Post(
 *     path="/auth/login",
 *     tags={"Authentication"},
 *     summary="Connexion utilisateur",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="email", type="string", format="email"),
 *             @OA\Property(property="password", type="string", format="password")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Connexion réussie")
 * )
 */
public function login(Request $request) {
    // Code de la méthode
}
```

### Génération automatique

Pour régénérer la documentation à partir des annotations :

```bash
php artisan l5-swagger:generate
```

## Schémas de données

### User
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john.doe@example.com",
  "email_verified_at": null,
  "created_at": "2024-01-15T10:30:00.000000Z",
  "updated_at": "2024-01-15T10:30:00.000000Z"
}
```

### Token de connexion
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "bearer",
  "expires_in": 3600,
  "user": {
    // Objet User
  }
}
```

## Extensions VS Code recommandées

Pour éditer les fichiers Swagger, installez l'extension :
- **OpenAPI (Swagger) Editor** (`42crunch.vscode-openapi`)

Cette extension fournit :
- Coloration syntaxique
- Validation en temps réel
- Autocomplétion
- Aperçu de la documentation

## Notes

- La documentation est mise à jour automatiquement lors du redémarrage du serveur Laravel
- Les tokens JWT expirent par défaut après 60 minutes (configurable dans `config/jwt.php`)
- La configuration de sécurité CORS est gérée dans `config/cors.php`
