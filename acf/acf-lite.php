<?php

/*
*  Advanced Custom Fields Lite
*
*  @description: a lite version of the Advanced Custom Fields WordPress plugin.
*  @Version: 3.5.4
*  @Author: Elliot Condon
*  @Author URI: http://www.elliotcondon.com/
*  @License: GPL
*  @Copyright: Elliot Condon
*/

include('core/api.php');

$acf = new acf_lite();

class acf_lite
{ 
	var $dir,
		$path,
		$version,
		$upgrade_version,
		$fields,
		$cache,
		$defaults,
		
		
		// controllers
		$input,
		$options_page,
		$everything_fields,
		$third_party;
	
	
	/*
	*  Constructor
	*
	*  @description: 
	*  @since 1.0.0
	*  @created: 23/06/12
	*/
	
	function __construct()
	{
		$this->path = dirname(__FILE__);
		$this->dir = str_replace(ABSPATH, get_bloginfo('url') . '/', $this->path);
		$this->version = '3.5.4';
		$this->cache = array(); // basic array cache to hold data throughout the page load
		$this->defaults = array(
			'options_page' => array(
				'capability' => 'edit_posts', // capability to view options page
				'title' => __('Options','acf'), // title / menu name ('Site Options')
				'pages' => array(), // an array of sub pages ('Header, Footer, Home, etc')
			),
			'activation_codes' => array(
				'repeater'			=> '', // activation code for the repeater add-on (XXXX-XXXX-XXXX-XXXX)
				'options_page'		=> '', // activation code for the options page add-on (XXXX-XXXX-XXXX-XXXX)
				'flexible_content'	=> '', // activation code for the flexible content add-on (XXXX-XXXX-XXXX-XXXX)
				'gallery'			=> '', // activation code for the gallery add-on (XXXX-XXXX-XXXX-XXXX)
			),
		);
			
			
		
		// controllers
		$this->setup_controllers();
		
		
		// actions
		add_action('init', array($this, 'init'));
		
		add_action('acf_save_post', array($this, 'acf_save_post'), 10); // save post, called from many places (api, input, everything, options)
		
		add_filter('acf_load_field', array($this, 'acf_load_field_defaults'), 5);
		
		// ajax
		add_action('wp_ajax_get_input_metabox_ids', array($this, 'get_input_metabox_ids'));
		
		
		return true;
	}
	
	
	/*
	*  Init
	*
	*  @description: 
	*  @since 1.0.0
	*  @created: 23/06/12
	*/
	
	function init()
	{
		// setup defaults
		$this->defaults = apply_filters('acf_settings', $this->defaults);
		
		
		// allow for older filters
		$this->defaults['options_page']['title'] = apply_filters('acf_options_page_title', $this->defaults['options_page']['title']);
		
		
		// setup fields
		$this->setup_fields();

	}
	
	
	/*
	*  get_cache
	*
	*  @description: Simple ACF (once per page) cache
	*  @since 3.1.9
	*  @created: 23/06/12
	*/
	
	function get_cache($key = false)
	{
		// key is required
		if( !$key )
			return false;
		
		
		// does cache at key exist?
		if( !isset($this->cache[$key]) )
			return false;
		
		
		// return cahced item
		return $this->cache[$key];
	}
	
	
	/*
	*  set_cache
	*
	*  @description: Simple ACF (once per page) cache
	*  @since 3.1.9
	*  @created: 23/06/12
	*/
	
	function set_cache($key = false, $value = null)
	{
		// key is required
		if( !$key )
			return false;
		
		
		// update the cache array
		$this->cache[$key] = $value;
		
		
		// return true. Probably not needed
		return true;
	}
	
	
	/*
	*  setup_fields
	*
	*  @description: Create an array of field objects, including custom registered field types
	*  @since 1.0.0
	*  @created: 23/06/12
	*/
	
