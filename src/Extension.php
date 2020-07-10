<?php

declare(strict_types=1);

namespace Bolt\UsersExtension;

use Bolt\Extension\BaseExtension;

class Extension extends BaseExtension
{
    public function getName(): string
    {
        return 'Users Extension';
    }

    public function getExtConfig(string $config, string $group = '', $fallback = null)
    {
        $groups = $this->getConfig()->get('groups', []);

        if (array_key_exists($group, $groups) && array_key_exists($config, $groups[$group])) {
            return $groups[$group][$config];
        }

        $default = $this->getConfig()->get('default', []);
        if (in_array($config, array_keys($default), true)) {
            return $default[$config];
        }

        return $fallback;
    }
}
