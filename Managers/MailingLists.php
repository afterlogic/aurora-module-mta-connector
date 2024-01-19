<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MtaConnector\Managers;

use Aurora\Modules\MtaConnector\Models\Account;
use Aurora\Modules\MtaConnector\Models\MailingList;

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
        return !!Account::create([
            'id_domain' => $iDomainId,
            'email' => $sEmail,
            'mailing_list' => true
        ]);
    }

    /**
     * Obtains mailing list with specified identifier.
     * @param int $iListId List identifier.
     * @return string|boolean
     */
    public function getMailingListEmail($iListId)
    {
        $account = Account::firstWhere('id_acct', $iListId);
        if ($account) {
            $result = $account->email;
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
        $query = Account::query();

        if ($iDomainId !== 0) {
            $query = $query->where('id_domain', $iDomainId);
        }
        if ($sSearch !== '') {
            $query = $query->where('email', 'LIKE', '%' . $sSearch . '%');
        }

        $query = $query->leftJoin('awm_domains', 'awm_domains.id_domain', '=', 'awm_accounts.id_domain')
            ->where('awm_accounts.mailing_list', true)
            ->where('awm_domains.id_tenant', $iTenantId);

        if ($iLimit > 0) {
            $query = $query->offset($iOffset)->limit($iLimit);
        }

        if ($bCount) {
            return $query->get()->count();
        }

        $result = [];
        $items = $query->get(['id_acct', 'email'])->all();
        foreach ($items as $item) {
            $result[] = [
                'Id' => $item->id_acct,
                'Name' => $item->email,
                'Email' => $item->email
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
     * @param int $iListId Mailing list identifier.
     * @return boolean
     */
    public function deleteMailingList($iListId)
    {
        MailingList::where('id_acct', $iListId)->delete();
        return !!Account::where('id_acct', $iListId)->delete();
    }

    /**
     * Obtains all mailing list members.
     * @param int $iListId Mailing list identifier.
     * @return array|boolean
     */
    public function getMailingListMembers($iListId)
    {
        return MailingList::where('id_acct', $iListId)->pluck('list_to')->toArray();
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
        return !!MailingList::create([
            'id_acct' => $iListId,
            'list_name' => $sListName,
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
        return !!MailingList::where('id_acct', $iListId)->where('list_to', $sListName)->delete();
    }

    /**
     * Obtains mailing list ID with specified email
     * @param string $sEmail email.
     * @return int|boolean
     */
    public function getMailingListIdByEmail($sEmail)
    {
        $account = Account::firstWhere('email', $sEmail)->where('mailing_list', true);
        if ($account) {
            return $account->id_acct;
        }

        return false;
    }
}
