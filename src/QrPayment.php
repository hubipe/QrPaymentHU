<?php

namespace hubipe\HuQrPayment;

use hubipe\HuQrPayment\Config\Configuration;
use hubipe\HuQrPayment\Config\ConfigurationInterface;
use hubipe\HuQrPayment\Enums\CharacterSet;
use hubipe\HuQrPayment\Enums\IdCode;
use hubipe\HuQrPayment\Enums\Purpose;
use hubipe\HuQrPayment\Exceptions\InvalidIbanException;
use hubipe\HuQrPayment\Exceptions\InvalidOptionException;
use hubipe\HuQrPayment\Helper\Utils;
use hubipe\HuQrPayment\Iban\IBAN;
use hubipe\HuQrPayment\Iban\IbanInterface;
use InvalidArgumentException;
use LogicException;
use Rikudou\QrPayment\QrPaymentInterface;
use Rikudou\QrPaymentQrCodeProvider\GetQrCodeTrait;

class QrPayment implements QrPaymentInterface
{
	use GetQrCodeTrait;

	/**
	 * @var string
	 */
	private $idCode = IdCode::TRANSFER_ORDER;

	/**
	 * @var int
	 */
	private $characterSet = CharacterSet::UTF_8;

	/**
	 * @var string
	 */
	private $bic = '';

	/**
	 * @var string
	 */
	private $name = '';

	/**
	 * @var IbanInterface
	 */
	private $iban;

	/**
	 * @var float
	 */
	private $amount = 0;

	/**
	 * @var string
	 */
	private $currency = 'HUF';

	/**
	 * @var \DateTimeImmutable
	 */
	private $dueDate;

	/**
	 * @var string|NULL
	 */
	private $paymentSituationIdentifier;

	/**
	 * @var string|NULL
	 */
	private $remittance;

	/**
	 * @var string|NULL
	 */
	private $shopId;

	/**
	 * @var string|NULL
	 */
	private $merchantDeviceId;

	/**
	 * @var string|NULL
	 */
	private $receiptId;

	/**
	 * @var string|NULL
	 */
	private $customerId;

	/**
	 * @var string|NULL
	 */
	private $payeeInternalId;

	/**
	 * @var string|NULL
	 */
	private $loyaltyId;

	/**
	 * @var string|NULL
	 */
	private $navVerificationCode;

	/**
	 * @var ConfigurationInterface
	 */
	private $configuration;

	/**
	 * @param string|IbanInterface $iban
	 */
	public function __construct($iban, ?ConfigurationInterface $configuration = NULL)
	{
		if (is_string($iban)) {
			$iban = new IBAN($iban);
		}
		if (!$iban instanceof IbanInterface) {
			throw new InvalidArgumentException('The IBAN must be a string or ' . IbanInterface::class . ', ' . Utils::getType($iban) . ' given');
		}
		if ($configuration === NULL) {
			$configuration = new Configuration();
		}
		$this->iban = $iban;
		$this->expiration = new \DateTimeImmutable('9999-12-31T23:59:59', new \DateTimeZone('Europe/Budapest'));
		$this->configuration = $configuration;
	}

	/**
	 * Returns the ID code of the QR code
	 * @see IdCode
	 * @return string
	 */
	public function getIdCode(): string
	{
		return $this->idCode;
	}

	/**
	 * Set the ID code
	 * @param string $idCode
	 * @return static
	 * @see IdCode
	 */
	public function setIdCode(string $idCode): self
	{
		if (!in_array($idCode, Utils::getConstants(IdCode::class))) {
			throw new InvalidArgumentException(sprintf('Invalid ID code "%s".', $idCode));
		}
		$this->idCode = $idCode;
		return $this;
	}

	/**
	 * Returns character set
	 * @return int
	 * @see CharacterSet
	 */
	public function getCharacterSet(): int
	{
		return $this->characterSet;
	}

	/**
	 * Set the character set
	 * @param int $characterSet
	 * @return static
	 * @see CharacterSet
	 */
	public function setCharacterSet(int $characterSet): self
	{
		if (!in_array($characterSet, Utils::getConstants(CharacterSet::class))) {
			throw new InvalidArgumentException("Invalid character set: {$characterSet}");
		}
		$this->characterSet = $characterSet;

		return $this;
	}

