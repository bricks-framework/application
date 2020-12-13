<?php

/** @copyright Sven Ullmann <kontakt@sumedia-webdesign.de> **/

namespace BricksFramework\Application;

use BricksCmf\ConfigService\ConfigService;
use BricksCmf\DiService\DiService;
use BricksCmf\DiService\DiServiceInterface;
use BricksCmf\EventManager\EventManager;
use BricksFramework\Bootstrap\BootstrapInterface;
use BricksFramework\Event\EventManager\EventManagerInterface;
use BricksFramework\ServiceLocator\ServiceLocatorInterface;

class Application implements ApplicationInterface
{
    /** @var BootstrapInterface */
    protected $bootstrap;

    /** @var ServiceLocatorInterface */
    protected $serviceLocator;

    protected $applicationModules;

    public function __construct(BootstrapInterface $bootstrap = null)
    {
        $this->bootstrap = $bootstrap;
        if ($this->bootstrap) {
            $this->initBootstrap();
        }
        $this->applicationModules = $this->serviceLocator->get(DiService::SERVICE_NAME);
    }

    protected function initBootstrap() : void
    {
        $di = $this->getDiFromBootstrap();
        $this->serviceLocator = $di->get('BricksFramework\\ServiceLocator\\ServiceLocator');

        foreach ($this->bootstrap->getServices() as $name) {
            $this->serviceLocator->set($name, $this->bootstrap->getService($name));
        }
    }

    protected function getDiFromBootstrap() : DiServiceInterface
    {
        $diService = $this->bootstrap->getService(DiService::SERVICE_NAME);
        if (!$diService) {
            $diServiceFactory = $this->bootstrap->getInstance('BricksCmf\\DiService\\Factory\\DiServiceFactory');
            $diService = $diServiceFactory->get($this->bootstrap->getContainer(), 'BricksCmf\\DiService\\DiService');
            $this->bootstrap->setService(DiService::SERVICE_NAME, $diService);
        }
        return $diService;
    }

    protected function getEventManager() : EventManagerInterface
    {
        $eventManager = $this->serviceLocator->get(EventManager::SERVICE_NAME);
        if (!$eventManager) {
            $di = $this->serviceLocator->get(DiService::SERVICE_NAME);
            $eventManager = $di->get('BricksCmf\\EventManager\\EventManager');
            $this->serviceLocator->set(EventManager::SERVICE_NAME, $eventManager);
        }
        return $eventManager;
    }

    public function run() : void
    {
        $this->configure();
        $this->initialize();

        echo '<h2>Hello World</h2><p>This Bootstrap has container stuff:</p><ul>';
        foreach ($this->bootstrap->getContainer()->getIterator() as $key => $value) {
            echo "<li><strong>$key</strong>: " . (is_object($value) ? get_class($value) : (!is_callable($value) ? $value : 'callable')) . "</li>";
        }
        echo '</ul>';
        echo '<p>This Application has services:</p><ul>';
        foreach ($this->serviceLocator->getServices() as $key => $value) {
            echo "<li><strong>$key</strong>: " . get_class($value) . "</li>";
        }
        echo '</ul>';
    }

    protected function configure() : void
    {
    }

    protected function initialize() : void
    {
    }
}
