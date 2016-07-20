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
 File: ext.crosslinking.php
-----------------------------------------------------
 Purpose: Automatic keyword crosslinking (for SEO purposes)
=====================================================
*/

if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}

require_once PATH_THIRD.'crosslinking/config.php';

class Crosslinking_ext {

	var $name	     	= CROSSLINKING_ADDON_NAME;
	var $version 		= CROSSLINKING_ADDON_VERSION;
	var $description	= 'Automatic keyword crosslinking (for SEO purposes)';
	var $settings_exist	= 'y';
	var $docs_url		= 'http://www.intoeetive.com/docs/crosslinking.html';
    
    var $settings 		= array();
    
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	function __construct($settings = '')
	{
		$this->EE =& get_instance();
        $this->settings = $settings;
	}
    
    /**
     * Activate Extension
     */
    function activate_extension()
    {
        
        $hooks = array(
    		array(
    			'hook'		=> 'channel_entries_tagdata_end',
    			'method'	=> 'replace_entry',
    			'priority'	=> 10
    		),
            array(
    			'hook'		=> 'forum_thread_rows_absolute_end',
    			'method'	=> 'replace_forum',
    			'priority'	=> 10
    		)
            
            
    	);
    	
        foreach ($hooks AS $hook)
    	{
    		$data = array(
        		'class'		=> __CLASS__,
        		'method'	=> $hook['method'],
        		'hook'		=> $hook['hook'],
        		'settings'	=> '',
        		'priority'	=> $hook['priority'],
        		'version'	=> $this->version,
        		'enabled'	=> 'y'
        	);
            $this->EE->db->insert('extensions', $data);
    	}	
                
        $sql[] = "CREATE TABLE `".$this->EE->db->dbprefix."crosslinking` (
    		`id` mediumint( 9 ) NOT NULL AUTO_INCREMENT ,
            `site_id` tinyint(4) NOT NULL default '1',
    		`keyword` tinytext NOT NULL ,
    		`url` tinytext NOT NULL ,
    		`title` tinytext NOT NULL ,
    		`rel` tinytext NOT NULL ,
    		`target` tinytext NOT NULL ,
    		PRIMARY KEY ( `id` ),
            KEY `site_id` (`site_id`)	
    	);";                
                    
