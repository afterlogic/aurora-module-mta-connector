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
	 * Return array of users quota
	 *
	 * @param array $aUserIds
	 * @return array
	 */
	public function getUserTotalQuotas($aUserIds)
	{
		return $this->oStorage->getUserTotalQuotas($aUserIds);
	}

	/**
	 * Update user total quota
	 *
	 * @param int $UserId
	 * @param int $iQuota
	 * @return bool
	 */
	public function updateUserTotalQuota($UserId, $iQuota)
	{
		return $this->oStorage->updateUserTotalQuota($UserId, $iQuota);
	}

	/**
	 * Update user mail quota
	 *
	 * @param int $UserId
	 * @param int $iQuota
	 * @return bool
	 */
	public function updateUserMailQuota($UserId, $iQuota)
	{
		return $this->oStorage->updateUserMailQuota($UserId, $iQuota);
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
}
