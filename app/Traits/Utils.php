<?php

namespace App\Traits;

use App\Consts\Util;
use App\Mail\Email;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Http\Request;

trait Utils{

  public function createLog(Request &$request,$fileNamePrefix = "", $fileName = ""){
    //https://stackoverflow.com/questions/14837065/how-to-get-public-directory
    try {
      $requestFields = [];
      $requestFields["method"] = strtoupper($request->method());
      $requestFields["ip"] = $request->ip();
      $requestFields["ips"] = $request->ips();
      $requestFields["path"] = $request->path();
      $requestFields["full_url"] = $request->fullUrl();
      $requestFields["data"] = $request->all();
      $requestFields["fileNamePrefix"] = $fileNamePrefix;
      $requestFields["fileName"] = $fileName;
      $requestFields["logDate"] = gmdate("Y-m-d H:i:s");

      $this->sendDataToQueue($requestFields, Util::LOG_QUEUE);

    }catch (Exception $exception){}
  }

  public function createLogg($data, $fileNamePrefix = "", $fileName = ""){
    //https://stackoverflow.com/questions/14837065/how-to-get-public-directory
    try {
      $requestFields = $data;
      $requestFields["fileNamePrefix"] = $fileNamePrefix;
      $requestFields["fileName"] = $fileName;
      $requestFields["loggDate"] = gmdate("Y-m-d H:i:s");
      $this->sendDataToQueue($requestFields, Util::LOG_QUEUE);

    }catch (Exception $exception){}
  }

  public function getId(){
    $id = Str::uuid();
    return $id;
  }

  public function sendDataToQueue($requestData, $queue)
  {
    $response = false;
    try {
      $connection = new AMQPStreamConnection(config('app.docker_instance').'davRabbitMqServer'
        , 5672, config('app.docker_rabbitmq_user'), config('app.docker_rabbitmq_pass'));
      $channel = $connection->channel();
      $msg = new AMQPMessage(
        json_encode($requestData),
        array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
      );
      $channel->basic_publish($msg, '', $queue);
      $channel->close();
      $connection->close();
      $response = true;
    }catch (Exception $exception){}
    return $response;
  }

  public function getDueDate($delai = 10)
  {
    $date = new DateTime("".gmdate("Y-m-d H:i:s"));
    $date->add(new DateInterval('PT'.$delai.'S'));
    return gmdate("Y-m-d H:i:s", $date->getTimestamp());
  }

  public function validateEmail($email)
  {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return false;
    } else {
      return true;
    }
  }

  public function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      // 32 bits for "time_low"
      mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

      // 16 bits for "time_mid"
      mt_rand( 0, 0xffff ),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 4
      mt_rand( 0, 0x0fff ) | 0x4000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      mt_rand( 0, 0x3fff ) | 0x8000,

      // 48 bits for "node"
      mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
  }

  public function checkUuid($uuid){
    return Str::isUuid($uuid);
    //return  preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $uuid);
  }

  public function dateAdd($datedepart = "", $nbjour = 0, $format = "Y-m-d"){
    if ($datedepart == "") {$datedepart = gmdate("Y-m-d");}
    $date = date_create($datedepart);
    date_add($date, date_interval_create_from_date_string("" . $nbjour . " days"));
    return date_format($date, $format);
  }

  public function get_password_segment() {
    $digits = range('0', '9');
    $lowercase = range('a', 'z');
    $uppercase = range('A', 'Z');
    //$special = str_split('!@#$%^&*+=-_?.,:;<>(){}[]/|~`\'"');
    $special = str_split('!@#$%^&*+=-_?.,:;(){}[]/|~');
    shuffle($digits);
    shuffle($special);
    shuffle($lowercase);
    shuffle($uppercase);
    $array_special = array_rand($special);
    $array_digits = array_rand($digits, 3);
    $array_lowercase = array_rand($lowercase, 3);
    $array_uppercase = array_rand($uppercase, 3);
    $password = str_shuffle(
      $special[$array_special].
      $digits[$array_digits[0]].
      $digits[$array_digits[1]].
      $digits[$array_digits[2]].
      $lowercase[$array_lowercase[0]].
      $lowercase[$array_lowercase[1]].
      $lowercase[$array_lowercase[2]].
      $uppercase[$array_uppercase[0]].
      $uppercase[$array_uppercase[1]].
      $uppercase[$array_uppercase[2]]
    );
    $password = str_shuffle($password);
    if (strlen($password) > 10) {
      $password = substr($password, 0, 10);
    }
    $password = str_shuffle($password);
    return $password;
  }

  public function get_password($length){
    $password = "";
    if(is_numeric($length)){
      if(($length%10) != 0) {
        $p = intdiv($length, 10) + 1;
      }else{
        $p = intdiv($length, 10);
      }
      for($o=0; $o < $p; $o++){
        $password .= $this->get_password_segment();
      }
      $password = str_shuffle($password);
      $password = substr($password, 0, $length);
      $password = str_shuffle($password);
    }
    return $password;
  }

  private function MailSender($data = [])
  {
    if(!empty($data)){
      Mail::to($data['recipient'])->send(new Email($data));
    }
  }

  public function contains($haystack, $needle, $caseSensitive = false) {
    return $caseSensitive ?
      (strpos($haystack, $needle) === FALSE ? FALSE : TRUE):
      (stripos($haystack, $needle) === FALSE ? FALSE : TRUE);
  }

  public function generateKey($chrs = 40, $alpha = 0)
  {
    // la longueur par défaut du code généré est 10
    $list = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"; // variable contenant tous les caractères possibles dans le code généré (on peut donc completer d'autres valeur dedans . &,#,@ par exemple
    if ($alpha == 1) {
      $list = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    } elseif ($alpha == 2) {
      $list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    } elseif ($alpha == 3) {
      $list = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    } elseif ($alpha == 4) {
      $list = "0123456789";
    }elseif ($alpha == 5) {
      $list = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-_,;!&#{[@)$(]}*%?";
    }elseif ($alpha == 6) {
      $list = "123456789";
    }elseif ($alpha == 7) {
      $list = "123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    }

    //$seed = intval(((double)microtime())) * 1000000;
    $seed = (double) gmdate("YmdHis");

    mt_srand($seed);
    $newstring = "";
    while (strlen($newstring) < $chrs) {
      $newstring .= $list[mt_rand(0, strlen($list) - 1)];
    }
    return $newstring;
  }

}
