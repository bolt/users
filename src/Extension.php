<?php

namespace Bolt\UsersExtension;

use Bolt\Extension\BaseExtension;

class Extension extends BaseExtension
{
    public function getName(): string
    {
        return 'Users Extension';
    }

    public function initialize(): void
    {
        dump("Here come the users!");
    }
}
