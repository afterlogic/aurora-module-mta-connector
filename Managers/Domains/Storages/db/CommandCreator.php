<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MtaConnector\Managers\Domains\Storages\db;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2023, Afterlogic Corp.
 */
class CommandCreator extends \Aurora\System\Db\AbstractCommandCreator
{
	/**
	 * Creates SQL-query to create domain.
	 * @param int $iDomainId Domain identifier.
	 * @param int $iTenantId Tenant identifier.
	 * @param int $sDomainName Domain name.
	 * @return string
	 */
	public function createDomain($iDomainId, $iTenantId, $sDomainName)
	{
		$sSql = 'INSERT INTO awm_domains ( id_domain, id_tenant, name ) VALUES ( %d, %d, %s )';
		
		return sprintf($sSql,
			$iDomainId,
			$iTenantId,
			$this->escapeString($sDomainName)
		);
	}
	
	/**
	 * Creates SQL-query to obtain all domains for specified tenant.
	 * @param int $iTenantId Tenant identifier.
	 * @return string
	 */
	public function getDomains($iTenantId)
	{
		$sSql = 'SELECT
				awm_domains.id_domain,
				awm_domains.id_tenant,
				awm_domains.name,
				COUNT(awm_accounts.id_acct) AS count
			FROM awm_domains
			LEFT JOIN awm_accounts ON awm_accounts.id_domain = awm_domains.id_domain AND awm_accounts.mailing_list = 0
			WHERE awm_domains.id_tenant = %d
			GROUP BY awm_domains.id_domain';

		return sprintf($sSql, $iTenantId);
	}
	
	/**
	 * Creates SQL-query to obtain domain with specified identifier.
	 * @param int $iDomainId Domain identifier.
	 * @return string
	 */
	public function getDomain($iDomainId)
	{
		$sSql = 'SELECT
				awm_domains.name,
				COUNT(awm_accounts.id_acct) AS count
			FROM awm_domains
			LEFT JOIN awm_accounts ON awm_accounts.id_domain = awm_domains.id_domain AND awm_accounts.mailing_list = 0
			WHERE awm_domains.id_domain = %d
			GROUP BY awm_domains.id_domain';
		
		return sprintf($sSql, $iDomainId);
	}
	
	/**
	 * Creates SQL-query to delete domain.
	 * @param int $iDomainId domain identifier.
	 * @return boolean
	 */
	public function deleteDomain($iDomainId)
	{
		$sSql = 'DELETE FROM awm_domains WHERE id_domain = %d';

		return sprintf($sSql,
			$iDomainId
		);
	}

	public function getDomainMembers($iDomainId)
	{
		$sSql = 'SELECT id_user, email
				FROM awm_accounts
				WHERE id_domain = %d';

		return sprintf($sSql, (int) $iDomainId);
	}

	/**
	 * Creates SQL-query to obtain domain with specified name.
	 * @param string $sDomainName Domain name.
	 * @return string
	 */
	public function getDomainByName($sDomainName)
	{
		$sSql = 'SELECT
				awm_domains.id_domain,
				awm_domains.id_tenant,
				awm_domains.name
			FROM awm_domains
			WHERE awm_domains.name = %s';

		return sprintf($sSql, $this->escapeString($sDomainName));
	}
}

class CommandCreatorMySQL extends CommandCreator
{
}
