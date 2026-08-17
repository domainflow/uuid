# DomainFlow Uuid

[![Tests](https://github.com/domainflow/uuid/actions/workflows/tests.yml/badge.svg)](https://github.com/domainflow/uuid/actions/workflows/tests.yml)
![Packagist Version](https://img.shields.io/packagist/v/domainflow/uuid)
![PHP Version](https://img.shields.io/packagist/php-v/domainflow/uuid)
![License](https://img.shields.io/github/license/domainflow/uuid)
![PHPStan](https://img.shields.io/badge/PHPStan-Level%209-brightgreen.svg)

An immutable UUID library for PHP supporting RFC versions 1–8, including generation, validation, metadata inspection, and JSON serialization.

---

## ⚙️ Requirements

- **PHP 8.4 or 8.5**

---

## ✨ Highlights

- Supports all UUID versions (v1–v8)
- Immutable, `final readonly` value objects
- `Inspector` for version/variant/metadata extraction from arbitrary UUID strings

---

## 📦 Installation

```bash
composer require domainflow/uuid
```

---

## 🧪 Usage

### Generate UUIDs

```php
use DomainFlow\Uuid\{UuidV1, UuidV2, UuidV3, UuidV4, UuidV5, UuidV6, UuidV7, UuidV8};

UuidV1::generate();                     // Time-based UUID (v1)
UuidV2::generate(1001, 'uid');          // DCE Security UUID (v2)
UuidV3::generate($ns, 'my-name');       // Name-based UUID with MD5 (v3)
UuidV4::generate();                     // Random UUID (v4)
UuidV5::generate($ns, 'my-name');       // Name-based UUID with SHA-1 (v5)
UuidV6::generate();                     // Time-ordered UUID (v6)
UuidV7::generate();                     // Unix timestamp UUID (v7)
UuidV8::generate();                     // Application-defined/custom UUID (v8)
```

### Inspect a UUID

```php
use DomainFlow\Uuid\Inspector;

$inspector = Inspector::analyze('your-uuid-here');
$inspector->version();   // int (e.g. 4)
$inspector->variant();   // string (e.g. "RFC 4122")
$inspector->metadata();  // array with version-specific data
```

---

## Parameters

### `UuidV2::generate(int $localId, string $domain)`

- `$localId`: Typically a UID or GID (`getmyuid()`, `getmygid()`)
- `$domain`: Either `'uid'` or `'gid'`

### `UuidV3::generate(string $namespace, string $name)` / `UuidV5::generate(string $namespace, string $name)`

- `$namespace`: A valid UUID string (e.g. the DNS namespace `6ba7b810-9dad-11d1-80b4-00c04fd430c8`)
- `$name`: Any string (email, file path, identifier)

---

## Trait & Interface

All UUID classes implement:

- `UuidInterface` — common contract for all versions
- `UuidMethodsTrait` — shared implementation (`equals()`, `fromString()`, `fromJson()`, etc.)

---

## 🛡 License

**DomainFlow Uuid** is open-sourced software licensed under the [MIT license](LICENSE).
