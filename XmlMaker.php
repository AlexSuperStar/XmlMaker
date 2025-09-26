<?php

namespace alexstar;

/*
 * Author: Alexey Starikov <alexsuperstar@mail.ru>
 * https://github.com/AlexSuperStar/xmlMaker
 * Modified for XML with attributes support
 *
 * Usage:
 * $a = new \alexstar\XmlMaker('root');
 * $a->item->{"@attributes"}["id"] = "1";
 * $a->item->name = "Test";
 * echo $a;
 * <root><item id="1"><name>Test</name></item></root>
 */

class XmlMaker implements \ArrayAccess, \IteratorAggregate, \Countable
{
	private array $_data = [];
	private string $_rootName;
	private array $_attributes = [];

	public function __construct(string $rootName = 'root')
	{
		$this->_rootName = $rootName;
	}

	public function parse($data): void
	{
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				if ($key === '@attributes') {
					$this->_attributes = is_array($value) ? $value : (array)$value;
				}
				elseif (is_scalar($value) || is_null($value)) {
					$this->_data[$key] = $value;
				}
				else {
					$child = new self($this->_rootName);
					$child->parse($value);
					$this->_data[$key] = $child;
				}
			}
		}
		elseif (is_object($data)) {
			foreach ($data as $key => $value) {
				if ($key === '@attributes') {
					$this->_attributes = is_array($value) ? $value : (array)$value;
				}
				elseif (is_scalar($value) || is_null($value)) {
					$this->_data[$key] = $value;
				}
				else {
					$child = new self($this->_rootName);
					$child->parse($value);
					$this->_data[$key] = $child;
				}
			}
		}
	}

	// === Magic Methods ===
	public function __set(string $name, $value): void
	{
		if (is_scalar($value) || is_null($value)) {
			$this->_data[$name] = $value;
		}
		else {
			$this->_data[$name] = new self($this->_rootName);
			if (is_array($value) || is_object($value)) {
				$this->_data[$name]->parse($value);
			}
			else {
				$this->_data[$name] = $value;
			}
		}
	}

	public function &__get(string $name)
	{
		if ($name === '@attributes') {
			return $this->_attributes;
		}

		if (!isset($this->_data[$name])) {
			$this->_data[$name] = new self($this->_rootName);
		}
		return $this->_data[$name];
	}

	public function __isset(string $name): bool
	{
		return isset($this->_data[$name]);
	}

	public function __unset(string $name): void
	{
		unset($this->_data[$name]);
	}

	// === ArrayAccess ===
	public function offsetSet($offset, $value): void
	{
		if (is_scalar($value) || is_null($value)) {
			$this->_data[$offset] = $value;
		}
		else {
			$this->_data[$offset] = new self($this->_rootName);
			if (is_array($value) || is_object($value)) {
				$this->_data[$offset]->parse($value);
			}
		}
	}

	public function offsetExists($offset): bool
	{
		return isset($this->_data[$offset]);
	}

	public function offsetUnset($offset): void
	{
		unset($this->_data[$offset]);
	}

	public function &offsetGet($offset): mixed
	{
		if (!isset($this->_data[$offset])) {
			$this->_data[$offset] = new self($this->_rootName);
		}
		return $this->_data[$offset];
	}

	// === Countable ===
	public function count(): int
	{
		return count($this->_data);
	}

	// === IteratorAggregate ===
	public function getIterator(): \Iterator
	{
		return new \ArrayIterator($this->_data);
	}

	// === Conversion ===
	public function toArray(): array
	{
		$result = [];
		if (!empty($this->_attributes)) {
			$result['@attributes'] = $this->_attributes;
		}
		foreach ($this->_data as $key => $value) {
			if ($key === '@items') {
				// Специальная обработка @items
				$result['@items'] = [];
				foreach ($value as $itemName => $list) {
					$result['@items'][$itemName] = [];
					foreach ($list as $item) {
						if ($item instanceof self) {
							$result['@items'][$itemName][] = $item->toArray();
						}
						else {
							$result['@items'][$itemName][] = $item;
						}
					}
				}
			}
			elseif ($value instanceof self) {
				$result[$key] = $value->toArray();
			}
			else {
				$result[$key] = $value;
			}
		}
		return $result;
	}

	public function addItem(string $itemName, array $itemData): self
	{
		if (!isset($this->_data['@items'])) {
			$this->_data['@items'] = [];
		}
		$this->_data['@items'][$itemName][] = $itemData;
		return $this; // для поддержки цепочки вызовов
	}

	// === XML ===
	private function arrayToXml(array $data, \SimpleXMLElement $xml, ?string $parentName = null): void
	{
		foreach ($data as $key => $value) {
			// Обработка служебных ключей
			if ($key === '@attributes' || $key === '@itemName') {
				continue; // эти ключи не создают элементы
			}
			if ($key === '@items') {
				if (!is_array($value)) {
					continue;
				}
				foreach ($value as $itemName => $itemsList) {
					if (!is_array($itemsList)) {
						continue;
					}
					foreach ($itemsList as $item) {
						$child = $xml->addChild($itemName);
						$attrs = [];
						if (is_array($item) && isset($item['@attributes'])) {
							$attrs = $item['@attributes'];
							unset($item['@attributes']);
						}
						foreach ($attrs as $attrName => $attrValue) {
							if ($attrName !== '' && $attrValue !== null) {
								$child->addAttribute($attrName, (string)$attrValue);
							}
						}
						if (is_array($item) && !empty($item)) {
							$this->arrayToXml($item, $child, $itemName);
						}
					}
				}
				continue;
			}

			// Обычный элемент
			$elementName = is_numeric($key) ? ($parentName ?: 'item') : $key;
			$child = $xml->addChild($elementName);
			$attrs = [];
			if (is_array($value) && isset($value['@attributes'])) {
				$attrs = $value['@attributes'];
				unset($value['@attributes']);
			}
			foreach ($attrs as $attrName => $attrValue) {
				if ($attrName !== '' && $attrValue !== null) {
					$child->addAttribute($attrName, (string)$attrValue);
				}
			}
			if (is_array($value)) {
				if (!empty($value)) {
					if (count($value) === 1 && isset($value[0]) && is_scalar($value[0])) {
						$child[0] = htmlspecialchars((string)$value[0], ENT_XML1 | ENT_QUOTES, 'UTF-8');
					}
					else {
						$this->arrayToXml($value, $child, $elementName);
					}
				}
			}
			elseif (is_scalar($value) || is_null($value)) {
				$child[0] = htmlspecialchars((string)$value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
			}
		}
	}

	public function toXml(): string
	{
		$data = $this->toArray();
		$rootTag = $this->_rootName ?: 'root';
		$xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $rootTag . '/>');

		// Атрибуты корня
		if (!empty($data['@attributes'])) {
			foreach ($data['@attributes'] as $name => $value) {
				if ($name !== '' && $value !== null) {
					$xml->addAttribute($name, (string)$value);
				}
			}
			unset($data['@attributes']);
		}
		if (!empty($data)) {
			$this->arrayToXml($data, $xml);
		}
		return $xml->asXML();
	}

	public function __toString(): string
	{
		return $this->toXml();
	}

	public function toPrettyXml(): string
	{
		$dom = new \DOMDocument();
		// Загружаем XML, подавляя предупреждения (если есть ошибки)
		$xml = $this->toXml();
		$dom->loadXML($xml, LIBXML_NOBLANKS);
		$dom->formatOutput = true;
		$dom->preserveWhiteSpace = false;
		return $dom->saveXML();
	}
}