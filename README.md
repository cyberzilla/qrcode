# QRCode — Single-File QR Code Generator

> **Zero-dependency**, single-file QR Code generator for **PHP** and **JavaScript**.  
> Supports **PNG, SVG, WEBP, HTML, ASCII** output — with **rounded modules**, **finder pattern styling**, **logo**, **label**, **transparent background**, and **auto mode detection**.

---

## 📁 Project Structure

```
qrcode/
├── QRCode.js          # Pure JavaScript (vanilla, no dependencies)
├── index.html         # Demo UI — JavaScript version (standalone)
├── README.md
└── php/
    ├── legacy/        # PHP 7.4+ — old API (setSize, setColors, setLogo...)
    │   ├── QRCode.php
    │   ├── api.php
    │   └── index.php
    ├── new/           # PHP 5.6+ — modern fluent API (size, colors, logo...)
    │   ├── QRCode.php
    │   ├── api.php
    │   └── index.php
    └── php8/          # PHP 8.1+ — enums, typed properties, named arguments
        └── QRCode.php
```

### Version Comparison

| | `php/legacy/` | `php/new/` | `php/php8/` | `QRCode.js` |
|---|---|---|---|---|
| **PHP** | ≥ 7.4 | ≥ 5.6 | ≥ 8.1 | — |
| **Browser** | — | — | — | Modern (Canvas API) |
| **API Style** | `setSize()`, `setColors()` | `size()`, `colors()` | `size()`, `colors()` + enums | `size()`, `colors()` |
| **Enums** | ❌ | ❌ (string constants) | ✅ `ErrorCorrection`, `ModuleShape` | ❌ (static constants) |
| **GIF** | ✅ | ✅ | ✅ | ❌ |
| **Backward Compat** | ✅ Old methods | ❌ Clean API only | ❌ Clean API only | — |
| **Demo UI** | `index.php` + `api.php` | `index.php` + `api.php` | — | `index.html` |

---

## Quick Start

### JavaScript (Browser)

```html
<script src="QRCode.js"></script>
<script>
  const qr = new QRCode('https://github.com', 'H', 2);
  qr.size(400)
    .colors('#000000', '#ffffff')
    .moduleRadius(0.4);

  // Append to DOM
  document.body.appendChild(qr.render('canvas'));

  // Async rendering (required for logo)
  const canvas = await qr.renderAsync('canvas');

  // Download
  await qr.download('qrcode.png', 'png');
</script>
```

### PHP 5.6+ (`php/new/`)

```php
require_once 'QRCode.php';

$qr = new QRCode('https://github.com', 'H', 2);
$qr->size(600)
   ->colors('#000000', '#ffffff')
   ->moduleRadius(0.4)
   ->render('png', 'qrcode.png');

$qr->render('svg', 'qrcode.svg');   // Same config → SVG
$qr->render('webp', 'qrcode.webp'); // Same config → WEBP
```

### PHP 8.1+ (`php/php8/`)

```php
require_once 'QRCode.php';

$qr = new QRCode('https://github.com', ErrorCorrection::High, quietZone: 2);
$qr->size(600)
   ->colors('#000000', '#ffffff')
   ->moduleShape(ModuleShape::Dot)
   ->finderStyle(outerColor: '#e74c3c', innerRadius: 0.5)
   ->render(OutputFormat::PNG, filename: 'qrcode.png');
```

### PHP Legacy (`php/legacy/`)

```php
require_once 'QRCode.php';

$qr = new QRCode('https://github.com', 'H', 0, 1, 40);
$qr->setSize(600)
   ->setColors('#000000', '#ffffff')
   ->setModuleRadius(0.4)
   ->render('png', 'qrcode.png');
```

---

## Constructor

### PHP (`new` & `php8`)

```php
$qr = new QRCode(
    string $data,               // Text/URL to encode
    string $ec = 'M',           // Error correction: 'L', 'M', 'Q', 'H'
    int    $quietZone = 2,      // Quiet zone (margin) in modules
    int    $minVer = 1,         // Minimum version (1-40)
    int    $maxVer = 40         // Maximum version (1-40)
);
```

