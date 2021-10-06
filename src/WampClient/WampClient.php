<?php

namespace App\WampClient;


use App\WampServer\Security\ClientAuthenticator\UserClientAuthenticator;
use React\Promise\PromiseInterface;
use Thruway\Peer\Client;
use Thruway\Transport\PawlTransportProvider;

class WampClient extends Client
{
    public function __construct(string $authid)
    {
        parent::__construct('user');
        $this->setAuthMethods(['user_wampcra']);
        $this->setAuthId($authid);
    }

    /** @required */
    public function setUserClientAuthenticator(UserClientAuthenticator $userClientAuthenticator): self
    {
        $this->addClientAuthenticator($userClientAuthenticator);

        return $this;
    }

    /** @required */
    public function setWampURL(string $wampURL)
    {
        $this->addTransportProvider(new PawlTransportProvider($wampURL));
    }

    public function publish($topicName, array $arguments = [], array $payload = [], array $options = []): PromiseInterface
    {
        $options['acknowledge'] = true;
        return $this->getSession()->publish($topicName, $arguments, $payload, $options);
    }
}
