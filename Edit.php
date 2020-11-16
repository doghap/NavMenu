<?php

class NavMenu_Edit extends NavMenu_Abstract_Nav implements Widget_Interface_Do {

    private $_nav_resourse;
    private $_current_level;

    private $_nav_menus = [];
    private $_current_nav = '';

    public function __construct($request, $response, $params = NULL) {
        parent::__construct($request, $response, $params);

        $this->_nav_menus = $this->db->fetchRow($this->select()
            ->where('name = ?', 'navMenus')->limit(1));

        if (!$this->_nav_menus) {
            $this->_nav_menus['name'] = 'navMenus';
            $this->_nav_menus['value'] = json_encode(['default']);
            $this->insert($this->_nav_menus);
        }
        $this->_nav_menus = json_decode($this->_nav_menus['value'], true);

        $this->_current_nav = $request->get('current', $this->_nav_menus[0]);

        if(!in_array($this->_current_nav, $this->_nav_menus)){
            throw new Typecho_Plugin_Exception('你请求的菜单不存在！');
        }

        $this->_nav_resourse = $this->db->fetchRow($this->select()
                        ->where('name = ?', 'navMenuOrder')->limit(1));
        if (!$this->_nav_resourse) {
            $this->_nav_resourse['name'] = 'navMenuOrder';
            $this->_nav_resourse['value'] = [];
            foreach ($this->_nav_menus as $nav_menu) {
                $this->_nav_resourse['value'][$nav_menu] = [];
            }

            $this->_nav_resourse['value'] = json_encode($this->_nav_resourse['value']);
            $this->insert($this->_nav_resourse);
        }
        $this->_nav_resourse = json_decode($this->_nav_resourse['value']);
        $this->_current_level = 0;
    }

    /**
     * 入口函数
     *
     * @access public
     * @return void
     */
    public function execute() {
        /** 编辑以上权限 */
        $this->user->pass('editor');
    }

    public function get_nav_resourse() {
        return isset($this->_nav_resourse) ? $this->_nav_resourse : NULL;
    }

    public function menuForm()
    {
        $form = new Typecho_Widget_Helper_Form($this->security->getIndex('/action/nav-edit'), Typecho_Widget_Helper_Form::POST_METHOD);

        /** 菜单数据 */
        $nav_menu = new Typecho_Widget_Helper_Form_Element_Text('nav_menu', NULL, NULL, _t('菜单名称'));
        $nav_menu->input->setAttribute('id', 'menuForm');
        $form->addInput($nav_menu);

        $do = new Typecho_Widget_Helper_Form_Element_Hidden('do');
        $do->value('add-menu');
        $form->addInput($do);

        /** 提交按钮 */
        $submit = new Typecho_Widget_Helper_Form_Element_Submit('submit', NULL, _t('添加'));
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);

