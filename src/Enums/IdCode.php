<?php

namespace hubipe\HuQrPayment\Enums;

/**
 * Supported ID codes
 */
class IdCode
{

	/**
	 * Submission of the credit transfer order, i.e. the payee generates the QR code to enable the payer to submit
	 * the credit transfer order
	 */
	public const TRANSFER_ORDER = 'HCT';

	/**
	 * Transmission of the request to pay, i.e. the payer generates the QR code to transfer his main data to the payee
	 */
	public const PAYMENT_REQUEST = 'RTP';

}
