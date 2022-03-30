<?php

namespace hubipe\HuQrPayment\Helper;

trait ToStringIban
{
    public function __toString()
    {
        if (!method_exists($this, 'asString')) {
            return '';
        }

        try {
            return $this->asString();
        } catch (\Throwable $exception) {
            return '';
        }
    }
}
