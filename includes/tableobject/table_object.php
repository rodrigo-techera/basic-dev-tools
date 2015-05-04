<?php
class tableObject {
	var $instance_name;
	var $title;
	var $table;
	var $primary_key;
	var $primary_key_format;
	var $actions;
	var $page;
	var $fields;
	var $fields_format;
	var $table_options;
	var $filters_fieldShow;
	var $filters_fieldAdd;
	var $filters_beforeSave;
	var $filters_afterSave;
	var $filters_beforeDelete;
	var $filters_afterDelete;
	var $templates;
	var $error_msg;

	function __construct($params) {
		$this->filters_fieldShow = array();
		$this->filters_fieldAdd = array();
		$this->filters_beforeSave = array();
		$this->filters_afterSave = array();
		$this->filters_beforeDelete = array();
		$this->filters_afterDelete = array();
		
		$this->actions = array(	'list' => true,
								'add' => true,
								'edit' => true,
								'delete' => true);

		$this->templates = array(	'directory' => 'templates',
									'show' => 'generic_show.php',
									'form' => 'generic_form.php');

		$this->table = $params['table'];
		$this->primary_key = $params['primary_key'];
		$this->primary_key_format = $params['primary_key_format'];
		$this->title = $params['title'];
		$this->fields = $params['fields'];
		$this->table_options = $params['table_options'];

		$this->instance_name = isset($params['instance_name'])?$params['instance_name']:'';
		if($this->instance_name)
			$this->instance_name .= '_';

		$this->page = $_GET['page'];
		$this->error_msg = array();
	}

	function set_action($action, $new_value) {
		if(!$action || !array_key_exists($action, $this->actions))
			return false;

		$this->actions[$action] = $new_value;
		return true;
	}

	function set_template($option, $new_value) {
		if(!$option || !array_key_exists($option, $this->templates))
			return false;

		$this->templates[$option] = $new_value;
		return true;
	}

	function set_table_options($option, $new_value) {
		if(!$option)
			return false;

		$this->table_options[$option] = $new_value;
		return true;
	}

	function init() {
		$this->inject_css();
		$this->inject_js();
		
		$show_list = true;
		if(isset($_GET[$this->instance_name.'add']) && $_GET[$this->instance_name.'add']=='true') {
			if($this->actions['add']) {
				$this->add();
			}
		} elseif(isset($_GET[$this->instance_name.'edit']) && $_GET[$this->instance_name.'edit']=='true' && isset($_GET[$this->instance_name.'id']) && is_numeric($_GET[$this->instance_name.'id']) && $_GET[$this->instance_name.'id']) {
			if($this->actions['edit']) {
				$this->edit($_GET[$this->instance_name.'id']);
			}
		} elseif(isset($_GET[$this->instance_name.'delete']) && $_GET[$this->instance_name.'delete']=='true' && isset($_GET[$this->instance_name.'id']) && is_numeric($_GET[$this->instance_name.'id']) && $_GET[$this->instance_name.'id']) {
			if($this->actions['delete'])
				$this->delete($_GET[$this->instance_name.'id']);
		}

		if($this->actions['list'])
			$this->show();
	}

	function getRows() {
		global $wpdb;

		if($this->table_options['select'])
			$get_rows_sql = $this->table_options['select'].' ';
		else 
			$get_rows_sql = 'SELECT * ';

		$get_rows_sql .= 'FROM '.$this->table.' ';

		if(isset($this->table_options['alias']) && $this->table_options['alias'])
			$get_rows_sql .= 'AS '.$this->table_options['alias'].' ';

		if(isset($this->table_options['join']) && $this->table_options['join'])
			foreach($this->table_options['join'] as $join)
				$get_rows_sql .= $join.' ';

		if(isset($this->table_options['where']) && $this->table_options['where'])
			$get_rows_sql .= $this->table_options['where'].' ';

		if(isset($this->table_options['final_sql']) && $this->table_options['final_sql'])
			$get_rows_sql .= $this->table_options['final_sql'];
		
		$rows_array = $wpdb->get_results($wpdb->prepare($get_rows_sql, $this->table_options['query_formats']), ARRAY_A);

		return $rows_array;
	}

