# Docker Guide Complet - Projet Laravel CrowApp API

## 🚀 Installation et Configuration Docker

### 1. Installation Docker (macOS)

```bash
# Installer Docker Desktop depuis https://docker.com/products/docker-desktop
# Ou via Homebrew
brew install --cask docker

# Vérifier l'installation
docker --version
docker-compose --version
```

### 2. Commandes Docker de Base

```bash
# Lister les conteneurs
docker ps                    # Conteneurs actifs
docker ps -a                # Tous les conteneurs

# Lister les images
docker images

# Supprimer conteneurs/images
docker rm <container_id>     # Supprimer un conteneur
docker rmi <image_id>        # Supprimer une image
docker system prune          # Nettoyer tout (conteneurs, images, volumes non utilisés)

# Logs
docker logs <container_name>
docker logs -f <container_name>  # Suivre les logs en temps réel
```

---

## 🏗️ Configuration du Projet Laravel avec Docker

### 3. Structure des Fichiers Docker

#### A. Dockerfile (à créer à la racine)
```dockerfile
# Dockerfile
FROM php:8.2-fpm

# Installer les dépendances système
# Équivalent Node.js : FROM node:18-alpine
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_pgsql

# Installer Composer
# Équivalent Node.js : RUN npm install -g npm@latest
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
# Équivalent Node.js : WORKDIR /app
WORKDIR /var/www

# Copier les fichiers de dépendances
# Équivalent Node.js : COPY package*.json ./
COPY composer.json composer.lock ./

# Installer les dépendances PHP
# Équivalent Node.js : RUN npm ci --only=production
RUN composer install --no-dev --optimize-autoloader

# Copier le code source
# Équivalent Node.js : COPY . .
COPY . .

# Définir les permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Port d'écoute
# Équivalent Node.js : EXPOSE 3000
EXPOSE 9000

CMD ["php-fpm"]
```

#### B. docker-compose.yml (à créer à la racine)
```yaml
# docker-compose.yml
version: '3.8'

services:
  # Service PHP/Laravel
  # Équivalent Node.js : service "app" avec image node:18
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: crowapp_api
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./:/var/www
      - ./docker/php/local.ini:/usr/local/etc/php/conf.d/local.ini
    networks:
      - crowapp_network

  # Serveur Web Nginx
  # Équivalent Node.js : pas nécessaire car Node.js a son propre serveur
  webserver:
    image: nginx:alpine
    container_name: crowapp_webserver
    restart: unless-stopped
    ports:
      - "8000:80"  # Port équivalent Node.js : "3000:3000"
    volumes:
      - ./:/var/www
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    networks:
      - crowapp_network

  # Base de données PostgreSQL
  # Équivalent Node.js : même configuration mais souvent MongoDB
  db:
    image: postgres:15
    container_name: crowapp_db
    restart: unless-stopped
    ports:
      - "5433:5432"  # Port personnalisé (ton .env utilise 5433)
    environment:
      # Équivalent Node.js : variables dans process.env
      POSTGRES_DB: crowapp          # process.env.DB_NAME
      POSTGRES_USER: kadea          # process.env.DB_USER  
      POSTGRES_PASSWORD: admin123   # process.env.DB_PASSWORD
    volumes:
      - postgres_data:/var/lib/postgresql/data
    networks:
      - crowapp_network

  # Redis pour le cache
  # Équivalent Node.js : même service, utilisé pour sessions/cache
  redis:
    image: redis:7-alpine
    container_name: crowapp_redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    networks:
      - crowapp_network

# Volumes persistants
# Équivalent Node.js : volumes pour node_modules ou uploads
volumes:
  postgres_data:

# Réseau Docker
# Équivalent Node.js : même concept pour connecter les services
networks:
  crowapp_network:
    driver: bridge
```

#### C. Configuration Nginx
```bash
# Créer le dossier de config
mkdir -p docker/nginx
```

