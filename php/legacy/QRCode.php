<?php
/**
 * QRCode.php - PHP QR Code Generator
 * Usage:
 *   $qr = new QRCode('Hello World', 'M', 0, 1, 40);
 *   $qr->toPNG('qrcode.png', 10, 4);
 *   $svg = $qr->toSVG(4, 16);
 *   $html = $qr->toHTML(4, 16);
 *   $matrix = $qr->getMatrix();
 */

class QRCode
{
    // =====================================================================
    // Constants
    // =====================================================================

    // QR Mode
    const MODE_NUMBER    = 1; // 1 << 0
    const MODE_ALPHA_NUM = 2; // 1 << 1
    const MODE_8BIT_BYTE = 4; // 1 << 2
    const MODE_KANJI     = 8; // 1 << 3

    // Error Correction Level (internal numeric values used in bit operations)
    const EC_L = 1;
    const EC_M = 0;
    const EC_Q = 3;
    const EC_H = 2;

    // Mask Patterns
    const PATTERN000 = 0;
    const PATTERN001 = 1;
    const PATTERN010 = 2;
    const PATTERN011 = 3;
    const PATTERN100 = 4;
    const PATTERN101 = 5;
    const PATTERN110 = 6;
    const PATTERN111 = 7;

    // =====================================================================
    // Static lookup tables
    // =====================================================================

    private static $PATTERN_POSITION_TABLE = [
        [],
        [6, 18],
        [6, 22],
        [6, 26],
        [6, 30],
        [6, 34],
        [6, 22, 38],
        [6, 24, 42],
        [6, 26, 46],
        [6, 28, 50],
        [6, 30, 54],
        [6, 32, 58],
        [6, 34, 62],
        [6, 26, 46, 66],
        [6, 26, 48, 70],
        [6, 26, 50, 74],
        [6, 30, 54, 78],
        [6, 30, 56, 82],
        [6, 30, 58, 86],
        [6, 34, 62, 90],
        [6, 28, 50, 72, 94],
        [6, 26, 50, 74, 98],
        [6, 30, 54, 78, 102],
        [6, 28, 54, 80, 106],
        [6, 32, 58, 84, 110],
        [6, 30, 58, 86, 114],
        [6, 34, 62, 90, 118],
        [6, 26, 50, 74, 98, 122],
        [6, 30, 54, 78, 102, 126],
        [6, 26, 52, 78, 104, 130],
        [6, 30, 56, 82, 108, 134],
        [6, 34, 60, 86, 112, 138],
        [6, 30, 58, 86, 114, 142],
        [6, 34, 62, 90, 118, 146],
        [6, 30, 54, 78, 102, 126, 150],
        [6, 24, 50, 76, 102, 128, 154],
        [6, 28, 54, 80, 106, 132, 158],
        [6, 32, 58, 84, 110, 136, 162],
        [6, 26, 54, 82, 110, 138, 166],
        [6, 30, 58, 86, 114, 142, 170],
    ];

    private static $RS_BLOCK_TABLE = [
        // L, M, Q, H for each version 1-40
        // 1
        [1, 26, 19],
        [1, 26, 16],
        [1, 26, 13],
        [1, 26, 9],
        // 2
        [1, 44, 34],
        [1, 44, 28],
        [1, 44, 22],
        [1, 44, 16],
        // 3
        [1, 70, 55],
        [1, 70, 44],
        [2, 35, 17],
        [2, 35, 13],
        // 4
        [1, 100, 80],
        [2, 50, 32],
        [2, 50, 24],
        [4, 25, 9],
        // 5
        [1, 134, 108],
        [2, 67, 43],
        [2, 33, 15, 2, 34, 16],
        [2, 33, 11, 2, 34, 12],
        // 6
        [2, 86, 68],
        [4, 43, 27],
        [4, 43, 19],
        [4, 43, 15],
        // 7
        [2, 98, 78],
        [4, 49, 31],
        [2, 32, 14, 4, 33, 15],
        [4, 39, 13, 1, 40, 14],
        // 8
        [2, 121, 97],
        [2, 60, 38, 2, 61, 39],
        [4, 40, 18, 2, 41, 19],
        [4, 40, 14, 2, 41, 15],
        // 9
        [2, 146, 116],
        [3, 58, 36, 2, 59, 37],
        [4, 36, 16, 4, 37, 17],
        [4, 36, 12, 4, 37, 13],
        // 10
        [2, 86, 68, 2, 87, 69],
        [4, 69, 43, 1, 70, 44],
        [6, 43, 19, 2, 44, 20],
        [6, 43, 15, 2, 44, 16],
        // 11
        [4, 101, 81],
        [1, 80, 50, 4, 81, 51],
        [4, 50, 22, 4, 51, 23],
        [3, 36, 12, 8, 37, 13],
        // 12
        [2, 116, 92, 2, 117, 93],
        [6, 58, 36, 2, 59, 37],
        [4, 46, 20, 6, 47, 21],
        [7, 42, 14, 4, 43, 15],
        // 13
        [4, 133, 107],
        [8, 59, 37, 1, 60, 38],
        [8, 44, 20, 4, 45, 21],
        [12, 33, 11, 4, 34, 12],
        // 14
        [3, 145, 115, 1, 146, 116],
        [4, 64, 40, 5, 65, 41],
        [11, 36, 16, 5, 37, 17],
        [11, 36, 12, 5, 37, 13],
        // 15
        [5, 109, 87, 1, 110, 88],
        [5, 65, 41, 5, 66, 42],
        [5, 54, 24, 7, 55, 25],
        [11, 36, 12, 7, 37, 13],
        // 16
        [5, 122, 98, 1, 123, 99],
        [7, 73, 45, 3, 74, 46],
        [15, 43, 19, 2, 44, 20],
        [3, 45, 15, 13, 46, 16],
        // 17
        [1, 135, 107, 5, 136, 108],
        [10, 74, 46, 1, 75, 47],
        [1, 50, 22, 15, 51, 23],
        [2, 42, 14, 17, 43, 15],
        // 18
        [5, 150, 120, 1, 151, 121],
        [9, 69, 43, 4, 70, 44],
        [17, 50, 22, 1, 51, 23],
        [2, 42, 14, 19, 43, 15],
        // 19
        [3, 141, 113, 4, 142, 114],
        [3, 70, 44, 11, 71, 45],
        [17, 47, 21, 4, 48, 22],
        [9, 39, 13, 16, 40, 14],
        // 20
        [3, 135, 107, 5, 136, 108],
        [3, 67, 41, 13, 68, 42],
        [15, 54, 24, 5, 55, 25],
        [15, 43, 15, 10, 44, 16],
        // 21
        [4, 144, 116, 4, 145, 117],
        [17, 68, 42],
        [17, 50, 22, 6, 51, 23],
        [19, 46, 16, 6, 47, 17],
        // 22
        [2, 139, 111, 7, 140, 112],
        [17, 74, 46],
        [7, 54, 24, 16, 55, 25],
        [34, 37, 13],
        // 23
        [4, 151, 121, 5, 152, 122],
        [4, 75, 47, 14, 76, 48],
        [11, 54, 24, 14, 55, 25],
        [16, 45, 15, 14, 46, 16],
        // 24
        [6, 147, 117, 4, 148, 118],
        [6, 73, 45, 14, 74, 46],
        [11, 54, 24, 16, 55, 25],
        [30, 46, 16, 2, 47, 17],
        // 25
        [8, 132, 106, 4, 133, 107],
        [8, 75, 47, 13, 76, 48],
        [7, 54, 24, 22, 55, 25],
        [22, 45, 15, 13, 46, 16],
        // 26
        [10, 142, 114, 2, 143, 115],
        [19, 74, 46, 4, 75, 47],
        [28, 50, 22, 6, 51, 23],
        [33, 46, 16, 4, 47, 17],
        // 27
        [8, 152, 122, 4, 153, 123],
        [22, 73, 45, 3, 74, 46],
        [8, 53, 23, 26, 54, 24],
        [12, 45, 15, 28, 46, 16],
        // 28
        [3, 147, 117, 10, 148, 118],
        [3, 73, 45, 23, 74, 46],
        [4, 54, 24, 31, 55, 25],
        [11, 45, 15, 31, 46, 16],
        // 29
        [7, 146, 116, 7, 147, 117],
        [21, 73, 45, 7, 74, 46],
        [1, 53, 23, 37, 54, 24],
        [19, 45, 15, 26, 46, 16],
        // 30
        [5, 145, 115, 10, 146, 116],
        [19, 75, 47, 10, 76, 48],
        [15, 54, 24, 25, 55, 25],
        [23, 45, 15, 25, 46, 16],
        // 31
        [13, 145, 115, 3, 146, 116],
        [2, 74, 46, 29, 75, 47],
        [42, 54, 24, 1, 55, 25],
        [23, 45, 15, 28, 46, 16],
        // 32
        [17, 145, 115],
        [10, 74, 46, 23, 75, 47],
        [10, 54, 24, 35, 55, 25],
        [19, 45, 15, 35, 46, 16],
        // 33
        [17, 145, 115, 1, 146, 116],
        [14, 74, 46, 21, 75, 47],
        [29, 54, 24, 19, 55, 25],
        [11, 45, 15, 46, 46, 16],
        // 34
        [13, 145, 115, 6, 146, 116],
        [14, 74, 46, 23, 75, 47],
        [44, 54, 24, 7, 55, 25],
        [59, 46, 16, 1, 47, 17],
        // 35
        [12, 151, 121, 7, 152, 122],
        [12, 75, 47, 26, 76, 48],
        [39, 54, 24, 14, 55, 25],
        [22, 45, 15, 41, 46, 16],
        // 36
        [6, 151, 121, 14, 152, 122],
        [6, 75, 47, 34, 76, 48],
        [46, 54, 24, 10, 55, 25],
        [2, 45, 15, 64, 46, 16],
        // 37
        [17, 152, 122, 4, 153, 123],
        [29, 74, 46, 14, 75, 47],
        [49, 54, 24, 10, 55, 25],
        [24, 45, 15, 46, 46, 16],
        // 38
        [4, 152, 122, 18, 153, 123],
        [13, 74, 46, 32, 75, 47],
        [48, 54, 24, 14, 55, 25],
        [42, 45, 15, 32, 46, 16],
        // 39
        [20, 147, 117, 4, 148, 118],
        [40, 75, 47, 7, 76, 48],
        [43, 54, 24, 22, 55, 25],
        [10, 45, 15, 67, 46, 16],
        // 40
        [19, 148, 118, 6, 149, 119],
        [18, 75, 47, 31, 76, 48],
        [34, 54, 24, 34, 55, 25],
        [20, 45, 15, 61, 46, 16],
    ];

    /** @var int[] GF(2^8) exponent table */
    private static $EXP_TABLE = null;

    /** @var int[] GF(2^8) logarithm table */
    private static $LOG_TABLE = null;

    // =====================================================================
    // Instance properties
    // =====================================================================

    private $typeNumber;
    private $errorCorrectionLevel;
    private $modules;
    private $moduleCount;
    private $dataCache;
    private $dataList = [];

    /** @var string Detected encoding mode ('Numeric', 'Alphanumeric', or 'Byte') */
    private $detectedMode = 'Byte';

    /** @var int Best mask pattern used */
    private $bestMaskPattern = 0;

    /** @var float Module corner radius ratio (0.0 = square, 0.5 = circle) */
    private $moduleRadius = 0.0;

    /** @var array|null Finder pattern styling options */
    private $finderStyle = null;

    /** @var string Module shape: 'square', 'dot', 'diamond' */
    private $moduleShape = 'square';

    // -- Render configuration (fluent API) --
    /** @var int Output size in pixels */
    private $renderSize = 200;
    /** @var string Foreground color */
    private $renderFg = '#000000';
    /** @var string Background color */
    private $renderBg = '#ffffff';
    /** @var int WEBP quality (0-100) */
    private $renderQuality = 90;
    /** @var bool SVG scalable (responsive) */
    private $renderScalable = false;
    /** @var string|null SVG accessibility title */
    private $renderTitle = null;
    /** @var string|null SVG accessibility description */
    private $renderDesc = null;
    /** @var int ASCII margin */
    private $renderMargin = 2;
    /** @var string|null Logo file path */
    private $logoPath = null;
    /** @var array Logo options */
    private $logoOptions = ['ratio' => 0.2, 'padding' => 6, 'radius' => 15];
    /** @var string|null Label text */
    private $labelText = null;
    /** @var array Label options */
    private $labelOptions = ['size' => 0.1, 'color' => '#000000', 'font' => null, 'fontFamily' => 'Inter, Arial, sans-serif', 'strip' => false];

    // =====================================================================
    // Constructor & Public API
    // =====================================================================

    /**
     * Create a QR Code.
     *
     * @param string $text    The text to encode.
     * @param string $ecLevel Error correction level: 'L', 'M', 'Q', or 'H'.
     * @param int    $quiet   Quiet zone modules (default 0).
     * @param int    $minVer  Minimum version (1-40, default 1).
     * @param int    $maxVer  Maximum version (1-40, default 40).
     */
    public function __construct(string $text, string $ecLevel = 'L', int $quiet = 0, int $minVer = 1, int $maxVer = 40)
    {
        self::initMathTables();

        $this->ecLevelChar = $ecLevel;
        $this->quiet = max(0, $quiet);
        $this->text = $text;

        $ecMap = ['L' => self::EC_L, 'M' => self::EC_M, 'Q' => self::EC_Q, 'H' => self::EC_H];
        if (!isset($ecMap[$ecLevel])) {
            throw new \InvalidArgumentException("Invalid error correction level: {$ecLevel}. Use L, M, Q, or H.");
        }
        $this->errorCorrectionLevel = $ecMap[$ecLevel];

        $minVer = max(1, $minVer);
        $maxVer = min(40, $maxVer);

        // Find minimum version that fits the data
        $success = false;
        for ($ver = $minVer; $ver <= $maxVer; $ver++) {
            try {
                $this->typeNumber = $ver;
                $this->dataList = [];
                $this->dataCache = null;
                $this->modules = null;
                $this->moduleCount = 0;

                $this->detectedMode = $this->detectMode($text);
                $this->addData($text, $this->detectedMode);
                $this->make();
                $success = true;
                break;
            } catch (\Exception $e) {
                // Try next version
            }
        }

        if (!$success) {
            throw new \RuntimeException("Text is too long to encode in QR code versions {$minVer}-{$maxVer} with EC level {$ecLevel}.");
        }
    }

    /**
     * Check if a module (pixel) is dark.
     *
     * @param int $row Row index (0-based, includes quiet zone).
     * @param int $col Column index (0-based, includes quiet zone).
     * @return bool
     */
    public function isDark(int $row, int $col): bool
    {
        $row -= $this->quiet;
        $col -= $this->quiet;

        if ($row < 0 || $row >= $this->moduleCount || $col < 0 || $col >= $this->moduleCount) {
            return false;
        }

        return (bool)$this->modules[$row][$col];
    }

    /**
     * Get total module count including quiet zone.
     *
     * @return int
     */
    public function getModuleCount(): int
    {
        return $this->moduleCount + 2 * $this->quiet;
    }

    /**
     * Get the raw module count (without quiet zone).
     *
     * @return int
     */
    public function getRawModuleCount(): int
    {
        return $this->moduleCount;
    }

    /**
     * Get the boolean matrix of the QR code (true = dark, false = light).
     * Includes quiet zone.
     *
     * @return bool[][]
     */
    public function getMatrix(): array
    {
        $total = $this->getModuleCount();
        $matrix = [];
        for ($row = 0; $row < $total; $row++) {
            $matrix[$row] = [];
            for ($col = 0; $col < $total; $col++) {
                $matrix[$row][$col] = $this->isDark($row, $col);
            }
        }
        return $matrix;
    }

