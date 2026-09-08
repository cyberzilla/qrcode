# QRCode.php — PHP 8.1+ with Enums & Typed Properties

> **Zero-dependency**, single-file QR Code generator for PHP 8.1+.  
> Supports **PNG, SVG, WEBP, HTML, GIF, ASCII** output — with **rounded modules**, **finder pattern styling**, **logo**, **label**, **transparent background**, and **auto mode detection**.

> Uses **PHP 8.1 Enums**, **typed properties**, **named arguments**, and **`static` return types**.

---

## Quick Start

```php
require_once 'QRCode.php';

$qr = new QRCode('https://cyberzilla.github.io/qrcode', ErrorCorrection::High, quietZone: 2);

$qr->size(600)
   ->colors('#000000', '#ffffff')
   ->moduleShape(ModuleShape::Dot)
   ->finderStyle(outerColor: '#e74c3c', innerRadius: 0.5)
   ->render(OutputFormat::PNG, filename: 'qrcode.png');

// Same config → different format
$qr->render(OutputFormat::SVG, filename: 'qrcode.svg');
```

---

## Enums

### `ErrorCorrection`

```php
ErrorCorrection::Low       // 'L' — ~7% recovery
ErrorCorrection::Medium    // 'M' — ~15% recovery
ErrorCorrection::Quartile  // 'Q' — ~25% recovery
ErrorCorrection::High      // 'H' — ~30% recovery (required for logo)
```

### `ModuleShape`

```php
ModuleShape::Square    // Default — square
ModuleShape::Dot       // Separate circles
ModuleShape::Diamond   // Diamond shape (45° rotation)
```

### `OutputFormat`

```php
OutputFormat::PNG       // Raster PNG (requires GD)
OutputFormat::SVG       // Vector SVG
OutputFormat::WEBP      // Raster WEBP (requires GD)
OutputFormat::GIF       // Pure PHP GIF encoder
OutputFormat::HTML      // HTML table
OutputFormat::ASCII     // Terminal art
OutputFormat::DataURI   // data:image/png;base64,...
OutputFormat::Base64    // Base64 string
OutputFormat::ImgTag    // <img> tag with inline GIF
```

---

## Constructor

```php
$qr = new QRCode(
    string           $data,
    ErrorCorrection  $ec = ErrorCorrection::Medium,
    int              $quietZone = 2,
    int              $minVersion = 1,
    int              $maxVersion = 40,
);
```

| Parameter | Default | Description |
|-----------|---------|-------------|
| `$data` | *(required)* | Text, URL, or data to encode |
| `$ec` | `ErrorCorrection::Medium` | Error correction level |
| `$quietZone` | `2` | Number of modules for quiet zone |
| `$minVersion` | `1` | Minimum QR version (1–40) |
| `$maxVersion` | `40` | Maximum QR version (1–40) |

---

## Fluent API — Configuration

All setters support **method chaining** with `static` return type.

### `size(int $size): static`

```php
$qr->size(600);
```

### `colors(string $fg, string $bg = '#ffffff'): static`

```php
$qr->colors('#1a1a2e', '#ffffff');
$qr->colors('#1a1a2e', 'transparent');   // Transparent background
```

### `moduleRadius(float $ratio): static`

```php
$qr->moduleRadius(0.0);    // Sharp corners
$qr->moduleRadius(0.5);    // Full circle
```

### `moduleShape(ModuleShape $shape): static`

```php
$qr->moduleShape(ModuleShape::Dot);
$qr->moduleShape(ModuleShape::Diamond);
```

### `finderStyle(...): static`

```php
// Named arguments — PHP 8 style
$qr->finderStyle(
    outerColor:  '#e74c3c',
    innerColor:  '#3498db',
    outerRadius: 0.5,
    innerRadius: 0.5,
);
```

### `logo(?string $path, ...): static`

