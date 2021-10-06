<?php

namespace App\WampServer\Security\ClientAuthenticator;

use Thruway\Authentication\ClientAuthenticationInterface;
use Thruway\Message\AuthenticateMessage;
use Thruway\Message\ChallengeMessage;

class UserClientAuthenticator implements ClientAuthenticationInterface
{
    private string $authId;

    public function getAuthId()
    {
        return $this->authId;
    }

    /** @required */
    public function setAuthId($authid)
    {
        $this->authId = $authid;
    }

    public function getAuthMethods(): array
    {
        return ['user_wampcra'];
    }

    public function getAuthenticateFromChallenge(ChallengeMessage $msg): AuthenticateMessage
    {
        return new AuthenticateMessage($this->authId);
    }
}
