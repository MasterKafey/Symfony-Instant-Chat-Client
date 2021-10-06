<?php

namespace App\WampServer;


class WampTopic
{
    public const MESSAGE_SEND = 'message_send';

    public function getTopic(string $topic, array $parameters = []): string
    {
        foreach ($parameters as $name => $value) {
            $topic = str_replace("{" . $name . "}", $value, $topic);
        }

        return $topic;
    }
}
