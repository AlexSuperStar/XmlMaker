# XmlMaker

[![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

A flexible PHP class for dynamic XML generation with support for attributes, repeating elements (`@items`), and pretty-printed output.

> Author: Alexey Starikov <alexsuperstar@mail.ru>  
> Inspired by [JsonMaker](https://github.com/AlexSuperStar/jsonMaker)

---

> 🇷🇺 Документация на русском | [🇬🇧 English version](#english)

---

## Русский

### ✨ Возможности

- Создание XML через объектный (`$xml->tag = 'value'`) или массивный (`$xml['tag'] = 'value'`) синтаксис
- Задание атрибутов через зарезервированный ключ `@attributes`
- Генерация повторяющихся элементов:
  - Через `parse()` с `@items`
  - Через метод `addItem()`
- Форматированный XML с отступами (`toPrettyXml()`)
- Полная поддержка UTF-8 (включая кириллицу)
- Автоматическое экранирование текста
- Без внешних зависимостей
- Совместимость с PHP 7.4+

### 📦 Установка

#### Вариант 1: Прямое подключение

Скачайте `XmlMaker.php` и подключите:

```php
require_once 'XmlMaker.php';
```

#### Вариант 2: Через Composer

```bash
composer require alexstar/xmlmaker
```

Затем в коде:

```php
require 'vendor/autoload.php';
use alexstar\XmlMaker;
```

### 🚀 Примеры использования

#### 1. Простой XML

```php
$xml = new \alexstar\XmlMaker('config');
$xml->host = 'localhost';
$xml->port = 5432;

echo $xml->toPrettyXml();
```

**Результат:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<config>
  <host>localhost</host>
  <port>5432</port>
</config>
```

#### 2. Атрибуты

```php
$xml->item->{"@attributes"}["id"] = "101";
$xml->item->name = "Товар";
```

→ `<item id="101"><name>Товар</name></item>`

Или через `parse()`:

```php
$xml->parse([
    'item' => [
        '@attributes' => ['id' => '101'],
        'name' => 'Товар'
    ]
]);
```

#### 3. Повторяющиеся элементы через `@items` (в `parse()`)

```php
$xml->parse([
    'ИнфПолФХЖ1' => [
        '@items' => [
            'ТекстИнф' => [
                ['@attributes' => ['Идентиф' => 'номер_акта',    'Значен' => 'ПРМ1123456-1']],
                ['@attributes' => ['Идентиф' => 'дата_акта',     'Значен' => '07.05.2025']],
                ['@attributes' => ['Идентиф' => 'номер_заказа',  'Значен' => 'ПРМЗАК-123']],
                ['@attributes' => ['Идентиф' => 'дата_заказа',   'Значен' => '03.05.2024']],
            ]
        ]
    ]
]);
```

**Результат:**
```xml
<ИнфПолФХЖ1>
  <ТекстИнф Идентиф="номер_акта" Значен="ПРМ1123456-1"/>
  <ТекстИнф Идентиф="дата_акта" Значен="07.05.2025"/>
  <ТекстИнф Идентиф="номер_заказа" Значен="ПРМЗАК-123"/>
  <ТекстИнф Идентиф="дата_заказа" Значен="03.05.2024"/>
</ИнфПолФХЖ1>
```

#### 4. Повторяющиеся элементы через `addItem()`

```php
$xml->ИнфПолФХЖ1
    ->addItem('ТекстИнф', ['@attributes' => ['Идентиф' => 'номер_акта', 'Значен' => 'ПРМ1123456-1']])
    ->addItem('ТекстИнф', ['@attributes' => ['Идентиф' => 'дата_акта',  'Значен' => '07.05.2025']]);
```

→ То же, что и выше. Метод поддерживает цепочку вызовов.

### ⚠️ Важно

- Зарезервированные ключи: `@attributes`, `@items`
- Все данные должны быть в кодировке **UTF-8**
- Пустые элементы с атрибутами выводятся как самозакрывающиеся теги
- Класс реализует `ArrayAccess`, `Countable`, `IteratorAggregate`

---

## English

> 🇬🇧 English documentation | [🇷🇺 Версия на русском](#русский)

### ✨ Features

- Build XML using object (`$xml->tag = 'value'`) or array (`$xml['tag'] = 'value'`) syntax
- Set XML attributes via reserved key `@attributes`
- Generate repeating elements:
  - Via `parse()` with `@items`
  - Via `addItem()` method (chainable)
- Pretty-printed XML output with indentation (`toPrettyXml()`)
- Full UTF-8 support (including Cyrillic)
- Automatic text escaping
- No external dependencies
- PHP 7.4+ compatible

### 📦 Installation

#### Option 1: Direct include

Download `XmlMaker.php` and include:

```php
require_once 'XmlMaker.php';
```

#### Option 2: Via Composer

```bash
composer require alexstar/xmlmaker
```

Then in your code:

```php
require 'vendor/autoload.php';
use alexstar\XmlMaker;
```

### 🚀 Usage Examples

#### 1. Basic XML

```php
$xml = new \alexstar\XmlMaker('config');
$xml->host = 'localhost';
$xml->port = 5432;

echo $xml->toPrettyXml();
```

**Output:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<config>
  <host>localhost</host>
  <port>5432</port>
</config>
```

#### 2. Attributes

```php
$xml->item->{"@attributes"}["id"] = "101";
$xml->item->name = "Product";
```

→ `<item id="101"><name>Product</name></item>`

Or via `parse()`:

```php
$xml->parse([
    'item' => [
        '@attributes' => ['id' => '101'],
        'name' => 'Product'
    ]
]);
```

#### 3. Repeating Elements with `@items` (in `parse()`)

```php
$xml->parse([
    'InfoBlock' => [
        '@items' => [
            'TextInfo' => [
                ['@attributes' => ['Ident' => 'doc_number', 'Value' => 'PRM1123456-1']],
                ['@attributes' => ['Ident' => 'doc_date',  'Value' => '07.05.2025']],
                ['@attributes' => ['Ident' => 'order_number', 'Value' => 'PRMZAK-123']],
                ['@attributes' => ['Ident' => 'order_date',  'Value' => '03.05.2024']],
            ]
        ]
    ]
]);
```

**Result:**
```xml
<InfoBlock>
  <TextInfo Ident="doc_number" Value="PRM1123456-1"/>
  <TextInfo Ident="doc_date" Value="07.05.2025"/>
  <TextInfo Ident="order_number" Value="PRMZAK-123"/>
  <TextInfo Ident="order_date" Value="03.05.2024"/>
</InfoBlock>
```

#### 4. Repeating Elements with `addItem()`

```php
$xml->InfoBlock
    ->addItem('TextInfo', ['@attributes' => ['Ident' => 'doc_number', 'Value' => 'PRM1123456-1']])
    ->addItem('TextInfo', ['@attributes' => ['Ident' => 'doc_date',  'Value' => '07.05.2025']]);
```

→ Same result. Chainable.

### ⚠️ Notes

- Reserved keys: `@attributes`, `@items`
- Input data must be in **UTF-8**
- Empty elements with attributes are rendered as self-closing tags
- Implements `ArrayAccess`, `Countable`, `IteratorAggregate`

---

### 📜 License

MIT License. See [LICENSE](LICENSE) for details.
