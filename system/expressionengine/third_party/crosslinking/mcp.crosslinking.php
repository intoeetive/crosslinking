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
 File: mcp.crosslinking.php
-----------------------------------------------------
 Purpose: Automatic keyword crosslinking (for SEO purposes)
=====================================================
*/

if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}

require_once PATH_THIRD.'crosslinking/config.php';

class Crosslinking_mcp {

    var $version = CROSSLINKING_ADDON_VERSION;
    
    var $perpage = 50;
    
    function __construct() { 
        // Make a local reference to the ExpressionEngine super object 
        $this->EE =& get_instance(); 
        
        if (version_compare(APP_VER, '2.6.0', '>='))
        {
        	$this->EE->view->cp_page_title = lang('crosslinking_module_name');
        }
        else
        {
        	$this->EE->cp->set_variable('cp_page_title', lang('crosslinking_module_name'));
        }
    } 
    
    function index()
    {
        return $this->keywords();
    }
    
    function keywords()
    {
        $this->EE->load->helper('form');
    	$this->EE->load->library('table');   
        $this->EE->load->library('javascript');

    	$vars = array();
        
        $vars['selected']['rownum']=($this->EE->input->get_post('rownum')!='')?$this->EE->input->get_post('rownum'):0;
        
        $this->EE->db->select('*');
        $this->EE->db->from('crosslinking');
        $this->EE->db->where('site_id', $this->EE->config->item('site_id'));
        $this->EE->db->order_by('keyword', 'asc');
        $this->EE->db->limit($this->perpage, $vars['selected']['rownum']);
        $query = $this->EE->db->get();
        $vars['total_count'] = $query->num_rows();
        
        $i = $vars['selected']['rownum']+1;
        $vars['table_headings'] = array(
                        '#',
                        $this->EE->lang->line('keyphrase'),
                        $this->EE->lang->line('url'),
                        $this->EE->lang->line('title'),
                        $this->EE->lang->line('rel'),
                        $this->EE->lang->line('target'),
                        '',
                        ''
                    );
                    
              
        foreach ($query->result() as $obj)
        {
           $vars['keywords'][$i]['id'] = $i;
           $vars['keywords'][$i]['keyphrase'] = $obj->keyword;
           if (strpos($obj->url, 'path=') !== FALSE)
            {
            	$link = preg_replace_callback("/".LD."\s*path=(.*?)".RD."/", array(&$this->EE->functions, 'create_url'), $obj->url);
            }
            elseif (strpos($obj->url, 'http')===0)
            {
                $link = $obj->url;
            }
            else
            {
                $link = $this->EE->functions->create_url($obj->url);
            }
           $vars['keywords'][$i]['url'] = $obj->url . " (<a href=\"".$link."\">".$this->EE->lang->line('visit')."</a>)";
           $vars['keywords'][$i]['title'] = $obj->title;
           $vars['keywords'][$i]['rel'] = $obj->rel;
           $vars['keywords'][$i]['target'] = $obj->target;

           $vars['keywords'][$i]['edit_link'] = "<a href=\"".BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=crosslinking'.AMP.'method=edit'.AMP.'id='.$obj->id."\">".$this->EE->lang->line('edit')."</a>";
           $vars['keywords'][$i]['delete_link'] = "<a class=\"keyword_delete_warning\" href=\"".BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=crosslinking'.AMP.'method=delete'.AMP.'id='.$obj->id."\">".$this->EE->lang->line('delete')."</a>";
           
           $i++;
        }
        
        $outputjs = '
				var draft_target = "";

			$("<div id=\"keyword_delete_warning\">'.$this->EE->lang->line('keyword_delete_warning').'</div>").dialog({
				autoOpen: false,
				resizable: false,
				title: "'.$this->EE->lang->line('confirm_deleting').'",
				modal: true,
				position: "center",
				minHeight: "0px", 
				buttons: {
					Cancel: function() {
					$(this).dialog("close");
					},
				"'.$this->EE->lang->line('delete_keyword').'": function() {
					location=draft_target;
				}
				}});

			$(".keyword_delete_warning").click( function (){
				$("#keyword_delete_warning").dialog("open");
				draft_target = $(this).attr("href");
				$(".ui-dialog-buttonpane button:eq(2)").focus();	
				return false;
		});';

		$this->EE->javascript->output(str_replace(array("\n", "\t"), '', $outputjs));
        
        $this->EE->load->library('pagination');

        $p_config = array();
        $p_config['base_url'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=crosslinking'.AMP.'method=keywords';
        $p_config['total_rows'] = $this->EE->db->count_all('crosslinking');
		$p_config['per_page'] = $this->perpage;
		$p_config['page_query_string'] = TRUE;
		$p_config['query_string_segment'] = 'rownum';
		$p_config['full_tag_open'] = '<p id="paginationLinks">';
		$p_config['full_tag_close'] = '</p>';
		$p_config['prev_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_prev_button.gif" width="13" height="13" alt="&lt;" />';
		$p_config['next_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_next_button.gif" width="13" height="13" alt="&gt;" />';
		$p_config['first_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_first_button.gif" width="13" height="13" alt="&lt; &lt;" />';
		$p_config['last_link'] = '<img src="'.$this->EE->cp->cp_theme_url.'images/pagination_last_button.gif" width="13" height="13" alt="&gt; &gt;" />';

		$this->EE->pagination->initialize($p_config);
        
		$vars['pagination'] = $this->EE->pagination->create_links();
        
        $this->EE->cp->set_right_nav(
            array( 'add_keyword' => BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=crosslinking'.AMP.'method=edit') 
        );
        
    	return $this->EE->load->view('keywords', $vars, TRUE);
	
    }    
    
    
    function delete()
    {

        if (!empty($_GET['id']))
        {
            $this->EE->db->where('id', $this->EE->input->get_post('id'));
            $this->EE->db->delete('crosslinking');
            if ($this->EE->db->affected_rows()>0)
            {
                $this->EE->session->set_flashdata('message_success', $this->EE->lang->line('keyword_deleted')); 
            }
            else
            {
                $this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('no_keyword_to_delete'));  
            }
            
        }
        else 
        {
            $this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('no_keyword_to_delete'));  
        }

        $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=crosslinking'.AMP.'method=keywords');
 
    }    
    
    
    
    function edit()
    {
    	$this->EE->load->helper('form');
    	$this->EE->load->library('table');  
        $this->EE->load->library('javascript');
        
    	$vars = array();

        $target = array(""=>"", "_self"=>"_self", "_top"=>"_top", "_blank"=>"_blank", "_parent"=>"_parent");
        $rel = array(""=>"", "external"=>"external", "nofollow"=>"nofollow");
 
        $id = intval($this->EE->input->get('id'));
        if ($id!=0)
        {
            $this->EE->db->select('*');
            $this->EE->db->from('crosslinking');
            $this->EE->db->where('id', $id);
            $query = $this->EE->db->get();

            $vars['data'] = array(	
                ''	=> form_hidden('id', $query->row('id')),
                'keyphrase'	=> form_input('keyword', $query->row('keyword'), 'style="width: 80%"'),
                'url'	=> form_input('url', $query->row('url'), 'style="width: 80%"'),
                'title'	=> form_input('title', $query->row('title'), 'style="width: 80%"'),
                'target'	=> form_dropdown('target', $target, $query->row('target')),
                'rel'	=> form_dropdown('rel', $rel, $query->row('rel'))
        		);
        }
        else
        {
            $vars['data'] = array(	
                ''	=> form_hidden('id', ''),
                'keyphrase'	=> form_input('keyword', '', 'style="width: 80%"'),
                'url'	=> form_input('url', '', 'style="width: 80%"'),
                'title'	=> form_input('title', '', 'style="width: 80%"'),
                'target'	=> form_dropdown('target', $target),
                'rel'	=> form_dropdown('rel', $rel)
        		);
        }
        
    	return $this->EE->load->view('edit', $vars, TRUE);
	
    }    
    
    
    function save()
    {

        if (empty($_POST['keyword']) || empty($_POST['url']))
        {
            $this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('missing_keyword_or_url'));
            $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=crosslinking'.AMP.'method=keywords');
            return false;
        }
        
        $this->EE->db->select('*');
        $this->EE->db->from('crosslinking');
        $this->EE->db->where('site_id', $this->EE->config->item('site_id'));
        if (!empty($_POST['id']))
        {
            $this->EE->db->where('id != ', $this->EE->input->post('id'));
        }
        $this->EE->db->where('LOWER(keyword)', strtolower($this->EE->input->get_post('keyword')));
        $query = $this->EE->db->get();
        if ($query->num_rows() > 0)
        {
            $this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('keyword_exist'));
            $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=crosslinking'.AMP.'method=keywords');
            return false;
        }
        
        $data['site_id'] = $this->EE->config->item('site_id');
        $data['url'] = $this->EE->input->get_post('url');
        $data['keyword'] = $this->EE->input->get_post('keyword');
        
        if (!empty($_POST['title']))
        {
            $data['title'] = $this->EE->input->get_post('title');
        }
        
        if (!empty($_POST['rel']))
        {
            $data['rel'] = $this->EE->input->get_post('rel');
        }
        
        if (!empty($_POST['target']))
        {
            $data['target'] = $this->EE->input->get_post('target');
        }
       
        
        if (!empty($_POST['id']))
        {
            $this->EE->db->where('id', $this->EE->input->post('id'));
            $this->EE->db->update('crosslinking', $data);
        }
        else
        {
            $this->EE->db->insert('crosslinking', $data);
        }
        
        $this->EE->session->set_flashdata('message_success', $this->EE->lang->line('updated'));        
        $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=crosslinking'.AMP.'method=keywords');
        
        
    }    
    
    
    function channel_linking()
    {
        $this->EE->load->helper('form');
    	$this->EE->load->library('table');  
        
        $url_options = array(
            ''=>lang('do_not_crosslink'), 
            'url_title'=>lang('url_title'), 
            'entry_id'=>lang('entry_id')
        );
        
        $this->EE->db->select('channel_id, channel_title, crosslinking_base');
        $this->EE->db->from('channels');
        $this->EE->db->where('site_id', $this->EE->config->item('site_id'));
        $q = $this->EE->db->get();
        
        foreach ($q->result_array() as $row)
        {
            $vars['data'][] = array(
                'channel'   => '<a href="'.BASE.AMP.'C=admin_content'.AMP.'M=channel_edit'.AMP.'channel_id='.$row['channel_id'].'">'.$row['channel_title'].'</a>',
                'setting'   => form_dropdown('crosslinking_base['.$row['channel_id'].']', $url_options, $row['crosslinking_base'])
            );
        }
        
        $vars['table_headings'] = array(
                        $this->EE->lang->line('channel'),
                        $this->EE->lang->line('url_base')
                    );
 
        
    	return $this->EE->load->view('channel_linking', $vars, TRUE);
    }
    
    
    
    function save_channel_linking()
    {

        if (empty($_POST['crosslinking_base']))
        {
            $this->EE->session->set_flashdata('message_failure', $this->EE->lang->line('unauthorized_access'));
            $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=crosslinking'.AMP.'method=channel_linking');
            return false;
        }
        
        foreach ($_POST['crosslinking_base'] as $channel=>$setting)
        {
            $data = array('crosslinking_base'=>$setting);
            $this->EE->db->where('channel_id', $channel);
            $this->EE->db->update('channels', $data);
        }
        
        $this->EE->session->set_flashdata('message_success', $this->EE->lang->line('updated'));        
        $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=crosslinking'.AMP.'method=channel_linking');
        
        
    }  
    

}
/* END */
?>