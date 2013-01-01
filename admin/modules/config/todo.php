<?php
if(!defined("IN_MYBB"))
{
	header("HTTP/1.0 404 Not Found");
	exit;
}

$page->add_breadcrumb_item($lang->todo, "index.php?module=config-todo");

if($mybb->input['action'] == "") {
	$page->output_header($lang->todo);
	generate_tabs("list");
	
	$table = new Table;

	$table->construct_header($lang->todo_item);
	$table->construct_header($lang->todo_todos);
	$table->construct_header($lang->controls, array("class" => "align_center", "colspan" => 2));

	//$query = $db->simple_select("todolist_projects", "*", "", array("order_by"=>"title"));
	$query = $db->query("SELECT p.*, COUNT(t.id) AS count
			FROM ".TABLE_PREFIX."todolist_projects p
			LEFT JOIN ".TABLE_PREFIX."todolist t ON (t.pid=p.id)");
	$found = false;
	if($db->num_rows($query) > 0)
	{
		while($todo = $db->fetch_array($query))
		{
			if($todo['id'] != NULL) {
			    $found = true;
				$table->construct_cell("{$todo['title']}<br /><i>{$todo['description']}</i>");
				$table->construct_cell($todo['count']);
				$table->construct_cell("<a href=\"index.php?module=config-todo&amp;action=edit&amp;id={$todo['id']}\">{$lang->edit}</a>", array('class' => 'align_center', 'width' => '10%'));
				$table->construct_cell("<a href=\"index.php?module=config-todo&amp;action=delete&amp;id={$todo['id']}\">{$lang->delete}</a>", array('class' => 'align_center', 'width' => '10%'));
				$table->construct_row();
			}
		}
	}
	if(!$found) {
		$table->construct_cell($lang->no_projects, array('class' => 'align_center', 'colspan' => 4));
		$table->construct_row();
	}
	$table->output($lang->todo);
}

$page->output_footer();

function generate_tabs($selected)
{
	global $lang, $page;

	$sub_tabs = array();
	$sub_tabs['list'] = array(
		'title' => $lang->todo_list,
		'link' => "index.php?module=config-todo",
		'description' => $lang->todo_list_desc
	);
	$sub_tabs['add'] = array(
		'title' => $lang->todo_add,
		'link' => "index.php?module=config-todo&amp;action=add",
		'description' => $lang->todo_add_desc
	);

	$page->output_nav_tabs($sub_tabs, $selected);
}
?>