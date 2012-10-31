<?php
define("IN_MYBB", 1);
define('THIS_SCRIPT', 'todolist.php');

require_once "global.php";
$templatelist = "todolist, todolist_show, todolist_edit, todolist_add";
$templates->cache($templatelist);

$lang->load('todolist');

$errors = '';

if($mybb->settings['todolist_setting1'] == '0') {
	error($lang->offline);
}

if($mybb->settings['todolist_setting2'] == '0') {
	if ($mybb->user['uid'] == '0') {
		error_no_permission();
	}
}

$perm_group = explode(",", $mybb->settings['todolist_setting3']);
foreach($perm_group as $groups) {
	if ($mybb->user['usergroup'] == $groups) {
		error_no_permission();
	}
}

$perm_group = explode(",", $mybb->settings['todolist_setting5']);
foreach($perm_group as $groups) {
	if ($mybb->user['usergroup'] == $groups) {
		$addtodo = "<strong><img src='images/todolist/add.png' /> <a href='todolist.php?act=submit'>$lang->add_todo</a></strong>";
	}
}

$modgroup = "";
$mods = explode(",", $mybb->settings['todolist_setting4']);
$a = count($mods);
foreach($mods as $key => $groups) {
	$query = $db->query("SELECT * FROM ".TABLE_PREFIX."usergroups WHERE gid='".$groups."'");
	while($row = $db->fetch_array($query)) {
		if($key +1 == $a) {
			$modgroup .= format_name($row['title'], $row['gid'])." ";
		} else {
			$modgroup .= format_name($row['title'], $row['gid']).", ";
		}
	}
}

