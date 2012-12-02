<?php

/*
*  Advanced Custom Fields Lite
*
*  @description: this is an example of a functions.php file and demonstrates how to use ACF Lite in your theme
*  @Author: Elliot Condon
*  @Author URI: http://www.elliotcondon.com/
*  @Copyright: Elliot Condon
*/


/*
*  1. Copy the "acf" folder to your theme and include it
*/ 

require_once('acf/acf-lite.php');


/*
*  2. Use the new "acf_settings" hook to setup your ACF settings
*  http://www.advancedcustomfields.com/docs/filters/acf_settings/
*/

function my_acf_settings( $options )
{
    // activate add-ons
    $options['activation_codes']['repeater'] = 'XXXX-XXXX-XXXX-XXXX';
    $options['activation_codes']['options_page'] = 'XXXX-XXXX-XXXX-XXXX';
 
    
    // set options page structure
    $options['options_page']['title'] = 'Global Options';
    $options['options_page']['pages'] = array('Header', 'Footer')
    
        
    return $options;
    
}
add_filter('acf_settings', 'my_acf_settings');


/*
*  3. Register any custom fields
*     http://www.advancedcustomfields.com/docs/tutorials/creating-and-registering-your-own-field/
*/

register_field('acf_file_path', dirname(__File__) . '/fields/file-path/file-path.php');



/*
*  4. Register your field groups
*     Field groups can be exported to PHP from the WP "Advanced Custom Fields" plugin.
*/

register_field_group(array (
	'id' => 'field_group_1',
	'title' => 'Page Fields',
	'fields' => array(
		array (
			'key' => 'field_1',
			'label' => 'Text',
			'name' => 'text',
			'type' => 'text',
			'instructions' => 'Add some text please',
			'required' => '0',
		),
		array (
			'key' => 'field_2',
			'label' => 'Content',
			'name' => 'content',
			'type' => 'wysiwyg',
			'instructions' => 'Add some text please',
			'required' => '1',
			'default_value' => '',
			'toolbar' => 'full',
			'media_upload' => 'yes',
			'the_content' => 'yes',
		),
	),
	'location' => array (
		'rules' => array (
			array (
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'page',
				'order_no' => '0',
			),
		),
		'allorany' => 'all',
	),
	'options' => 
	array (
		'position' => 'normal',
		'layout' => 'no_box',
		'hide_on_screen' => 
		array (
		),
	),
	'menu_order' => 0,
));

