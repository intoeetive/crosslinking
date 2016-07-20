<ul class="tab_menu" id="tab_menu_tabs">
<li class="content_tab current"> <a href="<?=BASE.AMP.'C=addons_extensions'.AMP.'M=extension_settings'.AMP.'file=crosslinking'?>"><?=lang('settings')?></a>  </li> 
<li class="content_tab"> <a href="<?=BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=crosslinking'.AMP.'method=channel_linking'?>"><?=lang('channel_linking')?></a>  </li> 
<li class="content_tab"> <a href="<?=BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=crosslinking'.AMP.'method=keywords'?>"><?=lang('keywords')?></a>  </li> 


</ul> 
<div class="clear_left shun"></div> 


<?=form_open('C=addons_extensions'.AMP.'M=save_extension_settings'.AMP.'file=crosslinking');?>

<?php 
$this->table->set_template($cp_pad_table_template);
$this->table->set_heading(
    array('data' => lang('preference'), 'style' => 'width:50%;'),
    lang('setting')
);

foreach ($settings as $key => $val)
{
	$this->table->add_row(lang($key, $key), $val);
}

echo $this->table->generate();

?>

<p><?=form_submit('submit', lang('submit'), 'class="submit"')?></p>
<?php $this->table->clear()?>
<?=form_close()?>
<?php
/* End of file index.php */
/* Location: ./system/expressionengine/third_party/link_truncator/views/index.php */