<?php

declare(strict_types=1);

namespace Bolt\UsersExtension\Event;

use Bolt\Event\UserEvent;
use Bolt\UsersExtension\ExtensionConfigInterface;
use Bolt\UsersExtension\ExtensionConfigTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tightenco\Collect\Support\Collection;

class UserEventSubscriber implements EventSubscriberInterface, ExtensionConfigInterface
{
    use ExtensionConfigTrait;

    public function onUserEdit(UserEvent $event): void
    {
        $extConfig = $this->getExtension()->getConfig();
        $configRoles = array_keys($extConfig->get('groups', []));

        /** @var Collection $cts */
        $cts = $this->getExtension()->getBoltConfig()->get('contenttypes');

        $roles = [];
        foreach ($configRoles as $role) {
            $accessTo = array_keys($cts->filter(function(Collection $ct) use ($role) {
                return $ct->has('allow_for_groups') && $ct->get('allow_for_groups')->contains($role);
            })->toArray());

            $roles[$role] = empty($accessTo) ? '' : 'Grants access to  ' .
                implode(",", $accessTo);
        }


        $event->setRoleOptions($event->getRoleOptions()->merge($roles));
    }

    public static function getSubscribedEvents()
    {
        return [
            UserEvent::ON_EDIT => ['onUserEdit', 0],
        ];
    }
}
