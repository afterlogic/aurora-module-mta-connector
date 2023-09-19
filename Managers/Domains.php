<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MtaConnector\Managers;

use Aurora\Modules\MtaConnector\Models\Domain;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2023, Afterlogic Corp.
 *
 * @property Module $oModule
 */
class Domains extends \Aurora\System\Managers\AbstractManager
{
    /**
     * Creates domain.
     * @param int $iDomainId Domain identifier.
     * @param int $iTenantId Tenant identifier.
     * @param int $sDomainName Domain name.
     * @return boolean
     */
    public function createDomain($iDomainId, $iTenantId, $sDomainName)
    {
        if (mb_detect_encoding($sDomainName) != "ASCII") {
            $sDomainName = \MailSo\Base\Utils::idn()->encode($sDomainName);
        }

        if ($this->getDomainByName($sDomainName)) {
            throw new \Aurora\Modules\MailDomains\Exceptions\Exception(\Aurora\Modules\MailDomains\Enums\ErrorCodes::DomainExists);
        }
        return !!Domain::create([
            'id_domain' => $iDomainId,
            'id_tenant' => $iTenantId,
            'name' => $sDomainName,
        ]);
    }

    /**
     * Obtains specified domain.
     * @param int $iDomainId Domain identifier.
     * @return array|boolean
     */
    public function getDomain($iDomainId)
    {
        $result = false;
        $domain = Domain::firstWhere('id_domain', $iDomainId);
        if ($domain) {
            $result = [
                'Id' => $domain->id_domain,
                'TenantId' => $domain->id_tenant,
                'Name' => $domain->name
            ];
        }

        return $result;
    }

    /**
     * Deletes domain.
     * @param int $iDomainId domain identifier.
     * @return boolean
     */
    public function deleteDomain($iDomainId)
    {
        return !!Domain::where('id_domain', $iDomainId)->delete();
    }

    /**
     * Get domain member emails list.
     * @param int $iDomainId domain identifier.
     * @return boolean
     */
    public function getDomainMembers($iDomainId)
    {
        $domains = Domain::where('id_domain', $iDomainId)->get(['id_user', 'email'])->all();

        $result = [];
        foreach ($domains as $domain) {
            $result[] = [
                'UserId' => $domain->id_user,
                'Email' => $domain->email
            ];
        }

        return $result;
    }

    /**
     * Obtains specified domain.
     * @param string $sDomainName Domain name.
     * @return array|boolean
     */
    public function getDomainByName($sDomainName)
    {
        if (mb_detect_encoding($sDomainName) != "ASCII") {
            $sDomainName = \MailSo\Base\Utils::idn()->encode($sDomainName);
        }

        $domain = Domain::firstWhere('name', $sDomainName);
        if ($domain) {
            return [
                'Id' => $domain->id_domain,
                'TenantId' => $domain->id_tenant,
                'Name' => $domain->name
            ];
        }

        return false;
    }
}
