<?php
class Basic_Dev_Tools_Cron_Manager {
	var $table_name = 'basictools_cron_schedules';
	var $schedules_manager = false;
	var $crons_manager = false;
	var $protected_crons = array(	'wp_maybe_auto_update',
									'wp_version_check',
									'wp_update_plugins',
									'wp_update_themes',
									'wp_scheduled_delete',
									'wp_scheduled_auto_draft_delete');

	var $protected_schedules = array();

	public function __construct() {
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

		if($_GET['schedule_manager_add']=='true' || $_GET['schedule_manager_edit'])
			$this->schedules_manager->set_action('list', false);
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
		$cron_array = _get_cron_array();
		if(is_array($cron_array) && count($cron_array)>0) {
			foreach($cron_array as $cron_timestamp=>$crons) {
				if(is_array($crons) && count($crons)>0) {
					foreach($crons as $cron_key=>$cron_values) {
						/*
						echo '<pre>';
						print_r($cron_timestamp);
						echo '</pre>';
						echo '<pre>';
						print_r($cron_key);
						echo '</pre>';
						echo '<pre>';
						print_r($cron_values);
						echo '</pre>';
						*/
					}
				}
			}
		}

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
			.wrap .add-new-h2, .wrap .add-new-h2:active {
				float: right;
			}
		</style>
		<div class="wrap acm metabox-holder">
			<div id="notifications"></div>
			<h2>Advanced Cron Manager</h2>
			<div class="error" style="display: none;" id="notif-flex"><p><strong></strong></p></div>
			<div class="updated" style="display: none;" id="notif-schedule-added"><p><strong>Schedule added successfully.</strong></p></div>
			<div class="updated" style="display: none;" id="notif-schedule-removed"><p><strong>Schedule removed successfully.</strong></p></div>
			<div class="updated" style="display: none;" id="notif-task-added"><p><strong>Task added successfully.</strong></p></div>
			<div class="updated" style="display: none;" id="notif-task-removed"><p><strong>Task removed successfully.</strong></p></div>
			<div class="updated" style="display: none;" id="notif-task-executed"><p><strong>Task executed successfully.</strong></p></div>
			<div class="basic-dev-tools-cron-manager-table">
				<table cellspacing="0" class="wp-list-table widefat fixed crons">
					<thead>
						<tr>
							<th class="manage-column column-hook" id="hook" scope="col"><span>Hook</span></th>
							<th class="manage-column column-schedule" id="schedule" scope="col"><span>Schedule</span></th>
							<th class="manage-column column-args" id="args" scope="col"><span>Arguments</span></th>
							<th class="manage-column column-next" id="next" scope="col"><span>Next execution</span></th>
							<th class="manage-column column-next" id="next" scope="col"><span>Action</span></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th class="manage-column column-hook" id="hook" scope="col"><span>Hook</span></th>
							<th class="manage-column column-schedule" id="schedule" scope="col"><span>Schedule</span></th>
							<th class="manage-column column-args" id="args" scope="col"><span>Arguments</span></th>
							<th class="manage-column column-next" id="next" scope="col"><span>Next execution</span></th>
							<th class="manage-column column-next" id="next" scope="col"><span>Action</span></th>
						</tr>
					</tfoot>
					<?php /*
					<tbody>
						<tr class="single-cron cron-f749f0fb alternate">
							<td class="column-hook">wp_version_check<div class="row-actions">Task protected</div></td>
							<td class="column-schedule">twicedaily</td><td class="column-args"></td>
							<td data-timestamp="1430206453" class="column-next">In 11 hours<br>28.04.2015 07:34:13</td>
							<td class="column-action"><a data-args="" data-noonce="dd3b86ea6a" data-task="wp_version_check" class="execute-task button-secondary">Execute</a></td>
						</tr>
						<tr class="single-cron cron-2889786a ">
							<td class="column-hook">wp_update_plugins<div class="row-actions">Task protected</div></td>
							<td class="column-schedule">twicedaily</td><td class="column-args"></td>
							<td data-timestamp="1430206453" class="column-next">In 11 hours<br>28.04.2015 07:34:13</td>
							<td class="column-action"><a data-args="" data-noonce="e465ab4015" data-task="wp_update_plugins" class="execute-task button-secondary">Execute</a></td>
						</tr>
						<tr class="alternate" id="add_task_row">
							<td colspan="5"><button class="button-secondary" id="show_task_form">Add Task</button></td>
						</tr>
						<tr style="display: none;" class="alternate" id="add_task_form_row">
							<form method="POST" id="add_task_form"></form>
							<td><input type="text" placeholder="schedule_hook_for_action" required="" class="widefat" name="schedule_hook" id="schedule_hook"></td>
							<td>
								<span>Execute now +</span>
								<span><input type="number" value="0" min="0" required="" name="timestamp_offset" id="timestamp_offset"> </span>
								<span>seconds. </span>
								<span>Then repeat</span>
								<span>
									<select required="" name="schedule" id="select-schedule">
										<option value="lala">lala</option>
										<option value="hourly">Once Hourly</option>
										<option value="twicedaily">Twice Daily</option>
										<option value="daily">Once Daily</option>
										<option value="single">Don't repeat</option>
									</select>
								</span>
							</td>
							<td>
								<div id="arguments-list"></div>
								<a class="button-secondary" id="add_argument_input">Add Argument</a>
							</td>
							<td colspan="2">
								<a class="button-primary" data-noonce="e683cd8a1a" name="add-task" id="add-task">Add task</a>
							</td>
						</tr>
					</tbody>
					*/ ?>
				</table>
			</div>
			<div class="basic-dev-tools-schedules-manager-table">
				<div class="postbox">
					<?php $this->schedules_manager->init();?>
				</div>
			</div>
		</div>
	<?php }
}

global $basic_dev_tools_cron_manager_obj;
$basic_dev_tools_cron_manager_obj = new Basic_Dev_Tools_Cron_Manager();
?>