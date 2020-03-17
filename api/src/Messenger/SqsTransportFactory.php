<?php
namespace App\Messenger;


use Aws\Sqs\SqsClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Transport\AmqpExt\AmqpTransport;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * Class SqsTransportFactory
 * @package App\Messenger
 * @author Federico Bollotta <federico.bollotta@gmail.com>
 */

class SqsTransportFactory implements TransportFactoryInterface {

    private $_sqs;
    private $_logger;
    private $_serializer;

    /**
     * SqsTransportFactory constructor.
     * @param SqsClient $sqs
     * @param LoggerInterface $_logger
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        SqsClient $sqs,
        LoggerInterface $_logger,
        ?SerializerInterface $serializer = null
    ){
        $this->_sqs = $sqs;
        $this->_logger = $_logger;
        $this->_serializer = $serializer;
    }

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer = null): TransportInterface {
        return new SqsTransport($this->_sqs, null, $this->_logger, $dsn);
    }

    public function supports(string $dsn, array $options): bool {
        return preg_match('#^https://sqs\.[\w\-]+\.amazonaws\.com/.+#', $dsn) === 1;
    }
}
