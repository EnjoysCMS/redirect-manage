<?php

declare(strict_types=1);


namespace EnjoysCMS\RedirectManage\Controller;


use DI\Container;
use EnjoysCMS\Module\Admin\AdminController;
use EnjoysCMS\RedirectManage\Config;

abstract class AbstractController extends AdminController
{
    protected Config $moduleConfig;
    protected \EnjoysCMS\Module\Admin\Config $adminConfig;

    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->moduleConfig = $container->get(Config::class);
        $this->adminConfig = $container->get(\EnjoysCMS\Module\Admin\Config::class);

        /** @psalm-suppress UndefinedInterfaceMethod */
        $this->twig->getLoader()->addPath($this->moduleConfig->getAdminTemplatePath(), 'redirect-manage');
    }
}