	function setup_fields()
	{
		// include parent field
		include_once('core/fields/acf_field.php');
		
		
		// include child fields
		include_once('core/fields/acf_field.php');
		include_once('core/fields/text.php');
		include_once('core/fields/textarea.php');
		include_once('core/fields/wysiwyg.php');
		include_once('core/fields/image.php');
		include_once('core/fields/file.php');
		include_once('core/fields/number.php');
		include_once('core/fields/select.php');
		include_once('core/fields/checkbox.php');
		include_once('core/fields/radio.php');
		include_once('core/fields/true_false.php');
		include_once('core/fields/page_link.php');
		include_once('core/fields/post_object.php');
		include_once('core/fields/relationship.php');
		include_once('core/fields/date_picker/date_picker.php');
		include_once('core/fields/color_picker.php');
		
		
		// add child fields
		$this->fields['none'] = new acf_Field($this); 
		$this->fields['text'] = new acf_Text($this); 
		$this->fields['textarea'] = new acf_Textarea($this); 
		$this->fields['wysiwyg'] = new acf_Wysiwyg($this); 
		$this->fields['image'] = new acf_Image($this); 
		$this->fields['file'] = new acf_File($this); 
		$this->fields['number'] = new acf_Number($this); 
		$this->fields['select'] = new acf_Select($this); 
		$this->fields['checkbox'] = new acf_Checkbox($this);
		$this->fields['radio'] = new acf_Radio($this);
		$this->fields['true_false'] = new acf_True_false($this);
		$this->fields['page_link'] = new acf_Page_link($this);
		$this->fields['post_object'] = new acf_Post_object($this);
		$this->fields['relationship'] = new acf_Relationship($this);
		$this->fields['date_picker'] = new acf_Date_picker($this);
		$this->fields['color_picker'] = new acf_Color_picker($this);
		
		
		// add repeater
		if($this->is_field_unlocked('repeater'))
		{
			include_once('core/fields/repeater.php');
			$this->fields['repeater'] = new acf_Repeater($this);
		}
		
		
		// add flexible content
		if($this->is_field_unlocked('flexible_content'))
		{
			include_once('core/fields/flexible_content.php');
			$this->fields['flexible_content'] = new acf_Flexible_content($this);
		}
		
		
		// add gallery
		if($this->is_field_unlocked('gallery'))
		{
			include_once('core/fields/gallery.php');
			$this->fields['gallery'] = new acf_Gallery($this);
		}
		
		
		// hook to load in third party fields
		$custom = apply_filters('acf_register_field',array());
		if(!empty($custom))
		{
			foreach($custom as $v)
			{
				//var_dump($v['url']);
				include($v['url']);
				$name = $v['class'];
				$custom_field = new $name($this);
				$this->fields[$custom_field->name] = $custom_field;
			}
		}
		
	}
	
	
	/*
	*  setup_fields
	*
	*  @description: 
	*  @since 3.2.6
	*  @created: 23/06/12
	*/

	function setup_controllers()
	{

		// input
		include_once('core/controllers/input.php');
		$this->input = new acf_input($this);
		
		
		// options page
		include_once('core/controllers/options_page.php');
		$this->options_page = new acf_options_page($this);
		
		
		// everthing fields
		include_once('core/controllers/everything_fields.php');
		$this->everything_fields = new acf_everything_fields($this);
		
		
		// Third Party Compatibility
		include_once('core/controllers/third_party.php');
		$this->third_party = new acf_third_party($this);
	}
	

