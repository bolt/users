<?php

declare(strict_types=1);

namespace Bolt\UsersExtension\Controller;

use Bolt\Configuration\Config;
use Bolt\Configuration\Content\ContentType;
use Bolt\Controller\Backend\ContentEditController;
use Bolt\Controller\TwigAwareController;
use Bolt\Entity\Content;
use Bolt\Entity\User;
use Bolt\Enum\Statuses;
use Bolt\Event\Listener\ContentFillListener;
use Bolt\Repository\ContentRepository;
use Bolt\UsersExtension\ExtensionConfigTrait;
use Bolt\UsersExtension\Utils\ExtensionTemplateChooser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontendUsersProfileController extends AccessAwareController
{
    use ExtensionConfigTrait;

    /** @var ContentRepository */
    private $contentRepository;

    /** @var ContentFillListener */
    private $contentFillListener;

    /** @var TwigAwareController */
    private $twigAwareController;

    /** @var ContentEditController */
    private $contentEditController;

    /** @var ExtensionTemplateChooser */
    private $templateChooser;

    public function __construct(
        Config $config,
        ContentRepository $contentRepository,
        ContentFillListener $contentFillListener,
        TwigAwareController $twigAwareController,
        ContentEditController $contentEditController,
        ExtensionTemplateChooser $templateChooser)
    {
        parent::__construct($config);

        $this->contentRepository = $contentRepository;
        $this->contentFillListener = $contentFillListener;
        $this->twigAwareController = $twigAwareController;
        $this->contentEditController = $contentEditController;
        $this->templateChooser = $templateChooser;
    }

    /**
     * @Route("/profile",methods={"GET"}, name="extension_frontend_user_profile")
     */
    public function view(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user instanceof User && $this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('bolt_user_edit', ['id' => $user->getId()]);
        }

        $contentTypeSlug = $this->getExtension()->getExtConfig('contenttype', $user->getRoles()[0]);

        /** @var ContentType $contentType */
        $contentType = $this->getBoltConfig()->getContentType($contentTypeSlug);
        $this->applyAllowForGroupsGuard($contentType);

        return $this->twigAwareController->renderSingle($this->getUserRecord($contentType));
    }

    /**
     * @Route("/profile/edit", methods={"GET", "POST"}, name="extension_frontend_user_edit")
     */
    public function edit(Request $request): Response
    {
        $this->applyIsAuthenticatedGuard();

        $contentTypeSlug = $this->getExtension()->getExtConfig('contenttype', $this->getUser()->getRoles()[0]);

        /** @var ContentType $contentType */
        $contentType = $this->getBoltConfig()->getContentType($contentTypeSlug);
        $this->applyAllowForGroupsGuard($contentType);
        $this->applyEditProfileGuard();

        $content = $this->getUserRecord($contentType);

        if ($request->getMethod() === 'POST') {
            $this->contentEditController->save($content);

            return $this->redirectToRoute('extension_frontend_user_profile');
        }

        $templates = $this->templateChooser->forProfileEdit($this->getUser()->getRoles()[0]);

        $parameters = [
            'record' => $content,
            $content->getContentTypeSingularSlug() => $content,
        ];

        return $this->twigAwareController->renderTemplate($templates, $parameters);
    }

    private function getUserRecord(ContentType $contentType): Content
    {
        /** @var User $user */
        $user = $this->getUser();
        $content = $this->contentRepository->findBy([
            'author' => $user,
            'contentType' => $contentType->getSlug(),
        ]);

        if (empty($content)) {
            $content = new Content($contentType);
            $content->setAuthor($user);
            $content->setPublishedAt(new \DateTime());
            $content->setStatus(Statuses::PUBLISHED);
            $contentTypeName = strtolower($contentType->get('name', $contentType->get('slug')));
            $content->setContentType($contentTypeName);
            $this->contentFillListener->fillContent($content);
        } elseif (is_iterable($content)) {
            $content = end($content);
        }

        return $content;
    }
}
