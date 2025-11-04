<?php

namespace App\Services;

use App\Consts\Util;
use App\Models\Country;
use App\Models\Phone;
use App\Models\Phonehistory;
use App\Models\User;
use App\Traits\Utils;
use WasenderApi\WasenderClient;

class WhatsAppService
{
    use Utils;

    protected $wasenderClient;

    public function __construct(WasenderClient $wasenderClient)
    {
        $this->wasenderClient = $wasenderClient;
    }

    public function startPhoneVerification($userId, $phoneCountryId, $phoneNumber)
    {
        $result = [
            'success' => false,
            'message' => 'Une erreur est survenue lors de la génération du code',
            'phone_id' => null
        ];

        // Vérifier que l'utilisateur existe
        $user = User::find($userId);
        if (!$user) {
            $result['message'] = 'Session non identifiée [2]';
            return $result;
        }

        // Vérifier que le pays existe
        $country = Country::find($phoneCountryId);
        if (!$country) {
            $result['message'] = 'Pays non identifié';
            return $result;
        }

        // Formatage du numéro de téléphone
        if (!str_starts_with($phoneNumber, $country->phone_code)) {
            $phoneNumber = $country->phone_code . $phoneNumber;
        }

        // Vérifier si le numéro existe déjà
        $phone = Phone::where('user_id', $userId)
            ->where("phonecountry_id", $phoneCountryId)
            ->where("phone", $phoneNumber)
            ->first();

        // Générer un code de vérification à 6 chiffres
        $date = gmdate('Y-m-d H:i:s');
        $code = $this->generateKey(6, 7);
        $message = "Pour valider votre numéro WhatsApp, veuillez bien saisir le code suivant : " . $code;

        $phoneId = "";
        if (!$phone) {
            $phoneId = $this->getId();
            Phone::create([
                'id' => $phoneId,
                'phone' => $phoneNumber,
                'phonecountry_id' => $phoneCountryId,
                'status' => Util::PHONE_STATUSES["PENDING"]["label"],
                'valcode' => $code,
                'valcode_gendate' => $date,
                'user_id' => $userId
            ]);
        } else {
            $phoneId = $phone->id;
            Phone::where('id', $phoneId)->update([
                'status' => Util::PHONE_STATUSES["PENDING"]["label"],
                'valcode' => $code,
                'valcode_gendate' => $date,
            ]);
        }

        // Envoyer le code par WhatsApp
        $apiResult = $this->wasenderClient->sendText($phoneNumber, $message);
        
        // Enregistrer l'historique
        $historyId = $this->getId();
        Phonehistory::create([
            'id' => $historyId,
            'history' => json_encode([
                "phone" => $phoneNumber,
                "message" => $message,
                "result" => $apiResult
            ]),
            'phone_id' => $phoneId
        ]);

        // Vérifier le résultat
        if (isset($apiResult["success"]) && $apiResult["success"] == true) {
            $result['success'] = true;
            $result['message'] = 'Veuillez vérifier votre messagerie WhatsApp';
            $result['phone_id'] = $phoneId;
        } else {
            $result['message'] = 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer plus tard.';
        }

        return $result;
    }

    public function verifyPhone($userId, $phoneId, $code, $phoneCountryId = null, $phoneNumber = null)
    {
        $result = [
            'success' => false,
            'message' => 'Une erreur est survenue lors de la validation'
        ];

        // Vérifier que l'utilisateur existe
        $user = User::find($userId);
        if (!$user) {
            $result['message'] = 'Session non identifiée [2]';
            return $result;
        }

        $query = Phone::where('id', $phoneId)
            ->where('user_id', $userId)
            ->where('valcode', $code);

        // Ajouter des conditions supplémentaires si fournies
        if ($phoneCountryId) {
            $query->where('phonecountry_id', $phoneCountryId);
        }

        if ($phoneNumber) {
            $query->where('phone', $phoneNumber);
        }

        $phone = $query->first();

        if ($phone) {
            // Valider le numéro
            Phone::where('id', $phoneId)->update([
                'status' => Util::PHONE_STATUSES["ACTIVE"]["label"]
            ]);
            
            $result['success'] = true;
            $result['message'] = 'Numéro WhatsApp validé avec succès';
        } else {
            $result['message'] = 'Nous n\'avons pas pu valider votre numéro WhatsApp. Veuillez réessayer plus tard.';
        }

        return $result;
    }

    public function sendMessage($recipient, $message)
    {
        return $this->wasenderClient->sendText($recipient, $message);
    }
}