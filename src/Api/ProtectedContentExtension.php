<?php

declare(strict_types=1);

namespace Bolt\UsersExtension\Api;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Bolt\Configuration\Config;
use Bolt\Entity\Content;
use Bolt\Entity\Field;
use Bolt\UsersExtension\Controller\AccessAwareController;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class ProtectedContentExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    /** @var Config */
    private $config;

    /** @var array */
    private $protectedContentTypes;

    public function __construct(Config $config, AccessAwareController $accessAwareController)
    {
        $this->config = $config;
        $cts = $this->config->get('contenttypes');

        $this->protectedContentTypes = [];

        foreach($cts as $ct) {
            try {
                $accessAwareController->applyAllowForGroupsGuard($ct);
            } catch (AccessDeniedHttpException $e) {
                $this->protectedContentTypes[] = $ct->get('slug');
            }
        }
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?string $operationName = null): void
    {
        if ($resourceClass === Content::class) {
            $this->filterProtectedContent($queryBuilder);
        }

        if ($resourceClass === Field::class) {
            $this->filterProtectedContentFields($queryBuilder);
        }
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?string $operationName = null, array $context = []): void
    {
        if ($resourceClass === Content::class) {
            $this->filterProtectedContent($queryBuilder);
        }

        if ($resourceClass === Field::class) {
            $this->filterProtectedContentFields($queryBuilder);
        }
    }

    private function filterProtectedContent(QueryBuilder $queryBuilder): void
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        //todo: Fix this when https://github.com/doctrine/orm/issues/3835 closed.
        if (! empty($this->protectedContentTypes)) {
            $queryBuilder->andWhere(sprintf('%s.contentType NOT IN (:cts)', $rootAlias));
            $queryBuilder->setParameter('cts', $this->protectedContentTypes);
        }
    }

    private function filterProtectedContentFields(QueryBuilder $queryBuilder): void
    {
        //todo: Fix this when https://github.com/doctrine/orm/issues/3835 closed.
        if (! empty($this->protectedContentTypes)) {
            $queryBuilder->andWhere('c.contentType NOT IN (:cts)');
            $queryBuilder->setParameter('cts', $this->protectedContentTypes);
        }
    }
}
