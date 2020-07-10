<?php

declare(strict_types=1);

namespace Bolt\UsersExtension\Exclude;

use Bolt\Configuration\Config;
use Bolt\Configuration\Content\ContentType;
use Bolt\Controller\Frontend\DetailController;
use Bolt\Controller\Frontend\DetailControllerInterface;
use Bolt\Extension\ExtensionRegistry;
use Bolt\UsersExtension\Controller\AccessAwareController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProtectedDetailController extends AccessAwareController implements DetailControllerInterface
{
    /** @var Config */
    private $config;

    /** @var DetailController */
    private $detailController;

    public function __construct(DetailController $detailController, Config $config, ExtensionRegistry $registry)
    {
        parent::__construct($config, $registry);

        $this->config = $config;
        $this->detailController = $detailController;
    }

    /**
     * @Route(
     *     "/{contentTypeSlug}/{slugOrId}",
     *     name="record",
     *     requirements={"contentTypeSlug"="%bolt.requirement.contenttypes%"},
     *     methods={"GET|POST"})
     * @Route(
     *     "/{_locale}/{contentTypeSlug}/{slugOrId}",
     *     name="record_locale",
     *     requirements={"contentTypeSlug"="%bolt.requirement.contenttypes%", "_locale": "%app_locales%"},
     *     methods={"GET|POST"})
     *
     * @param string|int $slugOrId
     */
    public function record($slugOrId, ?string $contentTypeSlug = null, bool $requirePublished = true): Response
    {
        $contentType = ContentType::factory($contentTypeSlug, $this->config->get('contenttypes'));
        $this->applyAllowForGroupsGuard($contentType);

        return $this->detailController->record($slugOrId, $contentTypeSlug, $requirePublished);
    }

    public function contentByFieldValue(string $contentTypeSlug, string $field, string $value): Response
    {
        $contentType = ContentType::factory($contentTypeSlug, $this->config->get('contenttypes'));
        $this->applyAllowForGroupsGuard($contentType);

        return $this->detailController->contentByFieldValue($contentTypeSlug, $field, $value);
    }
}