```php
$qr->logo('logo.png', ratio: 0.25, padding: 10, radius: 20);
$qr->logo(null);   // Clear
```

### `label(?string $text, ...): static`

```php
$qr->label('SCAN ME', size: 0.08, color: '#333', strip: true);
$qr->label(null);   // Clear
```

> **Note:** Logo and label are **mutually exclusive**.

### `quality(int $quality): static`

```php
$qr->quality(80);   // WEBP quality 0-100
```

### `scalable(bool $scalable = true): static`

```php
$qr->scalable(true);   // SVG responsive (without width/height)
```

### `accessibility(?string $title, ?string $desc = null): static`

```php
$qr->accessibility('QR Code', 'Link to GitHub');
```

### `margin(int $margin): static`

```php
$qr->margin(3);
```

---

## `render()` — Unified Output

```php
$qr->render(
    OutputFormat $format = OutputFormat::PNG,
    ?string      $filename = null,
): string|bool
```

| Format | Return | GD? | Description |
|--------|--------|-----|-------------|
| `OutputFormat::PNG` | Binary PNG | ✅ | Default |
| `OutputFormat::SVG` | SVG string | ❌ | Vector |
| `OutputFormat::WEBP` | Binary WEBP | ✅ | Compressed |
| `OutputFormat::GIF` | Binary GIF | ❌ | Pure PHP |
| `OutputFormat::HTML` | HTML table | ❌ | Email |
| `OutputFormat::ASCII` | ASCII art | ❌ | Terminal |
| `OutputFormat::DataURI` | Data URI | ✅ | Inline |
| `OutputFormat::Base64` | Base64 | ✅ | Raw |
| `OutputFormat::ImgTag` | `<img>` tag | ❌ | Inline |

---

## Examples

### QR with Logo (Named Arguments)

```php
$qr = new QRCode('https://cyberzilla.github.io/qrcode', ErrorCorrection::High, quietZone: 2);
$qr->size(600)
   ->colors('#000', '#fff')
   ->moduleRadius(0.4)
   ->logo('github-logo.png', ratio: 0.25, padding: 10, radius: 30)
   ->render(OutputFormat::PNG, filename: 'qr-github.png');
```

### Transparent Background

```php
$qr = new QRCode('https://cyberzilla.github.io/qrcode', ErrorCorrection::Medium);
$qr->size(400)
   ->colors('#000', 'transparent')
   ->render(OutputFormat::PNG, filename: 'transparent.png');
```

### Dot Shape + Custom Finders

```php
$qr = new QRCode('https://cyberzilla.github.io/qrcode', ErrorCorrection::High);
$qr->size(600)
   ->moduleShape(ModuleShape::Dot)
   ->finderStyle(outerColor: '#e74c3c', innerColor: '#3498db', outerRadius: 0.5, innerRadius: 0.5)
   ->render(OutputFormat::SVG, filename: 'styled.svg');
```

### Multi-format Output

```php
$qr = new QRCode('https://cyberzilla.github.io/qrcode', ErrorCorrection::High, quietZone: 2);
$qr->size(500)
   ->colors('#1a1a2e', '#fff')
   ->moduleRadius(0.35);

$qr->render(OutputFormat::PNG, filename: 'out.png');
$qr->render(OutputFormat::SVG, filename: 'out.svg');
$qr->render(OutputFormat::WEBP, filename: 'out.webp');

echo '<img src="' . $qr->render(OutputFormat::DataURI) . '">';
```

---

## Inspection Methods

```php
$qr->isDark(int $row, int $col): bool
$qr->getModuleCount(): int
$qr->getRawModuleCount(): int
$qr->matrix(): array
$qr->info(): array    // ['version', 'ecLevel', 'mode', 'modules', 'utilization', ...]
```

---

## Requirements

- **PHP** ≥ 8.1
- **GD Extension** — only required for PNG and WEBP output. SVG, HTML, GIF, and ASCII **require no extensions**.
