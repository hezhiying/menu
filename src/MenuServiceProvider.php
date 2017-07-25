<?php

namespace Zine\Menu;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Illuminate\Support\Facades\Event;

class MenuServiceProvider extends LaravelServiceProvider {
	protected $defer = true;

	public function provides() {
		return ['menu'];
	}

	public function boot() {
		Event::listen('defaultMenuCreate',function(Menu $menu){
			$menu->create('menu1/menu1_1','#')->divide();
			$menu->create('menu1/menu1_2',['route'=>'index','class'=>'classname','id'=>'id']);
			 return $menu;
		});
		Event::listen('adminMenuCreate',function(Menu $menu){
			return $menu->create('adminMenu',['route'=>'index','name'=>'adminMenu'])->create('menu1_1')->root;
		});
	}

	public function register() {
		$this->app->singleton('menu',function(){
			return new Menu();
		});
	}

}