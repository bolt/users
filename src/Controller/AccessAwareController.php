<?php

declare(strict_types=1);

namespace Bolt\UsersExtension\Controller;

use Bolt\Configuration\Content\ContentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

abstract class AccessAwareController extends AbstractController
{
    public function applyAllowForGroupsGuard(ContentType $contentType): void
    {
        if ($contentType->contains('allow_for_groups')) {
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
}
