<?php

declare(strict_types=1);

namespace Bolt\UsersExtension;

use Bolt\Extension\ExtensionRegistry;

trait ExtensionConfigTrait
{
    /** @var ExtensionRegistry */
    private $registry;

    public function getExtension(): Extension
    {
        /* @phpstan-ignore-next-line */
        return $this->registry->getExtension(Extension::class);
    }

    /**
     * @required
     */
    public function setRegistry(ExtensionRegistry $registry): void
    {
        $this->registry = $registry;
    }
}
