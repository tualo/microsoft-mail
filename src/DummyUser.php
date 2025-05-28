<?php

namespace Tualo\Office\MicrosoftMail;

class DummyUser
{
    public static function get(): array
    {
        return [
            'id' => 'dummy',
            'displayName' => 'Dummy User',
            'mail' => ''
        ];
    }
}