    /**
     * Set the corner radius of each QR module (dot).
     * 0.0 = sharp square (default), 0.5 = fully round circle.
     *
     * @param float $ratio Radius ratio (0.0 to 0.5).
     * @return $this For method chaining.
     */
    public function setModuleRadius(float $ratio): self
    {
        $this->moduleRadius = max(0.0, min(0.5, $ratio));
        return $this;
    }

    /**
     * Set the shape of data modules.
     *
     * @param string $shape Module shape:
     *   - 'square'  — Default square modules (use setModuleRadius for rounding)
     *   - 'dot'     — Circular dots with gap between them
     *   - 'diamond' — 45° rotated squares (rhombus)
     * @return $this For method chaining.
     */
    public function setModuleShape(string $shape): self
    {
        $shape = strtolower(trim($shape));
        $valid = ['square', 'dot', 'diamond'];
        if (!in_array($shape, $valid)) {
            throw new \InvalidArgumentException("Invalid module shape: '{$shape}'. Use: " . implode(', ', $valid));
        }
        $this->moduleShape = $shape;
        return $this;
    }

    /**
     * Set custom styling for the 3 finder patterns ("eyes") in the QR code corners.
     * Allows separate colors and radius for the outer frame and inner dot.
     *
     * @param array $style Associative array with optional keys:
     *   - 'outerColor' (string): Hex color for the outer frame (default: foreground color)
     *   - 'innerColor' (string): Hex color for the inner dot (default: foreground color)
     *   - 'outerRadius' (float): Radius ratio for outer frame (0.0-0.5, default: moduleRadius)
     *   - 'innerRadius' (float): Radius ratio for inner dot (0.0-0.5, default: moduleRadius)
     * @return $this For method chaining.
     */
    public function setFinderStyle(array $style): self
    {
        $this->finderStyle = [
            'outerColor'  => $style['outerColor'] ?? null,
            'innerColor'  => $style['innerColor'] ?? null,
            'outerRadius' => isset($style['outerRadius']) ? max(0.0, min(0.5, (float)$style['outerRadius'])) : null,
            'innerRadius' => isset($style['innerRadius']) ? max(0.0, min(0.5, (float)$style['innerRadius'])) : null,
        ];
        return $this;
    }

    /**
     * Determine if a module (in raw QR coordinates, without quiet zone) belongs
     * to a finder pattern, and if so, whether it's part of the outer frame or inner dot.
     *
     * @param int $row Raw row (0-based, no quiet zone).
     * @param int $col Raw col (0-based, no quiet zone).
     * @return string|null 'outer', 'inner', or null if not a finder module.
     */
    private function getFinderRole(int $row, int $col): ?string
    {
        $mc = $this->moduleCount;
        // Three finder pattern top-left origins (row, col)
        $origins = [
            [0, 0],                  // Top-left
            [0, $mc - 7],            // Top-right
            [$mc - 7, 0],            // Bottom-left
        ];

        foreach ($origins as [$or, $oc]) {
            $lr = $row - $or;
            $lc = $col - $oc;
            if ($lr >= 0 && $lr <= 6 && $lc >= 0 && $lc <= 6) {
                // Inside this finder pattern's 7×7 area
                // Inner dot: rows 2-4, cols 2-4
                if ($lr >= 2 && $lr <= 4 && $lc >= 2 && $lc <= 4) {
                    return 'inner';
                }
                // Outer frame: row 0 or 6, or col 0 or 6, plus the white ring
                // (the white ring modules are also within 7×7 but are light)
                return 'outer';
            }
        }

        return null;
    }

    /**
     * Get detailed information about the generated QR code.
     *
     * @return array Associative array with version, EC level, encoding mode,
     *               module counts, data capacity usage, and mask pattern.
     */
    public function getInfo(): array
    {
        $rawCount = $this->getRawModuleCount();
        $totalModules = $rawCount * $rawCount;

        // Calculate data capacity
        $rsBlocks = $this->getRSBlocks($this->typeNumber, $this->errorCorrectionLevel);
        $totalDataCount = 0;
        for ($i = 0; $i < count($rsBlocks); $i++) {
            $totalDataCount += $rsBlocks[$i]['dataCount'];
        }

        // Calculate actual data bits used
        $buffer = $this->createBitBuffer();
        foreach ($this->dataList as $data) {
            $buffer['put']($data['getMode'](), 4);
            $buffer['put']($data['getLength'](), $this->getLengthInBits($data['getMode'](), $this->typeNumber));
            $data['write']($buffer);
        }
        $dataBitsUsed = $buffer['getLengthInBits']();

        return [
            'version'       => $this->typeNumber,
            'ecLevel'       => $this->ecLevelChar,
            'mode'          => $this->detectedMode,
            'moduleCount'   => $this->getModuleCount(),
            'rawModuleCount'=> $rawCount,
            'maskPattern'   => $this->bestMaskPattern,
            'dataCapacityBits'  => $totalDataCount * 8,
            'dataUsedBits'      => $dataBitsUsed,
            'utilization'       => round($dataBitsUsed / ($totalDataCount * 8), 4),
        ];
    }

    // =====================================================================
    // Fluent Configuration API
    // =====================================================================

    /**
     * Set output size in pixels.
     *
     * @param int $size Size in pixels (default 200).
     * @return $this For method chaining.
     */
    public function setSize(int $size): self
    {
        $this->renderSize = max(10, $size);
        return $this;
    }

    /**
     * Set foreground and background colors.
     *
     * @param string $foreground Foreground color hex (e.g. '#000000').
     * @param string $background Background color hex or 'transparent'.
     * @return $this For method chaining.
     */
    public function setColors(string $foreground, string $background = '#ffffff'): self
    {
        $this->renderFg = $foreground;
        $this->renderBg = $background;
        return $this;
    }

    /**
     * Set logo to embed in center of QR code. Pass null to remove logo.
     *
     * @param string|null $path    Path to image file (PNG/JPEG/GIF/WEBP/BMP), or null to clear.
     * @param array       $options Logo options:
     *   - 'ratio'   (float)  Logo size relative to QR (0.05-0.4, default 0.2)
     *   - 'padding' (int)    Padding around logo in px (0-20, default 6)
     *   - 'radius'  (int)    Corner radius as % of logo size (0-50, default 15)
     * @return $this For method chaining.
     */
    public function setLogo(?string $path, array $options = []): self
    {
        $this->logoPath = $path;
        if ($path !== null) {
            $this->labelText = null; // Logo and label are mutually exclusive
        }
        if (!empty($options)) {
            $this->logoOptions = array_merge($this->logoOptions, $options);
        }
        return $this;
    }

    /**
     * Set text label to embed in QR code. Pass null to remove label.
     *
     * @param string|null $text    Label text (e.g. 'SCAN ME'), or null to clear.
     * @param array       $options Label options:
     *   - 'size'       (float)   Font size ratio (0.05-0.3, default 0.1)
     *   - 'color'      (string)  Font color hex (default '#000000')
     *   - 'font'       (string)  Path to .ttf font file (null = built-in font)
     *   - 'fontFamily' (string)  CSS font-family for SVG (default 'Inter, Arial, sans-serif')
     *   - 'strip'      (bool)    Full-width strip mode (default false)
     * @return $this For method chaining.
     */
    public function setLabel(?string $text, array $options = []): self
    {
        $this->labelText = $text;
        if ($text !== null) {
            $this->logoPath = null; // Logo and label are mutually exclusive
        }
        if (!empty($options)) {
            $this->labelOptions = array_merge($this->labelOptions, $options);
        }
        return $this;
    }

    /**
     * Set WEBP output quality.
     *
     * @param int $quality Quality (0-100, default 90).
     * @return $this For method chaining.
     */
    public function setQuality(int $quality): self
    {
        $this->renderQuality = max(0, min(100, $quality));
        return $this;
    }

    /**
     * Set SVG scalable (responsive) mode.
     *
     * @param bool $scalable True to omit width/height attributes.
     * @return $this For method chaining.
     */
    public function setScalable(bool $scalable): self
    {
        $this->renderScalable = $scalable;
        return $this;
    }

    /**
     * Set SVG accessibility attributes.
     *
     * @param string|null $title Title for <title> element.
     * @param string|null $desc  Description for <desc> element.
     * @return $this For method chaining.
     */
    public function setAccessibility(?string $title, ?string $desc = null): self
    {
        $this->renderTitle = $title;
        $this->renderDesc = $desc;
        return $this;
    }

    /**
     * Set ASCII art margin.
     *
     * @param int $margin Margin in character units.
     * @return $this For method chaining.
     */
    public function setMargin(int $margin): self
    {
        $this->renderMargin = max(0, $margin);
        return $this;
    }

    // =====================================================================
    // Unified Render
    // =====================================================================

    /**
     * Render the QR code in any supported format.
     *
     * Uses the pre-configured state from fluent setters (setSize, setColors,
     * setModuleRadius, setFinderStyle, setLogo, setLabel, etc.).
     *
     * Supported formats:
     *   'png'          — PNG binary (requires GD)
     *   'svg'          — SVG markup string
     *   'webp'         — WEBP binary (requires GD + WEBP support)
     *   'gif'          — GIF binary (pure PHP, no GD)
     *   'html'         — HTML <table> string
     *   'ascii'        — ASCII art string
     *   'datauri'      — PNG data URI (data:image/png;base64,...)
     *   'datauri:png'  — Same as 'datauri'
     *   'datauri:svg'  — SVG data URI
     *   'datauri:webp' — WEBP data URI
     *   'datauri:gif'  — GIF data URI
     *   'base64'       — Base64-encoded PNG (no data: prefix)
     *   'base64:webp'  — Base64-encoded WEBP
     *   'imgtag'       — HTML <img> tag with embedded GIF
     *   'output'       — Send PNG directly to browser with headers
     *
     * @param string      $format   Output format (see above).
     * @param string|null $filename File path to save. Null = return string.
     * @return string|bool Output data, or true/void when saving to file or outputting.
     */
    public function render(string $format = 'png', ?string $filename = null)
    {
        $format = strtolower(trim($format));
        $size = $this->renderSize;
        $fg   = $this->renderFg;
        $bg   = $this->renderBg;

        switch ($format) {
            case 'png':
                if ($this->logoPath !== null) {
                    return $this->toPNGWithLogo(
                        $this->logoPath, $filename, $size,
                        $this->logoOptions['ratio'] ?? 0.2, $fg, $bg,
                        $this->logoOptions['padding'] ?? 6,
                        $this->_calcLogoRadiusPx()
                    );
                }
                if ($this->labelText !== null) {
                    return $this->toPNGWithLabel(
                        $this->labelText, $filename, $size,
                        $this->labelOptions['size'] ?? 0.1,
                        $this->labelOptions['color'] ?? '#000000', $fg, $bg,
                        $this->labelOptions['font'] ?? null,
                        $this->labelOptions['strip'] ?? false
                    );
                }
                return $this->toPNG($filename, $size, $fg, $bg);

            case 'svg':
                if ($this->logoPath !== null) {
                    return $this->toSVGWithLogo(
                        $this->logoPath, $size,
                        $this->logoOptions['ratio'] ?? 0.2, $fg, $bg,
                        $this->logoOptions['padding'] ?? 6,
                        $this->logoOptions['radius'] ?? 15,
                        $this->renderScalable
                    );
                }
                if ($this->labelText !== null) {
                    return $this->toSVGWithLabel(
                        $this->labelText, $size,
                        $this->labelOptions['size'] ?? 0.1,
                        $this->labelOptions['color'] ?? '#000000', $fg, $bg,
                        $this->labelOptions['fontFamily'] ?? 'Inter, Arial, sans-serif',
                        $this->labelOptions['strip'] ?? false,
                        $this->renderScalable
                    );
                }
                $svg = $this->toSVG($size, $fg, $bg, $this->renderScalable, $this->renderTitle, $this->renderDesc);
                if ($filename !== null) {
                    return (bool) file_put_contents($filename, $svg);
                }
                return $svg;

            case 'webp':
                return $this->toWEBP($filename, $size, $fg, $bg, $this->renderQuality);

            case 'gif':
                $data = $this->_renderGifBinary($size);
                if ($filename !== null) {
                    return (bool) file_put_contents($filename, $data);
                }
                return $data;

            case 'html':
                $html = $this->toHTML($size, $fg, $bg);
                if ($filename !== null) {
                    return (bool) file_put_contents($filename, $html);
                }
                return $html;

            case 'ascii':
                return $this->toASCII($this->renderMargin);

            case 'datauri':
            case 'datauri:png':
                return $this->toPNGDataURI($size, $fg, $bg);

            case 'datauri:svg':
                $svg = $this->render('svg');
                return 'data:image/svg+xml;base64,' . base64_encode($svg);

            case 'datauri:webp':
                return $this->toWEBPDataURI($size, $fg, $bg, $this->renderQuality);

            case 'datauri:gif':
                return $this->toDataURI($size);

            case 'base64':
            case 'base64:png':
                return $this->toBase64PNG($size, $fg, $bg);

            case 'base64:webp':
                $webp = $this->toWEBP(null, $size, $fg, $bg, $this->renderQuality);
                return base64_encode($webp);

            case 'imgtag':
                return $this->toImgTag($size, $this->renderTitle);

            case 'output':
                $this->outputPNG($size, $fg, $bg);
                return true;

            default:
                throw new \InvalidArgumentException("Unsupported render format: '{$format}'. Use: png, svg, webp, gif, html, ascii, datauri, base64, imgtag, output.");
        }
    }

    /**
     * Calculate logo radius in pixels from percentage.
     */
    private function _calcLogoRadiusPx(): int
    {
        $logoSizePx = (int)($this->renderSize * ($this->logoOptions['ratio'] ?? 0.2));
        return (int)round($logoSizePx * ($this->logoOptions['radius'] ?? 15) / 100);
    }

    /**
     * Generate raw GIF binary using the internal pure-PHP encoder.
     */
    private function _renderGifBinary(int $size): string
    {
        $total = $this->getModuleCount();
        $moduleSize = (int)floor($size / $total);
        $realSize = $moduleSize * $total;

        $data = [];
        for ($row = 0; $row < $total; $row++) {
            for ($y = 0; $y < $moduleSize; $y++) {
                for ($col = 0; $col < $total; $col++) {
                    $dark = $this->isDark($row, $col) ? 0 : 1;
                    for ($x = 0; $x < $moduleSize; $x++) {
                        $data[] = $dark;
                    }
                }
            }
        }

        return $this->createGifBinary($realSize, $realSize, $data);
    }

