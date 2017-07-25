<?php

namespace Zine\Menu;

use Illuminate\Support\Collection;

class Menu extends Collection {
	use GenerateUrlTrait, RenderTrait;

	protected $items = [
		'id'        => '',
		'title'     => '',
		'icon'      => '',
		'url'       => '',
		'level'     => 0,
		'divider'   => '',
		'class'     => '',
		'pos'       => 9999,
		'child'     => [],
	];
	/**
	 * @var array $except 排除的字段
	 */
	protected $except     = ['route', 'action', 'url', 'child', 'divider'];
	protected $attrExcept = ['icon', 'url', 'level', 'divider', 'pos', 'attribute', 'child'];
	public    $root;
	public    $parent;

	function __get($name) {
		if (array_key_exists($name, $this->items)) {
			return $this->items[ $name ];
		}

		return Parent::__get($name);
	}

	/**
	 * Menu constructor.
	 *
	 * @param string $title
	 * @param null   $root
	 * @param null   $parent
	 */
	public function __construct($title = '/', $root = null, $parent = null) {
		Parent::__construct($this->items);
		$this->root   = $root ? $root : $this;
		$this->parent = $parent ? $parent : $this;
		$this->put('title', $title);
		$this->put('child', new Collection());
		$this->put('level', $this->parent->items['level'] + 1);
		$this->put('id', $this->id());
	}

	/**
	 * 创建或返回菜单
	 *
	 * @param string $paths
	 * @param null   $options
	 *
	 * @return null|\Zine\Menu\Menu
	 */
	public function create($paths, $options = null) {
		if ($paths == '/') {
			return $this->root;
		}
		$titles = explode("/", $paths);
		$menu   = $this;
		foreach ($titles as $title) {
			$menu = $menu->findOrCreateMenu($title);
		}

		if ($options instanceof \Closure) {
			call_user_func($options, $menu);
		} elseif (is_string($options)) {
			$this->items['url'] = $options;
		} elseif (is_array($options) && $options) {
			foreach (array_except($options, $this->except) as $attName => $attValue) {
				$this->items[ $attName ] = $attValue;
			}
			//分隔线
			if (array_key_exists('divider', $options)) {
				$this->divide($options['divider']);
			}
			//链接
			foreach (['route', 'action', 'url'] as $key) {
				if (array_key_exists($key, $options)) {
					$this->items['url'] = $this->dispatch($options);
				}
			}
		}

		return $menu;
	}

	/**
	 * 查找菜单
	 *
	 * @param string $menuPath 菜单路径
	 *
	 * @return $this|\Zine\Menu\Menu
	 * @throws \Zine\Menu\MenuNotFoundException
	 */
	public function find($menuPath) {
		if (!$menuPath) {
			return $this;
		}

		$titles = explode("/", $menuPath);
		$title  = array_shift($titles);
		foreach ($this->items['child'] as $menu) {
			/** @var self $menu */
			if ($menu->items['title'] == $title) {
				return $menu->find(implode($titles, '/'));
			}
		}
		throw new MenuNotFoundException($this->getMenuPath($title));
	}

	/**
	 * 返回子菜单集合
	 * @return mixed
	 */
	public function getChild(){
		return $this->items['child'];
	}

	/**
	 * 从当前向上返回菜单路径
	 *
	 * @param $path
	 *
	 * @return mixed
	 */
	public function getMenuPath($path = '') {
		if ($this->items['title'] != '/') {
			return $this->parent->getMenuPath($this->items['title'] . '/' . $path);
		}

		return rtrim($path, '/');
	}

	/**
	 * 查找或创建菜单
	 *
	 * @param              $title
	 *
	 * @return self
	 */
	protected function findOrCreateMenu($title) {
		foreach ($this->items['child'] as $item) {
			if ($item->items['title'] == $title) {
				return $item;
			}
		}

		$menu = new self($title, $this->root, $this);

		return $this->items['child']->push($menu)->last();

	}

	/**
	 * 设置菜单属性
	 *
	 * @param string $name
	 * @param string $val
	 *
	 * @return $this
	 */
	public function data($name, $val) {
		$this->items[ $name ] = $val;

		return $this;
	}

	/**
	 * Sort the collection using the given callback.
	 * 使用指定方法排序
	 *
	 * @param  string $field 排序字段
	 * @param  int    $options
	 * @param  bool   $descending
	 *
	 * @return static
	 */
	public function sortBy($field, $options = SORT_REGULAR, $descending = false) {
		if ($this->items['child'] ) {
			$this->items['child'] = $this->items['child']->sortBy($field, $options, $descending)->values();
		}
		foreach ($this->items['child'] as $menu) {
			/** @var self $menu */
			$menu->sortBy($field, $options, $descending);
		}

		return $this;
	}

	public function sortByDesc($field, $options = SORT_REGULAR) {
		return $this->sortBy($field, $options, true);
	}

	/**
	 * 触发菜单事件，获取相应类型的菜单
	 *
	 * @param string $type
	 *
	 * @return self
	 */
	public function fireMenuEvent($type = 'default') {
		$eventName = camel_case($type . 'MenuCreate');
		$menus     = event($eventName, [new self()]);
		foreach ($menus as $menu) {
			if ($menu instanceof Menu) {
				return $menu;
			}
		}

		return $this->root;
	}

	/**
	 * 是否有子元素
	 * @return bool
	 */
	public function hasChildren() {
		return $this->items['child']->count() > 0;
	}

	/**
	 * 插入分隔符
	 *
	 * @param  string $attributes
	 *
	 * @return void
	 */
	public function divide($attributes = '') {
		$classes                = $attributes . ' divider';
		$this->items['divider'] = implode(' ', array_unique(explode(' ', $classes)));
	}

	/**
	 * Generate an integer identifier for each new item
	 *
	 * @return int
	 */
	protected function id() {
		if ($this->items['title'] == '/') {
			return 'root';
		}

		return uniqid(rand());
	}
}