<?php
define("IN_MYBB", 1);
define('THIS_SCRIPT', 'todolist.php');

$templatelist = "todolist, todolist_show, todolist_add, todolist_edit, todolist_table, todolist_table_no_results, todolist_mod, todolist_mod_table, todolist_edited";
require_once "global.php";

$lang->load('todolist');

if(!function_exists("todolist_info") || $mybb->settings['todo_activate'] == '0')
	error($lang->offline);

if(!todo_has_any_permission())
	todo_no_permission();

if ($mybb->input['action'] == "") {
	add_breadcrumb($lang->title_overview.": ".$mybb->settings['todo_name'], "todolist.php");

	$page = (int)$mybb->input['page'];
	if($page > 0)
		$start = ($page-1) *$mybb->settings['todo_per_page'];
	else {
		$start = 0;
		$page = 1;
	}
	$query = $db->simple_select("todolist_projects", "COUNT(id) AS count");
	$num = $db->fetch_field($query, "count");
	$multipage = multipage($num, $mybb->settings['todo_per_page'], $page, "todolist.php");

	$query = $db->simple_select("todolist_projects", "*", "", array("order_by" => "title", "limit_start" => $start, "limit" => $mybb->settings['todo_per_page']));
	$todo = "";
	while($row = $db->fetch_array($query)) {
		if(!todo_has_permission($row['id'], "can_see"))
		    continue;
		eval("\$todo .= \"".$templates->get("todolist_projects_table")."\";");
	}

	if ($todo == '') {
		eval("\$todo = \"".$templates->get("todolist_projects_table_no_results")."\";");
	}

	eval("\$todolist .= \"".$templates->get("todolist_projects")."\";");
	output_page($todolist);
} elseif ($mybb->input['action'] == "show_project") {
	$id = (int)$mybb->input['id'];
	if(!todo_has_permission($id, "can_see"))
	    todo_no_permission();
	$query = $db->simple_select("todolist_projects", "*", "id={$id}");
	$project = $db->fetch_array($query);
	add_breadcrumb($lang->title_overview.": ".$mybb->settings['todo_name'], "todolist.php");
	add_breadcrumb($project['title'], "todolist.php?action=show_project&id={$id}");

	if(todo_has_permission($id, "can_add"))
		$addtodo = "<strong><img src=\"images/todolist/add.png\" /> <a href=\"todolist.php?action=add&pid={$id}\">{$lang->add_todo}</a></strong>";

	$page = (int)$mybb->input['page'];
	if($page > 0)
		$start = ($page-1) *$mybb->settings['todo_per_page'];
	else {
		$start = 0;
		$page = 1;
	}
	$query = $db->simple_select("todolist", "COUNT(id) AS count", "pid={$id}");
	$num = $db->fetch_field($query, "count");
	$multipage = multipage($num, $mybb->settings['todo_per_page'], $page, "todolist.php");

	$query = $db->simple_select("todolist", "*", "pid={$id}", array("order_by" => "date", "order_dir" => "DESC", "limit_start" => $start, "limit" => $mybb->settings['todo_per_page']));
	$todo = "";
	while($row = $db->fetch_array($query)) {
		if($row['nameid'] != "")
			$group = $db->fetch_field($db->simple_select("users", "usergroup", "uid={$row['nameid']}"), "usergroup");
		else
			$group = "";
		if($row['assign'] != 0) {
			$assign = get_user($row['assign']);
			$formattedname = format_name($assign['username'], $assign['usergroup']);
			$assign = build_profile_link($formattedname, $assign['uid']);
		} else {
			$assign = $lang->assign_none;
		}
			
		if($row['priority'] == 'normal') {
			$priority = "<img src=\"images/todolist/norm_prio.png\" border=\"0\" /> {$lang->normal_priority}";
		} elseif($row['priority'] == 'high') {
			$priority = "<img src=\"images/todolist/high_prio.gif\" border=\"0\" /> {$lang->high_priority}";
		} elseif($row['priority'] == 'low') {
			$priority = "<img src=\"images/todolist/low_prio.gif\" border=\"0\" /> {$lang->low_priority}";
		}
		
		if($row['status'] == 'wait') {
			$status = "<img src=\"images/todolist/waiting.png\" border=\"0\" /> {$lang->status_wait}";
		} elseif($row['status'] == 'development') {
			$status = "<img src=\"images/todolist/development.png\" border=\"0\" /> {$lang->status_dev}";
		} elseif($row['status'] == 'feedback') {
			$status = "<img src=\"images/todolist/feedback.png\" border=\"0\" /> {$lang->status_feed}";
		} elseif($row['status'] == 'resolved') {
			$status = "<img src=\"images/icons/exclamation.gif\" border=\"0\" /> {$lang->status_resolved}";
		} elseif($row['status'] == 'closed') {
			$status = "<img src=\"images/todolist/lock.png\" border=\"0\" /> {$lang->status_closed}";
		}
		
		if($row['done'] == '0') {
			$done = "<img src=\"images/spinner.gif\" border=\"0\" /> {$lang->done_0}";
		} elseif($row['done'] == '25') {
			$done = "<img src=\"images/spinner.gif\" border=\"0\" /> {$lang->done_25}";
		} elseif($row['done'] == '50') {
			$done = "<img src=\"images/spinner.gif\" border=\"0\" /> {$lang->done_50}";
		} elseif($row['done'] == '75') {
			$done = "<img src=\"images/spinner.gif\" border=\"0\" /> {$lang->done_75}";
		} elseif($row['done'] == '100') {
			$done = "<img src=\"images/todolist/done.png\" border=\"0\" /> {$lang->done_100}";
		}
		
		$date = my_date($mybb->settings['dateformat'], $row['date'])." - ".my_date($mybb->settings['timeformat'], $row['date']);
		
		$formattedname = format_name($row['name'], $group);
		$owner = build_profile_link($formattedname, $row['nameid']);

		if(todo_has_permission($id, "can_edit")) {
			$mod_todo = "- ";
			eval("\$mod_todo .= \"".$templates->get("todolist_mod")."\";");
		}
		
		eval("\$todo .= \"".$templates->get("todolist_table")."\";");		
	}
	
	if ($todo == '') {
		eval("\$todo = \"".$templates->get("todolist_table_no_results")."\";");
	}
	
	eval("\$todolist .= \"".$templates->get("todolist")."\";");
	output_page($todolist);
} elseif ($mybb->input['action'] == 'show') {
	$id = (int)$mybb->input['id'];
	$query = $db->simple_select('todolist', '*', "id='{$id}'");
	$row = $db->fetch_array($query);
	if(!todo_has_permission($row['pid'], "can_see"))
	    todo_no_permission();
	$query = $db->simple_select("todolist_projects", "*", "id={$row['pid']}");
	$project = $db->fetch_array($query);
	add_breadcrumb($lang->title_overview.": ".$mybb->settings['todo_name'], "todolist.php");
	add_breadcrumb($project['title'], "todolist.php?action=show_project&id={$id}");
	add_breadcrumb($lang->show_showtodo.": ".$row['title'], "todolist.php?action=show&id={$id}");

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

	if(todo_has_permission($row['pid'], "can_edit")) {
		eval("\$mod_todo = \"".$templates->get("todolist_mod")."\";");
		eval("\$mod_todo = \"".$templates->get("todolist_mod_table")."\";");
	}

	$message = $parser->parse_message($row['message'], $parser_options);
	if($row['lasteditorid'] != "")
		$editorgroup = $db->fetch_field($db->simple_select("users", "usergroup", "uid={$row['lasteditorid']}"), "usergroup");
	else
		$editorgroup = "";
	if($row['nameid'] != "")
		$group = $db->fetch_field($db->simple_select("users", "usergroup", "uid={$row['nameid']}"), "usergroup");
	else
		$group = "";
	if($row['assign'] != 0) {
		$assign = get_user($row['assign']);
		$formattedname = format_name($assign['username'], $assign['usergroup']);
		$assign = build_profile_link($formattedname, $assign['uid']);
	} else {
		$assign = $lang->assign_none;
	}
	
	
	if($row['priority'] == 'normal') {
		$priority = "<img src=\"images/todolist/norm_prio.png\" border=\"0\" /> {$lang->normal_priority}";
	} elseif($row['priority'] == 'high') {
		$priority = "<img src=\"images/todolist/high_prio.gif\" border=\"0\" /> {$lang->high_priority}";
	} elseif($row['priority'] == 'low') {
		$priority = "<img src=\"images/todolist/low_prio.gif\" border=\"0\" /> {$lang->low_priority}";
	}
	
	if($row['status'] == 'wait') {
		$status = "<img src=\"images/todolist/waiting.png\" border=\"0\" /> {$lang->status_wait}";
	} elseif($row['status'] == 'development') {
		$status = "<img src=\"images/todolist/development.png\" border=\"0\" /> {$lang->status_dev}";
	} elseif($row['status'] == 'feedback') {
		$status = "<img src=\"images/todolist/feedback.png\" border=\"0\" /> {$lang->status_feed}";
	} elseif($row['status'] == 'resolved') {
		$status = "<img src=\"images/icons/exclamation.gif\" border=\"0\" /> {$lang->status_resolved}";
	} elseif($row['status'] == 'closed') {
		$status = "<img src=\"images/todolist/lock.png\" border=\"0\" /> {$lang->status_closed}";
	}
	
	if($row['done'] == '0') {
		$done = "<img src=\"images/spinner.gif\" border=\"0\" /> {$lang->done_0}";
	} elseif($row['done'] == '25') {
		$done = "<img src=\"images/spinner.gif\" border=\"0\" /> {$lang->done_25}";
	} elseif($row['done'] == '50') {
		$done = "<img src=\"images/spinner.gif\" border=\"0\" /> {$lang->done_50}";
	} elseif($row['done'] == '75') {
		$done = "<img src=\"images/spinner.gif\" border=\"0\" /> {$lang->done_75}";
	} elseif($row['done'] == '100') {
		$done = "<img src=\"images/todolist/done.png\" border=\"0\" /> {$lang->done_100}";
	}
	
	$date = my_date($mybb->settings['dateformat'], $row['date'])." - ".my_date($mybb->settings['timeformat'], $row['date']);
	$editdate = my_date($mybb->settings['dateformat'], $row['lastedit'])." - ".my_date($mybb->settings['timeformat'], $row['lastedit']);

	$formattedname = format_name($row['name'], $group);
	$from = build_profile_link($formattedname, $row['nameid']);
	$formattedname = format_name($row['lasteditor'], $editorgroup);
	$lasteditor = build_profile_link($formattedname, $row['lasteditorid']);

	$lastedit = "";
	if($lasteditor != '' && $row['lasteditorid'] != 0 && $row['lastedit'] != '') {
		eval("\$lastedit = \"".$templates->get("todolist_edited")."\";");
	}
	
	$back = "<a href='todolist.php?action=show_project&id={$row['pid']}'>{$lang->back_showtodo}</a>";
	
	eval("\$todolist_show = \"".$templates->get("todolist_show")."\";");
	output_page($todolist_show);
} elseif ($mybb->input['action'] == 'add') {
	if(!todo_has_permission($mybb->input['pid'], "can_add"))
	    todo_no_permission();
	if($mybb->request_method == "post") {
		verify_post_check($mybb->input['my_post_key'], false);
		if(!isset($mybb->input['title']) || $mybb->input['title'] == "")
		    $errors[] = $lang->no_title;
	
		if(!isset($mybb->input['priority']) || $mybb->input['priority'] == "")
		    $errors[] = $lang->no_priority;
	
		if(!isset($mybb->input['message']) || $mybb->input['message'] == "")
		    $errors[] = $lang->no_message;
		
		if(!isset($errors)) {
			$insert = array(
				"pid" => (int)$mybb->input['pid'],
				"title" => $db->escape_string($mybb->input['title']),
				"message" => $db->escape_string($mybb->input['message']),
				"name" => $db->escape_string($mybb->user['username']),
				"nameid" => (int)$mybb->user['uid'],
				"date" => TIME_NOW,
				"assign" => (int)$mybb->input['assign'],
				"priority" => $db->escape_string($mybb->input['priority'])
			);
			$id = $db->insert_query("todolist", $insert);
			
			if($mybb->input['assign'] != 0 && $mybb->settings['todo_pm_notify']) {
				$assign = get_user($mybb->input['assign']);
				$profil = $mybb->settings['bburl']."/".get_profile_link($mybb->user['uid']);
				$todo = $mybb->settings['bburl']."/todolist.php?action=show&id=".$id;
				$message = $lang->sprintf($lang->notify_message, $assign['username'], $profil, $mybb->user['username'], $todo, $mybb->input['title']);
			    todo_pm($mybb->input['assign'], $lang->notify_subject, $message);
			}

			redirect("todolist.php?action=show&id={$id}", $lang->added_todo);
		}
	}
	
	$priority_check = array("high" => "", "normal" => "", "low" => "");
	if(isset($errors))
	{
		$errors = inline_error($errors);
		if($mybb->input['priority'] == 'normal')
			$priority_check['normal'] = "selected=\"selected\"";
		elseif($mybb->input['priority'] == 'high')
			$priority_check['high'] = "selected=\"selected\"";
		elseif($mybb->input['priority'] == 'low')
			$priority_check['low'] = "selected=\"selected\"";
		$title = $mybb->input['title'];
		$message = $mybb->input['message'];
	} else {
		$priority_check['normal'] = "selected=\"selected\"";
		$title = ""; $message = "";
	}
	add_breadcrumb($lang->title_overview.": ".$mybb->settings['todo_name'], "todolist.php");
	add_breadcrumb($lang->add_todo, "todolist.php?action=add");
	$query = $db->simple_select("users", "uid, username", "", array("order_by" => "username"));
	$userselect = "<option value=\"0\">-</option>";
	while($user = $db->fetch_array($query)) {
		if(isset($mybb->input['assign']) && $mybb->input['assign'] == $user['uid'])
		    $userselect .= "<option value=\"{$user['uid']}\" selected=\"selected\">{$user['username']}</option>";
		else
		    $userselect .= "<option value=\"{$user['uid']}\">{$user['username']}</option>";
	}
	$codebuttons = build_mycode_inserter();
	eval("\$todolist_add = \"".$templates->get("todolist_add")."\";");
	output_page($todolist_add);
} elseif ($mybb->input['action'] == 'delete') {
	$id = (int)$mybb->input['id'];
	$query = $db->simple_select("todolist", "pid", "id={$id}");
	if(!todo_has_permission($db->fetch_field($query, "pid"), "can_edit"))
	    todo_no_permission();
	$db->delete_query("todolist", "id='{$id}'");
	redirect("todolist.php", $lang->deleted_todo);
} elseif ($mybb->input['action'] == 'edit') {
	if(!isset($mybb->input['id']))
	    header("Location: {$mybb->settings['bburl']}/todolist.php");
	$id = (int)$mybb->input['id'];
	$query = $db->simple_select('todolist', '*', "id='{$id}'");
	$row = $db->fetch_array($query);
	if(!todo_has_permission($row['pid'], "can_edit"))
	    todo_no_permission();
	
	if($mybb->request_method == "post") {
		verify_post_check($mybb->input['my_post_key'], false);
		if(!isset($mybb->input['title']) || $mybb->input['title'] == "")
		    $errors[] = $lang->no_title;

		if(!isset($mybb->input['done']) || $mybb->input['done'] == "")
		    $errors[] = $lang->no_done;

		if(!isset($mybb->input['status']) || $mybb->input['status'] == "")
		    $errors[] = $lang->no_status;

		if(!isset($mybb->input['priority']) || $mybb->input['priority'] == "")
		    $errors[] = $lang->no_priority;

		if(!isset($mybb->input['message']) || $mybb->input['message'] == "")
		    $errors[] = $lang->no_message;

		if(!isset($errors)) {
			$update_array = array(
				"title" => $db->escape_string($mybb->input['title']),
				"message" => $db->escape_string($mybb->input['message']),
				"assign" =>	(int)$mybb->input['assign'],
				"lasteditor" => $db->escape_string($mybb->user['username']),
				"lasteditorid" => (int)$mybb->user['uid'],
				"lastedit" => TIME_NOW,
				"priority" => $db->escape_string($mybb->input['priority']),
				"status" => $db->escape_string($mybb->input['status']),
				"done" => $db->escape_string($mybb->input['done'])
			);
			$db->update_query("todolist", $update_array, "id='{$id}'");

			if($mybb->input['assign'] != $row['assign'] && $mybb->input['assign'] != 0 && $mybb->settings['todo_pm_notify']) {
				$assign = get_user($mybb->input['assign']);
				$profil = $mybb->settings['bburl']."/".get_profile_link($mybb->user['uid']);
				$todo = $mybb->settings['bburl']."/todolist.php?action=show&id=".$id;
				$message = $lang->sprintf($lang->notify_message, $assign['username'], $profil, $mybb->user['username'], $todo, $mybb->input['title']);
			    todo_pm($mybb->input['assign'], $lang->notify_subject, $message);
			}

			redirect("todolist.php?action=show&id={$id}", $lang->edited_todo);
		}
	}

	add_breadcrumb($lang->title_overview.": ".$mybb->settings['todo_name'], "todolist.php");

	$priority_check = array("high" => "", "normal" => "", "low" => "");
	$status_check = array("wait" => "", "development" => "", "feedback" => "", "resolved" => "", "closed" => "");
	$done_check = array("0" => "", "25" => "", "50" => "", "75" => "", "100" => "");
	if(isset($errors))
	{
		$errors = inline_error($errors);
		
		if($mybb->input['priority'] == 'normal')
			$priority_check['normal'] = "selected=\"selected\"";
		elseif($mybb->input['priority'] == 'high')
			$priority_check['high'] = "selected=\"selected\"";
		elseif($mybb->input['priority'] == 'low')
			$priority_check['low'] = "selected=\"selected\"";

		if($mybb->input['status'] == 'wait')
			$status_check['wait'] = "selected=\"selected\"";
		elseif($mybb->input['status'] == 'development')
			$status_check['development'] = "selected=\"selected\"";
		elseif($mybb->input['status'] == 'feedback')
			$status_check['feedback'] = "selected=\"selected\"";
		elseif($mybb->input['status'] == 'resolved')
			$status_check['resolved'] = "selected=\"selected\"";
		elseif($mybb->input['status'] == 'closed')
			$status_check['closed'] = "selected=\"selected\"";
	
		if($mybb->input['done'] == '0')
			$done_check['0'] = "selected=\"selected\"";
		elseif($mybb->input['done'] == '25')
			$done_check['25'] = "selected=\"selected\"";
		elseif($mybb->input['done'] == '50')
			$done_check['50'] = "selected=\"selected\"";
		elseif($mybb->input['done'] == '75')
			$done_check['75'] = "selected=\"selected\"";
		elseif($mybb->input['done'] == '100')
			$done_check['100'] = "selected=\"selected\"";

		$title = $mybb->input['title'];
		$message = $mybb->input['message'];
		$assign = $mybb->input['assign'];
	} else {
		$errors = "";
		
		if($row['priority'] == 'normal')
			$priority_check['normal'] = "selected=\"selected\"";
		elseif($row['priority'] == 'high')
			$priority_check['high'] = "selected=\"selected\"";
		elseif($row['priority'] == 'low')
			$priority_check['low'] = "selected=\"selected\"";

		if($row['status'] == 'wait')
			$status_check['wait'] = "selected=\"selected\"";
		elseif($row['status'] == 'development')
			$status_check['development'] = "selected=\"selected\"";
		elseif($row['status'] == 'feedback')
			$status_check['feedback'] = "selected=\"selected\"";
		elseif($row['status'] == 'resolved')
			$status_check['resolved'] = "selected=\"selected\"";
		elseif($row['status'] == 'closed')
			$status_check['closed'] = "selected=\"selected\"";

		if($row['done'] == '0')
			$done_check['0'] = "selected=\"selected\"";
		elseif($row['done'] == '25')
			$done_check['25'] = "selected=\"selected\"";
		elseif($row['done'] == '50')
			$done_check['50'] = "selected=\"selected\"";
		elseif($row['done'] == '75')
			$done_check['75'] = "selected=\"selected\"";
		elseif($row['done'] == '100')
			$done_check['100'] = "selected=\"selected\"";

		$message = $row['message'];
		$title = $row['title'];
		$assign = $row['assign'];
	}

	add_breadcrumb($lang->show_showtodo.": ".$title, "todolist.php?action=show&id={$id}");
	add_breadcrumb($lang->edit_edittodo, "todolist.php?action=edit&id={$id}");
	
	$query = $db->simple_select("users", "uid, username", "", array("order_by" => "username"));
	$userselect = "<option value=\"0\">-</option>";
	while($user = $db->fetch_array($query)) {
		if($assign == $user['uid'])
		    $userselect .= "<option value=\"{$user['uid']}\" selected=\"selected\">{$user['username']}</option>";
		else
		    $userselect .= "<option value=\"{$user['uid']}\">{$user['username']}</option>";
	}

   	if($row['priority'] == 'normal')
		$priority = "<img src=\"images/todolist/norm_prio.png\" border=\"0\" /> {$lang->normal_priority}";
	elseif($row['priority'] == 'high')
		$priority = "<img src=\"images/todolist/high_prio.gif\" border=\"0\" /> {$lang->high_priority}";
	elseif($row['priority'] == 'low')
		$priority = "<img src=\"images/todolist/low_prio.gif\" border=\"0\" /> {$lang->low_priority}";
	
	if($row['status'] == 'wait')
		$status = "<img src=\"images/todolist/waiting.png\" border=\"0\" /> {$lang->status_wait}";
	elseif($row['status'] == 'development')
		$status = "<img src=\"images/todolist/development.png\" border=\"0\" /> {$lang->status_dev}";
	elseif($row['status'] == 'feedback')
		$status = "<img src=\"images/todolist/feedback.png\" border=\"0\" /> {$lang->status_feed}";
	elseif($row['status'] == 'resolved')
		$status = "<img src=\"images/icons/exclamation.gif\" border=\"0\" /> {$lang->status_resolved}";
	elseif($row['status'] == 'closed')
		$status = "<img src=\"images/todolist/lock.png\" border=\"0\" /> {$lang->status_closed}";
	
	if($row['done'] == '0')
		$done = "<img src=\"images/spinner.gif\" border=\"0\" /> {$lang->done_0}";
	elseif($row['done'] == '25')
		$done = "<img src=\"images/spinner.gif\" border=\"0\" /> {$lang->done_25}";
	elseif($row['done'] == '50')
		$done = "<img src=\"images/spinner.gif\" border=\"0\" /> {$lang->done_50}";
	elseif($row['done'] == '75')
		$done = "<img src=\"images/spinner.gif\" border=\"0\" /> {$lang->done_75}";
	elseif($row['done'] == '100')
		$done = "<img src=\"images/todolist/done.png\" border=\"0\" /> {$lang->done_100}";
	
	$codebuttons = build_mycode_inserter();	
	eval("\$todolist_edit = \"".$templates->get("todolist_edit")."\";");
	output_page($todolist_edit);
}
?>