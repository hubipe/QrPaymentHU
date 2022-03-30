<?php

namespace hubipe\HuQrPayment\Iban;

use hubipe\HuQrPayment\Helper\ToStringIban;
use hubipe\HuQrPayment\Iban\Validator\GenericIbanValidator;
use hubipe\HuQrPayment\Iban\Validator\ValidatorInterface;

class IBAN implements IbanInterface
{
    use ToStringIban;

    /**
     * @var string
     */
    private $iban;

    public function __construct(string $iban)
    {
        $this->iban = $iban;
    }

    /**
     * Returns the resulting IBAN.
     *
     * @return string
     */
    public function asString(): string
    {
        return $this->iban;
    }

    /**
     * Returns the validator that checks whether the IBAN is valid.
     *
     * @return ValidatorInterface|null
     */
    public function getValidator(): ?ValidatorInterface
    {
        return new GenericIbanValidator($this);
    }
}
