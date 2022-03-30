<?php

namespace hubipe\HuQrPayment\Iban;

use hubipe\HuQrPayment\Exceptions\InvalidBbanException;
use hubipe\HuQrPayment\Helper\ToStringIban;
use hubipe\HuQrPayment\Helper\Utils;
use hubipe\HuQrPayment\Iban\Validator\HungarianIbanValidator;
use Rikudou\Iban\Iban\IbanInterface;
use Rikudou\Iban\Validator\CompoundValidator;
use Rikudou\Iban\Validator\GenericIbanValidator;
use Rikudou\Iban\Validator\ValidatorInterface;

class HungarianBbanAdapter implements IbanInterface
{

	use ToStringIban;

	/**
	 * @var string
	 */
	private $bban;

	/**
	 * @var string|NULL
	 */
	private $iban = NULL;

	public function __construct(string $bban)
	{
		$this->bban = $bban;
	}

	/**
	 * Returns the resulting IBAN.
	 *
	 * @return string
	 */
	public function asString(): string
	{
		if ($this->iban === NULL) {
			$accountNumber = strtoupper((string) preg_replace('/[\s\-]+/', '', $this->bban));

			if (!in_array(strlen($accountNumber), [16, 24])) {
				throw new InvalidBbanException('Account number length is not valid');
			}

			$accountNumber = str_pad($accountNumber, 24, '0');

			$checkString = (string) preg_replace_callback(['/[A-Z]/', '/^[0]+/'], function ($matches) {
				if (substr($matches[0], 0, 1) !== '0') { // may be multiple leading 0's
					return base_convert($matches[0], 36, 10);
				}
				return '';
			}, $accountNumber . 'HU00');

			$mod = Utils::bcmod($checkString, '97');
			$code = (string) (98 - $mod);

			$this->iban = sprintf(
				'HU%s%s',
				str_pad($code, 2, '0', STR_PAD_LEFT),
				$accountNumber
			);
		}
		return $this->iban;
	}

	/**
	 * Returns the validator that checks whether the IBAN is valid.
	 *
	 * @return ValidatorInterface|NULL
	 */
	public function getValidator(): ?ValidatorInterface
	{
		return new CompoundValidator(
			new HungarianIbanValidator($this->bban),
			new GenericIbanValidator($this)
		);
	}
}
