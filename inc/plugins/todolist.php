<?php
if(!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function todolist_info() {
	global $lang;
	$lang->load('todolist');
	
  return array(
		"name"			=> $lang->name_plugins,
		"description"	=> $lang->description_plugins."<br />",
		"website"		=> "http://mybbservice.de",
		"author"		=> "MyBBService",
		"authorsite"	=> "http://mybbservice.de",
		"version"		=> "1.0 Beta",
		"guid"			=> "",
		"compatibility" => "16*",
  );
}
	

function todolist_install() {
	global $db, $cache, $lang;
	$lang->load('todolist');
	
	$db->query("CREATE TABLE `".TABLE_PREFIX."todolist` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`title` TEXT NOT NULL ,
`date` INT(11) ,
`name` TEXT NOT NULL ,
`nameid` TEXT NOT NULL ,
`usergroup` TEXT NOT NULL ,
`lasteditor` TEXT NOT NULL ,
`lasteditorid` TEXT NOT NULL ,
`editorgroup` TEXT NOT NULL ,
`lastedit` INT(11),
`priority` TEXT NOT NULL ,
`message` TEXT NOT NULL ,
`status` TEXT NOT NULL ,
`done` TEXT NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

  $db->query("ALTER TABLE ".TABLE_PREFIX."usergroups ADD cantodolist VARCHAR(10) NOT NULL DEFAULT '1';");
  $db->query("UPDATE ".TABLE_PREFIX."usergroups SET cantodolist='0' WHERE gid='1';");
  
	$cache->update_usergroups();
}

function todolist_is_installed() {
    global $db, $lang;
	$lang->load('todolist');
    return $db->table_exists("todolist");
}

function todolist_activate() {
	global $lang, $db;
	$lang->load('todolist');
	
	require MYBB_ROOT.'/inc/adminfunctions_templates.php';
	
	    $templatearray = array(
        "title" => "todolist",
        "template" => "<html>
<head>
<title>{\$mybb->settings[\'bbname\']} - {\$lang->title_overview}: {\$mybb->settings[\'todolist_setting6\']}</title>
{\$headerinclude}
</head>
<body>
{\$header}
<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"clear: both;\">
<tr><td class=\"thead\" colspan=\"7\"><strong>{\$lang->title_overview}: {\$mybb->settings[\'todolist_setting6\']}</strong></td></tr>
<tr><td class=tcat>{\$lang->title_todo}</td><td class=tcat>{\$lang->date_todo}</td><td class=tcat>{\$lang->from_todo}</td><td class=tcat>{\$lang->priority_todo}</td><td class=tcat>{\$lang->status_todo}</td><td class=tcat>{\$lang->done_todo}</td><td class=tcat style=\"width:300px;\">{\$lang->action_todo}</td></tr>
{\$todo}
<tr class=\"trow1\"><td colspan=\"6\">{\$addtodo}</td><td style=\"float:right; width:190px;\">{\$lang->moderation_todo}: {\$modgroup}</td></tr>
</table>
{\$loggedin}
<br />
{\$footer}
</body>
</html>",
        "sid" => -1
        );
        $db->insert_query("templates", $templatearray);
		
	    $templatearray = array(
        "title" => "todolist_show",
        "template" => "<html>
<head>
<title>{\$mybb->settings[\'bbname\']} - {\$mybb->settings[\'todolist_setting6\']} - {\$lang->show_showtodo}</title>
{\$headerinclude}
</head>
<body>
{\$header}
{\$selectodo}
<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"clear: both;\">
<tr><td class=thead colspan=\"4\"><strong>{\$lang->title_overview}: {\$mybb->settings[\'todolist_setting6\']} - Aufgabe anzeigen</strong></td></tr>
<tr class=\"trow1\"><td style=\"width:200px;\">{\$lang->title_todo}:</td><td>{\$showtodotitle}</td></tr>
<tr class=\"trow2\"><td style=\"width:200px;\">{\$lang->date_todo}:</td><td>{\$showtododate}</td></tr>
<tr class=\"trow1\"><td style=\"width:200px;\">{\$lang->from_todo}:</td><td>{\$showtodofrom}</td></tr>
<tr class=\"trow2\"><td style=\"width:200px;\">{\$lang->priority_todo}:</td><td>{\$showtodoprio}</td></tr>
<tr class=\"trow1\"><td style=\"width:200px;\">{\$lang->done_todo}:</td><td>{\$showtododone}</td></tr>
<tr class=\"trow2\"><td style=\"width:200px;\">{\$lang->status_todo}:</td><td>{\$showtodostatus}</td></tr>
<tr class=\"trow1\"><td style=\"width:200px;\">{\$lang->description_todo}:</td><td>{\$showtodomess}</td></tr>
{\$showtodoaction}
{\$showtodolastedit}
<tr class=\"trow2\"><td colspan=\"2\">{\$showtodoback}</td></tr>
</table>
{\$loggedin}
<br />
{\$footer}
</body>
</html>",
        "sid" => -1
        );
        $db->insert_query("templates", $templatearray);
		
		$templatearray = array(
        "title" => "todolist_add",
        "template" => "<html>
	<head>
<title>{\$mybb->settings[\'bbname\']} - {\$mybb->settings[\'todolist_setting6\']} - {\$lang->add_todo}</title>
{\$headerinclude}
</head>
<body>
	{\$header}
<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"clear: both;\">
<tr><td class=thead colspan=2><strong>{\$lang->title_overview}: {\$lang->add_todo}</strong></td></tr>
<form action=\"\" method=\"post\">
<tr class=\"trow1\"><td style=\"width:100px;\">{\$lang->title_todo}:</td><td><input type=\"text\" name=\"title\" style=\"width:300px;\"/></td></tr>
<tr class=\"trow1\"><td style=\"width:100px;\">{\$lang->priority_todo}:</td><td><select name=\"priority\" style=\"width:100px;\"><option value=\"normal\" style=\"background-image:url(images/todolist/norm_prio.png); background-repeat:no-repeat; text-align:center; \">{\$lang->normal_priority}</option><option value=\"high\" style=\"background-image:url(images/todolist/high_prio.gif); background-repeat:no-repeat; text-align:center; \">{\$lang->high_priority}</option><option value=\"low\" style=\"background-image:url(images/todolist/low_prio.gif); background-repeat:no-repeat; text-align:center; \">{\$lang->low_priority}</option></select></td></tr>
<tr class=\"trow1\"><td style=\"width:200px;\">{\$lang->description_todo}:</td><td><textarea name=\"message\" rows=\"6\" cols=\"15\" style=\"width:300px; height:200px;\">$message</textarea></td></tr>
<tr class=\"trow1\"><td colspan=\"2\"><input type=\"submit\" value=\"{\$lang->add_todo}\" style=\"margin-left: 280px; \"/></td></tr>
</table>
{\$footer}
</body>
</html>",
        "sid" => -1
        );
        $db->insert_query("templates", $templatearray);
		
	$templatearray = array(
        "title" => "todolist_edit",
        "template" => "<html>
<head>
<title>{\$mybb->settings[\'bbname\']} - {\$mybb->settings[\'todolist_setting6\']} - {\$lang->edit_edittodo}</title>
{\$headerinclude}
</head>
<body>
{\$header}
<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"clear: both;\">
<tr><td class=thead colspan=6><strong>{\$lang->title_overview}: {\$lang->edit_edittodo}</strong></td></tr>
<form action=\"todolist.php?act=submit-edit\" method=\"post\">
{\$editing}
</form>
</table>
{\$footer}
</body>
</html>",
        "sid" => -1
        );
        $db->insert_query("templates", $templatearray);
	
     $todolist_group = array(
        "gid"            => "NULL",
        "title"          => $lang->name_settingoverview,
        "name"           => "todolist",
        "description"    => $lang->description_settingoverview,
        "disporder"      => "50",
        "isdefault"      => "no",
    );
    
	global $db, $cache, $lang;
	$lang->load('todolist');
    $db->insert_query("settinggroups", $todolist_group);
    $gid = $db->insert_id(); //This will get the id of the just added record in the db
	
	$todolist_setting_1 = array(
        "sid"            => "NULL",
        "name"           => "todolist_setting1",
        "title"          => $lang->name_setting1,
        "description"    => $lang->desc_setting1,
        "optionscode"    => "yesno",
        "value"          => 'yes',
        "disporder"      => '1',
        "gid"            => intval($gid),
    );
	
	$db->insert_query("settings", $todolist_setting_1);
    rebuildsettings();
	
	$todolist_setting_2 = array(
        "sid"            => "NULL",
        "name"           => "todolist_setting2",
        "title"          => $lang->name_setting2,
        "description"    => $lang->desc_setting2,
        "optionscode"    => "yesno",
        "value"          => 'no',
        "disporder"      => '2',
        "gid"            => intval($gid),
    );
	
	$db->insert_query("settings", $todolist_setting_2);
    rebuildsettings();
	
	$todolist_setting_3 = array(
        "sid"            => "NULL",
        "name"           => "todolist_setting3",
        "title"          => $lang->name_setting3,
        "description"    => $lang->desc_setting3,
        "optionscode"    => "text",
		"value"			 => "5",
        "disporder"      => '3',
        "gid"            => intval($gid),
    );
	
	$db->insert_query("settings", $todolist_setting_3);
    rebuildsettings();
	
	$todolist_setting_4 = array(
        "sid"            => "NULL",
        "name"           => "todolist_setting4",
        "title"          => $lang->name_setting4,
        "description"    => $lang->desc_setting4,
        "optionscode"    => "text",
		"value"          => '4',
        "disporder"      => '5',
        "gid"            => intval($gid),
    );
	
	$db->insert_query("settings", $todolist_setting_4);
    rebuildsettings();
	
	$todolist_setting_5 = array(
        "sid"            => "NULL",
        "name"           => "todolist_setting5",
        "title"          => $lang->name_setting5,
        "description"    => $lang->desc_setting5,
        "optionscode"    => "text",
		"value"          => '4',
        "disporder"      => '4',
        "gid"            => intval($gid),
	);
		
	$db->insert_query("settings", $todolist_setting_5);
    rebuildsettings();
	
	$todolist_setting_6 = array(
        "sid"            => "NULL",
        "name"           => "todolist_setting6",
        "title"          => $lang->name_setting6,
        "description"    => $lang->desc_setting6,
        "optionscode"    => "text",
        "disporder"      => '6',
        "gid"            => intval($gid),
	);
	
	$db->insert_query("settings", $todolist_setting_6);
    rebuildsettings();
	
	$cache->update_usergroups();
}

function todolist_deactivate() {
	global $db;

	$db->query("DELETE FROM `".TABLE_PREFIX."settings` WHERE name='todolist_setting1';");
	$db->query("DELETE FROM `".TABLE_PREFIX."settings` WHERE name='todolist_setting2';");
	$db->query("DELETE FROM `".TABLE_PREFIX."settings` WHERE name='todolist_setting3';");
	$db->query("DELETE FROM `".TABLE_PREFIX."settings` WHERE name='todolist_setting4';");
	$db->query("DELETE FROM `".TABLE_PREFIX."settings` WHERE name='todolist_setting5';");
	$db->query("DELETE FROM `".TABLE_PREFIX."settings` WHERE name='todolist_setting6';");
    $db->query("DELETE FROM `".TABLE_PREFIX."settinggroups` WHERE name='todolist';");
    rebuildsettings();	
	
	//Delete templates
	    $templatearray = array(
        "todolist",
        "todolist_show",
        "todolist_add",
		"todolist_edit"
    );
    $deltemplates = implode("','", $templatearray);
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title in ('{$deltemplates}');");
}

function todolist_uninstall() {
	global $db, $cache;
	
	$db->drop_table("todolist");
	$db->query("ALTER TABLE `".TABLE_PREFIX."usergroups` DROP cantodolist;");
	$cache->update_usergroups();
}
?>