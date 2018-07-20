<?php
/**
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */
namespace Aurora\Modules\MailSuite\Managers\Main\Storages\db;
/**
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2018, Afterlogic Corp.
 *
 * @internal
 * 
 * @package Helpdesk
 * @subpackage Storages
 */
class Storage extends \Aurora\Modules\MailSuite\Managers\Main\Storages\DefaultStorage
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
	public function createAccount($sEmail, $sPassword, $iQuota)
	{
		$bResult = false;
		if ($this->oConnection->Execute($this->oCommandCreator->createAccount($sEmail, $sPassword, $iQuota)))
		{
			$AccountId = $this->oConnection->GetLastInsertId('awm_accounts', 'id_acct');
			$bResult = true;
		}

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
}