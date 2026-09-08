# QRCode.php — PHP Legacy (7.4+)

> **Zero-dependency**, single-file QR Code generator for PHP 7.4+.  
> Supports **PNG, SVG, WEBP, HTML, GIF, ASCII** output — with **rounded modules**, **finder pattern styling**, **logo**, **label**, **transparent background**, and **auto mode detection**.

> **Backward Compatible** — all legacy methods (`toPNG()`, `toSVG()`, etc.) remain available alongside the new fluent API.

---

## Quick Start

```php
require_once 'QRCode.php';

$qr = new QRCode('https://github.com', 'H', 0, 1, 40);

// Fluent API — configure once, render anywhere
$qr->setSize(600)
   ->setColors('#000000', '#ffffff')
   ->setModuleRadius(0.4)
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
    string $text,               // Text/URL to encode
    string $ecLevel = 'L',      // Error correction: 'L', 'M', 'Q', 'H'
    int    $quiet   = 0,        // Quiet zone (margin) in modules
    int    $minVer  = 1,        // Minimum version (1-40)
    int    $maxVer  = 40        // Maximum version (1-40)
);
```

| Parameter | Default | Description |
|-----------|---------|-------------|
| `$text` | *(required)* | Text, URL, or data to encode |
| `$ecLevel` | `'L'` | Error correction level |
| `$quiet` | `0` | Number of modules for quiet zone (border padding) |
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

## Configuration (Fluent Setters)

All setters support **method chaining** and return `$this`.

### `setSize(int $size): self`

```php
$qr->setSize(600);    // Output 600×600 px
```

### `setColors(string $fg, string $bg = '#ffffff'): self`

```php
$qr->setColors('#1a1a2e', '#ffffff');      // Custom colors
$qr->setColors('#1a1a2e', 'transparent');  // Transparent background
```

### `setModuleRadius(float $ratio): self`

```php
$qr->setModuleRadius(0.0);   // Sharp corners (default)
$qr->setModuleRadius(0.25);  // Slightly rounded
$qr->setModuleRadius(0.5);   // Full circle
```

### `setModuleShape(string $shape): self`

```php
$qr->setModuleShape('square');   // Default — square
$qr->setModuleShape('dot');      // Separate circles
$qr->setModuleShape('diamond');  // Diamond shape (45° rotation)
```

### `setFinderStyle(array $style): self`

```php
$qr->setFinderStyle([
    'outerColor'  => '#e74c3c',  // Outer frame color
    'innerColor'  => '#3498db',  // Center dot color
    'outerRadius' => 0.5,        // Frame radius (0.0-0.5)
    'innerRadius' => 0.5,        // Dot radius (0.0-0.5)
]);
```

### `setLogo(?string $path, array $options = []): self`

```php
$qr->setLogo('logo.png', [
    'ratio'   => 0.25,    // 25% of QR size (default 0.2)
    'padding' => 10,      // Padding around logo in px (default 6)
    'radius'  => 20,      // Corner radius as % of logo size (default 15)
]);

$qr->setLogo(null);       // Clear logo
```

> **Note:** Logo and label are mutually exclusive. `setLogo()` automatically clears the label, and vice versa.

### `setLabel(?string $text, array $options = []): self`

```php
$qr->setLabel('SCAN ME', [
    'size'       => 0.08,                          // Font size ratio (default 0.1)
    'color'      => '#333333',                     // Text color (default '#000000')
    'font'       => '/path/to/arial.ttf',          // TTF font file (default: built-in)
    'fontFamily' => 'Inter, Arial, sans-serif',    // CSS font-family for SVG
    'strip'      => true,                          // Full-width strip (default false)
]);

$qr->setLabel(null);       // Clear label
```

### `setQuality(int $quality): self`

```php
$qr->setQuality(80);      // WEBP quality 0-100 (default 90)
```

### `setScalable(bool $scalable): self`

```php
$qr->setScalable(true);   // SVG without width/height (responsive)
```

### `setAccessibility(?string $title, ?string $desc): self`

```php
$qr->setAccessibility('QR Code', 'Link to GitHub');  // SVG <title> and <desc>
```

### `setMargin(int $margin): self`

```php
$qr->setMargin(3);        // ASCII art margin (default 2)
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
| `'output'` | void | ✅ | Send directly to browser |

**Without `$filename`** → returns string. **With `$filename`** → saves to file, returns `true`.

---

## Backward Compatible Methods

All legacy methods **still work** exactly as before:

```php
$qr->toPNG('output.png', 600, '#000', '#fff');
$qr->toPNGWithLogo('logo.png', 'out.png', 600, 0.2, '#000', '#fff', 6, 8);
$qr->toPNGWithLabel('SCAN ME', 'out.png', 600, 0.1, '#333', '#000', '#fff');
$qr->toSVG(600, 'black', 'white');
$qr->toSVGWithLogo('logo.png', 600);
$qr->toSVGWithLabel('TEXT', 600);
$qr->toWEBP('out.webp', 600);
$qr->toHTML(200);
$qr->toDataURI(200);
$qr->toImgTag(200);
$qr->toASCII(2);
$qr->outputPNG(400);
$qr->toBase64PNG(400);
$qr->toPNGDataURI(400);
$qr->toWEBPDataURI(400);
```

---

## Inspection Methods

```php
$qr->isDark(int $row, int $col): bool     // Check dark/light module
$qr->getModuleCount(): int                 // Total dimension (with quiet zone)
$qr->getRawModuleCount(): int              // Dimension without quiet zone
$qr->getMatrix(): array                    // 2D boolean array
$qr->getInfo(): array                      // Detailed metadata (version, mode, utilization, etc.)
```

---

## Requirements

- **PHP** ≥ 7.4
- **GD Extension** — only required for `*PNG*` and `*WEBP*` methods. SVG, HTML, GIF, and ASCII **require no extensions**.