        foreach ($sql as $qstr)
        {
            $this->EE->db->query($qstr);
        }
        

    }
    
    /**
     * Update Extension
     */
    function update_extension($current = '')
    {
    	if ($current == '' OR $current == $this->version)
    	{
    		return FALSE;
    	}
    	
    	if ($current < '1.1.0')
    	{
    		// Update to version 1.1.0
            $this->EE->db->query("ALTER TABLE `".$this->EE->db->dbprefix."crosslinking` ADD `site_id` TINYINT NOT NULL DEFAULT '1' AFTER `id` ,
ADD INDEX ( `site_id` ) ");
    	}
    	
    	$this->EE->db->where('class', __CLASS__);
    	$this->EE->db->update(
    				'extensions', 
    				array('version' => $this->version)
    	);
    }
    
    
    /**
     * Disable Extension
     */
    function disable_extension()
    {
    	$this->EE->db->where('class', __CLASS__);
    	$this->EE->db->delete('extensions');
        
        $sql[] = "DROP TABLE `".$this->EE->db->dbprefix."crosslinking`";                
                    
        foreach ($sql as $qstr)
        {
            $this->EE->db->query($qstr);
        }
    }
        
    
    function settings_form($current)
    {
    	$this->EE->load->helper('form');
    	$this->EE->load->library('table');
        
        $this->EE->db->select('hook, method, enabled');
        $this->EE->db->from('extensions');
        $this->EE->db->where('class', __CLASS__);
        $ext_q = $this->EE->db->get();
        foreach ($ext_q->result() as $ext)
        {
            $varname = $ext->hook;
            $$varname = $ext->enabled;
        }

        $vars = array();

    	$yes_no_options = array(
    		'y' 	=> lang('yes'), 
    		'n'	=> lang('no')
    	);
    	
    	if (!isset($current['replace_n_times'])) $current['replace_n_times']=1;
    	
    	$vars['settings']['replace_n_times'] = form_input(
    					'replace_n_times',
    					$current['replace_n_times']);
            
        $vars['settings']['enable_entries'] = form_dropdown(
    					'enable_entries',
    					$yes_no_options, 
    					$channel_entries_tagdata_end);
         
        /*$vars['settings']['enable_comments'] = form_dropdown(
    					'enable_comments',
    					$yes_no_options, 
    					$comment_entries_tagdata);*/

    	if ($this->EE->config->item('forum_is_installed') == 'y')
    	{
    		$vars['settings']['enable_forum'] = form_dropdown(
    					'enable_forum',
    					$yes_no_options, 
    					$forum_thread_rows_absolute_end);
    	}

    	return $this->EE->load->view('settings', $vars, TRUE);			
    }
    
    
    
    
    function save_settings()
    {
    	if (empty($_POST))
    	{
    		show_error($this->EE->lang->line('unauthorized_access'));
    	}
        
        if (isset($_POST['enable_entries']) && $_POST['enable_entries']!='')
        {
            $data = array('enabled' => $this->EE->input->post('enable_entries'));
            $this->EE->db->where('class', __CLASS__);
        	$this->EE->db->where('hook', 'channel_entries_tagdata_end');
            $this->EE->db->update('extensions', $data);
        }
        /*
        if ($_POST['enable_comments']!='')
        {
            $data = array('enabled', $this->EE->input->post('enable_comments'));
            $this->EE->db->where('class', __CLASS__);
        	$this->EE->db->where('method', 'comment_entries_tagdata');
            $this->EE->db->update('extensions', $data);
        }*/
        
        if (isset($_POST['enable_forum']) && $_POST['enable_forum']!='')
        {
            $data = array('enabled' => $this->EE->input->post('enable_forum'));
            $this->EE->db->where('class', __CLASS__);
        	$this->EE->db->where('hook', 'forum_thread_rows_absolute_end');
            $this->EE->db->update('extensions', $data);
        }
        
        $data = array('replace_n_times' => $this->EE->input->post('replace_n_times'));
        
        $this->EE->db->where('class', __CLASS__);
    	$this->EE->db->update('extensions', array('settings' => serialize($data)));
    	
    	$this->EE->session->set_flashdata(
    		'message_success',
    	 	$this->EE->lang->line('preferences_updated')
    	);
    }
    
    
    function replace_entry($tagdata, $row, $obj) 
    {
        
		if ($this->EE->extensions->last_call!==FALSE)
        {
            $tagdata = $this->EE->extensions->last_call;
        }
        $tagdata = $this->_replace_keywords($tagdata);
        $tagdata = $this->_replace_channels($tagdata);
        $tagdata = str_replace(LD.'no_crosslink'.RD, '', $tagdata);
        $tagdata = str_replace(LD.'/no_crosslink'.RD, '', $tagdata);
        return $tagdata;
    }
    
    function replace_comment($tagdata, $row) 
    {
        if ($this->EE->extensions->last_call!==FALSE)
        {
            $tagdata = $this->EE->extensions->last_call;
        }
        $tagdata = $this->_replace_keywords($tagdata);
        $tagdata = $this->_replace_channels($tagdata);
        $tagdata = str_replace(LD.'no_crosslinking'.RD, '', $tagdata);
        $tagdata = str_replace(LD.'/no_crosslinking'.RD, '', $tagdata);        
        $tagdata = str_replace(LD.'no_crosslink'.RD, '', $tagdata);
        $tagdata = str_replace(LD.'/no_crosslink'.RD, '', $tagdata);
        return $tagdata;
    }
    
    function replace_forum($obj, $data, $tagdata)
    {
        if ($this->EE->extensions->last_call!==FALSE)
        {
            $tagdata = $this->EE->extensions->last_call;
        }
        $tagdata = $this->_replace_keywords($tagdata);
        $tagdata = $this->_replace_channels($tagdata);
        $tagdata = str_replace(LD.'no_crosslinking'.RD, '', $tagdata);
        $tagdata = str_replace(LD.'/no_crosslinking'.RD, '', $tagdata);
        $tagdata = str_replace(LD.'no_crosslink'.RD, '', $tagdata);
        $tagdata = str_replace(LD.'/no_crosslink'.RD, '', $tagdata);
        return $tagdata;
    }

    
    function _replace_keywords($tagdata) 
    {
        
        
        if ($this->EE->session->userdata['group_id']==1)
        {
            //echo $tagdata;
        }  
        
        $this->EE->db->select('*');
        $this->EE->db->from('crosslinking');
        $this->EE->db->where('site_id', $this->EE->config->item('site_id'));
        $this->EE->db->order_by('LENGTH(keyword)', 'desc');
        $q = $this->EE->db->get();

		foreach($q->result() as $link)
        {							
			$link->keyword = str_replace('/', '\/', preg_quote($link->keyword));
            $find = '/'.$link->keyword.'/i';
			$replacement_a = array();

			$matches = array();
			preg_match_all($find, $tagdata, $matches, PREG_OFFSET_CAPTURE);
			$all_matches = $matches[0];
			
			$nc_a = array(
				'/<h[1-6][^>]*>(.*?)'.$link->keyword.'(.*?)<\/h[1-6]>/si',
				'/<title>[^<]*'.$link->keyword.'[^<]*<\/title>/i',
				'/<a[^>]+>[^<]*'.$link->keyword.'[^<]*<\/a>/i',
				'/href=("|\')[^"\']+'.$link->keyword.'(.*)[^"\']+("|\')/i',
				'/src=("|\')[^"\']*'.$link->keyword.'[^"\']*("|\')/i',
				'/alt=("|\')[^"\']*'.$link->keyword.'[^"\']*("|\')/i',
				'/title=("|\')[^"\']*'.$link->keyword.'[^"\']*("|\')/i',
                '/value=("|\')[^"\']*'.$link->keyword.'[^"\']*("|\')/i',
                '/rel=("|\')[^"\']*'.$link->keyword.'[^"\']*("|\')/i',
                '/wmode=("|\')[^"\']*'.$link->keyword.'[^"\']*("|\')/i',
				'/content=("|\')[^"\']*'.$link->keyword.'[^"\']*("|\')/i',
				'/<script[^>]*>[^<]*'.$link->keyword.'[^<]*<\/script>/i',
                '/<style[^>]*>[^<]*'.$link->keyword.'[^<]*<\/style>/i',
                '/<object[^>]+>[^<]*'.$link->keyword.'[^<]*<\/object>/i',
				'/<embed[^>]+>[^<]*'.$link->keyword.'[^<]*<\/embed>/i',
				'/{[^}]*'.$link->keyword.'[^{]*}/i',
				'/[a-zA-Z]*'.$link->keyword.'[a-zA-Z]+/i',
				'/[a-zA-Z]+'.$link->keyword.'[a-zA-Z]*/i',
				'/'.LD.'[^}]*'.$link->keyword.'[^{]*'.RD.'/i',
				'/'.LD.'no_crosslinking'.RD.'(.*?)'.$link->keyword.'(.*?)'.LD.'\/no_crosslinking'.RD.'/si',
                '/'.LD.'no_crosslink'.RD.'(.*?)'.$link->keyword.'(.*?)'.LD.'\/no_crosslink'.RD.'/si',
                
			);
            
            $clean = true;

			foreach($nc_a as $nc)
            {
				$results = array();
				preg_match_all($nc, $tagdata, $results, PREG_OFFSET_CAPTURE);
				$nc_matches = $results[0];

				if(!empty($nc_matches)) 
                {
					$clean = false;
                    foreach($nc_matches as $nc_match)
                    {
						$start = $nc_match[1];
						$end = $nc_match[1] + strlen($nc_match[0]);
						foreach($all_matches as $i => $data)
                        {
							if($data[1] >= $start && $data[1] <= $end)
                            {
								$all_matches[$i][2] = true;
							}
						}
					}
				}		
			}
            
            $replacement_a = array();
            

			foreach($all_matches as $i => $match){
			     if (!isset($match[2]))
                 {
                    $match[2] = false;
                                     
                 }                 

                if(($clean == true) || ($match[2] != true) && !empty($match)) 
                {
                    $replacement_a[] = $match;
					//break;
				}
			}
            
            
            $replace_n_times = (isset($this->settings['replace_n_times']))?$this->settings['replace_n_times']:1;

			if(!empty($replacement_a))
            {
				$i = 0;
				$shift = 0;
				//var_dump($replacement_a);
				foreach ($replacement_a as $repl)
				{
					if ($replace_n_times==0 || $i < $replace_n_times)
					{	
						$url = '<a href="'.$link->url.'"';
						if ($link->target!='') $url .= ' target="'.$link->target.'"';
						if ($link->rel!='') $url .= ' rel="'.$link->rel.'"';
		                if ($link->title!='') $url .= ' title="'.$link->title.'"';
		                $url .= ' class="crosslinking"';
						$url .= '>'.$repl[0].'</a>';

						$tagdata = substr($tagdata, 0, $repl[1] + $shift) . $url . substr($tagdata, $repl[1] + $shift + strlen($repl[0]));
						
						$shift += strlen($url) - strlen($repl[0]);
						
						$i++;
						//echo $i;
					}
				}
             
			}

		}
        
        

        return $tagdata;	
    }
    
    
    
    
    function _replace_channels($tagdata) 
    {
        $channels = array();
        $urls = array();
        $this->EE->db->select('channel_id, channel_url, comment_url, crosslinking_base')
                            ->from('channels')
                            ->where('site_id', $this->EE->config->item('site_id'));
        $q = $this->EE->db->get();
        
        
        foreach ($q->result_array() as $row)
        {
            if ($row['crosslinking_base']!='')
            {
                $basepath = ($row['comment_url']!='') ? $row['comment_url'] : $row['channel_url'];
                if ($row['crosslinking_base']=='entry_id')
                {
                     $urls[$row['channel_id']] = $basepath.'/%ENTRY_ID%';
                }
                else if ($row['crosslinking_base']=='url_title')
                {
                     $urls[$row['channel_id']] = $basepath.'/%URL_TITLE%';
                }
                $channels[$row['channel_id']] =  $row['channel_id'];
                if (version_compare(APP_VER, '2.6.0', '<'))
    			{
    				$urls[$row['channel_id']] = $this->EE->functions->remove_double_slashes($urls[$row['channel_id']]);
    			}
    			else
    			{
    				$urls[$row['channel_id']] = reduce_double_slashes($urls[$row['channel_id']]);
    			}
            }
        }
        
        if (empty($channels))
        {
            return $tagdata;
        }
        
        $this->EE->db->select('entry_id, channel_id, url_title, title AS keyword')
                        ->from('channel_titles')
                        ->where_in('status', array('open'))
                        ->where_in('channel_id', $channels)
                        ->order_by('LENGTH(keyword)', 'desc');
        $q = $this->EE->db->get();

		foreach($q->result() as $link)
        {							
			$link->keyword = str_replace('/', '\/', preg_quote($link->keyword));
            $find = '/'.$link->keyword.'/i';
			$replacement_a = array();

			$matches = array();
			preg_match_all($find, $tagdata, $matches, PREG_OFFSET_CAPTURE);
			$all_matches = $matches[0];
			
			$nc_a = array(
				'/<h[1-6][^>]*>(.*?)'.$link->keyword.'(.*?)<\/h[1-6]>/si',
				'/<title>[^<]*'.$link->keyword.'[^<]*<\/title>/i',
				'/<a[^>]+>[^<]*'.$link->keyword.'[^<]*<\/a>/i',
				'/href=("|\')[^"\']+'.$link->keyword.'(.*)[^"\']+("|\')/i',
				'/src=("|\')[^"\']*'.$link->keyword.'[^"\']*("|\')/i',
				'/alt=("|\')[^"\']*'.$link->keyword.'[^"\']*("|\')/i',
				'/title=("|\')[^"\']*'.$link->keyword.'[^"\']*("|\')/i',
                '/value=("|\')[^"\']*'.$link->keyword.'[^"\']*("|\')/i',
                '/rel=("|\')[^"\']*'.$link->keyword.'[^"\']*("|\')/i',
				'/content=("|\')[^"\']*'.$link->keyword.'[^"\']*("|\')/i',
				'/<script[^>]*>[^<]*'.$link->keyword.'[^<]*<\/script>/i',
                '/<style[^>]*>[^<]*'.$link->keyword.'[^<]*<\/style>/i',
                '/<object[^>]+>[^<]*'.$link->keyword.'[^<]*<\/object>/i',
				'/<embed[^>]+>[^<]*'.$link->keyword.'[^<]*<\/embed>/i',
				'/{[^}]*'.$link->keyword.'[^{]*}/i',
				'/'.LD.'[^}]*'.$link->keyword.'[^{]*'.RD.'/i',
				'/wmode=("|\')[^"\']*'.$link->keyword.'[^"\']*("|\')/i',
				'/'.LD.'no_crosslinking'.RD.'(.*?)'.$link->keyword.'(.*?)'.LD.'\/no_crosslinking'.RD.'/si',
                '/'.LD.'no_crosslink'.RD.'(.*?)'.$link->keyword.'(.*?)'.LD.'\/no_crosslink'.RD.'/si',
                
			);
            
            $clean = true;

			foreach($nc_a as $nc)
            {
				$results = array();
				preg_match_all($nc, $tagdata, $results, PREG_OFFSET_CAPTURE);
				$nc_matches = $results[0];

				if(!empty($nc_matches)) 
                {
					$clean = false;
                    foreach($nc_matches as $nc_match)
                    {
						$start = $nc_match[1];
						$end = $nc_match[1] + strlen($nc_match[0]);
						foreach($all_matches as $i => $data)
                        {
							if($data[1] >= $start && $data[1] <= $end)
                            {
								$all_matches[$i][2] = true;
							}
						}
					}
				}		
			}
            
            

			foreach($all_matches as $i => $match){
			     if (!isset($match[2]))
                 {
                    $match[2] = false;
                                     
                 }                 
				
                if(($clean == true) || ($match[2] != true) && !empty($match)) 
                {
                    $replacement_a = $match;
					break;
				}
			}
            
            


			if(!empty($replacement_a))
            {
                $uristring = str_replace('%ENTRY_ID%', $link->entry_id, $urls[$link->channel_id]);
                $uristring = str_replace('%URL_TITLE%', $link->url_title, $uristring);
                
                $url = '<a href="'.$uristring.'"';
				//$url .= ' target="'.$link->target.'"';
				//$url .= ' rel="'.$link->rel.'"';
                //$url .= ' title="'.$link->title.'"';
                $url .= ' class="crosslinking"';
				$url .= '>'.$replacement_a[0].'</a>';
 
				$tagdata = substr($tagdata, 0, $replacement_a[1]) . $url . substr($tagdata, $replacement_a[1] + strlen($replacement_a[0]));
             
			}

		}
        
        

        return $tagdata;	
    } 
    
    
    

}
// END CLASS
