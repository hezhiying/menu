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
		Event::listen('menuCreate',function(Menu $menu){
			return $menu->create('menu1',['route'=>'index','name'=>'oneMenu'])->create('menu1_1')->root;
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