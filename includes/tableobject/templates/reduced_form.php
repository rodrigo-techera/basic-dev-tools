<style>
.wp-admin select, .wp-admin input, .wp-admin textarea {
	width:100%;
}
.error_reduced {
	background: #fff none repeat scroll 0 0;
    border-left: 4px solid #fff;
    box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);
    padding: 1px 12px;
    margin: 5px 0 15px;;
    border-color: #dd3d36;
}

.error_reduced p {
	margin: 0.5em 0;
    padding: 2px;
    font-size: 13px;
    line-height: 1.5;
}
</style>
<?php if(isset($_GET[$this->instance_name.'id']) && $_GET[$this->instance_name.'id']) { ?>
	<h3>Update <?php echo $this->title['singular'];?> <a class="add-new-h2 secondary" href="/wp-admin/admin.php?page=<?php echo $this->page;?>">cancel</a></h3>
<?php } else { ?>
	<h3>Add New <?php echo $this->title['singular'];?> <a class="add-new-h2 secondary" href="/wp-admin/admin.php?page=<?php echo $this->page;?>">cancel</a></h3>
<?php } ?>
<form method="post" action="/wp-admin/admin.php?<?php echo $_SERVER['QUERY_STRING'];?>&<?php echo $this->instance_name;?>save=true" enctype="multipart/form-data">
	<input type="hidden" name="id" value="<?php if(isset($_GET[$this->instance_name.'id'])) echo $_GET[$this->instance_name.'id'];?>">
	<table cellspacing="0" cellpadding="10" border="0">
		<?php if(count($this->error_msg)>0) { ?>
			<tr>
				<td colspan="2">
					<div class="error_reduced"><p><?php echo implode('</p><p>', $this->error_msg);?></p></div>
				</td>
			</tr>
		<?php } ?>
		<?php foreach($this->fields['add'] as $field_name=>$field_settings) { ?>
			<?php if($this->primary_key==$field_name) { ?>
				<input type="hidden" name="id" value="<?php echo $_POST[$field_name];?>" />
			<?php } else { ?>
				<tr id="row_<?php echo $field_name;?>">
					<td><label style="margin-right:5%;"><?php echo $field_settings['title'];?>:</label></td>
					<td><?php echo $this->render_field($field_name, $field_settings);?></td>
				</tr>
			<?php } ?>
		<?php } ?>
		<tr>
			<td colspan="2" style="text-align:right;"><input type="submit" class="button-primary" value="<?php if(isset($_GET[$this->instance_name.'id']) && $_GET[$this->instance_name.'id']) { ?>Update<?php } else { ?>Create<?php } ?>" /></td>
		</tr>
	</table>
</form>