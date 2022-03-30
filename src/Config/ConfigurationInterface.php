<?php

namespace hubipe\HuQrPayment\Config;

interface ConfigurationInterface
{

	/**
     * The standard version. Currently only 001 is supported
     *
     * @return string
     */
    public function getVersion(): string;

}