### JavaScript

```javascript
const qr = new QRCode(data, ec = 'M', quietZone = 2, minVer = 1, maxVer = 40);
```

| Parameter | Default | Description |
|-----------|---------|-------------|
| `data` | *(required)* | Text, URL, or data to encode |
| `ec` | `'M'` | Error correction level |
| `quietZone` | `2` | Number of modules for quiet zone (border padding) |
| `minVer` | `1` | Minimum QR version (1 = 21×21 modules) |
| `maxVer` | `40` | Maximum QR version (40 = 177×177 modules) |

### Error Correction Levels

| Level | Recovery | Recommendation |
|-------|----------|----------------|
| `'L'` | ~7% | Maximum data capacity, no logo |
| `'M'` | ~15% | General balance |
| `'Q'` | ~25% | With text labels |
| `'H'` | ~30% | **Required for logo** — highest damage tolerance |

### Auto Mode Detection

Encoding mode is automatically selected based on data content:

| Mode | Efficiency | Pattern |
|------|-----------|---------|
| **Numeric** | 3.3 bit/char | Digits `0-9` only |
| **Alphanumeric** | 5.5 bit/char | Digits, `A-Z` uppercase, `$%*+-./:` and space |
| **Byte** | 8 bit/char | All characters including UTF-8 |

---

## Fluent API — Configuration

All setters support **method chaining** and return `this` / `$this`.

> The API below applies to `php/new/`, `php/php8/`, and `QRCode.js`.  
> For `php/legacy/`, use the `set` prefix (e.g. `setSize()`, `setColors()`).

### `size(size)`

```javascript
qr.size(600);    // Output 600×600 px
```

### `colors(foreground, background)`

```javascript
qr.colors('#1a1a2e', '#ffffff');       // Custom colors
qr.colors('#1a1a2e', 'transparent');   // Transparent background (PNG/Canvas)
```

### `moduleRadius(ratio)`

```javascript
qr.moduleRadius(0.0);    // Sharp corners (default)
qr.moduleRadius(0.25);   // Slightly rounded
qr.moduleRadius(0.5);    // Full circle
```

### `moduleShape(shape)`

```javascript
qr.moduleShape('square');    // Default — square
qr.moduleShape('dot');       // Separate circles with gap
qr.moduleShape('diamond');   // Diamond shape (45° rotation)
```

> **Note:** Finder patterns ("eyes") are not affected by shape — they use rounded rects via `finderStyle()`.

### `finderStyle(outerColor, innerColor, outerRadius, innerRadius)`

```javascript
qr.finderStyle('#e74c3c', '#3498db', 0.5, 0.5);
```

| Parameter | Description |
|-----------|-------------|
| `outerColor` | Outer frame color (null = follow foreground) |
| `innerColor` | Center dot color (null = follow foreground) |
| `outerRadius` | Frame radius (0.0–0.5) |
| `innerRadius` | Dot radius (0.0–0.5) |

### `logo(path, ratio, padding, radius)`

Embed a logo in the center of the QR code. Use EC Level `'H'`.

```javascript
qr.logo('logo.png', 0.25, 10, 20);
// ratio: 25% of QR size
// padding: 10px around logo
// radius: 20% rounded corners
```

> **JS Note:** Logo rendering is async. Use `renderAsync()` or `download()`.  
> **Transparent PNG:** Logo alpha channel is preserved automatically.

### `label(text, size, color, fontFamily, strip)`

```javascript
qr.label('SCAN ME', 0.08, '#333', 'Arial, sans-serif', false);
```

| Parameter | Default | Description |
|-----------|---------|-------------|
| `text` | — | Label text |
| `size` | `0.1` | Font size ratio relative to QR size |
| `color` | `'#000'` | Text color |
| `fontFamily` | `'Inter, Arial, sans-serif'` | CSS font-family |
| `strip` | `false` | Full-width background strip |

> **Note:** Logo and label are **mutually exclusive**. Calling `logo()` automatically clears the label, and vice versa.

### `quality(quality)`

