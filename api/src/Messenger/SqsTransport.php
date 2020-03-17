<?php


namespace App\Messenger;
use Aws\Sqs\Exception\SqsException;
use Aws\Sqs\SqsClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Serializer\SerializerInterface;

class SqsTransport implements TransportInterface {

    const REGION = "eu-west-1";
    const MAX_NUMBER_RECEIVED = 1;

    /** @var SqsClient  */
    private $_sqs;
    /** @var PhpSerializer|SerializerInterface|null  */
    private $_serializer;
    /** @var string  */
    private $_uri;
    /** @var LoggerInterface  */
    private $_logger;
    /** @var SqsConsumer */
    private $_receiver;

    public function __construct(
        SqsClient $_sqs,
        ?SerializerInterface $_serializer,
        LoggerInterface $_logger,
        string $_uri
    ){
        $this->_sqs = $_sqs;
        $this->_serializer = $_serializer ?? new PhpSerializer();
        $this->_logger = $_logger;
        $this->_uri = $_uri;
    }

    /**
     * @inheritDoc
     */
    public function send(Envelope $envelope): Envelope {
        $_encodedMessage = $this->_serializer->encode($envelope);
        $_headers = $_encodedMessage['headers'] ?? [];
        try {
            $_result = $this->_sqs->sendMessage([
                'MessageAttributes' => [
                    'Headers' => [
                        'DataType' => 'String', 'StringValue' => json_encode($_headers, JSON_THROW_ON_ERROR)
                    ]
                ],
                'MessageBody' => $_encodedMessage['body'],
                'QueueUrl' => $this->_uri
            ]);
        } catch (SqsException $_sqsException) {
            $this->_logger->error("Cannot add message to queue due to internal error.. > SQS QUEUE ERROR ");
            throw new TransportException($_sqsException->getMessage());
        } catch (TransportException $_transportException){
            $this->_logger->error("Cannot add message to queue due to internal error.. > TRANSPORT ERROR ");
            throw new TransportException($_transportException->getMessage());
        }

        if(is_null($_result) || !$_result->hasKey('MessageId')){
            $_e = "Could not add a message to the SQS queue";
            $this->_logger->error($_e);
            throw new TransportException($_e);
        }

        return $envelope;
    }

    public function getReceiver(): SqsConsumer {
        $this->_receiver = new SqsConsumer($this->_sqs, null, $this->_logger);
        $this->_receiver->setUri($this->_uri);
        return $this->_receiver;
    }

    /**
     * @inheritDoc
     */
    public function get(): iterable {
        return ($this->receiver ?? $this->getReceiver())->get();
    }

    /**
     * @inheritDoc
     */
    public function ack(Envelope $envelope): void {
        $_return = $this->receiver ?? $this->getReceiver()->ack($envelope);
        return;
    }

    /**
     * @inheritDoc
     */
    public function reject(Envelope $envelope): void {
        $_return = $this->receiver ?? $this->getReceiver()->reject($envelope);
        return;
    }

}
