<?php
if(!defined("IN_MYBB"))
{
	header("HTTP/1.0 404 Not Found");
	exit;
}

$page->add_breadcrumb_item($lang->todo, "index.php?module=config-todo");

if($mybb->input['action'] == "do_add") {
	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect("index.php?module=config-todo&action=add");
	}

	if(!strlen(trim($mybb->input['title'])))
        $errors[] = $lang->missing_title;

	if(!strlen(trim($mybb->input['desc'])))
        $errors[] = $lang->missing_desc;

	if(!$errors) {
		$insert = array(
			"title"			=> $db->escape_string($mybb->input['title']),
			"description"	=> $db->escape_string($mybb->input['desc']),
		);
		$id = $db->insert_query("todolist_projects", $insert);

		foreach($mybb->input['perm'] as $gid => $perms)
		{
			$permRow = array(
					"pid" => $id,
					"gid" => (int)$gid,
					"can_see"  => in_array("can_see",  $perms),
					"can_add"  => in_array("can_add",  $perms),
					"can_edit" => in_array("can_edit", $perms)
			);
			$db->insert_query('todolist_permissions', $permRow);
		}


		flash_message($lang->add_success, 'success');
		admin_redirect("index.php?module=config-todo");
	} else {
		$mybb->input['action'] = "add";
	}
}
if($mybb->input['action'] == "add") {
	$page->add_breadcrumb_item($lang->todo_add, "index.php?module=config-todo&action=add");
	$page->output_header($lang->todo_add);
	generate_tabs("add");

	if($errors) {
		$page->output_inline_error($errors);
		$title = $mybb->input['title'];
		$desc = $mybb->input['desc'];
	} else {
		$title = "";
		$desc = "";
	}

	$form = new Form("index.php?module=config-todo&amp;action=do_add", "post");
	$form_container = new FormContainer($lang->todo_add);

	$add_title = $form->generate_text_box("title", $title);
	$form_container->output_row($lang->todo_title." <em>*</em>", $lang->todo_title_desc, $add_title);

	$add_desc = $form->generate_text_area("desc", $desc);
	$form_container->output_row($lang->todo_desc." <em>*</em>", $lang->todo_desc_desc, $add_desc);

	$form_container->end();


	$table = new Table;
	$table->construct_header($lang->group, array('style' => 'text-align: center;'));
	$table->construct_header($lang->can_see, array('style' => 'text-align: center;'));
	$table->construct_header($lang->can_add, array('style' => 'text-align: center;'));
	$table->construct_header($lang->can_edit, array('style' => 'text-align: center;'));
	
	foreach($groupscache as $group)
	{
		$table->construct_cell(htmlspecialchars_uni($group['title']));

		if($errors) {
			if(!isset($mybb->input['perm'][$group['gid']])) {
				$perms['can_see'] = false;
				$perms['can_add'] = false;
				$perms['can_edit'] = false;
			} else {
				$perms['can_see'] = in_array("can_see", $mybb->input['perm'][$group['gid']]);
				$perms['can_add'] = in_array("can_add", $mybb->input['perm'][$group['gid']]);
				$perms['can_edit'] = in_array("can_edit", $mybb->input['perm'][$group['gid']]);
			}
		} else {
			$perms['can_see'] = true;
			$perms['can_add'] = true;
			$perms['can_edit'] = true;
		}

		$can_see = $form->generate_check_box("perm[{$group['gid']}][]", 'can_see' , "", array("checked" => $perms['can_see']));
		$table->construct_cell($can_see, array('style' => 'text-align: center;'));
		$can_add = $form->generate_check_box("perm[{$group['gid']}][]", 'can_add' , "", array("checked" => $perms['can_add']));
		$table->construct_cell($can_add, array('style' => 'text-align: center;'));
		$can_edit = $form->generate_check_box("perm[{$group['gid']}][]", 'can_edit' , "", array("checked" => $perms['can_edit']));
		$table->construct_cell($can_edit, array('style' => 'text-align: center;'));
	
		$table->construct_row();
	}
	$table->output($lang->todo_permissions);


	$buttons[] = $form->generate_submit_button($lang->todo_save);
	$buttons[] = $form->generate_reset_button($lang->reset);
	$form->output_submit_wrapper($buttons);
	$form->end();
}
if($mybb->input['action']=="delete") {
	if(!strlen(trim($mybb->input['id'])))
	{
		flash_message($lang->todo_no_id, 'error');
		admin_redirect("index.php?module=config-todo");
	}
	$id=(int)$mybb->input['id'];

	if($mybb->input['no'])
		admin_redirect("index.php?module=config-todo");
	else {
		if($mybb->request_method == "post") {
			$db->delete_query("todolist_projects", "id='{$id}'");
			$db->delete_query("todolist", "pid='{$id}'");
			$db->delete_query("todolist_permissions", "pid='{$id}'");
			flash_message($lang->todo_deleted, 'success');
			admin_redirect("index.php?module=config-todo");
		} else
			$page->output_confirm_action("index.php?module=config-todo&action=delete&id={$id}", $lang->todo_delete_confirm);
	}
}
if($mybb->input['action'] == "do_edit") {
	if(!strlen(trim($mybb->input['id'])))
	{
		flash_message($lang->todo_no_id, 'error');
		admin_redirect("index.php?module=config-todo");
	}
	$id=(int)$mybb->input['id'];

    if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect("index.php?module=config-todo&action=edit");
	}

	if(!strlen(trim($mybb->input['title'])))
        $errors[] = $lang->missing_title;

	if(!strlen(trim($mybb->input['desc'])))
        $errors[] = $lang->missing_desc;

	if(!$errors) {
		$update = array(
			"title"			=> $db->escape_string($mybb->input['title']),
			"description"	=> $db->escape_string($mybb->input['desc']),
		);
		$db->update_query("todolist_projects", $update, "id={$id}");

		$db->delete_query("todolist_permissions", "pid={$id}");
		foreach($mybb->input['perm'] as $gid => $perms)
		{
			$permRow = array(
					"pid" => $id,
					"gid" => (int)$gid,
					"can_see"  => in_array("can_see",  $perms),
					"can_add"  => in_array("can_add",  $perms),
					"can_edit" => in_array("can_edit", $perms)
			);
			$db->insert_query('todolist_permissions', $permRow);
		}


		flash_message($lang->edit_success, 'success');
		admin_redirect("index.php?module=config-todo");
	} else {
		$mybb->input['action'] = "edit";
	}
}
if($mybb->input['action'] == "edit") {
	if(!strlen(trim($mybb->input['id'])))
	{
		flash_message($lang->todo_no_id, 'error');
		admin_redirect("index.php?module=config-todo");
	}
	$id=(int)$mybb->input['id'];
	$query = $db->simple_select("todolist_projects", "*", "id='{$id}'");
	if($db->num_rows($query) != 1)
	{
		flash_message($lang->todo_wrong_id, 'error');
		admin_redirect("index.php?module=config-todo");
	}
	$todo = $db->fetch_array($query);

	$page->add_breadcrumb_item($lang->todo_edit, "index.php?module=config-todo&action=edit&id={$id}");
	$page->output_header($lang->todo_edit);
	generate_tabs("edit");

	if($errors) {
		$page->output_inline_error($errors);
		$title = $mybb->input['title'];
		$desc = $mybb->input['desc'];
	} else {
		$title = $todo['title'];
		$desc = $todo['description'];
	}

	$form = new Form("index.php?module=config-todo&amp;action=do_edit", "post");
	$form_container = new FormContainer($lang->todo_edit);

	$add_title = $form->generate_text_box("title", $title);
	$form_container->output_row($lang->todo_title." <em>*</em>", $lang->todo_title_desc, $add_title);

	$add_desc = $form->generate_text_area("desc", $desc);
	$form_container->output_row($lang->todo_desc." <em>*</em>", $lang->todo_desc_desc, $add_desc);

	$form_container->end();


	$table = new Table;
	$table->construct_header($lang->group, array('style' => 'text-align: center;'));
	$table->construct_header($lang->can_see, array('style' => 'text-align: center;'));
	$table->construct_header($lang->can_add, array('style' => 'text-align: center;'));
	$table->construct_header($lang->can_edit, array('style' => 'text-align: center;'));

	foreach($groupscache as $group)
	{
		$table->construct_cell(htmlspecialchars_uni($group['title']));

		if($errors) {
			if(!isset($mybb->input['perm'][$group['gid']])) {
				$perms['can_see'] = false;
				$perms['can_add'] = false;
				$perms['can_edit'] = false;
			} else {
				$perms['can_see'] = in_array("can_see", $mybb->input['perm'][$group['gid']]);
				$perms['can_add'] = in_array("can_add", $mybb->input['perm'][$group['gid']]);
				$perms['can_edit'] = in_array("can_edit", $mybb->input['perm'][$group['gid']]);
			}
		} else {
			$query = $db->simple_select("todolist_permissions", "*", "pid={$todo['id']} AND gid={$group['gid']}");
			$perms = $db->fetch_array($query);
		}

		$can_see = $form->generate_check_box("perm[{$group['gid']}][]", 'can_see' , "", array("checked" => $perms['can_see']));
		$table->construct_cell($can_see, array('style' => 'text-align: center;'));
		$can_add = $form->generate_check_box("perm[{$group['gid']}][]", 'can_add' , "", array("checked" => $perms['can_add']));
		$table->construct_cell($can_add, array('style' => 'text-align: center;'));
		$can_edit = $form->generate_check_box("perm[{$group['gid']}][]", 'can_edit' , "", array("checked" => $perms['can_edit']));
		$table->construct_cell($can_edit, array('style' => 'text-align: center;'));

		$table->construct_row();
	}
	$table->output($lang->todo_permissions);


	echo $form->generate_hidden_field("id", $id);
	$buttons[] = $form->generate_submit_button($lang->todo_save);
	$buttons[] = $form->generate_reset_button($lang->reset);
	$form->output_submit_wrapper($buttons);
	$form->end();
}
if($mybb->input['action'] == "search_do_add") {
	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect("index.php?module=config-todo&action=search_add");
	}

	if(!strlen(trim($mybb->input['title'])))
        $errors[] = $lang->missing_search_title;

	if(!$errors) {
		$url = "todolist.php?action=search&search=do";

		if($mybb->input['string'] != "")
			$url .= "&string={$mybb->input['string']}";

		if($mybb->input['creator'] != "")
			$url .= "&creator={$mybb->input['creator']}";


		if(!empty($mybb->input['status'])){
	   		foreach($mybb->input['status'] as $st)
			    $url .= "&status[]={$st}";
		}

		if(!empty($mybb->input['project'])){
			foreach($mybb->input['project'] as $pr)
			    $url .= "&project[]={$pr}";
		}

		if($mybb->input['assign'] != "")
		    $url .= "&assign={$mybb->input['assign']}";

		if(!empty($mybb->input['priority'])){
			foreach($mybb->input['priority'] as $pr)
			    $url .= "&priority[]={$pr}";
		}

		$insert = array(
			"title"			=> $db->escape_string($mybb->input['title']),
			"url"			=> $db->escape_string($url),
		);
		$db->insert_query("todolist_searchs", $insert);

		flash_message($lang->search_add_success, 'success');
		admin_redirect("index.php?module=config-todo&action=search");
	} else {
		$mybb->input['action'] = "search_add";
	}
}
if($mybb->input['action'] == "search_add") {
	$page->add_breadcrumb_item($lang->todo_search, "index.php?module=config-todo&action=search");
	$page->add_breadcrumb_item($lang->todo_search_add, "index.php?module=config-todo&action=search_add");
	$page->output_header($lang->todo_search_add);
	generate_tabs("search_add");

	if($errors) {
		$page->output_inline_error($errors);
		$title = $mybb->input['title'];
		$string = $mybb->input['string'];
		$status_select = $mybb->input['status'];
		$creator = $mybb->input['creator'];
		$assign = $mybb->input['assign'];
		$project_select = $mybb->input['project'];
		$priority_select = $mybb->input['priority'];
	} else {
		$title = "";
		$string = "";
		$status_select = array();
		$creator = "";
		$assign = "";
		$project_select = array();
		$priority_select = array();
	}

	$form = new Form("index.php?module=config-todo&amp;action=search_do_add", "post");
	$form_container = new FormContainer($lang->todo_search_add);

	$add_title = $form->generate_text_box("title", $title);
	$form_container->output_row($lang->todo_title." <em>*</em>", $lang->todo_search_title_desc, $add_title);

	$add_string = $form->generate_text_box("string", $string);
	$form_container->output_row($lang->todo_string, $lang->todo_string_desc, $add_string);

	$status = array(
		"wait" => $lang->status_wait,
		"development" => $lang->status_dev,
		"resolved" => $lang->status_resolved,
		"feedback" => $lang->status_feed,
		"closed" => $lang->status_closed
	);
	$add_status = $form->generate_select_box("status[]", $status, $status_select, array("multiple" => "true"));
	$form_container->output_row($lang->todo_status, $lang->todo_status_desc, $add_status);

	$add_creator = $form->generate_text_box("creator", $creator);
	$form_container->output_row($lang->todo_creator, $lang->todo_creator_desc, $add_creator);

	$add_assign = $form->generate_text_box("assign", $assign);
	$form_container->output_row($lang->todo_assign, $lang->todo_assign_desc, $add_assign);

	$query = $db->simple_select("todolist_projects", "id, title", "", array("order_by" => "title"));
	if($db->num_rows($query) > 0) {
		while($pr = $db->fetch_array($query))
			$project[$pr['id']] = $pr['title'];
	} else { $project = array(); }
	$add_project = $form->generate_select_box("project[]", $project, $project_select, array("multiple" => "true"));
	$form_container->output_row($lang->todo_project, $lang->todo_project_desc, $add_project);

	$priority = array(
		"high" => $lang->priority_high,
		"normal" => $lang->priority_normal,
		"low" => $lang->priority_low
	);
	$add_priority = $form->generate_select_box("priority[]", $priority, $priority_select, array("multiple" => "true"));
	$form_container->output_row($lang->todo_priority, $lang->todo_priority_desc, $add_priority);

	$form_container->end();

	$buttons[] = $form->generate_submit_button($lang->todo_save_search);
	$buttons[] = $form->generate_reset_button($lang->reset);
	$form->output_submit_wrapper($buttons);
	$form->end();
}
if($mybb->input['action']=="search_delete") {
	if(!strlen(trim($mybb->input['id'])))
	{
		flash_message($lang->todo_no_id, 'error');
		admin_redirect("index.php?module=config-todo&action=search");
	}
	$id=(int)$mybb->input['id'];

	if($mybb->input['no'])
		admin_redirect("index.php?module=config-todo&action=search");
	else {
		if($mybb->request_method == "post") {
			$db->delete_query("todolist_searchs", "id='{$id}'");
			flash_message($lang->todo_search_deleted, 'success');
			admin_redirect("index.php?module=config-todo&action=search");
		} else
			$page->output_confirm_action("index.php?module=config-todo&action=search_delete&id={$id}", $lang->todo_search_delete_confirm);
	}
}
if($mybb->input['action'] == "search_do_edit") {
	if(!strlen(trim($mybb->input['id'])))
	{
		flash_message($lang->todo_no_id, 'error');
		admin_redirect("index.php?module=config-todo&action=search");
	}
	$id=(int)$mybb->input['id'];

   	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect("index.php?module=config-todo&action=search_edit");
	}

	if(!strlen(trim($mybb->input['title'])))
        $errors[] = $lang->missing_search_title;

	if(!$errors) {
		$url = "todolist.php?action=search&search=do";

		if($mybb->input['string'] != "")
			$url .= "&string={$mybb->input['string']}";

		if($mybb->input['creator'] != "")
			$url .= "&creator={$mybb->input['creator']}";


		if(!empty($mybb->input['status'])){
	   		foreach($mybb->input['status'] as $st)
			    $url .= "&status[]={$st}";
		}

		if(!empty($mybb->input['project'])){
			foreach($mybb->input['project'] as $pr)
			    $url .= "&project[]={$pr}";
		}

		if($mybb->input['assign'] != "")
		    $url .= "&assign={$mybb->input['assign']}";

		if(!empty($mybb->input['priority'])){
			foreach($mybb->input['priority'] as $pr)
			    $url .= "&priority[]={$pr}";
		}

		$update = array(
			"title"			=> $db->escape_string($mybb->input['title']),
			"url"			=> $db->escape_string($url),
		);
		$db->update_query("todolist_searchs", $update, "id={$id}");

		flash_message($lang->search_edit_success, 'success');
		admin_redirect("index.php?module=config-todo&action=search");
	} else {
		$mybb->input['action'] = "search_edit";
	}

}
if($mybb->input['action'] == "search_edit") {
	if(!strlen(trim($mybb->input['id'])))
	{
		flash_message($lang->todo_no_id, 'error');
		admin_redirect("index.php?module=config-todo&action=search");
	}
	$id=(int)$mybb->input['id'];
	$query = $db->simple_select("todolist_searchs", "*", "id='{$id}'");
	if($db->num_rows($query) != 1)
	{
		flash_message($lang->todo_wrong_id, 'error');
		admin_redirect("index.php?module=config-todo&action=search");
	}
	$todo = $db->fetch_array($query);

	$page->add_breadcrumb_item($lang->todo_search, "index.php?module=config-todo&action=search");
	$page->add_breadcrumb_item($lang->todo_search_edit, "index.php?module=config-todo&action=search_edit&id={$id}");
	$page->output_header($lang->todo_search_edit);
	generate_tabs("search_edit");

	if($errors) {
		$page->output_inline_error($errors);
		$title = $mybb->input['title'];
		$string = $mybb->input['string'];
		$status_select = $mybb->input['status'];
		$creator = $mybb->input['creator'];
		$assign = $mybb->input['assign'];
		$project_select = $mybb->input['project'];
		$priority_select = $mybb->input['priority'];
	} else {
		$title = $todo['title'];
		$pars = substr($todo['url'], strpos($todo['url'], "?"));
		$pars = explode("&", $pars);
		foreach($pars as $par) {
			$temp = explode("=", $par);
			if(substr($temp[0], -2) == "[]")
			    $parameters[substr($temp[0], 0, -2)][] = $temp[1];
			else
				$parameters[$temp[0]] = $temp[1];
		}
		if(isset($parameters['string']))
			$string = $parameters['string'];
		else
			$string = "";
		if(isset($parameters['status']))
			$status_select = $parameters['status'];
		else
			$status_select = array();
		if(isset($parameters['creator']))
			$creator = $parameters['creator'];
		else
			$creator = "";
		if(isset($parameters['assign']))
			$assign = $parameters['assign'];
		else
			$assign = "";
		if(isset($parameters['project']))
			$project_select = $parameters['project'];
		else
			$project_select = array();
		if(isset($parameters['priority']))
			$priority_select = $parameters['priority'];
		else
			$priority_select = array();
	}

	$form = new Form("index.php?module=config-todo&amp;action=search_do_edit", "post");
	$form_container = new FormContainer($lang->todo_search_edit);

	$add_title = $form->generate_text_box("title", $title);
	$form_container->output_row($lang->todo_title." <em>*</em>", $lang->todo_search_title_desc, $add_title);

	$add_string = $form->generate_text_box("string", $string);
	$form_container->output_row($lang->todo_string, $lang->todo_string_desc, $add_string);

	$status = array(
		"wait" => $lang->status_wait,
		"development" => $lang->status_dev,
		"resolved" => $lang->status_resolved,
		"feedback" => $lang->status_feed,
		"closed" => $lang->status_closed
	);
	$add_status = $form->generate_select_box("status[]", $status, $status_select, array("multiple" => "true"));
	$form_container->output_row($lang->todo_status, $lang->todo_status_desc, $add_status);

	$add_creator = $form->generate_text_box("creator", $creator);
	$form_container->output_row($lang->todo_creator, $lang->todo_creator_desc, $add_creator);

	$add_assign = $form->generate_text_box("assign", $assign);
	$form_container->output_row($lang->todo_assign, $lang->todo_assign_desc, $add_assign);

	$query = $db->simple_select("todolist_projects", "id, title", "", array("order_by" => "title"));
	if($db->num_rows($query) > 0) {
		while($pr = $db->fetch_array($query))
			$project[$pr['id']] = $pr['title'];
	} else { $project = array(); }
	$add_project = $form->generate_select_box("project[]", $project, $project_select, array("multiple" => "true"));
	$form_container->output_row($lang->todo_project, $lang->todo_project_desc, $add_project);

	$priority = array(
		"high" => $lang->priority_high,
		"normal" => $lang->priority_normal,
		"low" => $lang->priority_low
	);
	$add_priority = $form->generate_select_box("priority[]", $priority, $priority_select, array("multiple" => "true"));
	$form_container->output_row($lang->todo_priority, $lang->todo_priority_desc, $add_priority);

	$form_container->end();

	echo $form->generate_hidden_field("id", $id);
	$buttons[] = $form->generate_submit_button($lang->todo_save_search);
	$buttons[] = $form->generate_reset_button($lang->reset);
	$form->output_submit_wrapper($buttons);
	$form->end();
}
if($mybb->input['action'] == "search") {
	$page->add_breadcrumb_item($lang->todo_search, "index.php?module=config-todo&action=search");
	$page->output_header($lang->todo_search);
	generate_tabs("search");

	$table = new Table;

	$table->construct_header($lang->todo_title);
	$table->construct_header($lang->todo_url);
	$table->construct_header($lang->controls, array("class" => "align_center", "colspan" => 2));

	$query = $db->simple_select("todolist_searchs", "*", "", array("order_by"=>"title"));
	if($db->num_rows($query) > 0)
	{
		while($todo = $db->fetch_array($query))
		{
			$table->construct_cell($todo['title']);
			$table->construct_cell("<a href=\"{$mybb->settings['bburl']}/{$todo['url']}\" target=\"_blank\">{$todo['url']}</a>");
			$table->construct_cell("<a href=\"index.php?module=config-todo&amp;action=search_edit&amp;id={$todo['id']}\">{$lang->edit}</a>", array('class' => 'align_center', 'width' => '10%'));
			$table->construct_cell("<a href=\"index.php?module=config-todo&amp;action=search_delete&amp;id={$todo['id']}\">{$lang->delete}</a>", array('class' => 'align_center', 'width' => '10%'));
			$table->construct_row();
		}
	} else {
		$table->construct_cell($lang->no_searchs, array('class' => 'align_center', 'colspan' => 4));
		$table->construct_row();
	}
	$table->output($lang->todo_search);	
}
if($mybb->input['action'] == "") {
	$page->output_header($lang->todo);
	generate_tabs("list");
	
	$table = new Table;

	$table->construct_header($lang->todo_item);
	$table->construct_header($lang->todo_todos);
	$table->construct_header($lang->controls, array("class" => "align_center", "colspan" => 2));

	$query = $db->simple_select("todolist_projects", "*", "", array("order_by"=>"title"));
	if($db->num_rows($query) > 0)
	{
		while($todo = $db->fetch_array($query))
		{
			$count = $db->num_rows($db->simple_select("todolist", "id", "pid={$todo['id']}"));
			$table->construct_cell("{$todo['title']}<br /><i>{$todo['description']}</i>");
			$table->construct_cell($count);
			$table->construct_cell("<a href=\"index.php?module=config-todo&amp;action=edit&amp;id={$todo['id']}\">{$lang->edit}</a>", array('class' => 'align_center', 'width' => '10%'));
			$table->construct_cell("<a href=\"index.php?module=config-todo&amp;action=delete&amp;id={$todo['id']}\">{$lang->delete}</a>", array('class' => 'align_center', 'width' => '10%'));
			$table->construct_row();
		}
	} else {
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
	$sub_tabs['search'] = array(
		'title' => $lang->todo_search,
		'link' => "index.php?module=config-todo&amp;action=search",
		'description' => $lang->todo_search_desc
	);
	$sub_tabs['search_add'] = array(
		'title' => $lang->todo_search_add,
		'link' => "index.php?module=config-todo&amp;action=search_add",
		'description' => $lang->todo_search_add_desc
	);

	$page->output_nav_tabs($sub_tabs, $selected);
}
?>