<?php
/**
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */
namespace Aurora\Modules\MtaConnector\Managers\MailingLists\Storages\db;
/**
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2019, Afterlogic Corp.
 *
 * @internal
 */
class CommandCreator extends \Aurora\System\Db\AbstractCommandCreator
{
	/**
	 * Creates SQL-query to create mailing list.
	 * @param int $iDomainId Domain identifier.
	 * @param string $sEmail Email of mailing list.
	 * @return string
	 */
	public function createMailingList($iDomainId, $sEmail)
	{
		$sSql = 'INSERT INTO awm_accounts ( id_domain, email, mailing_list ) VALUES ( %d, %s, 1 )';
		
		return sprintf($sSql,
			$iDomainId,
			$this->escapeString($sEmail)
		);
	}
	
	/**
	 * Creates SQL-query to obtain mailing lists with specified parameters.
	 * @param int $iTenantId Tenant identifier.
	 * @param int $iDomainId Domain identifier.
	 * @param string $sSearch Search.
	 * @param int $iOffset Offset.
	 * @param int $iLimit Limit.
	 * @return string
	 */
	public function getMailingLists($iTenantId = 0, $iDomainId = 0, $sSearch = '', $iOffset = 0, $iLimit = 0)
	{
		$sWhere = '';
		if ($iDomainId !== 0)
		{
			$sWhere .= sprintf(' AND awm_accounts.id_domain = %d', $iDomainId);
		}
		if ($sSearch !== '')
		{
			$sWhere .= sprintf(' AND awm_accounts.email LIKE %s', '\'%' . $this->escapeString($sSearch, true, true) . '%\'');
		}

		$sSql = sprintf('SELECT
				awm_accounts.id_acct,
				awm_accounts.email
			FROM awm_accounts
			LEFT JOIN awm_domains ON awm_domains.id_domain = awm_accounts.id_domain
			WHERE awm_accounts.mailing_list = 1 AND awm_domains.id_tenant = %d', $iTenantId)
			. $sWhere
			. ($iLimit > 0 ? sprintf(' LIMIT %d OFFSET %d', $iLimit, $iOffset) : '');

		return $sSql;
	}
	
	/**
	 * Creates SQL-query to obtain count of mailing lists with specified parameters.
	 * @param int $iTenantId Tenant identifier.
	 * @param int $iDomainId Domain identifier.
	 * @param string $sSearch Search.
	 * @return string
	 */
	public function getMailingListsCount($iTenantId = 0, $iDomainId = 0, $sSearch = '')
	{
		$sWhere = '';
		if ($iDomainId !== 0)
		{
			$sWhere .= sprintf(' AND awm_accounts.id_domain = %d', $iDomainId);
		}
		if ($sSearch !== '')
		{
			$sWhere .= sprintf(' AND awm_accounts.email LIKE %s', '\'%' . $this->escapeString($sSearch, true, true) . '%\'');
		}

		$sSql = sprintf('SELECT COUNT(awm_accounts.id_acct) as count
			FROM awm_accounts
			LEFT JOIN awm_domains ON awm_domains.id_domain = awm_accounts.id_domain
			WHERE awm_accounts.mailing_list = 1 AND awm_domains.id_tenant = %d', $iTenantId) . $sWhere;

		return $sSql;
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
	 * Creates SQL-query to delete mailing list members.
	 * @param int $iListId Mailing list identifier.
	 * @return boolean
	 */
	public function deleteMailingListMembers($iListId)
	{
		$sSql = 'DELETE FROM awm_mailinglists WHERE id_acct = %d';

		return sprintf($sSql,
			$iListId
		);
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
		$sSql = 'SELECT list_to FROM awm_mailinglists WHERE id_acct = %d';
		
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
		$sSql = 'DELETE FROM awm_mailinglists WHERE id_acct = %d AND list_to = %s';

		return sprintf($sSql,
			$iListId,
			$this->escapeString($sListName)
		);
	}

	/**
	 * Creates SQL-query to obtain mailing list ID with specified email.
	 * @param string $sEmail email.
	 * @return string
	 */
	public function getMailingListIdByEmail($sEmail)
	{
		$sSql = 'SELECT id_acct FROM `awm_accounts` WHERE email = %s AND mailing_list = 1';

		return sprintf($sSql, $this->escapeString($sEmail));
	}
}

class CommandCreatorMySQL extends CommandCreator
{
}
