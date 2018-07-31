<?php
/**
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */
namespace Aurora\Modules\MailSuite\Managers\MailingLists\Storages\db;
/**
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2018, Afterlogic Corp.
 *
 * @internal
 */
class Storage extends \Aurora\Modules\MailSuite\Managers\MailingLists\Storages\DefaultStorage
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
	 * Creates mailing list.
	 * @param int $iTenantId Tenant identifier.
	 * @param int $sEmail Email of mailing list.
	 * @return boolean
	 */
	public function createMailingList($iTenantId, $sEmail)
	{
		$mResult = $this->oConnection->Execute($this->oCommandCreator->createMailingList($iTenantId, $sEmail));

		$this->throwDbExceptionIfExist();
		
		return $mResult;
	}
	
	/**
	 * Obtains all mailing lists for specified tenant.
	 * @param int $iTenantId Tenant identifier.
	 * @return array|boolean
	 */
	public function getMailingLists($iTenantId)
	{
		$mResult = false;
		if ($this->oConnection->Execute($this->oCommandCreator->getMailingLists($iTenantId)))
		{
			$mResult = [];
			while (false !== ($oRow = $this->oConnection->GetNextRecord()))
			{
				if ($oRow)
				{
					$mResult[] = [
						'Id' => $oRow->id_acct,
						'Name' => $oRow->email,
						'Email' => $oRow->email
					];
				}
			}
		}
		
		$this->throwDbExceptionIfExist();
		
		return $mResult;
	}
	
	/**
	 * Obtains mailing list with specified identifier.
	 * @param int $iListId List identifier.
	 * @return string|boolean
	 */
	public function getMailingListEmail($iListId)
	{
		$mResult = false;
		if ($this->oConnection->Execute($this->oCommandCreator->getMailingListEmail($iListId)))
		{
			$oRow = $this->oConnection->GetNextRecord();
			if ($oRow)
			{
				$mResult = $oRow->email;
			}
		}
		
		$this->throwDbExceptionIfExist();
		
		return $mResult;
	}
	
	/**
	 * Deletes mailing list.
	 * @param int $iListId Mailing list identifier.
	 * @return boolean
	 */
	public function deleteMailingList($iListId)
	{
		$mResult = $this->oConnection->Execute($this->oCommandCreator->deleteMailingList($iListId));

		$this->throwDbExceptionIfExist();
		
		return $mResult;
	}
	
	/**
	 * Creates SQL-query to obtain all mailing list members.
	 * @param int $iListId Mailing list identifier.
	 * @return string
	 */
	public function getMailingListMembers($iListId)
	{
		$mResult = false;
		if ($this->oConnection->Execute($this->oCommandCreator->getMailingListMembers($iListId)))
		{
			$mResult = [];
			while (false !== ($oRow = $this->oConnection->GetNextRecord()))
			{
				if ($oRow)
				{
					$mResult[] = $oRow->list_name;
				}
			}
		}
		
		$this->throwDbExceptionIfExist();
		
		return $mResult;
	}
	
	/**
	 * Adds new member to mailing list.
	 * @param int $iListId Mailing list identifier.
	 * @param string $sListName Email of mailing list.
	 * @param string $sListTo Email of the mailbox where messages should be sent.
	 * @return boolean
	 */
	public function addMember($iListId, $sListName, $sListTo)
	{
		$mResult = $this->oConnection->Execute($this->oCommandCreator->addMember($iListId, $sListName, $sListTo));

		$this->throwDbExceptionIfExist();
		
		return $mResult;
	}
	
	/**
	 * Deletes member from mailing list.
	 * @param int $iListId Mailing list identifier.
	 * @param string $sListName Email of the mailbox where messages should be sent.
	 * @return boolean
	 */
	public function deleteMember($iListId, $sListName)
	{
		$mResult = $this->oConnection->Execute($this->oCommandCreator->deleteMember($iListId, $sListName));

		$this->throwDbExceptionIfExist();
		
		return $mResult;
	}
}
