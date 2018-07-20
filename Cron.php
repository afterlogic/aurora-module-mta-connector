<?php
namespace Aurora\Modules\MailSuite;

require_once dirname(__file__)."/../../system/autoload.php";
\Aurora\System\Api::Init();

class CronFetcher
{
	private $oApiFetchersManager;
	private $oApiAccountsManager;
	
	public function __construct()
	{
		$oMailSuiteModule =  \Aurora\System\Api::GetModule('MailSuite');
		$this->oApiFetchersManager = $oMailSuiteModule->oApiFetchersManager;
		
		$oMailModule =  \Aurora\System\Api::GetModule('Mail');
		$this->oApiAccountsManager = $oMailModule->oApiAccountsManager;
	}
	
	public static function NewInstance()
	{
		return new self();
	}
	
	public function Execute()
	{
		$iTimer = microtime(true);
		$this->log('Start fetcher cron script');

		$sBasedir = "/opt/afterlogic";
		$aFetchers = $this->oApiFetchersManager->getFetchers();
		foreach ($aFetchers as $key => $oFetcher)
		{
			$bDoFetch = true;
			$oAccount = $this->oApiAccountsManager->getAccountById($oFetcher->IdAccount);
			preg_match('/(.+)@(.+)$/', $oAccount->Email, $aMatches);
			$sLogin = "";
			$sDomain = "";
			if (isset($aMatches) && count($aMatches) > 1)
			{
				$sLogin = $aMatches[1];
				$sDomain = $aMatches[2];
			}
			$sMaildir = $sBasedir . '/data/' . $sDomain . '/' . $sLogin;
			
			$this->log('Fetch mail from ' . $oFetcher->IncomingServer . ':' . $oFetcher->IncomingPort . '/' . $oFetcher->IncomingLogin . ' - ' . $sDomain . '/' . $sLogin);
			
			if ($oFetcher->IsLocked)
			{
				$iTimeInterval = microtime(true) - $oFetcher->CheckLastTime;
				if ($oFetcher->CheckInterval > 0 && $iTimeInterval > $oFetcher->CheckInterval)
				{
					$this->log('Previous lock timeout expired, acquiring the lock...');
					$oFetcher->CheckLastTime = microtime(true);
				}
				else
				{
					$this->log('The fetching process for a current mailbox is already running, exiting...');
					$bDoFetch = false;
				}
			}
			else
			{
				$this->log('Mailbox is free, locking it...');
				$oFetcher->IsLocked = true;
				$oFetcher->CheckLastTime = microtime(true);
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

				$sCommand = $sBasedir . '/bin/mpop';
				$sCommand .= ' --host="' . $oFetcher->IncomingServer . '"';
				$sCommand .= ' --port=' . $oFetcher->IncomingPort;
				$sCommand .= ' --user="' . $oFetcher->IncomingLogin . '"';
				$sCommand .= ' --auth=user';
				$sCommand .= ' --uidls-file="' . $sMaildir . '/fetcher-id' . $oFetcher->EntityId . '-uidls"';
				$sCommand .= ' --delivery=mda,"' . $sBasedir . '/scripts/dovecot-lda-wrapper.sh -d ' . $oAccount->Email . ' -m \'' . $oFetcher->Folder . '\'"';
				$sCommand .= ' --keep=' . ($oFetcher->LeaveMessagesOnServer ? 'on' : 'off');
				$sCommand .= ' --only-new=on';
				$sCommand .= ' --tls=' . $sTls;
				$sCommand .= ' --tls-certcheck=off';
				$sCommand .= ' --tls-starttls=' . $sStartTls;
				$sCommand .= ' --received-header=off';
				$sCommand .= ' --half-quiet';
//				$sCommand .= ' -d'; // debug mode
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

		$this->log('Cron execution time: '.(microtime(true) - $iTimer).' sec.');
	}
	
	private function log($sMessage)
	{
		\Aurora\System\Api::Log($sMessage, \Aurora\System\Enums\LogLevel::Full, 'cron-fetcher-');
	}
}

CronFetcher::NewInstance()->Execute();
