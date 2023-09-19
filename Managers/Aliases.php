<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MtaConnector\Managers;

use Aurora\Modules\MtaConnector\Models\Alias;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2023, Afterlogic Corp.
 *
 * @property Module $oModule
 */
class Aliases extends \Aurora\System\Managers\AbstractManager
{
    /**
     * Obtains all aliases for specified mail account.
     * @param int $iAccountId Account identifier.
     * @return array|boolean
     */
    public function getAliases($iAccountId)
    {
        $result = [];
        $aliases = Alias::where('id_acct', $iAccountId)->get();
        foreach ($aliases as $alias) {
            $result[] = $alias->alias_name . '@' . $alias->alias_domain;
        }

        return $result;
    }

    /**
     * Creates new alias with specified name and domain.
     * @param int $iAccountId Account identifier.
     * @param string $sName Alias name.
     * @param string $sDomain Alias domain.
     * @param string $sToEmail Email of the mailbox where messages should be sent.
     * @return boolean
     */
    public function addAlias($iAccountId, $sName, $sDomain, $sToEmail)
    {
        return !!Alias::create([
            'id_acct' => $iAccountId,
            'alias_name' => $sName,
            'alias_domain' => $sDomain,
            'alias_to' => $sToEmail,
        ]);
    }

    /**
     * Deletes alias with specified name and domain.
     * @param int $iAccountId Account identifier
     * @param string $sName Alias name.
     * @param string $sDomain Alias domain.
     * @return string
     */
    public function deleteAlias($iAccountId, $sName, $sDomain)
    {
        return !!Alias::where('id_acct', $iAccountId)->where('alias_name', $sName)->where('alias_domain', $sDomain)->delete();
    }

    /**
     * Deletes all aliases for specified account.
     * @param int $iAccountId Account identifier.
     * @return boolean
     */
    public function deleteAliases($iAccountId)
    {
        return !!Alias::where('id_acct', $iAccountId)->delete();
    }
}
