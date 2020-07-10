<?php

declare(strict_types=1);

namespace Bolt\UsersExtension;

/**
 * All classes that use the extension configuration must implement this.
 */
interface ExtensionConfigInterface
{
    public function getExtension(): Extension;
}
