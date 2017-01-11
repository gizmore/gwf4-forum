<?php
/**
 * Forum administration. Approve/Disapprove posts.
 * @author gizmore
 * @version 3.0
 * @since 1.0
 */
final class Forum_Admin extends GWF_Method
{
	const DEFAULT_BY = 'post_date';
	const DEFAULT_DIR = 'DESC';
	
	public function getUserGroups() { return array(GWF_Group::ADMIN, GWF_Group::STAFF); }
	
	public function getHTAccess()
	{
		return 'RewriteRule ^forum/admin$ index.php?mo=Forum&me=Admin [QSA]'.PHP_EOL;
	}
	
	public function getPageMenuLinks()
	{
		return array(
			array(
				'page_url' => 'forum/admin',
				'page_title' => 'Forum Admin Panel',
				'page_meta_desc' => 'Admin Panel for the Forum',
			),
		);
	}
	
	public function execute()
	{
		if (false !== Common::getGet('fixcounters'))
		{
			return $this->onFixCounters().$this->templateAdmin();
		}
		if (false !== Common::getGet('cleanup'))
		{
			return $this->onCleanup().$this->templateAdmin();
		}
		return $this->templateAdmin();
	}
	
	private function templateAdmin()
	{
		$posts = GDO::table('GWF_ForumPost');
		$ipp = $this->module->getPostsPerThread();

		# In Moderation
		$mconditions = 'post_options&'.GWF_ForumPost::IN_MODERATION;
		$mnItems = $posts->countRows($mconditions);
		$mnPages = GWF_PageMenu::getPagecount($ipp, $mnItems);
		$mpage = Common::clamp(intval(Common::getGet('mpage')), 1, $mnPages);
		$mby = Common::getGet('mby', self::DEFAULT_BY);
		$mdir = Common::getGet('mdir', self::DEFAULT_DIR);
		$morderby = $posts->getMultiOrderby($mby, $mdir);
		
		$tVars = array(
			# In Moderation
			'posts_mod' => $posts->selectObjects('*', $mconditions, $morderby, $ipp, GWF_PageMenu::getFrom($mpage, $ipp)),
			'sort_url_mod' => GWF_WEB_ROOT.'index.php?mo=Forum&me=Admin&mby=%BY%&mdir=%DIR%',
			'page_menu_mod' => GWF_PageMenu::display($mpage, $mnPages, sprintf(GWF_WEB_ROOT.'index.php?mo=Forum&me=Admin&mby=%s&mdir=%s&mpage=%%PAGE%%', $mby, $mdir)),
			# Buttons
			'href_fix_counters' => $this->getMethodHref('&fixcounters=now'),
			'href_cleanup' => $this->getMethodHref('&cleanup=now'),
		);
		return $this->module->templatePHP('admin.php', $tVars);
	}
	
	private function onCleanup()
	{
		$threads = GDO::table('GWF_ForumThread');
		$mod = GWF_ForumThread::IN_MODERATION;
		if (false === $threads->deleteWhere("thread_options&$mod"))
		{
			return GWF_HTML::err('ERR_DATABASE', array(__FILE__, __LINE__));
		}
		$dt = $threads->affectedRows();
		
		$posts = GDO::table('GWF_ForumPost');
		$mod = GWF_ForumPost::IN_MODERATION;
		if (false === $posts->deleteWhere("post_options&$mod"))
		{
			return GWF_HTML::err('ERR_DATABASE', array(__FILE__, __LINE__));
		}
		$dp = $posts->affectedRows();
		
		return $this->module->message('msg_cleanup', array($dt, $dp));
	}

	private function onFixCounters()
	{
		
	}
}
