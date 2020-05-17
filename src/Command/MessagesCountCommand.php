<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;

class MessagesCountCommand extends Command
{
    protected static $defaultName = 'mbt:messages:count';

    private $receiverLocator;

    public function __construct(ContainerInterface $receiverLocator)
    {
        $this->receiverLocator = $receiverLocator;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDefinition([
                new InputArgument('receiver', InputArgument::REQUIRED, 'Name of the receiver/transport to count', null),
            ])
            ->setDescription('Consumes messages')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command count messages of a transport.

    <info>php %command.full_name% <receiver-name></info>
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $receiverName = $input->getArgument('receiver');
        if (!$this->receiverLocator->has($receiverName)) {
            throw new RuntimeException(sprintf('The receiver "%s" does not exist.', $receiverName));
        }

        $receiver = $this->receiverLocator->get($receiverName);

        if (!$receiver instanceof MessageCountAwareInterface) {
            throw new RuntimeException(sprintf('The receiver "%s" does not support count messages.', $receiverName));
        }

        $output->write($receiver->getMessageCount());

        return 0;
    }
}
