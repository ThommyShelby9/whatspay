<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Phone;
use App\Models\Phonehistory;
use App\Services\WhatsAppService;
use App\Traits\Utils;
use Illuminate\Http\Request;
use WasenderApi\WasenderClient;

class WhatsAppApiController extends Controller
{
    use Utils;

    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    public function whatsappGeneratecode(Request $request, WasenderClient $client)
    {
        $report = [
            "error" => true,
            "error_message" => "",
            "success" => false,
            "operator_response" => [],
            "http_status" => 500,
            "http_reason" => ""
        ];

        $requestData = $request->all();

        try {
            if (strtoupper($request->method()) == "POST") {
                if (empty($requestData["session"])) {
                    $report["error_message"] = "Session non identifiée [1]";
                } elseif (empty($requestData["phonecountry"])) {
                    $report["error_message"] = "Veuillez bien indiquer le pays correspondant au numéro de téléphone";
                } elseif (empty($requestData["phone"])) {
                    $report["error_message"] = "Veuillez bien indiquer le numéro de téléphone";
                } else {
                    $result = $this->whatsAppService->generateVerificationCode(
                        $requestData["session"], 
                        $requestData["phonecountry"], 
                        $requestData["phone"]
                    );

                    if ($result['success']) {
                        $report["http_status"] = 200;
                        $report["error"] = false;
                        $report["error_message"] = "Veuillez vérifier votre messagerie WhatsApp";
                        $report["success"] = true;
                        $report["operator_response"]["pId"] = $result['phone_id'];
                    } else {
                        $report["error_message"] = $result['message'];
                    }
                }
            }
        } catch (\Exception $exception) {
            $report["error"] = true;
            $report["error_message"] = $exception->getMessage();
        }

        return response()->json($report, $report["http_status"]);
    }

    public function whatsappValidatecode(Request $request)
    {
        $report = [
            "error" => true,
            "error_message" => "",
            "success" => false,
            "operator_response" => [],
            "http_status" => 500,
            "http_reason" => ""
        ];

        $requestData = $request->all();

        try {
            if (strtoupper($request->method()) == "POST") {
                if (empty($requestData["session"])) {
                    $report["error_message"] = "Session non identifiée [1]";
                } elseif (empty($requestData["phonecountry"])) {
                    $report["error_message"] = "Veuillez bien indiquer le pays correspondant au numéro de téléphone";
                } elseif (empty($requestData["phone"])) {
                    $report["error_message"] = "Veuillez bien indiquer le numéro de téléphone";
                } elseif (empty($requestData["code"])) {
                    $report["error_message"] = "Veuillez bien indiquer le code de validation";
                } elseif (empty($requestData["whatsappNumberId"])) {
                    $report["error_message"] = "Session non identifiée [3]";
                } else {
                    $result = $this->whatsAppService->verifyPhone(
                        $requestData["session"],
                        $requestData["whatsappNumberId"],
                        $requestData["code"],
                        $requestData["phonecountry"],
                        $requestData["phone"]
                    );

                    if ($result['success']) {
                        $report["http_status"] = 200;
                        $report["error"] = false;
                        $report["error_message"] = "Numéro WhatsApp validé avec succès";
                        $report["success"] = true;
                    } else {
                        $report["error_message"] = $result['message'];
                    }
                }
            }
        } catch (\Exception $exception) {
            $report["error"] = true;
            $report["error_message"] = $exception->getMessage();
        }

        return response()->json($report, $report["http_status"]);
    }

    public function whatsappnotifier(Request $request)
    {
        $requestData = $request->all();
        $this->createLogg($requestData, "whatsappnotifier_", "");
        
        return response()->json([
            'error' => false,
            'message' => 'message received'
        ], 200);
    }
}