<?php


namespace App\Messenger;


use App\Messenger\Messages\IngestionMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class Subscriber implements MessageHandlerInterface {

    /** @var LoggerInterface  */
    private $_logger;
    /** @var SqsConsumer  */
    private $_consumer;

    public function __construct(
        LoggerInterface $_logger,
        SqsConsumer $_consumer
    ){
        $this->_logger = $_logger;
        $this->_consumer = $_consumer;
    }

    public function __invoke(IngestionMessage $_message) {
        $this->_logger->info("Message Subscriber Invoke");
        $this->_logger->info($_message->getBody());
    }

}