$act = $_GET['act'];
if ($act == "") {
	add_breadcrumb("{$lang->title_overview}: {$mybb->settings['todolist_setting6']}", "todolist.php");
	$get_todo = $db->query("SELECT * FROM ".TABLE_PREFIX."todolist ORDER BY date DESC");
	while($row = $db->fetch_array($get_todo)) {
	$id = $row['id'];
	$title = $row['title'];
	$name = $row['name'];
	$group = $row['usergroup'];
	
$perm_group = explode(",", $mybb->settings['todolist_setting4']);
foreach($perm_group as $groups) {
	if ($mybb->user['usergroup'] == $groups) {
		$mod_todo = "- <a href='todolist.php?act=edit&id=$id'><img src='images/todolist/edit.png' /> ".$lang->edit_todo."</a> - <a href='todolist.php?act=delete&id=$id'><img src='images/todolist/delete.png' /> ".$lang->delete_todo."</a>";
	}
}
		
		if($row['priority'] == 'normal') {
			$priority = "<img src=\"images/todolist/norm_prio.png\" border=\"0\" /> {$lang->normal_priority}";
		} elseif($row['priority'] == 'high') {
			$priority = "<img src=\"images/todolist/high_prio.gif\" border=\"0\" /> {$lang->high_priority}";
		} elseif($row['priority'] == 'low') {
			$priority = "<img src='images/todolist/low_prio.gif' border=\"0\" /> {$lang->low_priority}";
		}
		
		if($row['status'] == 'wait') {
			$status = "<img src='images/todolist/waiting.png' border='0' /> {$lang->status_wait}";
		} elseif($row['status'] == 'development') {
			$status = "<img src='images/todolist/development.png' border='0' /> {$lang->status_dev}";
		} elseif($row['status'] == 'feedback') {
			$status = "<img src='images/todolist/feedback.png' border='0' /> {$lang->status_feed}";
		} elseif($row['status'] == 'resolved') {
			$status = "<img src='images/icons/exclamation.gif' border='0' /> {$lang->status_resolved}";
		} elseif($row['status'] == 'closed') {
			$status = "<img src='images/todolist/lock.png' border='0' /> {$lang->status_closed}";
		}
		
		if($row['done'] == '0 done') {
			$done = "<img src='images/spinner.gif' border='0' /> {$lang->done_0}";
		} elseif($row['done'] == '25 done') {
			$done = "<img src='images/spinner.gif' border='0' /> {$lang->done_25}";
		} elseif($row['done'] == '50 done') {
			$done = "<img src='images/spinner.gif' border='0' /> {$lang->done_50}";
		} elseif($row['done'] == '75 done') {
			$done = "<img src='images/spinner.gif' border='0' /> {$lang->done_75}";
		} elseif($row['done'] == '100 done') {
			$done = "<img src='images/todolist/done.png' border='0' /> {$lang->done_100}";
		}
		
		$time = date("d.m.Y", $row['date']);
		$time2 = date("H:i", $row['date']);
		
		$formattedname = format_name($name, $group);
		$owner = "<a href='member.php?action=profile&uid={$row[nameid]}'>$formattedname</a>";
		
		$todo.= "<tr class='trow1' colspan='7'><td>$title</td><td>$time - $time2</td><td>$owner</td><td>$priority</td><td>$status</td><td>$done</td><td style='width:200px'><center><a href='todolist.php?act=show&id=$id'><img src='images/todolist/show.png' /> ".$lang->show_todo."</a> ".$mod_todo."</a></center></td></tr>";
		
	}
	
	if ($todo == '') {
		$todo .= "<tr class='trow1'><td colspan='7'><center>".$lang->no_todo."</center></td></tr>";
	}
	
	eval("\$todolist .= \"".$templates->get("todolist")."\";");
	output_page($todolist);
} elseif ($act == 'show') {
	add_breadcrumb("{$lang->title_overview}: {$mybb->settings['todolist_setting6']}", "todolist.php");
	add_breadcrumb("{$lang->show_showtodo}", "todolist.php?act=show&id=$id");
	
		$id = $_GET["id"];
        $query = $db->simple_select('todolist', '*', 'id='.$id);
        while($row = $db->fetch_array($query))  {
		$id = $row['id'];
		$title = $row['title'];
		$nameid = $row['nameid'];
		$name = $row['name'];
		$message = $row['message'];
		$group = $row['usergroup'];
		$editor = $row['lasteditor'];
		$editorid = $row['lasteditorid'];
		$editorgroup = $row['editorgroup'];
		
		$perm_group = explode(",", $mybb->settings['todolist_setting4']);
		foreach($perm_group as $groups) {
			if ($mybb->user['usergroup'] == $groups) {
				$mod_todo = "<tr class='trow2'><td style='width:100px;'>".$lang->action_todo."</td><td><a href='todolist.php?act=edit&id=$id'><img src='images/todolist/edit.png' /> ".$lang->edit_todo."</a> - <a href='todolist.php?act=delete&id=$id'><img src='images/todolist/delete.png' /> ".$lang->delete_todo."</a></td></tr>";
			}
		}
	
		
		if($row['priority'] == 'normal') {
			$priority = "<img src=\"images/todolist/norm_prio.png\" border=\"0\" /> {$lang->normal_priority}";
		} elseif($row['priority'] == 'high') {
			$priority = "<img src=\"images/todolist/high_prio.gif\" border=\"0\" /> {$lang->high_priority}";
		} elseif($row['priority'] == 'low') {
			$priority = "<img src='images/todolist/low_prio.gif' border=\"0\" /> {$lang->low_priority}";
		}
		
		if($row['status'] == 'wait') {
			$status = "<img src='images/todolist/waiting.png' border='0' /> {$lang->status_wait}";
		} elseif($row['status'] == 'development') {
			$status = "<img src='images/todolist/development.png' border='0' /> {$lang->status_dev}";
		} elseif($row['status'] == 'feedback') {
			$status = "<img src='images/todolist/feedback.png' border='0' /> {$lang->status_feed}";
		} elseif($row['status'] == 'resolved') {
			$status = "<img src='images/icons/exclamation.gif' border='0' /> {$lang->status_resolved}";
		} elseif($row['status'] == 'closed') {
			$status = "<img src='images/todolist/lock.png' border='0' /> {$lang->status_closed}";
		}
		
		if($row['done'] == '0 done') {
			$done = "<img src='images/spinner.gif' border='0' /> {$lang->done_0}";
		} elseif($row['done'] == '25 done') {
			$done = "<img src='images/spinner.gif' border='0' /> {$lang->done_25}";
		} elseif($row['done'] == '50 done') {
			$done = "<img src='images/spinner.gif' border='0' /> {$lang->done_50}";
		} elseif($row['done'] == '75 done') {
			$done = "<img src='images/spinner.gif' border='0' /> {$lang->done_75}";
		} elseif($row['done'] == '100 done') {
			$done = "<img src='images/todolist/done.png' border='0' /> {$lang->done_100}";
		}
		
		$time = date("d.m.Y", $row['date']);
		$time2 = date("H:i", $row['date']);
		$time3 = date("d.m.Y", $row['lastedit']);
		$time4 = date("H:i", $row['lastedit']);
		
		$timestamp = date("d.m.Y", time());
		$timestamp2 = date("d.m.Y", time() -86400);
		
		if($time3 == $timestamp) {
			$time3 = "{$lang->today_showtodo}";
		} elseif($time3 == $timestamp2) {
			$time3 = "{$lang->yesterday_showtodo}";
		}
		
		$formattedname = format_name($name, $group);
		$formattedname2 = format_name($editor, $editorgroup);
		
		if($editor != '' AND $editorid != '' AND $time3 != '' AND $editorgroup != '') {
			$showtodolastedit = "<tr class='trow1'><td style='width:200px;'>{$lang->lastedit_showtodo}:</td><td>$time3 - $time4 {$lang->from_todo} <a href='member.php?action=profile&uid=$editorid'>".$formattedname2."</a></td></tr>";
		}
	
		$showtodotitle = "$title";
		$showtododate = "$time - $time2";
		$showtodoprio = "$priority";
		$showtododone = "$done";
		$showtodostatus = "$status";
		$showtodofrom = "<a href='member.php?action=profile&uid=$nameid'>$formattedname</a>";
		$showtodoaction = "$mod_todo";
		$showtodomess = "$message";
		$showtodoback = "<a href='todolist.php' target='_self'>$lang->back_showtodo</a>";
		}
		
	eval("\$todolist_show = \"".$templates->get("todolist_show")."\";");
	output_page($todolist_show);
}
elseif ($act == 'submit') {
	//show the form
	if ($mybb->input['title'] == '') {
		add_breadcrumb("{$lang->title_overview}: {$mybb->settings['todolist_setting6']}", "todolist.php");
		add_breadcrumb("$lang->add_addtodo", "todolist.php?act=submit");
		eval("\$todolist_add = \"".$templates->get("todolist_add")."\";");
		output_page($todolist_add);
	} else {
		$insert['date'] = time();
		$insert['nameid'] = $mybb->user['uid'];
		$insert['name'] = $mybb->user['username'];
		$insert['title'] = $db->escape_string($mybb->input['title']);
		$insert['priority'] = $db->escape_string($mybb->input['priority']);
		$insert['message'] = $db->escape_string($mybb->input['message']);
		$insert['usergroup'] = $mybb->user['displaygroup'];
		$insert['status'] = 'wait';
		$insert['done'] = '0 done';
		$db->insert_query("todolist",$insert);
		redirect("todolist.php", $lang->added_todo);
	}
} elseif ($act == 'delete') {
	$id = $_GET['id'];
	$db->query("DELETE FROM ".TABLE_PREFIX."todolist WHERE id='" . $id . "'");
	redirect("todolist.php", $lang->deleted_todo);
} elseif ($act == 'edit') {
		$id = $_GET['id'];
		if(isset($id)) {
			add_breadcrumb("{$lang->title_overview}: {$mybb->settings['todolist_setting6']}", "todolist.php");
			add_breadcrumb("$lang->show_showtodo", "todolist.php?act=show&id=$id");
			add_breadcrumb("$lang->edit_edittodo", "todolist.php?act=edit&id={$row[id]}");
        $query = $db->simple_select('todolist', '*', 'id='.$id);
        while($row = $db->fetch_array($query)) {
		$id = $row['id'];
		$title = $row['title'];
		$message = $row['message'];
		
		if($row['priority'] == 'normal') {
			$priority = "<img src=\"images/todolist/norm_prio.png\" border=\"0\" /> {$lang->normal_priority}";
			$changeprio = "<select name='priority' style='width:100px;'><option value='high' style='background-image:url(images/todolist/high_prio.gif); background-repeat:no-repeat; text-align:center; '>{$lang->high_priority}</option><option value='normal' style='background-image:url(images/todolist/norm_prio.png); background-repeat:no-repeat; text-align:center; ' selected>{$lang->normal_priority}</option><option value='low' style='background-image:url(images/todolist/low_prio.gif); background-repeat:no-repeat; text-align:center; '>{$lang->low_priority}</option></select>";
		} elseif($row['priority'] == 'high') {
			$priority = "<img src=\"images/todolist/high_prio.gif\" border=\"0\" /> {$lang->high_priority}";
			$changeprio = "<select name='priority' style='width:100px;'><option value='high' style='background-image:url(images/todolist/high_prio.gif); background-repeat:no-repeat; text-align:center; ' selected>{$lang->high_priority}</option><option value='normal' style='background-image:url(images/todolist/norm_prio.png); background-repeat:no-repeat; text-align:center; '>{$lang->normal_priority}</option><option value='low' style='background-image:url(images/todolist/low_prio.gif); background-repeat:no-repeat; text-align:center; '>{$lang->low_priority}</option></select>";
		} elseif($row['priority'] == 'low') {
			$priority = "<img src='images/todolist/low_prio.gif' border=\"0\" /> {$lang->low_priority}";
			$changeprio = "<select name='priority' style='width:100px;'><option value='high' style='background-image:url(images/todolist/high_prio.gif); background-repeat:no-repeat; text-align:center; '>{$lang->high_priority}</option><option value='normal' style='background-image:url(images/todolist/norm_prio.png); background-repeat:no-repeat; text-align:center; '>{$lang->normal_priority}</option><option value='low' style='background-image:url(images/todolist/low_prio.gif); background-repeat:no-repeat; text-align:center; ' selected>{$lang->low_priority}</option></select>";
		}
		
		if($row['status'] == 'wait') {
			$status = "<img src='images/todolist/waiting.png' border='0' /> {$lang->status_wait}";
			$changestatus = "<select name='status' style='width:140px;'><option value='wait' style='background-image:url(images/todolist/waiting.png); background-repeat:no-repeat; text-align:center; '>{$lang->status_wait}</option><option value='development' style='background-image:url(images/todolist/development.png); background-repeat:no-repeat; text-align:center; '>{$lang->status_dev}</option><option value='resolved' style='background-image:url(images/icons/exclamation.gif); background-repeat:no-repeat; text-align:center; '>{$lang->status_resolved}</option><option value='closed' style='background-image:url(images/todolist/lock.png); background-repeat:no-repeat; text-align:center; '>{$lang->status_closed}</option></select>";
		} elseif($row['status'] == 'development') {
			$status = "<img src='images/todolist/development.png' border='0' /> {$lang->status_dev}";
			$changestatus = "<select name='status' style='width:140px;'><option value='wait' style='background-image:url(images/todolist/waiting.png); background-repeat:no-repeat; text-align:center; '>{$lang->status_wait}</option><option value='development' style='background-image:url(images/todolist/development.png); background-repeat:no-repeat; text-align:center; ' selected>{$lang->status_dev}</option><option value='resolved' style='background-image:url(images/icons/exclamation.gif); background-repeat:no-repeat; text-align:center; '>{$lang->status_resolved}</option><option value='closed' style='background-image:url(images/todolist/lock.png); background-repeat:no-repeat; text-align:center; '>{$lang->status_closed}</option></select>";
		/*
		} elseif($row['status'] == 'feedback') {
			$status = "<img src='images/todolist/feedback.png' border='0' /> {$lang->status_feed}";
			$changestatus = "<select name='status' style='width:140px;'><option value='feedback' style='background-image:url(images/todolist/feedback.png); background-repeat:no-repeat; text-align:center; '>{$lang->status_feed}</option><option value='wait' style='background-image:url(images/todolist/waiting.png); background-repeat:no-repeat; text-align:center; '>{$lang->status_wait}</option><option value='development' style='background-image:url(images/todolist/development.png); background-repeat:no-repeat; text-align:center; '>{$lang->status_dev}</option><option value='resolved' style='background-image:url(images/icons/exclamation.gif); background-repeat:no-repeat; text-align:center; '>{$lang->status_resolved}</option><option value='closed' style='background-image:url(images/todolist/lock.png); background-repeat:no-repeat; text-align:center; '>{$lang->status_closed}</option></select>";
		*/
		} elseif($row['status'] == 'resolved') {
			$status = "<img src='images/icons/exclamation.gif' border='0' /> {$lang->status_resolved}";
			$changestatus = "<select name='status' style='width:140px;'><option value='wait' style='background-image:url(images/todolist/waiting.png); background-repeat:no-repeat; text-align:center; '>{$lang->status_wait}</option><option value='development' style='background-image:url(images/todolist/development.png); background-repeat:no-repeat; text-align:center; '>{$lang->status_dev}</option><option value='resolved' style='background-image:url(images/icons/exclamation.gif); background-repeat:no-repeat; text-align:center; ' selected>{$lang->status_resolved}</option><option value='closed' style='background-image:url(images/todolist/lock.png); background-repeat:no-repeat; text-align:center; '>{$lang->status_closed}</option></select>";
		} elseif($row['status'] == 'closed') {
			$status = "<img src='images/todolist/lock.png' border='0' /> {$lang->status_closed}";
			$changestatus = "<select name='status' style='width:140px;'><option value='wait' style='background-image:url(images/todolist/waiting.png); background-repeat:no-repeat; text-align:center; '>{$lang->status_wait}</option><option value='development' style='background-image:url(images/todolist/development.png); background-repeat:no-repeat; text-align:center; '>{$lang->status_dev}</option><option value='resolved' style='background-image:url(images/icons/exclamation.gif); background-repeat:no-repeat; text-align:center; '>{$lang->status_resolved}</option><option value='closed' style='background-image:url(images/todolist/lock.png); background-repeat:no-repeat; text-align:center; ' selected>{$lang->status_closed}</option></select>";
		}
		
		if($row['done'] == '0 done') {
			$done = "<img src='images/spinner.gif' border='0' /> {$lang->done_0}";
			$changedone = "<select name='done' style='width:130px;'><option value='0 done' style='background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center; '>{$lang->done_0}</option><option value='25 done' style='background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center; '>{$lang->done_25}</option><option value='50 done' style='background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center; '>{$lang->done_50}</option></option><option value='75 done' style='background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center; '>{$lang->done_75}</option><option value='100 done' style='background-image:url(images/todolist/done.png); background-repeat:no-repeat; text-align:center; '>{$lang->done_100}</option></select>";
		} elseif($row['done'] == '25 done') {
			$done = "<img src='images/spinner.gif' border='0' /> {$lang->done_25}";
			$changedone = "<select name='done' style='width:130px;'><option value='0 done' style='background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center; '>{$lang->done_0}</option><option value='25 done' style='background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center; ' selected>{$lang->done_25}</option><option value='50 done' style='background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center; '>{$lang->done_50}</option></option><option value='75 done' style='background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center; '>{$lang->done_75}</option><option value='100 done' style='background-image:url(images/todolist/done.png); background-repeat:no-repeat; text-align:center; '>{$lang->done_100}</option></select>";
		} elseif($row['done'] == '50 done') {
			$done = "<img src='images/spinner.gif' border='0' /> {$lang->done_50}";
			$changedone = "<select name='done' style='width:130px;'><option value='0 done' style='background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center; '>{$lang->done_0}</option><option value='25 done' style='background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center; '>{$lang->done_25}</option><option value='50 done' style='background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center; ' selected>{$lang->done_50}</option></option><option value='75 done' style='background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center; '>{$lang->done_75}</option><option value='100 done' style='background-image:url(images/todolist/done.png); background-repeat:no-repeat; text-align:center; '>{$lang->done_100}</option></select>";
		} elseif($row['done'] == '75 done') {
			$done = "<img src='images/spinner.gif' border='0' /> {$lang->done_75}";
			$changedone = "<select name='done' style='width:130px;'><option value='0 done' style='background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center; '>{$lang->done_0}</option><option value='25 done' style='background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center; '>{$lang->done_25}</option><option value='50 done' style='background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center; '>{$lang->done_50}</option></option><option value='75 done' style='background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center; ' selected>{$lang->done_75}</option><option value='100 done' style='background-image:url(images/todolist/done.png); background-repeat:no-repeat; text-align:center; '>{$lang->done_100}</option></select>";
		} elseif($row['done'] == '100 done') {
			$done = "<img src='images/todolist/done.png' border='0' /> {$lang->done_100}";
			$changedone = "<select name='done' style='width:130px;'><option value='0 done' style='background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center; '>{$lang->done_0}</option><option value='25 done' style='background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center; '>{$lang->done_25}</option><option value='50 done' style='background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center; '>{$lang->done_50}</option></option><option value='75 done' style='background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center; '>{$lang->done_75}</option><option value='100 done' style='background-image:url(images/todolist/done.png); background-repeat:no-repeat; text-align:center; ' selected>{$lang->done_100}</option></select>";
		}
		
		$editing = "<tr class='trow1'><input type='hidden' name='id' size='2' value='" . $id . "'><td style='width:100px;'>Titel:</td><td><input type='text' name='title' size='40' value='" . $title . "'></td></tr>
			<tr class='trow1'><td style='width:100px;'>{$lang->priority_edittodo}:</td><td>{$lang->nowprio_edittodo}: $priority - $changeprio</td></tr>
			<tr class='trow1'><td style='width:100px;'>{$lang->done_edittodo}:</td><td>{$lang->nowdone_edittodo}: $done - $changedone</td></tr>
			<tr class='trow1'><td style='width:100px;'>{$lang->status_edittodo}:</td><td>{$lang->nowstat_edittodo}: $status - $changestatus</td></tr>
			<tr class='trow1'><td style='width:200px;'>{$lang->description_edittodo}:</td><td><textarea name='message' rows='6' cols='15' style='width:300px; height:200px;'>$message</textarea></td></tr>
			<tr class='trow1'><td colspan='2'><input type='submit' value='{$lang->send_edittodo}' style='margin-left: 280px; '/></td></form>
			<tr class='trow1'><td colspan='2'><a href='todolist.php?act=show&id=$id' target='_self'>{$lang->back_showtodo}</a></td></tr>";
		}
		
	eval("\$todolist_edit = \"".$templates->get("todolist_edit")."\";");
	output_page($todolist_edit);
	}
} elseif($act == 'submit-edit') {
	$id = $_POST['id'];
	$title = $_POST['title'];
	$done = $_POST['done'];
	$status = $_POST['status'];
	$priority = $_POST['priority'];
	$message = $_POST['message'];
	$lasteditor = $mybb->user['username'];
	$lasteditorid = $mybb->user['uid'];
	$lastedit = time();
	$editorgroup = $mybb->user['displaygroup'];
	
	$db->query("UPDATE ".TABLE_PREFIX."todolist SET title='$title', done='$done', status='$status', priority='$priority', message='$message', lasteditor='$lasteditor', lasteditorid='$lasteditorid', lastedit='$lastedit', editorgroup='$editorgroup' WHERE id='$id'");
	redirect("todolist.php?act=show&id=$id", $lang->edited_todo);
}
?>