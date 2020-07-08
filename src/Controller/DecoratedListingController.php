<?php

namespace Bolt\UsersExtension\Controller;

use Bolt\Configuration\Config;
use Bolt\Configuration\Content\ContentType;
use Bolt\Controller\Frontend\ListingController;
use Bolt\Entity\User;
use Bolt\Repository\ContentRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class DecoratedListingController
{
    /** @var ListingController */
    private $listingController;
    /** @var Config */
    private $config;

    /** @var Security */
    private $security;

    public function __construct(ListingController $listingController, Config $config, Security $security)
    {
        $this->listingController = $listingController;
        $this->config = $config;
        $this->security = $security;
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

        if ($contentType->contains('allow_for_groups')) {
            $user = $this->security->getUser();

            if (! $user instanceof User || $contentType->get('allow_for_groups')->intersect($user->getRoles())->isEmpty()) {
                // Insufficient permissions
                throw new AccessDeniedHttpException();
            }
        }

        return $this->listingController->listing($contentRepository, $contentTypeSlug);
    }

}
