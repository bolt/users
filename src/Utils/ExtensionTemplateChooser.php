<?php

declare(strict_types=1);

namespace Bolt\UsersExtension\Utils;

use Bolt\TemplateChooser;
use Bolt\UsersExtension\ExtensionConfigInterface;
use Bolt\UsersExtension\ExtensionConfigTrait;
use Tightenco\Collect\Support\Collection;

class ExtensionTemplateChooser extends TemplateChooser implements ExtensionConfigInterface
{
    use ExtensionConfigTrait;

    public function forProfileEdit(string $group): array
    {
        $templates = new Collection();

        // @todo: Add default twig edit
        $templates->push($this->getExtension()->getExtConfig('profile_edit_template', $group, 'edit_profile.twig'));

        return $templates->unique()->filter()->toArray();
    }
}
