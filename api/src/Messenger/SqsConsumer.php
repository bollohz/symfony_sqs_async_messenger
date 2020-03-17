<?php


namespace App\Messenger;


use App\Messenger\Messages\IngestionMessage;
use Aws\Sqs\SqsClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Serializer\SerializerInterface;

class SqsConsumer implements ReceiverInterface {

    private $_sqs;
    private $_serializer;
    private $_uri;
    private $_logger;

    public function __construct(
        SqsClient $_sqsClient,
        ?SerializerInterface $_serializer,
        LoggerInterface $_logger
    ){
        $this->_sqs = $_sqsClient;
        $this->_serializer = $_serializer ?? new PhpSerializer();
        $this->_logger = $_logger;
    }

    public function setUri($_uri){
        $this->_uri = $_uri;
        return $this;
    }

    public function getUri(): string {
        return $this->_uri;
    }

    /**
     * @inheritDoc
     */
    public function get(): iterable {
        $_arguments = [
            '@region' => SqsTransport::REGION,
            'AttributeNames' => ['All'],
            'MessageAttributeNames' => ['All'],
            'MaxNumberOfMessages' => SqsTransport::MAX_NUMBER_RECEIVED,
            'QueueUrl' => $this->getUri(),
            'WaitTimeSeconds' => 15,
        ];
        $_messages = $this->_sqs->receiveMessage($_arguments);
        if ($_messages->hasKey('Messages')) {
            $_ingestionMessages = [];
            foreach ($_messages->get('Messages') as $_message){
                $this->_logger->info("MESSAGE IS > " . json_encode($_message));
                array_push($_ingestionMessages, [
                    'body' => $_message['Body']
                ]);
            }
            $_envelopes = [];
            foreach ($_ingestionMessages as $_ingestionMessage){
                $_envelope = $this->_serializer->decode($_ingestionMessage);
                array_push($_envelopes, $_envelope->with(new ReceivedStamp('sqs')));
            }
            return $_envelopes;
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function ack(Envelope $envelope): void {
        return;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function reject(Envelope $envelope): void {
       throw new \Exception("MESSAGE REJECTED");
    }
}
