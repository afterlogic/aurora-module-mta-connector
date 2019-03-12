<?php
/**
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */

namespace Aurora\Modules\MtaConnector\Managers\Domains;

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
		parent::__construct($oModule, new \Aurora\Modules\MtaConnector\Managers\Domains\Storages\db\Storage($this));
	}

	/**
	 * Creates domain.
	 * @param int $iDomainId Domain identifier.
	 * @param int $iTenantId Tenant identifier.
	 * @param int $sDomainName Domain name.
	 * @return boolean
	 */
	public function createDomain($iDomainId, $iTenantId, $sDomainName)
	{
		if ($this->getDomainByName($sDomainName))
		{
			throw new \Aurora\Modules\MailDomains\Exceptions\Exception(\Aurora\Modules\MailDomains\Enums\ErrorCodes::DomainExists);
		}
		return $this->oStorage->createDomain($iDomainId, $iTenantId, $sDomainName);
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
		return $this->oStorage->deleteDomain($iDomainId);
	}

	/**
	 * Get domain member emails list.
	 * @param int $iDomainId domain identifier.
	 * @return boolean
	 */
	public function getDomainMembers($iDomainId)
	{
		return $this->oStorage->getDomainMembers($iDomainId);
	}

	/**
	 * Obtains specified domain.
	 * @param string $sDomainName Domain name.
	 * @return array|boolean
	 */
	public function getDomainByName($sDomainName)
	{
		return $this->oStorage->getDomainByName($sDomainName);
	}
}
