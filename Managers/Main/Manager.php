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

	public function createAccount($sEmail, $sPassword, $iUserId, $iDomainId)
	{
		return $this->oStorage->createAccount($sEmail, self::EncodePassword($sPassword), $iUserId, $iDomainId);
	}
	
	public function updateAccountPassword($sEmail, $sPassword, $sNewPassword)
	{
		return $this->oStorage->updateAccountPassword($sEmail, $sPassword, self::EncodePassword($sNewPassword));
	}	
	

	public function deleteAccount($sEmail)
	{
		return $this->oStorage->deleteAccount($sEmail);
	}

	/**
	 * Creates tables required for module work by executing create.sql file.
	 *
	 * @return boolean
	 */
	public function createTablesFromFile()
	{
		$sFilePath = dirname(__FILE__) . '/Storages/db/Sql/create.sql';
		$bResult = \Aurora\System\Managers\Db::getInstance()->executeSqlFile($sFilePath);
		
		return $bResult;
	}

	/**
	 * Update user mail quota
	 *
	 * @param int $UserId
	 * @param int $iQuotaKb
	 * @return bool
	 */
	public function updateUserMailQuota($UserId, $iQuotaKb)
	{
		return $this->oStorage->updateUserMailQuota($UserId, $iQuotaKb);
	}

	/**
	 * Return user mail quota in KB
	 *
	 * @param $UserId
	 * @return int
	 */
	public function getUserMailQuota($UserId)
	{
		return $this->oStorage->getUserMailQuota($UserId);
	}

	/**
	 * Return user mail quota usage in Bytes
	 *
	 * @param $UserId
	 * @return int
	 */
	public function getUserMailQuotaUsage($UserId)
	{
		return $this->oStorage->getUserMailQuotaUsage($UserId);
	}

	public static function EncodePassword($sPassword)
	{
		if (empty($sPassword))
		{
			return '';
		}
		$salt = substr(sha1(rand()), 0, 16);
		$sResult = hash('sha256', $sPassword . hex2bin($salt)) . $salt;

		return $sResult;
	}

	/**
	 * Return Account with specified email
	 *
	 * @param $sAccountEmail
	 * @return array|bool
	 */
	public function getAccountByEmail($sAccountEmail)
	{
		return $this->oStorage->getAccountByEmail(\trim($sAccountEmail));
	}
}
