<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Envoie un SMS avec l'OTP à un numéro de téléphone
     *
     * @param string $phone Numéro de téléphone destinataire
     * @param int $otp Code OTP à envoyer
     * @return bool True si l'envoi a réussi, false sinon
     */
    public static function envoyerSms($phone, $otp): bool
    {
        $url = env('SMS_API_URL', 'https://nmlygy.api.infobip.com/sms/2/text/advanced');
        
        $headers = [
            'Authorization' => 'App ' . env('SMS_API_KEY'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $body = [
            'messages' => [
                [
                    'destinations' => [
                        ['to' => $phone]
                    ],
                    'from' => '447491163443',
                    'text' => "Votre code de vérification est : {$otp}. Il est valide pour 5 minutes."
                ]
            ]
        ];

        try {
            $response = Http::withHeaders($headers)->post($url, $body);
            
            if ($response->successful()) {
                Log::info('SMS envoyé avec succès', [
                    'phone' => $phone,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('Erreur lors de l\'envoi du SMS', [
                    'phone' => $phone,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'envoi du SMS', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}