	function getRow($id) {
		global $wpdb;

		if(!$id || !is_numeric($id))
			return false;

		if($this->table_options['select'])
			$get_row_sql = $this->table_options['select'];
		else 
			$get_row_sql = 'SELECT * ';

		$get_row_sql .= 'FROM '.$this->table.' ';
		
		if($this->table_options['alias'])
			$get_row_sql .= 'AS '.$this->table_options['alias'].' ';

		if($this->table_options['join'])
			foreach($this->table_options['join'] as $join)
				$get_row_sql .= $join.' ';

		if($this->table_options['where'])
			$get_row_sql .= $this->table_options['where'].' ';

		if($this->table_options['final_sql'])
			$get_row_sql .= $this->table_options['final_sql'];

		$get_row_sql = 'SELECT * FROM '.$this->table.' WHERE '.$this->primary_key.'='.$this->primary_key_format;
		$row_array = $wpdb->get_row($wpdb->prepare($get_row_sql, array($id)), ARRAY_A);

		return $row_array;
	}

	function show() {
		$rows = $this->getRows();
		
		if(isset($this->table_options['display_array']) && is_array($this->table_options['display_array']) && count($this->table_options['display_array'])>0) {
			$display_array = array_reverse($this->table_options['display_array'], true);
			foreach($display_array as $array_key=>$array_value) {
				$array_value['protected'] = true;
				array_unshift($rows, $array_value);
			}
		}

		$template = $this->templates['directory'].'/'.$this->templates['show'];
		include_once($template);
	}

	function show_form() {
		if($_POST) {
			global $user_ID;
			$fields = array();
			
			foreach($_POST as $field_code=>$field_value) {
				if(is_string($field_value))
					$fields[$field_code] = trim($field_value);
				elseif(is_array($field_value) || is_object($field_value))
					$fields[$field_code] = serialize($field_value);

				$fields[$field_code] = $this->apply_edit_filters($field_code, $fields[$field_code]);
			}

			//reset POST with the output of the filters
			$_POST = $fields;

			if($_GET[$this->instance_name.'save'] && !count($this->error_msg)>0) {
				global $wpdb;

				if(isset($fields['id']))
					unset($fields['id']);
				
				$fields = $this->apply_before_save_filters($fields);
				
				$fields_format = array();
				foreach($fields as $field_name=>$field_value)
					$fields_format[] = $this->fields['add'][$field_name]['format'];

				if($_REQUEST['id']) {
					$where[$this->primary_key] = $_REQUEST['id'];
					$where_format[$this->primary_key] = $this->primary_key_format;
					
					$result = $wpdb->update($this->table, $fields, $where, $fields_format, $where_format);
					$id = $_REQUEST['id'];
				} else {
					$result = $wpdb->insert($this->table, $fields, $fields_format);
					$id = $wpdb->insert_id;
				}

				$fields['id'] = $id;

				$fields = $this->apply_after_save_filters($fields);
				unset($_POST, $_GET);
				
				$request_uri = str_replace('add=true', '', str_replace('edit=true', '', str_replace('save=true', '', $_SERVER['REQUEST_URI'])));
				echo '<script language="javascript">document.location="'.$request_uri.'";</script>';
				return;
			}
		}

		$template = $this->templates['directory'].'/'.$this->templates['form'];
		include_once($template);
	}

	function add() {
		$this->show_form();
	}

	function edit($id) {
		if(!$_POST)
			$_POST = $this->getRow($id);

		$this->show_form();
	}

	function delete($id) {
		if($id && is_numeric($id) && $id>0) {
			global $wpdb;

			$fields_values = $this->getRow($id);
			$this->apply_before_delete_filters($fields_values);

			$where[$this->primary_key] = $id;
			$wpdb->delete($this->table, $where, $this->primary_key_format);

			$this->apply_after_delete_filters($fields_values);

			$request_uri = str_replace('delete=true', '', $_SERVER['REQUEST_URI']);
			echo '<script language="javascript">document.location="'.$request_uri.'";</script>';
			return;
		}
	}

