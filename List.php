<?php

class NavMenu_List extends NavMenu_Abstract_Nav {

  /**
   * _navOptions
   * 
   * @var mixed
   * @access private
   */
  private $_navOptions = NULL;
  private $_nav_resourse = NULL;
  private $_customCreateNavItemsCallback = false;

  public function __construct($request, $response, $params = NULL) {
    parent::__construct($request, $response, $params);

    /** 初始化回调函数 */
    if (function_exists('createNavItemsCallback')) {
      $this->$_customCreateNavItemsCallback = true;
    }

    $this->_nav_resourse = $this->db->fetchRow($this->select()->where('name = ?', 'navMenuOrder')->limit(1));
    if (!$this->_nav_resourse) {
      $this->_nav_resourse['name'] = 'navMenuOrder';
      $this->_nav_resourse['value'] = '[]';
      $this->insert($this->_nav_resourse);
    }
    $this->_nav_resourse = json_decode($this->_nav_resourse['value']);
  }

  public function navMenu($navOptions = NULL) {

    //初始化一些变量
    $this->_navOptions = Typecho_Config::factory($navOptions);
    $this->_navOptions->setDefault(array(
        'wrapTag' => 'ul',
        'wrapClass' => '',
        'wrapId' => '',
        'itemTag' => 'li',
        'itemClass' => '',
    ));

    $this->stack = $this->_nav_resourse;

    if ($this->have()) {
      if ($this->_navOptions->wrapTag) {
        echo '<' . $this->_navOptions->wrapTag . (empty($this->_navOptions->wrapClass) ? '' : ' class="' . $this->_navOptions->wrapClass . '"') . (empty($this->_navOptions->wrapId) ? '' : ' id="' . $this->_navOptions->wrapId . '"') . '>';
        self::generateNavItems($this->_nav_resourse);
        echo '</' . $this->_navOptions->wrapTag . '>';
      } else {
        self::generateNavItems($this->_nav_resourse);
      }

      $this->stack = $this->_map;
    }
  }

  private function generateNavItems($items) {

    $widget_cat = Typecho_Widget::widget('Widget_Metas_Category_List');
    $widget_archive = Typecho_Widget::widget('Widget_Archive');
    $navOptions = $this->_navOptions;
    if (is_array($items)) {
      foreach ($items as $item) {
        $classes = array();
        if ($navOptions->itemClass) {
          $classes[] = $navOptions->itemClass;
        }
        $classes[] = 'menu-item';
        $item_class = isset($item->class) ? ' class="' . $item->class . '"' : "";
        $item_target = isset($item->target) && "_blank" == $item->target ? ' target="_blank"' : "";

        if ('cat' === $item->type) {
          $current_cat = $widget_cat->getCategory($item->id);
          $classes[] = 'menu-category-item';
          if ($widget_archive->is('archive', $current_cat['slug'])) {
            $classes[] = 'current';
          }
          if ($navOptions->itemTag)
            echo '<', $navOptions->itemTag, ' class="', implode(' ', $classes), '">';
          echo '<a href="', $current_cat['permalink'], '" ', $item_class, $item_target, '>', $item->name, '</a>';
        }else if ('page' === $item->type) {
          $current_page = $this->getPage($item->id);

          $classes[] = 'menu-page-item';
          if ($widget_archive->is('page', $current_page['slug'])) {
            $classes[] = 'current';
          }
          if ($navOptions->itemTag)
            echo '<', $navOptions->itemTag, ' class="', implode(' ', $classes), '">';
          echo '<a href="', $current_page['permalink'], '" ', $item_class, '>', $item->name, '</a>';
        }else if ('custom' === $item->type) {
          $classes[] = 'menu-custom-item';
          $current_url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
          if ($current_url == $item->id) {
            $classes[] = 'current';
          }
          if ($navOptions->itemTag)
            echo '<', $navOptions->itemTag, ' class="', implode(' ', $classes), '">';
          echo '<a href="', $item->id, '" ', $item_class, '>', $item->name, '</a>';
        }

        if (isset($item->children) && count($item->children) > 0) {
          echo '<ul class="sub_menu">';
          self::generateNavItems($item->children);
          echo '</ul>';
        }
        if ($navOptions->itemTag)
        echo '</' . $navOptions->itemTag . '>';
      }
    }
  }

  private function getPage($id) {
    $this->widget('Widget_Contents_Page_List')->to($pages);
    if (count($pages->stack) > 0) {
      foreach ($pages->stack as $page) {
        if ($id == $page['cid'])
          return $page;
      }
    }
  }

}
