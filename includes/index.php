<?php
if(isset($_POST['basic_dev_tools_special_settings_save']) && $_POST['basic_dev_tools_special_settings_save']=='true') {
	if($_POST['basic_dev_tools_show_admin_bar']=='yes') {
		update_option('basic_dev_tools_show_admin_bar', true);
	} else {
		update_option('basic_dev_tools_show_admin_bar', false);
	}
}
?>
<div class="wrap">
	<h2>Special Settings</h2>
	<form method="post">
		<input type="hidden" name="basic_dev_tools_special_settings_save" value="true">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="blogname">Show Admin Bar</label></th>
					<td>
						<label><input type="radio"<?php echo get_option('basic_dev_tools_show_admin_bar', false)?' checked="checked"':'';?> value="yes" name="basic_dev_tools_show_admin_bar"> Yes</label>
						<label><input type="radio"<?php echo get_option('basic_dev_tools_show_admin_bar', false)?'':' checked="checked"';?> value="no" name="basic_dev_tools_show_admin_bar"> No</label>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" value="Save Changes" class="button button-primary" name="save">
		</p>
	</form>
</div>