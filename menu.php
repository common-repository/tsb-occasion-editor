<?php

add_action('admin_menu', 'occ_menu');

function occ_menu() {
	add_management_page('TSB Occasions Editor', 'TSB Occasions', 'manage_options', 'my-unique-identifier', 'occ_options');
}

function occ_delete(){
	global $wpdb;
	$id = $_POST['id'];
	switch ($_POST['table']) {
		case 'occasion':
			$table_name = $wpdb->prefix . 'tsb_occassions_editor';
			break;
		case 'image':
			$table_name = $wpdb->prefix . 'tsb_occassions_images';
			break;
	}
	if($id){
		$sql = 'DELETE FROM ' . $table_name . ' WHERE id = ' . $id;
		$wpdb->query($sql);
	}
	die();
}
function occ_update(){
	global $wpdb;
	$url = $_POST['url'];
	$title = $_POST['title'];
	$id = $_POST['id'];
	$desc = $_POST['desc'];
	switch ($_POST['table']) {
		case 'occasion':
			$table_name = $wpdb->prefix . 'tsb_occassions_editor';
			$sql = "UPDATE " . $table_name . " SET title = '" . $title . "' WHERE id = " . $id;
			echo $sql;
			break;
		case 'image':
			$table_name = $wpdb->prefix . 'tsb_occassions_images';
			$sql = "UPDATE " . $table_name . " SET url = '" . $url . "', title = '" . $title . "', descr='" . $desc . "' WHERE id = " . $id;
			break;
	}

	$wpdb->query($wpdb->prepare($sql));
	die();
}
function occ_insert(){
	global $wpdb;
	$url = $_POST['url'];
	$table = $_POST['table'];
	$title = $_POST['title'];
	$tag_id = $_POST['tag_id'];
	$desc = $_POST['desc'];
	switch ($table) {
		case 'occasion':
			$table_name = $wpdb->prefix . 'tsb_occassions_editor';
			$sql = "INSERT " . $table_name . " (title) VALUES ('" . $title . "')";
			break;
		case 'image':
			$table_name = $wpdb->prefix . 'tsb_occassions_images';
			$sql = "INSERT " . $table_name . " (url, title, tag_id, descr) VALUES ('" . $url . "', '" . $title . "', '" . $tag_id . "', '" . $desc . "')";
			break;
	}

	$wpdb->query($wpdb->prepare($sql));

	$sql = "SELECT MAX(id) AS id FROM " . $table_name;

	$ids = $wpdb->get_results($sql);
	$obj = $ids[0];
	echo $obj->id;
	die();
}
// Adds occ_delete() to the ajax of WP
add_action('wp_ajax_occ_delete', 'occ_delete');
add_action('wp_ajax_occ_insert', 'occ_insert');
add_action('wp_ajax_occ_update', 'occ_update');

// Build AJAX to get tags and images
function occ_get_tags(){
	global $wpdb;
	$table_name = $wpdb->prefix . 'tsb_occassions_editor';
	$sql = 'SELECT id, title FROM ' . $table_name;
	$occasions = $wpdb->get_results($sql);
	echo json_encode($occasions);
	die();
}
function occ_get_images_by_tag(){
	global $wpdb;
	$table_name = $wpdb->prefix . 'tsb_occassions_images';
	$tag_id = $_POST['tag_id'];
	$sql = 'SELECT id, title, url, descr FROM ' . $table_name . ' WHERE tag_id = ' . $tag_id;
	$images = $wpdb->get_results($sql);
	echo json_encode($images);
	die();
}
function occ_get_image_by_id(){
	global $wpdb;
	$table_name = $wpdb->prefix . 'tsb_occassions_images';
	die();
}

add_action('wp_ajax_nopriv_occ_get_tags', 'occ_get_tags');
add_action('wp_ajax_nopriv_occ_get_images_by_tag', 'occ_get_images_by_tag');
add_action('wp_ajax_nopriv_occ_get_image_by_id', 'occ_get_image_by_id');

