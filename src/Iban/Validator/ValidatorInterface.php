<?php

namespace hubipe\HuQrPayment\Iban\Validator;

interface ValidatorInterface
{
    public function isValid(): bool;
}
