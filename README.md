# Typecho 自定义菜单插件

## 安装说明
 将插件克隆到本地, 放到 `usr/plugins` 目录下即可。

## 插件说明
 此插件早在2015年本人在大三时就已完成。当时只支持单个菜单，最近抽点时间改成多菜单支持。

## 插件特点：
1. 多菜单支持
2. 支持分类、独立页面和自定义页面链接
3. 支持编辑菜单项（自定义菜单名称，自定义class, 新窗口等）
4. 可拖动设置顺序和层级
5. 菜单结构可自定义，方便个性化布局

## 使用方法

1. 后台启用插件
2. 进入主菜单 `控制台->菜单管理` 即可看到菜单设置页面。
3. 默认有名为`default` 的菜单，在左侧新增菜单可以新增。
3. 左下方的菜单项里可添加分类，独立页面和自定义链接到右侧菜单结构中。
4. 拉动菜单结构可以调整菜单顺序和层次。
5. 调整完了之后 *务必点击 `保存设置` 按钮进行保存，否则菜单不会生效*
6. 在主题中使用一下方法调用菜单:
```php
<?php $this->widget('NavMenu_List')->navMenu('header_menu'); ?>
```
7. 当前菜单有相应的 `.current` class 名，可进行菜单高亮等布置
## 调用函数说明：

```php
/**
 * @param string $menu 菜单名称
 * @param null $navOptions 菜单配置
 */
public function navMenu($menu = 'default', $navOptions = NULL)

```
## 菜单配置项
```php
[
    'wrapTag' => 'ul', // 菜单项的容器
    'wrapClass' => '', // 容器自定义class
    'wrapId' => '', // 容器自定义ID (二级菜单展开等功能可使用ID 去解决)
    'itemTag' => 'li', // a 链接的容器
    'itemClass' => '', // 容器自定义class
];
```

## 插件截图
![image](https://raw.githubusercontent.com/doghap/NavMenu/master/screen.png)


## 其他

1. 插件没有提供前端菜单样式，可根据主题进行自定义布置
2. 后台菜单结构拖动有点问题， 但可以正常使用
