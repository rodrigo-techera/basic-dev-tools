<?php
class Basic_Dev_Tools_Cron_Manager {
	var $table_name = 'basictools_cron_schedules';
	var $schedules_manager = false;
	var $crons_manager = false;
	var $cron_form_errors = array();
	var $protected_crons = array(	'wp_maybe_auto_update',
									'wp_version_check',
									'wp_update_plugins',
									'wp_update_themes',
									'wp_scheduled_delete',
									'wp_scheduled_auto_draft_delete');

	var $protected_schedules = array();

	public function __construct() {
		//Schedule Manager
		require_once('tableobject/table_object.php');

		$params['table'] = $this->table_name;
		$params['instance_name'] = 'schedule_manager';
		$params['primary_key'] = 'id';
		$params['primary_key_format'] = '%d';

		$params['title'] = array(	'singular'=>'Schedule',
									'plural'  =>'Schedules');

		$params['fields']['show'] = array(	'schedule_key' => 'Key',
											'description' => 'Description');
		
		$params['fields']['add']['schedule_key'] = array(	'title' => 'Schedule Key',
															'render' => 'text',
															'format' => '%s');
		$params['fields']['add']['schedule_interval'] = array(	'title' => 'Interval in Seconds',
																'render' => 'text',
																'format' => '%d');
		$params['fields']['add']['description'] = array('title' => 'Description',
														'render' => 'text',
														'format' => '%s');
		
		$params['table_options']['final_sql'] = 'ORDER BY schedule_key';

		$protected_schedules = wp_get_schedules();
		foreach($protected_schedules as $protected_key=>$protected_values) {
			$this->protected_schedules[] = array(	'schedule_key' => $protected_key,
													'description' => $protected_values['display']);
		}

		$this->schedules_manager = new tableObject($params);
		$this->schedules_manager->set_template('show', 'reduced_show.php');
		$this->schedules_manager->set_template('form', 'reduced_form.php');
		$this->schedules_manager->set_table_options('display_array', $this->protected_schedules);
		$this->schedules_manager->add_before_save_filter(array($this, 'verify_schedule_key'));

		if($_GET['schedule_manager_add']=='true' || $_GET['schedule_manager_edit'])
			$this->schedules_manager->set_action('list', false);

		add_filter('cron_schedules', array($this, 'add_all_schedules_to_filter'));

		//Task Manager
		if(isset($_GET['cron_manager_save']) && $_GET['cron_manager_save']=='true') {
			if($_POST['cron_hook'] && $_POST['cron_schedule']) {
				//check if the cron hook already exists
				$tasks = $this->get_all_tasks();
				foreach($tasks as $cron_key=>$cron_values) {
					if($cron_key==$_POST['cron_hook']) {
						$this->cron_form_errors[] = 'Cron Hook already exists.';
						break;
					}
				}

				if(count($this->cron_form_errors)==0) {
					$cron_hook = strtolower(trim(str_replace(' ', '_', $_POST['cron_hook'])));
					$args = $_POST['cron_arguments'];
					foreach($args as $arg_index=>$arg_values) {
						if(!trim($arg_values))
							unset($args[$arg_index]);
					}

					if(wp_next_scheduled($cron_hook)) {
						$this->cron_form_errors[] = 'The task that you are trying to add is already scheduled.';
					} else {
						$status = wp_schedule_event(time(), $_POST['cron_schedule'], $cron_hook, $args);
						if($status===false)
							$this->cron_form_errors[] = 'The task that you are trying to add could not be scheduled.';
					}
				}
			} else {
				$this->cron_form_errors[] = 'You need to complete the fields.';
			}
		}

		if(isset($_GET['cron_manager_delete']) && $_GET['cron_manager_delete']=='true' && isset($_GET['cron_manager_id']) && $_GET['cron_manager_id']) {
			//check if the cron hook are not in protected
			$protected_tasks = $this->protected_crons;
			$cron_key = $_GET['cron_manager_id'];
			foreach($protected_tasks as $index=>$cron_values) {
				if(in_array($cron_key, $this->protected_crons)) {
					$this->cron_form_errors[] = 'You cannot delete protected Task.';
					break;
				}
			}

			if(count($this->cron_form_errors)==0) {
				$all_tasks = $this->get_all_tasks();
				foreach($all_tasks as $task_index=>$task_values) {
					if($task_index==$cron_key) {
						wp_clear_scheduled_hook($cron_key, $task_values['args']);
						$this->cron_form_errors[] = 'The task with the key "'.$cron_key.'" has been deleted.';
						break;
					}
				}
			}
		}

		if(isset($_GET['cron_manager_execute']) && $_GET['cron_manager_execute']=='true' && isset($_GET['cron_manager_id']) && $_GET['cron_manager_id']) {
			$cron_key = $_GET['cron_manager_id'];
			$all_tasks = $this->get_all_tasks();
			foreach($all_tasks as $task_index=>$task_values) {
				if($task_index==$cron_key) {
					if(isset($task_values['args']) && is_array($task_values['args']) && count($task_values['args'])>0) {
						do_action_ref_array($cron_key, $task_values['args']);
					} else {
						do_action($cron_key);
					}
					
					$this->cron_form_errors[] = 'The task with the key "'.$cron_key.'" has been executed.';
					break;
				}
			}
		}
	}

