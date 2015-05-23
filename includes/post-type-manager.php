<?php
class Basic_Dev_Tools_Post_Type_Manager {
	var $table_name = 'basictools_post_types';
	var $table_object = false;
	var $support_values = array('title' => 'Title',
								'editor' => 'Editor',
								'author' => 'Author',
								'thumbnail' => 'Feature Image',
								'excerpt' => 'Excerpt',
								'trackbacks' => 'Trackbacks',
								'custom-fields' => 'Custom Fields',
								'comments' => 'Comments',
								'revisions' => 'Revisions',
								'page-attributes' => 'Page Attriutes',
								'post-formats' => 'Post Formats');

	function __construct() {
		require_once('tableobject/table_object.php');

		$params['table'] = $this->table_name;
		$params['primary_key'] = 'id';
		$params['primary_key_format'] = '%d';

		$params['title'] = array(	'singular'=>'Post Type',
									'plural'  =>'Post Types Manager');

		$params['fields']['show'] = array(	'post_type_key' => 'Key',
											'singular_name' => 'Singular Name',
											'name' => 'Plural Name',
											'supports' => 'Supports',
											'taxonomies' => 'Taxonomies');

		$params['fields']['add']['post_type_key'] = array(	'title' => 'Key',
															'render' => 'text',
															'format' => '%s');
		$params['fields']['add']['name'] = array(	'title' => 'Plural Name',
													'render' => 'text',
													'format' => '%s');
		$params['fields']['add']['singular_name'] = array(	'title' => 'Singular Name',
															'render' => 'text',
															'format' => '%s');
		$params['fields']['add']['supports'] = array(	'title' => 'Supports',
														'render' => 'checkboxes',
														'format' => '%s',
														'values' => $this->support_values);
		$params['fields']['add']['taxonomies'] = array(	'title' => 'Custom Taxonomies',
														'render' => 'nText',
														'format' => '%s',
														'values' => array('taxonomy_key', 'taxonomy_name'));
		
		$params['table_options']['final_sql'] = 'ORDER BY name';

		$this->table_object = new tableObject($params);
		$this->table_object->add_before_save_filter(array($this, 'save_taxonomies'));
	}

	function plugin_activation() {
		global $wpdb;
		$sql = 'CREATE TABLE IF NOT EXISTS `'.$this->table_name.'` (
					`id` int(11) NOT NULL,
				  	`post_type_key` varchar(255) NOT NULL,
				  	`name` varchar(255) NOT NULL,
				  	`singular_name` varchar(255) NOT NULL,
				  	`supports` varchar(255) NOT NULL,
				  	`taxonomies` varchar(255) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8';
		$wpdb->query($sql);

		$sql = 'ALTER TABLE `'.$this->table_name.'` ADD PRIMARY KEY (`id`)';
		$wpdb->query($sql);
		
		$sql = 'ALTER TABLE `'.$this->table_name.'` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT';
		$wpdb->query($sql);
	}

	function plugin_deactivation() {
		global $wpdb;
		
		$sql = 'DROP TABLE '.$this->table_name;
		$wpdb->query($sql);
	}

	function show() {
		$this->table_object->add_show_filter('supports', array($this, 'show_support'));
		$this->table_object->add_show_filter('taxonomies', array($this, 'show_taxonomies'));
		$this->table_object->init();
	}

	function show_support($row_values) {
		$supports = unserialize($row_values['supports']);
		$supports_array = array();
		if($supports && is_array($supports))
			foreach($supports as $support_key)
				$supports_array[] = $this->support_values[$support_key];
		
		return implode(' | ', $supports_array);
	}

	function show_taxonomies($row_values) {
		$taxonomies = unserialize($row_values['taxonomies']);
		$taxonimes_array = array();
		if($taxonomies && is_array($taxonomies))
			foreach($taxonomies['taxonomy_name'] as $taxonomy_name)
				$taxonimes_array[] = $taxonomy_name;
		
		return implode(' | ', $taxonimes_array);
	}

	function save_taxonomies($row_values) {
		$row_values['taxonomies'] = serialize(array('taxonomy_key' => unserialize($row_values['taxonomy_key']),
													'taxonomy_name' => unserialize($row_values['taxonomy_name'])));

		unset($row_values['taxonomy_key'], $row_values['taxonomy_name']);

		return $row_values;
	}

	function add_post_types() {
		$post_types = $this->table_object->get_rows();
		foreach($post_types as $post_type_values) {
			$supports = unserialize($post_type_values['supports']);
			$taxonomies = unserialize($post_type_values['taxonomies']);

			register_post_type($post_type_values['post_type_key'],
								array(	'labels' => array(	'name' => $post_type_values['name'],
															'singular_name' => $post_type_values['singular_name']),
										'public' => true,
										'supports' => $supports,
										'taxonomies' => array()
								));

			if(isset($taxonomies['taxonomy_key']) && is_array($taxonomies['taxonomy_key']))
				foreach($taxonomies['taxonomy_key'] as $taxonomy_index=>$taxonomy_key) {
					register_taxonomy($taxonomy_key, $post_type_values['post_type_key'], array( 'hierarchical' => true,
					                  															'label' => $taxonomies['taxonomy_name'][$taxonomy_index],
				                  																'query_var' => true));
				}
		}
	}

	function process_shortcodes($atts) {
		if(isset($atts['options']) && $atts['options']) {
			$options = explode(',', $atts['options']);

			unset($atts['options']);
		} else
			$options = array('title', 'thumbnail', 'excerpt', 'readmore');
		

	    query_posts($atts);
	    while(have_posts()) {
	    	the_post(); ?>
			<div>
				<?php foreach($options as $option) {
					switch($option) {
						case 'id':
							?><div class="id"><?php the_ID();?></div><?php
						break;
						case 'title':
							?><div class="title"><?php the_title();?></div><?php
						break;
						case 'thumbnail':
							?><div class="thumbnail"><?php the_thumbnail();?></div><?php
						break;
						case 'excerpt':
							?><div class="excerpt"><?php the_excerpt();?></div><?php
						break;
						case 'content':
							?><div class="content"><?php the_content();?></div><?php
						break;
						case 'meta':
							?><div class="meta"><?php the_meta();?></div><?php
						break;
						case 'author':
							?><div class="author"><?php the_author();?></div><?php
						break;
						case 'readmore':
							?><div class="readmore"><a href="<?php the_permalink();?>">read more</a></div><?php
						break;
						case 'category':
							?><div class="category"><?php the_category();?></div><?php
						break;
						case 'tags':
							?><div class="tags"><?php the_tags();?></div><?php
						break;
						case 'terms':
							?><div class="tags"><?php the_terms();?></div><?php
						break;
					}
				} ?>
			</div>
		<?php }

		wp_reset_query();
	}
}

global $basic_dev_tools_post_type_manager_obj;
$basic_dev_tools_post_type_manager_obj = new Basic_Dev_Tools_Post_Type_Manager();
?>