function occ_options() {
	global $wpdb;
	
	$table_name = $wpdb->prefix . 'tsb_occassions_editor';
	$sql = "SELECT id, title FROM " . $table_name;
	$occasions = $wpdb->get_results($sql);

	$table_name = $wpdb->prefix . 'tsb_occassions_images';
	$sql = "SELECT id, tag_id, title, url, descr FROM " . $table_name;
	$occ_images = $wpdb->get_results($sql);

	?>

	<style>
		.occasion, .image-container{
			margin:5px 0;
		}
		.text-input{
			padding:5px;
			margin:0 5px;
			font-size:1.2em;
		}
	</style>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.3/jquery.min.js"></script>
	<script>
		var animTime = 200;
		$(document).ready(function(){
			//Adding new item
			$('#add-occasion').click(function(){
				$occ = $('<fieldset data-table="occasion" style="display:none" class="occasion"></fieldset>');
				$occ.append('<input name="id" type="hidden" class="id-hidden" value="" />');
				$occ.append('<input placeholder="Occasion Title" name="title" class="title text-input" size="30" value="" />');
				// $occ.append('<input placeholder="Tag" name="tag" class="tag text-input" size="10" value="" />');
				$occ.append('<input type="button" value="Delete" class="delete-occasion" />');
				$occ.appendTo('#occasion-form').show(animTime, function(){
					$occ.find('.title').focus();
				});

				update_delete_click();
				update_text_change();
			});

			update_delete_click();
			update_text_change();
			update_tag_list();

			// When selecting a tag to add/delete imgs
			$('#occasion-image-select').change(function(){
				$('.tag-images').hide();
				$('#occasion-tag-' + $(this).val()).show(500);
			});

			$('.tag-images').hide();

			$('#add-image').click(function(){
				var tag_id = $('.tag-images:visible').data('tag');
				$img = $('<div data-table="image" data-tag="' + tag_id + '" class="image-container" style="display:none"></div>');
				$img.append('<input name="id" type="hidden" class="id-hidden" />');
				$img.append('<input placeholder="Image Title" name="title" class="title text-input" size="20" />');
				$img.append('<input placeholder="URL" name="url" class="url text-input" size="40" />');
				$img.append('<input type="button" value="Delete" class="delete-occasion" /><br /><br />');
				$img.append('<input placeholder="One or two sentence description." name="desc" class="desc text-input" size="66" />');
				$img.appendTo('.tag-images:visible').show(animTime,function(){
					$img.find('.title').focus();
				});

				update_delete_click();
				update_text_change();
			});
		});

		// init tag list var
		var occ_list = <?php echo json_encode($occasions) ?>;

		function update_tag_list(){
			$('#occasion-image-select').empty();
			// init tag list
			for(i = 0; i < occ_list.length; ++i){
				var $opt = $('<option></option');
				$opt.html(occ_list[i].title);
				$opt.attr('value', occ_list[i].id);
				$opt.appendTo('#occasion-image-select');
			}
			var $opt = $('<option></option');
			$opt.prependTo('#occasion-image-select');
		}
		function update_text_change(){
			$('.text-input').change(function(){
				$par = $(this).parent(),
				id = $par.find('.id-hidden').val(),
				url = $par.find('.url').val(),
				title = $par.find('.title').val(),
				table = $par.data('table'),
				that = this,
				tag_id = $par.data('tag'),
				desc = $par.find('.desc').val();
				if(title && title.length){
					if(id.length){
						$.ajax({
							type: 'POST',
							url: ajaxurl,
							data: {
								'action': 'occ_update',
								'id': id,
								'url': url,
								'title': title,
								'table': table,
								'tag_id': tag_id,
								'desc': desc
							},
							success: function(){
								if(!tag_id){
									for(i = 0; i < occ_list.length; ++i){
										if(occ_list[i].id == id){
											occ_list[i].title = title;
										}
										update_tag_list();
									}
								}
							}
						});
					}else{
						$.ajax({
							type: 'POST',
							url: ajaxurl,
							data: {
								'action': 'occ_insert',
								'url': url,
								'title': title,
								'table': table,
								'tag_id': tag_id,
								'desc': desc
							},
							success: function(obj){
								$par.find('.id-hidden').val(obj);
								if(!tag_id){
									occ_list.push({
										id: obj,
										title: $par.find('.title').val(),
										table: table
									});
									update_tag_list();

									$('#occasion-images-form').append('<fieldset data-tag="' + obj + '" id="occasion-tag-' + obj + '" class="tag-images"></fieldset>')
								}
							}
						});
					}
				}
			});
		}
		function update_delete_click(){
			$('.delete-occasion').click(function(){
				var $par = $(this).parent(),
				id = $par.find('.id-hidden').val(),
				table = $par.data('table');
				tag_id = $par.data('tag');
				$par.hide(animTime, function(){
					$par.remove();
					if(id.length){
						$.ajax({
							type: 'POST',
							url: ajaxurl,
							data: {
								'action': 'occ_delete',
								'id': id,
								'table': table
							},
							success: function(){
								if(!tag_id){
									for(i = 0; i < occ_list.length; ++i){
										if(occ_list[i].id == id){
											occ_list.splice(i, 1);
										}
										update_tag_list();
									}
								}
							}
						});
					}
				});
			});
		}
	</script>

	<div class="wrap">
		<header>
			<h1>TSB Occasions Editor</h1>
			<p>Add, modify, and delete options. As you edit this page, the app will be updated in real time. So be careful of what you edit here! The title will be what is displayed in the list. The tag is what you tag all of your images as. If you need to change a title, I would recommend leaving the tag as it is. Users will never see a tag name, and when you update a tag you will have to manually update all image tags.</p>
			<p>Changes are made in real time. When you delete an occasion, <strong>it is deleted on all apps</strong>.</p>
		</header>
		<section id="occ-tags">
			<form id="occasion-form" method="post" action="">
				<input type="button" value="New Occasion" id="add-occasion" />
				<?php foreach($occasions as $o) : ?>
				<fieldset data-table="occasion" class="occasion">
					<input name="id" type="hidden" class="id-hidden" value="<?php echo $o->id ?>" /><input placeholder="Occasion Title" name="title" class="title text-input" size="30" value="<?php echo $o->title ?>" /><input type="button" value="Delete" class="delete-occasion" />
				</fieldset>
				<?php endforeach ?>
			</form>
		</section>
		<section id="occ-images">
			<header>
				<h1>Edit images for each Occasion</h1>
				<p>Select a tag and then add, modify, and delete images.&nbsp;<select id="occasion-image-select"></select></p>
				<input type="button" value="New Image" id="add-image" />
			</header>
			<form id="occasion-images-form" method="post" action="">
				<?php foreach ($occasions as $o): ?>
				<fieldset data-tag="<?php echo $o->id ?>" id="occasion-tag-<?php echo $o->id ?>" class="tag-images">
					<?php foreach ($occ_images as $i): ?>
						<?php if($i->tag_id == $o->id) : ?>
						<div data-table="image" data-tag="<?php echo $o->id ?>" class="image-container">
							<input name="id" type="hidden" class="id-hidden" value="<?php echo $i->id ?>" /><input name="title" class="title text-input" size="20" placeholder="Image Title" value="<?php echo $i->title ?>" /><input placeholder="URL" name="url" class="url text-input" size="40" value="<?php echo $i->url ?>" /><input type="button" value="Delete" class="delete-occasion" /><br /><br /><input placeholder="One or two sentence description." value="<?php echo $i->descr ?>" name="desc" class="desc text-input" size="66" />
						</div>
						<?php endif ?>
					<?php endforeach ?>
				</fieldset>
				<?php endforeach ?>
			</form>
		</section>
	</div>
	<?php
}

?>