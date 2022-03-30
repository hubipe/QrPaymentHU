<?php

namespace hubipe\HuQrPayment\Iban\Validator;

class CompoundValidator implements ValidatorInterface
{

	/**
	 * @var ValidatorInterface[]
	 */
	private $validators;

	public function __construct(ValidatorInterface ...$validators)
	{
		if ($validators === []) {
			throw new \InvalidArgumentException('At least one validator is required');
		}
		$this->validators = $validators;
	}

	public function isValid(): bool
	{
		foreach ($this->validators as $validator) {
			if (!$validator->isValid()) {
				return FALSE;
			}
		}

		return TRUE;
	}
}
