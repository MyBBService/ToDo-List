<?php
if(!defined("IN_MYBB")) {
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function todolist_info()
{
	global $lang;
	$lang->load('todolist');
	return array(
		"name"			=> "ToDo-Liste",
		"description"	=> "Dieses Plugin erstellt eine ToDo Liste, mithilfe Aufgaben in deinem Forum verwaltet werden können<br /><i>Based on ToDo List by FalkenaugeMihawk</i>",
		"website"		=> "http://mybbservice.de",
		"author"		=> "MyBBService",
		"authorsite"	=> "http://mybbservice.de",
		"version"		=> "1.0 Beta",
		"guid"			=> "",
		"compatibility" => "16*",
	);
}


function todolist_install()
{
	global $db, $lang;
	$lang->load('todolist');
	
	$col = $db->build_create_table_collation();
	$db->query("CREATE TABLE `".TABLE_PREFIX."todolist` (
				`id`			int(11)			NOT NULL AUTO_INCREMENT,
				`title`			varchar(50)		NOT NULL,
				`date`			bigint(30)		NOT NULL,
				`name`			varchar(120)	NOT NULL,
				`nameid`		int(10)			NOT NULL,
				`lasteditor`	varchar(120)	NOT NULL DEFAULT '',
				`lasteditorid`	int(10)			NOT NULL DEFAULT '0',
				`lastedit`		bigint(30)		NOT NULL DEFAULT '0',
				`priority`		varchar(6)		NOT NULL,
				`message`		text			NOT NULL,
				`status`		varchar(11)		NOT NULL,
				`done`			varchar(8)		NOT NULL,
	PRIMARY KEY (`id`) ) ENGINE=MyISAM {$col}");

	$templateset = array(
	    "prefix" => "todolist",
	    "title" => "ToDoListe",
    );
	$db->insert_query("templategroups", $templateset);

	$templatearray = array(
        "title" => "todolist",
        "template" => "<html>
<head>
<title>{\$mybb->settings[\'bbname\']} - {\$lang->title_overview}: {\$mybb->settings[\'todo_name\']}</title>
{\$headerinclude}
</head>
<body>
{\$header}
{\$multipage}
<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"clear: both;\">
	<tr>
		<td class=\"thead\" colspan=\"7\"><strong>{\$lang->title_overview}: {\$mybb->settings[\'todo_name\']}</strong></td>
	</tr>
	<tr>
		<td class=tcat>{\$lang->title_todo}</td>
		<td class=tcat>{\$lang->date_todo}</td>
		<td class=tcat>{\$lang->from_todo}</td>
		<td class=tcat>{\$lang->priority_todo}</td>
		<td class=tcat>{\$lang->status_todo}</td>
		<td class=tcat>{\$lang->done_todo}</td>
		<td class=tcat style=\"width:300px;\">{\$lang->action_todo}</td>
	</tr>
	{\$todo}
	<tr class=\"trow1\">
		<td colspan=\"5\">{\$addtodo}</td>
		<td style=\"width:190px;\" colspan=\"2\">{\$lang->moderation_todo}: {\$modgroup}</td>
	</tr>
</table>
{\$multipage}
{\$loggedin}
<br />
{\$footer}
</body>
</html>",
        "sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
        "title" => "todolist_show",
        "template" => "<html>
<head>
<title>{\$mybb->settings[\'bbname\']} - {\$mybb->settings[\'todo_name\']} - {\$lang->show_showtodo}</title>
{\$headerinclude}
</head>
<body>
{\$header}
{\$selectodo}
<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"clear: both;\">
	<tr>
		<td class=thead colspan=\"4\"><strong>{\$lang->title_overview}: {\$mybb->settings[\'todo_name\']} - {\$lang->show_showtodo}</strong></td>
	</tr>
	<tr class=\"trow1\">
		<td style=\"width:200px;\">{\$lang->title_todo}:</td>
		<td>{\$showtodotitle}</td>
	</tr>
	<tr class=\"trow2\">
		<td style=\"width:200px;\">{\$lang->date_todo}:</td>
		<td>{\$showtododate}</td>
	</tr>
	<tr class=\"trow1\">
		<td style=\"width:200px;\">{\$lang->from_todo}:</td>
		<td>{\$showtodofrom}</td>
	</tr>
	<tr class=\"trow2\">
		<td style=\"width:200px;\">{\$lang->priority_todo}:</td>
		<td>{\$showtodoprio}</td>
	</tr>
	<tr class=\"trow1\">
		<td style=\"width:200px;\">{\$lang->done_todo}:</td>
		<td>{\$showtododone}</td>
	</tr>
	<tr class=\"trow2\">
		<td style=\"width:200px;\">{\$lang->status_todo}:</td>
		<td>{\$showtodostatus}</td>
	</tr>
	<tr class=\"trow1\">
		<td style=\"width:200px;\">{\$lang->description_todo}:</td>
		<td>{\$showtodomess}</td>
	</tr>
	{\$showtodoaction}
	{\$showtodolastedit}
	<tr class=\"trow2\">
		<td colspan=\"2\">{\$showtodoback}</td>
	</tr>
</table>
{\$loggedin}
<br />
{\$footer}
</body>
</html>",
        "sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
        "title" => "todolist_add",
        "template" => "<html>
<head>
<title>{\$mybb->settings[\'bbname\']} - {\$mybb->settings[\'todo_name\']} - {\$lang->add_todo}</title>
{\$headerinclude}
</head>
<body>
{\$header}
<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"clear: both;\">
	<tr>
		<td class=thead colspan=2><strong>{\$lang->title_overview}: {\$lang->add_todo}</strong></td>
	</tr>
	<form action=\"\" method=\"post\">
	<tr class=\"trow1\">
		<td style=\"width:100px;\">{\$lang->title_todo}:</td>
		<td><input type=\"text\" name=\"title\" style=\"width:300px;\"/></td>
	</tr>
	<tr class=\"trow1\">
		<td style=\"width:100px;\">{\$lang->priority_todo}:</td>
		<td><select name=\"priority\" style=\"width:100px;\">
			<option value=\"normal\" style=\"background-image:url(images/todolist/norm_prio.png); background-repeat:no-repeat; text-align:center; \">{\$lang->normal_priority}</option>
			<option value=\"high\" style=\"background-image:url(images/todolist/high_prio.gif); background-repeat:no-repeat; text-align:center; \">{\$lang->high_priority}</option>
			<option value=\"low\" style=\"background-image:url(images/todolist/low_prio.gif); background-repeat:no-repeat; text-align:center; \">{\$lang->low_priority}</option>
		</select></td>
	</tr>
	<tr class=\"trow1\">
		<td style=\"width:200px;\">{\$lang->description_todo}:</td>
		<td><textarea name=\"message\" rows=\"6\" cols=\"15\" style=\"width:300px; height:200px;\">$message</textarea></td>
	</tr>
	<tr class=\"trow1\">
		<td colspan=\"2\"><input type=\"submit\" value=\"{\$lang->add_todo}\" style=\"margin-left: 280px; \"/></td>
	</tr>
</table>
{\$footer}
</body>
</html>",
        "sid" => -2
    );
    $db->insert_query("templates", $templatearray);

	$templatearray = array(
        "title" => "todolist_edit",
        "template" => "<html>
<head>
<title>{\$mybb->settings[\'bbname\']} - {\$mybb->settings[\'todo_name\']} - {\$lang->edit_edittodo}</title>
{\$headerinclude}
</head>
<body>
{\$header}
<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"clear: both;\">
	<tr>
		<td class=thead colspan=6><strong>{\$lang->title_overview}: {\$lang->edit_edittodo}</strong></td>
	</tr>
	<form action=\"todolist.php?action=submit-edit\" method=\"post\">
		{\$editing}
	</form>
</table>
{\$footer}
</body>
</html>",
        "sid" => -2
    );
    $db->insert_query("templates", $templatearray);


	$todolist_group = array(
        "title"          => $lang->setting_group_todo,
        "name"           => "todo",
        "description"    => $lang->setting_group_todo_desc,
        "disporder"      => "50",
        "isdefault"      => "0",
    );
    $gid = $db->insert_query("settinggroups", $todolist_group);

	$todolist_setting_1 = array(
        "name"           => "todo_activate",
        "title"          => $lang->setting_todo_activate,
        "description"    => $lang->setting_todo_activate_desc,
        "optionscode"    => "yesno",
        "value"          => 'yes',
        "disporder"      => '1',
        "gid"            => (int)$gid,
    );
	$db->insert_query("settings", $todolist_setting_1);

	$todolist_setting_2 = array(
        "name"           => "todo_allow_guests",
        "title"          => $lang->setting_todo_allow_guests,
        "description"    => $lang->setting_todo_allow_guests_desc,
        "optionscode"    => "yesno",
        "value"          => 'no',
        "disporder"      => '2',
        "gid"            => (int)$gid,
    );
	$db->insert_query("settings", $todolist_setting_2);

	$todolist_setting_3 = array(
        "name"           => "todo_disallowed_groups",
        "title"          => $lang->setting_todo_disallowed_groups,
        "description"    => $lang->setting_todo_disallowed_groups_desc,
        "optionscode"    => "text",
		"value"			 => "5",
        "disporder"      => '3',
        "gid"            => (int)$gid,
    );
	$db->insert_query("settings", $todolist_setting_3);

	$todolist_setting_4 = array(
        "name"           => "todo_mod_groups",
        "title"          => $lang->setting_todo_mod_groups,
        "description"    => $lang->setting_todo_mod_groups_desc,
        "optionscode"    => "text",
		"value"          => '4',
        "disporder"      => '5',
        "gid"            => (int)$gid,
    );
	$db->insert_query("settings", $todolist_setting_4);

	$todolist_setting_5 = array(
        "name"           => "todo_add_groups",
        "title"          => $lang->setting_todo_add_groups,
        "description"    => $lang->setting_todo_add_groups_desc,
        "optionscode"    => "text",
		"value"          => '4',
        "disporder"      => '4',
        "gid"            => (int)$gid,
	);
	$db->insert_query("settings", $todolist_setting_5);

	$todolist_setting_6 = array(
        "name"           => "todo_name",
        "title"          => $lang->setting_todo_name,
        "description"    => $lang->setting_todo_name_desc,
        "optionscode"    => "text",
        "disporder"      => '6',
        "gid"            => (int)$gid,
	);
	$db->insert_query("settings", $todolist_setting_6);

	$todolist_setting_7 = array(
        "name"			=> "todo_per_page",
        "title"			=> $lang->setting_todo_per_page,
        "description"	=> $lang->setting_todo_per_page_desc,
        "optionscode"	=> "text",
        "value"			=> "10",
        "disporder"		=> '7',
        "gid"			=> (int)$gid,
	);
	$db->insert_query("settings", $todolist_setting_7);
	rebuild_settings();
}

function todolist_is_installed() {
	global $db;
	return $db->table_exists("todolist");
}

function todolist_activate() {}

function todolist_deactivate() {}

function todolist_uninstall()
{
	global $db;
	
	$db->drop_table("todolist");

	$query = $db->simple_select("settinggroups", "gid", "name='todo'");
    $g = $db->fetch_array($query);
	$db->delete_query("settinggroups", "gid='".$g['gid']."'");
	$db->delete_query("settings", "gid='".$g['gid']."'");
	rebuild_settings();

	//Delete templates
	$templatearray = array(
        "todolist",
        "todolist_show",
        "todolist_add",
		"todolist_edit"
    );
    $deltemplates = implode("','", $templatearray);
	$db->delete_query("templates", "title in ('{$deltemplates}')");
}
?>