    /**
     * Draw all QR modules onto a GD image resource.
     * When moduleRadius > 0, uses the jquery-qrcode neighbor-aware rounded algorithm:
     * - Dark modules: round only corners where both adjacent neighbors are light
     * - Light modules: fill inner corners where all 3 surrounding neighbors are dark
     *
     * @param \GdImage|resource $img
     */
    private function drawModulesPNG($img, int $total, int $moduleSize, int $offset, int $fgColor, int $bgColor): void
    {
        $hasFinderStyle = ($this->finderStyle !== null);
        $shape = $this->moduleShape;

        // Fast path: plain square modules, no special styling
        if ($shape === 'square' && $this->moduleRadius <= 0 && !$hasFinderStyle) {
            for ($row = 0; $row < $total; $row++) {
                for ($col = 0; $col < $total; $col++) {
                    if ($this->isDark($row, $col)) {
                        $x = $offset + $col * $moduleSize;
                        $y = $offset + $row * $moduleSize;
                        imagefilledrectangle($img, $x, $y, $x + $moduleSize - 1, $y + $moduleSize - 1, $fgColor);
                    }
                }
            }
            return;
        }

        // Dot / Diamond shapes — each module drawn independently, no neighbor logic
        if ($shape === 'dot' || $shape === 'diamond') {
            $dotScale = 0.80; // 80% of module size → visible gap between dots
            $dotDiam = (int)round($moduleSize * $dotScale);
            $half = (int)round($moduleSize / 2);

            for ($row = 0; $row < $total; $row++) {
                for ($col = 0; $col < $total; $col++) {
                    // Skip finder modules when styled separately
                    if ($hasFinderStyle) {
                        $rawRow = $row - $this->quiet;
                        $rawCol = $col - $this->quiet;
                        if ($rawRow >= 0 && $rawCol >= 0 && $this->getFinderRole($rawRow, $rawCol) !== null) {
                            continue;
                        }
                    }

                    if (!$this->isDark($row, $col)) continue;

                    $cx = $offset + $col * $moduleSize + $half;
                    $cy = $offset + $row * $moduleSize + $half;

                    if ($shape === 'dot') {
                        imagefilledellipse($img, $cx, $cy, $dotDiam, $dotDiam, $fgColor);
                    } else { // diamond
                        $pts = [
                            $cx, $cy - $half,     // top
                            $cx + $half, $cy,     // right
                            $cx, $cy + $half,     // bottom
                            $cx - $half, $cy,     // left
                        ];
                        // PHP < 8.0 requires num_points param
                        if (PHP_VERSION_ID < 80000) {
                            imagefilledpolygon($img, $pts, 4, $fgColor);
                        } else {
                            imagefilledpolygon($img, $pts, $fgColor);
                        }
                    }
                }
            }

            if ($hasFinderStyle) {
                $this->drawFinderPatternsPNG($img, $moduleSize, $offset, $fgColor, $bgColor);
            }
            return;
        }

        // Square shape with radius / finder styling
        $defaultRad = ($this->moduleRadius > 0) ? max(1, (int)round($moduleSize * $this->moduleRadius)) : 0;

        for ($row = 0; $row < $total; $row++) {
            for ($col = 0; $col < $total; $col++) {
                // Skip finder pattern modules — drawn separately as smooth shapes
                if ($hasFinderStyle) {
                    $rawRow = $row - $this->quiet;
                    $rawCol = $col - $this->quiet;
                    if ($rawRow >= 0 && $rawCol >= 0 && $this->getFinderRole($rawRow, $rawCol) !== null) {
                        continue;
                    }
                }

                $l = $offset + $col * $moduleSize;
                $t = $offset + $row * $moduleSize;
                $r = $l + $moduleSize - 1;
                $b = $t + $moduleSize - 1;
                $curRad = $defaultRad;

                $d_center = $this->isDark($row, $col);
                $d_n = $this->isDark($row - 1, $col);
                $d_s = $this->isDark($row + 1, $col);
                $d_w = $this->isDark($row, $col - 1);
                $d_e = $this->isDark($row, $col + 1);
                $d_nw = $this->isDark($row - 1, $col - 1);
                $d_ne = $this->isDark($row - 1, $col + 1);
                $d_se = $this->isDark($row + 1, $col + 1);
                $d_sw = $this->isDark($row + 1, $col - 1);

                if ($d_center) {
                    imagefilledrectangle($img, $l, $t, $r, $b, $fgColor);

                    if ($curRad > 0) {
                        if (!$d_n && !$d_w) {
                            $this->clearCornerPNG($img, $l, $t, $curRad, 'nw', $fgColor, $bgColor);
                        }
                        if (!$d_n && !$d_e) {
                            $this->clearCornerPNG($img, $r, $t, $curRad, 'ne', $fgColor, $bgColor);
                        }
                        if (!$d_s && !$d_e) {
                            $this->clearCornerPNG($img, $r, $b, $curRad, 'se', $fgColor, $bgColor);
                        }
                        if (!$d_s && !$d_w) {
                            $this->clearCornerPNG($img, $l, $b, $curRad, 'sw', $fgColor, $bgColor);
                        }
                    }
                } else if ($curRad > 0) {
                    if ($d_n && $d_w && $d_nw) {
                        $this->fillInnerCornerPNG($img, $l, $t, $curRad, 'nw', $fgColor, $bgColor);
                    }
                    if ($d_n && $d_e && $d_ne) {
                        $this->fillInnerCornerPNG($img, $r, $t, $curRad, 'ne', $fgColor, $bgColor);
                    }
                    if ($d_s && $d_e && $d_se) {
                        $this->fillInnerCornerPNG($img, $r, $b, $curRad, 'se', $fgColor, $bgColor);
                    }
                    if ($d_s && $d_w && $d_sw) {
                        $this->fillInnerCornerPNG($img, $l, $b, $curRad, 'sw', $fgColor, $bgColor);
                    }
                }
            }
        }

        // Draw finder patterns as smooth layered rounded rectangles
        if ($hasFinderStyle) {
            $this->drawFinderPatternsPNG($img, $moduleSize, $offset, $fgColor, $bgColor);
        }
    }

    /**
     * Draw the 3 finder patterns as smooth, layered rounded rectangles.
     * Each pattern = outer 7×7 rect + background 5×5 gap + inner 3×3 dot.
     * Uses GD's imagefilledarc for silky smooth corner curves.
     */
    private function drawFinderPatternsPNG($img, int $moduleSize, int $offset, int $fgColor, int $bgColor): void
    {
        $outerColor = $fgColor;
        $innerColor = $fgColor;

        if ($this->finderStyle['outerColor'] !== null) {
            $rgb = $this->hexToRgb($this->finderStyle['outerColor']);
            $outerColor = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
        }
        if ($this->finderStyle['innerColor'] !== null) {
            $rgb = $this->hexToRgb($this->finderStyle['innerColor']);
            $innerColor = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
        }

        // Radius in pixels: ratio × full pattern dimension
        $outerRatio = $this->finderStyle['outerRadius'] ?? $this->moduleRadius;
        $innerRatio = $this->finderStyle['innerRadius'] ?? $this->moduleRadius;
        $outerRadPx = (int)round(7 * $moduleSize * $outerRatio);
        $gapRadPx   = (int)round(5 * $moduleSize * $outerRatio);
        $innerRadPx = (int)round(3 * $moduleSize * $innerRatio);

        // Three finder origins in the full grid (including quiet zone)
        $q = $this->quiet;
        $rawMC = $this->moduleCount;
        $origins = [
            [$q, $q],
            [$q, $q + $rawMC - 7],
            [$q + $rawMC - 7, $q],
        ];

        foreach ($origins as [$gr, $gc]) {
            // Outer 7×7 rounded rect
            $ox1 = $offset + $gc * $moduleSize;
            $oy1 = $offset + $gr * $moduleSize;
            $ox2 = $ox1 + 7 * $moduleSize - 1;
            $oy2 = $oy1 + 7 * $moduleSize - 1;
            $this->drawRoundedRectPNG($img, $ox1, $oy1, $ox2, $oy2, $outerRadPx, $outerColor);

            // Gap 5×5 (background color — creates the "ring" effect)
            $gx1 = $ox1 + $moduleSize;
            $gy1 = $oy1 + $moduleSize;
            $gx2 = $gx1 + 5 * $moduleSize - 1;
            $gy2 = $gy1 + 5 * $moduleSize - 1;
            $this->drawRoundedRectPNG($img, $gx1, $gy1, $gx2, $gy2, $gapRadPx, $bgColor);

            // Inner 3×3 dot
            $ix1 = $ox1 + 2 * $moduleSize;
            $iy1 = $oy1 + 2 * $moduleSize;
            $ix2 = $ix1 + 3 * $moduleSize - 1;
            $iy2 = $iy1 + 3 * $moduleSize - 1;
            $this->drawRoundedRectPNG($img, $ix1, $iy1, $ix2, $iy2, $innerRadPx, $innerColor);
        }
    }

    /**
     * Draw a filled rounded rectangle on a GD image using imagefilledarc
     * for perfectly smooth anti-aliased corner curves.
     */
    private function drawRoundedRectPNG($img, int $x1, int $y1, int $x2, int $y2, int $rad, int $color): void
    {
        if ($rad <= 0) {
            imagefilledrectangle($img, $x1, $y1, $x2, $y2, $color);
            return;
        }

        $w = $x2 - $x1;
        $h = $y2 - $y1;
        $rad = min($rad, (int)floor($w / 2), (int)floor($h / 2));
        if ($rad < 1) {
            imagefilledrectangle($img, $x1, $y1, $x2, $y2, $color);
            return;
        }

        // Two overlapping rectangles forming a cross (covers everything except corners)
        imagefilledrectangle($img, $x1 + $rad, $y1, $x2 - $rad, $y2, $color);
        imagefilledrectangle($img, $x1, $y1 + $rad, $x2, $y2 - $rad, $color);

        // Four quarter-circle arcs for smooth corners
        $d = $rad * 2;
        imagefilledarc($img, $x1 + $rad, $y1 + $rad, $d, $d, 180, 270, $color, IMG_ARC_PIE);
        imagefilledarc($img, $x2 - $rad, $y1 + $rad, $d, $d, 270, 360, $color, IMG_ARC_PIE);
        imagefilledarc($img, $x1 + $rad, $y2 - $rad, $d, $d, 90, 180, $color, IMG_ARC_PIE);
        imagefilledarc($img, $x2 - $rad, $y2 - $rad, $d, $d, 0, 90, $color, IMG_ARC_PIE);
    }

    /**
     * Clear (erase) a corner of a dark module to create a rounded effect.
     * Uses pixel-by-pixel circle distance check for precise convex rounding.
     * Clears pixels OUTSIDE the quarter circle to bgColor.
     */
    private function clearCornerPNG($img, int $cx, int $cy, int $rad, string $corner, int $fgColor, int $bgColor): void
    {
        $r2 = $rad * $rad;
        imagealphablending($img, false);
        for ($oy = 0; $oy < $rad; $oy++) {
            for ($ox = 0; $ox < $rad; $ox++) {
                // Distance from the circle center (inner corner of the area)
                $dx = $rad - $ox;
                $dy = $rad - $oy;
                if ($dx * $dx + $dy * $dy > $r2) {
                    switch ($corner) {
                        case 'nw': imagesetpixel($img, $cx + $ox, $cy + $oy, $bgColor); break;
                        case 'ne': imagesetpixel($img, $cx - $ox, $cy + $oy, $bgColor); break;
                        case 'se': imagesetpixel($img, $cx - $ox, $cy - $oy, $bgColor); break;
                        case 'sw': imagesetpixel($img, $cx + $ox, $cy - $oy, $bgColor); break;
                    }
                }
            }
        }
        imagealphablending($img, true);
    }

    /**
     * Fill an inner corner of a light module (where 3 dark neighbors meet).
     * Uses pixel-by-pixel circle distance check for precise concave shape.
     * Circle centered at far corner of rect (same as clearCornerPNG).
     */
    private function fillInnerCornerPNG($img, int $cx, int $cy, int $rad, string $corner, int $fgColor, int $bgColor): void
    {
        $r2 = $rad * $rad;
        for ($oy = 0; $oy < $rad; $oy++) {
            for ($ox = 0; $ox < $rad; $ox++) {
                $dx = $rad - $ox;
                $dy = $rad - $oy;
                if ($dx * $dx + $dy * $dy > $r2) {
                    switch ($corner) {
                        case 'nw': imagesetpixel($img, $cx + $ox, $cy + $oy, $fgColor); break;
                        case 'ne': imagesetpixel($img, $cx - $ox, $cy + $oy, $fgColor); break;
                        case 'se': imagesetpixel($img, $cx - $ox, $cy - $oy, $fgColor); break;
                        case 'sw': imagesetpixel($img, $cx + $ox, $cy - $oy, $fgColor); break;
                    }
                }
            }
        }
    }

