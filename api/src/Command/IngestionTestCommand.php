<?php

namespace App\Command;

use App\Messenger\Messages\IngestionMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class IngestionTestCommand extends Command
{
    protected static $defaultName = 'ingestion:test';

    private $_messageBus;

    public function __construct(
        MessageBusInterface $_messageBus,
        string $name = null
    ){
        $this->_messageBus = $_messageBus;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('IngestionTest')
        ;
        parent::configure();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        try {
            $this->sendMessage($this->_messageBus, 'sensor with id 3569 is in ERROR ');
            return 0;
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            exit(1);
        }
    }

    public function sendMessage(MessageBusInterface $bus, string $message)
    {
        try {
            $msg = new IngestionMessage();
            $msg->setBody($message);
            $bus->dispatch($msg);

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
