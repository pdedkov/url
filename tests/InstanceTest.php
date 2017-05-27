<?php
namespace Url;

class InstanceTest extends \PHPUnit_Framework_TestCase {
	public function testShouldWwwHttp() {
		$url = 'https://www.beesyst.com/kakogo-brokera-vyibrat-dlya-torgovli-na-foreks/';

		$proto = Instance::getProto($url);
		$wwwLess = Instance::wwwLess(Instance::httpLess($url));

		$this->assertEquals('https://', $proto);
		$this->assertEquals('beesyst.com/kakogo-brokera-vyibrat-dlya-torgovli-na-foreks/', $wwwLess);


	}
	public function testShouldCheckDecodeEncode() {
		$raw = 'http://appliances.wikimart.ru/builtin/cooker_hood/tag/кухонные-вытяжки/';
		$encoded = 'http://appliances.wikimart.ru/builtin/cooker_hood/tag/%D0%BA%D1%83%D1%85%D0%BE%D0%BD%D0%BD%D1%8B%D0%B5-%D0%B2%D1%8B%D1%82%D1%8F%D0%B6%D0%BA%D0%B8/';

		$this->assertEquals($encoded, Instance::encode($raw));
		$this->assertEquals(Instance::encode($encoded), Instance::encode($raw));
		$this->assertEquals(Instance::decode($encoded), $raw);
		$this->assertEquals(Instance::decode($encoded), Instance::decode($raw));
	}

	public function testShouldCheckSameUrl() {
		$this->assertFalse(Instance::isSameUrl('http://президент2.рф/', 'xn--d1abbgf6aiiy.xn--p1ai'));

		$this->assertTrue(Instance::isSameUrl('http://президент.рф/продвижение/оптимизация/?q=3&v=2', 'xn--d1abbgf6aiiy.xn--p1ai/оптимизация/продвижение/?v=2&q=3'));
		$this->assertFalse(Instance::isSameUrl('http://президент.рф/продвижение/оптимизация/?q=4&v=2', 'xn--d1abbgf6aiiy.xn--p1ai/оптимизация/продвижение/?v=2&q=3'));
		$this->assertFalse(Instance::isSameUrl('http://президент.рф/продвижение/оптимизация/?q=4&v=2&m=5', 'xn--d1abbgf6aiiy.xn--p1ai/оптимизация/продвижение/?v=2&q=3'));

		$this->assertTrue(Instance::isSameUrl('http://президент.рф/?v=2&mail=1', 'xn--d1abbgf6aiiy.xn--p1ai/?mail=1&v=2'));
		$this->assertFalse(Instance::isSameUrl('http://mail.ru/?v=2&mail=1', 'xn--d1abbgf6aiiy.xn--p1ai/?mail=1&v=2'));

		$this->assertTrue(Instance::isSameUrl('http://www.президент.рф/?v=2&mail=1', 'xn--d1abbgf6aiiy.xn--p1ai/?mail=1&v=2'));
		$this->assertFalse(Instance::isSameUrl('http://www.президент.рф/?v=2&mail=1', 'xn--d1abbgf6aiiy.xn--p1ai/?mail=1&v=2', true));
		$this->assertTrue(Instance::isSameUrl(
			'популярная-медицина.рф/%D1%80%D0%B0%D0%BD%D0%BD%D0%B8%D0%B5-%D1%81%D0%B8%D0%BC%D0%BF%D1%82%D0%BE%D0%BC%D1%8B-%D1%80%D0%B0%D0%BA%D0%B0-%D0%B3%D0%BE%D1%80%D0%BB%D0%B0-%D0%B8-%D0%B3%D0%BE%D1%80%D1%82%D0%B0%D0%BD%D0%B8',
			'http://xn----7sbbpetaslhhcmbq0c8czid.xn--p1ai/%D1%80%D0%B0%D0%BD%D0%BD%D0%B8%D0%B5-%D1%81%D0%B8%D0%BC%D0%BF%D1%82%D0%BE%D0%BC%D1%8B-%D1%80%D0%B0%D0%BA%D0%B0-%D0%B3%D0%BE%D1%80%D0%BB%D0%B0-%D0%B8-%D0%B3%D0%BE%D1%80%D1%82%D0%B0%D0%BD%D0%B8'
		));

		$this->assertTrue(Instance::isSameUrl(
			'www.autorules.ru/info/truck/1286/', 'https://www.autorules.ru/info/truck/1286/'
		));

		$this->assertTrue(Instance::isSameUrl(
			'www.autorules.ru/info/truck/1286/', 'https://www.autorules.ru/info/1286/truck/'
		));

		$this->assertTrue(Instance::isSameUrl(
			'почемуже.рф/как-выбрать-свадебного-фотографа/',
			'http://xn--e1aacxif5a3a.xn--p1ai/%D0%BA%D0%B0%D0%BA-%D0%B2%D1%8B%D0%B1%D1%80%D0%B0%D1%82%D1%8C-%D1%81%D0%B2%D0%B0%D0%B4%D0%B5%D0%B1%D0%BD%D0%BE%D0%B3%D0%BE-%D1%84%D0%BE%D1%82%D0%BE%D0%B3%D1%80%D0%B0%D1%84%D0%B0/'
		));

		$this->assertTrue(Instance::isSameUrl(
			'www.iserov.ru/последние-новости/как-подобрать-бильярдный-клуб.html',
			'http://www.iserov.ru/%D0%BF%D0%BE%D1%81%D0%BB%D0%B5%D0%B4%D0%BD%D0%B8%D0%B5-%D0%BD%D0%BE%D0%B2%D0%BE%D1%81%D1%82%D0%B8/%D0%BA%D0%B0%D0%BA-%D0%BF%D0%BE%D0%B4%D0%BE%D0%B1%D1%80%D0%B0%D1%82%D1%8C-%D0%B1%D0%B8%D0%BB%D1%8C%D1%8F%D1%80%D0%B4%D0%BD%D1%8B%D0%B9-%D0%BA%D0%BB%D1%83%D0%B1.html'
		));

		$this->assertTrue(Instance::isSameUrl(
			'76yar.ru/статьи/электрика/generator-sadko-vasha-lichnaya-minielektrostanciya.html',
			'http://76yar.ru/%D1%81%D1%82%D0%B0%D1%82%D1%8C%D0%B8/%D1%8D%D0%BB%D0%B5%D0%BA%D1%82%D1%80%D0%B8%D0%BA%D0%B0/generator-sadko-vasha-lichnaya-minielektrostanciya.html'
		));

		$this->assertTrue(Instance::isSameUrl(
			'мегапочему.рф/?p=7000',
			'http://xn--80affc5aemh3bzb.xn--p1ai/?p=7000'
		));

		$this->assertTrue(Instance::isSameUrl(
				'акак.рф/здоровье/три-причины-рожать-за-границей/',
				'http://xn--80aa3ab.xn--p1ai/%D0%B7%D0%B4%D0%BE%D1%80%D0%BE%D0%B2%D1%8C%D0%B5/%D1%82%D1%80%D0%B8-%D0%BF%D1%80%D0%B8%D1%87%D0%B8%D0%BD%D1%8B-%D1%80%D0%BE%D0%B6%D0%B0%D1%82%D1%8C-%D0%B7%D0%B0-%D0%B3%D1%80%D0%B0%D0%BD%D0%B8%D1%86%D0%B5%D0%B9/'
			));
	}

