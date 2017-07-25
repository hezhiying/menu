<?php
/**
 * Class MenuNotFoundException
 *
 * @author zine hezhiying@gmail.com
 * Date 2017/7/25 下午3:34
 */

namespace Zine\Menu;


class MenuNotFoundException extends \Exception {
	protected $message = 'Menu []';
	public function __construct($menuPath, $code = 404) {
		parent::__construct(sprintf("Menu [%s] Not Found.",$menuPath), $code);
	}
}