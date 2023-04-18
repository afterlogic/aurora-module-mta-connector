<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MtaConnector;

use Aurora\System\SettingsProperty;

/**
 * @property bool $Disabled
 * @property bool $AllowFetchers
 * @property string $FetchersCronMpopScript
 * @property string $FetchersCronMpopDataFolder
 * @property string $FetchersCronDeliveryScript
 * @property int $Fetchers * @property IntervalMinutes
 * @property int $UserDefaultQuotaMB
 */

class Settings extends \Aurora\System\Module\Settings
{
    protected function initDefaults()
    {
        $this->aContainer = [
            "Disabled" => new SettingsProperty(
                false,
                "bool",
                null,
                "Setting to true disables the module",
            ),
            "AllowFetchers" => new SettingsProperty(
                false,
                "bool",
                null,
                "If true, Add POP3 Fetcher option becomes available in Email Accounts area of Settings screen",
            ),
            "FetchersCronMpopScript" => new SettingsProperty(
                "/usr/bin/mpop",
                "string",
                null,
                "Defines location of mpop binary",
            ),
            "FetchersCronMpopDataFolder" => new SettingsProperty(
                "/opt/afterlogic/data",
                "string",
                null,
                "Defines location of mailserver data directory",
            ),
            "FetchersCronDeliveryScript" => new SettingsProperty(
                "/usr/lib/dovecot/dovecot-lda",
                "string",
                null,
                "Defines location of local delivery agent",
            ),
            "FetchersIntervalMinutes" => new SettingsProperty(
                20,
                "int",
                null,
                "Interval of running fetcher script, in minutes; should match the interval set in cronjob",
            ),
            "UserDefaultQuotaMB" => new SettingsProperty(
                200,
                "int",
                null,
                "Default size for new mailboxes created, in Mbytes",
            ),
        ];
    }
}
