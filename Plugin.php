<?php

if (!defined('__TYPECHO_ROOT_DIR__'))
    exit;

/**
 * NavMenu for typecho
 * 
 * @package NavMenu
 * @author merdan
 * @version 1.0.0
 * @link http://merdan.cc
 */
class NavMenu_Plugin implements Typecho_Plugin_Interface {
    
    /**
     * 启用插件方法,如果启用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate() {
        Helper::addPanel(1, 'NavMenu/panel/nav-menus.php', _t('管理菜单'), _t('管理菜单'), 'administrator');
        Helper::addAction('nav-edit', 'NavMenu_Edit');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate() {
        Helper::removeAction('nav-edit');
        Helper::removePanel(1, 'NavMenu/panel/nav-menus.php');
    }

    public static function config(\Typecho_Widget_Helper_Form $form) {
        
    }

    public static function personalConfig(\Typecho_Widget_Helper_Form $form) {
        
    }
    
    public static function header_scripts($header){
        $options = Helper::options();
        $panelUrl = $options->pluginUrl.'/NavMenu/panel';
        if($options->lang == "ug_CN"){
            echo $header,'<link rel="stylesheet" href="'.$panelUrl.'/css/nav-menu-rtl.css"/>';
        }else{
            echo $header,'<link rel="stylesheet" href="'.$panelUrl.'/css/nav-menu.css"/>';
        }
    }
    
    public static function navMenu($navOptions = NULL) {
        Typecho_Widget::widget('NavMenu_List')->navMenu($navOptions);
    }

}