        return $form;
    }

    public function menuList()
    {
        foreach ($this->_nav_menus as $nav_menu) {
            $url = Helper::url(urlencode('NavMenu/panel/nav-menus.php') . '&current=' . $nav_menu);
            $class = $this->_current_nav === $nav_menu ? 'active' : '';
            echo <<<HTML
<li class="w-30 $class"><a href="$url">$nav_menu</a></li>
HTML;
        }
    }

    public function form() {
        $form = new Typecho_Widget_Helper_Form($this->security->getIndex('/action/nav-edit'), Typecho_Widget_Helper_Form::POST_METHOD);

        /** 菜单数据 */
        $nav_menu_order = new Typecho_Widget_Helper_Form_Element_Hidden('nav_menu_order');
        $nav_menu_order->input->setAttribute('id', 'orderlist');
        $form->addInput($nav_menu_order);

        /** 分类动作 */
        $do = new Typecho_Widget_Helper_Form_Element_Hidden('do');
        $do->value('update');
        $form->addInput($do);


        $current = new Typecho_Widget_Helper_Form_Element_Hidden('current');
        $current->value($this->_current_nav);
        $form->addInput($current);

        /** 提交按钮 */
        $submit = new Typecho_Widget_Helper_Form_Element_Submit('submit', NULL, _t('保存设置'));
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);
        $nav_menu_order->value(json_encode($this->_nav_resourse->{$this->_current_nav}));
        return $form;
    }

    public function action() {
        $this->security->protect();
        $this->on($this->request->is('do=update'))->updateNav();
        $this->on($this->request->is('do=add-menu'))->addMenu();
    }

    public function addMenu()
    {
        $from = $this->request->from('nav_menu');
        if(in_array($from['nav_menu'], $this->_nav_menus)){
            /** 提示信息 */
            $this->widget('Widget_Notice')->set(_t('菜单已存在'), 'error');

            /** 转向原页 */
            $this->response->goBack();
        }
        $this->_nav_menus[] = $from['nav_menu'];
        $this->update(array('value' => json_encode($this->_nav_menus)), $this->db->sql()->where('name = ?', 'navMenus'));
        $this->_nav_resourse->{$from['nav_menu']} = [];
        $this->update(array('value' => json_encode($this->_nav_resourse)), $this->db->sql()->where('name = ?', 'navMenuOrder'));

        /** 提示信息 */
        $this->widget('Widget_Notice')->set(_t('菜单新增成功'), 'success');

        /** 转向原页 */
        $this->response->goBack();
    }

    public function updateNav() {

        $from = $this->request->from('nav_menu_order');
        $this->_nav_resourse->{$this->_current_nav} = json_decode($from['nav_menu_order'], true);
        $this->update(array('value' => json_encode($this->_nav_resourse)), $this->db->sql()->where('name = ?', 'navMenuOrder'));

        /** 设置高亮 */
//        $this->widget('Widget_Notice')->highlight();

        /** 提示信息 */
        $this->widget('Widget_Notice')->set(_t('菜单更新成功'), 'success');

        /** 转向原页 */
        $this->response->goBack();
    }

    public function generateMenuList() {

        if ($this->_nav_resourse) {
            if (count($this->_nav_resourse->{$this->_current_nav}) > 0) {
                self::generateMenuItems($this->_nav_resourse->{$this->_current_nav});
            }
        }
    }

    private function generateMenuItems($items) {
        if (is_array($items)) {
            foreach ($items as $item) {
                switch ($item->type) {
                    case 'cat' : $item_type = _t('分类');
                        break;
                    case 'page' : $item_type = _t('独立页面');
                        break;
                    default : $item_type = _t('自定义链接');
                }
                $this->_current_level++;
                $item_class = isset($item->class) ? $item->class : "";
                $item_target = isset($item->target) ? $item->target : "";
                $checked = isset($item->target) && "_blank" == $item->target ? "checked" : "";

                echo '<li id="menu_item_' . $this->_current_level . '" data-id="' . $item->id . '" data-type="' . $item->type . '" data-name="' . $item->name . '" data-class="' . $item_class . '" data-target="' . $item_target . '" class="menu_item"><dl class="menu-item-bar"><dt class="menu-item-handle"><span class="item-title"><span class="menu-item-title">' . $item->name . '</span></span><span class="item-controls"><span class="item-type">' . $item_type . '</span><a class="item-edit" href="#" data-item = "menu_item_' . $this->_current_level . '"></a></span></dt></dl>';
                switch ($item->type) {
                    case 'custom': echo '<div class="menu-item-settings" id="menu_item_settings_' . $this->_current_level . '"><section class="typecho-post-option"><label for="link_name" class="typecho-label">' . _t('链接名称') . '</label><p><input type="text" id="link_name" name="link_name" value="' . $item->name . '" class="w-100"></p></section><section class="typecho-post-option"><label for="link_url" class="typecho-label">' . _t('链接地址') . '</label><p><input type="text" id="link_url" name="link_url" value="' . $item->id . '" placeholder="http://" class="w-100"></p></section><section class="typecho-post-option"><label for="link_class" class="typecho-label">' . _t('自定义class值') . '</label><p><input type="text" id="link_class" name="link_class" value="' . $item_class . '" class="w-100"></p></section><section class="typecho-post-option"><p><input type="checkbox" id="link_target_'.$this->_current_level.'" name="link_target" value="_blank" '.$checked.'><label for="link_target_'.$this->_current_level.'">' . _t('新标签中打开') . '</label></p></section><button class="btn save_menu_item" data-item-settings="menu_item_settings_' . $this->_current_level . '" data-item="menu_item_' . $this->_current_level . '" data-type="' . $item->type . '">' . _t('保存菜单项') . '</button><a href="#" class="delete_menu_item" data-id="menu_item_' . $this->_current_level . '">' . _t('删除') . '</a></div>';
                        break;
                    case 'cat' :
                        $widget_cat = Typecho_Widget::widget('Widget_Metas_Category_List');
                        $current_cat = $widget_cat->getCategory($item->id);
                        echo '<div class="menu-item-settings" id="menu_item_settings_' . $this->_current_level . '"><section class="typecho-post-option"><label for="order" class="typecho-label">' . _t('链接名称') . '</label><p><input type="text" id="link_name" name="link_name" value="' . $item->name . '" class="w-100"></p></section><section class="typecho-post-option"><label for="order" class="typecho-label">' . _t('菜单项属性') . '</label><p>' . $item_type . ' : <a href="' . $current_cat["permalink"] . '">' . $current_cat["name"] . '</a></p></section><section class="typecho-post-option"><label for="link_class" class="typecho-label">' . _t('自定义class值') . '</label><p><input type="text" id="link_class" name="link_class" value="' . $item_class . '" class="w-100"></p></section><section class="typecho-post-option"><p><input type="checkbox" id="link_target_'.$this->_current_level.'" name="link_target" value="_blank" '.$checked.'><label for="link_target_'.$this->_current_level.'">' . _t('新标签中打开') . '</label></p></section><button class="btn save_menu_item" data-item-settings="menu_item_settings_' . $this->_current_level . '" data-item="menu_item_' . $this->_current_level . '" data-type="' . $item->type . '">' . _t('保存菜单项') . '</button><a href="#" class="delete_menu_item" data-id="menu_item_' . $this->_current_level . '">' . _t('删除') . '</a></div>';
                        break;
                    case 'page' :
                        Typecho_Widget::widget('Widget_Contents_Page_List')->to($widget_page);
                        $current_page = $widget_page->getPage($item->id);
                        echo '<div class="menu-item-settings" id="menu_item_settings_' . $this->_current_level . '"><section class="typecho-post-option"><label for="order" class="typecho-label">' . _t('链接名称') . '</label><p><input type="text" id="link_name" name="link_name" value="' . $item->name . '" class="w-100"></p></section><section class="typecho-post-option"><label for="order" class="typecho-label">' . _t('菜单项属性') . '</label><p>' . $item_type . ' : <a href="' . $current_page["permalink"] . '">' . $current_page["title"] . '</a></p></section><section class="typecho-post-option"><label for="link_class" class="typecho-label">' . _t('自定义class值') . '</label><p><input type="text" id="link_class" name="link_class" value="' . $item_class . '" class="w-100"></p></section><section class="typecho-post-option"><p><input type="checkbox" id="link_target_'.$this->_current_level.'" name="link_target" value="_blank" '.$checked.'><label for="link_target_'.$this->_current_level.'">' . _t('新标签中打开') . '</label></p></section><button class="btn save_menu_item" data-item-settings="menu_item_settings_' . $this->_current_level . '" data-item="menu_item_' . $this->_current_level . '" data-type="' . $item->type . '">' . _t('保存菜单项') . '</button><a href="#" class="delete_menu_item" data-id="menu_item_' . $this->_current_level . '">' . _t('删除') . '</a></div>';
                }

                if (isset($item->children) && count($item->children) > 0) {
                    echo "<ul>";
                    self::generateMenuItems($item->children);
                    echo "</ul>";
                }
                echo '</li>';
            }
        }
    }

    public function getCurrentLevel() {
        return $this->_current_level ? $this->_current_level : NULL;
    }

}
