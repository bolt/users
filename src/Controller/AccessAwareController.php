<?php

declare(strict_types=1);

namespace Bolt\UsersExtension\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

abstract class AccessAwareController extends AbstractController
{
    /** Override*/
    protected function denyAccessUnlessGranted($attributes, $subject = null, string $message = 'Access Denied.'): void
    {
        if (! $this->isGranted($attributes)) {
            throw new AccessDeniedHttpException();
        }
    }
}