    /**
     * Build SVG markup for all QR modules.
     * When moduleRadius > 0, uses the jquery-qrcode neighbor-aware rounded algorithm.
     * Translates Canvas moveTo/lineTo/arcTo calls into SVG path M/L/A commands.
     */
    private function buildSVGModules(int $total, float $moduleSize, float $offset, string $foreground): string
    {
        $fg = htmlspecialchars($foreground);
        $ms = round($moduleSize, 4);
        $hasFinderStyle = ($this->finderStyle !== null);
        $shape = $this->moduleShape;

        // Finder colors
        $finderOuterFg = ($hasFinderStyle && $this->finderStyle['outerColor'] !== null)
            ? htmlspecialchars($this->finderStyle['outerColor']) : $fg;
        $finderInnerFg = ($hasFinderStyle && $this->finderStyle['innerColor'] !== null)
            ? htmlspecialchars($this->finderStyle['innerColor']) : $fg;

        $defaultRad = round($moduleSize * $this->moduleRadius, 4);

        // Fast path: plain square modules, no special styling
        if ($shape === 'square' && $this->moduleRadius <= 0 && !$hasFinderStyle) {
            $rect = 'l' . $ms . ',0 0,' . $ms . ' -' . $ms . ',0 0,-' . $ms . 'z ';
            $svg = '<path d="';
            for ($r = 0; $r < $total; $r++) {
                $mr = round($r * $moduleSize + $offset, 4);
                for ($c = 0; $c < $total; $c++) {
                    if ($this->isDark($r, $c)) {
                        $mc = round($c * $moduleSize + $offset, 4);
                        $svg .= 'M' . $mc . ',' . $mr . $rect;
                    }
                }
            }
            $svg .= '" stroke="transparent" fill="' . $fg . '"/>';
            return $svg;
        }

        // Dot / Diamond shapes — independent modules
        if ($shape === 'dot' || $shape === 'diamond') {
            $d = '';
            $dotR = round($moduleSize * 0.40, 4); // radius = 40% of module = 80% diameter
            $halfMs = round($moduleSize / 2, 4);

            for ($row = 0; $row < $total; $row++) {
                for ($col = 0; $col < $total; $col++) {
                    if ($hasFinderStyle) {
                        $rawRow = $row - $this->quiet;
                        $rawCol = $col - $this->quiet;
                        if ($rawRow >= 0 && $rawCol >= 0 && $this->getFinderRole($rawRow, $rawCol) !== null) {
                            continue;
                        }
                    }

                    if (!$this->isDark($row, $col)) continue;

                    $cx = round($col * $moduleSize + $offset + $halfMs, 4);
                    $cy = round($row * $moduleSize + $offset + $halfMs, 4);

                    if ($shape === 'dot') {
                        // Circle: two half-arcs
                        $d2 = round($dotR * 2, 4);
                        $d .= 'M' . round($cx - $dotR, 4) . ',' . $cy;
                        $d .= 'a' . $dotR . ',' . $dotR . ' 0 1,0 ' . $d2 . ',0';
                        $d .= 'a' . $dotR . ',' . $dotR . ' 0 1,0 -' . $d2 . ',0Z ';
                    } else { // diamond
                        $d .= 'M' . $cx . ',' . round($cy - $halfMs, 4);
                        $d .= 'L' . round($cx + $halfMs, 4) . ',' . $cy;
                        $d .= 'L' . $cx . ',' . round($cy + $halfMs, 4);
                        $d .= 'L' . round($cx - $halfMs, 4) . ',' . $cy . 'Z ';
                    }
                }
            }

            $svg = '';
            if ($d !== '') {
                $svg .= '<path d="' . $d . '" fill="' . $fg . '"/>';
            }

            // Append finder pattern rects if styled
            if ($hasFinderStyle) {
                $svg .= $this->buildSVGFinderRects($total, $moduleSize, $offset, $fg, $finderOuterFg, $finderInnerFg);
            }
            return $svg;
        }

        // Build path data for data modules only (skip finder when styled)
        $d = '';

        for ($row = 0; $row < $total; $row++) {
            for ($col = 0; $col < $total; $col++) {
                // Skip finder modules — drawn as <rect> elements below
                if ($hasFinderStyle) {
                    $rawRow = $row - $this->quiet;
                    $rawCol = $col - $this->quiet;
                    if ($rawRow >= 0 && $rawCol >= 0 && $this->getFinderRole($rawRow, $rawCol) !== null) {
                        continue;
                    }
                }

                $l = round($col * $moduleSize + $offset, 4);
                $t = round($row * $moduleSize + $offset, 4);
                $r = round($l + $moduleSize, 4);
                $b = round($t + $moduleSize, 4);
                $rad = $defaultRad;

                $d_center = $this->isDark($row, $col);
                $d_n = $this->isDark($row - 1, $col);
                $d_s = $this->isDark($row + 1, $col);
                $d_w = $this->isDark($row, $col - 1);
                $d_e = $this->isDark($row, $col + 1);
                $d_nw = $this->isDark($row - 1, $col - 1);
                $d_ne = $this->isDark($row - 1, $col + 1);
                $d_se = $this->isDark($row + 1, $col + 1);
                $d_sw = $this->isDark($row + 1, $col - 1);

                if ($d_center) {
                    if ($rad <= 0) {
                        $d .= 'M' . $l . ',' . $t . 'l' . $ms . ',0 0,' . $ms . ' -' . $ms . ',0 0,-' . $ms . 'z ';
                    } else {
                        $nw = !$d_n && !$d_w;
                        $ne = !$d_n && !$d_e;
                        $se = !$d_s && !$d_e;
                        $sw = !$d_s && !$d_w;

                        $d .= $nw ? 'M' . ($l + $rad) . ',' . $t : 'M' . $l . ',' . $t;
                        $d .= $ne ? 'L' . ($r - $rad) . ',' . $t . 'A' . $rad . ',' . $rad . ' 0 0 1 ' . $r . ',' . ($t + $rad) : 'L' . $r . ',' . $t;
                        $d .= $se ? 'L' . $r . ',' . ($b - $rad) . 'A' . $rad . ',' . $rad . ' 0 0 1 ' . ($r - $rad) . ',' . $b : 'L' . $r . ',' . $b;
                        $d .= $sw ? 'L' . ($l + $rad) . ',' . $b . 'A' . $rad . ',' . $rad . ' 0 0 1 ' . $l . ',' . ($b - $rad) : 'L' . $l . ',' . $b;
                        $d .= $nw ? 'L' . $l . ',' . ($t + $rad) . 'A' . $rad . ',' . $rad . ' 0 0 1 ' . ($l + $rad) . ',' . $t : 'L' . $l . ',' . $t;
                        $d .= 'Z ';
                    }
                } elseif ($rad > 0) {
                    if ($d_n && $d_w && $d_nw) {
                        $d .= 'M' . $l . ',' . $t . 'L' . ($l + $rad) . ',' . $t . 'A' . $rad . ',' . $rad . ' 0 0 0 ' . $l . ',' . ($t + $rad) . 'Z ';
                    }
                    if ($d_n && $d_e && $d_ne) {
                        $d .= 'M' . $r . ',' . $t . 'L' . $r . ',' . ($t + $rad) . 'A' . $rad . ',' . $rad . ' 0 0 0 ' . ($r - $rad) . ',' . $t . 'Z ';
                    }
                    if ($d_s && $d_e && $d_se) {
                        $d .= 'M' . $r . ',' . $b . 'L' . ($r - $rad) . ',' . $b . 'A' . $rad . ',' . $rad . ' 0 0 0 ' . $r . ',' . ($b - $rad) . 'Z ';
                    }
                    if ($d_s && $d_w && $d_sw) {
                        $d .= 'M' . $l . ',' . $b . 'L' . $l . ',' . ($b - $rad) . 'A' . $rad . ',' . $rad . ' 0 0 0 ' . ($l + $rad) . ',' . $b . 'Z ';
                    }
                }
            }
        }

        // Data modules path
        $svg = '';
        if ($d !== '') {
            $svg .= '<path d="' . $d . '" fill="' . $fg . '"/>';
        }

        // Finder patterns as smooth <rect> elements
        if ($hasFinderStyle) {
            $svg .= $this->buildSVGFinderRects($total, $moduleSize, $offset, $fg, $finderOuterFg, $finderInnerFg);
        }

        return $svg;
    }

    /**
     * Build SVG <rect> elements for the 3 finder patterns with smooth rounded corners.
     */
    private function buildSVGFinderRects(int $total, float $moduleSize, float $offset, string $fg, string $outerFg, string $innerFg): string
    {
        $outerRatio = $this->finderStyle['outerRadius'] ?? $this->moduleRadius;
        $innerRatio = $this->finderStyle['innerRadius'] ?? $this->moduleRadius;

        $q = $this->quiet;
        $rawMC = $this->moduleCount;
        $origins = [
            [$q, $q],
            [$q, $q + $rawMC - 7],
            [$q + $rawMC - 7, $q],
        ];

        $outerRx = round(7 * $moduleSize * $outerRatio, 4);
        $gapRx   = round(5 * $moduleSize * $outerRatio, 4);
        $innerRx = round(3 * $moduleSize * $innerRatio, 4);

        $bgFill = htmlspecialchars($this->renderBg);
        $svg = '';

        foreach ($origins as [$gr, $gc]) {
            $ox = round($gc * $moduleSize + $offset, 4);
            $oy = round($gr * $moduleSize + $offset, 4);
            $outerW = round(7 * $moduleSize, 4);
            $gapW   = round(5 * $moduleSize, 4);
            $innerW = round(3 * $moduleSize, 4);

            $gx = round($ox + $moduleSize, 4);
            $gy = round($oy + $moduleSize, 4);
            $ix = round($ox + 2 * $moduleSize, 4);
            $iy = round($oy + 2 * $moduleSize, 4);

            // Outer 7×7
            $svg .= '<rect x="' . $ox . '" y="' . $oy . '" width="' . $outerW . '" height="' . $outerW . '"';
            if ($outerRx > 0) $svg .= ' rx="' . $outerRx . '" ry="' . $outerRx . '"';
            $svg .= ' fill="' . $outerFg . '"/>';

            // Gap 5×5
            $svg .= '<rect x="' . $gx . '" y="' . $gy . '" width="' . $gapW . '" height="' . $gapW . '"';
            if ($gapRx > 0) $svg .= ' rx="' . $gapRx . '" ry="' . $gapRx . '"';
            $svg .= ' fill="' . $bgFill . '"/>';

            // Inner 3×3 dot
            $svg .= '<rect x="' . $ix . '" y="' . $iy . '" width="' . $innerW . '" height="' . $innerW . '"';
            if ($innerRx > 0) $svg .= ' rx="' . $innerRx . '" ry="' . $innerRx . '"';
            $svg .= ' fill="' . $innerFg . '"/>';
        }

        return $svg;
    }

    // =====================================================================
    // Output Methods
    // =====================================================================

    /**
     * Generate PNG image and return binary data or save to file.
     * Requires GD extension.
     *
     * @param string|null $filename   File path to save. Null to return binary string.
     * @param int         $size       Total output size in pixels (default 200).
     * @param string      $foreground Foreground color hex (e.g., '#000000').
     * @param string      $background Background color hex (e.g., '#ffffff').
     * @return string|bool PNG binary data if $filename is null, true on file save success.
     */
    public function toPNG(?string $filename = null, int $size = 200, string $foreground = '#000000', string $background = '#ffffff')
    {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('GD extension is required for PNG output.');
        }

        $total = $this->getModuleCount();

        // Supersampling: render at 4x when rounded for smooth anti-aliased curves
        $hasFinderRadius = ($this->finderStyle !== null && (
            ($this->finderStyle['outerRadius'] ?? 0) > 0 ||
            ($this->finderStyle['innerRadius'] ?? 0) > 0
        ));
        $needsSmooth = $this->moduleShape !== 'square';
        $scale = ($this->moduleRadius > 0 || $hasFinderRadius || $needsSmooth) ? 4 : 1;
        $rSize = $size * $scale;
        $rModuleSize = (int)floor($rSize / $total);
        $rOffset = (int)floor(($rSize - $rModuleSize * $total) / 2);

        $img = imagecreatetruecolor($rSize, $rSize);

        $isTransparent = ($background === 'transparent');
        $fgRGB = $this->hexToRgb($foreground);
        $fgColor = imagecolorallocate($img, $fgRGB[0], $fgRGB[1], $fgRGB[2]);

        if ($isTransparent) {
            imagealphablending($img, false);
            imagesavealpha($img, true);
            $bgColor = imagecolorallocatealpha($img, 0, 0, 0, 127);
            imagefilledrectangle($img, 0, 0, $rSize - 1, $rSize - 1, $bgColor);
            imagealphablending($img, true);
        } else {
            $bgRGB = $this->hexToRgb($background);
            $bgColor = imagecolorallocate($img, $bgRGB[0], $bgRGB[1], $bgRGB[2]);
            imagefilledrectangle($img, 0, 0, $rSize - 1, $rSize - 1, $bgColor);
        }
        $this->drawModulesPNG($img, $total, $rModuleSize, $rOffset, $fgColor, $bgColor);

        // Downsample for anti-aliasing
        if ($scale > 1) {
            $final = imagecreatetruecolor($size, $size);
            if ($isTransparent) {
                imagealphablending($final, false);
                imagesavealpha($final, true);
            }
            imagecopyresampled($final, $img, 0, 0, 0, 0, $size, $size, $rSize, $rSize);
            imagedestroy($img);
            $img = $final;
        }

        if ($filename !== null) {
            $result = imagepng($img, $filename);
            imagedestroy($img);
            return $result;
        }

