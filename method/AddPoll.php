<?php

final class Forum_AddPoll extends GWF_Method
{
	public function isLoginRequired() { return true; }
	
	public function getHTAccess()
	{
		return 'RewriteRule ^thread_add_poll/(\d+)/[^/]+$ index.php?mo=Forum&me=AddPoll&tid=$1 [QSA]'.PHP_EOL;
	}
	
	private $thread;
	
	public function execute()
	{
		if (false === ($mod_votes = GWF_Module::loadModuleDB('Votes'))) {
			return GWF_HTML::err('ERR_MODULE_MISSING', array('Votes'));
		}
		$mod_votes->onInclude();
		
		if (!($this->thread = GWF_ForumThread::getThread(Common::getGet('tid')))) {
			return $this->module->error('err_thread');
		}
		
		$this->user = GWF_Session::getUser();
		
		if (!$this->thread->mayAddPoll($this->user)) {
			return GWF_HTML::err('ERR_NO_PERMISSION');
		}
		
		if (false !== Common::getPost('assign')) {
			return $this->onAssign().$this->template();
		}
		
		return $this->template();
	}
	
	private function template()
	{
		$form = $this->getForm();
		$tVars = array(
			'may_add_poll' => Module_Votes::mayAddPoll($this->user),
			'href_add' => Module_Votes::hrefAddPoll(),
			'form' => $form->templateY($this->module->lang('ft_add_poll')),
		);
		return $this->module->templatePHP('add_poll.php', $tVars);
	}
	
	private function getForm()
	{
		$data = array(
			'pollid' => array(GWF_Form::SELECT, $this->getPollSelect(), $this->module->lang('th_thread_pollid')),
			'assign' => array(GWF_Form::SUBMIT, $this->module->lang('btn_assign')),
		);
		return new GWF_Form($this, $data);
	}

	private function getPollSelect()
	{
		if (false === ($mv = GWF_Module::getModule('Votes'))) {
			return GWF_HTML::lang('ERR_MODULE_MISSING', array('Votes'));
		}
		$uid = GWF_Session::getUserID();
		
		if (false === $polltable = GDO::table('GWF_VoteMulti')) {
			return GWF_HTML::lang('ERR_MODULE_MISSING', array('Votes'));
		}
		
		$polls = $polltable->selectAll('vm_id, vm_title', "vm_uid=$uid", 'vm_title ASC', NULL, -1, -1, GDO::ARRAY_N);
		
		$data = array(
			array('0', $this->module->lang('sel_poll')),
		);
		
		
		foreach ($polls as $poll)
		{
			$data[] = $poll;
//			$data[] = $poll;array($poll[0], $poll->getVar('vm_title'), );
		}
		
		return GWF_Select::display('pollid', $data, Common::getPostString('pollid', '0'));
	}
	
	/**
	 * @var GWF_VoteMulti
	 */
	private $poll = NULL;
	public function validate_pollid(Module_Forum $m, $arg)
	{
		if (false === ($p = GWF_VoteMulti::getByID($arg))) {
			return $m->lang('err_poll');
		}
		if ($p->getUserID() !== $this->user->getID()) {
			return $m->lang('err_poll');
		}
		return false;
	}
	
	private function onAssign()
	{
		$form = $this->getForm();
		if (false !== ($errors = $form->validate($this->module))) {
			return $errors;
		}
		
		if (false === $this->thread->saveVar('thread_pollid', $form->getVar('pollid'))) {
			return GWF_HTML::err('ERR_DATABASE', array(__FILE__, __LINE__));
		}
		
		return $this->module->message('msg_poll_assigned');
	}
}
?>
