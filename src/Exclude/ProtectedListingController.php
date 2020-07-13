<?php

declare(strict_types=1);

namespace Bolt\UsersExtension\Exclude;

use Bolt\Configuration\Config;
use Bolt\Configuration\Content\ContentType;
use Bolt\Controller\Frontend\ListingController;
use Bolt\Repository\ContentRepository;
use Bolt\UsersExtension\Controller\AccessAwareController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProtectedListingController extends AccessAwareController
{
    /** @var ListingController */
    private $listingController;

    /** @var Config */
    private $config;

    public function __construct(ListingController $listingController, Config $config)
    {
        parent::__construct($config);

        $this->listingController = $listingController;
        $this->config = $config;
    }

    /**
     * @Route(
     *     "/{contentTypeSlug}",
     *     name="listing_custom",
     *     requirements={"contentTypeSlug"="%bolt.requirement.contenttypes%"},
     *     methods={"GET|POST"})
     * @Route(
     *     "/{_locale}/{contentTypeSlug}",
     *     name="listing_locale_custom",
     *     requirements={"contentTypeSlug"="%bolt.requirement.contenttypes%", "_locale": "%app_locales%"},
     *     methods={"GET|POST"})
     */
    public function listing(ContentRepository $contentRepository, string $contentTypeSlug): Response
    {
        $contentType = ContentType::factory($contentTypeSlug, $this->config->get('contenttypes'));
        $this->applyAllowForGroupsGuard($contentType);

        return $this->listingController->listing($contentRepository, $contentTypeSlug);
    }
}
