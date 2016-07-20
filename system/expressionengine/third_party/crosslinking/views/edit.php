<ul class="tab_menu" id="tab_menu_tabs">
<li class="content_tab"> <a href="<?=BASE.AMP.'C=addons_extensions'.AMP.'M=extension_settings'.AMP.'file=crosslinking'?>"><?=lang('settings')?></a>  </li> 
<li class="content_tab"> <a href="<?=BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=crosslinking'.AMP.'method=channel_linking'?>"><?=lang('channel_linking')?></a>  </li> 
<li class="content_tab current"> <a href="<?=BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=crosslinking'.AMP.'method=keywords'?>"><?=lang('keywords')?></a>  </li> 

</ul> 
<div class="clear_left shun"></div> 

<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=crosslinking'.AMP.'method=save', array('id'=>'crosslinking_edit_form'));?>

<?php 

$this->table->set_template($cp_pad_table_template);

foreach ($data as $key => $val)
{
	if ($val!='') $this->table->add_row(lang($key, $key), $val);
}

echo $this->table->generate();

?>
<?php $this->table->clear()?>

<p><?=form_submit('submit', lang('save'), 'class="submit"')?></p>

<?php
form_close();

