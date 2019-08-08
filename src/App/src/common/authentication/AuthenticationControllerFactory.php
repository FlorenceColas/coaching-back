<?php

namespace Authentication;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class AuthenticationControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return AuthenticationController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $authService = $container->get(ZendAuthenticationService::class);
        $config      = $container->get('config');
        $mailService = $container->get(MailService::class);

        return new AuthenticationController($authService, $config, $mailService);
    }
}
