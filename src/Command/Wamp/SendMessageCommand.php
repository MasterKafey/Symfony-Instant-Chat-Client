<?php

namespace App\Command\Wamp;

use App\WampClient\WampClient;
use App\WampServer\WampTopic;
use Psr\Log\NullLogger;
use React\EventLoop\Loop;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Thruway\Logging\Logger;

#[AsCommand(name: 'app:message:send')]
class SendMessageCommand extends Command
{
    public function __construct(
        private readonly WampClient $wampClient,
        private readonly WampTopic $wampTopic,
    )
    {
        parent::__construct();
        Logger::set(new NullLogger());
    }

    public function configure(): void
    {
        $this->addArgument('message', InputArgument::REQUIRED, 'Message you want to send');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
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
        $this->wampClient->start();

        return Command::SUCCESS;
    }
}
