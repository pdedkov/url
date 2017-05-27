<?php
namespace Url;

use Etechnika\IdnaConvert\IdnaConvert;

/**
 * Сборник функций для обработки урлов
 */
class Instance {
	protected static $_instance = null;

	protected static $_urlNotations = [
		'scheme' => ['after' => '://'],
		'user' => null,
		'pass' => ['after' => '@', 'before' => ':'],
		'host' => null,
		'port' => ['before' => ':'],
		'path' => null,
		'query' => ['before' => '?'],
		'fragment' => ['before' => '#'],
	];

	public static function getInstance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Валиден ли адрес
	 *
	 * @param string $url
	 * @return bool
	 */
	public static function isValid($url) {
		$uriRegexp = '/^(?:(?:http(s?)):\\/\\/)?(?:(?:(?:25[0-5]|2[0-4]\d|(?:(?:1\d)?|[1-9]?)\d)\.){3}(?:25[0-5]|2[0-4]\d|(?:(?:1\d)?|[1-9]?)\d)'
			. '|(?:[0-9a-zа-яА-ЯёЁі]{1}[0-9a-zа-яА-ЯёЁі\\-\_]*\\.)*(?:[0-9a-zа-яА-ЯёЁі]{1}[0-9a-zа-яА-ЯёЁі\\-\_]{0,56})\\.(?:[a-zа-яА-ЯёЁ0-9\\-\_]{2,10}|[a-zа-яА-ЯёЁ0-9\\]{2}\\.[a-zа-яА-ЯёЁ0-9\\-\_]{2,10})'
			. '(?::[0-9]{1,4})?)(?:\\/?|\\/[*a-zA-Zа-яА-ЯёЁ\\w\\-\\.,\'–@?^=%&:;\/~\\+#!\(\)\[\]\{\}]*[*a-zA-Zа-яА-ЯёЁ\\w\\-\\–@?^=%&\/~\\+#;\.!\)\]\}])$/iu';

		if (!preg_match($uriRegexp, $url)) {
			return (bool)preg_match($uriRegexp, rawurldecode($url)); // проверяем закодированные кириллические урлы
		}
		return true;
	}

	/**
	 * Возвращает исходный адрес без http:// в начале
	 *
	 * @param string $url
	 * @return string
	 */
	public static function httpLess($url) {
		if (strpos($url, 'http://') === 0) {
			$url = substr($url, 7);
		} elseif (strpos($url, 'https://') === 0) {
			$url = substr($url, 8);
		}
		return $url;
	}

