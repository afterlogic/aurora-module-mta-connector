<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MtaConnector\Managers\Aliases\Storages\db;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2019, Afterlogic Corp.
 */
class CommandCreator extends \Aurora\System\Db\AbstractCommandCreator
{
	/**
	 * Creates SQL-query to obtain all aliases for specified mail account.
	 * @param int $iAccountId Account identifier.
	 * @return string
	 */
	public function getAliases($iAccountId)
	{
		$sSql = 'SELECT alias_name, alias_domain, alias_to FROM awm_mailaliases WHERE id_acct = %d';
		
		return sprintf($sSql, $iAccountId);
	}
	
	/**
	 * Creates SQL-query to add new alias with specified name and domain.
	 * @param int $iAccountId Account identifier.
	 * @param string $sName Alias name.
	 * @param string $sDomain Alias domain.
	 * @param string $sToEmail Email of the mailbox where messages should be sent.
	 * @return string
	 */
	public function addAlias($iAccountId, $sName, $sDomain, $sToEmail)
	{
		$sSql = 'INSERT INTO awm_mailaliases ( id_acct, alias_name, alias_domain, alias_to ) VALUES ( %d, %s, %s, %s )';
		
		return sprintf($sSql,
			$iAccountId,
			$this->escapeString($sName),
			$this->escapeString($sDomain),
			$this->escapeString($sToEmail)
		);
	}

	/**
	 * Creates SQL-query to delete alias with specified name and domain.
	 * @param int $iAccountId Account identifier
	 * @param string $sName Alias name.
	 * @param string $sDomain Alias domain.
	 * @return string
	 */
	public function deleteAlias($iAccountId, $sName, $sDomain)
	{
		$sSql = 'DELETE FROM awm_mailaliases WHERE id_acct = %d AND alias_name = %s AND alias_domain = %s';

		return sprintf($sSql,
			$iAccountId,
			$this->escapeString($sName),
			$this->escapeString($sDomain)
		);
	}
	
	/**
	 * Creates SQL-query to delete all aliases for specified account.
	 * @param int $iAccountId Account identifier.
	 * @return string
	 */
	public function deleteAliases($iAccountId)
	{
		$sSql = 'DELETE FROM awm_mailaliases WHERE id_acct = %d';

		return sprintf($sSql,
			$iAccountId
		);
	}
}

class CommandCreatorMySQL extends CommandCreator
{
}
