<?php

declare(strict_types=1);

namespace Bolt\UsersExtension;

trait ExtensionConfigTrait
{
    public function getExtension(): Extension
    {
        /** @var Extension $extension */
        return $this->registry->getExtension(Extension::class);
    }
}