```nginx
# docker/nginx/default.conf
server {
    listen 80;
    index index.php index.html;
    error_log /var/log/nginx/error.log;
    access_log /var/log/nginx/access.log;
    root /var/www/public;

    # Équivalent Node.js : proxy_pass http://localhost:3000
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass app:9000;  # "app" = nom du service PHP
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
        gzip_static on;
    }
}
```

#### D. Configuration PHP
```bash
# Créer le dossier de config PHP
mkdir -p docker/php
```

```ini
# docker/php/local.ini
# Configuration PHP personnalisée
# Équivalent Node.js : variables dans process.env ou config files

upload_max_filesize=40M        ; process.env.MAX_FILE_SIZE
post_max_size=40M              ; process.env.MAX_POST_SIZE
memory_limit=256M              ; process.env.MEMORY_LIMIT
max_execution_time=600         ; process.env.TIMEOUT
max_input_vars=3000           ; process.env.MAX_VARS

# Extensions nécessaires (automatiquement chargées)
# Équivalent Node.js : dépendances dans package.json
extension=pdo_pgsql           ; npm install pg (PostgreSQL)
extension=redis               ; npm install redis
extension=gd                  ; npm install sharp (images)
```

---

## 🔧 Configuration Laravel pour Docker

### 4. Adaptation du fichier .env

```properties
# .env - Configuration Docker
# Chaque ligne commentée avec équivalent Node.js

APP_NAME=CrowApp                    # process.env.APP_NAME
APP_ENV=local                       # process.env.NODE_ENV='development'
APP_KEY=base64:...                  # process.env.JWT_SECRET
APP_DEBUG=true                      # process.env.DEBUG=true
APP_URL=http://localhost:8000       # process.env.BASE_URL

# Base de données (Docker)
# Équivalent Node.js : configuration DB dans config/database.js
DB_CONNECTION=pgsql                 # process.env.DB_TYPE='postgres'
DB_HOST=db                          # process.env.DB_HOST (nom du service Docker)
DB_PORT=5432                        # process.env.DB_PORT (port interne Docker)
DB_DATABASE=crowapp                 # process.env.DB_NAME
DB_USERNAME=kadea                   # process.env.DB_USER
DB_PASSWORD=admin123                # process.env.DB_PASSWORD

# Cache Redis (Docker)
# Équivalent Node.js : redis://redis:6379
CACHE_STORE=redis                   # process.env.CACHE_TYPE='redis'
REDIS_HOST=redis                    # process.env.REDIS_HOST (nom du service Docker)
REDIS_PASSWORD=null                 # process.env.REDIS_PASSWORD
REDIS_PORT=6379                     # process.env.REDIS_PORT

# JWT Configuration
# Équivalent Node.js : jsonwebtoken package
JWT_SECRET=your_jwt_secret          # process.env.JWT_SECRET
JWT_TTL=60                          # process.env.JWT_EXPIRES_IN
```

---

## 🚀 Commandes de Lancement

### 5. Démarrage du Projet

```bash
# 1. Construire et lancer les conteneurs
# Équivalent Node.js : docker-compose up pour une app Node
docker-compose up -d --build

# 2. Vérifier que les conteneurs tournent
docker-compose ps

# 3. Installer les dépendances Laravel
# Équivalent Node.js : docker-compose exec app npm install
docker-compose exec app composer install

# 4. Générer la clé d'application
# Équivalent Node.js : pas nécessaire
docker-compose exec app php artisan key:generate

# 5. Générer la clé JWT
# Équivalent Node.js : pas nécessaire (JWT géré différemment)
docker-compose exec app php artisan jwt:secret

# 6. Exécuter les migrations
# Équivalent Node.js : docker-compose exec app npm run migrate
docker-compose exec app php artisan migrate

# 7. Créer le lien de stockage
# Équivalent Node.js : configurer multer pour uploads
docker-compose exec app php artisan storage:link

# 8. Optimiser l'application
# Équivalent Node.js : npm run build
docker-compose exec app php artisan optimize
```

