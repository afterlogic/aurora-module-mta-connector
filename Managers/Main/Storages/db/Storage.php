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
	 * @return bool
	 */
	public function createAccount($sEmail, $sPassword, $iUserId, $iDomainId, $iQuota)
	{
		$bResult = false;
		if ($this->oConnection->Execute($this->oCommandCreator->createAccount($sEmail, $sPassword, $iUserId, $iDomainId, $iQuota)))
		{
			$AccountId = $this->oConnection->GetLastInsertId('awm_accounts', 'id_acct');
			$bResult = true;
		}

		$this->throwDbExceptionIfExist();
		return $bResult;
	}
	
	public function updateAccountPassword($sEmail, $sPassword, $sNewPassword)
	{
		$bResult = $this->oConnection->Execute($this->oCommandCreator->updateAccountPassword($sEmail, $sPassword, $sNewPassword));
		$this->throwDbExceptionIfExist();
		return $bResult;
	}	

	public function deleteAccount($sEmail)
	{
		$bResult = $this->oConnection->Execute($this->oCommandCreator->deleteAccountByEmail($sEmail));
		$this->throwDbExceptionIfExist();
		return $bResult;
	}

	public function getUserTotalQuotas($aUserIds)
	{
		$mResult = [];
		if ($this->oConnection->Execute($this->oCommandCreator->getUserTotalQuotas($aUserIds)))
		{
			while (false !== ($oRow = $this->oConnection->GetNextRecord()))
			{
				if ($oRow)
				{
					$mResult[$oRow->id_user] = $oRow->total_quota;
				}
			}
		}
		$this->throwDbExceptionIfExist();

		return $mResult;
	}

	public function updateUserTotalQuota($UserId, $iQuota)
	{
		$bResult = $this->oConnection->Execute($this->oCommandCreator->updateUserTotalQuota($UserId, $iQuota));
		$this->throwDbExceptionIfExist();
		return $bResult;
	}

	public function updateUserMailQuota($UserId, $iQuota)
	{
		$bResult = $this->oConnection->Execute($this->oCommandCreator->updateUserMailQuota($UserId, $iQuota));
		$this->throwDbExceptionIfExist();
		return $bResult;
	}

	public function getUserMailQuota($UserId)
	{
		$iMailQuota = 0;
		if ($this->oConnection->Execute($this->oCommandCreator->getUserMailQuota($UserId)))
		{
			$oRow = $this->oConnection->GetNextRecord();
			if ($oRow)
			{
				$iMailQuota = (int) $oRow->mail_quota;
			}
			$this->oConnection->FreeResult();
		}
		$this->throwDbExceptionIfExist();

		return $iMailQuota;
	}
}
