<?php
/**
 * Mark all unread threads as read.
 * @author gizmore
 */
final class Forum_MarkRead extends GWF_Method
{
	public function isLoginRequired() { return true; }
	
	public function getPageMenuLinks()
	{
		return array(
				array(
						'page_url' => 'index.php?mo=Forum&me=MarkRead',
						'page_title' => 'Mark threads as read',
						'page_meta_desc' => 'Mark all the unread forum threads to read',
				),
		);
	}
	
	public function execute()
	{
		$user = GWF_Session::getUser();
		
		$cnt = GWF_ForumThread::getUnreadThreadCount($user);
		
		// Save stamp
		$data = $user->getUserData();
		$data[GWF_ForumThread::STAMP_NAME] = GWF_Time::getDate(GWF_Date::LEN_SECOND);
		if (false === $user->saveUserData($data)) {
			return GWF_HTML::err('ERR_DATABASE', __FILE__, __LINE__);
		}
		
		// Clean thread table
		$threads = GDO::table('GWF_ForumThread');
		$uid = $user->getVar('user_id');
		if (false === $threads->update("thread_unread=REPLACE(thread_unread, ':$uid:', ':'), thread_force_unread=REPLACE(thread_force_unread, ':$uid:', ':')")) {
			return GWF_HTML::err('ERR_DATABASE', __FILE__, __LINE__);
		}

// 		$cnt = $threads->affectedRows(); # As we mark all read it's easier to assume it works :D
		
		return $this->module->message('msg_mark_aread', array($cnt));
	}
}
?>