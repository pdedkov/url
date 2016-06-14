<?php
namespace Url;

use Browser\Instance as Browser;
use Browser\Exception as Exception;

use Config\Singleton as Base;

/**
 *
 * Работа со страницами
 */
class Checker extends Base {
	protected $_Cache = null;

	protected static $_defaults = [
		'agent'	=> 'Browser\Checker',
		'cache' => false
	];

	protected function __construct($config = array()) {
		parent::__construct(__NAMESPACE__, $config);

		try {
			if ($this->_config['cache'] && class_exists('Cache\Db')) {
				$this->_Cache = new Cache\Db();
			}
		} catch (\Exception $e) {}
	}

	const STATUS_OK = 200;
	const STATUS_NOT_FOUND = 404;

	const STATUS_FAIL = -1;
	const STATUS_INVALID_CONTENT = -2;

	protected static $_instance = null;

	public static function getInstance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Проверяем доступность страницы и приемлемость контент-типа
	 *
	 * @param string $page url страницы
	 * @param string $type проверка контента
	 * @param int $size максимально допустимый размер файла
	 * @param bool $return возвращаем bool или url в случае необходимости
	 *
	 * @return bool|string
	 */
	public static function isValid($page, $type = null, $size = null, $return = false) {
		$_this = self::getInstance();

		$header = $_this->getAnswerHeader($page);

		if ($header['http_code'] == self::STATUS_OK) {
			//проверим тип контента

			if (!empty($type) && strpos($header['content_type'], $type) === false) {
				//невалидный контент
				$header['http_code'] = self::STATUS_INVALID_CONTENT;
			} elseif (!empty($size) && !empty($header['download_content_length']) && $size < intval($header['download_content_length'])) {
				$header['http_code'] = self::STATUS_INVALID_CONTENT;
			}
		}
		if ($header['http_code'] == self::STATUS_OK) {
			return $return ? $header['url'] : true;
		} else {
			return false;
		}
	}

	/**
	 * Проверяем доступность страницы
	 *
	 * @param string $page url страницы
	 * @return bool
	 */
	public static function isAvailable($page) {
		$_this = self::getInstance();
		$header = $_this->getAnswerHeader($page);
		
		return $header['http_code'];
	}

	/**
	 * Получаем хэдер страницы, код ответа подменяется - либо 200, либо 404, все остальные -1
	 *
	 * @param string $page url страницы
	 * @return array хэдер
	 */
	public static function getAnswerHeader($page) {
		$_this = self::getInstance();

		// пытаемся считать из кэша
		if (is_object($_this->_Cache)) {
			try {
				$info = $_this->_Cache->readCache($page);
				if (!empty($info)) {
					return $info;
				}
			} catch (\Exception $e) {}
		}

		$info = array();
		try {

			Browser::configure(
				['proxy' => 'random', 'options' => [CURLOPT_NOBODY => true, CURLOPT_FOLLOWLOCATION => true], 'agent' => $_this->_config['agent']],
				['retry' => false, 'exception' => false, 'return' => 'headers']
			);

			// Получаем страницу
			$info = Browser::get($page);

			if (!in_array($info['http_code'], array(self::STATUS_OK, self::STATUS_NOT_FOUND))) {
				$info['http_code'] = self::STATUS_FAIL;
			}

		} catch (Exception $e) {
			// Если браузер свалился - то код все упало
			$info['http_code'] = self::STATUS_FAIL;
		}

		if (is_object($_this->_Cache)) {
			// пишем всё это в кэш
			try {
				$_this->_Cache->writeCache($page, $info);
			} catch (\Exception $e) {}
		}

		return $info;
	}
}
