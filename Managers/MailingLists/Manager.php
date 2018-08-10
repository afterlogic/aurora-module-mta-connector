<?php
/**
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */

namespace Aurora\Modules\MtaConnector\Managers\MailingLists;

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
		parent::__construct($oModule, new \Aurora\Modules\MtaConnector\Managers\MailingLists\Storages\db\Storage($this));
	}

	/**
	 * Creates mailing list.
	 * @param int $iTenantId Tenant identifier.
	 * @param int $iDomainId Domain identifier.
	 * @param string $sEmail Email of mailing list.
	 * @return boolean
	 */
	public function createMailingList($iTenantId, $iDomainId, $sEmail)
	{
		return $this->oStorage->createMailingList($iTenantId, $iDomainId, $sEmail);
	}
	
	/**
	 * Obtains mailing list with specified identifier.
	 * @param int $iListId List identifier.
	 * @return string|boolean
	 */
	public function getMailingListEmail($iListId)
	{
		return $this->oStorage->getMailingListEmail($iListId);
	}
	
	/**
	 * Obtains all mailing lists for specified tenant.
	 * @param int $iTenantId Tenant identifier.
	 * @return array|boolean
	 */
	public function getMailingLists($iTenantId)
	{
		return $this->oStorage->getMailingLists($iTenantId);
	}
	
	/**
	 * Deletes mailing list.
	 * @param int $iListId Mailing list identifier.
	 * @return boolean
	 */
	public function deleteMailingList($iListId)
	{
		if ($this->oStorage->deleteMailingListMembers($iListId))
		{
			return $this->oStorage->deleteMailingList($iListId);
		}
		return false;
	}
	
	/**
	 * Obtains all mailing list members.
	 * @param int $iListId Mailing list identifier.
	 * @return array|boolean
	 */
	public function getMailingListMembers($iListId)
	{
		return $this->oStorage->getMailingListMembers($iListId);
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
		return $this->oStorage->addMember($iListId, $sListName, $sListTo);
	}
	
	/**
	 * Deletes member from mailing list.
	 * @param int $iListId Mailing list identifier.
	 * @param string $sListName Email of the mailbox where messages should be sent.
	 * @return boolean
	 */
	public function deleteMember($iListId, $sListName)
	{
		return $this->oStorage->deleteMember($iListId, $sListName);
	}
}
