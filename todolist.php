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
		
		$all = 0; $solved = 0; $percent = 0;
		$aquery = $db->simple_select("todolist", "done", "pid={$row['id']}");
		while($t = $db->fetch_array($aquery)) {
			++$all;
			if($t['done'] == 100)
			    ++$solved;
			$percent = $percent + $t['done'];
		}
		if($all != 0) {
			$percent = (int) $percent / $all;
			if($percent < 100)
				$done = "<img src=\"images/spinner.gif\" border=\"0\" /> {$percent}% ({$solved}/{$all})";
			else
				$done = "<img src=\"images/todolist/done.png\" border=\"0\" /> {$percent}% ({$solved}/{$all})";
		} else
			$done = "-";
		
		$row['title'] = htmlspecialchars($row['title']);
		$row['description'] = htmlspecialchars($row['description']);
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
	add_breadcrumb(htmlspecialchars($project['title']), "todolist.php?action=show_project&id={$id}");

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
	$multipage = multipage($num, $mybb->settings['todo_per_page'], $page, "todolist.php?action=show_project&id={$id}");

	$query = $db->simple_select("todolist", "*", "pid={$id}", array("order_by" => "date", "order_dir" => "DESC", "limit_start" => $start, "limit" => $mybb->settings['todo_per_page']));
	$todo = "";
	while($row = $db->fetch_array($query)) {
		$row['title'] = htmlspecialchars($row['title']);
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
	add_breadcrumb(htmlspecialchars($project['title']), "todolist.php?action=show_project&id={$row['pid']}");
	add_breadcrumb($lang->show_showtodo.": ".htmlspecialchars($row['title']), "todolist.php?action=show&id={$id}");

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

	$row['title'] = htmlspecialchars($row['title']);

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
	
	$row['version'] = htmlspecialchars($row['version']);
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
				"priority" => $db->escape_string($mybb->input['priority']),
				"version" => $db->escape_string($mybb->input['version'])
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
		$version = $mybb->input['version'];
	} else {
		$priority_check['normal'] = "selected=\"selected\"";
		$title = ""; $message = "";
		$version = "";
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
	if(!isset($mybb->input['id']))
	    header("Location: {$mybb->settings['bburl']}/todolist.php");
	$id=(int)$mybb->input['id'];
	$query = $db->simple_select("todolist", "pid", "id={$id}");
	if(!todo_has_permission($db->fetch_field($query, "pid"), "can_edit"))
	    todo_no_permission();

	add_breadcrumb($lang->title_overview.": ".$mybb->settings['todo_name'], "todolist.php");
	add_breadcrumb($lang->delete_todo, "todolist.php?action=delete&id={$id}");

	if($mybb->input['no'])
	    header("Location: {$mybb->settings['bburl']}/todolist.php?action=show&id={$id}");
	else {
		if($mybb->request_method == "post") {
			$db->delete_query("todolist", "id='{$id}'");
			redirect("todolist.php", $lang->deleted_todo);
		} else {
			eval("\$todolist_confirm = \"".$templates->get("todolist_confirm")."\";");
			output_page($todolist_confirm);
		}
	}
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
				"done" => $db->escape_string($mybb->input['done']),
				"version" => $db->escape_string($mybb->input['version'])
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

		$title = htmlspecialchars($mybb->input['title']);
		$message = $mybb->input['message'];
		$assign = $mybb->input['assign'];
		$version = $mybb->input['version'];
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
		$title = htmlspecialchars($row['title']);
		$assign = $row['assign'];
		$version = $row['version'];
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
} elseif($mybb->input['action'] == "search") {
	add_breadcrumb($lang->title_overview.": ".$mybb->settings['todo_name'], "todolist.php");
	add_breadcrumb($lang->search, "todolist.php?action=search");

    if($mybb->input['string'])
	    $string = $mybb->input['string'];
	else
		$string = "";

    if($mybb->input['creator'])
	    $creator = $mybb->input['creator'];
	else
		$creator = "";

    if($mybb->input['status'])
	    $status = $mybb->input['status'];
	else
		$status = array("wait", "development", "resolved", "feedback", "closed");

	if($mybb->input['project'])
	    $project = $mybb->input['project'];
	else
		$project = array();

	if($mybb->input['assign'])
	    $assign = $mybb->input['assign'];
	else
		$assign = "";

	if($mybb->input['priority'])
	    $priority = $mybb->input['priority'];
	else
		$priority = array("low", "normal", "high");

	if($mybb->input['version'])
	    $version = $mybb->input['version'];
	else
		$version = "";

	if($mybb->input['search'] == "do") {
		//Let's search :O
		$where = array();
		$url = "todolist.php?action=search&search=do";
		
		if($string != "") {
		    $where[] = "(title LIKE '%".$db->escape_string($string)."%' OR message LIKE '%".$db->escape_string($string)."%')";
			$url .= "&string=".urlencode($string);
		}

		if($creator != "") {
		    $where[] = "name = '".$db->escape_string($creator)."'";
			$url .= "&creator=".urlencode($creator);
		}
		
		$where[] = "status IN ('".str_replace(",", "','", $db->escape_string(implode(",", $status)))."')";
		foreach($status as $st)
		    $url .= "&status[]=".urlencode($st);
		
		if(!empty($project)){
			foreach($project as $pr) {
				if(!todo_has_permission($pr, "can_see"))
				    continue;
			    $url .= "&project[]=".urlencode($pr);
			    $npr[] = $pr;
			}
			$project = $npr;
			$where[] = "pid IN ('".str_replace(",", "','", $db->escape_string(implode(",", $project)))."')";
		}

		if($assign != "") {
			$aid = $db->fetch_field($db->simple_select("users", "uid", "username='".$db->escape_string($assign)."'"), "uid");
		    $where[] = "assign = '".$db->escape_string($aid)."'";
		    $url .= "&assign=".urlencode($assign);
		}

		$where[] = "priority IN ('".str_replace(",", "','", $db->escape_string(implode(",", $priority)))."')";
		foreach($priority as $pr)
		    $url .= "&priority[]=".urlencode($pr);

		if($version != "") {
		    $where[] = "version = '".$db->escape_string($version)."'";
			$url .= "&version=".urlencode($version);
		}

		$where = implode(" AND ", $where);
		
		$page = (int)$mybb->input['page'];
		if($page > 0)
			$start = ($page-1) *$mybb->settings['todo_per_page'];
		else {
			$start = 0;
			$page = 1;
		}
	
		$query = "SELECT * FROM ".TABLE_PREFIX."todolist WHERE {$where} ORDER BY date DESC";
		$nquery = $db->query($query);

		$num = $db->num_rows($nquery);
		$multipage = multipage($num, $mybb->settings['todo_per_page'], $page, $url);

		$query = $db->query($query." LIMIT {$start}, {$mybb->settings['todo_per_page']}");
		$resulttable = "";
		while($row = $db->fetch_array($query)) {
			$prname = $db->fetch_field($db->simple_select("todolist_projects", "title", "id={$row['pid']}"), "title");
			$date = my_date($mybb->settings['dateformat'], $row['date'])." - ".my_date($mybb->settings['timeformat'], $row['date']);
			$group = $db->fetch_field($db->simple_select("users", "usergroup", "uid={$row['nameid']}"), "usergroup");
			$formattedname = format_name($row['name'], $group);
			$from = build_profile_link($formattedname, $row['nameid']);

			if($row['assign'] != 0) {
				$sassign = get_user($row['assign']);
				$formattedname = format_name($sassign['username'], $sassign['usergroup']);
				$sassign = build_profile_link($formattedname, $sassign['uid']);
			} else {
				$sassign = $lang->assign_none;
			}

			if($row['priority'] == 'normal') {
				$spriority = "<img src=\"images/todolist/norm_prio.png\" border=\"0\" /> {$lang->normal_priority}";
			} elseif($row['priority'] == 'high') {
				$spriority = "<img src=\"images/todolist/high_prio.gif\" border=\"0\" /> {$lang->high_priority}";
			} elseif($row['priority'] == 'low') {
				$spriority = "<img src=\"images/todolist/low_prio.gif\" border=\"0\" /> {$lang->low_priority}";
			}
			
			if($row['status'] == 'wait') {
				$sstatus = "<img src=\"images/todolist/waiting.png\" border=\"0\" /> {$lang->status_wait}";
			} elseif($row['status'] == 'development') {
				$sstatus = "<img src=\"images/todolist/development.png\" border=\"0\" /> {$lang->status_dev}";
			} elseif($row['status'] == 'feedback') {
				$sstatus = "<img src=\"images/todolist/feedback.png\" border=\"0\" /> {$lang->status_feed}";
			} elseif($row['status'] == 'resolved') {
				$sstatus = "<img src=\"images/icons/exclamation.gif\" border=\"0\" /> {$lang->status_resolved}";
			} elseif($row['status'] == 'closed') {
				$sstatus = "<img src=\"images/todolist/lock.png\" border=\"0\" /> {$lang->status_closed}";
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

			eval("\$resulttable .= \"".$templates->get("todolist_search_resulttable")."\";");
		}
		if($resulttable == "")
			eval("\$resulttable = \"".$templates->get("todolist_search_resulttable_nothing")."\";");

		eval("\$results = \"".$templates->get("todolist_search_results")."\";");
	}

	$query = $db->simple_select("todolist_searchs", "*", "", array("order_by" => "title"));
	$searches = "";
	$count = 0;
	$sarray = array();
	while($row = $db->fetch_array($query)) {
		if(strpos("project", $row['url'])) {
			$start = strpos("project", $row['url']) +7;
			$end = strpos("&", $row['url'], $start);
			$projects = explode(",", substr($row['url'], $start, $end));
			$can_view = false;
			foreach($projects as $pr) {
				if(todo_has_permission($pr))
				    $can_view = true;
			}
			if(!$can_view)
			    continue;
		}
		++$count;

		$sarray[$count] = $row;
		
		if($count == 5) {
			$searches .= "<tr class=\"trow1\">\n";
			foreach($sarray as $s) {
				$searches .= "<td style=\"width: 20%;\"><a href=\"{$s['url']}\">".htmlspecialchars($s['title'])."</a></td>\n";
			}
			$searches .= "</tr>\n";
			$count = 0;
			$sarray = array();
		}
	}
	if($count != 0) {
		$searches .= "<tr class=\"trow1\">\n";
		foreach($sarray as $s) {
			$searches .= "<td><a href=\"".rawurlencode($s['url'])."\">".htmlspecialchars($s['title'])."</a></td>\n";
		}
		for($i = sizeof($sarray); $i < 5; ++$i) {
			$searches .= "<td></td>\n";
		}
		$searches .= "</tr>\n";
	}
	if($searches != "")
		eval("\$searches = \"".$templates->get("todolist_searches")."\";");	    

	$priority_check = array("high" => "", "normal" => "", "low" => "");
	$status_check = array("wait" => "", "development" => "", "feedback" => "", "resolved" => "", "closed" => "");
	foreach($priority as $pr)
	    $priority_check[$pr] = "selected=\"selected\"";
	foreach($status as $st)
	    $status_check[$st] = "selected=\"selected\"";

	$query = $db->simple_select("todolist_projects", "*", "", array("order_by" => "title"));
	$projects = "";
	while($row = $db->fetch_array($query)) {
		if(!todo_has_permission($row['id'], "can_see"))
		    continue;
		if(in_array($row['id'], $project))
		    $projects .= "<option value=\"{$row['id']}\" selected=\"selected\">".htmlspecialchars($row['title'])."</option>";
		else
		    $projects .= "<option value=\"{$row['id']}\">".htmlspecialchars($row['title'])."</option>";
	}

	$string = htmlspecialchars($string); $creator = htmlspecialchars($creator); $assign = htmlspecialchars($assign);
	eval("\$search = \"".$templates->get("todolist_search")."\";");
	output_page($search);
} elseif($mybb->input['action'] == "new") {
	add_breadcrumb($lang->title_overview.": ".$mybb->settings['todo_name'], "todolist.php");
	add_breadcrumb($lang->new, "todolist.php?action=new");

	$fetch = 20;
	$fetched = array();
	//Fetch new added
	$query = $db->simple_select("todolist", "*", "", array("order_by" => "date", "order_dir" => "desc", "limit" => $fetch));
	$count = 0; $lastdate = 0;
	while($row = $db->fetch_array($query)) {
		++$count;
		$fetched[$row['id']] = $row;
		if($count == $fetch)
		    $lastdate = $row['date'];
	}
	//Fetch last edited
	$query = $db->simple_select("todolist", "*", "lastedit > '{$lastdate}'", array("order_by" => "lastedit", "order_dir" => "desc", "limit" => $fetch));
	while($row = $db->fetch_array($query))
		$fetched[$row['id']] = $row;
	
	uasort($fetched, "todo_sort_new");
	array_splice($fetched, $fetch);
	
	foreach($fetched as $row) {
		if(!todo_has_permission($row['pid']))
		    continue;
		$title = "<a href=\"todolist.php?action=show&id={$row['id']}\">".htmlspecialchars($row['title'])."</a>";
		if($row['nameid'] != "")
			$group = $db->fetch_field($db->simple_select("users", "usergroup", "uid={$row['nameid']}"), "usergroup");
		else
			$group = "";
		$formattedname = format_name($row['name'], $group);
		$from = build_profile_link($formattedname, $row['nameid']);

		$pquery = $db->simple_select("todolist_projects", "*", "id={$row['pid']}");
		$project = $db->fetch_array($pquery);
		$project = "<a href=\"todolist.php?action=show_project&id={$project['id']}\">".htmlspecialchars($project['title'])."</a>";

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
		eval("\$news .= \"".$templates->get("todolist_new_table")."\";");
	}

	eval("\$new = \"".$templates->get("todolist_new")."\";");
	output_page($new);
}

function todo_sort_new($a, $b)
{
	if($a['lastedit'] > $a['date'])
	    $adate = $a['lastedit'];
	else
		$adate = $a['date'];

	if($b['lastedit'] > $b['date'])
	    $bdate = $b['lastedit'];
	else
		$bdate = $b['date'];
	
	if($adate == $bdate)
	    return 1;
	return ($adate < $bdate) ? 2 : 0;

}
?>