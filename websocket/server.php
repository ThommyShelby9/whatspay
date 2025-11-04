<?php
session_start();

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

// Make sure composer dependencies have been installed
require __DIR__ . '/vendor/autoload.php';

/**
 * chat.php
 * Send any incoming messages to all connected clients (except sender)
 */
class davWebSocketServer implements MessageComponentInterface
{
    protected $clients;
    protected $redis;
    protected $redis_version;
    protected $redis_expiry = 1800;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        try {
          $this->redis = new Predis\Client([
            'scheme' => 'tcp',
            'host'   => 'davRedisServer',
            'port'   => 6379,
            'password' => 'davREDISPassw0rd',
            'options' => null,
          ]);
        }catch (Exception $exception){
          $this->redis = null;
          echo 'redis not connected. reason : '.$exception->getMessage();
        }
        $this->redis_version = gmdate("YmdHis");
    }

    public function onOpen(ConnectionInterface $client) {
        echo "davWebSocketClient attached [". $client->resourceId."] from ".$client->remoteAddress.PHP_EOL;
        $this->clients->attach($client);
    }

    private function getValue(&$key, &$value)
    {
      $value = $this->redis->get($key);
      if(empty($value)){$value = json_encode(["ressources"=>[], "messages"=>[]]);};
      $value = json_decode($value, true);
      if(empty($value["ressources"])){$value["ressources"] = [];}
      if(empty($value["messages"])){$value["messages"] = [];}
    }

    public function onMessage(ConnectionInterface $client, $msg) {
        echo 'message received : ['. $client->resourceId.'] from '.$client->remoteAddress.' : '.$msg.PHP_EOL;
        try {
          $key = null; $value = null;
          $msg = json_decode($msg, true);
          $value = null;

          if(!empty($msg["chanel"])){
            $key = $msg["chanel"];
            $this->getValue($key, $value);
          }

          if(!empty($msg["setChanel"]) && !empty($msg["chanel"])){
            echo 'setting channel ' . $key . PHP_EOL;
            array_push($value["ressources"], $client->resourceId);
          }

          if(!empty($msg["sendMessage"]) && !empty($msg["chanel"]) && !empty($msg["message"])){
            echo 'sending message to chanel '.$key.' x ['. $client->resourceId.'] from '.$client->remoteAddress.PHP_EOL;
            array_push($value["messages"], [
              'message' => $msg["message"],
              'source' => $client->resourceId,
            ]);
          }

          if(!empty($key)){
            echo 'key ok = '.$key.PHP_EOL;
            $this->redis->setex($key, $this->redis_expiry, json_encode($value));
            if(!empty($value["messages"])) {
              if(!empty($msg["setChanel"])){
                foreach ($value["messages"] as $message) {
                  $client->send(json_encode($message));
                }
              }
              if(!empty($msg["sendMessage"])){
                echo 'hum ['. $client->resourceId.'] from '.$client->remoteAddress.PHP_EOL;
                foreach ($this->clients as $c) {
                  echo 'ham ['. $client->resourceId.'] from '.$client->remoteAddress.PHP_EOL;
                  if(in_array($c->resourceId, $value["ressources"]) && ($c->resourceId != $client->resourceId)){
                      echo $client->resourceId.' is sending message to '.$c->resourceId.PHP_EOL;
                      $c->send(json_encode($msg));
                  }
                }
              }
            }
          }else{
            echo 'key nok = '.PHP_EOL;
          }

        }catch (Exception $exception){
          echo 'an error occured [1]. details : '.$exception->getMessage().PHP_EOL;
        }
    }

    public function onClose(ConnectionInterface $client) {
        echo "davWebSocketClient detached [". $client->resourceId."] from ".$client->remoteAddress.PHP_EOL;
        $this->clients->detach($client);
    }

    public function onError(ConnectionInterface $client, \Exception $e) {
        echo "davWebSocketServer error [". $client->resourceId."] from ".$client->remoteAddress." : ".$e->getMessage().PHP_EOL;
        $client->close();
    }
}

// Run the server application through the WebSocket protocol on port 8080
$app = new Ratchet\App('127.0.0.1', 8080, '0.0.0.0');
$app->route('/', new davWebSocketServer, array('*'));

echo "davWebSocketServer started".PHP_EOL;

$app->run();
