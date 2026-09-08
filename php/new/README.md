# QRCode.php — Modern PHP (5.6+)

> **Zero-dependency**, single-file QR Code generator for PHP 5.6+.  
> Supports **PNG, SVG, WEBP, HTML, GIF, ASCII** output — with **rounded modules**, **finder pattern styling**, **logo**, **label**, **transparent background**, and **auto mode detection**.

> **Clean API** — no backward compatibility baggage. Fluent interface with concise method names.

---

## Quick Start

```php
require_once 'QRCode.php';

$qr = new QRCode('https://cyberzilla.github.io/qrcode', 'H', 2);

// Fluent API — configure once, render anywhere
$qr->size(600)
   ->colors('#000000', '#ffffff')
   ->moduleRadius(0.4)
   ->render('png', 'qrcode.png');

$qr->render('svg', 'qrcode.svg');
$qr->render('webp', 'qrcode.webp');

// Or return as string
$data = $qr->render('png');
$svg  = $qr->render('svg');
$uri  = $qr->render('datauri');
```

---

## Constructor

```php
$qr = new QRCode(
    string $data,               // Text/URL to encode
    string $ec = 'M',           // Error correction: 'L', 'M', 'Q', 'H'
    int    $quietZone = 2,      // Quiet zone (margin) in modules
    int    $minVer = 1,         // Minimum version (1-40)
    int    $maxVer = 40         // Maximum version (1-40)
);
```

| Parameter | Default | Description |
|-----------|---------|-------------|
| `$data` | *(required)* | Text, URL, or data to encode |
| `$ec` | `'M'` | Error correction level |
| `$quietZone` | `2` | Number of modules for quiet zone (border padding) |
| `$minVer` | `1` | Minimum QR version (1 = 21×21 modules) |
| `$maxVer` | `40` | Maximum QR version (40 = 177×177 modules) |

### Error Correction Levels

| Level | Recovery | Recommendation |
|-------|----------|----------------|
| `'L'` | ~7% | Maximum data capacity, no logo |
| `'M'` | ~15% | General balance |
| `'Q'` | ~25% | With text labels |
| `'H'` | ~30% | **Required for logo** — highest damage tolerance |

---

## Configuration (Fluent API)

All setters support **method chaining** and return `$this`.

### `size($size)`

```php
$qr->size(600);    // Output 600×600 px
```

### `colors($foreground, $background = '#ffffff')`

```php
$qr->colors('#1a1a2e', '#ffffff');      // Custom colors
$qr->colors('#1a1a2e', 'transparent');  // Transparent background
```

### `moduleRadius($ratio)`

```php
$qr->moduleRadius(0.0);   // Sharp corners (default)
$qr->moduleRadius(0.25);  // Slightly rounded
$qr->moduleRadius(0.5);   // Full circle
```

### `moduleShape($shape)`

```php
$qr->moduleShape('square');   // Default — square
$qr->moduleShape('dot');      // Separate circles
$qr->moduleShape('diamond');  // Diamond shape (45° rotation)
```

> **Note:** Finder patterns ("eyes") are not affected by shape — they use rounded rects via `finderStyle()`.

### `finderStyle($outerColor, $innerColor, $outerRadius, $innerRadius)`

```php
$qr->finderStyle('#e74c3c', '#3498db', 0.5, 0.5);
```

| Parameter | Description |
|-----------|-------------|
| `$outerColor` | Outer frame color (null = follow foreground) |
| `$innerColor` | Center dot color (null = follow foreground) |
| `$outerRadius` | Frame radius (0.0–0.5) |
| `$innerRadius` | Dot radius (0.0–0.5) |

### `logo($path, $ratio = 0.2, $padding = 6, $radius = 15)`

Embed a logo in the center of the QR code. Use EC Level `'H'`.

```php
$qr->logo('logo.png', 0.25, 10, 20);

$qr->logo(null);       // Clear logo
```

> **Note:** Logo and label are mutually exclusive. `logo()` automatically clears the label, and vice versa.

