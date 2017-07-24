<?php

namespace Zine\Menu;

use Illuminate\Support\Collection;

class Menu extends Collection {
	use GenerateUrlTrait, RenderTrait;

	protected $items             = [
		'id'        => '',
		'title'     => '',
		'icon'      => '',
		'link'      => '',
		'level'     => 0,
		'divider'   => '',
		'class'     => '',
		'pos'       => 9999,
		'attribute' => [],
		'child'     => [],
	];
	protected $reserved = [
		'title',
		'link',
		'attribute',
		'level',
		'icon',
		'route',
		'action',
		'url',
		'prefix',
		'root',
		'parent',
		'secure',
		'raw',
		'child',
		'divider',
		'pos'
	];
	public    $root;
	public    $parent;

	public function __construct($title = '/', $options = '', $root = null, $parent = null) {
		Parent::__construct($this->items);
		$this->root   = $root ? $root : $this;
		$this->parent = $parent ? $parent : $this;
		$this->put('title', $title);
		$this->put('child', new Collection());
		$this->put('level', $this->parent->get('level') + 1);
		$this->put('id', $this->id());
		$this->update($options);
	}

	public function update($options) {
		if (!$options) {
			return $this;
		}
		if (is_array($options) && $options) {
			foreach ($options as $attName => $attValue) {
				$this->items[ $attName ] = $attValue;
			}
		}
		if (is_array($options) && array_has($options,'divider')) {
			$this->divide($options['divider']);
		}

		$this->items['link'] = $this->dispatch($options) ?: $this->items['link'];
//		if (is_array($options) && $options) {
//			$this->saveAttribute(array_except($options, $this->reserved));
//		}

		return $this;
	}

	/**
	 * 获取单个子菜单，不存在则先创建
	 *
	 * @param              $title
	 * @param string|array $options
	 *
	 * @return self
	 */
	protected function getSingleMenu($title, $options = '') {
		foreach ($this->items['child'] as $item) {
			if ($item->items['title'] == $title) {
				return $item;
			}
		}

		$menu = new self($title, $options, $this->root, $this);

		return $this->items['child']->push($menu)->last();

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
			if ($title) {
				$menu = $menu->getSingleMenu($title);
			}
		}

		if ($options instanceof \Closure) {
			call_user_func($options, $menu);
		} elseif (!is_null($options)) {
			$menu->update($options);
		}

		return $menu;
	}

	/**
	 * 保存菜单属性
	 *
	 * @param string|array $name
	 * @param null         $val
	 */
	public function saveAttribute($name = null, $val = null) {

		if (is_array($name)) {
			foreach ($name as $attName => $attValue) {
				$this->saveAttribute($attName, $attValue);
			}
		} elseif ($name) {
			$this->items['attribute'][ $name ] = $val;
		}
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
		if ($this->items['child'] && $this->items['child'] instanceof BaseCollection) {
			$this->items['child'] = $this->items['child']->sortBy($field, $options, $descending)->values();
		}
		foreach ($this->items['child'] as $menu) {
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
	public function fireMenuEvent($type = '') {
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
		return $this->get('child')->count() > 0;
	}

	/**
	 * 插入分隔符
	 *
	 * @param  string $attributes
	 *
	 * @return void
	 */
	public function divide( $attributes = '') {
		$classes             = $attributes . ' divider';
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