### 6. Commandes de Développement Quotidiennes

```bash
# Démarrer les conteneurs
# Équivalent Node.js : docker-compose up
docker-compose up -d

# Arrêter les conteneurs
# Équivalent Node.js : docker-compose down
docker-compose down

# Voir les logs
# Équivalent Node.js : docker-compose logs app
docker-compose logs -f app        # Laravel
docker-compose logs -f webserver  # Nginx
docker-compose logs -f db         # PostgreSQL

# Exécuter des commandes dans le conteneur
# Équivalent Node.js : docker-compose exec app npm run <script>
docker-compose exec app php artisan migrate
docker-compose exec app php artisan make:controller ApiController
docker-compose exec app php artisan route:list
docker-compose exec app composer require package-name

# Accéder au bash du conteneur
# Équivalent Node.js : docker-compose exec app sh
docker-compose exec app bash

# Redémarrer un service spécifique
# Équivalent Node.js : docker-compose restart app
docker-compose restart app
docker-compose restart db
```

### 7. Commandes de Base de Données

```bash
# Migrations
# Équivalent Node.js : sequelize db:migrate ou prisma migrate
docker-compose exec app php artisan migrate
docker-compose exec app php artisan migrate:rollback
docker-compose exec app php artisan migrate:reset

# Seeders
# Équivalent Node.js : sequelize db:seed ou npm run seed
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan migrate:fresh --seed

# Accéder à PostgreSQL
# Équivalent Node.js : connexion directe avec psql ou GUI
docker-compose exec db psql -U kadea -d crowapp
```

---

## 🔄 Workflow de Développement

### 8. Routine Quotidienne

```bash
# 1. Démarrer le projet
docker-compose up -d

# 2. Vérifier l'état
docker-compose ps

# 3. Voir les logs si problème
docker-compose logs -f app

# 4. Accéder à l'application
# Laravel : http://localhost:8000
# Équivalent Node.js : http://localhost:3000

# 5. Arrêter le projet en fin de journée
docker-compose down
```

### 9. Commandes de Débogage

```bash
# Problème de permissions
docker-compose exec app chown -R www-data:www-data storage
docker-compose exec app chmod -R 775 storage

# Vider les caches Laravel
# Équivalent Node.js : redémarrer l'app ou vider cache Redis
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear

# Reconstruire complètement
docker-compose down
docker-compose up -d --build --force-recreate

# Nettoyer Docker
docker system prune -f
docker volume prune -f
```

---

## 📋 Checklist de Vérification

### 10. Tests de Fonctionnement

```bash
# ✅ Vérifier que les conteneurs tournent
docker-compose ps

# ✅ Tester l'API
curl http://localhost:8000/api/test

# ✅ Vérifier la base de données
docker-compose exec db psql -U kadea -d crowapp -c "\l"

# ✅ Vérifier Redis
docker-compose exec redis redis-cli ping

# ✅ Vérifier les logs Laravel
docker-compose exec app tail -f storage/logs/laravel.log
```

---

## 🆘 Dépannage Courant

### 11. Problèmes Fréquents

```bash
# Erreur de port déjà utilisé
# Solution : changer les ports dans docker-compose.yml
lsof -i :8000  # Voir qui utilise le port
# Équivalent Node.js : lsof -i :3000

# Problème de permissions
docker-compose exec app chown -R www-data:www-data /var/www
docker-compose exec app chmod -R 755 /var/www

# Base de données inaccessible
# Vérifier que le service db est démarré
docker-compose ps
docker-compose logs db

# Cache Laravel corrompu
docker-compose exec app php artisan optimize:clear
```

---

## 🔗 URLs d'Accès

- **Application Laravel** : http://localhost:8000
- **Base de données PostgreSQL** : localhost:5433
- **Redis** : localhost:6379

**Équivalent Node.js :**
- **Application Node.js** : http://localhost:3000
- **Base de données MongoDB** : localhost:27017
- **Redis** : localhost:6379