```javascript
qr.quality(80);   // WEBP quality 0-100 (default 85)
```

### `scalable(bool)`

```javascript
qr.scalable(true);   // SVG without width/height (responsive)
```

### `accessibility(title, desc)`

```javascript
qr.accessibility('QR Code', 'Link to GitHub');   // SVG <title> and <desc>
```

### `margin(margin)`

```javascript
qr.margin(3);   // ASCII art margin (default 2)
```

---

## Render Output

### PHP

```php
$qr->render(string $format = 'png', ?string $filename = null): string|bool
```

### JavaScript

```javascript
qr.render(format);            // Synchronous — returns string/Canvas
await qr.renderAsync(format); // Async — required for logo on canvas
await qr.download(filename, format); // Download file via Blob
```

### Format Table

| Format | PHP | JS | Return (JS) | GD? | Description |
|--------|-----|-----|-------------|-----|-------------|
| `'png'` | ✅ | ✅ | Data URI | ✅ | Default bitmap |
| `'svg'` | ✅ | ✅ | SVG string | ❌ | Vector, scalable |
| `'webp'` | ✅ | ✅ | Data URI | ✅ | Smaller than PNG |
| `'gif'` | ✅ | ❌ | — | ❌ | PHP-only, pure LZW encoder |
| `'html'` | ✅ | ✅ | HTML string | ❌ | For email templates |
| `'ascii'` | ✅ | ✅ | ASCII string | ❌ | For terminal/CLI |
| `'canvas'` | ❌ | ✅ | HTMLCanvasElement | — | JS-only, DOM element |
| `'datauri'` | ✅ | ✅ | `data:image/...` | ✅ | Inline base64 |
| `'base64'` | ✅ | ✅ | Base64 string | ✅ | Without `data:` prefix |
| `'imgtag'` | ✅ | ✅ | `<img>` tag | ❌ | Inline GIF/PNG |

---

## Examples

### Simple QR

```javascript
// JavaScript
const qr = new QRCode('https://example.com', 'M', 2);
document.body.appendChild(qr.size(400).render('canvas'));
```

```php
// PHP
$qr = new QRCode('https://example.com', 'M', 2);
$qr->size(400)->render('png', 'simple.png');
```

### Rounded QR with Logo

```javascript
// JavaScript (async required for logo)
const qr = new QRCode('https://github.com', 'H', 2);
qr.size(600)
  .colors('#000', '#fff')
  .moduleRadius(0.4)
  .logo('logo.png', 0.25, 10, 30);

const canvas = await qr.renderAsync('canvas');
document.body.appendChild(canvas);
```

```php
// PHP
$qr = new QRCode('https://github.com', 'H', 2);
$qr->size(600)
   ->colors('#000', '#fff')
   ->moduleRadius(0.4)
   ->logo('logo.png', 0.25, 10, 30)
   ->render('png', 'qr-github.png');
```

### Transparent Background

```javascript
// JavaScript
const qr = new QRCode('https://example.com', 'M', 2);
qr.size(400).colors('#000', 'transparent');
const canvas = await qr.renderAsync('canvas');
```

```php
// PHP
$qr = new QRCode('https://example.com', 'M', 2);
$qr->size(400)
   ->colors('#000', 'transparent')
   ->render('png', 'transparent.png');
```

### Finder Pattern Styling

```javascript
const qr = new QRCode('https://example.com', 'H', 2);
qr.size(600)
  .moduleRadius(0.4)
  .finderStyle('#e74c3c', '#3498db', 0.5, 0.5)
  .render('canvas');
```

### Label with Strip

```javascript
const qr = new QRCode('https://example.com', 'Q', 2);
qr.size(500)
  .moduleRadius(0.3)
  .label('SCAN ME', 0.08, '#333', 'Arial, sans-serif', true);

document.body.appendChild(qr.render('canvas'));
```

### Dot / Diamond Shape

```javascript
const qr = new QRCode('https://example.com', 'H', 2);
qr.size(500)
  .moduleShape('dot')
  .finderStyle('#e74c3c', '#3498db', 0.5, 0.5);

document.body.appendChild(qr.render('canvas'));
```

