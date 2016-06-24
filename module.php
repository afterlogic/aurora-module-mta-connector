<?php

class MailSiuteModule extends AApiModule
{
	public $oApiMailsuiteManager = null;
	
	public function init() 
	{
		parent::init();
		$this->oApiMailsuiteManager = $this->GetManager();
	}
}


