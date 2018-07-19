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
//		$this->aUsers = array();
//		$this->aCalendars = array();
//		$this->sCurRunFilePath = \Aurora\System\Api::DataPath().'/reminder-run';
//
		$oMailModule =  \Aurora\System\Api::GetModule('Mail');
//		$this->oCalendarModule = \Aurora\System\Api::GetModule('Calendar');
//
//		$this->oApiUsersManager = \Aurora\System\Api::GetModule('Core')->oApiUsersManager ;
//		$this->oApiCalendarManager = $this->oCalendarModule->oApiCalendarManager;
//		$this->oApiMailManager = $oMailModule->oApiMailManager;
		$this->oApiAccountsManager = $oMailModule->oApiAccountsManager;
	}
	
	public static function NewInstance()
	{
		return new self();
	}
	
	public function Execute()
	{
		$iTimer = microtime(true);
		$this->log('---------- Start fetcher cron script');

		$aFetchers = $this->oApiFetchersManager->getFetchers();
		foreach ($aFetchers as $key => $oFetcher) {
			// if interval is ended and fetcher is not locked:
			// lock
			
			// fetch
			
			$sBasedir = "/opt/afterlogic";
			
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
			
//			$sCommand = 'su -s /bin/bash -m -c "' . $sBasedir . '/bin/mpop';
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
//			$sCommand .= ' --half-quiet';
			$sCommand .= ' -d'; // debug mode
			$sCommand .= ' --passwordeval="' . $oFetcher->IncomingPassword . '"';
			
			echo '<pre>' . $sCommand . '</pre>';
			//execute a fetch command as user afterlogic
			$output = [];
			$return_var = 0;
			$sOutput = exec($sCommand, $output, $return_var);
			var_dump($sOutput);
			var_dump($output);
			var_dump($return_var);
			
			// unlock
			
		}

		$this->log('Cron execution time: '.(microtime(true) - $iTimer).' sec.');
	}
	
	private function log($sMessage)
	{
		echo $sMessage . '<br />';
//		\Aurora\System\Api::Log($sMessage, \Aurora\System\Enums\LogLevel::Full, 'cron-fetcher-');
	}
}


CronFetcher::NewInstance()->Execute();
