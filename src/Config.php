<?php

declare(strict_types=1);

namespace EnjoysCMS\RedirectManage;

use EnjoysCMS\Core\Modules\AbstractModuleConfig;

final class Config extends AbstractModuleConfig
{

    public function getModulePackageName(): string
    {
        return 'enjoyscms/redirect-manage';
    }

    public function getAdminTemplatePath(): string
    {

        return __DIR__ . '/../template';
    }

}
