<?php

namespace App\Command\Wamp;

use App\WampClient\WampClient;
use App\WampServer\WampTopic;
use Psr\Log\NullLogger;
use React\EventLoop\Loop;
use React\Promise\PromiseInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Thruway\ClientSession;
use Thruway\Logging\Logger;

#[AsCommand('app:message:read')]
class StartWampClientCommand extends Command
{


    public function __construct(
        private readonly WampTopic  $wampTopic,
        private readonly WampClient $wampClient,
    )
    {
        parent::__construct();
        Logger::set(new NullLogger());
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->wampClient->on('open', function (ClientSession $session) use ($input, $output) {
            $output->writeln("Connecté en tant que " . $this->wampClient->getAuthId());
            $session->subscribe($this->wampTopic->getTopic(WampTopic::MESSAGE_SEND), function (array $parameters, \stdClass $payload) use ($output) {
                $output->writeln("{$payload->username}: {$payload->content}");
            }, function ($data) use ($output) {
                $output->writeln('Error, can\'t subscribe to topic', $data);
            });

        });
        $this->wampClient->start(true);

        return Command::SUCCESS;
    }
}
