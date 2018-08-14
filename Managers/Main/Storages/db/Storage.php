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
 * @package Helpdesk
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
	 * TODO remove
	 * @param string $sSql
	 *
	 * @return CHelpdeskUser|false
	 */
	protected function _getUserBySql($sSql)
	{
		$oUser = false;
		if ($this->oConnection->Execute($sSql))
		{
			$oUser = null;

			$oRow = $this->oConnection->GetNextRecord();
			if ($oRow)
			{
				$oUser = new CHelpdeskUser();
				$oUser->InitByDbRow($oRow);
			}

			$this->oConnection->FreeResult();
		}

		$this->throwDbExceptionIfExist();
		return $oUser;
	}

	/**
	 * TODO remove
	 * @param CHelpdeskUser $oHelpdeskUser
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

	/**
	 * TODO use Core modue API and remove this this method
	 * @param int $iIdTenant
	 * @param int $iHelpdeskUserId
	 *
	 * @return CHelpdeskUser|false
	 */
	public function getUserById($iIdTenant, $iHelpdeskUserId)
	{
		return $this->_getUserBySql($this->oCommandCreator->getUserById($iIdTenant, $iHelpdeskUserId));
	}

	public function createProceduresFromFile($sFilePath)
	{
		$mFileContent = file_exists($sFilePath) ? file_get_contents($sFilePath) : false;

		if ($mFileContent && $this->oConnection)
		{
			$sPrepSql = trim($mFileContent);
			if (!empty($sPrepSql))
			{
				$Settings = \Aurora\System\Api::GetSettings();

				$sql = new \Aurora\System\Db\MySql($Settings->GetConf('DBHost'),
					$Settings->GetConf('DBLogin'),
					$Settings->GetConf('DBPassword'),
					$Settings->GetConf('DBName'));
				$sql->Connect();
				$sql->Execute($sPrepSql);
			}
		}
	}
}
