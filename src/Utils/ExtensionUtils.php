<?php

namespace Bolt\UsersExtension\Utils;

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ExtensionUtils
{
    /** @var UrlGeneratorInterface */
    private $generator;

    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    public function isRoute(string $route, array $params = []): bool
    {
        try {
            $this->generator->generate($route, $params);
        } catch(RouteNotFoundException $e) {
            return false;
        }

        return true;
    }

    public function generateFromRoute(string $route, array $params = []): string
    {
        return $this->generator->generate($route, $params);
    }
}