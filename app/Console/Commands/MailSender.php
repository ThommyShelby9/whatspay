<?php

namespace App\Console\Commands;

use App\Consts\Util;
use App\Mail\Email;
use App\Models\Transaction;
use App\Models\Transactiondata;
use App\Traits\Utils;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MailSender extends Command
{

    use Utils;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:mailsender';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'RabbitMq Consumer : MailSenderQueue';

    protected $queueName = Util::MAILSENDER_QUEUE;

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
     */
    public function handle()
    {
        $connection = new AMQPStreamConnection(
            config('app.docker_instance').'davRabbitMqServer'
            , 5672, config('app.docker_rabbitmq_user'), config('app.docker_rabbitmq_pass'));
        $channel = $connection->channel();


        $channel->queue_declare(
            $this->queueName,
            false,
            true,
            false,
            false
        );

        echo " [*] ".gmdate("Y-m-d H:i:s")." Waiting for messages. To exit press CTRL+C\n";

        $callback = function ($msg) {
            echo ' [x] '.gmdate("Y-m-d H:i:s").' Received ', $msg->body, "\n";

            try {
                $data = json_decode($msg->body, true);
                $this->MailSender($data);
            }catch (Exception $exception){
                echo ' [x] '.gmdate("Y-m-d H:i:s").' Error. details : ', $exception->getMessage()."\n".$msg->body, "\n";
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };


        $channel->basic_qos(null, 1, null);

        $channel->basic_consume(
            $this->queueName,
            '',
            false,
            false,
            false,
            false,
            $callback
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();

        return Command::SUCCESS;

    }
}
