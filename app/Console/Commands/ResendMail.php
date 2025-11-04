<?php

namespace App\Console\Commands;

use App\Consts\Util;
use App\Mail\Email;
use App\Models\User;
use App\Traits\Utils;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;

class ResendMail extends Command
{

    use Utils;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:resendmail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'RabbitMq Consumer : ResendMailQueue';

    protected $queueName = Util::RESENDMAIL_QUEUE;

    /**
     * queue_declare function:
     * Declares queue, creates if needed
     *
     * @param string $queue
     * @param bool $passive
     * @param bool $durable
     * @param bool $exclusive
     * @param bool $auto_delete
     * @param bool $nowait
     * @param array|AMQPTable $arguments
     * @param int|null $ticket
     * @return array|null
     *@throws \PhpAmqpLib\Exception\AMQPTimeoutException if the specified operation timeout was exceeded
     */

    /**
     * basic_consume function:
     * @param string consumer_tag: Consumer identifier
     * @param bool no_local: Don't receive messages published by this consumer.
     * @param bool no_ack: If set to true, automatic acknowledgement mode will be used by this consumer. See https://www.rabbitmq.com/confirms.html for details.
     * @param bool exclusive: Request exclusive consumer access, meaning only this consumer can access the queue
     * @param bool nowait:
     * callback: A PHP Callback
     */


    /**
     * Execute the console command.
     *
     *
     *
     *
     */
    public function handle()
    {



        $items = User::all();
        echo count($items).' items found'.PHP_EOL;
        foreach ($items as $item) {

          $token = [
            'type' => 'registration',
            'date' => gmdate("YmdHis"),
            'mail' => $item->email,
            'id' => $item->id
          ];
          //https://github.com/firebase/php-jwt
          $token = JWT::encode($token, Util::JWTKEY, 'HS256');

          $this->sendDataToQueue([
            'recipient' => $item->email,
            'type' => 'registration',
            'subject' => 'Inscription sur WhatsPAY',
            'lastname' => $item->lastname,
            'firstname' => $item->firstname,
            'token' => $token,
            'url' => config('app.url'),
          ], Util::MAILSENDER_QUEUE);

          echo 'mail sent to '.$item->email.' ['.$item->id.', '.$item->firstname.']'.PHP_EOL;
        }

        return Command::SUCCESS;

    }
}
