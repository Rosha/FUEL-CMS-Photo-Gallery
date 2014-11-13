<div class="buttonbar" id="action_btns">
	<ul>
        <li<?php echo isset($current_page) && $current_page == '' ? ' class="active"' : ''; ?>><a href="<?=fuel_url($nav_selected);?>" id="toggle_tree" class="ico ico_tree" title="">Gallery Manager</a></li>
		<li<?php echo isset($current_page) && $current_page == 'add_gallery_group' ? ' class="active"' : ''; ?>><a href="<?=fuel_url($nav_selected);?>/add_group" id="btn_Add_GalleryGroup" class="ico ico_select_all" title="">Create Gallery Group</a></li>
		<li class="end<?php echo isset($current_page) && $current_page == 'help' ? ' active' : ''; ?>"><a href="<?=fuel_url($nav_selected.'/help');?>" id="toggle_tree" class="ico ico_tree" title="">Help</a></li>
	</ul>	
</div>