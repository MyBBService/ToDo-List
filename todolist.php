<?php
define("IN_MYBB", 1);
define('THIS_SCRIPT', 'todolist.php');

$templatelist = "todolist, todolist_show, todolist_edit, todolist_add";
require_once "global.php";

$lang->load('todolist');

if(!function_exists("todolist_info") || $mybb->settings['todo_activate'] == '0')
	error($lang->offline);

if($mybb->settings['todo_allow_guests'] == '0' && $mybb->user['uid'] == '0')
	error_no_permission();

$perm_group = explode(",", $mybb->settings['todo_disallowed_groups']);
foreach($perm_group as $groups) {
	if ($mybb->user['usergroup'] == $groups)
		error_no_permission();
}

$perm_group = explode(",", $mybb->settings['todo_add_groups']);
foreach($perm_group as $groups) {
	if ($mybb->user['usergroup'] == $groups)
		$addtodo = "<strong><img src='images/todolist/add.png' /> <a href='todolist.php?action=submit'>{$lang->add_todo}</a></strong>";
}

$modgroup = "";
$mods = explode(",", $mybb->settings['todo_mod_groups']);
foreach($groupscache as $group) {
	if(in_array($group['gid'], $mods))
		$modgroup .= format_name($group['title'], $group['gid']).", ";
}
$modgroup = substr($modgroup, 0, -2);

