<?php

namespace Zine\Menu;

trait RenderTrait {

	/**
	 * Generate the menu items as list items using a recursive function
	 *
	 * @param string $type
	 * @param array  $childrenAttributes
	 *
	 * @return string
	 */
	public function render($type = 'ul',  $childrenAttributes = array()) {
		$items = '';

		$item_tag = in_array($type, array('ul', 'ol')) ? 'li' : $type;

		foreach ($this->items['child'] as $item) {
			/** @var \Zine\Menu\Menu $item */
			
			$items .= '<' . $item_tag . $this->menuClass() . '>';
			if ($item->items['url']) {
				$items .= '<a' . $item->menuClass() . ' href="' . $item->items['url'] . '">' . $item->items['title'] . '</a>';
			} else {
				$items .= $item->items['title'];
			}

			if ($item->hasChildren()) {
				$items .= '<' . $type . self::attributes($childrenAttributes) . '>';
				$items .= $item->render($type);
				$items .= "</{$type}>";
			}

			$items .= "</{$item_tag}>";
			if($item->items['divider']) {
				$items .= '<' . $item_tag . $item->dividerClass() . '></' . $item_tag . '>';
			}

		}

		return $items;
	}

	/**
	 * Returns the menu as an unordered list.
	 *
	 * @param array $attributes
	 * @param array $childrenAttributes
	 *
	 * @return string
	 */
	public function asUl($attributes = array(), $childrenAttributes = array()) {
		return '<ul' . self::attributes($attributes) . '>' . $this->render('ul',  $childrenAttributes) . '</ul>';
	}

	public function dropdown($attributes = []) {
		$button = sprintf('<button class="btn btn-default dropdown-toggle" type="button" id="%s" data-toggle="dropdown">%s<span class="caret"></span></button>',$this->id,$this->title);
		$ul = sprintf('<ul class="dropdown-menu" role="menu" aria-labelledby="%s">',$this->id);
		foreach ($this->items['child'] as $menu){
			/** @var \Zine\Menu\Menu $menu */
			$ul .= '<li role="presentation">';
			if ($menu->items['url']) {
				$ul .= '<a' . $menu->menuClass() . ' href="' . $menu->items['url'] . '" role="menuitem" tabindex="-1">' . $menu->items['title'] . '</a>';
			} else {
				$ul .= $menu->items['title'];
			}
			$ul .= '</li>';
			if($menu->items['divider']) {
				$ul .= '<li role="presentation"' . $menu->dividerClass() . '></li>';
			}
		}
		$ul .= '</ul>';

		return '<div class="dropdown">'.$button.$ul.'</div>';
	}

	public function menuClass(){
		return self::attributes(array_except($this->items, $this->attrExcept));
	}
	
	public function dividerClass(){
		return ' class="'.trim($this->items['divider']).'"';
	}
	/**
	 * Build an HTML attribute string from an array.
	 *
	 * @param  array $attributes
	 *
	 * @return string
	 */
	public static function attributes($attributes) {
		$html = array();

		foreach ((array)$attributes as $key => $value) {
			$element = self::attributeElement($key, $value);
			if (!is_null($element)) $html[] = $element;
		}

		return count($html) > 0 ? ' ' . implode(' ', $html) : '';
	}

	/**
	 * Build a single attribute element.
	 *
	 * @param  string $key
	 * @param  string $value
	 *
	 * @return string
	 */
	protected static function attributeElement($key, $value) {
		if (is_numeric($key)) $key = $value;
		if (!empty($value)) return $key . '="' . e($value) . '"';
	}

}