	function add_show_filter($field, $function_name) {
		$this->filters_fieldShow[$field][] = $function_name;
	}

	function apply_show_filters($field, $row_values) {
		if(isset($this->filters_fieldShow[$field]) && is_array($this->filters_fieldShow[$field]))
			foreach($this->filters_fieldShow[$field] as $filter_index=>$function_name)
				if(is_array($function_name)) {
					if(method_exists($function_name[0], $function_name[1]))
						$row_values[$field] = $function_name[0]->$function_name[1]($row_values);
				} elseif(is_string($function_name)) {
					if(method_exists($this, $function_name))
						$row_values[$field] = $this->$function_name($row_values);
					else
						$row_values[$field] = call_user_func($function_name, $row_values);
				}
		
		return $row_values[$field];
	}

	function add_edit_filter($field, $function_name) {
		$this->filters_fieldAdd[$field][] = $function_name;
	}

	function apply_edit_filters($field_code, $field_value) {
		if(isset($this->filters_fieldAdd[$field_code]) && is_array($this->filters_fieldAdd[$field_code]))
			foreach($this->filters_fieldAdd[$field_code] as $filter_index=>$function_name) {
				if(method_exists($this, $function_name))
					$field_value = $this->$function_name($field_code, $field_value);
				else
					$field_value = call_user_func($function_name, $field_value);
			}
		
		return $field_value;
	}

	function add_before_save_filter($function_name) {
		$this->filters_beforeSave[] = $function_name;
	}

	function add_after_save_filter($function_name) {
		$this->filters_afterSave[] = $function_name;
	}

	function apply_before_save_filters($fields_values) {
		foreach($this->filters_beforeSave as $filter_index=>$function_name) {
			if(is_array($function_name)) {
				if(method_exists($function_name[0], $function_name[1]))
					$fields_values = $function_name[0]->$function_name[1]($fields_values);
			} elseif(is_string($function_name))
				$fields_values = call_user_func($function_name, $fields_values);
		}

		return $fields_values;
	}
	
	function apply_after_save_filters($fields_values) {
		foreach($this->filters_afterSave as $filter_index=>$function_name) {
			$fields_values = call_user_func($function_name, $fields_values);
		}

		return $fields_values;
	}

	function add_before_delete_filter($function_name) {
		$this->filters_beforeDelete[] = $function_name;
	}

	function add_after_delete_filter($function_name) {
		$this->filters_afterDelete[] = $function_name;
	}

	function apply_before_delete_filters($fields_values) {
		foreach($this->filters_beforeDelete as $filter_index=>$function_name) {
			$fields_values = call_user_func($function_name, $fields_values);
		}

		return $fields_values;
	}

	function apply_after_delete_filters($fields_values) {
		foreach($this->filters_afterDelete as $filter_index=>$function_name) {
			$fields_values = call_user_func($function_name, $fields_values);
		}

		return $fields_values;
	}

