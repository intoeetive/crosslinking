<?php

/*
=====================================================
 Crosslinking
-----------------------------------------------------
 http://www.intoeetive.com/
-----------------------------------------------------
 Copyright (c) 2011-2012 Yuri Salimovskiy
=====================================================
 This software is intended for usage with
 ExpressionEngine CMS, version 2.0 or higher
=====================================================
 File: upd.crosslinking.php
-----------------------------------------------------
 Purpose: Automatic keyword crosslinking (for SEO purposes)
=====================================================
*/

if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}

require_once PATH_THIRD.'crosslinking/config.php';

class Crosslinking_upd {

    var $version = CROSSLINKING_ADDON_VERSION;
    
    function __construct() { 
        // Make a local reference to the ExpressionEngine super object 
        $this->EE =& get_instance(); 
    } 
    
    function install() { 
        
        $this->EE->load->dbforge(); 

        $data = array( 'module_name' => 'Crosslinking' , 'module_version' => $this->version, 'has_cp_backend' => 'y' ); 
        $this->EE->db->insert('modules', $data); 
        
        if ($this->EE->db->field_exists('crosslinking_base', 'channels') == FALSE)
		{
			$this->EE->dbforge->add_column('channels', array('crosslinking_base' => array('type' => 'VARCHAR', 'constraint' => '100', 'default'=>'') ) );
		}
        
        return TRUE; 
        
    } 
    
    function uninstall() { 
        
        $this->EE->load->dbforge(); 
        
        $this->EE->db->select('module_id'); 
        $query = $this->EE->db->get_where('modules', array('module_name' => 'Crosslinking')); 
        
        $this->EE->db->where('module_id', $query->row('module_id')); 
        $this->EE->db->delete('module_member_groups'); 
        
        $this->EE->db->where('module_name', 'Crosslinking'); 
        $this->EE->db->delete('modules'); 
        
        $this->EE->db->where('class', 'Crosslinking'); 
        $this->EE->db->delete('actions'); 
        
        return TRUE; 
    } 
    
    function update($current='') { 
        
        if ($current < 1.2) 
        { 
            $this->EE->load->dbforge(); 
    		if ($this->EE->db->field_exists('crosslinking_base', 'channels') == FALSE)
    		{
    			$this->EE->dbforge->add_column('channels', array('crosslinking_base' => array('type' => 'VARCHAR', 'constraint' => '100', 'default'=>'') ) );
    		}
        }
        
        return TRUE; 
    } 
	

}
/* END */
?>