        ob_start();
        imagepng($img);
        imagedestroy($img);
        return ob_get_clean();
    }

    /**
     * Output PNG directly to browser with appropriate headers.
     *
     * @param int    $size       Total output size in pixels.
     * @param string $foreground Foreground color hex.
     * @param string $background Background color hex.
     */
    public function outputPNG(int $size = 200, string $foreground = '#000000', string $background = '#ffffff'): void
    {
        header('Content-Type: image/png');
        echo $this->toPNG(null, $size, $foreground, $background);
    }

    /**
     * Generate PNG with a logo/image embedded in the center.
     * Requires GD extension. Use with EC level 'H' for best results.
     *
     * @param string      $logoPath    Path to logo image (PNG, JPG, GIF, WEBP).
     * @param string|null $filename    File path to save. Null to return binary string.
     * @param int         $size        Total output size in pixels (default 200).
     * @param float       $logoRatio   Logo size as ratio of QR code (0.0-0.4, default 0.2).
     * @param string      $foreground  Foreground color hex.
     * @param string      $background  Background color hex.
     * @param int         $logoPadding Padding around logo in pixels.
     * @param int         $logoRadius  Border radius of logo background in pixels.
     * @return string|bool PNG binary data if $filename is null, true on file save success.
     */
    public function toPNGWithLogo(
        string $logoPath,
        ?string $filename = null,
        int $size = 200,
        float $logoRatio = 0.2,
        string $foreground = '#000000',
        string $background = '#ffffff',
        int $logoPadding = 6,
        int $logoRadius = 8
    ) {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('GD extension is required for PNG output.');
        }
        if (!file_exists($logoPath)) {
            throw new \RuntimeException("Logo file not found: {$logoPath}");
        }

        $total = $this->getModuleCount();

        // Supersampling for smooth rounded modules and finder patterns
        $hasFinderRadius = ($this->finderStyle !== null && (
            ($this->finderStyle['outerRadius'] ?? 0) > 0 ||
            ($this->finderStyle['innerRadius'] ?? 0) > 0
        ));
        $needsSmooth = $this->moduleShape !== 'square';
        $scale = ($this->moduleRadius > 0 || $hasFinderRadius || $needsSmooth) ? 4 : 1;
        $rSize = $size * $scale;
        $rModuleSize = (int)floor($rSize / $total);
        $rOffset = (int)floor(($rSize - $rModuleSize * $total) / 2);
        $rPad = $logoPadding * $scale;
        $rRad = $logoRadius * $scale;

        $img = imagecreatetruecolor($rSize, $rSize);

        $isTransparent = ($background === 'transparent');
        $fgRGB = $this->hexToRgb($foreground);
        $fgColor = imagecolorallocate($img, $fgRGB[0], $fgRGB[1], $fgRGB[2]);

        if ($isTransparent) {
            imagealphablending($img, false);
            imagesavealpha($img, true);
            $bgColor = imagecolorallocatealpha($img, 0, 0, 0, 127);
            imagefilledrectangle($img, 0, 0, $rSize - 1, $rSize - 1, $bgColor);
            imagealphablending($img, true);
            $logoBgClr = imagecolorallocate($img, 255, 255, 255);
        } else {
            $bgRGB = $this->hexToRgb($background);
            $bgColor = imagecolorallocate($img, $bgRGB[0], $bgRGB[1], $bgRGB[2]);
            imagefilledrectangle($img, 0, 0, $rSize - 1, $rSize - 1, $bgColor);
            $logoBgClr = $bgColor;
        }
        $this->drawModulesPNG($img, $total, $rModuleSize, $rOffset, $fgColor, $bgColor);

        // Load logo
        $logoImg = $this->loadImage($logoPath);
        if ($logoImg) {
            $logoRatio = max(0.05, min(0.4, $logoRatio));
            $logoSize = (int)($rSize * $logoRatio);

            $logoBgSize = $logoSize + $rPad * 2;
            $logoBgX = (int)(($rSize - $logoBgSize) / 2);
            $logoBgY = (int)(($rSize - $logoBgSize) / 2);

            // Draw white background behind logo with optional rounded corners
            if ($logoRadius > 0) {
                $this->imageFilledRoundedRect($img, $logoBgX, $logoBgY,
                    $logoBgX + $logoBgSize, $logoBgY + $logoBgSize,
                    $rRad, $logoBgClr);
            } else {
                imagefilledrectangle($img, $logoBgX, $logoBgY,
                    $logoBgX + $logoBgSize - 1, $logoBgY + $logoBgSize - 1, $logoBgClr);
            }

            // Draw logo centered, clipped to rounded corners if needed
            $logoX = $logoBgX + $rPad;
            $logoY = $logoBgY + $rPad;
            $origW = imagesx($logoImg);
            $origH = imagesy($logoImg);

            if ($logoRadius > 0 && $rRad > 0) {
                // Clip logo image to rounded corners using the same
                // imageFilledRoundedRect as the background for consistent rounding.
                $clipRad = min($rRad, (int)($logoSize / 2));

                // 1. Create rounded rect mask
                $mask = imagecreatetruecolor($logoSize, $logoSize);
                $maskBlack = imagecolorallocate($mask, 0, 0, 0);
                $maskWhite = imagecolorallocate($mask, 255, 255, 255);
                imagefilledrectangle($mask, 0, 0, $logoSize - 1, $logoSize - 1, $maskBlack);
                $this->imageFilledRoundedRect($mask, 0, 0, $logoSize - 1, $logoSize - 1, $clipRad, $maskWhite);

                // 2. Resize logo into temp canvas, preserving alpha/transparency
                $logoResized = imagecreatetruecolor($logoSize, $logoSize);
                imagealphablending($logoResized, false);
                imagesavealpha($logoResized, true);
                $transResized = imagecolorallocatealpha($logoResized, 0, 0, 0, 127);
                imagefilledrectangle($logoResized, 0, 0, $logoSize - 1, $logoSize - 1, $transResized);
                imagealphablending($logoResized, true);
                imagecopyresampled($logoResized, $logoImg, 0, 0, 0, 0,
                    $logoSize, $logoSize, $origW, $origH);

                // 3. Build output: transparent base, logo pixels only inside mask
                $logoTmp = imagecreatetruecolor($logoSize, $logoSize);
                imagealphablending($logoTmp, false);
                imagesavealpha($logoTmp, true);
                $trans = imagecolorallocatealpha($logoTmp, 0, 0, 0, 127);
                imagefilledrectangle($logoTmp, 0, 0, $logoSize - 1, $logoSize - 1, $trans);

                for ($py = 0; $py < $logoSize; $py++) {
                    for ($px = 0; $px < $logoSize; $px++) {
                        if ((imagecolorat($mask, $px, $py) & 0xFF) > 0) {
                            // Read full RGBA color (imagecolorat returns alpha in bits 24-30)
                            $rgba = imagecolorat($logoResized, $px, $py);
                            $a = ($rgba >> 24) & 0x7F;
                            $r = ($rgba >> 16) & 0xFF;
                            $g = ($rgba >> 8) & 0xFF;
                            $b = $rgba & 0xFF;
                            $pixelColor = imagecolorallocatealpha($logoTmp, $r, $g, $b, $a);
                            imagesetpixel($logoTmp, $px, $py, $pixelColor);
                        }
                    }
                }

                imagedestroy($mask);
                imagedestroy($logoResized);

                // 4. Paste rounded logo onto main image
                imagealphablending($img, true);
                imagecopy($img, $logoTmp, $logoX, $logoY, 0, 0, $logoSize, $logoSize);
                imagedestroy($logoTmp);
            } else {
                imagecopyresampled($img, $logoImg, $logoX, $logoY, 0, 0,
                    $logoSize, $logoSize, $origW, $origH);
            }
            imagedestroy($logoImg);
        }

        // Downsample for anti-aliasing
        if ($scale > 1) {
            $final = imagecreatetruecolor($size, $size);
            if ($isTransparent) {
                imagealphablending($final, false);
                imagesavealpha($final, true);
            }
            imagecopyresampled($final, $img, 0, 0, 0, 0, $size, $size, $rSize, $rSize);
            imagedestroy($img);
            $img = $final;
        }

        if ($filename !== null) {
            $result = imagepng($img, $filename);
            imagedestroy($img);
            return $result;
        }

        ob_start();
        imagepng($img);
        imagedestroy($img);
        return ob_get_clean();
    }

    /**
     * Generate PNG with a text label overlaid in the center.
     * Requires GD extension. Use with EC level 'H' or 'Q' for best results.
     *
     * @param string      $label      Text label to display.
     * @param string|null $filename   File path to save. Null to return binary string.
     * @param int         $size       Total output size in pixels (default 200).
     * @param float       $labelSize  Font size ratio relative to QR size (0.05-0.3, default 0.1).
     * @param string      $fontColor  Label text color hex.
     * @param string      $foreground QR foreground color hex.
     * @param string      $background QR background color hex.
     * @param string|null $fontPath   Path to TTF font file. Null uses built-in GD font.
     * @param bool        $strip      If true, clear a horizontal strip. If false, clear a box around text.
     * @return string|bool PNG binary data if $filename is null, true on file save success.
     */
    public function toPNGWithLabel(
        string $label,
        ?string $filename = null,
        int $size = 200,
        float $labelSize = 0.1,
        string $fontColor = '#000000',
        string $foreground = '#000000',
        string $background = '#ffffff',
        ?string $fontPath = null,
        bool $strip = false
    ) {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('GD extension is required for PNG output.');
        }

        $total = $this->getModuleCount();

        // Supersampling for smooth rounded modules and finder patterns
        $hasFinderRadius = ($this->finderStyle !== null && (
            ($this->finderStyle['outerRadius'] ?? 0) > 0 ||
            ($this->finderStyle['innerRadius'] ?? 0) > 0
        ));
        $needsSmooth = $this->moduleShape !== 'square';
        $scale = ($this->moduleRadius > 0 || $hasFinderRadius || $needsSmooth) ? 4 : 1;
        $rSize = $size * $scale;
        $rModuleSize = (int)floor($rSize / $total);
        $rOffset = (int)floor(($rSize - $rModuleSize * $total) / 2);

        $img = imagecreatetruecolor($rSize, $rSize);

        $isTransparent = ($background === 'transparent');
        $fgRGB = $this->hexToRgb($foreground);
        $fcRGB = $this->hexToRgb($fontColor);
        $fgColor = imagecolorallocate($img, $fgRGB[0], $fgRGB[1], $fgRGB[2]);
        $txtColor = imagecolorallocate($img, $fcRGB[0], $fcRGB[1], $fcRGB[2]);

        if ($isTransparent) {
            imagealphablending($img, false);
            imagesavealpha($img, true);
            $bgColor = imagecolorallocatealpha($img, 0, 0, 0, 127);
            imagefilledrectangle($img, 0, 0, $rSize - 1, $rSize - 1, $bgColor);
            imagealphablending($img, true);
            $labelBgClr = imagecolorallocate($img, 255, 255, 255);
        } else {
            $bgRGB = $this->hexToRgb($background);
            $bgColor = imagecolorallocate($img, $bgRGB[0], $bgRGB[1], $bgRGB[2]);
            imagefilledrectangle($img, 0, 0, $rSize - 1, $rSize - 1, $bgColor);
            $labelBgClr = $bgColor;
        }
        $this->drawModulesPNG($img, $total, $rModuleSize, $rOffset, $fgColor, $bgColor);

        // Downsample for anti-aliasing BEFORE drawing label
        // so that font sizes are correct regardless of supersampling scale
        if ($scale > 1) {
            $final = imagecreatetruecolor($size, $size);
            if ($isTransparent) {
                imagealphablending($final, false);
                imagesavealpha($final, true);
            }
            imagecopyresampled($final, $img, 0, 0, 0, 0, $size, $size, $rSize, $rSize);
            imagedestroy($img);
            $img = $final;
        }

        // Reallocate colors on the (possibly new) image after downsample
        $fcRGB = $this->hexToRgb($fontColor);
        $txtColor = imagecolorallocate($img, $fcRGB[0], $fcRGB[1], $fcRGB[2]);
        if ($isTransparent) {
            $labelBgClr = imagecolorallocate($img, 255, 255, 255);
        } else {
            $bgRGB = $this->hexToRgb($background);
            $labelBgClr = imagecolorallocate($img, $bgRGB[0], $bgRGB[1], $bgRGB[2]);
        }

        // Calculate label dimensions on final-size image
        $labelSize = max(0.05, min(0.3, $labelSize));
        $fontSize = (int)($size * $labelSize);
        $padding = (int)($fontSize * 0.4);

        if ($fontPath !== null && file_exists($fontPath)) {
            // TrueType font
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $label);
            $textW = abs($bbox[2] - $bbox[0]);
            $textH = abs($bbox[7] - $bbox[1]);

            $textX = (int)(($size - $textW) / 2);
            $textY = (int)($size / 2) + (int)($textH / 2);

            // Clear area behind text
            $clearLeft = $textX - $padding;
            $clearTop = $textY - $textH - $padding;
            $clearRight = $textX + $textW + $padding;
            $clearBottom = $textY + $padding;

            if ($strip) {
                $clearLeft = 0;
                $clearRight = $size;
            }

            imagefilledrectangle($img, $clearLeft, $clearTop, $clearRight, $clearBottom, $labelBgClr);
            imagettftext($img, $fontSize, 0, $textX, $textY, $txtColor, $fontPath, $label);
        } else {
            // Built-in font — render at native font 5 size, then scale to desired size.
            // GD built-in fonts are bitmap (max size 5 = 9×15 px), so we render
            // natively and use imagecopyresampled to scale to any arbitrary size.
            $gdFont = 5;
            $nativeCharW = imagefontwidth($gdFont);
            $nativeCharH = imagefontheight($gdFont);
            $nativeTextW = $nativeCharW * strlen($label);
            $nativeTextH = $nativeCharH;

            if ($nativeTextW < 1) $nativeTextW = 1;

            // Scale factor to match desired fontSize
            $scaleFactor = $fontSize / $nativeCharH;
            $textW = (int)round($nativeTextW * $scaleFactor);
            $textH = (int)round($nativeTextH * $scaleFactor);

            $textX = (int)(($size - $textW) / 2);
            $textY = (int)(($size - $textH) / 2);

            $clearLeft = $textX - $padding;
            $clearTop = $textY - $padding;
            $clearRight = $textX + $textW + $padding;
            $clearBottom = $textY + $textH + $padding;

            if ($strip) {
                $clearLeft = 0;
                $clearRight = $size;
            }

            imagefilledrectangle($img, $clearLeft, $clearTop, $clearRight, $clearBottom, $labelBgClr);

            // Render text at native size on a temp canvas with matching background
            $labelBgRGB = $isTransparent ? [255, 255, 255] : $this->hexToRgb($background);
            $textCanvas = imagecreatetruecolor($nativeTextW, $nativeTextH);
            $tcBg = imagecolorallocate($textCanvas, $labelBgRGB[0], $labelBgRGB[1], $labelBgRGB[2]);
            $tcFg = imagecolorallocate($textCanvas, $fcRGB[0], $fcRGB[1], $fcRGB[2]);
            imagefilledrectangle($textCanvas, 0, 0, $nativeTextW - 1, $nativeTextH - 1, $tcBg);
            imagestring($textCanvas, $gdFont, 0, 0, $label, $tcFg);

            // Scale and paste onto main image
            imagecopyresampled($img, $textCanvas, $textX, $textY, 0, 0,
                $textW, $textH, $nativeTextW, $nativeTextH);
            imagedestroy($textCanvas);
        }

        if ($filename !== null) {
            $result = imagepng($img, $filename);
            imagedestroy($img);
            return $result;
        }

        ob_start();
        imagepng($img);
        imagedestroy($img);
        return ob_get_clean();
    }

    /**
     * Generate SVG markup with a logo/image embedded in the center.
     * The logo is embedded as a base64 data URI within the SVG.
     *
     * @param string $logoPath    Path to logo image file.
     * @param int    $size        Total output size in SVG units (default 200).
     * @param float  $logoRatio   Logo size as ratio (0.05-0.4, default 0.2).
     * @param string $foreground  Foreground color.
     * @param string $background  Background color.
     * @param int    $logoPadding Padding around logo.
     * @param int    $logoRadius  Border radius of logo background.
     * @param bool   $scalable    If true, omit width/height.
     * @return string SVG markup.
     */
    public function toSVGWithLogo(
        string $logoPath,
        int $size = 200,
        float $logoRatio = 0.2,
        string $foreground = 'black',
        string $background = 'white',
        int $logoPadding = 4,
        int $logoRadius = 4,
        bool $scalable = false
    ): string {
        if (!file_exists($logoPath)) {
            throw new \RuntimeException("Logo file not found: {$logoPath}");
        }

        $total = $this->getModuleCount();
        $moduleSize = $size / $total;

        $logoRatio = max(0.05, min(0.4, $logoRatio));
        $logoSz = (int)($size * $logoRatio);
        $logoBgSize = $logoSz + $logoPadding * 2;
        $logoBgX = ($size - $logoBgSize) / 2;
        $logoBgY = ($size - $logoBgSize) / 2;
        $logoX = $logoBgX + $logoPadding;
        $logoY = $logoBgY + $logoPadding;

        // Build base SVG
        $svg = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"';
        if (!$scalable) {
            $svg .= ' width="' . $size . 'px" height="' . $size . 'px"';
        }
        $svg .= ' viewBox="0 0 ' . $size . ' ' . $size . '"';
        $svg .= ' preserveAspectRatio="xMinYMin meet">';

        if ($background !== 'transparent') {
            $svg .= '<rect width="100%" height="100%" fill="' . htmlspecialchars($background) . '"/>';
        }
        $svg .= $this->buildSVGModules($total, $moduleSize, 0, $foreground);

        // Logo background — clamp radius to half the background size
        $logoBg = ($background === 'transparent') ? 'white' : htmlspecialchars($background);
        $bgRad = min($logoRadius, (int)($logoBgSize / 2));
        $svg .= '<rect x="' . $logoBgX . '" y="' . $logoBgY . '" width="' . $logoBgSize . '" height="' . $logoBgSize . '"';
        $svg .= ' fill="' . $logoBg . '" rx="' . $bgRad . '" ry="' . $bgRad . '"/>';

        // Embed logo as base64, clipped to rounded corners
        $mime = $this->getMimeType($logoPath);
        $logoData = base64_encode(file_get_contents($logoPath));

        if ($logoRadius > 0) {
            // Clamp clip radius to half the logo size (same as PNG)
            $clipRad = min($logoRadius, (int)($logoSz / 2));

            // Define a clipPath with rounded rect for the logo image
            $svg .= '<defs><clipPath id="logoClip">';
            $svg .= '<rect x="' . $logoX . '" y="' . $logoY . '" width="' . $logoSz . '" height="' . $logoSz . '"';
            $svg .= ' rx="' . $clipRad . '" ry="' . $clipRad . '"/>';
            $svg .= '</clipPath></defs>';

            $svg .= '<image x="' . $logoX . '" y="' . $logoY . '" width="' . $logoSz . '" height="' . $logoSz . '"';
            $svg .= ' href="data:' . $mime . ';base64,' . $logoData . '"';
            $svg .= ' preserveAspectRatio="xMidYMid meet" clip-path="url(#logoClip)"/>';
        } else {
            $svg .= '<image x="' . $logoX . '" y="' . $logoY . '" width="' . $logoSz . '" height="' . $logoSz . '"';
            $svg .= ' href="data:' . $mime . ';base64,' . $logoData . '"';
            $svg .= ' preserveAspectRatio="xMidYMid meet"/>';
        }

        $svg .= '</svg>';
        return $svg;
    }

    /**
     * Generate SVG markup with a text label overlaid in the center.
     *
     * @param string $label      Text to display.
     * @param int    $size       Total output size in SVG units (default 200).
     * @param float  $labelSize  Font size ratio (0.05-0.3, default 0.1).
     * @param string $fontColor  Label text color.
     * @param string $foreground QR foreground color.
     * @param string $background QR background color.
     * @param string $fontFamily CSS font-family for the label.
     * @param bool   $strip      If true, clear horizontal strip. If false, auto-sized box.
     * @param bool   $scalable   If true, omit width/height.
     * @return string SVG markup.
     */
    public function toSVGWithLabel(
        string $label,
        int $size = 200,
        float $labelSize = 0.1,
        string $fontColor = 'black',
        string $foreground = 'black',
        string $background = 'white',
        string $fontFamily = 'Inter, Arial, sans-serif',
        bool $strip = false,
        bool $scalable = false
    ): string {
        $total = $this->getModuleCount();
        $moduleSize = $size / $total;

        $labelSize = max(0.05, min(0.3, $labelSize));
        $fontSize = $size * $labelSize;
        $padding = $fontSize * 0.5;

        // Estimate text width (approximate, ~0.6 ratio for sans-serif)
        $estTextW = strlen($label) * $fontSize * 0.6;
        $boxW = $strip ? $size : $estTextW + $padding * 2;
        $boxH = $fontSize + $padding * 2;
        $boxX = ($size - $boxW) / 2;
        $boxY = ($size - $boxH) / 2;

        $textX = $size / 2;
        $textY = $size / 2 + $fontSize * 0.35;

        // Build SVG
        $svg = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg"';
        if (!$scalable) {
            $svg .= ' width="' . $size . 'px" height="' . $size . 'px"';
        }
        $svg .= ' viewBox="0 0 ' . $size . ' ' . $size . '"';
        $svg .= ' preserveAspectRatio="xMinYMin meet">';

        if ($background !== 'transparent') {
            $svg .= '<rect width="100%" height="100%" fill="' . htmlspecialchars($background) . '"/>';
        }
        $svg .= $this->buildSVGModules($total, $moduleSize, 0, $foreground);

        // Label background box
        $labelBg = ($background === 'transparent') ? 'white' : htmlspecialchars($background);
        $svg .= '<rect x="' . $boxX . '" y="' . $boxY . '" width="' . $boxW . '" height="' . $boxH . '"';
        $svg .= ' fill="' . $labelBg . '" rx="4" ry="4"/>';

        // Label text
        $svg .= '<text x="' . $textX . '" y="' . $textY . '"';
        $svg .= ' font-family="' . htmlspecialchars($fontFamily) . '"';
        $svg .= ' font-size="' . $fontSize . '"';
        $svg .= ' font-weight="bold"';
        $svg .= ' fill="' . htmlspecialchars($fontColor) . '"';
        $svg .= ' text-anchor="middle">';
        $svg .= htmlspecialchars($label);
        $svg .= '</text>';

        $svg .= '</svg>';
        return $svg;
    }

    /**
     * Generate SVG markup string.
     *
     * @param int         $size       Total output size in SVG units (default 200).
     * @param string      $foreground Foreground color.
     * @param string      $background Background color.
     * @param bool        $scalable   If true, omit width/height attributes.
     * @param string|null $title      Optional title for accessibility.
     * @param string|null $alt        Optional description for accessibility.
     * @return string SVG markup.
     */
    public function toSVG(int $size = 200, string $foreground = 'black', string $background = 'white', bool $scalable = false, ?string $title = null, ?string $alt = null): string
    {
        $total = $this->getModuleCount();
        $moduleSize = $size / $total;

        $svg = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg"';
        if (!$scalable) {
            $svg .= ' width="' . $size . 'px" height="' . $size . 'px"';
        }
        $svg .= ' viewBox="0 0 ' . $size . ' ' . $size . '"';
        $svg .= ' preserveAspectRatio="xMinYMin meet"';

        $ariaIds = [];
        if ($title !== null) {
            $ariaIds[] = 'qrcode-title';
        }
        if ($alt !== null) {
            $ariaIds[] = 'qrcode-description';
        }
        if (!empty($ariaIds)) {
            $svg .= ' role="img" aria-labelledby="' . htmlspecialchars(implode(' ', $ariaIds)) . '"';
        }
        $svg .= '>';

        if ($title !== null) {
            $svg .= '<title id="qrcode-title">' . htmlspecialchars($title) . '</title>';
        }
        if ($alt !== null) {
            $svg .= '<description id="qrcode-description">' . htmlspecialchars($alt) . '</description>';
        }

        if ($background !== 'transparent') {
            $svg .= '<rect width="100%" height="100%" fill="' . htmlspecialchars($background) . '"/>';
        }
        $svg .= $this->buildSVGModules($total, $moduleSize, 0, $foreground);
        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Generate an HTML table representation.
     *
     * @param int    $size       Total output size in pixels (default 200).
     * @param string $foreground Dark module color.
     * @param string $background Light module color.
     * @return string HTML table markup.
     */
    public function toHTML(int $size = 200, string $foreground = '#000000', string $background = '#ffffff'): string
    {
        $total = $this->getModuleCount();
        $moduleSize = (int)floor($size / $total);
        $offset = (int)floor(($size - $moduleSize * $total) / 2);

        $html = '<table style="border-width:0;border-style:none;border-collapse:collapse;padding:0;margin:0;">';
        $html .= '<tbody>';

        for ($r = 0; $r < $total; $r++) {
            $html .= '<tr>';
            for ($c = 0; $c < $total; $c++) {
                $color = $this->isDark($r, $c) ? $foreground : $background;
                $html .= '<td style="border-width:0;border-style:none;border-collapse:collapse;padding:0;margin:0;'
                    . 'width:' . $moduleSize . 'px;height:' . $moduleSize . 'px;'
                    . 'background-color:' . htmlspecialchars($color) . ';"/>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * Generate a GIF data URI (no GD required, pure PHP implementation).
     *
     * @param int $size Total output size in pixels (default 200).
     * @return string Data URI string.
     */
    public function toDataURI(int $size = 200): string
    {
        $total = $this->getModuleCount();
        $moduleSize = (int)floor($size / $total);
        $offset = (int)floor(($size - $moduleSize * $total) / 2);
        $qrEnd = $offset + $moduleSize * $total;

        $gifData = [];
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if ($offset <= $x && $x < $qrEnd && $offset <= $y && $y < $qrEnd) {
                    $c = (int)floor(($x - $offset) / $moduleSize);
                    $r = (int)floor(($y - $offset) / $moduleSize);
                    $gifData[$y * $size + $x] = $this->isDark($r, $c) ? 0 : 1;
                } else {
                    $gifData[$y * $size + $x] = 1;
                }
            }
        }

        return 'data:image/gif;base64,' . $this->createGifBase64($size, $size, $gifData);
    }

    /**
     * Generate an <img> tag with embedded GIF data URI.
     *
     * @param int         $size Total output size in pixels (default 200).
     * @param string|null $alt  Alt text.
     * @return string HTML img tag.
     */
    public function toImgTag(int $size = 200, ?string $alt = null): string
    {
        $img = '<img src="' . $this->toDataURI($size) . '"';
        $img .= ' width="' . $size . '"';
        $img .= ' height="' . $size . '"';
        if ($alt !== null) {
            $img .= ' alt="' . htmlspecialchars($alt) . '"';
        }
        $img .= '/>';

        return $img;
    }

    /**
     * Generate a base64-encoded PNG string (for embedding in HTML/CSS).
     * Requires GD extension.
     *
     * @param int    $size       Total output size in pixels (default 200).
     * @param string $foreground Foreground color hex.
     * @param string $background Background color hex.
     * @return string Base64-encoded PNG data.
     */
    public function toBase64PNG(int $size = 200, string $foreground = '#000000', string $background = '#ffffff'): string
    {
        $data = $this->toPNG(null, $size, $foreground, $background);
        return base64_encode($data);
    }

    /**
     * Generate a data URI for PNG (for embedding in HTML src attributes).
     * Requires GD extension.
     *
     * @param int    $size       Total output size in pixels (default 200).
     * @param string $foreground Foreground color hex.
     * @param string $background Background color hex.
     * @return string PNG data URI string.
     */
    public function toPNGDataURI(int $size = 200, string $foreground = '#000000', string $background = '#ffffff'): string
    {
        return 'data:image/png;base64,' . $this->toBase64PNG($size, $foreground, $background);
    }

    /**
     * Generate WEBP image and return binary data or save to file.
     * Requires GD extension with WEBP support.
     *
     * @param string|null $filename   File path to save. Null to return binary string.
     * @param int         $size       Total output size in pixels (default 200).
     * @param string      $foreground Foreground color hex.
     * @param string      $background Background color hex.
     * @param int         $quality    WEBP quality (0-100, default 90).
     * @return string|bool WEBP binary data if $filename is null, true on file save success.
     */
    public function toWEBP(?string $filename = null, int $size = 200, string $foreground = '#000000', string $background = '#ffffff', int $quality = 90)
    {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('GD extension is required for WEBP output.');
        }
        if (!function_exists('imagewebp')) {
            throw new \RuntimeException('WEBP support is not available in your GD installation.');
        }

        $total = $this->getModuleCount();

        $hasFinderRadius = ($this->finderStyle !== null && (
            ($this->finderStyle['outerRadius'] ?? 0) > 0 ||
            ($this->finderStyle['innerRadius'] ?? 0) > 0
        ));
        $needsSmooth = $this->moduleShape !== 'square';
        $scale = ($this->moduleRadius > 0 || $hasFinderRadius || $needsSmooth) ? 4 : 1;
        $rSize = $size * $scale;
        $rModuleSize = (int)floor($rSize / $total);
        $rOffset = (int)floor(($rSize - $rModuleSize * $total) / 2);

        $img = imagecreatetruecolor($rSize, $rSize);

        $isTransparent = ($background === 'transparent');
        $fgRGB = $this->hexToRgb($foreground);
        $fgColor = imagecolorallocate($img, $fgRGB[0], $fgRGB[1], $fgRGB[2]);

        if ($isTransparent) {
            imagealphablending($img, false);
            imagesavealpha($img, true);
            $bgColor = imagecolorallocatealpha($img, 0, 0, 0, 127);
            imagefilledrectangle($img, 0, 0, $rSize - 1, $rSize - 1, $bgColor);
            imagealphablending($img, true);
        } else {
            $bgRGB = $this->hexToRgb($background);
            $bgColor = imagecolorallocate($img, $bgRGB[0], $bgRGB[1], $bgRGB[2]);
            imagefilledrectangle($img, 0, 0, $rSize - 1, $rSize - 1, $bgColor);
        }
        $this->drawModulesPNG($img, $total, $rModuleSize, $rOffset, $fgColor, $bgColor);

        if ($scale > 1) {
            $final = imagecreatetruecolor($size, $size);
            if ($isTransparent) {
                imagealphablending($final, false);
                imagesavealpha($final, true);
            }
            imagecopyresampled($final, $img, 0, 0, 0, 0, $size, $size, $rSize, $rSize);
            imagedestroy($img);
            $img = $final;
        }

        if ($filename !== null) {
            $result = imagewebp($img, $filename, $quality);
            imagedestroy($img);
            return $result;
        }

        ob_start();
        imagewebp($img, null, $quality);
        imagedestroy($img);
        return ob_get_clean();
    }

    /**
     * Generate a data URI for WEBP.
     * Requires GD extension with WEBP support.
     *
     * @param int    $size       Total output size in pixels (default 200).
     * @param string $foreground Foreground color hex.
     * @param string $background Background color hex.
     * @param int    $quality    WEBP quality (0-100, default 90).
     * @return string WEBP data URI string.
     */
    public function toWEBPDataURI(int $size = 200, string $foreground = '#000000', string $background = '#ffffff', int $quality = 90): string
    {
        $data = $this->toWEBP(null, $size, $foreground, $background, $quality);
        return 'data:image/webp;base64,' . base64_encode($data);
    }

    /**
     * Render QR code as ASCII art.
     *
     * @param int $margin Margin in character units.
     * @return string ASCII representation.
     */
    public function toASCII(int $margin = 2): string
    {
        $total = $this->getModuleCount();
        $size = $total + $margin * 2;
        $min = $margin;
        $max = $size - $margin;

        $blocks = [
            '██' => '█',
            '█ ' => '▀',
            ' █' => '▄',
            '  ' => ' ',
        ];

        $blocksLast = [
            '██' => '▀',
            '█ ' => '▀',
            ' █' => ' ',
            '  ' => ' ',
        ];

        $ascii = '';
        for ($y = 0; $y < $size; $y += 2) {
            $r1 = (int)floor(($y - $min));
            $r2 = (int)floor(($y + 1 - $min));
            for ($x = 0; $x < $size; $x++) {
                $p = '█';
                $cx = $x - $min;

                if ($min <= $x && $x < $max && $min <= $y && $y < $max && $this->isDark($r1, $cx)) {
                    $p = ' ';
                }

                if ($min <= $x && $x < $max && $min <= ($y + 1) && ($y + 1) < $max && $this->isDark($r2, $cx)) {
                    $p .= ' ';
                } else {
                    $p .= '█';
                }

                $ascii .= ($margin < 1 && $y + 1 >= $max) ? $blocksLast[$p] : $blocks[$p];
            }
            $ascii .= "\n";
        }

        if ($size % 2 && $margin > 0) {
            return substr($ascii, 0, strlen($ascii) - $size - 1) . str_repeat('▀', $size);
        }

        return rtrim($ascii, "\n");
    }

    // =====================================================================
    // Internal: QR Code Generation
    // =====================================================================

    private $ecLevelChar;
    private $quiet;
    private $text;

    /**
     * Detect the optimal encoding mode for the given data.
     * Numeric mode: digits only (3.3 bits/char)
     * Alphanumeric mode: digits, uppercase A-Z, and symbols $%*+-./: space (5.5 bits/char)
     * Byte mode: any character including UTF-8 (8 bits/char)
     *
     * @param string $data The data to analyze.
     * @return string 'Numeric', 'Alphanumeric', or 'Byte'
     */
    private function detectMode(string $data): string
    {
        // Check Numeric: all digits
        if (preg_match('/^\d+$/', $data)) {
            return 'Numeric';
        }
        // Check Alphanumeric: digits, uppercase A-Z, and specific symbols
        if (preg_match('/^[0-9A-Z $%*+\-.\/\:]+$/', $data)) {
            return 'Alphanumeric';
        }
        // Default: Byte mode
        return 'Byte';
    }

    /**
     * Add data to the QR code.
     */
    private function addData(string $data, string $mode = 'Byte'): void
    {
        switch ($mode) {
            case 'Numeric':
                $this->dataList[] = $this->createQRNumber($data);
                break;
            case 'Alphanumeric':
                $this->dataList[] = $this->createQRAlphaNum($data);
                break;
            case 'Byte':
                $this->dataList[] = $this->createQR8BitByte($data);
                break;
            default:
                throw new \InvalidArgumentException("Unsupported mode: {$mode}");
        }
        $this->dataCache = null;
    }

    /**
     * Build the QR code modules.
     */
    private function make(): void
    {
        if ($this->typeNumber < 1) {
            $typeNumber = 1;
            for (; $typeNumber < 40; $typeNumber++) {
                $rsBlocks = $this->getRSBlocks($typeNumber, $this->errorCorrectionLevel);
                $buffer = $this->createBitBuffer();

                for ($i = 0; $i < count($this->dataList); $i++) {
                    $data = $this->dataList[$i];
                    $buffer['put']($data['getMode'](), 4);
                    $buffer['put']($data['getLength'](), $this->getLengthInBits($data['getMode'](), $typeNumber));
                    $data['write']($buffer);
                }

                $totalDataCount = 0;
                for ($i = 0; $i < count($rsBlocks); $i++) {
                    $totalDataCount += $rsBlocks[$i]['dataCount'];
                }

                if ($buffer['getLengthInBits']() <= $totalDataCount * 8) {
                    break;
                }
            }
            $this->typeNumber = $typeNumber;
        }

        $this->bestMaskPattern = $this->getBestMaskPattern();
        $this->makeImpl(false, $this->bestMaskPattern);
    }

    private function makeImpl(bool $test, int $maskPattern): void
    {
        $this->moduleCount = $this->typeNumber * 4 + 17;

        // Initialize modules
        $this->modules = [];
        for ($row = 0; $row < $this->moduleCount; $row++) {
            $this->modules[$row] = array_fill(0, $this->moduleCount, null);
        }

        $this->setupPositionProbePattern(0, 0);
        $this->setupPositionProbePattern($this->moduleCount - 7, 0);
        $this->setupPositionProbePattern(0, $this->moduleCount - 7);
        $this->setupPositionAdjustPattern();
        $this->setupTimingPattern();
        $this->setupTypeInfo($test, $maskPattern);

        if ($this->typeNumber >= 7) {
            $this->setupTypeNumber($test);
        }

        if ($this->dataCache === null) {
            $this->dataCache = $this->createData($this->typeNumber, $this->errorCorrectionLevel, $this->dataList);
        }

        $this->mapData($this->dataCache, $maskPattern);
    }

    private function setupPositionProbePattern(int $row, int $col): void
    {
        for ($r = -1; $r <= 7; $r++) {
            if ($row + $r <= -1 || $this->moduleCount <= $row + $r) continue;
            for ($c = -1; $c <= 7; $c++) {
                if ($col + $c <= -1 || $this->moduleCount <= $col + $c) continue;

                if ((0 <= $r && $r <= 6 && ($c == 0 || $c == 6))
                    || (0 <= $c && $c <= 6 && ($r == 0 || $r == 6))
                    || (2 <= $r && $r <= 4 && 2 <= $c && $c <= 4)) {
                    $this->modules[$row + $r][$col + $c] = true;
                } else {
                    $this->modules[$row + $r][$col + $c] = false;
                }
            }
        }
    }

    private function getBestMaskPattern(): int
    {
        $minLostPoint = 0;
        $pattern = 0;

        for ($i = 0; $i < 8; $i++) {
            $this->makeImpl(true, $i);
            $lostPoint = $this->getLostPoint();

            if ($i == 0 || $minLostPoint > $lostPoint) {
                $minLostPoint = $lostPoint;
                $pattern = $i;
            }
        }

        return $pattern;
    }

    private function setupTimingPattern(): void
    {
        for ($r = 8; $r < $this->moduleCount - 8; $r++) {
            if ($this->modules[$r][6] !== null) continue;
            $this->modules[$r][6] = ($r % 2 == 0);
        }
        for ($c = 8; $c < $this->moduleCount - 8; $c++) {
            if ($this->modules[6][$c] !== null) continue;
            $this->modules[6][$c] = ($c % 2 == 0);
        }
    }

    private function setupPositionAdjustPattern(): void
    {
        $pos = self::$PATTERN_POSITION_TABLE[$this->typeNumber - 1];

        for ($i = 0; $i < count($pos); $i++) {
            for ($j = 0; $j < count($pos); $j++) {
                $row = $pos[$i];
                $col = $pos[$j];

                if ($this->modules[$row][$col] !== null) continue;

                for ($r = -2; $r <= 2; $r++) {
                    for ($c = -2; $c <= 2; $c++) {
                        if ($r == -2 || $r == 2 || $c == -2 || $c == 2 || ($r == 0 && $c == 0)) {
                            $this->modules[$row + $r][$col + $c] = true;
                        } else {
                            $this->modules[$row + $r][$col + $c] = false;
                        }
                    }
                }
            }
        }
    }

    private function setupTypeNumber(bool $test): void
    {
        $bits = $this->getBCHTypeNumber($this->typeNumber);

        for ($i = 0; $i < 18; $i++) {
            $mod = (!$test && (($bits >> $i) & 1) == 1);
            $this->modules[(int)floor($i / 3)][$i % 3 + $this->moduleCount - 8 - 3] = $mod;
        }

        for ($i = 0; $i < 18; $i++) {
            $mod = (!$test && (($bits >> $i) & 1) == 1);
            $this->modules[$i % 3 + $this->moduleCount - 8 - 3][(int)floor($i / 3)] = $mod;
        }
    }

    private function setupTypeInfo(bool $test, int $maskPattern): void
    {
        $data = ($this->errorCorrectionLevel << 3) | $maskPattern;
        $bits = $this->getBCHTypeInfo($data);

        // vertical
        for ($i = 0; $i < 15; $i++) {
            $mod = (!$test && (($bits >> $i) & 1) == 1);
            if ($i < 6) {
                $this->modules[$i][8] = $mod;
            } elseif ($i < 8) {
                $this->modules[$i + 1][8] = $mod;
            } else {
                $this->modules[$this->moduleCount - 15 + $i][8] = $mod;
            }
        }

        // horizontal
        for ($i = 0; $i < 15; $i++) {
            $mod = (!$test && (($bits >> $i) & 1) == 1);
            if ($i < 8) {
                $this->modules[8][$this->moduleCount - $i - 1] = $mod;
            } elseif ($i < 9) {
                $this->modules[8][15 - $i - 1 + 1] = $mod;
            } else {
                $this->modules[8][15 - $i - 1] = $mod;
            }
        }

        // fixed module
        $this->modules[$this->moduleCount - 8][8] = !$test;
    }

    private function mapData(array $data, int $maskPattern): void
    {
        $inc = -1;
        $row = $this->moduleCount - 1;
        $bitIndex = 7;
        $byteIndex = 0;

        for ($col = $this->moduleCount - 1; $col > 0; $col -= 2) {
            if ($col == 6) $col--;

            while (true) {
                for ($c = 0; $c < 2; $c++) {
                    if ($this->modules[$row][$col - $c] === null) {
                        $dark = false;

                        if ($byteIndex < count($data)) {
                            $dark = ((self::uRightShift($data[$byteIndex], $bitIndex) & 1) == 1);
                        }

                        $mask = $this->getMaskValue($maskPattern, $row, $col - $c);

                        if ($mask) {
                            $dark = !$dark;
                        }

                        $this->modules[$row][$col - $c] = $dark;
                        $bitIndex--;

                        if ($bitIndex == -1) {
                            $byteIndex++;
                            $bitIndex = 7;
                        }
                    }
                }

                $row += $inc;

                if ($row < 0 || $this->moduleCount <= $row) {
                    $row -= $inc;
                    $inc = -$inc;
                    break;
                }
            }
        }
    }

    // =====================================================================
    // Internal: Data Encoding
    // =====================================================================

    private function createQR8BitByte(string $data): array
    {
        $bytes = $this->stringToUTF8Bytes($data);
        return [
            'getMode' => function () { return self::MODE_8BIT_BYTE; },
            'getLength' => function () use ($bytes) { return count($bytes); },
            'write' => function (&$buffer) use ($bytes) {
                for ($i = 0; $i < count($bytes); $i++) {
                    $buffer['put']($bytes[$i], 8);
                }
            },
        ];
    }

    private function createQRNumber(string $data): array
    {
        return [
            'getMode' => function () { return self::MODE_NUMBER; },
            'getLength' => function () use ($data) { return strlen($data); },
            'write' => function (&$buffer) use ($data) {
                $i = 0;
                while ($i + 2 < strlen($data)) {
                    $buffer['put']((int)substr($data, $i, 3), 10);
                    $i += 3;
                }
                if ($i < strlen($data)) {
                    $remaining = strlen($data) - $i;
                    if ($remaining == 1) {
                        $buffer['put']((int)substr($data, $i, 1), 4);
                    } elseif ($remaining == 2) {
                        $buffer['put']((int)substr($data, $i, 2), 7);
                    }
                }
            },
        ];
    }

    private function createQRAlphaNum(string $data): array
    {
        $getCode = function (string $c): int {
            if ($c >= '0' && $c <= '9') {
                return ord($c) - ord('0');
            } elseif ($c >= 'A' && $c <= 'Z') {
                return ord($c) - ord('A') + 10;
            }
            $map = [' ' => 36, '$' => 37, '%' => 38, '*' => 39, '+' => 40, '-' => 41, '.' => 42, '/' => 43, ':' => 44];
            if (isset($map[$c])) return $map[$c];
            throw new \RuntimeException("Illegal alphanumeric char: {$c}");
        };

        return [
            'getMode' => function () { return self::MODE_ALPHA_NUM; },
            'getLength' => function () use ($data) { return strlen($data); },
            'write' => function (&$buffer) use ($data, $getCode) {
                $i = 0;
                while ($i + 1 < strlen($data)) {
                    $buffer['put']($getCode($data[$i]) * 45 + $getCode($data[$i + 1]), 11);
                    $i += 2;
                }
                if ($i < strlen($data)) {
                    $buffer['put']($getCode($data[$i]), 6);
                }
            },
        ];
    }

    private function stringToUTF8Bytes(string $str): array
    {
        $bytes = [];
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $bytes[] = ord($str[$i]);
        }
        // If the string has multi-byte chars, re-encode
        $utf8 = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
        $bytes = [];
        $rawBytes = unpack('C*', $utf8);
        if ($rawBytes) {
            $bytes = array_values($rawBytes);
        }
        return $bytes;
    }

    // =====================================================================
    // Internal: Data Creation & Error Correction
    // =====================================================================

    private function createData(int $typeNumber, int $errorCorrectionLevel, array $dataList): array
    {
        $PAD0 = 0xEC;
        $PAD1 = 0x11;

        $rsBlocks = $this->getRSBlocks($typeNumber, $errorCorrectionLevel);
        $buffer = $this->createBitBuffer();

        for ($i = 0; $i < count($dataList); $i++) {
            $data = $dataList[$i];
            $buffer['put']($data['getMode'](), 4);
            $buffer['put']($data['getLength'](), $this->getLengthInBits($data['getMode'](), $typeNumber));
            $data['write']($buffer);
        }

        $totalDataCount = 0;
        for ($i = 0; $i < count($rsBlocks); $i++) {
            $totalDataCount += $rsBlocks[$i]['dataCount'];
        }

        if ($buffer['getLengthInBits']() > $totalDataCount * 8) {
            throw new \RuntimeException('Code length overflow. (' . $buffer['getLengthInBits']() . '>' . ($totalDataCount * 8) . ')');
        }

        // end code
        if ($buffer['getLengthInBits']() + 4 <= $totalDataCount * 8) {
            $buffer['put'](0, 4);
        }

        // padding
        while ($buffer['getLengthInBits']() % 8 != 0) {
            $buffer['putBit'](false);
        }

        // padding
        while (true) {
            if ($buffer['getLengthInBits']() >= $totalDataCount * 8) break;
            $buffer['put']($PAD0, 8);
            if ($buffer['getLengthInBits']() >= $totalDataCount * 8) break;
            $buffer['put']($PAD1, 8);
        }

        return $this->createBytes($buffer, $rsBlocks);
    }

    private function createBytes(array &$buffer, array $rsBlocks): array
    {
        $offset = 0;
        $maxDcCount = 0;
        $maxEcCount = 0;

        $dcdata = [];
        $ecdata = [];

        for ($r = 0; $r < count($rsBlocks); $r++) {
            $dcCount = $rsBlocks[$r]['dataCount'];
            $ecCount = $rsBlocks[$r]['totalCount'] - $dcCount;

            $maxDcCount = max($maxDcCount, $dcCount);
            $maxEcCount = max($maxEcCount, $ecCount);

            $dcdata[$r] = [];
            for ($i = 0; $i < $dcCount; $i++) {
                $bufferData = $buffer['getBuffer']();
                $dcdata[$r][$i] = 0xff & $bufferData[$i + $offset];
            }
            $offset += $dcCount;

            $rsPoly = $this->getErrorCorrectPolynomial($ecCount);
            $rawPoly = $this->createPolynomial($dcdata[$r], $rsPoly['getLength']() - 1);

            $modPoly = $rawPoly['mod']($rsPoly);
            $ecdata[$r] = [];
            $ecLen = $rsPoly['getLength']() - 1;
            for ($i = 0; $i < $ecLen; $i++) {
                $modIndex = $i + $modPoly['getLength']() - $ecLen;
                $ecdata[$r][$i] = ($modIndex >= 0) ? $modPoly['getAt']($modIndex) : 0;
            }
        }

        $totalCodeCount = 0;
        for ($i = 0; $i < count($rsBlocks); $i++) {
            $totalCodeCount += $rsBlocks[$i]['totalCount'];
        }

        $data = array_fill(0, $totalCodeCount, 0);
        $index = 0;

        for ($i = 0; $i < $maxDcCount; $i++) {
            for ($r = 0; $r < count($rsBlocks); $r++) {
                if ($i < count($dcdata[$r])) {
                    $data[$index] = $dcdata[$r][$i];
                    $index++;
                }
            }
        }

        for ($i = 0; $i < $maxEcCount; $i++) {
            for ($r = 0; $r < count($rsBlocks); $r++) {
                if ($i < count($ecdata[$r])) {
                    $data[$index] = $ecdata[$r][$i];
                    $index++;
                }
            }
        }

        return $data;
    }

    // =====================================================================
    // Internal: RS Blocks
    // =====================================================================

    private function getRSBlocks(int $typeNumber, int $errorCorrectionLevel): array
    {
        $rsBlock = $this->getRsBlockTable($typeNumber, $errorCorrectionLevel);
        if ($rsBlock === null) {
            throw new \RuntimeException("Bad RS block @ typeNumber:{$typeNumber}/errorCorrectionLevel:{$errorCorrectionLevel}");
        }

        $length = count($rsBlock) / 3;
        $list = [];

        for ($i = 0; $i < $length; $i++) {
            $count = $rsBlock[$i * 3 + 0];
            $totalCount = $rsBlock[$i * 3 + 1];
            $dataCount = $rsBlock[$i * 3 + 2];

            for ($j = 0; $j < $count; $j++) {
                $list[] = ['totalCount' => $totalCount, 'dataCount' => $dataCount];
            }
        }

        return $list;
    }

    private function getRsBlockTable(int $typeNumber, int $errorCorrectionLevel): ?array
    {
        switch ($errorCorrectionLevel) {
            case self::EC_L: return self::$RS_BLOCK_TABLE[($typeNumber - 1) * 4 + 0];
            case self::EC_M: return self::$RS_BLOCK_TABLE[($typeNumber - 1) * 4 + 1];
            case self::EC_Q: return self::$RS_BLOCK_TABLE[($typeNumber - 1) * 4 + 2];
            case self::EC_H: return self::$RS_BLOCK_TABLE[($typeNumber - 1) * 4 + 3];
            default: return null;
        }
    }

    // =====================================================================
    // Internal: Bit Buffer
    // =====================================================================

    private function createBitBuffer(): array
    {
        $state = ['buffer' => [], 'length' => 0];

        $buf = [
            'getBuffer' => function () use (&$state) {
                return $state['buffer'];
            },
            'getAt' => function (int $index) use (&$state): bool {
                $bufIndex = (int)floor($index / 8);
                return ((self::uRightShift($state['buffer'][$bufIndex], 7 - $index % 8)) & 1) == 1;
            },
            'put' => function (int $num, int $length) use (&$state) {
                for ($i = 0; $i < $length; $i++) {
                    $bit = ((self::uRightShift($num, $length - $i - 1)) & 1) == 1;
                    // inline putBit
                    $bufIndex = (int)floor($state['length'] / 8);
                    if (count($state['buffer']) <= $bufIndex) {
                        $state['buffer'][] = 0;
                    }
                    if ($bit) {
                        $state['buffer'][$bufIndex] |= (self::uRightShift(0x80, $state['length'] % 8));
                    }
                    $state['length']++;
                }
            },
            'getLengthInBits' => function () use (&$state): int {
                return $state['length'];
            },
            'putBit' => function (bool $bit) use (&$state) {
                $bufIndex = (int)floor($state['length'] / 8);
                if (count($state['buffer']) <= $bufIndex) {
                    $state['buffer'][] = 0;
                }
                if ($bit) {
                    $state['buffer'][$bufIndex] |= (self::uRightShift(0x80, $state['length'] % 8));
                }
                $state['length']++;
            },
        ];

        return $buf;
    }

    // =====================================================================
    // Internal: Polynomial
    // =====================================================================

    private function createPolynomial(array $num, int $shift): array
    {
        // Strip leading zeros
        $offset = 0;
        while ($offset < count($num) && $num[$offset] == 0) {
            $offset++;
        }
        $coefficients = [];
        $numLen = count($num) - $offset;
        for ($i = 0; $i < $numLen; $i++) {
            $coefficients[$i] = $num[$i + $offset];
        }
        // Add shift zeros
        for ($i = 0; $i < $shift; $i++) {
            $coefficients[] = 0;
        }

        $self = null;
        $self = [
            'getAt' => function (int $index) use (&$coefficients): int {
                return $coefficients[$index];
            },
            'getLength' => function () use (&$coefficients): int {
                return count($coefficients);
            },
            'multiply' => function (array $e) use (&$self): array {
                $thisLen = $self['getLength']();
                $eLen = $e['getLength']();
                $num = array_fill(0, $thisLen + $eLen - 1, 0);

                for ($i = 0; $i < $thisLen; $i++) {
                    for ($j = 0; $j < $eLen; $j++) {
                        $num[$i + $j] ^= self::gexp(self::glog($self['getAt']($i)) + self::glog($e['getAt']($j)));
                    }
                }

                return $this->createPolynomial($num, 0);
            },
            'mod' => function (array $e) use (&$self): array {
                if ($self['getLength']() - $e['getLength']() < 0) {
                    return $self;
                }

                $ratio = self::glog($self['getAt'](0)) - self::glog($e['getAt'](0));

                $thisLen = $self['getLength']();
                $num = [];
                for ($i = 0; $i < $thisLen; $i++) {
                    $num[$i] = $self['getAt']($i);
                }

                $eLen = $e['getLength']();
                for ($i = 0; $i < $eLen; $i++) {
                    $num[$i] ^= self::gexp(self::glog($e['getAt']($i)) + $ratio);
                }

                // recursive call
                $newPoly = $this->createPolynomial($num, 0);
                return $newPoly['mod']($e);
            },
        ];

        return $self;
    }

    private function getErrorCorrectPolynomial(int $errorCorrectLength): array
    {
        $a = $this->createPolynomial([1], 0);
        for ($i = 0; $i < $errorCorrectLength; $i++) {
            $a = $a['multiply']($this->createPolynomial([1, self::gexp($i)], 0));
        }
        return $a;
    }

    // =====================================================================
    // Internal: GF(2^8) Math
    // =====================================================================

    private static function initMathTables(): void
    {
        if (self::$EXP_TABLE !== null) return;

        self::$EXP_TABLE = array_fill(0, 256, 0);
        self::$LOG_TABLE = array_fill(0, 256, 0);

        for ($i = 0; $i < 8; $i++) {
            self::$EXP_TABLE[$i] = 1 << $i;
        }
        for ($i = 8; $i < 256; $i++) {
            self::$EXP_TABLE[$i] = self::$EXP_TABLE[$i - 4]
                ^ self::$EXP_TABLE[$i - 5]
                ^ self::$EXP_TABLE[$i - 6]
                ^ self::$EXP_TABLE[$i - 8];
        }
        for ($i = 0; $i < 255; $i++) {
            self::$LOG_TABLE[self::$EXP_TABLE[$i]] = $i;
        }
    }

    private static function glog(int $n): int
    {
        if ($n < 1) {
            throw new \RuntimeException("glog({$n})");
        }
        return self::$LOG_TABLE[$n];
    }

    private static function gexp(int $n): int
    {
        while ($n < 0) {
            $n += 255;
        }
        while ($n >= 256) {
            $n -= 255;
        }
        return self::$EXP_TABLE[$n];
    }

    // =====================================================================
    // Internal: BCH Calculations
    // =====================================================================

    private static function getBCHDigit(int $data): int
    {
        $digit = 0;
        while ($data != 0) {
            $digit++;
            $data = self::uRightShift($data, 1);
        }
        return $digit;
    }

    private function getBCHTypeInfo(int $data): int
    {
        $G15 = (1 << 10) | (1 << 8) | (1 << 5) | (1 << 4) | (1 << 2) | (1 << 1) | (1 << 0);
        $G15_MASK = (1 << 14) | (1 << 12) | (1 << 10) | (1 << 4) | (1 << 1);

        $d = $data << 10;
        while (self::getBCHDigit($d) - self::getBCHDigit($G15) >= 0) {
            $d ^= ($G15 << (self::getBCHDigit($d) - self::getBCHDigit($G15)));
        }
        return (($data << 10) | $d) ^ $G15_MASK;
    }

    private function getBCHTypeNumber(int $data): int
    {
        $G18 = (1 << 12) | (1 << 11) | (1 << 10) | (1 << 9) | (1 << 8) | (1 << 5) | (1 << 2) | (1 << 0);

        $d = $data << 12;
        while (self::getBCHDigit($d) - self::getBCHDigit($G18) >= 0) {
            $d ^= ($G18 << (self::getBCHDigit($d) - self::getBCHDigit($G18)));
        }
        return ($data << 12) | $d;
    }

    // =====================================================================
    // Internal: Mask Functions
    // =====================================================================

    private function getMaskValue(int $maskPattern, int $i, int $j): bool
    {
        switch ($maskPattern) {
            case self::PATTERN000: return ($i + $j) % 2 == 0;
            case self::PATTERN001: return $i % 2 == 0;
            case self::PATTERN010: return $j % 3 == 0;
            case self::PATTERN011: return ($i + $j) % 3 == 0;
            case self::PATTERN100: return ((int)floor($i / 2) + (int)floor($j / 3)) % 2 == 0;
            case self::PATTERN101: return ($i * $j) % 2 + ($i * $j) % 3 == 0;
            case self::PATTERN110: return (($i * $j) % 2 + ($i * $j) % 3) % 2 == 0;
            case self::PATTERN111: return (($i * $j) % 3 + ($i + $j) % 2) % 2 == 0;
            default: throw new \RuntimeException("Bad maskPattern: {$maskPattern}");
        }
    }

    // =====================================================================
    // Internal: Lost Point Calculation
    // =====================================================================

    private function getLostPoint(): int
    {
        $moduleCount = $this->moduleCount;
        $lostPoint = 0;

        // LEVEL1
        for ($row = 0; $row < $moduleCount; $row++) {
            for ($col = 0; $col < $moduleCount; $col++) {
                $sameCount = 0;
                $dark = $this->modules[$row][$col];

                for ($r = -1; $r <= 1; $r++) {
                    if ($row + $r < 0 || $moduleCount <= $row + $r) continue;
                    for ($c = -1; $c <= 1; $c++) {
                        if ($col + $c < 0 || $moduleCount <= $col + $c) continue;
                        if ($r == 0 && $c == 0) continue;
                        if ($dark == $this->modules[$row + $r][$col + $c]) {
                            $sameCount++;
                        }
                    }
                }

                if ($sameCount > 5) {
                    $lostPoint += (3 + $sameCount - 5);
                }
            }
        }

        // LEVEL2
        for ($row = 0; $row < $moduleCount - 1; $row++) {
            for ($col = 0; $col < $moduleCount - 1; $col++) {
                $count = 0;
                if ($this->modules[$row][$col]) $count++;
                if ($this->modules[$row + 1][$col]) $count++;
                if ($this->modules[$row][$col + 1]) $count++;
                if ($this->modules[$row + 1][$col + 1]) $count++;
                if ($count == 0 || $count == 4) {
                    $lostPoint += 3;
                }
            }
        }

        // LEVEL3
        for ($row = 0; $row < $moduleCount; $row++) {
            for ($col = 0; $col < $moduleCount - 6; $col++) {
                if ($this->modules[$row][$col]
                    && !$this->modules[$row][$col + 1]
                    && $this->modules[$row][$col + 2]
                    && $this->modules[$row][$col + 3]
                    && $this->modules[$row][$col + 4]
                    && !$this->modules[$row][$col + 5]
                    && $this->modules[$row][$col + 6]) {
                    $lostPoint += 40;
                }
            }
        }

        for ($col = 0; $col < $moduleCount; $col++) {
            for ($row = 0; $row < $moduleCount - 6; $row++) {
                if ($this->modules[$row][$col]
                    && !$this->modules[$row + 1][$col]
                    && $this->modules[$row + 2][$col]
                    && $this->modules[$row + 3][$col]
                    && $this->modules[$row + 4][$col]
                    && !$this->modules[$row + 5][$col]
                    && $this->modules[$row + 6][$col]) {
                    $lostPoint += 40;
                }
            }
        }

        // LEVEL4
        $darkCount = 0;
        for ($col = 0; $col < $moduleCount; $col++) {
            for ($row = 0; $row < $moduleCount; $row++) {
                if ($this->modules[$row][$col]) {
                    $darkCount++;
                }
            }
        }

        $ratio = abs(100 * $darkCount / $moduleCount / $moduleCount - 50) / 5;
        $lostPoint += (int)($ratio * 10);

        return $lostPoint;
    }

    // =====================================================================
    // Internal: Length in bits per mode
    // =====================================================================

    private function getLengthInBits(int $mode, int $type): int
    {
        if (1 <= $type && $type < 10) {
            switch ($mode) {
                case self::MODE_NUMBER:    return 10;
                case self::MODE_ALPHA_NUM: return 9;
                case self::MODE_8BIT_BYTE: return 8;
                case self::MODE_KANJI:     return 8;
                default: throw new \RuntimeException("mode: {$mode}");
            }
        } elseif ($type < 27) {
            switch ($mode) {
                case self::MODE_NUMBER:    return 12;
                case self::MODE_ALPHA_NUM: return 11;
                case self::MODE_8BIT_BYTE: return 16;
                case self::MODE_KANJI:     return 10;
                default: throw new \RuntimeException("mode: {$mode}");
            }
        } elseif ($type < 41) {
            switch ($mode) {
                case self::MODE_NUMBER:    return 14;
                case self::MODE_ALPHA_NUM: return 13;
                case self::MODE_8BIT_BYTE: return 16;
                case self::MODE_KANJI:     return 12;
                default: throw new \RuntimeException("mode: {$mode}");
            }
        } else {
            throw new \RuntimeException("type: {$type}");
        }
    }

    // =====================================================================
    // Internal: GIF Image Generation (pure PHP, no GD)
    // =====================================================================

    private function createGifBase64(int $width, int $height, array $data): string
    {
        $gif = $this->createGifBinary($width, $height, $data);
        return base64_encode($gif);
    }

    private function createGifBinary(int $width, int $height, array $data): string
    {
        $out = '';

        // GIF Signature
        $out .= 'GIF87a';

        // Screen Descriptor
        $out .= pack('v', $width);
        $out .= pack('v', $height);
        $out .= chr(0x80); // 2bit
        $out .= chr(0);
        $out .= chr(0);

        // Global Color Map
        // black
        $out .= chr(0x00) . chr(0x00) . chr(0x00);
        // white
        $out .= chr(0xff) . chr(0xff) . chr(0xff);

        // Image Descriptor
        $out .= ',';
        $out .= pack('v', 0);
        $out .= pack('v', 0);
        $out .= pack('v', $width);
        $out .= pack('v', $height);
        $out .= chr(0);

        // Raster Data
        $lzwMinCodeSize = 2;
        $raster = $this->getLZWRaster($lzwMinCodeSize, $data);

        $out .= chr($lzwMinCodeSize);

        $offset = 0;
        while (count($raster) - $offset > 255) {
            $out .= chr(255);
            for ($i = 0; $i < 255; $i++) {
                $out .= chr($raster[$offset + $i]);
            }
            $offset += 255;
        }

        $remaining = count($raster) - $offset;
        $out .= chr($remaining);
        for ($i = 0; $i < $remaining; $i++) {
            $out .= chr($raster[$offset + $i]);
        }
        $out .= chr(0x00);

        // GIF Terminator
        $out .= ';';

        return $out;
    }

    private function getLZWRaster(int $lzwMinCodeSize, array $data): array
    {
        $clearCode = 1 << $lzwMinCodeSize;
        $endCode = (1 << $lzwMinCodeSize) + 1;
        $bitLength = $lzwMinCodeSize + 1;

        // Setup LZW Table
        $table = [];
        $tableSize = 0;
        for ($i = 0; $i < $clearCode; $i++) {
            $table[chr($i)] = $tableSize++;
        }
        $table[chr($clearCode)] = $tableSize++;
        $table[chr($endCode)] = $tableSize++;

        // Output stream
        $outBytes = [];
        $bitBuffer = 0;
        $bitLen = 0;

        $writeBits = function (int $data, int $length) use (&$outBytes, &$bitBuffer, &$bitLen) {
            if (($data >> $length) != 0 && $length < 32) {
                // noop, handled
            }
            while ($bitLen + $length >= 8) {
                $outBytes[] = 0xff & (($data << $bitLen) | $bitBuffer);
                $length -= (8 - $bitLen);
                $data >>= (8 - $bitLen);
                $bitBuffer = 0;
                $bitLen = 0;
            }
            $bitBuffer = ($data << $bitLen) | $bitBuffer;
            $bitLen = $bitLen + $length;
        };

        $flushBits = function () use (&$outBytes, &$bitBuffer, &$bitLen) {
            if ($bitLen > 0) {
                $outBytes[] = $bitBuffer;
            }
        };

        // clear code
        $writeBits($clearCode, $bitLength);

        $dataIndex = 0;
        $s = chr($data[$dataIndex]);
        $dataIndex++;

        while ($dataIndex < count($data)) {
            $c = chr($data[$dataIndex]);
            $dataIndex++;

            if (isset($table[$s . $c])) {
                $s = $s . $c;
            } else {
                $writeBits($table[$s], $bitLength);

                if ($tableSize < 0xfff) {
                    if ($tableSize == (1 << $bitLength)) {
                        $bitLength++;
                    }
                    $table[$s . $c] = $tableSize++;
                }

                $s = $c;
            }
        }

        $writeBits($table[$s], $bitLength);

        // end code
        $writeBits($endCode, $bitLength);

        $flushBits();

        return $outBytes;
    }

    // =====================================================================
    // Internal: Utilities
    // =====================================================================

    /**
     * Unsigned right shift (>>> in JavaScript).
     * PHP doesn't have >>>, so we implement it.
     */
    private static function uRightShift(int $a, int $b): int
    {
        if ($b == 0) return $a;
        if ($a >= 0) return $a >> $b;
        // For negative numbers, simulate unsigned right shift
        return ($a >> $b) & (PHP_INT_MAX >> ($b - 1));
    }

    /**
     * Convert hex color string to RGB array.
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Load an image from file path. Supports PNG, JPG, GIF, WEBP, BMP.
     * @return \GdImage|resource|false
     */
    private function loadImage(string $path)
    {
        $info = @getimagesize($path);
        if (!$info) return false;

        switch ($info[2]) {
            case IMAGETYPE_PNG:  return @imagecreatefrompng($path);
            case IMAGETYPE_JPEG: return @imagecreatefromjpeg($path);
            case IMAGETYPE_GIF:  return @imagecreatefromgif($path);
            case IMAGETYPE_WEBP: return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false;
            case IMAGETYPE_BMP:  return function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($path) : false;
            default: return false;
        }
    }

    /**
     * Draw a filled rounded rectangle on a GD image.
     * @param \GdImage|resource $img
     */
    private function imageFilledRoundedRect($img, int $x1, int $y1, int $x2, int $y2, int $radius, int $color): void
    {
        // Clamp radius
        $radius = min($radius, (int)(($x2 - $x1) / 2), (int)(($y2 - $y1) / 2));

        // Fill center areas
        imagefilledrectangle($img, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
        imagefilledrectangle($img, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);

        // Fill corners
        imagefilledellipse($img, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    }

    /**
     * Get MIME type of an image file.
     */
    private function getMimeType(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $map = [
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'bmp'  => 'image/bmp',
            'svg'  => 'image/svg+xml',
            'ico'  => 'image/x-icon',
        ];
        return $map[$ext] ?? 'application/octet-stream';
    }
}

