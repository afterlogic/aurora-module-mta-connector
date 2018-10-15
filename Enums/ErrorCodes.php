<?php
/*
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MtaConnector\Enums;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2018, Afterlogic Corp.
 */
class ErrorCodes
{
	const Validation_InvalidParameters	= 1001;
	const DomainExists				= 1002;

	/**
	 * @var array
	 */
	protected $aConsts = [
		'Validation_InvalidParameters'	=> self::Validation_InvalidParameters,
		'DomainExists'				=> self::DomainExists
	];
}
