#!/bin/bash

echo "🧪 === TEST DES APIs RÉELLES ===\n"

API_BASE="http://localhost:8000/api"
USER_EMAIL="john.api.test.$(date +%s)@example.com"
USER_PASSWORD="password123"
USER_NAME="John API Test"
USER_PHONE="+1234567890"

echo "1️⃣ Test de l'API de base..."
curl -s -X GET "$API_BASE/test" | jq '.'

echo -e "\n2️⃣ Inscription d'un nouvel utilisateur..."
REGISTER_RESPONSE=$(curl -s -X POST "$API_BASE/auth/register" \
  -H "Content-Type: application/json" \
  -d "{
    \"name\": \"$USER_NAME\",
    \"email\": \"$USER_EMAIL\",
    \"password\": \"$USER_PASSWORD\",
    \"password_confirmation\": \"$USER_PASSWORD\",
    \"phone\": \"$USER_PHONE\",
    \"user_type\": \"investisseur\"
  }")

echo "$REGISTER_RESPONSE" | jq '.'

# Extraction de l'OTP pour la vérification
OTP=$(echo "$REGISTER_RESPONSE" | jq -r '.otp_code // empty')

if [ -z "$OTP" ] || [ "$OTP" = "null" ]; then
    echo "❌ Échec de l'inscription - pas d'OTP reçu"
    exit 1
fi

echo "✅ OTP reçu: $OTP"

echo -e "\n3️⃣ Vérification de l'OTP et connexion..."
LOGIN_RESPONSE=$(curl -s -X POST "$API_BASE/auth/login" \
  -H "Content-Type: application/json" \
  -d "{
    \"email\": \"$USER_EMAIL\",
    \"password\": \"$USER_PASSWORD\"
  }")

echo "$LOGIN_RESPONSE" | jq '.'

# Extraction du token
TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.access_token // .token // empty')

if [ -z "$TOKEN" ] || [ "$TOKEN" = "null" ]; then
    echo "❌ Échec de la connexion - pas de token reçu"
    exit 1
fi

echo "✅ Token reçu: ${TOKEN:0:50}..."

echo -e "\n4️⃣ Récupération du profil utilisateur (doit créer le profil automatiquement)..."
curl -s -X GET "$API_BASE/user/profile" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | jq '.'

echo -e "\n5️⃣ Mise à jour du profil utilisateur..."
UPDATE_RESPONSE=$(curl -s -X PUT "$API_BASE/user/profile" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "bio": "Investisseur passionné par les technologies innovantes et les startups disruptives.",
    "profession": "Entrepreneur",
    "company": "TechVentures Inc.",
    "birth_date": "1985-03-15",
    "city": "Paris",
    "country": "France",
    "gender": "male",
    "interests": ["technologie", "startup", "intelligence artificielle"],
    "expertise_areas": ["fintech", "blockchain", "e-commerce"],
    "investment_preferences": ["tech", "green energy", "healthcare"],
    "risk_tolerance": "medium",
    "experience_level": "advanced"
  }')

echo "$UPDATE_RESPONSE" | jq '.'

echo -e "\n6️⃣ Récupération du profil mis à jour..."
curl -s -X GET "$API_BASE/user/profile" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | jq '.'

echo -e "\n7️⃣ Récupération des médias utilisateur..."
curl -s -X GET "$API_BASE/user/medias" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" | jq '.'

echo -e "\n🎉 === TESTS TERMINÉS ===\n"
