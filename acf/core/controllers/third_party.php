<?php 

/*
*  third_party
*
*  @description: 
*  @since 3.5.1
*  @created: 23/06/12
*/
 
class acf_third_party 
{

	var $parent,
		$data;
	
	
	/*
	*  __construct
	*
	*  @description: 
	*  @since 3.1.8
	*  @created: 23/06/12
	*/
	
	function __construct($parent)
	{
		// vars
		$this->parent = $parent;
		$this->data['metaboxes'] = array();
		
		
		// Tabify Edit Screen - http://wordpress.org/extend/plugins/tabify-edit-screen/
		add_action('admin_head-settings_page_tabify-edit-screen', array($this,'admin_head_tabify'));
		
			
	}
	
	
	/*
	*  admin_head_tabify
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 9/10/12
	*/
	
	function admin_head_tabify()
	{
		// remove ACF from the tabs
		add_filter('tabify_posttypes', array($this, 'tabify_posttypes'));
		
		
		// add acf metaboxes to list
		add_action('tabify_add_meta_boxes' , array($this,'tabify_add_meta_boxes'));
		
	}
	
	
	/*
	*  tabify_posttypes
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 9/10/12
	*/
	
	function tabify_posttypes( $posttypes )
	{
		if( isset($posttypes['acf']) )
		{
			unset( $posttypes['acf'] );
		}
	
		return $posttypes;
	}
	
	
	/*
	*  tabify_add_meta_boxes
	*
	*  @description: 
	*  @since 3.5.1
	*  @created: 9/10/12
	*/
	
	function tabify_add_meta_boxes( $post_type )
	{
		// get acf's
		$acfs = $this->parent->get_field_groups();
		
		if($acfs)
		{
			foreach($acfs as $acf)
			{
				// add meta box
				add_meta_box(
					'acf_' . $acf['id'], 
					$acf['title'], 
					array($this, 'dummy'), 
					$post_type
				);
				
			}
			// foreach($acfs as $acf)
		}
		// if($acfs)
	}
	
	function dummy(){ /* Do Nothing */ }
	
	

}

?>