	/*--------------------------------------------------------------------------------------
	*
	*	get_field_groups
	*
	*	This function returns an array of post objects found in the get_posts and the 
	*	register_field_group calls.
	*
	*	@author Elliot Condon
	*	@since 3.0.6
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_field_groups()
	{
		// return cache
		$cache = $this->get_cache('acf_field_groups');
		if($cache != false)
		{
			return $cache;
		}
		
		
		$acfs = array();
		
		
		// hook to load in registered field groups
		$acfs = apply_filters('acf_register_field_group', $acfs);
		
		
		// update cache
		$this->set_cache('acf_field_groups', $acfs);
		
		
		// return
		if( empty($acfs) )
		{
			return false;
		}
		
		
		return $acfs;
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_acf_field
	*	- returns a field
	*	- $post_id can be passed to make sure the correct field is loaded. Eg: a duplicated
	*	field group may have the same field_key, but a different post_id
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/

	function get_acf_field( $field_key, $post_id = false )
	{
		
		// return cache
		$cache = $this->get_cache('acf_field_' . $field_key);
		if($cache != false)
		{
			return $cache;
		}
		
		
		// hook to load in registered field groups
		$acfs = $this->get_field_groups();
		
		if($acfs)
		{
			// loop through acfs
			foreach($acfs as $acf)
			{
				// loop through fields
				if($acf['fields'])
				{
					foreach($acf['fields'] as $field)
					{
						if($field['key'] == $field_key)
						{
							// apply filters
							$field = apply_filters('acf_load_field', $field);
							
							$keys = array('type', 'name', 'key');
							foreach( $keys as $key )
							{
								if( isset($field[ $key ]) )
								{
									$value = apply_filters('acf_load_field-' . $field[ $key ], $field);
								}
							}
							
							
							// set cache
							$this->set_cache('acf_field_' . $field_key, $field);
							
							return $field;
						}
					}
				}
				// if($acf['fields'])
			}
			// foreach($acfs as $acf)
		}
		// if($acfs)

 		
 		return null;
	}
	
	
	/*
	*  acf_load_field_defaults
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 14/10/12
	*/
	
