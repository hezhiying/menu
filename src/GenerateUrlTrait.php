<?php

namespace Zine\Menu;

trait GenerateUrlTrait {

	/**
	 * @return \Illuminate\Contracts\Routing\UrlGenerator
	 */
	protected function urlResolver() {
		return app('url');
	}

	/**
	 * Get the form action from the options.
	 *
	 * @param string|array $options
	 *
	 * @return string
	 */
	public function dispatch($options) {

		if (isset($options['url'])) {
			return $this->getUrl($options);
		} elseif (isset($options['route'])) {
			return $this->getRoute($options['route']);
		} elseif (isset($options['action'])) {
			return $this->getControllerAction($options['action']);
		} elseif (is_string($options)) {
			return $options;
		}

		return null;
	}

	/**
	 * Get the action for a "url" option.
	 *
	 * @param  array|string $options
	 *
	 * @return string
	 */
	protected function getUrl($options) {
		$url    = $options['url'];
		$secure = (isset($options['secure']) && $options['secure'] === true) ? true : false;
		if (is_array($url)) {
			if (self::isAbs($url[0])) {
				return $url[0];
			}

			return $this->urlResolver()->to($url[0], array_slice($url, 1), $secure);
		}

		if (self::isAbs($url)) {
			return $url;
		}

		return $this->urlResolver()->to($url, array(), $secure);
	}

	/**
	 * Check if the given url is an absolute url.
	 *
	 * @param  string $url
	 *
	 * @return boolean
	 */
	public static function isAbs($url) {
		return parse_url($url, PHP_URL_SCHEME) or false;
	}

	/**
	 * Get the action for a "route" option.
	 *
	 * @param  array|string $options
	 *
	 * @return string
	 */
	protected function getRoute($options) {
		if (is_array($options)) {
			return $this->urlResolver()->route($options[0], array_slice($options, 1));
		}

		return $this->urlResolver()->route($options);
	}

	/**
	 * Get the action for an "action" option.
	 *
	 * @param  array|string $options
	 *
	 * @return string
	 */
	protected function getControllerAction($options) {
		if (is_array($options)) {
			return $this->urlResolver()->action($options[0], array_slice($options, 1));
		}

		return $this->urlResolver()->action($options);
	}

}