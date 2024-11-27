<?php
// src/Service/InvitationLinkService.php

namespace App\Service;

class InvitationLinkService
{
    private string $secret;
    private const LINK_VALIDITY_HOURS = 24; // Durée de validité en heures

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function generateSignedEmail(array  $userData): string
    {
        $timestamp = time();
        // ajouter les champs de l'utilisateur à signer
        $dataToSign = [
            'pseudo' => $userData['pseudo'],
            'email' => $userData['email'],
            'nom' => $userData['nom'],
            'prenom' => $userData['prenom'],
            'telephone' => $userData['telephone'] ?? null,
            'timestamp' => $timestamp
        ];
        $signature = hash_hmac('sha256', json_encode($dataToSign), $this->secret);

        $finalData = array_merge($dataToSign, ['signature' => $signature]);

        return base64_encode(json_encode($finalData));
    }

    /**
     * Valider les données signées par comparaison de la signature
     * @param string $signedData Données signées par l'aglorithme HMAC SHA256
     */
    public function validateSignedData(string $signedData): ?array
    {
        try {
            $data = json_decode(base64_decode($signedData), true);

            if (!isset($data['email'], $data['timestamp'], $data['signature'])) {
                return null;
            }

            // Vérifier si le lien n'a pas expiré
            $timestamp = (int) $data['timestamp'];
            $expirationTime = $timestamp + (self::LINK_VALIDITY_HOURS * 3600);

            if (time() > $expirationTime) {
                return null;
            }

            // Recréer la signature pour vérification
            $dataToVerify = array_diff_key($data, ['signature' => '']);
            $expectedSignature = hash_hmac('sha256', json_encode($dataToVerify), $this->secret);

            if (!hash_equals($expectedSignature, $data['signature'])) {
                return null;
            }

            // Retourner les données utilisateur si tout est valide
            return $dataToVerify;
        } catch (\Exception $e) {
            return null;
        }
    }
}
