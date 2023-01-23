<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MtaConnector;

require_once dirname(__file__)."/../../system/autoload.php";
\Aurora\System\Api::Init();

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2023, Afterlogic Corp.
 */
class CronFetcher
{
	private $oMtaConnectorModule;
	private $oApiFetchersManager;
	private $oApiAccountsManager;
	
	private $sFetchersCronMpopDataFolder;
	private $sFetchersCronMpopScript;
	private $sFetchersCronDeliveryScript;
	
	public function __construct()
	{
		$this->oMtaConnectorModule =  \Aurora\System\Api::GetModule('MtaConnector');
		$this->oApiFetchersManager = $this->oMtaConnectorModule->oApiFetchersManager;
		
		$oMailModule =  \Aurora\System\Api::GetModule('Mail');
		$this->oApiAccountsManager = $oMailModule->getAccountsManager();
		
		$this->sFetchersCronMpopDataFolder = $this->oMtaConnectorModule->getConfig('FetchersCronMpopDataFolder', '');
		$this->sFetchersCronMpopScript = $this->oMtaConnectorModule->getConfig('FetchersCronMpopScript', '');
		$this->sFetchersCronDeliveryScript = $this->oMtaConnectorModule->getConfig('FetchersCronDeliveryScript', '');
	}
	
	public static function NewInstance()
	{
		return new self();
	}
	
	public function ExecuteFetcher($oFetcher)
	{
		$bDoFetch = true;
		$oAccount = $this->oApiAccountsManager->getAccountById($oFetcher->IdAccount);
		if (!isset($oAccount))
		{
			$this->log('There is no mail account with identifier ' . $oFetcher->IdAccount);
		}
		else
		{
			preg_match('/(.+)@(.+)$/', $oAccount->Email, $aMatches);
			$sLogin = '';
			$sDomain = '';
			if (isset($aMatches) && count($aMatches) > 1)
			{
				$sLogin = $aMatches[1];
				$sDomain = $aMatches[2];
			}
			$sMaildir = $this->sFetchersCronMpopDataFolder . '/' . $sDomain . '/' . $sLogin;

			$this->log('Fetch mail from ' . $oFetcher->IncomingServer . ':' . $oFetcher->IncomingPort . '/' . $oFetcher->IncomingLogin . ' - ' . $sDomain . '/' . $sLogin);

			if ($oFetcher->IsLocked)
			{
				$this->log('The fetching process for a current mailbox is already running, exiting...');
				$bDoFetch = false;
			}
			else
			{
				$iNow = microtime(true);
				$iTimeInterval = $iNow - $oFetcher->CheckLastTime;
				$iCheckIntervalSeconds = $oFetcher->CheckInterval * 60;
				if ($iCheckIntervalSeconds > 0 && $iTimeInterval < $iCheckIntervalSeconds)
				{
					$this->log('Last check was ' . floor($iTimeInterval) . ' seconds ago, check interval is ' . $iCheckIntervalSeconds . ' seconds, exiting...');
					$bDoFetch = false;
				}
				else
				{
					$this->log('Mailbox is free, locking it...');
					$oFetcher->IsLocked = true;
					$oFetcher->CheckLastTime = $iNow;
				}
			}

			if ($bDoFetch)
			{
				$this->oApiFetchersManager->updateFetcher($oFetcher, false);

				$sTls = 'off';
				$sStartTls = 'off';
				switch ($oFetcher->IncomingMailSecurity)
				{
					case \MailSo\Net\Enumerations\ConnectionSecurityType::SSL:
						$sTls = 'on';
						break;
					case \MailSo\Net\Enumerations\ConnectionSecurityType::STARTTLS:
						$sTls = 'on';
						$sStartTls = 'on';
						break;
				}

				$sCommand = $this->sFetchersCronMpopScript;
				$sCommand .= ' --host="' . $oFetcher->IncomingServer . '"';
				$sCommand .= ' --port=' . $oFetcher->IncomingPort;
				$sCommand .= ' --user="' . $oFetcher->IncomingLogin . '"';
				$sCommand .= ' --auth=user';
				$sCommand .= ' --uidls-file="' . $sMaildir . '/fetcher-id' . $oFetcher->EntityId . '-uidls"';
				$sCommand .= ' --delivery=mda,"' . $this->sFetchersCronDeliveryScript . ' -d ' . $oAccount->Email . ' -m \'' . $oFetcher->Folder . '\'"';
				$sCommand .= ' --keep=' . ($oFetcher->LeaveMessagesOnServer ? 'on' : 'off');
				$sCommand .= ' --only-new=on';
				$sCommand .= ' --tls=' . $sTls;
				$sCommand .= ' --tls-certcheck=off';
				$sCommand .= ' --tls-starttls=' . $sStartTls;
				$sCommand .= ' --received-header=off';
				$sCommand .= ' --half-quiet';
	//			$sCommand .= ' -d'; // debug mode
				$sCommand .= ' --passwordeval="echo \'' . $oFetcher->IncomingPassword . '\'"';

				$this->log('Execute MPOP...');
				$aOutput = [];
				$iResult = 0;
				$sOutput = exec($sCommand, $aOutput, $iResult);
				$this->log('Result: ' . $sOutput . '/' . $iResult);
				$this->log('Result serialized: ' . json_encode($aOutput));

				$this->log('Unlock mailbox...');
				$oFetcher->IsLocked = false;
				$this->oApiFetchersManager->updateFetcher($oFetcher, false);
			}
		}
	}
	
	public function ExecuteAllFetchers()
	{
		$iTimer = microtime(true);
		$this->log('Start fetcher cron script');

		$bAllowFetchersCrone = $this->oMtaConnectorModule->getConfig('AllowFetchers', false);
		
		if (!$bAllowFetchersCrone)
		{
			$this->log('Fetchers are not allowed, exiting...');
		}
		
//		if (!file_exists($this->sFetchersCronMpopDataFolder))
//		{
//			$this->log('Cron data folder (' . $this->sFetchersCronMpopDataFolder . ') does not exist, exiting...');
//			$bAllowFetchersCrone = false;
//		}
//		
//		if (!file_exists($this->sFetchersCronMpopScript))
//		{
//			$this->log('Cron MPOP script (' . $this->sFetchersCronMpopScript . ') does not exist, exiting...');
//			$bAllowFetchersCrone = false;
//		}
//		
//		if (!file_exists($this->sFetchersCronDeliveryScript))
//		{
//			$this->log('Cron delivery script (' . $this->sFetchersCronDeliveryScript . ') does not exist, exiting...');
//			$bAllowFetchersCrone = false;
//		}
		
		if ($bAllowFetchersCrone)
		{
			$aFetchers = $this->oApiFetchersManager->getFetchers();
			foreach ($aFetchers as $oFetcher)
			{
				if ($oFetcher->IsEnabled) {
					$this->ExecuteFetcher($oFetcher);
				}
			}
		}

		$this->log('Cron execution time: '.(microtime(true) - $iTimer).' sec.');
	}
	
	private function log($sMessage)
	{
		\Aurora\System\Api::Log($sMessage, \Aurora\System\Enums\LogLevel::Full, 'cron-fetcher-');
	}
}

CronFetcher::NewInstance()->ExecuteAllFetchers();
