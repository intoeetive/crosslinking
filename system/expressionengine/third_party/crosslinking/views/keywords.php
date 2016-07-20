<ul class="tab_menu" id="tab_menu_tabs">
<li class="content_tab"> <a href="<?=BASE.AMP.'C=addons_extensions'.AMP.'M=extension_settings'.AMP.'file=crosslinking'?>"><?=lang('settings')?></a>  </li> 
<li class="content_tab"> <a href="<?=BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=crosslinking'.AMP.'method=channel_linking'?>"><?=lang('channel_linking')?></a>  </li> 
<li class="content_tab current"> <a href="<?=BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=crosslinking'.AMP.'method=keywords'?>"><?=lang('keywords')?></a>  </li> 

</ul> 
<div class="clear_left shun"></div> 



<?php if ($total_count == 0):?>
	<div class="tableFooter">
		<p class="notice"><?=lang('no_keywords')?></p>
	</div>
<?php else:?>

	<?php
		$this->table->set_template($cp_table_template);
		$this->table->set_heading($table_headings);

		echo $this->table->generate($keywords);
	?>



<span class="pagination"><?=$pagination?></span>


<?php endif; /* if $total_count > 0*/?>


<?php
