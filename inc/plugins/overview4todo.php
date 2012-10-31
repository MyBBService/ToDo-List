<?php
if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("index_start", "overview_load");

function overview4todo_info()
{
    return array(
        "name"            => "Overview f&uuml;r ToDo-Liste",
        "description"    => "F&uuml;gt eine &Uuml;bersicht auf der Startseite hinzu",
        "website"        => "http://mybbservice.de/",
        "author"        => "MyBBService",
        "authorsite"    => "http://mybbservice.de/",
        "version"        => "1.0",
        "guid"             => "",
        "compatibility" => "16*"
    );
}

function overview4todo_install()
{
	global $db, $lang;
	$lang->load('todolist');

	$templatearray = array(
        "title" => "todolist_overview",
        "template" => "<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"clear: both; width: 45%; float: left;\">
	<tr>
		<td class=thead colspan=\"6\"><strong>{\$lang->title_overview}: {\$mybb->settings[\'todo_name\']} - {\$lang->newest}</strong></td>
	</tr>
	<tr class=\"tcat\">
		<td>{\$lang->title_todo}</td>
		<td>{\$lang->project}</td>
		<td>{\$lang->from_todo}</td>
		<td>{\$lang->date_todo}</td>
		<td>{\$lang->done_todo}</td>
		<td>{\$lang->status_todo}</td>
	</tr>
	{\$newest}
</table>
<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"clear: both; width: 45%; float: right;\">
	<tr>
		<td class=thead colspan=\"6\"><strong>{\$lang->title_overview}: {\$mybb->settings[\'todo_name\']} - {\$lang->last_edit}</strong></td>
	</tr>
	<tr class=\"tcat\">
		<td>{\$lang->title_todo}</td>
		<td>{\$lang->project}</td>
		<td>{\$lang->from_todo}</td>
		<td>{\$lang->date_todo}</td>
		<td>{\$lang->done_todo}</td>
		<td>{\$lang->status_todo}</td>
	</tr>
	{\$edits}
</table>",
        "sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
        "title" => "todolist_overview_table",
        "template" => "	<tr class=\"trow1\">
		<td>{\$title}</td>
		<td>{\$project}</td>
		<td>{\$from}</td>
		<td>{\$date}</td>
		<td>{\$done}</td>
		<td>{\$status}</td>
	</tr>",
        "sid" => -2
    );
    $db->insert_query("templates", $templatearray);

    $gid = $db->fetch_field($db->simple_select("settinggroups", "gid", "name='todo'"), "gid");

	//Einstellungen
	$todolist_setting_1 = array(
        "name"           => "todo_number",
        "title"          => "Overview Anzahl",
        "description"    => "Wie viele Elemente sollen angezeigt werden?",
        "optionscode"    => "text",
        "value"          => '5',
        "disporder"      => '6',
        "gid"            => (int)$gid,
    );
	$db->insert_query("settings", $todolist_setting_1);
	rebuild_settings();
}

function overview4todo_is_installed() {
	global $db;
	$query = $db->simple_select("templates", "title", "title='todolist_overview'");
	if($db->num_rows($query) > 0)
		return true;
	return false;
}

function overview4todo_activate()
{
	require MYBB_ROOT."inc/adminfunctions_templates.php";
	find_replace_templatesets("index", "#".preg_quote('{$forums}')."#i", '{$forums}'."\n".'{$todo_overview}');
}

function overview4todo_deactivate()
{
	require MYBB_ROOT."inc/adminfunctions_templates.php";
	find_replace_templatesets("index", "#".preg_quote("\n".'{$todo_overview}')."#i", '');
}

function overview4todo_uninstall()
{
	global $db;

	$db->delete_query("settings", "name='todo_number'");
	rebuild_settings();

	//Delete templates
	$templatearray = array(
		"todolist_overview",
		"todolist_overview_table"
    );
    $deltemplates = implode("','", $templatearray);
	$db->delete_query("templates", "title in ('{$deltemplates}')");
}

function overview_load()
{
	global $db, $mybb, $todo_overview, $templates, $theme, $lang;
	$lang->load("todolist");
	$lang->load("todolist_overview");
	if(!function_exists("todo_has_any_permission") || !todo_has_any_permission())
	    return;
	
	$newest = ""; $edits = "";
	//Neue
	$query = $db->simple_select("todolist", "*", "", array("order_by" => "date", "order_dir" => "desc", "limit" => $mybb->settings['todo_number'], "limit_start" => 0));
	while($row = $db->fetch_array($query)) {
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
		eval("\$newest .= \"".$templates->get("todolist_overview_table")."\";");
	}

	$query = $db->simple_select("todolist", "*", "", array("order_by" => "lastedit", "order_dir" => "desc", "limit" => $mybb->settings['todo_number']));
	while($row = $db->fetch_array($query)) {
		if(!todo_has_permission($row['pid']))
		    continue;
		if($row['lastedit'] == 0)
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

		$date = my_date($mybb->settings['dateformat'], $row['lastedit'])." - ".my_date($mybb->settings['timeformat'], $row['lastedit']);
		eval("\$edits .= \"".$templates->get("todolist_overview_table")."\";");
	}
	eval("\$todo_overview = \"".$templates->get("todolist_overview")."\";");
}
?>