<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MtaConnector\Managers;

use Aurora\Modules\MtaConnector\Models\Account;
use Aurora\Modules\MtaConnector\Models\MailingList;
use Aurora\Modules\MtaConnector\Models\MailingListMember;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2023, Afterlogic Corp.
 *
 * @property Module $oModule
 */
class MailingLists extends \Aurora\System\Managers\AbstractManager
{
    /**
     * Creates mailing list.
     * @param int $iDomainId Domain identifier.
     * @param string $sEmail Email of mailing list.
     * @return boolean
     */
    public function createMailingList($iDomainId, $sEmail)
    {
        return !!MailingList::create([
            'id_domain' => $iDomainId,
            'name' => $sEmail
        ]);
    }

    /**
     * Obtains mailing list with specified identifier.
     * @param int $iListId List identifier.
     * @return string|boolean
     */
    public function getMailingListEmail($iListId)
    {
        $list = MailingList::firstWhere('id', $iListId);
        if ($list) {
            $result = $list->name;
        }

        return $result;
    }

    /**
     * Obtains mailing lists with specified parameters.
     * @param int $iTenantId Tenant identifier.
     * @param int $iDomainId Domain identifier.
     * @param string $sSearch Search.
     * @param int $iOffset Offset.
     * @param int $iLimit Limit.
     * @param bool $bCount Count.
     * @return array|int|boolean
     */
    public function getMailingLists($iTenantId = 0, $iDomainId = 0, $sSearch = '', $iOffset = 0, $iLimit = 0, $bCount = false)
    {
        $query = MailingList::query();

        if ($iDomainId !== 0) {
            $query = $query->where('awm_mailinglists.id_domain', $iDomainId);
        }
        if ($sSearch !== '') {
            $query = $query->where('awm_mailinglists.name', 'LIKE', '%' . $sSearch . '%');
        }

        $query = $query->leftJoin('awm_domains', 'awm_domains.id_domain', '=', 'awm_mailinglists.id_domain')
            ->where('awm_domains.id_tenant', $iTenantId);

        if ($iLimit > 0) {
            $query = $query->offset($iOffset)->limit($iLimit);
        }

        if ($bCount) {
            return $query->get()->count();
        }

        $result = [];
        $items = $query->get(['awm_mailinglists.id', 'awm_mailinglists.name'])->all();
        foreach ($items as $item) {
            $result[] = [
                'Id' => $item->id,
                'Name' => $item->name,
                'Email' => $item->name
            ];
        }

        return $result;
    }

    /**
     * Obtains count of mailing lists with specified parameters.
     * @param int $iTenantId Tenant identifier.
     * @param int $iDomainId Domain identifier.
     * @param string $sSearch Search.
     * @return int
     */
    public function getMailingListsCount($iTenantId = 0, $iDomainId = 0, $sSearch = '')
    {
        return $this->getMailingLists($iTenantId, $iDomainId, $sSearch, 0, 0, true);
    }

    /**
     * Deletes mailing list.
     * @param array $ListIds Array of Mailing list identifiers.
     * @return boolean
     */
    public function deleteMailingLists($ListIds)
    {
        if (is_array($ListIds) && count($ListIds) > 0) {
            MailingListMember::whereIn('id_mailinglist', $ListIds)->delete();
            return !!MailingList::where('id', $ListIds)->delete();
        } else {
            return false;
        }
    }

    /**
     * Obtains all mailing list members.
     * @param int $iListId Mailing list identifier.
     * @return array|boolean
     */
    public function getMembers($iListId)
    {
        return MailingListMember::where('id_mailinglist', $iListId)->pluck('list_to')->toArray();
    }

    /**
     * Obtains mailing list member.
     * @param int $iListId Mailing list identifier.
     * @return MailingListMember|boolean
     */
    public function getMember($iListId, $sListTo)
    {
        return MailingListMember::where('id_mailinglist', $iListId)->where('list_to', $sListTo)->first();
    }

    /**
     * Adds new member to mailing list.
     * @param int $iListId Mailing list identifier.
     * @param string $sListTo Email of the mailbox where messages should be sent.
     * @return boolean
     */
    public function addMember($iListId, $sListTo)
    {
        return !!MailingListMember::create([
            'id_mailinglist' => $iListId,
            'list_to' => $sListTo
        ]);
    }

    /**
     * Deletes member from mailing list.
     * @param int $iListId Mailing list identifier.
     * @param string $sListName Email of the mailbox where messages should be sent.
     * @return boolean
     */
    public function deleteMember($iListId, $sListName)
    {
        return !!MailingListMember::where('id_mailinglist', $iListId)->where('list_to', $sListName)->delete();
    }

    /**
     * Obtains mailing list ID with specified email
     * @param string $sEmail email.
     * @return int|boolean
     */
    public function getMailingListIdByEmail($sEmail)
    {
        $list = MailingList::firstWhere('name', $sEmail);
        if ($list) {
            return $list->id;
        }

        return false;
    }
}
