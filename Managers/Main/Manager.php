<?php
/**
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */

namespace Aurora\Modules\MtaConnector\Managers\Main;

/**
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2018, Afterlogic Corp.
 *
 * @package MtaConnector
 * @subpackage Managers
 */
class Manager extends \Aurora\System\Managers\AbstractManagerWithStorage
{
	/**
	 * @param \Aurora\System\Module\AbstractModule $oModule
	 */
	public function __construct(\Aurora\System\Module\AbstractModule $oModule = null)
	{
		parent::__construct($oModule, new \Aurora\Modules\MtaConnector\Managers\Main\Storages\db\Storage($this));
	}

	public function createAccount($sEmail, $sPassword, $iUserId, $iDomainId, $iQuota = 0)
	{
		return $this->oStorage->createAccount($sEmail, self::EncodePassword($sPassword), $iUserId, $iDomainId, $iQuota);
	}
	
	public function updateAccountPassword($sEmail, $sPassword, $sNewPassword)
	{
		return $this->oStorage->updateAccountPassword($sEmail, $sPassword, $sNewPassword);
	}	
	

	public function deleteAccount($sEmail)
	{
		return $this->oStorage->deleteAccount($sEmail);
	}

	public static function DecodePassword($sPassword)
	{
		$sResult = '';
		$iPasswordLen = strlen($sPassword);

		if (0 < $iPasswordLen && strlen($sPassword) % 2 == 0)
		{
			$sDecodeByte = chr(hexdec(substr($sPassword, 0, 2)));
			$sPlainBytes = $sDecodeByte;
			$iStartIndex = 2;
			$iCurrentByte = 1;

			do
			{
				$sHexByte = substr($sPassword, $iStartIndex, 2);
				$sPlainBytes .= (chr(hexdec($sHexByte)) ^ $sDecodeByte);

				$iStartIndex += 2;
				$iCurrentByte++;
			}
			while ($iStartIndex < $iPasswordLen);

			$sResult = $sPlainBytes;
		}

		// fix problem with 1-symbol password
		if ($iPasswordLen === 2 && $iPasswordLen === strlen($sResult))
		{
			$sResult = substr($sResult, 0,  1);
		}

		return $sResult;
	}

	public static function EncodePassword($sPassword)
	{
		if (empty($sPassword))
		{
			return '';
		}

		$sPlainBytes = $sPassword;
		$sEncodeByte = $sPlainBytes{0};
		$sResult = bin2hex($sEncodeByte);

		for ($iIndex = 1, $iLen = strlen($sPlainBytes); $iIndex < $iLen; $iIndex++)
		{
			$sPlainBytes{$iIndex} = ($sPlainBytes{$iIndex} ^ $sEncodeByte);
			$sResult .= bin2hex($sPlainBytes{$iIndex});
		}

		return $sResult;
	}
}