### `label($text, $size = 0.1, $color = '#000', $font = null, $fontFamily = '...', $strip = false)`

```php
$qr->label('SCAN ME', 0.08, '#333', null, 'Inter, Arial, sans-serif', true);

$qr->label(null);       // Clear label
```

| Parameter | Default | Description |
|-----------|---------|-------------|
| `$text` | — | Label text |
| `$size` | `0.1` | Font size ratio relative to QR size |
| `$color` | `'#000000'` | Text color |
| `$font` | `null` | Path to TTF font file (optional) |
| `$fontFamily` | `'Inter, Arial, sans-serif'` | CSS font-family for SVG |
| `$strip` | `false` | Full-width background strip |

### `quality($quality)`

```php
$qr->quality(80);      // WEBP quality 0-100 (default 85)
```

### `scalable($scalable = true)`

```php
$qr->scalable(true);   // SVG without width/height (responsive)
```

### `accessibility($title, $desc = null)`

```php
$qr->accessibility('QR Code', 'Link to GitHub');  // SVG <title> and <desc>
```

### `margin($margin)`

```php
$qr->margin(3);        // ASCII art margin (default 2)
```

---

## `render()` — Unified Output

```php
$qr->render(string $format = 'png', ?string $filename = null): string|bool
```

| Format | Return | GD? | Description |
|--------|--------|-----|-------------|
| `'png'` | Binary PNG | ✅ | Default format |
| `'svg'` | SVG string | ❌ | Vector, scalable |
| `'webp'` | Binary WEBP | ✅ | 25-34% smaller than PNG |
| `'gif'` | Binary GIF | ❌ | Pure PHP encoder |
| `'html'` | HTML table | ❌ | For email templates |
| `'ascii'` | ASCII art | ❌ | For terminal/CLI |
| `'datauri'` | PNG Data URI | ✅ | `data:image/png;base64,...` |
| `'base64'` | Base64 PNG | ✅ | Without `data:` prefix |
| `'imgtag'` | HTML `<img>` | ❌ | Inline GIF in `<img>` tag |

**Without `$filename`** → returns string. **With `$filename`** → saves to file, returns `true`.

---

## Examples

### QR with Logo

```php
$qr = new QRCode('https://cyberzilla.github.io/qrcode', 'H', 2);
$qr->size(600)
   ->colors('#000', '#fff')
   ->moduleRadius(0.4)
   ->logo('github-logo.png', 0.25, 10, 30)
   ->render('png', 'qr-github.png');
```

### Transparent Background

```php
$qr = new QRCode('https://cyberzilla.github.io/qrcode', 'M', 2);
$qr->size(400)
   ->colors('#000', 'transparent')
   ->render('png', 'transparent.png');
```

### Finder Styling + Dot Shape

```php
$qr = new QRCode('https://cyberzilla.github.io/qrcode', 'H', 2);
$qr->size(600)
   ->moduleShape('dot')
   ->finderStyle('#e74c3c', '#3498db', 0.5, 0.5)
   ->render('png', 'styled.png');
```

### Label with Strip

```php
$qr = new QRCode('https://cyberzilla.github.io/qrcode', 'Q', 2);
$qr->size(500)
   ->moduleRadius(0.3)
   ->label('SCAN ME', 0.08, '#333', null, 'Arial, sans-serif', true)
   ->render('png', 'label.png');
```

---

## Inspection Methods

```php
$qr->isDark($row, $col)       // bool — dark/light module
$qr->getModuleCount()         // int — total dimension (with quiet zone)
$qr->getRawModuleCount()      // int — dimension without quiet zone
$qr->matrix()                 // array — 2D boolean array
$qr->info()                   // array — metadata (version, mode, utilization, etc.)
```

---

## Requirements

- **PHP** ≥ 5.6
- **GD Extension** — only required for PNG and WEBP output. SVG, HTML, GIF, and ASCII **require no extensions**.
