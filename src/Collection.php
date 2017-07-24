<?php
/**
 * Class Collection
 *
 * @author zine hezhiying@gmail.com
 * Date 2017/7/24 下午3:39
 */

namespace Zine\Menu;
use Illuminate\Support\Collection as BaseCollection;

class Collection extends BaseCollection {

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
}