<?php

namespace ExampleApp;

use Lipid\BasePDO;
use Lipid\Config;
use Lipid\Config\Cfg;

final class AppPDO extends BasePDO
{
    public function __construct(Config $config = null)
    {
        parent::__construct(
            $config ?? new Cfg()
        );
    }
}