	public function getBic(): string
	{
		return $this->bic;
	}

	public function setBic(string $bic): self
	{
		$this->checkLength($bic, 11, 8);
		$this->checkCharacters($bic);
		$this->bic = $bic;

		return $this;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): self
	{
		$this->checkLength($name, 70);
		$this->checkCharacters($name);
		$this->name = $name;

		return $this;
	}

	public function getIban(): IbanInterface
	{
		return $this->iban;
	}

	public function getAmount(): float
	{
		return $this->amount;
	}

	public function setAmount(float $amount): self
	{
		if ($amount < 0) {
			throw new InvalidArgumentException('The amount cannot be less than 0');
		}
		$this->checkLength(number_format($amount, 0, '', ''), 12);
		$this->amount = $amount;

		return $this;
	}

	public function getCurrency(): string
	{
		return $this->currency;
	}

	public function setCurrency(string $currency)
	{
		if ($currency !== 'HUF') {
			throw new InvalidArgumentException(sprintf('Only supported currency is HUF, "%s" given.', $currency));
		}
		$this->currency = $currency;
		return $this;
	}


	public function getDueDate(): \DateTimeInterface
	{
		return $this->expiration;
	}

	/**
	 * @param \DateTimeInterface $dueDate
	 * @return static
	 */
	public function setDueDate(\DateTimeInterface $dueDate)
	{
		if ($dueDate instanceof \DateTime) {
			$dueDate = \DateTimeImmutable::createFromMutable($dueDate);
		}
		$this->expiration = $dueDate;
		return $this;
	}

	/**
	 * @return string|null
	 * @see Purpose
	 */
	public function getPaymentSituationIdentifier(): ?string
	{
		return $this->paymentSituationIdentifier;
	}

	/**
	 * @param string|null $paymentSituationIdentifier
	 * @see Purpose
	 */
	public function setPaymentSituationIdentifier(?string $paymentSituationIdentifier): self
	{
		if ($paymentSituationIdentifier !== NULL) {
			$this->checkLength($paymentSituationIdentifier, 4, 4);
			$this->checkCharacters($paymentSituationIdentifier);
		}
		$this->paymentSituationIdentifier = $paymentSituationIdentifier;
		return $this;
	}

	public function getRemittance(): ?string
	{
		return $this->remittance;
	}

	public function setRemittance(?string $remittance): self
	{
		if ($remittance !== NULL) {
			$this->checkLength($remittance, 70);
			$this->checkCharacters($remittance);
		}
		$this->remittance = $remittance;
		return $this;
	}

	public function getShopId(): ?string
	{
		return $this->shopId;
	}

	public function setShopId(?string $shopId): self
	{
		if ($shopId !== NULL) {
			$this->checkLength($shopId, 35);
			$this->checkCharacters($shopId);
		}
		$this->shopId = $shopId;
		return $this;
	}

	public function getMerchantDeviceId(): ?string
	{
		return $this->merchantDeviceId;
	}

	public function setMerchantDeviceId(?string $merchantDeviceId): self
	{
		if ($merchantDeviceId !== NULL) {
			$this->checkLength($merchantDeviceId, 35);
			$this->checkCharacters($merchantDeviceId);
		}
		$this->merchantDeviceId = $merchantDeviceId;
		return $this;
	}

	public function getReceiptId(): ?string
	{
		return $this->receiptId;
	}

	public function setReceiptId(?string $receiptId): self
	{
		if ($receiptId !== NULL) {
			$this->checkLength($receiptId, 35);
			$this->checkCharacters($receiptId);
		}
		$this->receiptId = $receiptId;
		return $this;
	}

	public function getCustomerId(): ?string
	{
		return $this->customerId;
	}

	public function setCustomerId(?string $customerId): self
	{
		if ($customerId !== NULL) {
			$this->checkLength($customerId, 35);
			$this->checkCharacters($customerId);
		}
		$this->customerId = $customerId;
		return $this;
	}

	public function getPayeeInternalId(): ?string
	{
		return $this->payeeInternalId;
	}

