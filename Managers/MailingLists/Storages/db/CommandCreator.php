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
class CommandCreator extends \Aurora\System\Db\AbstractCommandCreator
{
	/**
	 * Creates SQL-query to create mailing list.
	 * @param int $iTenantId Tenant identifier.
	 * @param int $sEmail Email of mailing list.
	 * @return string
	 */
	public function createMailingList($iTenantId, $sEmail)
	{
		$sSql = 'INSERT INTO awm_accounts ( id_tenant, email, mail_inc_login, mailing_list ) VALUES ( %d, %s, %s, 1 )';
		
		return sprintf($sSql,
			$iTenantId,
			$this->escapeString($sEmail),
			$this->escapeString($sEmail)
		);
	}
	
	/**
	 * Creates SQL-query to obtain all mailing lists for specified tenant.
	 * @param int $iTenantId Tenant identifier.
	 * @return string
	 */
	public function getMailingLists($iTenantId)
	{
		$sSql = 'SELECT id_acct, email FROM awm_accounts WHERE id_tenant = %d';
		
		return sprintf($sSql, $iTenantId);
	}
	
	/**
	 * Creates SQL-query to obtain mailing list with specified identifier.
	 * @param int $iListId List identifier.
	 * @return string
	 */
	public function getMailingListEmail($iListId)
	{
		$sSql = 'SELECT email FROM awm_accounts WHERE id_acct = %d';
		
		return sprintf($sSql, $iListId);
	}
	
	/**
	 * Creates SQL-query to delete mailing list.
	 * @param int $iListId Mailing list identifier.
	 * @return boolean
	 */
	public function deleteMailingList($iListId)
	{
		$sSql = 'DELETE FROM awm_accounts WHERE id_acct = %d';

		return sprintf($sSql,
			$iListId
		);
	}
	
	/**
	 * Creates SQL-query to obtain all mailing list members.
	 * @param int $iListId Mailing list identifier.
	 * @return string
	 */
	public function getMailingListMembers($iListId)
	{
		$sSql = 'SELECT list_name FROM awm_mailinglists WHERE id_acct = %d';
		
		return sprintf($sSql, $iListId);
	}
	
	/**
	 * Creates SQL-query to add new member to mailing list.
	 * @param int $iListId Mailing list identifier.
	 * @param string $sListName Email of mailing list.
	 * @param string $sListTo Email of the mailbox where messages should be sent.
	 * @return string
	 */
	public function addMember($iListId, $sListName, $sListTo)
	{
		$sSql = 'INSERT INTO awm_mailinglists ( id_acct, list_name, list_to ) VALUES ( %d, %s, %s )';
		
		return sprintf($sSql,
			$iListId,
			$this->escapeString($sListName),
			$this->escapeString($sListTo)
		);
	}

	/**
	 * Creates SQL-query to delete member from mailing list.
	 * @param int $iListId Mailing list identifier.
	 * @param string $sListName Email of the mailbox where messages should be sent.
	 * @return string
	 */
	public function deleteMember($iListId, $sListName)
	{
		$sSql = 'DELETE FROM awm_mailinglists WHERE id_acct = %d AND list_name = %s';

		return sprintf($sSql,
			$iListId,
			$this->escapeString($sListName)
		);
	}
}

class CommandCreatorMySQL extends CommandCreator
{
}