	function render_field($field_name, $field_settings) {
		switch($field_settings['render']) {
			case 'text':
				return '<input id="field_'.$field_name.'" type="text" name="'.$field_name.'" value="'.(isset($_POST[$field_name])?$_POST[$field_name]:'').'">';
			break;
			case 'password':
				return '<input id="field_'.$field_name.'" type="password" name="'.$field_name.'" value="">';
			break;
			case 'textarea':
				return '<textarea id="field_'.$field_name.'" name="'.$field_name.'">'.(isset($_POST[$field_name])?$_POST[$field_name]:'').'</textarea>';
			break;
			case 'listbox':
				if(is_array($field_settings['values'])) {
					$values = $field_settings['values'];
				} elseif(is_string($field_settings['values'])) {
					$values = call_user_func($field_settings['values'], $_POST[$field_name]);
				}

				$result = '<select id="field_'.$field_name.'" name="'.$field_name.'">';
				foreach($values as $value_code=>$value_title) {
					$result .= '<option value="'.$value_code.'"';
					if(isset($_POST[$field_name]) && $value_code==$_POST[$field_name])
						$result .= ' selected="selected"';
					$result .='>'.$value_title.'</option>';
				}
				$result .= '</select>';

				return $result;
			break;
			case 'checkboxes':
				if(is_array($field_settings['values'])) {
					$values = $field_settings['values'];
					
					$selected_values = unserialize($_POST[$field_name]);
					
					$result = '';
					foreach($values as $value_code=>$value_title)
						$result .= '<span><input type="checkbox" name="'.$field_name.'[]" value="'.$value_code.'"'.(is_array($selected_values)&&in_array($value_code, $selected_values)?'checked="checked"':'').'>'.$value_title.'</span>';
					
					return $result;
				}
			break;
			case 'nText':
				$result = '';
				$values = unserialize($_POST[$field_name]);
				if($values && is_array($values)) {
					foreach($values as $value_index=>$value_values) {
						$positions_qty = count($value_values);
						break;
					}

					for($i=0;$i<$positions_qty;$i++) {
						$result .= '<span>';
						foreach($field_settings['values'] as $input_name) {
							$result .= '<input type="text" name="'.$input_name.'[]" value="'.$values[$input_name][$i].'"> ';
						}
						$result .= '<a href="#" class="nText_delete_button"'.($positions_qty<=1?'style="display:none;"':'').'>x</a></span>';
					}
				} else {
					$result .= '<span>';
					foreach($field_settings['values'] as $input_name) {
						$result .= '<input type="text" name="'.$input_name.'[]" value=""> ';
					}
					$result .= '<a href="#" class="nText_delete_button" style="display:none;">x</a></span>';
				}

				$result .='<input type="button" value="add" class="nText_add_button">';

				return $result;
			break;
			case 'image':
				return '<input id="field_'.$field_name.'" type="file" name="'.$field_name.'"> '.(isset($_POST[$field_name])?$_POST[$field_name]:'');
			break;
		}
	}

	function require_field($field_name, $field_value) {
		if(!$field_value) {
			$this->error_msg['You must complete all required fields.'] = 'You must complete all required fields.';
		}

		return $field_value;
	}

	function show_debug() {
		echo '<pre>';
		print_r($this);
		echo '</pre>';
	}

	function inject_css() { ?>
		<style>
			.modifier_user_id {
				width:40px;
				text-align: center;
			}
			.last_modified {
				width:70px;
				text-align: center;
			}
			.date_created {
				width:70px;
				text-align: center;
			}
			.id {
				width:30px;
			}
			.wp-admin select, .wp-admin input, .wp-admin textarea {
				width: 100%;
			}
			.wp-admin #add input[type="submit"]
			.wp-admin #add input[type="text"] {
			    width: 100px;
			}
			.wp-admin select, .wp-admin input, .wp-admin textarea {
				width:auto;
			}
		</style>
	<?php }

	function inject_js() { ?>
		<script language="javascript" type="text/javascript">
			jQuery(document).ready(function () {
				jQuery('.nText_delete_button').click(function (e) {
					e.preventDefault();

					var span_container = jQuery(this).parent().parent();
					if(jQuery(span_container).find('span').length>1) {
						jQuery(this).parent().fadeOut('fast', function () {
							jQuery(this).remove();

							if(jQuery(span_container).find('span').length==1)
								jQuery(span_container).find('span a.nText_delete_button').hide();
						});
					}
				});

				jQuery('.nText_add_button').click(function (e) {
					var this_obj = this;
					jQuery(this).siblings('span:lt(1)').each(function () {
						var new_inputs = jQuery(this).clone(true);
						jQuery(new_inputs).find('input').each(function () {
							jQuery(this).val('');
						});
						jQuery(new_inputs).insertBefore(this_obj);
					});

					jQuery(this).parent().parent().find('span a.nText_delete_button').show();
				});
			});
		</script>
	<?php }
}
?>