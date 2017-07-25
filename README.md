# menu
menu library for laravel-php, simple yet powerful !

##创建菜单
```php
//添加监听
protected $listen = [
    'defaultMenuCreate' => [
        'App\Containers\Home\Listeners\CreateMenu',
     ]
];
	

//处理
public function handle( Menu $menu) {
    //任何情况下这里不能返回false，否则其它监听菜单的不会被执行到
    $menu->create('用户管理/添加用户','#');
    $menu->create('用户管理/用户充值',['route'=>'user_pay']);
    $menu->create('用户管理/用户小号',function($menu){
        	        $menu->url = '#';
        	        $menu->create('子菜单/子菜单');
        	    });
    return $menu;
}
```
```php
use Zine\Menu\Menu;
 
Event::listen('menuCreate',function(Menu $menu){
			 $menu->create('menu1/menu1_1','#');
			 $menu->create('menu1/menu1_2',['route'=>'routename','class'=>'classname','id'=>'id']);
			 return $menu;
});
```
##快速开始

```php
//返回的是菜单集合 Collection类型
$menu = app('menu')->fireMenuEvent($type)
$menu->find('menu1/menu2'); 
$menu->find('menu1/menu2')->getChild(); 
$menu->create('menu1/menu2','#');
$menu->sortBy('pos'); 
$menu->sortByDesc('pos'); 
$menu->find('menu1/menu2')->asUl(); 
$menu->find('menu1/menu2')->dropdown(); 

```