	public function testShouldGetHost() {
		 $this->assertEquals(Instance::getHost('http://miralab.ru/mail.html'), 'miralab.ru');
		 $this->assertEquals(Instance::getHost('http://www.miralab.ru/mail.html'), 'www.miralab.ru');
		 $this->assertEquals(Instance::getHost('http://www.miralab.ru/mail.html', true), 'miralab.ru');
		 $this->assertEquals(Instance::getHost('http://президент.рф/mail.html', true), 'президент.рф');
		 $this->assertEquals(Instance::getHost('президент.рф'), 'президент.рф');
		 $this->assertEquals(Instance::getHost('президент.рф/fsdfasdfas.com'), 'президент.рф');
		 $this->assertEquals(Instance::getHostIdn('президент.рф/fsdfasdfas.com'), 'xn--d1abbgf6aiiy.xn--p1ai');
	}

	public function testShouldTestValid() {
		$this->assertTrue(Instance::isValid('http://miralab.ru/mail.html'));
		$this->assertTrue(Instance::isValid('http://miralab.ru/mail.htmlfdsfasd'));
		$this->assertTrue(Instance::isValid('http://президент.рф'));
	}

	public function testShouldGetUri() {
		$this->assertEquals(Instance::getUri('miralab.ru', 'http://miralab.ru/mail.html'), '/mail.html');
		$this->assertEquals(Instance::getUri('miralab.ru', 'http://www.miralab.ru/mail1.html'), '/mail1.html');
		$this->assertEquals(Instance::getUri('www.президент.рф', 'http://президент.рф/mail3123dafsadfasdfa.html'), '/mail3123dafsadfasdfa.html');
		$this->assertEquals(Instance::getUri(null, 'http://президент.рф/mail3123dafsadfasdfa.html'), '/mail3123dafsadfasdfa.html');
	}
}