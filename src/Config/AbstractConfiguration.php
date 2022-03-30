<?php

namespace hubipe\HuQrPayment\Config;

abstract class AbstractConfiguration implements ConfigurationInterface
{

    public function getVersion(): string
    {
        return '001';
    }

}