	public function plugin_activation() {
		global $wpdb;

		$sql = 'CREATE TABLE IF NOT EXISTS `'.$this->table_name.'` (
					`id` int(11) NOT NULL,
				  	`schedule_key` varchar(255) NOT NULL,
				  	`schedule_interval` int(11) NOT NULL,
				  	`description` varchar(255) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8';
		$wpdb->query($sql);

		$sql = 'ALTER TABLE `'.$this->table_name.'` ADD PRIMARY KEY (`id`)';
		$wpdb->query($sql);
		
		$sql = 'ALTER TABLE `'.$this->table_name.'` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT';
		$wpdb->query($sql);
	}

	public function plugin_deactivation() {
		global $wpdb;

		$sql = 'DROP TABLE '.$this->table_name;
		$wpdb->query($sql);
	}

	public function show() {
		?>
		<style>
			.basic-dev-tools-cron-manager-table, .basic-dev-tools-schedules-manager-table {
				float: left;
			}

			.basic-dev-tools-cron-manager-table {
				width: 68%;
			}
			.basic-dev-tools-schedules-manager-table {
				width: 30%;
				margin-left: 2%;
			}
			a.remove {
				color: #FF0000;
			}
			h3>a {
				text-decoration: none;
			}
			.wrap .add-new-h2.secondary, .wrap .add-new-h2.secondary:active {
				float: right;
			}
			.button-secondary {
				width: 100%;
				text-align: center;
			}
			.cron-manager-form {
				width: 38%;
			}
			.arguments-field {
				padding-top: 14px;
				vertical-align: top;
			}
			.wp-admin select.schedule-field {
				width: 88%;
			}
		</style>
		<div class="wrap acm metabox-holder">
			<h2>
				Task Manager
				<?php if(!(isset($_GET['cron_manager_add']) && $_GET['cron_manager_add']=='true')) { ?>
					<a href="/wp-admin/admin.php?page=basic-dev-tools/includes/cron-manager.php&cron_manager_add=true" class="add-new-h2">New Task</a>
				<?php } ?>
			</h2>
			<?php if(is_array($this->cron_form_errors) && count($this->cron_form_errors)>0) { ?>
				<div class="error"><p><?php echo implode('</p><p>', $this->cron_form_errors);?></p></div>
			<?php } ?>
			<?php if(isset($_GET['cron_manager_add']) && $_GET['cron_manager_add']=='true') { ?>
				<h3>Add New Task <a href="/wp-admin/admin.php?page=basic-dev-tools/includes/cron-manager.php" class="add-new-h2">cancel</a></h3>
				<form enctype="multipart/form-data" action="/wp-admin/admin.php?page=basic-dev-tools/includes/cron-manager.php&cron_manager_save=true" method="post">
					<table cellspacing="0" cellpadding="10" border="0" class="cron-manager-form">
						<tbody>
							<tr id="row_schedule_key">
								<td><label style="margin-right:5%;">Hook:</label></td>
								<td><input type="text" value="" name="cron_hook" id="cron_hook"></td>
							</tr>
							<tr id="row_schedule_interval">
								<td><label style="margin-right:5%;">Scheduled:</label></td>
								<td>
									<select name="cron_schedule" class="schedule-field">
										<?php
											$actual_schedules = $this->get_all_schedules();
											foreach($actual_schedules as $schedule_index=>$schedule_values) { ?>
												<option value="<?php echo $schedule_values['schedule_key'];?>"><?php echo $schedule_values['description'];?></option>
											<?php }
										?>
									</select>
								</td>
							</tr>
							<tr id="row_description">
								<td class="arguments-field"><label style="margin-right:5%;">Arguments:</label></td>
								<td>
									<span><input type="text" value="" name="cron_arguments[]"> <a href="#" class="nText_delete_button" style="display:none;">x</a></span>
									<br><input type="button" value="Add new argument" class="nText_add_button">
								</td>
							</tr>
							<tr>
								<td style="text-align:right;" colspan="2"><input type="submit" class="button-primary" value="Save"></td>
							</tr>
						</tbody>
					</table>
				</form>
			<?php } ?>
			<div class="basic-dev-tools-cron-manager-table">
				<table cellspacing="0" class="wp-list-table widefat fixed crons">
					<thead>
						<tr>
							<th class="manage-column column-hook"><span>Hook</span></th>
							<th class="manage-column column-schedule"><span>Schedule</span></th>
							<th class="manage-column column-args"><span>Arguments</span></th>
							<th class="manage-column column-next"><span>Next execution</span></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th class="manage-column column-hook"><span>Hook</span></th>
							<th class="manage-column column-schedule"><span>Schedule</span></th>
							<th class="manage-column column-args"><span>Arguments</span></th>
							<th class="manage-column column-next"><span>Next execution</span></th>
						</tr>
					</tfoot>
					<tbody>
					<?php
						$tasks = $this->get_all_tasks();
						foreach($tasks as $cron_key=>$cron_values) { ?>
							<tr class="alternate">
								<td class="post-title page-title column-title">
									<?php echo $cron_key;?>
									<div class="row-actions">
										<?php if(in_array($cron_key, $this->protected_crons)) {
											//pass	
										} else { ?>
											<span class="trash"><a onclick="if(!confirm('Are you sure you want to delete this?')) { return false;}" class="submitdelete" href="/wp-admin/admin.php?page=basic-dev-tools/includes/cron-manager.php&cron_manager_delete=true&cron_manager_id=<?php echo $cron_key;?>" title="Delete this Task">Delete</a></span> |
										<?php } ?>
										<span><a href="/wp-admin/admin.php?page=basic-dev-tools/includes/cron-manager.php&cron_manager_execute=true&cron_manager_id=<?php echo $cron_key;?>" title="Execute this Task">Execute</a></span>
									</div>
								</td>
								<td class="column-schedule"><?php echo $cron_values['schedule']?$cron_values['schedule']:'single';?></td>
								<td class="column-args"><?php echo implode(', ', $cron_values['args']);?></td>
								<td class="column-next">In <?php echo human_time_diff($cron_values['timestamp'], time()).'<br>'.date('m/d/Y H:i:s', $cron_values['timestamp']);?></td>
							</tr>
							<?php
						}
					?>
					</tbody>
				</table>
			</div>
			<div class="basic-dev-tools-schedules-manager-table">
				<div class="postbox">
					<?php $this->schedules_manager->init();?>
				</div>
			</div>
		</div>
	<?php }

