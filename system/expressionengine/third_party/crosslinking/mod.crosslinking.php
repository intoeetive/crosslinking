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
 File: mod.crosslinking.php
-----------------------------------------------------
 Purpose: Automatic keyword crosslinking (for SEO purposes)
=====================================================
*/

if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}

class Crosslinking {

    var $return_data	= ''; 						// Bah!

    /** ----------------------------------------
    /**  Constructor
    /** ----------------------------------------*/

    function __construct()
    {        
    	$this->EE =& get_instance(); 
    }
    /* END */


}
/* END */
?>