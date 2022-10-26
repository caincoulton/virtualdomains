<?php

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Janguo\Component\VirtualDomains\Administrator\Extension\VirtualDomainsComponent;

return new class implements ServiceProviderInterface {

    public function register(Container $container): void {
        $container->registerServiceProvider(new MVCFactory('\\Janguo\\Component\\VirtualDomains'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\NetballNZ\\Component\\VirtualDomains'));
        $container->registerServiceProvider(new RouterFactory('\\Janguo\\Component\\VirtualDomains'));

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new VirtualDomainsComponent($container->get(ComponentDispatcherFactoryInterface::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setRouterFactory($container->get(RouterFactoryInterface::class));

                return $component;
            }
        );
    }
    
};