if ($mybb->input['action'] == "") {
	add_breadcrumb("{$lang->title_overview}: {$mybb->settings['todo_name']}", "todolist.php");

	$page = (int)$mybb->input['page'];
	if($page > 0)
		$start = ($page-1) *$mybb->settings['todo_per_page'];
	else {
		$start = 0;
		$page = 1;
	}
	$query = $db->simple_select("todolist", "COUNT(id) AS count");
	$num = $db->fetch_field($query, "count");
	$multipage = multipage($num, $mybb->settings['todo_per_page'], $page, "todolist.php");

	$get_todo = $db->simple_select("todolist", "*", "", array("order_by" => "date", "order_dir" => "DESC", "limit_start" => $start, "limit" => $mybb->settings['todo_per_page']));
	$todo = "";

	$perm_group = explode(",", $mybb->settings['todo_mod_groups']);
	foreach($perm_group as $groups) {
		if ($mybb->user['usergroup'] == $groups) {
			$mod_todo = "- ";
			eval("\$mod_todo .= \"".$templates->get("todolist_mod")."\";");
		}
	}
	while($row = $db->fetch_array($get_todo)) {
		$id = $row['id'];
		$title = $row['title'];
		$name = $row['name'];
		if($nameid != "")
			$group = $db->fetch_field($db->simple_select("users", "usergroup", "uid={$row['nameid']}"), "usergroup");
		else
			$group = "";
			
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
		$owner = "<a href='member.php?action=profile&uid={$row[nameid]}'>{$formattedname}</a>";
		
		eval("\$todo .= \"".$templates->get("todolist_table")."\";");		
	}
	
	if ($todo == '') {
		eval("\$todo = \"".$templates->get("todolist_table_no_results")."\";");
	}
	
	eval("\$todolist .= \"".$templates->get("todolist")."\";");
	output_page($todolist);
} elseif ($mybb->input['action'] == 'show') {
	add_breadcrumb("{$lang->title_overview}: {$mybb->settings['todo_name']}", "todolist.php");
	
	$id = (int)$mybb->input['id'];
	$query = $db->simple_select('todolist', '*', "id='{$id}'");

	require_once MYBB_ROOT."inc/class_parser.php";
	$parser = new postParser;
	$parser_options = array(
		"allow_html" => 0,
		"allow_mycode" => 1,
		"allow_smilies" => 1,
		"allow_imgcode" => 1,
		"allow_videocode" => 0,
		"filter_badwords" => 1
	);

	$perm_group = explode(",", $mybb->settings['todo_mod_groups']);
	foreach($perm_group as $groups) {
		if ($mybb->user['usergroup'] == $groups) {
			eval("\$mod_todo = \"".$templates->get("todolist_mod")."\";");
			eval("\$mod_todo = \"".$templates->get("todolist_mod_table")."\";");
		}
	}

	$row = $db->fetch_array($query);
	$id = $row['id'];
	$title = $row['title'];
	add_breadcrumb("{$lang->show_showtodo}: {$title}", "todolist.php?action=show&id=$id");
	$nameid = $row['nameid'];
	$name = $row['name'];
	$message = $parser->parse_message($row['message'], $parser_options);
	$editor = $row['lasteditor'];
	$editorid = $row['lasteditorid'];
	if($editorid != "")
		$editorgroup = $db->fetch_field($db->simple_select("users", "usergroup", "uid={$editorid}"), "usergroup");
	else
		$editorgroup = "";
	if($nameid != "")
		$group = $db->fetch_field($db->simple_select("users", "usergroup", "uid={$nameid}"), "usergroup");
	else
		$group = "";
	
	
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
		$time3 = $lang->today_showtodo;
	} elseif($time3 == $timestamp2) {
		$time3 = $lang->yesterday_showtodo;
	}
	
	$formattedname = format_name($name, $group);
	$formattedname2 = format_name($editor, $editorgroup);
	
	if($editor != '' && $editorid != '' && $time3 != '' && $editorgroup != '') {
		eval("\$showtodolastedit = \"".$templates->get("todolist_edited")."\";");
	}
	
	$showtodotitle = $title;
	$showtododate = $time." - ".$time2;
	$showtodoprio = $priority;
	$showtododone = $done;
	$showtodostatus = $status;
	$showtodofrom = "<a href='member.php?action=profile&uid={$nameid}'>{$formattedname}</a>";
	$showtodoaction = $mod_todo;
	$showtodomess = $message;
	$showtodoback = "<a href='todolist.php'>{$lang->back_showtodo}</a>";
	
	eval("\$todolist_show = \"".$templates->get("todolist_show")."\";");
	output_page($todolist_show);
} elseif ($mybb->input['action'] == 'submit') {
	//show the form
	if ($mybb->input['title'] == '') {
		add_breadcrumb("{$lang->title_overview}: {$mybb->settings['todo_name']}", "todolist.php");
		add_breadcrumb($lang->add_todo, "todolist.php?action=submit");
		$codebuttons = build_mycode_inserter();
		eval("\$todolist_add = \"".$templates->get("todolist_add")."\";");
		output_page($todolist_add);
	} else {
		$insert = array(
			"date" => TIME_NOW,
			"nameid" => (int)$mybb->user['uid'],
			"name" => $db->escape_string($mybb->user['username']),
			"title" => $db->escape_string($mybb->input['title']),
			"priority" => $db->escape_string($mybb->input['priority']),
			"message" => $db->escape_string($mybb->input['message']),
			"status" => "wait",
			"done" => "0 done"
		);
		$db->insert_query("todolist", $insert);
		redirect("todolist.php", $lang->added_todo);
	}
} elseif ($mybb->input['action'] == 'delete') {
	$id = (int)$mybb->input['id'];
	$db->delete_query("todolist", "id='{$id}'");
	redirect("todolist.php", $lang->deleted_todo);
} elseif ($mybb->input['action'] == 'edit') {
	$id = (int)$mybb->input['id'];
	if(isset($id)) {
		add_breadcrumb("{$lang->title_overview}: {$mybb->settings['todo_name']}", "todolist.php");
		$query = $db->simple_select('todolist', '*', "id='{$id}'");
		$row = $db->fetch_array($query);

		$id = $row['id'];
		$title = $row['title'];
		add_breadcrumb("{$lang->show_showtodo}: {$title}", "todolist.php?action=show&id=$id");
		add_breadcrumb($lang->edit_edittodo, "todolist.php?action=edit&id={$row[id]}");
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
		
		$codebuttons = build_mycode_inserter();
		
		eval("\$todolist_edit = \"".$templates->get("todolist_edit")."\";");
		output_page($todolist_edit);
	}
} elseif($mybb->input['action'] == 'submit-edit') {
	$id = (int)$mybb->input['id'];

	$update_array = array(
		"title" => $db->escape_string($mybb->input['title']),
		"done" => $db->escape_string($mybb->input['done']),
		"status" => $db->escape_string($mybb->input['status']),
		"priority" => $db->escape_string($mybb->input['priority']),
		"message" => $db->escape_string($mybb->input['message']),
		"lasteditor" => $db->escape_string($mybb->user['username']),
		"lasteditorid" => (int)$mybb->user['uid'],
		"lastedit" => TIME_NOW
	);
	$db->update_query("todolist", $update_array, "id='{$id}'");
	redirect("todolist.php?action=show&id=$id", $lang->edited_todo);
}
?>