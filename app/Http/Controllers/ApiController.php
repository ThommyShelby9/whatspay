<?php

namespace App\Http\Controllers;

use App\Consts\Util;
use App\Models\Country;
use App\Models\Phone;
use App\Models\Phonehistory;
use App\Models\User;
use App\Traits\Utils;
use DateInterval;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use WasenderApi\WasenderClient;

class ApiController extends Controller
{

    use Utils;

    public function __construct(){}

    public function baseFunction(Request $request){
        $report = [];
        $report["error"] = true;
        $report["error_message"] = "";
        $report["success"] = false;
        $report["operator_response"] = [];
        $report["http_status"] = 500;
        $report["http_reason"] = "";
        $requestData = $request->all();

        switch (strtoupper($request->method())) {
          case "POST":

            break;
          case "GET":

            break;
        }

        return response()->json($report, $report["http_status"]);
    }

    public function whatsappGeneratecode(Request $request, WasenderClient $client){
        $report = [];
        $report["error"] = true;
        $report["error_message"] = "";
        $report["success"] = false;
        $report["operator_response"] = [];
        $report["http_status"] = 500;
        $report["http_reason"] = "";
        $requestData = $request->all();

      try {
        switch (strtoupper($request->method())) {
          case "POST":
            if(empty($requestData["session"])){
              $report["error_message"] = "Session non identifiee [1]";
            }elseif(empty($requestData["phonecountry"])){
              $report["error_message"] = "Veuillez bien indiquer le pays correspondant au numero de telephone svp";
            }elseif(empty($requestData["phone"])){
              $report["error_message"] = "Veuillez bien indiquer le numero de telephone svp";
            }else{
              $user = User::where("id", $requestData["session"])->get();
              if(count($user) == 0){
                $report["error_message"] = "Session non identifiee [2]";
              }else{
                $country = Country::where("id", $requestData["phonecountry"])->get();
                if(count($country) == 0){
                  $report["error_message"] = "Pays non identifie";
                }else{
                  $phonenumber = $country[0]->phone_code."".$requestData["phone"];
                  $phone = Phone::where('user_id', $requestData["session"])
                    ->where("phonecountry_id", $requestData["phonecountry"])
                    ->where("phone", $phonenumber)
                    ->get();

                  $date = gmdate('Y-m-d H:i:s');
                  $code = $this->generateKey(6,7);
                  $message = "Pour valider votre numero WhatsApp, veuillez bien saisir le code suivant svp : ".$code;

                  $pId = "";
                  if(count($phone) == 0){
                    $pId = $this->getId();
                    Phone::create([
                      'id' => $pId,
                      'phone' => $phonenumber,
                      'phonecountry_id' => $requestData["phonecountry"],
                      'status' => Util::PHONE_STATUSES["PENDING"]["label"],
                      'valcode' => $code,
                      'valcode_gendate' => $date,
                      'user_id' => $requestData["session"]
                    ]);
                  }else{
                    $pId = $phone[0]->id;
                    Phone::where('id', $pId)->update([
                      'status' => Util::PHONE_STATUSES["PENDING"]["label"],
                      'valcode' => $code,
                      'valcode_gendate' => $date,
                    ]);
                  }

                  $result = $client->sendText($phonenumber, $message);
                  $phId = $this->getId();
                  Phonehistory::create([
                    'id' => $phId,
                    'history' => json_encode([
                      "phone" => $phonenumber,
                      "message" => $message,
                      "result" => $result
                    ]),
                    'phone_id' => $pId
                  ]);
                  if(isset($result["success"]) && $result["success"] == true){
                    $report["http_status"] = 200;
                    $report["error"] = false;
                    $report["error_message"] = "Veuillez bien verifier votre messagerie WhatsApp svp";
                    $report["success"] = true;
                    $report["operator_response"]["pId"] = $pId;
                  }else{
                    $report["error_message"] = "Une erreur est survenue lors de l'envoi du message.
                    Veuillez bien reesayer plus tard svp.";
                  }
                }
              }

            }

            break;
          case "GET":

            break;
        }
      }catch (Exception $exception){
        $report["error"] = true;
        $report["error_message"] = $exception->getMessage();
      }

        return response()->json($report, $report["http_status"]);
    }

