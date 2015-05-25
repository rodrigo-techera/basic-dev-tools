<?php
if(isset($_POST['basic_dev_tools_special_settings_save']) && $_POST['basic_dev_tools_special_settings_save']=='true') {
	update_option('basic_dev_tools_hide_admin_bar', $_POST['basic_dev_tools_hide_admin_bar']=='yes'?true:false);
	update_option('basic_dev_tools_disable_theme_updates', $_POST['basic_dev_tools_disable_theme_updates']=='yes'?true:false);
	update_option('basic_dev_tools_disable_plugin_updates', $_POST['basic_dev_tools_disable_plugin_updates']=='yes'?true:false);
	update_option('basic_dev_tools_disable_core_updates', $_POST['basic_dev_tools_disable_core_updates']=='yes'?true:false);
}
?>
<div class="wrap">
	<h2>Special Settings</h2>
	<form method="post">
		<input type="hidden" name="basic_dev_tools_special_settings_save" value="true">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="blogname">Hide Admin Bar</label></th>
					<td>
						<label><input type="radio"<?php echo get_option('basic_dev_tools_hide_admin_bar', false)?' checked="checked"':'';?> value="yes" name="basic_dev_tools_hide_admin_bar"> Yes</label>
						<label><input type="radio"<?php echo get_option('basic_dev_tools_hide_admin_bar', false)?'':' checked="checked"';?> value="no" name="basic_dev_tools_hide_admin_bar"> No</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="blogname">Disable Theme Updates</label></th>
					<td>
						<label><input type="radio"<?php echo get_option('basic_dev_tools_disable_theme_updates', false)?' checked="checked"':'';?> value="yes" name="basic_dev_tools_disable_theme_updates"> Yes</label>
						<label><input type="radio"<?php echo get_option('basic_dev_tools_disable_theme_updates', false)?'':' checked="checked"';?> value="no" name="basic_dev_tools_disable_theme_updates"> No</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="blogname">Disable Plugin Updates</label></th>
					<td>
						<label><input type="radio"<?php echo get_option('basic_dev_tools_disable_plugin_updates', false)?' checked="checked"':'';?> value="yes" name="basic_dev_tools_disable_plugin_updates"> Yes</label>
						<label><input type="radio"<?php echo get_option('basic_dev_tools_disable_plugin_updates', false)?'':' checked="checked"';?> value="no" name="basic_dev_tools_disable_plugin_updates"> No</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="blogname">Disable Core Updates</label></th>
					<td>
						<label><input type="radio"<?php echo get_option('basic_dev_tools_disable_core_updates', false)?' checked="checked"':'';?> value="yes" name="basic_dev_tools_disable_core_updates"> Yes</label>
						<label><input type="radio"<?php echo get_option('basic_dev_tools_disable_core_updates', false)?'':' checked="checked"';?> value="no" name="basic_dev_tools_disable_core_updates"> No</label>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" value="Save Changes" class="button button-primary" name="save">
		</p>
	</form>
</div>