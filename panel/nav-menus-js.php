<script src="<?php $options->PluginUrl($pluginName);echo '/panel/js/jquery-ui-1.10.0.custom.min.js'; ?>"></script>
<script src="<?php $options->PluginUrl($pluginName);echo '/panel/js/jquery.mjs.nestedSortable.js'; ?>"></script>

<script type="text/javascript">
	$(document).ready(function(){

		var $addBtn = $("#add_to_menu");
		var $selectedItems = $("#selectedItems");
		var $orderlist = $("#orderlist");
		<?php $current_level = Typecho_Widget::widget('NavMenu_Edit')->getCurrentLevel(); ?>
		var current_level = <?php echo  isset($current_level) ? $current_level : 0 ;?>;

    //更新菜单排序
    function updateMenuOrder(){
    	setTimeout(function(){
    		$orderlist.val(JSON.stringify(makeOrderlist()));
    	}, 1000);
    }
    
    //创建菜单项
    function createMenuItems(items){
    	if(items.length > 0){
    		for(var i = 0; i < items.length; i++){
    			var $item = menu_order[i],$item_type;
    			switch ($item.type){
    				case 'cat' : $item_type = '<?php _e('分类') ?>'; break;
    				case 'page' : $item_type = '<?php _e('独立页面') ?>'; break;
    				default : $item_type = '<?php _e('自定义链接') ?>';
    			}	
    			var menu_html = '<li id="menu_item" data-id="'+$item.id+'" data-type="'+$item.type+'" data-name="'+$item.name+'" class="menu_item mjs-nestedSortable-leaf"><dl class="menu-item-bar"><dt class="menu-item-handle"><span class="item-title"><span class="menu-item-title">'+$item.name+'</span></span><span class="item-controls"><span class="item-type">'+$item_type+'</span><a class="item-edit" href="#"></a></span></dt></dl>';

    			if($item.children){
    				menu_html += '<ul>' + createMenuItems($item.children) + '</ul>';
    			}
    			menu_html += '</li>';
    		}
    	}
	//console.log(menu_html);
	return menu_html;
}

    // 控制选项和附件的切换
    $('#edit-secondary .typecho-option-tabs li').click(function() {
    	$('#edit-secondary .typecho-option-tabs li').removeClass('active');
    	$(this).addClass('active');
    	$('.tab-content').addClass('hidden');

    	var selected_tab = $(this).find('a').attr('href');
    	$(selected_tab).removeClass('hidden');
    	$addBtn.attr('data-type', selected_tab);
    	return false;
    });
    
    //排序插件
    $selectedItems.nestedSortable({
    	listType: 'ul',
    	handle:'dl',
    	items: 'li',
    	rtl:true,
    	maxLevels:3,
    	tolerenceElement: '>.menu-item-bar',
    	opacity : 0.8,
    	cursor : 'move',
    	isTree: true,
    	change : function(){
    		updateMenuOrder();
    	}
    });

    $addBtn.click(function(){
    	var d = $(this),selected_el = d.attr('data-type'), f, html, item_type_title = selected_el === '#cat' ? '<?php _e('分类') ?>' : selected_el === '#page' ? '<?php _e('独立页面') ?>' : '<?php _e('自定义链接') ?>';
    	if(selected_el === '#custom'){
    		var link_name = $('#link_name').val(), 
    		link_url = $('#link_url').val();
    		if('' === link_url || "http://" === link_url || '' === link_name) return false;
    		current_level++;
    		html = '<li id="menu_item_'+current_level+'" data-id="'+link_url+'" data-type="custom" data-name="'+link_name+'" data-class="" data-target="" class="menu_item"><dl class="menu-item-bar"><dt class="menu-item-handle"><span class="item-title"><span class="menu-item-title">'+ link_name +'</span></span><span class="item-controls"><span class="item-type">'+item_type_title+'</span><a class="item-edit" href="#" data-item="menu_item_'+current_level+'"></a></span></dt></dl><div class="menu-item-settings" id="menu_item_settings_'+current_level+'" data-item="menu_item_'+current_level+'"><section class="typecho-post-option"><label for="order" class="typecho-label"><?php _e("链接名称") ?></label><p><input type="text" name="link_name" value="'+link_name+'" class="w-100"></p></section><section class="typecho-post-option"><label for="order" class="typecho-label"><?php _e("链接名称") ?></label><p><input type="text" name="link_url" value="'+link_url+'" placeholder="http://" class="w-100"></p></section><section class="typecho-post-option"><label for="link_class" class="typecho-label"><?php _e('自定义class值');?></label><p><input type="text" id="link_class" name="link_class" class="w-100"></p></section><section class="typecho-post-option"><p><input type="checkbox" id="link_target_'+current_level+'" name="link_target" value="_blank"><label for="link_target_'+current_level+'"><?php _e("新标签中打开") ?></label></p></section><button class="btn primary save_menu_item" data-item-settings="menu_item_settings_'+current_level+'" data-item="menu_item_'+current_level+'" data-type="custom"><?php _e("保存菜单项") ?></button><a href="#" class="delete_menu_item" data-id="menu_item_'+current_level+'"><?php _e("删除") ?></a></div></li>';
    		$selectedItems.append(html);
    		$('#link_name, #link_url').val('');
    	}else{
    		f = $(selected_el).find("input:checked"),
    		data_type = $(this).attr('data-type') === "#cat" ? "cat" : "page";
    		if(f.length > 0){
    			f.each(function(){
    				current_level++;
    				html = '<li id="menu_item_'+current_level+'" data-id="'+$(this).val()+'" data-type="'+ data_type +'"  data-name="'+$(this).data('name')+'" data-class="" data-target="" class="menu_item"><dl class="menu-item-bar"><dt class="menu-item-handle"><span class="item-title"><span class="menu-item-title">'+ $(this).data('name') +'</span></span><span class="item-controls"><span class="item-type">'+item_type_title+'</span><a class="item-edit" href="#" data-item="menu_item_'+current_level+'"></a></span></dt></dl><div class="menu-item-settings" id="menu_item_settings_'+current_level+'"><section class="typecho-post-option"><label for="order" class="typecho-label"><?php _e("链接名称") ?></label><p><input type="text" id="link_name" name="link_name" value="'+$(this).data('name')+'" class="w-100"></p></section><section class="typecho-post-option"><label for="link_class" class="typecho-label"><?php _e('自定义class值');?></label><p><input type="text" id="link_class" name="link_class" class="w-100"></p></section><section class="typecho-post-option"><p><input type="checkbox" id="link_target_'+current_level+'" name="link_target" value="_blank"><label for="link_target_'+current_level+'"><?php _e("新标签中打开") ?></label></p></section><button class="btn primary save_menu_item" data-item-settings="menu_item_settings_'+current_level+'" data-item="menu_item_'+current_level+'" data-type="'+data_type+'"><?php _e("保存菜单项") ?></button><a href="#" class="delete_menu_item" data-id="menu_item_'+current_level+'"><?php _e("删除") ?></a></div></li>';
    				$selectedItems.append(html);
    				$(this).attr("checked", false);
    			});
    		}
    	}
    	updateMenuOrder();
    });
    
    function makeOrderlist(parent_item){
    	var sorted_data = [];
    	if(typeof(parent_item) === "undefined"){
    		parent_item = $('#selectedItems');
    	}
    	parent_item.children('.menu_item').each(function(){
    		var d = $(this),
    		curr = {};
    		curr.type = d.attr('data-type');
    		curr.name = d.attr('data-name');
    		curr.id = d.attr('data-id');
    		curr.class = d.attr('data-class');
    		curr.target = d.attr('data-target');

    		if(d.children('ul').children('.menu_item').length > 0){
    			curr.children = makeOrderlist(d.children('ul'));
    		}else{
    			curr.children = [];
    		}
    		sorted_data.push(curr);
    	});
    	return sorted_data;
    }
    
    $(document).on('click', '.item-edit', function(){
    	var d = $(this),
    	item_li_id = "#"+d.data('item');
    	$(item_li_id).toggleClass('active');
    	return false;
    });
    
    $(document).on('click', '.save_menu_item', function(){

    	var d = $(this),
    	item_settings_id = "#"+d.attr('data-item-settings'),
    	item_id = "#"+d.attr('data-item'),
    	$item = $(item_id),
    	$item_settings = $(item_settings_id),
    	$link_name = $item_settings.find("input[name='link_name']"),
    	$link_url = $item_settings.find("input[name='link_url']");
    	$link_class = $item_settings.find("input[name='link_class']");
    	$link_target = $item_settings.find("input[name='link_target']");

    	if(($link_name.val() !== undefined && $link_name.val() !== "") || ($link_url.val() !== undefined && $link_url.val() !== "")){

    		if($item.attr('data-name') !== $link_name.val()){
    			$item.attr('data-name', $link_name.val());
    			$item.find('.menu-item-title').text($link_name.val());
    		}

    		if($item.attr('data-id') !== $link_url.val()){
    			$item.attr('data-id', $link_url.val());
    		}

    		if($item.attr('data-class') !== $link_class.val()){
    			$item.attr('data-class', $link_class.val());
    		}

    		if($link_target.is(":checked")){
    			$item.attr('data-target', "_blank");
    		}else{
    			$item.attr('data-target', "");
    		}


    		$item.removeClass('active');
    		$item.find('.menu-item-handle').addClass('item_saved');
    		setTimeout(function(){$item.find('.menu-item-handle').removeClass('item_saved');}, 1000);
    		updateMenuOrder();

    	}
    	return false;
    });
    
    $(document).on('click', '.delete_menu_item', function(){

    	var d = $(this), item = $("#"+d.data("id"));
    	item.removeClass('active');
    	item.find('.menu-item-handle').addClass('item_deleted');
    	item.find('.menu-item-bar').addClass('item_deleted');
    	item.fadeOut(1000);
    	setTimeout(function(){item.remove();}, 1000);
    	updateMenuOrder();
    	return false;
    });
    
  });
</script>