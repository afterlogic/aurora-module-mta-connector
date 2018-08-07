<?php
/**
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */

namespace Aurora\Modules\MailSuite\Managers\Domains;

/**
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2018, Afterlogic Corp.
 *
 * @package MailSuite
 * @subpackage Managers
 */
class Manager extends \Aurora\System\Managers\AbstractManagerWithStorage
{
	/**
	 * @param \Aurora\System\Module\AbstractModule $oModule
	 */
	public function __construct(\Aurora\System\Module\AbstractModule $oModule = null)
	{
		parent::__construct($oModule, new \Aurora\Modules\MailSuite\Managers\Domains\Storages\db\Storage($this));
	}

	/**
	 * Creates domain.
	 * @param int $iTenantId Tenant identifier.
	 * @param int $sDomainName Domain name.
	 * @return boolean
	 */
	public function createDomain($iTenantId, $sDomainName)
	{
		return $this->oStorage->createDomain($iTenantId, $sDomainName);
	}
	
	/**
	 * Obtains all domains for specified tenant.
	 * @param int $iTenantId Tenant identifier.
	 * @return array|boolean
	 */
	public function getDomains($iTenantId)
	{
		return $this->oStorage->getDomains($iTenantId);
	}
	
	/**
	 * Obtains specified domain.
	 * @param int $iDomainId Domain identifier.
	 * @return array|boolean
	 */
	public function getDomain($iDomainId)
	{
		return $this->oStorage->getDomain($iDomainId);
	}
	
	/**
	 * Deletes domain.
	 * @param int $iDomainId domain identifier.
	 * @return boolean
	 */
	public function deleteDomain($iDomainId)
	{
		if ($this->oStorage->deleteDomainMembers($iDomainId))
		{
			return $this->oStorage->deleteDomain($iDomainId);
		}
		return false;
	}

}