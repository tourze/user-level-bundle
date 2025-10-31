<?php

declare(strict_types=1);

namespace UserLevelBundle\Service;

use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\RouteCollection;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;
use UserLevelBundle\Controller\AssignLogCrudController;
use UserLevelBundle\Controller\LevelCrudController;
use UserLevelBundle\Controller\UpgradeProgressCrudController;
use UserLevelBundle\Controller\UpgradeRuleCrudController;
use UserLevelBundle\Controller\UserLevelRelationCrudController;

#[AutoconfigureTag(name: 'routing.loader')]
class AttributeControllerLoader extends Loader implements RoutingAutoLoaderInterface
{
    private AttributeRouteControllerLoader $controllerLoader;

    private RouteCollection $collection;

    public function __construct()
    {
        parent::__construct();
        $this->controllerLoader = new AttributeRouteControllerLoader();

        $this->collection = new RouteCollection();
        $this->collection->addCollection($this->controllerLoader->load(LevelCrudController::class));
        $this->collection->addCollection($this->controllerLoader->load(UpgradeRuleCrudController::class));
        $this->collection->addCollection($this->controllerLoader->load(UserLevelRelationCrudController::class));
        $this->collection->addCollection($this->controllerLoader->load(UpgradeProgressCrudController::class));
        $this->collection->addCollection($this->controllerLoader->load(AssignLogCrudController::class));
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        return $this->collection;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return false;
    }

    public function autoload(): RouteCollection
    {
        return $this->collection;
    }
}
