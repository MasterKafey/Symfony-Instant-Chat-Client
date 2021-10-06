<?php

namespace App\Command\Wamp;

use App\WampClient\WampClient;
use App\WampServer\WampTopic;
use Psr\Log\NullLogger;
use React\EventLoop\Loop;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Thruway\Logging\Logger;

class SendMessageCommand extends Command
{
    private WampClient $wampClient;

    private WampTopic $wampTopic;

    protected static $defaultName = 'app:send-message';

    public function __construct(string $name = null)
    {
        parent::__construct($name);
        Logger::set(new NullLogger());
    }

    public function configure()
    {
        $this->addArgument('message', InputArgument::REQUIRED, 'Message you want to send');
    }

    /** @required */
    public function setWampClient(WampClient $wampClient): self
    {
        $this->wampClient = $wampClient;

        return $this;
    }

    /** @required */
    public function setWampTopic(WampTopic $wampTopic): self
    {
        $this->wampTopic = $wampTopic;

        return $this;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $input->getArgument('message');
        $this->wampClient->on('open', function() use ($message, $input, $output) {
            $this->wampClient->publish($this->wampTopic->getTopic(WampTopic::MESSAGE_SEND), [], [
                    'username' => $this->wampClient->getAuthId(),
                    'content' => $message,
                ]
            )->then(function() {
                $this->wampClient->getSession()->close();
                Loop::get()->stop();
            }, function ($message) use ($input, $output) {
                $output->writeln("Error to send message", $message);
                $this->wampClient->getSession()->close();
                Loop::get()->stop();
            });
        });
        $this->wampClient->start(true);

        return Command::SUCCESS;
    }
}
