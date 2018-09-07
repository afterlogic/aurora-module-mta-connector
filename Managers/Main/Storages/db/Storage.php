<?php
/**
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */
namespace Aurora\Modules\MtaConnector\Managers\Main\Storages\db;
/**
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2018, Afterlogic Corp.
 *
 * @internal
 * 
 * @package MtaConnector
 * @subpackage Storages
 */
class Storage extends \Aurora\Modules\MtaConnector\Managers\Main\Storages\DefaultStorage
{
	protected $oConnection;
	protected $oCommandCreator;

	/**
	 * 
	 * @param \Aurora\System\Managers\AbstractManager $oManager
	 */
	public function __construct(\Aurora\System\Managers\AbstractManager &$oManager)
	{
		parent::__construct($oManager);

		$this->oConnection =& $oManager->GetConnection();
		$this->oCommandCreator = new CommandCreator();
	}

	/**
	 *
	 * @return bool|int
	 */
	public function createAccount($sEmail, $sPassword, $iUserId, $iDomainId)
	{
		$mResult = false;
		if ($this->oConnection->Execute($this->oCommandCreator->createAccount($sEmail, $sPassword, $iUserId, $iDomainId)))
		{
			$AccountId = $this->oConnection->GetLastInsertId('awm_accounts', 'id_acct');
			$mResult = $AccountId;
		}

		$this->throwDbExceptionIfExist();
		return $mResult;
	}
	
	public function updateAccountPassword($sEmail, $sPassword, $sNewPassword)
	{
		$bResult = $this->oConnection->Execute($this->oCommandCreator->updateAccountPassword($sEmail, $sPassword, $sNewPassword));
		$this->throwDbExceptionIfExist();
		return $bResult;
	}	

	public function deleteAccount($sEmail)
	{
		$bResult = $this->oConnection->Execute($this->oCommandCreator->deleteAccountByEmail($sEmail)) &&
			$this->oConnection->Execute($this->oCommandCreator->deleteAccountQuotaUsageByEmail($sEmail));
		$this->throwDbExceptionIfExist();
		return $bResult;
	}

	/**
	 *
	 * @param int $UserId
	 * @param int $iQuotaKb
	 * @return boolean
	 */
	public function updateUserMailQuota($UserId, $iQuotaKb)
	{
		$bResult = $this->oConnection->Execute($this->oCommandCreator->updateUserMailQuota($UserId, $iQuotaKb));
		$this->throwDbExceptionIfExist();
		return $bResult;
	}

	/**
	 * Return user mail quota in KB
	 *
	 * @param int $UserId
	 * @return int
	 */
	public function getUserMailQuota($UserId)
	{
		$iMailQuotaKB = 0;
		if ($this->oConnection->Execute($this->oCommandCreator->getUserMailQuota($UserId)))
		{
			$oRow = $this->oConnection->GetNextRecord();
			if ($oRow)
			{
				$iMailQuotaKB = (int) $oRow->mail_quota_kb;
			}
			$this->oConnection->FreeResult();
		}
		$this->throwDbExceptionIfExist();

		return $iMailQuotaKB;
	}

	/**
	 * Return user mail quota usage in bytes
	 *
	 * @param int $UserId
	 * @return int
	 */
	public function getUserMailQuotaUsage($UserId)
	{
		$iMailQuotaUsageBytes = 0;
		if ($this->oConnection->Execute($this->oCommandCreator->getUserMailQuotaUsage($UserId)))
		{
			$oRow = $this->oConnection->GetNextRecord();
			if ($oRow)
			{
				$iMailQuotaUsageBytes = (int) $oRow->mail_quota_usage_bytes;
			}
			$this->oConnection->FreeResult();
		}
		$this->throwDbExceptionIfExist();

		return $iMailQuotaUsageBytes;
	}
}