	function verify_schedule_key($fields_values, $tableobject_instance) {
		$schedules = $this->get_all_schedules();
		foreach($schedules as $schedule_key=>$schedule_values) {
			if($fields_values['schedule_key']==$schedule_values['schedule_key']) {
				if(isset($fields_values['id']) && isset($schedule_values['id'])) {
					if($fields_values['id']!=$schedule_values['id']) {
						$tableobject_instance->error_msg[] = 'Schedule key already exists.';
						break;
					}
				} else {
					$tableobject_instance->error_msg[] = 'Schedule key already exists.';
					break;
				}
			}
		}
		
		return $fields_values;
	}
	function add_all_schedules_to_filter() {
		$actual_schedules = $this->schedules_manager->get_rows();
		foreach($actual_schedules as $schedule_key=>$schedule_values) {
			$schedules[$schedule_values['schedule_key']] = array(	'interval' => $schedule_values['schedule_interval'],
																	'display' => $schedule_values['description']);
		}
		
		return $schedules;
	}

	function get_all_schedules() {
		$schedules = $this->protected_schedules;
		
		$actual_schedules = $this->schedules_manager->get_rows();
		foreach ($actual_schedules as $schedule_key=>$schedule_values) {
			$schedules[] = array(	'schedule_key' => $schedule_values['schedule_key'],
									'description' => $schedule_values['description'],
									'id' => $schedule_values['id']);
		}
		
		return $schedules;
	}

	function get_all_tasks() {
		$tasks = array();
		$cron_array = _get_cron_array();
		if(is_array($cron_array) && count($cron_array)>0)
			foreach($cron_array as $cron_timestamp=>$crons)
				if(is_array($crons) && count($crons)>0)
					foreach($crons as $cron_key=>$cron_values) {
						list($hash_key, $cron_values) = each($cron_values);
						$tasks[$cron_key] = array(	'schedule' => $cron_values['schedule'],
													'args' => $cron_values['args'],
													'timestamp' => $cron_timestamp);
					}

		return $tasks;
	}
}

global $basic_dev_tools_cron_manager_obj;
$basic_dev_tools_cron_manager_obj = new Basic_Dev_Tools_Cron_Manager();
?>