<?php

declare(strict_types=1);

namespace Bolt\UsersExtension\Controller;

use Bolt\Configuration\Config;
use Bolt\Configuration\Content\ContentType;
use Bolt\Extension\ExtensionController;
use Bolt\Extension\ExtensionRegistry;
use Bolt\UsersExtension\ExtensionConfigInterface;
use Bolt\UsersExtension\ExtensionConfigTrait;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AccessAwareController extends ExtensionController implements ExtensionConfigInterface
{
    use ExtensionConfigTrait;

    /** @var ExtensionRegistry */
    protected $registry;

    public function __construct(Config $config, ExtensionRegistry $registry)
    {
        parent::__construct($config);

        $this->registry = $registry;
    }

    public function applyAllowForGroupsGuard(ContentType $contentType): void
    {
        if ($contentType->has('allow_for_groups')) {
            $allowedGroups = $contentType->get('allow_for_groups');
            if (! is_iterable($allowedGroups)) {
                $this->denyAccessUnlessGranted(new Expression(sprintf("'%s' in role_names", $allowedGroups)));
            }

            // Iterate over all of the given roles.
            // If at least one is allowed, then the user has access and we return.
            foreach ($allowedGroups as $role) {
                if ($this->isGranted(new Expression(sprintf("'%s' in role_names", $role)))) {
                    return;
                }
            }

            throw new AccessDeniedHttpException();
        }
    }

    public function applyIsAuthenticatedGuard(): void
    {
        if ($this->isGranted(new Expression('is_anonymous()'))) {
            throw new AccessDeniedHttpException();
        }
    }

    public function applyEditProfileGuard(): void
    {
        if (! $this->getExtension()->getExtConfig('allow_profile_edit', $this->getUser()->getRoles()[0], false)) {
            throw new AccessDeniedHttpException();
        }
    }
}