	function acf_load_field_defaults( $field )
	{
		if( !is_array($field) )
		{
			return $field;	
		}
		
		$defaults = array(
			'key' => '',
			'label' => '',
			'name' => '',
			'type' => 'text',
			'order_no' =>	'1',
			'instructions' =>	'',
			'required' => '0',
			'conditional_logic' => array(
				'status' => '0',
				'allorany' => 'all',
				'rules' => false
			),
		);
		
		$field = array_merge($defaults, $field);
		
		return $field;
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	create_field
	*
	*	@author Elliot Condon
	*	@since 1.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function create_field($field)
	{
		
		if(!isset($this->fields[$field['type']]) || !is_object($this->fields[$field['type']]))
		{
			_e('Error: Field Type does not exist!','acf');
			return false;
		}
		
		
		// defaults - class
		if( !isset($field['class']) )
		{
			$field['class'] = $field['type'];
		}
		
		
		// defaults - id
		// - isset is needed for the edit field group page where fields are created without many parameters
		if( !isset($field['id']) )
		{
			if( isset($field['key']) )
			{
				$field['id'] = 'acf-' . $field['key'];
			}
			else
			{
				$field['id'] = 'acf-' . $field['name'];
			}
		}
		
		
		$this->fields[ $field['type'] ]->create_field($field);
		

		// conditional logic
		// - isset is needed for the edit field group page where fields are created without many parameters
		if( isset($field['conditional_logic']) && $field['conditional_logic']['status'] == '1' ):
		
			$join = ' && ';
			if( $field['conditional_logic']['allorany'] == "any" )
			{
				$join = ' || ';
			}
			
			?>
<script type="text/javascript">
(function($){
	
	// create the conditional function
	$(document).live('acf/conditional_logic/<?php echo $field['key']; ?>', function(){
		
		var field = $('.field-<?php echo $field['key']; ?>');		
<?php

		$if = array();
		foreach( $field['conditional_logic']['rules'] as $rule )
		{
			$if[] = 'acf.conditional_logic.calculate({ field : "'. $field['key'] .'", toggle : "' . $rule['field'] . '", operator : "' . $rule['operator'] .'", value : "' . $rule['value'] . '"})' ;
		}
		
?>
		if(<?php echo implode( $join, $if ); ?>)
		{
			field.show();
		}
		else
		{
			field.hide();
		}
		
	});
	
	
	// add change events to all fields
<?php foreach( $field['conditional_logic']['rules'] as $rule ): ?>
	$('.field-<?php echo $rule['field']; ?> *[name]').live('change', function(){
		$(document).trigger('acf/conditional_logic/<?php echo $field['key']; ?>');
	});
<?php endforeach; ?>
	
	$(document).live('acf/setup_fields', function(e, postbox){
		$(document).trigger('acf/conditional_logic/<?php echo $field['key']; ?>');
	});
		
})(jQuery);
</script>
			<?php
		endif;
	}
	
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_value
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_value($post_id, $field)
	{
		if( empty($this->fields) )
		{
			$this->setup_fields();
		}
		
		if( !isset($field['type'], $this->fields[ $field['type'] ]) )
		{
			return false;
		}
				
		return $this->fields[$field['type']]->get_value($post_id, $field);
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_value_for_api
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_value_for_api($post_id, $field)
	{
		if( empty($this->fields) )
		{
			$this->setup_fields();
		}
		
		if( !isset($field['type'], $this->fields[ $field['type'] ]) )
		{
			return false;
		}
		
		return $this->fields[$field['type']]->get_value_for_api($post_id, $field);
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	update_value
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function update_value($post_id, $field, $value)
	{
		if( isset($field['type'], $this->fields[ $field['type'] ]) )
		{
			$this->fields[$field['type']]->update_value($post_id, $field, $value);
		}
	}
	
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	format_value_for_api
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function format_value_for_api($value, $field)
	{
		return $this->fields[$field['type']]->format_value_for_api($value, $field);
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	create_format_data
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function create_format_data($field)
	{
		return $this->fields[$field['type']]->create_format_data($field);
	}
	
	
	/*
	*  render_fields_for_input
	*
	*  @description: 
	*  @since 3.1.6
	*  @created: 23/06/12
	*/
	
	function render_fields_for_input($fields, $post_id)
	{
			
		// create fields
		if($fields)
		{
			foreach($fields as $field)
			{
				// if they didn't select a type, skip this field
				if(!$field['type'] || $field['type'] == 'null') continue;
				
				
				// set value
				$field['value'] = $this->get_value($post_id, $field);
				
				$required_class = "";
				$required_label = "";
				
				if($field['required'] == "1")
				{
					$required_class = ' required';
					$required_label = ' <span class="required">*</span>';
				}
				
				echo '<div id="acf-' . $field['name'] . '" class="field field-' . $field['type'] . ' field-'.$field['key'] . $required_class . '">';

					echo '<p class="label">';
						echo '<label for="fields[' . $field['key'] . ']">' . $field['label'] . $required_label . '</label>';
						echo $field['instructions'];
					echo '</p>';
					
					$field['name'] = 'fields[' . $field['key'] . ']';
					$this->create_field($field);
				
				echo '</div>';
				
			}
			// foreach($fields as $field)
		}
		// if($fields)
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_input_metabox_ids
	*	- called by function.fields to hide / show metaboxes
	*	
	*	@author Elliot Condon
	*	@since 2.0.5
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_input_metabox_ids($overrides = array(), $json = true)
	{
		// overrides
		if(isset($_POST))
		{
			$override_keys = array(
				'post_id',
				'post_type',
				'page_template',
				'page_parent',
				'page_type',
				'page',
				'post',
				'post_category',
				'post_format',
				'taxonomy',
				'lang',
			);

			foreach( $override_keys as $override_key )
			{
				if( isset($_POST[ $override_key ]) && $_POST[ $override_key ] != 'false' )
				{
					$overrides[ $override_key ] = $_POST[ $override_key ];
				}
			}

		}
		

		// WPML
		if( isset($overrides['lang']) )
		{
			global $sitepress;
			$sitepress->switch_lang( $overrides['lang'] );
		}
		
		
		// create post object to match against
		$post = isset($overrides['post_id']) ? get_post($overrides['post_id']) : false;
		
		
		// find all acf objects
		$acfs = $this->get_field_groups();
		
		
		// blank array to hold acfs
		$return = array();
		
		if($acfs)
		{
			foreach($acfs as $acf)
			{
				$add_box = false;

				if($acf['location']['allorany'] == 'all')
				{
					// ALL
					$add_box = true;
					
					if($acf['location']['rules'])
					{
						foreach($acf['location']['rules'] as $rule)
						{
							
							// if any rules dont return true, dont add this acf
							if(!$this->match_location_rule($post, $rule, $overrides))
							{
								$add_box = false;
							}
						}
					}
					
				}
				elseif($acf['location']['allorany'] == 'any')
				{
					// ANY
					
					$add_box = false;
					
					if($acf['location']['rules'])
					{
						foreach($acf['location']['rules'] as $rule)
						{
							// if any rules return true, add this acf
							if($this->match_location_rule($post, $rule, $overrides))
							{
								$add_box = true;
							}
						}
					}
				}
							
				if($add_box == true)
				{
					$return[] = $acf['id'];
				}
				
			}
		}
		
		if($json)
		{
			echo json_encode($return);
			die;
		}
		else
		{
			return $return;
		}
		
		
	}
	
	
	
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	match_location_rule
	*
	*	@author Elliot Condon
	*	@since 2.0.0
	* 
	*-------------------------------------------------------------------------------------*/

	function match_location_rule($post = null, $rule = array(), $overrides = array())
	{
		
		// no post? Thats okay if you are one of the bellow exceptions. Otherwise, return false
		if(!$post)
		{
			$exceptions = array(
				'user_type',
				'options_page',
				'ef_taxonomy',
				'ef_user',
				'ef_media',
				'post_type',
			);
			
			if( !in_array($rule['param'], $exceptions) )
			{
				return false;
			}
		}
		
		
		if(!isset($rule['value']))
		{
			return false;
		}
		
		
		switch ($rule['param']) {
		
			// POST TYPE
		    case "post_type":
		    
		    	$post_type = isset($overrides['post_type']) ? $overrides['post_type'] : get_post_type($post);
		        
		        if($rule['operator'] == "==")
		        {
		        	if($post_type == $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if($post_type != $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        
		        break;
		        
		    // PAGE
		    case "page":
		        
		        $page = isset($overrides['page']) ? $overrides['page'] : $post->ID;
		        
		        if($rule['operator'] == "==")
		        {
		        	if($page == $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if($page != $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        
		        break;
		        
			// PAGE
		    case "page_type":
		        
		        $page = isset($overrides['page']) ? $overrides['page'] : $post->ID;
		        
		        if( $rule['value'] == 'front_page')
		        {
			        $front_page = (int) get_option('page_on_front');
			        
			        if( $rule['operator'] == "==" )
			        {
			        	if( $front_page == $page )
			        	{
				        	return true;
			        	}
			        }
			        elseif( $rule['operator'] == "!=" )
			        {
			        	if( $front_page != $page )
			        	{
				        	return true;
			        	}
			        }
			        
			        return false;
		        }
		        
		        
		        if( $rule['value'] == 'posts_page')
		        {
			        $posts_page = (int) get_option('page_for_posts');
			        
			        if( $rule['operator'] == "==" )
			        {
			        	if( $posts_page == $page )
			        	{
				        	return true;
			        	}
			        }
			        elseif( $rule['operator'] == "!=" )
			        {
			        	if( $posts_page != $page )
			        	{
				        	return true;
			        	}
			        }
			        
			        return false;
		        }
		        
		        
		        if( $rule['value'] == 'parent')
		        {
		        	$children = get_pages(array(
		        		'post_type' => $post->post_type,
		        		'child_of' =>  $post->ID,
		        	));
		        	
			        
			        if( $rule['operator'] == "==" )
			        {
			        	if( count($children) > 0 )
			        	{
				        	return true;
			        	}
			        }
			        elseif( $rule['operator'] == "!=" )
			        {
			        	if( count($children) == 0 )
			        	{
				        	return true;
			        	}
			        }
			        
			        return false;
		        }
		        
		        
		        if( $rule['value'] == 'child')
		        {
		        	$post_parent = $post->post_parent;
		        	if( isset($overrides['page_parent']) )
		        	{
			        	$post_parent = (int) $overrides['page_parent'];
		        	}
			        
			        if( $rule['operator'] == "==" )
			        {
			        	if( $post_parent != 0 )
			        	{
				        	return true;
			        	}
			        }
			        elseif( $rule['operator'] == "!=" )
			        {
			        	if( $post_parent == 0 )
			        	{
				        	return true;
			        	}
			        }
			        
			        return false;
		        }
		        
		        		        
		        break;
		        
		    // PAGE PARENT
		    case "page_parent":
		        
		        $page_parent = isset($overrides['page_parent']) ? $overrides['page_parent'] : $post->post_parent;
		        
		        if($rule['operator'] == "==")
		        {
		        	if($page_parent == $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        	
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if($page_parent != $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        
		        break;
		    
		    // PAGE
		    case "page_template":
		        
		        $page_template = isset($overrides['page_template']) ? $overrides['page_template'] : get_post_meta($post->ID,'_wp_page_template',true);
		        
		        if($rule['operator'] == "==")
		        {
		        	if($page_template == $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	if($rule['value'] == "default" && !$page_template)
		        	{
		        		return true;
		        	}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if($page_template != $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        
		        break;
		       
		    // POST
		    case "post":
		        
		        $post_id = isset($overrides['post']) ? $overrides['post'] : $post->ID;
		        
		        if($rule['operator'] == "==")
		        {
		        	if($post_id == $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if($post_id != $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        
		        break;
		        
		    // POST CATEGORY
		    case "post_category":
		        
		        $cats = array();
		        
		        if(isset($overrides['post_category']))
		        {
		        	$cats = $overrides['post_category'];
		        }
		        else
		        {
		        	$all_cats = get_the_category($post->ID);
		        	foreach($all_cats as $cat)
					{
						$cats[] = $cat->term_id;
					}
		        }
		        if($rule['operator'] == "==")
		        {
		        	if($cats)
					{
						if(in_array($rule['value'], $cats))
						{
							return true; 
						}
					}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if($cats)
					{
						if(!in_array($rule['value'], $cats))
						{
							return true; 
						}
					}
		        	
		        	return false;
		        }
		        
		        break;
			
			
			// USER TYPE
		    case "user_type":
		        		
		        if($rule['operator'] == "==")
		        {
		        	if(current_user_can($rule['value']))
		        	{
		        		return true;
		        	}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if(!current_user_can($rule['value']))
		        	{
		        		return true;
		        	}
		        	
		        	return false;
		        }
		        
		        break;
		    
		    // Options Page
		    case "options_page":
		    	
		    	global $plugin_page;
		    	
		    	
				// older location rules may be "options-pagename"
				if( substr($rule['value'], 0, 8) == 'options-' )
				{
					$rule['value'] = 'acf-' . $rule['value'];
				}
				
				
				// older location ruels may be "Pagename"
				if( substr($rule['value'], 0, 11) != 'acf-options' )
				{
					$rule['value'] = 'acf-options-' . sanitize_title( $rule['value'] );
					
					// value may now be wrong (acf-options-options)
					if( $rule['value'] == 'acf-options-options' )
					{
						$rule['value'] = 'acf-options';
					}
				}
				
				
		        if($rule['operator'] == "==")
		        {
		        	if( $plugin_page == $rule['value'] )
		        	{
		        		return true;
		        	}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if( $plugin_page == $rule['value'] )
		        	{
		        		return true;
		        	}
		        	
		        	return false;
		        }
		        
		        break;
		    
		    
		    // Post Format
		    case "post_format":
		        
		       	
		       	$post_format = isset($overrides['post_format']) ? $overrides['post_format'] : get_post_format( $post->ID );
		        if($post_format == "0") $post_format = "standard";
		        
		        if($rule['operator'] == "==")
		        {
		        	if($post_format == $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if($post_format != $rule['value'])
		        	{
		        		return true; 
		        	}
		        	
		        	return false;
		        }
		        
		        
		        break;
		    
		    // Taxonomy
		    case "taxonomy":
		        
		        $terms = array();

		        if(isset($overrides['taxonomy']))
		        {
		        	$terms = $overrides['taxonomy'];
		        }
		        else
		        {
		        	$taxonomies = get_object_taxonomies($post->post_type);
		        	if($taxonomies)
		        	{
			        	foreach($taxonomies as $tax)
						{
							$all_terms = get_the_terms($post->ID, $tax);
							if($all_terms)
							{
								foreach($all_terms as $all_term)
								{
									$terms[] = $all_term->term_id;
								}
							}
						}
					}
		        }
		        
		        if($rule['operator'] == "==")
		        {
		        	if($terms)
					{
						if(in_array($rule['value'], $terms))
						{
							return true; 
						}
					}
		        	
		        	return false;
		        }
		       elseif($rule['operator'] == "!=")
		        {
		        	if($terms)
					{
						if(!in_array($rule['value'], $terms))
						{
							return true; 
						}
					}
		        	
		        	return false;
		        }
		        
		        
		        break;
			
			// Everything Fields: Taxonomy
		    case "ef_taxonomy":
		       	
		       	if( !isset($overrides['ef_taxonomy']) )
		       	{
		       		return false;
		       	}
		       	
		       	$ef_taxonomy = $overrides['ef_taxonomy'];
				
		        if($rule['operator'] == "==")
		        {
		        	if( $ef_taxonomy == $rule['value'] || $rule['value'] == "all" )
		       		{
		       			return true; 
		       		}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if( $ef_taxonomy != $rule['value'] || $rule['value'] == "all" )
		       		{
		       			return true; 
		       		}
		        	
		        	return false;
		        }
		        
		        
		        break;
			
			// Everything Fields: User
		    case "ef_user":
		       	
		       	if( !isset($overrides['ef_user']) )
		       	{
		       		return false;
		       	}
		       	
		       	$ef_user = $overrides['ef_user'];
				
		        if($rule['operator'] == "==")
		        {
		        	if( user_can($ef_user, $rule['value']) || $rule['value'] == "all" )
		       		{
		       			return true; 
		       		}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if( user_can($ef_user, $rule['value']) || $rule['value'] == "all" )
		       		{
		       			return true; 
		       		}
		        	
		        	return false;
		        }
		        
		        
		        break;
			
			// Everything Fields: Media
		    case "ef_media":
		       	
		       	if( !isset($overrides['ef_media']) )
		       	{
		       		return false;
		       	}
		       	
		       	$ef_media = $overrides['ef_media'];
				
		        if($rule['operator'] == "==")
		        {
		        	if( $rule['value'] == "all" )
		       		{
		       			return true; 
		       		}
		        	
		        	return false;
		        }
		        elseif($rule['operator'] == "!=")
		        {
		        	if( $rule['value'] == "all" )
		       		{
		       			return true; 
		       		}
		        	
		        	return false;
		        }
		        
		        
		        break;
		}
		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	is_field_unlocked
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function is_field_unlocked($field_name)
	{
		$hashes = array(
			'repeater'			=> 'bbefed143f1ec106ff3a11437bd73432',
			'options_page'		=> '1fc8b993548891dc2b9a63ac057935d8',
			'flexible_content'	=> 'd067e06c2b4b32b1c1f5b6f00e0d61d6',
			'gallery'			=> '69f4adc9883195bd206a868ffa954b49',
		);
			
		$hash = md5( $this->get_license_key($field_name) );
		
		if( $hashes[$field_name] == $hash )
		{
			return true;
		}
		
		return false;
		
	}
	
	/*--------------------------------------------------------------------------------------
	*
	*	is_field_unlocked
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_license_key($field_name)
	{
		
		$value = '';
		
		if( isset( $this->defaults['activation_codes'][ $field_name ] ) )
		{
			$value = $this->defaults['activation_codes'][ $field_name ];
		}

		return $value;
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	admin_message
	*
	*	@author Elliot Condon
	*	@since 2.0.5
	* 
	*-------------------------------------------------------------------------------------*/
	
	function admin_message($message = "", $type = 'updated')
	{
		$GLOBALS['acf_mesage'] = $message;
		$GLOBALS['acf_mesage_type'] = $type;
		
		add_action('admin_notices', array($this, 'acf_admin_notice'));
	}
	
	function acf_admin_notice()
	{
	    echo '<div class="' . $GLOBALS['acf_mesage_type'] . '" id="message">'.$GLOBALS['acf_mesage'].'</div>';
	}
		
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_taxonomies_for_select
	*
	*---------------------------------------------------------------------------------------
	*
	*	returns a multidimentional array of taxonomies grouped by the post type / taxonomy
	*
	*	@author Elliot Condon
	*	@since 3.0.2
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_taxonomies_for_select( $args = array() )
	{	
		// vars
		$post_types = get_post_types();
		$choices = array();
		$defaults = array(
			'simple_value'	=>	false,
		);
		
		$options = array_merge($defaults, $args);
		
		
		if($post_types)
		{
			foreach($post_types as $post_type)
			{
				$post_type_object = get_post_type_object($post_type);
				$taxonomies = get_object_taxonomies($post_type);
				if($taxonomies)
				{
					foreach($taxonomies as $taxonomy)
					{
						if(!is_taxonomy_hierarchical($taxonomy)) continue;
						$terms = get_terms($taxonomy, array('hide_empty' => false));
						if($terms)
						{
							foreach($terms as $term)
							{
								$value = $taxonomy . ':' . $term->term_id;
								
								if( $options['simple_value'] )
								{
									$value = $term->term_id;
								}
								
								$choices[$post_type_object->label . ': ' . $taxonomy][$value] = $term->name; 
							}
						}
					}
				}
			}
		}
		
		return $choices;
	}
	
	

	/*
	*  get_all_image_sizes
	*
	*  @description: returns an array holding all the image sizes
	*  @since 3.2.8
	*  @created: 6/07/12
	*/
	
	function get_all_image_sizes()
	{
		// find all sizes
		$all_sizes = get_intermediate_image_sizes();
		
		
		// define default sizes
		$image_sizes = array(
			'thumbnail'	=>	__("Thumbnail",'acf'),
			'medium'	=>	__("Medium",'acf'),
			'large'		=>	__("Large",'acf'),
			'full'		=>	__("Full",'acf')
		);
		
		
		// add extra registered sizes
		foreach($all_sizes as $size)
		{
			if (!isset($image_sizes[$size]))
			{
				$image_sizes[$size] = ucwords( str_replace('-', ' ', $size) );
			}
		}
		
		
		// return array
		return $image_sizes;
	}
	
	
	/*
	*  acf_save_post
	*
	*  @description: 
	*  @created: 4/09/12
	*/
	
	function acf_save_post( $post_id )
	{

		// load from post
		if( !isset($_POST['fields']) )
		{
			return false;
		}
		
		
		// loop through and save
		if( $_POST['fields'] )
		{
			foreach( $_POST['fields'] as $key => $value )
			{
				// get field
				$field = $this->get_acf_field($key);
				
				$this->update_value($post_id, $field, $value);
			}
			// foreach($fields as $key => $value)
		}
		// if($fields)
		
		
		return true;
	}
	
}
?>