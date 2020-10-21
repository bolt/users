<?php

declare(strict_types=1);

namespace Bolt\UsersExtension\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RegistrationFormExtension extends AbstractExtension
{
    /** @var UrlGeneratorInterface */
    private $router;

    /** @var CsrfTokenManagerInterface */
    private $csrfTokenManager;

    public function __construct(UrlGeneratorInterface $router, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    /**
     * Register Twig functions.
     */
    public function getFunctions(): array
    {
        $safe = [
            'is_safe' => ['html'],
        ];

        return [
            new TwigFunction('registration_form', [$this, 'getRegistrationForm'], $safe),
            new TwigFunction('registration_form_displayname', [$this, 'getDisplayNameField'], $safe),
            new TwigFunction('registration_form_username', [$this, 'getUsernameField'], $safe),
            new TwigFunction('registration_form_password', [$this, 'getPasswordField'], $safe),
            new TwigFunction('registration_form_email', [$this, 'getEmailField'], $safe),
            new TwigFunction('registration_form_group', [$this, 'getGroupField'], $safe),
            new TwigFunction('registration_form_csrf', [$this, 'getCsrfField'], $safe),
            new TwigFunction('registration_form_submit', [$this, 'getSubmitButton'], $safe),
        ];
    }

    public function getRegistrationForm(string $group, bool $withLabels = true, array $labels = []): string
    {
        $displayname = $this->getDisplayNameField($withLabels, $labels);
        $username = $this->getUsernameField($withLabels, $labels);
        $password = $this->getPasswordField($withLabels, $labels);
        $email = $this->getEmailField($withLabels, $labels);
        $group = $this->getGroupField($group);
        $csrf = $this->getCsrfField();
        $submit = $this->getSubmitButton($labels);
        $postUrl = $this->router->generate('extension_edit_frontend_user');

        return sprintf("<form method='post' action='%s'>%s %s %s %s %s %s %s</form>", $postUrl, $displayname, $username, $password, $email, $group, $submit, $csrf);
    }

    public function getDisplayNameField(bool $withLabel, array $labels): string
    {
        $text = in_array('displayname', $labels, true) ? $labels['displayname'] : 'Display Name';
        $label = $withLabel ? sprintf('<label for="displayname">%s</label>', $text) : '';

        $input = '<input type="text" id="displayname" name="displayname">';

        return $label . $input;
    }

    public function getUsernameField(bool $withLabel, array $labels): string
    {
        $text = in_array('username', $labels, true) ? $labels['username'] : 'Username';
        $label = $withLabel ? sprintf('<label for="username">%s</label>', $text) : '';

        $input = '<input type="text" id="username" name="username">';

        return $label . $input;
    }

    public function getPasswordField(bool $withLabel, array $labels): string
    {
        $text = in_array('password', $labels, true) ? $labels['password'] : 'Password';
        $label = $withLabel ? sprintf('<label for="password">%s</label>', $text) : '';

        $input = '<input type="password" id="password" name="password">';

        return $label . $input;
    }

    public function getEmailField(bool $withLabel, array $labels): string
    {
        $text = in_array('email', $labels, true) ? $labels['email'] : 'Email';
        $label = $withLabel ? sprintf('<label for="email">%s</label>', $text) : '';

        $input = '<input type="email" id="email" name="email">';

        return $label . $input;
    }

    public function getGroupField(string $group): string
    {
        return sprintf('<input type="hidden" id="group" name="group" value="%s">', $group);
    }

    public function getSubmitButton(array $labels = []): string
    {
        $text = in_array('submit', $labels, true) ? $labels['submit'] : 'Submit';

        return sprintf('<input type="submit" value="%s">', $text);
    }

    public function getCsrfField(): string
    {
        $token = $this->csrfTokenManager->getToken('edit_frontend_user');

        return sprintf('<input type="hidden" name="_csrf_token" value="%s">', $token);
    }
}