    public function whatsappValidatecode(Request $request){
        $report = [];
        $report["error"] = true;
        $report["error_message"] = "";
        $report["success"] = false;
        $report["operator_response"] = [];
        $report["http_status"] = 500;
        $report["http_reason"] = "";
        $requestData = $request->all();

        switch (strtoupper($request->method())) {
          case "POST":
            if(empty($requestData["session"])){
              $report["error_message"] = "Session non identifiee [1]";
            }elseif(empty($requestData["phonecountry"])){
              $report["error_message"] = "Veuillez bien indiquer le pays correspondant au numero de telephone svp";
            }elseif(empty($requestData["phone"])){
              $report["error_message"] = "Veuillez bien indiquer le numero de telephone svp";
            }elseif(empty($requestData["code"])){
              $report["error_message"] = "Veuillez bien indiquer le code de validation svp";
            }elseif(empty($requestData["whatsappNumberId"])){
              $report["error_message"] = "Session non identifiee [3]";
            }else{
              $user = User::where("id", $requestData["session"])->get();
              if(count($user) == 0){
                $report["error_message"] = "Session non identifiee [2]";
              }else{
                $country = Country::where("id", $requestData["phonecountry"])->get();
                if(count($country) == 0){
                  $report["error_message"] = "Pays non identifie";
                }else{

                  $phonenumber = $country[0]->phone_code."".$requestData["phone"];
                  $phone = Phone::where('id', $requestData["whatsappNumberId"])
                    ->where("user_id", $requestData["session"])
                    ->where("phonecountry_id", $requestData["phonecountry"])
                    ->where("phone", $phonenumber)
                    ->where("valcode", $requestData["code"])
                    ->get();

                  if(count($phone) == 1){

                    $report["http_status"] = 200;
                    $report["error"] = false;
                    $report["error_message"] = "Numero WhatsApp valide avec succes";
                    $report["success"] = true;


                    Phone::where('id', $requestData["whatsappNumberId"])->update([
                      'status' => Util::PHONE_STATUSES["ACTIVE"]["label"]
                    ]);


                  }else{
                    $report["error_message"] = "Nous n'avons pas pu valider votre Numero WhatsApp.
                    Veuillez bien reesayer plus tard svp.";
                  }

                }
              }

            }
            break;
          case "GET":

            break;
        }

        return response()->json($report, $report["http_status"]);
    }

  public function whatsappnotifier(Request $request){
    $requestData = $request->all();
    $this->createLogg($requestData,"whatsappnotifier_","" );
    return response()->json([
      'error' => false,
      'message' => 'message received'
    ], 200);
  }

  public function upload(Request $request)
  {
    try {
      // Vérification si c'est un seul fichier ou plusieurs
      $files = $request->file('files');
      if (!$files) {
        return response()->json([
          'error' => true,
          'message' => 'Aucun fichier trouvé dans la requête.'
        ], 400);
      }
      if (!is_array($files)) {
        $files = [$files];
      }
      $saved_media = [];

      foreach ($files as $file) {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $destinationPath = public_path('uploads/images');
        $file->move($destinationPath, $filename);

        $saved_media[] = [
          'file_name' => $filename,
          'file_type' => $file->getClientMimeType(),
          'original_name' => $file->getClientOriginalName(),
          'path' => $destinationPath,
          'state' => 'active',
        ];
      }

      return response()->json([
        'error' => false,
        'files' => $saved_media,
        'message' => ( count($saved_media) > 1 ? 'Fichiers enregistrés avec succès.' : 'Fichier enregistré avec succès.' )
      ], 201);

    } catch (\Exception $e) {
      return response()->json([
        'error' => true,
        'message' => 'Erreur lors de l\'enregistrement du fichier : ' . $e->getMessage()
      ], 500);
    }
  }


}