### Download (JS only)

```javascript
const qr = new QRCode('https://example.com', 'H', 2);
qr.size(600).colors('#1a1a2e', '#fff');

await qr.download('qrcode.png', 'png');
await qr.download('qrcode.svg', 'svg');
await qr.download('qrcode.webp', 'webp');
```

### Multi-format from Single Config (PHP)

```php
$qr = new QRCode('https://example.com', 'H', 2);
$qr->size(500)
   ->colors('#1a1a2e', '#ffffff')
   ->moduleRadius(0.35)
   ->finderStyle('#e74c3c', '#3498db');

$qr->render('png', 'output.png');
$qr->render('svg', 'output.svg');
$qr->render('webp', 'output.webp');

echo '<img src="' . $qr->render('datauri') . '">';
```

---

## Inspection Methods

```javascript
// JavaScript
qr.isDark(row, col)      // Boolean — dark/light module
qr.getModuleCount()      // Number — total dimension (with quiet zone)
qr.getRawModuleCount()   // Number — dimension without quiet zone
qr.matrix()              // 2D boolean array
qr.info()                // Object — metadata (version, mode, utilization, etc.)
```

```php
// PHP
$qr->isDark($row, $col)       // bool
$qr->getModuleCount()         // int
$qr->getRawModuleCount()      // int
$qr->matrix()                 // array
$qr->info()                   // array
```

---

## Feature Matrix

| Feature | Legacy PHP | New PHP | PHP 8.1+ | JavaScript |
|---------|-----------|---------|----------|------------|
| PNG | ✅ (GD) | ✅ (GD) | ✅ (GD) | ✅ (Canvas) |
| SVG | ✅ | ✅ | ✅ | ✅ |
| WEBP | ✅ (GD) | ✅ (GD) | ✅ (GD) | ✅ (Canvas) |
| GIF | ✅ | ✅ | ✅ | ❌ |
| HTML | ✅ | ✅ | ✅ | ✅ |
| ASCII | ✅ | ✅ | ✅ | ✅ |
| Transparent BG | ✅ | ✅ | ✅ | ✅ |
| Module Shapes | ✅ | ✅ | ✅ | ✅ |
| Module Radius | ✅ | ✅ | ✅ | ✅ |
| Finder Styling | ✅ | ✅ | ✅ | ✅ |
| Logo (raster) | ✅ | ✅ | ✅ | ✅ (async) |
| Logo (SVG) | ✅ | ✅ | ✅ | ✅ |
| Logo transparency | ✅ | ✅ | ✅ | ✅ |
| Label | ✅ | ✅ | ✅ | ✅ |
| Label strip | ✅ | ✅ | ✅ | ✅ |
| Custom TTF font | ✅ | ✅ | ✅ | ❌ (CSS fonts) |
| SVG scalable | ✅ | ✅ | ✅ | ✅ |
| SVG accessibility | ✅ | ✅ | ✅ | ✅ |
| Data URI | ✅ | ✅ | ✅ | ✅ |
| Download | — | — | — | ✅ (Blob) |
| Enums | ❌ | ❌ | ✅ | ❌ |
| Named args | ❌ | ❌ | ✅ | ❌ |

---

## Demo

| Demo | URL | Requirements |
|------|-----|--------------|
| **JavaScript** | `index.html` | Standalone, no server required |
| **PHP New** | `php/new/index.php` | PHP 5.6+ + GD |
| **PHP Legacy** | `php/legacy/index.php` | PHP 7.4+ + GD |

---

## Requirements

| Version | Requirement |
|---------|------------|
| `QRCode.js` | Modern browser with Canvas API |
| `php/new/` | PHP ≥ 5.6, ext-gd (for PNG/WEBP) |
| `php/php8/` | PHP ≥ 8.1, ext-gd (for PNG/WEBP) |
| `php/legacy/` | PHP ≥ 7.4, ext-gd (for PNG/WEBP) |

> **GD Extension** is only required for raster output (PNG, WEBP). SVG, HTML, GIF, and ASCII output **require no extensions**.

---

## License

MIT
