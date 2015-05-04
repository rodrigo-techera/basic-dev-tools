<style>
	.widefat tbody th.check-column, .widefat tfoot th.check-column, .widefat thead th.check-column {
		width:0;
	}
</style>
<h3>
	<?php echo $this->title['plural'];?> 
	<?php if($this->actions['add']) { ?><a class="add-new-h2" href="/wp-admin/admin.php?page=<?php echo $this->page;?>&<?php echo $this->instance_name;?>add=true">New <?php echo $this->title['singular'];?></a><?php } ?>
</h3>
<table cellspacing="0" class="wp-list-table widefat fixed crons">
	<thead>
		<tr>
			<th class="manage-column column-cd check-column" scope="col"></th>
			<?php foreach($this->fields['show'] as $field_code=>$field_title) { ?>
				<?php if($this->primary_key==$field_code) { ?>
					<th class="manage-column id" scope="col"><?php echo $field_title;?></th>
				<?php } else { ?>
					<th class="manage-column <?php echo $field_code;?>" scope="col"><?php echo $field_title;?></th>
				<?php }?>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
		<?php if(is_array($rows) && count($rows)>0) { foreach($rows as $row_index=>$row_values) { ?>
		<tr valign="top" class="type-post status-publish format-standard hentry category-uncategorized alternate iedit author-self">
			<th class="check-column" scope="row"></th>
			<?php $first_row=true; foreach($this->fields['show'] as $field_code=>$field_title) {
				if($this->primary_key==$field_code) { ?>
					<td class="id"><?php echo $row_values[$field_code];?></td>
				<?php } else {
					$row_values[$field_code] = $this->apply_show_filters($field_code, $row_values);
					if($first_row) { $first_row=false;?>
						<td class="post-title page-title column-title">
							<strong>
								<?php if($this->actions['edit'] && !(isset($row_values['protected']) && $row_values['protected'])) { ?><a title="Edit “<?php echo $row_values[$field_code];?>”" href="/wp-admin/admin.php?page=<?php echo $this->page;?>&<?php echo $this->instance_name;?>edit=true&<?php echo $this->instance_name;?>id=<?php echo $row_values[$this->primary_key];?>" class="row-title"><?php } ?>
								<?php echo $row_values[$field_code];?>
								<?php if($this->actions['edit'] && !(isset($row_values['protected']) && $row_values['protected'])) { ?></a><?php } ?>
							</strong>
							<div class="row-actions">
								<?php if($this->actions['edit'] && !(isset($row_values['protected']) && $row_values['protected'])) { ?><span class="edit"><a title="Edit this <?php echo $this->title['singular'];?>" href="/wp-admin/admin.php?page=<?php echo $this->page;?>&<?php echo $this->instance_name;?>edit=true&<?php echo $this->instance_name;?>id=<?php echo $row_values[$this->primary_key];?>">Edit</a> | </span><?php } ?>
								<?php if($this->actions['delete'] && !(isset($row_values['protected']) && $row_values['protected'])) { ?><span class="trash"><a  title="Delete this <?php echo $this->title['singular'];?>" href="/wp-admin/admin.php?page=<?php echo $this->page;?>&<?php echo $this->instance_name;?>delete=true&<?php echo $this->instance_name;?>id=<?php echo $row_values[$this->primary_key];?>" class="submitdelete" onclick="if(!confirm('Are you sure you want to delete this?')) { return false;}">Delete</a></span><?php } ?>
							</div>
						</td>
					<?php } else { ?>
						<td class="<?php echo $field_code;?>"><?php echo $row_values[$field_code];?></td>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</tr>
		<?php }} ?>
	</tbody>
</table>