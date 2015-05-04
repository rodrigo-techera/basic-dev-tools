<?php if(isset($_GET[$this->instance_name.'id']) && $_GET[$this->instance_name.'id']) { ?>
	<h2>Update <?php echo $this->title['singular'];?> <a class="add-new-h2" href="/wp-admin/admin.php?page=<?php echo $this->page;?>">cancel</a></h2>
<?php } else { ?>
	<h2>Add New <?php echo $this->title['singular'];?> <a class="add-new-h2" href="/wp-admin/admin.php?page=<?php echo $this->page;?>">cancel</a></h2>
<?php } ?>
<form method="post" action="/wp-admin/admin.php?<?php echo $_SERVER['QUERY_STRING'];?>&<?php echo $this->instance_name;?>save=true" enctype="multipart/form-data">
	<input type="hidden" name="id" value="<?php if(isset($_GET[$this->instance_name.'id'])) echo $_GET[$this->instance_name.'id'];?>">
	<table id="add" cellspacing="0" cellpadding="10" border="0">
		<?php if(count($this->error_msg)>0) { ?>
		<tr>
			<td colspan="2" class="error">
				<?php foreach($this->error_msg as $msg) {?>
					<p><?php echo $msg;?></p>
				<?php }?>
			</td>
		</tr>
		<?php } ?>
		<?php foreach($this->fields['add'] as $field_name=>$field_settings) { ?>
			<?php if($this->primary_key==$field_name) { ?>
				<input type="hidden" name="id" value="<?php echo $_POST[$field_name];?>" />
			<?php } else { ?>
				<tr id="row_<?php echo $field_name;?>">
					<td style="width:50%;"><label style="margin-right: 5%;"><?php echo $field_settings['title'];?>:</label></td>
					<td style="width:40%;"><?php echo $this->render_field($field_name, $field_settings);?></td>
				</tr>
			<?php } ?>
		<?php } ?>
		<tr>
			<td colspan="2" style="text-align:right;"><input type="submit" value="<?php if(isset($_GET[$this->instance_name.'id']) && $_GET[$this->instance_name.'id']) { ?>Update<?php } else { ?>Create<?php } ?>" /></td>
		</tr>
	</table>
</form>