	public function setPayeeInternalId(?string $payeeInternalId): self
	{
		if ($payeeInternalId !== NULL) {
			$this->checkLength($payeeInternalId, 35);
			$this->checkCharacters($payeeInternalId);
		}
		$this->payeeInternalId = $payeeInternalId;
		return $this;
	}

	public function getLoyaltyId(): ?string
	{
		return $this->loyaltyId;
	}

	public function setLoyaltyId(?string $loyaltyId): self
	{
		if ($loyaltyId !== NULL) {
			$this->checkLength($loyaltyId, 35);
			$this->checkCharacters($loyaltyId);
		}
		$this->loyaltyId = $loyaltyId;
		return $this;
	}

	public function getNavVerificationCode(): ?string
	{
		return $this->navVerificationCode;
	}

	public function setNavVerificationCode(?string $navVerificationCode): self
	{
		if ($navVerificationCode !== NULL) {
			$this->checkLength($navVerificationCode, 35);
			$this->checkCharacters($navVerificationCode);
		}
		$this->navVerificationCode = $navVerificationCode;
		return $this;
	}


	public function getQrString(): string
	{
		$this->checkParameterValidity();

		if ($validator = $this->getIban()->getValidator()) {
			if (!$validator->isValid()) {
				throw new InvalidIbanException('The IBAN is not valid');
			}
		}

		if ($this->getAmount() > 0) {
			$amount = number_format($this->getAmount(), 0, '', '');
		}
		else {
			$amount = '';
		}

		$localTimeZone = new \DateTimeZone('Europe/Budapest');
		$dueDate = sprintf(
			'%s+%d',
			$this->expiration->setTimezone($localTimeZone)->format('YmdHis'),
			round($localTimeZone->getOffset($this->expiration) / 3600)
		);

		$result = [];
		$result[] = $this->getIdCode();
		$result[] = $this->configuration->getVersion();
		$result[] = $this->getCharacterSet();
		$result[] = $this->getBic();
		$result[] = $this->getName();
		$result[] = $this->getIban()->asString();
		$result[] = $amount !== '' ? $this->getCurrency() . $amount : '';
		$result[] = $dueDate;
		$result[] = $this->getPaymentSituationIdentifier();
		$result[] = $this->getRemittance();
		$result[] = $this->getShopId();
		$result[] = $this->getMerchantDeviceId();
		$result[] = $this->getReceiptId();
		$result[] = $this->getCustomerId();
		$result[] = $this->getPayeeInternalId();
		$result[] = $this->getLoyaltyId();
		$result[] = $this->getNavVerificationCode();

		$result = implode("\n", $result);

		$byteLength = strlen($result);

		if ($byteLength > 345) {
			throw new LogicException(sprintf(
				'The resulting QR string is limited to 345 bytes, yours has %d bytes',
				$byteLength
			));
		}

		return $result;
	}

	private function checkParameterValidity(): void
	{
		if ($this->getBic() === '') {
			throw new LogicException('Payer\'s or payee\'s BIC/BEI is a mandatory parameter.');
		}
		if ($this->getName() === '') {
			throw new LogicException('Payer\'s or payee\'s name is a mandatory parameter.');
		}
	}

	/**
	 * @param string $string
	 * @param int $max
	 * @param int $min
	 */
	private function checkLength(string $string, int $max, int $min = 0): void
	{
		$length = mb_strlen($string);
		if ($length > $max || $length < $min) {
			throw new InvalidArgumentException(sprintf(
				'The string should be between %d and %d characters long, your string contains %d characters.',
				$min,
				$max,
				$length
			));
		}
	}

	private function checkCharacters(string $string): void
	{
		if (strpos("\n", $string) !== FALSE) {
			throw new InvalidArgumentException(sprintf(
				'The string "%s" contains forbidden new line character.',
				$string
			));
		}
	}

	/**
	 * @param array<string, string|float|int> $options
	 *
	 * @return QrPayment
	 */
	public function setOptions(array $options)
	{
		foreach ($options as $key => $value) {
			$methodName = 'set' . ucfirst($key);
			if (method_exists($this, $methodName)) {
				/** @phpstan-ignore-next-line */
				call_user_func([$this, $methodName], $value);
			}
			else {
				throw new InvalidOptionException("The option '{$key}' is invalid");
			}
		}

		return $this;
	}

}