	/**
	 * Добавляет к исходному адресу http, если отсутствует
	 *
	 * @param string $url
	 * @return string
	 */
	public static function httpAdd($url) {
		if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
			$url = 'http://' . $url;
		}
		return $url;
	}

	/**
	 * Возвращает адрес без начального "www."
	 *
	 * @param string $url
	 * @return string
	 */
	public static function wwwLess($url) {
		if (strpos($url, 'www.') === 0 && substr_count($url, '.') > 1) {
			$url = substr($url, 4);
		}
		return $url;
	}

	public static function slashLess($url) {
		return preg_replace('#/+$#', '', $url);
	}

	/**
	 * Получение имени хоста (домена) из урла
	 *
	 * @param string $url
	 * @param bool $cutWWW - отрезать ли www. если оно имеется
	 * @return string
	 */
	public static function getHost($url, $cutWWW = false) {
		$url = self::httpLess($url);
		if ($cutWWW) {
			$url = self::wwwLess($url);
		}
		$p = strpos($url,'/');
		return ($p === false) ? $url : substr($url, 0, $p);
	}

	/**
	 * Получение имени хоста (домена) из урла с перекодированием в idn-формат если необходимо
	 *
	 * @param string $url
	 * @param bool $cutWWW - отрезать ли www. если оно имеется
	 * @return string
	 */
	public static function getHostIdn($url, $cutWWW = false) {
		$url = self::httpLess($url);
		if ($cutWWW) {
			$url = self::wwwLess($url);
		}
		$p = strpos($url,'/');
		$url = ($p === false) ? $url : substr($url, 0, $p);

		return (new IdnaConvert())->encode($url);
	}

	/**
	 * Конвертируем в пуникод
	 *
	 * @param string $url не кодированный url
	 * @return string кодированный url
	 */
	public static function toIdn($url) {
		$host = self::getHost($url);

		return str_replace($host, (new IdnaConvert())->encode($host), $url);
	}

	/**
	 * Конвертируем из пуникода
	 *
	 * @param string $url кодированный
	 * @paran string $url декодированный
	 */
	public static function fromIdn($url) {
		return (new IdnaConvert())->decode($url);
	}

	/**
	 * Сравнение доменов для url
	 *
	 * @param string $url1 url без http://
	 * @param string $url2 url без http://
	 * @param string $strict сравнение с учетом www
	 * @return bool
	 */
	public static function isSameHost($url1, $url2, $strict = true) {
		return strcasecmp(self::getHost($url1, !$strict), self::getHost($url2, !$strict)) == 0;
	}

	/**
	 * Сравнение доменов для url с цчётом idna
	 *
	 * @param string $url1 url без http://
	 * @param string $url2 url без http://
	 * @param string $strict сравнение с учетом www
	 * @return bool
	 */
	public static function isSameHostIdna($url1, $url2, $strict = true) {
		$Idna = new IdnaConvert();
		$url1 = $Idna->encode($url1);
		$url2 = $Idna->encode($url2);

		return strcasecmp(self::getHost($url1, !$strict), self::getHost($url2, !$strict)) == 0;
	}

	/**
	 * Сравнение двух url-ов
	 *
	 * @param string $url1 первый url
	 * @param string $url2 второй url
	 * @param bool $strict сравнивать с учётом www
	 */
	public static function isSameUrl($url1, $url2, $strict = false) {
		// проверяем host
		if (!self::isSameHostIdna($url1, $url2, $strict)) {
			return false;
		}

		// приводим к каноническому виду
		// -http, -www, -/
		$url1 = parse_url(self::httpAdd(self::slashLess(self::wwwLess($url1))));
		$url2 = parse_url(self::httpAdd(self::slashLess(self::wwwLess($url2))));

		// разбиваем path и query по разделителю, сортируем, собираем назад и сравниваем
		foreach (['path' => '/', 'query' => '&'] as $index => $delimeter) {
			if (empty($url1[$index]) || empty($url2[$index])) {
				continue;
			}

			// пытаемся декодировать эти блоки для сравнения
			$url1[$index] = urldecode($url1[$index]);
			$url2[$index] = urldecode($url2[$index]);

			$path = [];

			foreach ([$url1, $url2] as $url) {
				$url[$index] = array_filter(explode($delimeter, $url[$index]));
				sort($url[$index]);
				$path[] = implode($delimeter, $url[$index]);

			}
			if (strcasecmp($path[0], $path[1]) != 0) {
				// пытаемся кодировать-декодировать
				return false;
			}
		}

		return true;
	}

	/**
	 * БОлее полная проверка совпадения Url-а с учётом пуни и тд
	 * @param $url1
	 * @param $url2
	 *
	 * @return bool
	 */
	public function isSameUrlFull($url1, $url2) {
		$url1 = mb_strtolower($url1);
		$url2 = mb_strtolower($url2);

		if (self::isSameUrl($url1, $url2)) {
			return true;
		}

		// проверим пуникоду ссылки
		$pyni = self::convertToPunycode($url1);
		$urlDec = urldecode($url1);

		if (strcasecmp($url1, $url2) == 0) {
			return true;
		} elseif (strcasecmp($urlDec, $url2) == 0) {
			return true;
		} elseif (strcasecmp($pyni['linkPunyEnc'], $url2) == 0) {
			return true;
		} elseif (strcasecmp($pyni['linkPunyDec'], $url2) == 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Возвращает адрес без последнего "/"
	 *
	 * @param string $url
	 * @return string
	 */
	public static function excludeTrailingSlash($url) {
		return (substr($url, -1) == '/')
			? substr($url, 0, - 1)
			: $url;
	}

	/**
	 * Выделяем из полного url-а только path
	 * @param string $site исходные домен
	 * @param string $url url из которого нужно получить
	 *
	 * @return string полученный url
	 */
	public static function getUri($site, $url) {
		$site = $site ?: self::getHost($url);

		$site = self::wwwLess($site);
		$Idna = new IdnaConvert();
		// конвертим сайт в idna
		$idna = $Idna->encode($site);
		$page = self::httpLess($url);
		$page = str_replace(array("www.$site", $site, $idna, "www.$idna"), '', $page);

		return (empty($page)) ? '/' : $page;
	}

	/**
	 * Разбивает URL на протокол и остальную часть
	 * @param $url
	 *
	 * @return array
	 */
	public static function splitUrl($url) {
		$splitData = array();
		if(preg_match('/(https?)*(:\/\/)*(.*)/', $url, $matches)) {
			$splitData[] = (isset($matches[1]) && !empty($matches[1])) ? $matches[1] : 'http';
			$splitData[] = (isset($matches[3]) && !empty($matches[3])) ? $matches[3] : '';
		}
		return $splitData;
	}

	/**
	 * Возвращает URL с протоколом.
	 * В случае отсутствия его, подставляет http
	 * @param $url
	 *
	 * @return string
	 */
	public static function getUrlWithProtocol($url) {
		if(empty($url)) {
			return $url;
		}
		list($scheme, $host) = self::splitUrl($url);
		if(empty($scheme) || empty($host)) {
			return '';
		}
		return $scheme.'://'.$host;
	}

	/**
	 * Возвращает список подходящих ссылок на странице
	 * @param string $page
	 * @param bool $withSlash
	 * @param bool $withWww
	 *
	 * @return array
	 */
	public static function giveMeLinksDom($page, $withSlash = false, $withWww = false) {
		// символ 00 мешает DOM-парсеру
		$page = preg_replace('/\x00/', ' ', $page);

		// приходит уже перекодированный в ютф8 текс, нужно заменить объявляения кодировки в тексте
		$page = preg_replace(['/windows-1251/i', '/cp1251/i'], 'utf-8', $page);
		// некоторые символы в ссылках кодируются, исправим это
		$page = preg_replace(['/&#0?61;/', '/&#0?38;/', '/&amp;/', '/&#0?37;/', '/&ndash;/'], ['=', '&', '&', '%', '–'], $page);
		// заменим закоменченый тег <!--noindex--> на обычный
		$c = 1;
		while ($c && $c > 0) {
			$page = preg_replace('/(\<\!--noindex--\>)(.*)(\<\!--\/noindex--\>)/ismU', '<noindex>$2</noindex>', $page, -1, $c);
		}
		$xml = new \DOMDocument();
		@$xml->loadHTML('<?xml encoding="UTF-8">' . $page);
		$links = array();
		foreach($xml->getElementsByTagName('a') as $link) {
			$href = $link->getAttribute('href');
			// ссылка без урла
			if(!$href || in_array($href, ["#", '/', 'javascript:void(0);'])) {
				continue;
			}
			// проверим элемент в <noindex>
			$noindex = self::inNoIndexDom($link);
			// проверим чтобы только элемент находился в noindex
			$noindexOnly = $noindex ? self::inNoIndexDom($link, true) : false;
			if(mb_strlen(utf8_decode($href)) <  mb_strlen($href)) {
				$href = utf8_decode($href);
			}
			$href = urldecode($href);

			$href = self::httpLess(trim($href));
			if (!$withWww) {
				$href = self::wwwLess($href);
			}

			if (!$withSlash) {
				$href = preg_replace(['/(\/\s*)$/i', '/(\/?#\s*)$/i'], '', $href);
			} else {
				$href = preg_replace(['/(\s*)$/i', '/(?#\s*)$/i'], '', $href);
			}

			$links[] = array(
				'acceptor' => mb_strtolower($href),
				'anchor' => $link->nodeValue,
				'rel' => $link->getAttribute('rel'),
				'noindex' => $noindex,
				'noindexOnly' => $noindexOnly
			);
		}

		return $links;
	}

	/**
	 * Не спрятан ли элемент под noindex
	 * @param DOMElement $node
	 * @param bool $onlyNode если true, то проверить чтобы только элемент был обернут в noindex
	 * @return bool
	 */
	public static function inNoIndexDom(\DOMElement $node, $onlyNode = false) {
		$parent = $node->parentNode;

		if ($onlyNode) {
			if ($parent->nodeName == 'noindex') {
				$prevNode = $node->previousSibling;
				$nextNode = $node->nextSibling;

				// проверяем предыдущие узлы в ДОМе
				while ($prevNode) {
					// пустые текстовые пропускаем
					if (
						$prevNode->nodeType !== XML_TEXT_NODE
						|| preg_replace('/(\s|&nbsp;)+/iu', '', $prevNode->nodeValue)
					) {
						return false;
					}

					$prevNode = $prevNode->previousSibling;
				}

				// проверяем следующие за элементом узлы в ДОМе
				while ($nextNode) {
					// пустые текстовые пропускаем
					if (
						$nextNode->nodeType !== XML_TEXT_NODE
						|| preg_replace('/(\s|&nbsp;)+/iu', '', $nextNode->nodeValue)
					) {
						return false;
					}
					$nextNode = $nextNode->nextSibling;
				}

				// проверяем, чтобы не было больше родителей noindex
				$parent = $parent->parentNode;
				while ($parent) {
					if ($parent->nodeName == 'noindex') {
						return false;
					}
					$parent = $parent->parentNode;
				}

				return true;
			}
		} else {
			while ($parent) {
				if ($parent->nodeName == 'noindex') {
					return true;
				}
				$parent = $parent->parentNode;
			}
		}

		return false;
	}

	/**
	 * Почистим видео ссылку
	 * @param $link
	 * @return mixed
	 */
	public static function clearVideoUrl($link){
		$link = self::httpLess($link);
		return preg_replace('#^//#', '', $link);
	}

	/**
	 * Возвращает список ссылок на странице
	 * @param string $page
	 * @return array
	 */
	public static function giveMeLinks($page) {
		$result = [];
		$pattern = "/<a \s [^<]*? href \s*  = \s* (['|\"]) \s* ([^\\1]*?) (\\1) .*? > (.*?) <\/a\s*>/xis";

		if (preg_match_all($pattern, $page, $subject, PREG_SET_ORDER)) {
			foreach ($subject as $e) {
				$result[] = [
					'href' => $e[2],
					'anchor' => $e[4]
				];
			}
		}

		$subject = [];
		$pattern = "/<a \s [^<]*? href \s* = \s*([^'\"]+?) ( (\s[^>]*? >) | > ) (.*?) <\/a\s*>/xis";
		if (preg_match_all($pattern, $page, $subject, PREG_SET_ORDER)) {
			foreach ($subject as $e) {
				if (!in_array($e[1], \Set::extract('{n}.href', $result))) {
					$result[] = [
						'href' => $e[1],
						'anchor' => $e[4]
					];
				}
			}
		}
		return $result;
	}

	/**
	 * Для домена .рф конвертируем ссылку для проверок
	 * @param string $link
	 * @return array
	 *
	 */
	public static function convertToPunycode($link = null){
		$Idna = new IdnaConvert();

		$link = self::httpLess($link);

		// для конветрации нужно выделить домен и конвертировать только его.
		$host = self::getHost($link);
		$artUrl = str_replace(self::getHost($link),'', $link);

		$res['linkPunyEnc'] = $Idna->encode($host).$artUrl;
		$res['linkPunyDec'] = $Idna->decode(strtolower($host)).$artUrl; // strtolower необходим из-за того, что idna_convert неверно декодирует при большебуквенном написании

		return $res;
	}

	/**
	 * кодируем url
	  *  @param stirng $url
	 *
     *  @return string кодированный url
   */
	public static function encode($url) {
		$decoded = self::decode($url);

		if (strcasecmp($decoded, $url) == 0) {
			// строка изначально не кодированная так что кодируем её
			$parsed = self::parseUrl($url);

			foreach (['path', 'query', 'fragment'] as $part) {
				if (empty($parsed[$part])) {
					continue;
				}
				// сначала бьём по "/"
				$parsed[$part] = implode("/", array_map(function($value) {
					return rawurlencode($value);
				}, explode("/", $parsed[$part])));
			}
			$url = self::buildUrl($parsed);
		}

		return $url;
	}

     /**
	   * Обёртка urldecode
	  *
	  * @param string $url раскодируем
	  * @return string раскодированная строка
     */
     public static function decode($url) {
     	return rawurldecode($url);
     }

	/**
	 * парсим url, на данный момент просто обёртка над parse_url
	 *
	 * @param string $url
	 *
 *    @return array
 	*/
	public static function parseUrl($url) {
		return parse_url($url);
	}

	/**
	 * результат self::parseUrl
	 * @param string $url исходная
    * @return string собранный url
    */
	public static function buildUrl($url) {
		if (!is_array($url)) {
			return $url;
		}

		foreach (self::$_urlNotations as $part => $rules) {
			if (array_key_exists($part, $url)) {
				$url[$part] = @$rules['before'] . $url[$part] . @$rules['after'];
			}
		}

		return array_reduce($url,  function($return, $item) {
			$return .= $item;

			return $return;
		});
	}
}