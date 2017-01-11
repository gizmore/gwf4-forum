<div class="gwf_board_quicktree"><?php echo Module_Forum::getNavTree(); ?></div>

<?php echo GWF_Box::box($subscr_intro) ?>; 

<!-- Manual subscribed boards -->
<div class="box box_c">
<?php echo $subscr_intro_boards; ?>
<?php echo GWF_Table::start(); ?>
<?php echo GWF_Table::displayHeaders1(array(
array('ID'),
array($lang->lang('th_title')),
array($lang->lang('btn_unsubscribe'))
));

foreach ($subscr_boards as $sub)
{
	GWF_Table::rowStart();
	echo GWF_Table::column($sub['boardid'], 'gwf-num');
	echo GWF_Table::column(GWF_HTML::anchor($sub['board_url'], $sub['board_title']));
	echo GWF_Table::column(GWF_Button::generic($lang->lang('btn_unsubscribe'), $sub['href_unsub']));
	echo GWF_Table::rowEnd();
}
echo GWF_Table::end();
?>
</div>

<!-- Manual subscribed threads -->
<div class="box box_c">
<?php echo $subscr_intro_threads; ?>
<?php echo GWF_Table::start(); ?>
<?php echo GWF_Table::displayHeaders1(array(
array('ID'),
array($lang->lang('th_title')),
array($lang->lang('btn_unsubscribe'))
));

foreach ($subscr_threads as $sub)
{
	GWF_Table::rowStart();
	echo GWF_Table::column($sub['thread_tid'], 'gwf-num');
	echo GWF_Table::column(GWF_HTML::anchor($sub['thread_url'], $sub['thread_title']));
	echo GWF_Table::column(GWF_Button::generic($lang->lang('btn_unsubscribe'), $sub['href_unsub']));
	echo GWF_Table::rowEnd();
}
echo GWF_Table::end();
?>
</div>
