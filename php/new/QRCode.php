<?php

/**
 * QRCode.php — Modern QR Code Generator (PHP 5.6+)
 *
 * A modernized, single-file QR code generator with fluent API
 * and clean public interface. No legacy caller support.
 *
 * Usage:
 *   $qr = new QRCode('Hello World', 'M', 2);
 *   $qr->size(600)
 *      ->colors('#1a1a2e', '#ffffff')
 *      ->moduleShape('dot')
 *      ->finderStyle('#e74c3c', null, 0.5)
 *      ->render('png', 'qrcode.png');
 *
 * @requires PHP 5.6+
 * @requires ext-gd (for PNG/WEBP output)
 */

class QRCode
{
    const EC_L = 'L';
    const EC_M = 'M';
    const EC_Q = 'Q';
    const EC_H = 'H';

    const SHAPE_SQUARE  = 'square';
    const SHAPE_DOT     = 'dot';
    const SHAPE_DIAMOND = 'diamond';

    const FMT_PNG     = 'png';
    const FMT_SVG     = 'svg';
    const FMT_WEBP    = 'webp';
    const FMT_GIF     = 'gif';
    const FMT_HTML    = 'html';
    const FMT_ASCII   = 'ascii';
    const FMT_DATAURI = 'datauri';
    const FMT_BASE64  = 'base64';
    const FMT_IMGTAG  = 'imgtag';

    const MODE_NUMBER    = 1;
    const MODE_ALPHA_NUM = 2;
    const MODE_8BIT_BYTE = 4;
    const MODE_KANJI     = 8;

    const PATTERN000 = 0;
    const PATTERN001 = 1;
    const PATTERN010 = 2;
    const PATTERN011 = 3;
    const PATTERN100 = 4;
    const PATTERN101 = 5;
    const PATTERN110 = 6;
    const PATTERN111 = 7;


    private static $EC_INTERNAL = ['L' => 1, 'M' => 0, 'Q' => 3, 'H' => 2];

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
        [1, 26, 19],
        [1, 26, 16],
        [1, 26, 13],
        [1, 26, 9],
        [1, 44, 34],
        [1, 44, 28],
        [1, 44, 22],
        [1, 44, 16],
        [1, 70, 55],
        [1, 70, 44],
        [2, 35, 17],
        [2, 35, 13],
        [1, 100, 80],
        [2, 50, 32],
        [2, 50, 24],
        [4, 25, 9],
        [1, 134, 108],
        [2, 67, 43],
        [2, 33, 15, 2, 34, 16],
        [2, 33, 11, 2, 34, 12],
        [2, 86, 68],
        [4, 43, 27],
        [4, 43, 19],
        [4, 43, 15],
        [2, 98, 78],
        [4, 49, 31],
        [2, 32, 14, 4, 33, 15],
        [4, 39, 13, 1, 40, 14],
        [2, 121, 97],
        [2, 60, 38, 2, 61, 39],
        [4, 40, 18, 2, 41, 19],
        [4, 40, 14, 2, 41, 15],
        [2, 146, 116],
        [3, 58, 36, 2, 59, 37],
        [4, 36, 16, 4, 37, 17],
        [4, 36, 12, 4, 37, 13],
        [2, 86, 68, 2, 87, 69],
        [4, 69, 43, 1, 70, 44],
        [6, 43, 19, 2, 44, 20],
        [6, 43, 15, 2, 44, 16],
        [4, 101, 81],
        [1, 80, 50, 4, 81, 51],
        [4, 50, 22, 4, 51, 23],
        [3, 36, 12, 8, 37, 13],
        [2, 116, 92, 2, 117, 93],
        [6, 58, 36, 2, 59, 37],
        [4, 46, 20, 6, 47, 21],
        [7, 42, 14, 4, 43, 15],
        [4, 133, 107],
        [8, 59, 37, 1, 60, 38],
        [8, 44, 20, 4, 45, 21],
        [12, 33, 11, 4, 34, 12],
        [3, 145, 115, 1, 146, 116],
        [4, 64, 40, 5, 65, 41],
        [11, 36, 16, 5, 37, 17],
        [11, 36, 12, 5, 37, 13],
        [5, 109, 87, 1, 110, 88],
        [5, 65, 41, 5, 66, 42],
        [5, 54, 24, 7, 55, 25],
        [11, 36, 12, 7, 37, 13],
        [5, 122, 98, 1, 123, 99],
        [7, 73, 45, 3, 74, 46],
        [15, 43, 19, 2, 44, 20],
        [3, 45, 15, 13, 46, 16],
        [1, 135, 107, 5, 136, 108],
        [10, 74, 46, 1, 75, 47],
        [1, 50, 22, 15, 51, 23],
        [2, 42, 14, 17, 43, 15],
        [5, 150, 120, 1, 151, 121],
        [9, 69, 43, 4, 70, 44],
        [17, 50, 22, 1, 51, 23],
        [2, 42, 14, 19, 43, 15],
        [3, 141, 113, 4, 142, 114],
        [3, 70, 44, 11, 71, 45],
        [17, 47, 21, 4, 48, 22],
        [9, 39, 13, 16, 40, 14],
        [3, 135, 107, 5, 136, 108],
        [3, 67, 41, 13, 68, 42],
        [15, 54, 24, 5, 55, 25],
        [15, 43, 15, 10, 44, 16],
        [4, 144, 116, 4, 145, 117],
        [17, 68, 42],
        [17, 50, 22, 6, 51, 23],
        [19, 46, 16, 6, 47, 17],
        [2, 139, 111, 7, 140, 112],
        [17, 74, 46],
        [7, 54, 24, 16, 55, 25],
        [34, 37, 13],
        [4, 151, 121, 5, 152, 122],
        [4, 75, 47, 14, 76, 48],
        [11, 54, 24, 14, 55, 25],
        [16, 45, 15, 14, 46, 16],
        [6, 147, 117, 4, 148, 118],
        [6, 73, 45, 14, 74, 46],
        [11, 54, 24, 16, 55, 25],
        [30, 46, 16, 2, 47, 17],
        [8, 132, 106, 4, 133, 107],
        [8, 75, 47, 13, 76, 48],
        [7, 54, 24, 22, 55, 25],
        [22, 45, 15, 13, 46, 16],
        [10, 142, 114, 2, 143, 115],
        [19, 74, 46, 4, 75, 47],
        [28, 50, 22, 6, 51, 23],
        [33, 46, 16, 4, 47, 17],
        [8, 152, 122, 4, 153, 123],
        [22, 73, 45, 3, 74, 46],
        [8, 53, 23, 26, 54, 24],
        [12, 45, 15, 28, 46, 16],
        [3, 147, 117, 10, 148, 118],
        [3, 73, 45, 23, 74, 46],
        [4, 54, 24, 31, 55, 25],
        [11, 45, 15, 31, 46, 16],
        [7, 146, 116, 7, 147, 117],
        [21, 73, 45, 7, 74, 46],
        [1, 53, 23, 37, 54, 24],
        [19, 45, 15, 26, 46, 16],
        [5, 145, 115, 10, 146, 116],
        [19, 75, 47, 10, 76, 48],
        [15, 54, 24, 25, 55, 25],
        [23, 45, 15, 25, 46, 16],
        [13, 145, 115, 3, 146, 116],
        [2, 74, 46, 29, 75, 47],
        [42, 54, 24, 1, 55, 25],
        [23, 45, 15, 28, 46, 16],
        [17, 145, 115],
        [10, 74, 46, 23, 75, 47],
        [10, 54, 24, 35, 55, 25],
        [19, 45, 15, 35, 46, 16],
        [17, 145, 115, 1, 146, 116],
        [14, 74, 46, 21, 75, 47],
        [29, 54, 24, 19, 55, 25],
        [11, 45, 15, 46, 46, 16],
        [13, 145, 115, 6, 146, 116],
        [14, 74, 46, 23, 75, 47],
        [44, 54, 24, 7, 55, 25],
        [59, 46, 16, 1, 47, 17],
        [12, 151, 121, 7, 152, 122],
        [12, 75, 47, 26, 76, 48],
        [39, 54, 24, 14, 55, 25],
        [22, 45, 15, 41, 46, 16],
        [6, 151, 121, 14, 152, 122],
        [6, 75, 47, 34, 76, 48],
        [46, 54, 24, 10, 55, 25],
        [2, 45, 15, 64, 46, 16],
        [17, 152, 122, 4, 153, 123],
        [29, 74, 46, 14, 75, 47],
        [49, 54, 24, 10, 55, 25],
        [24, 45, 15, 46, 46, 16],
        [4, 152, 122, 18, 153, 123],
        [13, 74, 46, 32, 75, 47],
        [48, 54, 24, 14, 55, 25],
        [42, 45, 15, 32, 46, 16],
        [20, 147, 117, 4, 148, 118],
        [40, 75, 47, 7, 76, 48],
        [43, 54, 24, 22, 55, 25],
        [10, 45, 15, 67, 46, 16],
        [19, 148, 118, 6, 149, 119],
        [18, 75, 47, 31, 76, 48],
        [34, 54, 24, 34, 55, 25],
        [20, 45, 15, 61, 46, 16],
    ];

    private static $EXP_TABLE = null;

    private static $LOG_TABLE = null;

    private $typeNumber;
    private $errorCorrectionLevel;
    private $modules = null;
    private $moduleCount = 0;
    private $dataCache = null;
    private $dataList = [];

    private $detectedMode = 'Byte';
    private $bestMaskPattern = 0;

    private $moduleRadius = 0.0;
    private $moduleShape = 'square';
    private $finderStyle = null;

    private $renderSize = 400;
    private $renderFg = '#000000';
    private $renderBg = '#ffffff';
    private $renderQuality = 85;
    private $renderScalable = false;
    private $renderTitle = null;
    private $renderDesc = null;
    private $renderMargin = 2;
    private $logoPath = null;
    private $logoOptions = ['ratio' => 0.2, 'padding' => 6, 'radius' => 15];
    private $labelText = null;
    private $labelOptions = [
        'size' => 0.1,
        'color' => '#000000',
        'font' => null,
        'fontFamily' => 'Inter, Arial, sans-serif',
        'strip' => false,
    ];

    private $ecLevelChar;
    private $quiet;
    private $text;

    public function __construct($data, $ec = 'M', $quietZone = 2, $minVer = 1, $maxVer = 40)
    {
        self::initMathTables();

        $this->ecLevelChar = $ec;
        $this->quiet = max(0, $quietZone);
        $this->text = $data;
        $this->errorCorrectionLevel = self::$EC_INTERNAL[$ec];

        $minVer = max(1, $minVer);
        $maxVer = min(40, $maxVer);

        $success = false;
        for ($ver = $minVer; $ver <= $maxVer; $ver++) {
            try {
                $this->typeNumber = $ver;
                $this->dataList = [];
                $this->dataCache = null;
                $this->modules = null;
                $this->moduleCount = 0;

                $this->detectedMode = $this->detectMode($data);
                $this->addData($data, $this->detectedMode);
                $this->make();
                $success = true;
                break;
            } catch (\Exception $e) {
            }
        }

        if (!$success) {
            throw new \RuntimeException(
                "Data too long for QR versions {$minVer}-{$maxVer} with EC level {$ec}."
            );
        }
    }

    public function size($size) {
        $this->renderSize = max(10, $size);
        return $this;
    }

    public function colors($foreground, $background = '#ffffff') {
        $this->renderFg = $foreground;
        $this->renderBg = $background;
        return $this;
    }

    public function moduleRadius($ratio) {
        $this->moduleRadius = max(0.0, min(0.5, $ratio));
        return $this;
    }

    public function moduleShape($shape) {
        $this->moduleShape = $shape;
        return $this;
    }

    public function finderStyle($outerColor = null, $innerColor = null, $outerRadius = null, $innerRadius = null
    ) {
        $this->finderStyle = [
            'outerColor'  => $outerColor,
            'innerColor'  => $innerColor,
            'outerRadius' => $outerRadius !== null ? max(0.0, min(0.5, $outerRadius)) : null,
            'innerRadius' => $innerRadius !== null ? max(0.0, min(0.5, $innerRadius)) : null,
        ];
        return $this;
    }

    public function logo($path, $ratio = 0.2, $padding = 6, $radius = 15
    ) {
        $this->logoPath = $path;
        if ($path !== null) {
            $this->labelText = null;
            $this->logoOptions = compact('ratio', 'padding', 'radius');
        }
        return $this;
    }

    public function label($text, $size = 0.1, $color = '#000000', $font = null, $fontFamily = 'Inter, Arial, sans-serif', $strip = false
    ) {
        $this->labelText = $text;
        if ($text !== null) {
            $this->logoPath = null;
            $this->labelOptions = compact('size', 'color', 'font', 'fontFamily', 'strip');
        }
        return $this;
    }

    public function quality($quality) {
        $this->renderQuality = max(0, min(100, $quality));
        return $this;
    }

    public function scalable($scalable = true) {
        $this->renderScalable = $scalable;
        return $this;
    }

    public function accessibility($title, $desc = null) {
        $this->renderTitle = $title;
        $this->renderDesc = $desc;
        return $this;
    }

    public function margin($margin) {
        $this->renderMargin = max(0, $margin);
        return $this;
    }

    public function isDark($row, $col) {
        $row -= $this->quiet;
        $col -= $this->quiet;

        if ($row < 0 || $row >= $this->moduleCount || $col < 0 || $col >= $this->moduleCount) {
            return false;
        }

        return (bool)$this->modules[$row][$col];
    }

    public function getModuleCount() {
        return $this->moduleCount + 2 * $this->quiet;
    }

    public function getRawModuleCount() {
        return $this->moduleCount;
    }

    public function matrix() {
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

    public function info() {
        $rawCount = $this->getRawModuleCount();
        $rsBlocks = $this->getRSBlocks($this->typeNumber, $this->errorCorrectionLevel);
        $totalDataCount = 0;
        for ($i = 0; $i < count($rsBlocks); $i++) {
            $totalDataCount += $rsBlocks[$i]['dataCount'];
        }

        $buffer = $this->createBitBuffer();
        foreach ($this->dataList as $data) {
            $buffer['put']($data['getMode'](), 4);
            $buffer['put']($data['getLength'](), $this->getLengthInBits($data['getMode'](), $this->typeNumber));
            $data['write']($buffer);
        }
        $dataBitsUsed = $buffer['getLengthInBits']();

        return [
            'version'          => $this->typeNumber,
            'ecLevel'          => $this->ecLevelChar,
            'mode'             => $this->detectedMode,
            'moduleCount'      => $this->getModuleCount(),
            'rawModuleCount'   => $rawCount,
            'maskPattern'      => $this->bestMaskPattern,
            'dataCapacityBits' => $totalDataCount * 8,
            'dataUsedBits'     => $dataBitsUsed,
            'utilization'      => round($dataBitsUsed / ($totalDataCount * 8), 4),
        ];
    }

    public function render($format = 'png', $filename = null) {
        $size = $this->renderSize;
        $fg   = $this->renderFg;
        $bg   = $this->renderBg;

        switch ($format) {
            case 'png':
                if ($this->logoPath !== null) {
                    return $this->toPNGWithLogo($this->logoPath, $filename, $size,
                        (isset($this->logoOptions['ratio']) ? $this->logoOptions['ratio'] : 0.2), $fg, $bg,
                        (isset($this->logoOptions['padding']) ? $this->logoOptions['padding'] : 6), $this->_calcLogoRadiusPx()
                    );
                }
                if ($this->labelText !== null) {
                    return $this->toPNGWithLabel($this->labelText, $filename, $size,
                        (isset($this->labelOptions['size']) ? $this->labelOptions['size'] : 0.1),
                        (isset($this->labelOptions['color']) ? $this->labelOptions['color'] : '#000000'), $fg, $bg,
                        (isset($this->labelOptions['font']) ? $this->labelOptions['font'] : null),
                        (isset($this->labelOptions['strip']) ? $this->labelOptions['strip'] : false)
                    );
                }
                return $this->toPNG($filename, $size, $fg, $bg);

            case 'svg':
                return $this->renderSVG($filename, $size, $fg, $bg);

            case 'webp':
                return $this->toWEBP($filename, $size, $fg, $bg, $this->renderQuality);

            case 'gif':
                return $this->renderGif($filename, $size);

            case 'html':
                return $this->renderAndSave($this->toHTML($size, $fg, $bg), $filename);

            case 'ascii':
                return $this->toASCII($this->renderMargin);

            case 'datauri':
                return 'data:image/png;base64,' . base64_encode($this->toPNG(null, $size, $fg, $bg));

            case 'base64':
                return base64_encode($this->toPNG(null, $size, $fg, $bg));

            case 'imgtag':
                return '<img src="' . $this->renderGifDataUri($size) . '"'
                    . ' width="' . $size . '" height="' . $size . '"'
                    . ($this->renderTitle !== null ? ' alt="' . htmlspecialchars($this->renderTitle) . '"' : '')
                    . '/>';

            default:
                throw new \InvalidArgumentException("Unsupported format: '{$format}'");
        }
    }

    private function renderSVG($filename, $size, $fg, $bg) {
        if ($this->logoPath !== null) {
            $svg = $this->toSVGWithLogo($this->logoPath, $size,
                (isset($this->logoOptions['ratio']) ? $this->logoOptions['ratio'] : 0.2), $fg, $bg,
                (isset($this->logoOptions['padding']) ? $this->logoOptions['padding'] : 6),
                (isset($this->logoOptions['radius']) ? $this->logoOptions['radius'] : 15), $this->renderScalable
            );
        } elseif ($this->labelText !== null) {
            $svg = $this->toSVGWithLabel($this->labelText, $size,
                (isset($this->labelOptions['size']) ? $this->labelOptions['size'] : 0.1),
                (isset($this->labelOptions['color']) ? $this->labelOptions['color'] : '#000000'), $fg, $bg,
                (isset($this->labelOptions['fontFamily']) ? $this->labelOptions['fontFamily'] : 'Inter, Arial, sans-serif'),
                (isset($this->labelOptions['strip']) ? $this->labelOptions['strip'] : false), $this->renderScalable
            );
        } else {
            $svg = $this->toSVG($size, $fg, $bg, $this->renderScalable, $this->renderTitle, $this->renderDesc);
        }

        if ($filename !== null) {
            return (bool) file_put_contents($filename, $svg);
        }
        return $svg;
    }

    private function renderGif($filename, $size) {
        $data = $this->_renderGifBinary($size);
        if ($filename !== null) {
            return (bool) file_put_contents($filename, $data);
        }
        return $data;
    }

    private function renderAndSave($data, $filename) {
        if ($filename !== null) {
            return (bool) file_put_contents($filename, $data);
        }
        return $data;
    }

    private function _calcLogoRadiusPx() {
        $logoSizePx = (int)($this->renderSize * ((isset($this->logoOptions['ratio']) ? $this->logoOptions['ratio'] : 0.2)));
        return (int)round($logoSizePx * ((isset($this->logoOptions['radius']) ? $this->logoOptions['radius'] : 15)) / 100);
    }

    private function _renderGifBinary($size) {
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

    private function getFinderRole($row, $col) {
        $mc = $this->moduleCount;
        $origins = [[0, 0], [0, $mc - 7], [$mc - 7, 0]];

        foreach ($origins as [$or, $oc]) {
            $lr = $row - $or;
            $lc = $col - $oc;
            if ($lr >= 0 && $lr <= 6 && $lc >= 0 && $lc <= 6) {
                if ($lr >= 2 && $lr <= 4 && $lc >= 2 && $lc <= 4) {
                    return 'inner';
                }
                return 'outer';
            }
        }
        return null;
    }

    private function drawModulesPNG($img, $total, $moduleSize, $offset, $fgColor, $bgColor) {
        $hasFinderStyle = ($this->finderStyle !== null);
        $shape = $this->moduleShape;

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

        if ($shape === 'dot' || $shape === 'diamond') {
            $dotScale = 0.80;
            $dotDiam = (int)round($moduleSize * $dotScale);
            $half = (int)round($moduleSize / 2);

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

                    $cx = $offset + $col * $moduleSize + $half;
                    $cy = $offset + $row * $moduleSize + $half;

                    if ($shape === 'dot') {
                        imagefilledellipse($img, $cx, $cy, $dotDiam, $dotDiam, $fgColor);
                    } else {
                        $pts = [
                            $cx, $cy - $half, $cx + $half, $cy, $cx, $cy + $half, $cx - $half, $cy,
                        ];
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

        $defaultRad = ($this->moduleRadius > 0) ? max(1, (int)round($moduleSize * $this->moduleRadius)) : 0;

        for ($row = 0; $row < $total; $row++) {
            for ($col = 0; $col < $total; $col++) {
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

        if ($hasFinderStyle) {
            $this->drawFinderPatternsPNG($img, $moduleSize, $offset, $fgColor, $bgColor);
        }
    }

    private function drawFinderPatternsPNG($img, $moduleSize, $offset, $fgColor, $bgColor) {
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

        $outerRatio = (isset($this->finderStyle['outerRadius']) ? $this->finderStyle['outerRadius'] : $this->moduleRadius);
        $innerRatio = (isset($this->finderStyle['innerRadius']) ? $this->finderStyle['innerRadius'] : $this->moduleRadius);
        $outerRadPx = (int)round(7 * $moduleSize * $outerRatio);
        $gapRadPx   = (int)round(5 * $moduleSize * $outerRatio);
        $innerRadPx = (int)round(3 * $moduleSize * $innerRatio);

        $q = $this->quiet;
        $rawMC = $this->moduleCount;
        $origins = [
            [$q, $q],
            [$q, $q + $rawMC - 7],
            [$q + $rawMC - 7, $q],
        ];

        foreach ($origins as [$gr, $gc]) {
            $ox1 = $offset + $gc * $moduleSize;
            $oy1 = $offset + $gr * $moduleSize;
            $ox2 = $ox1 + 7 * $moduleSize - 1;
            $oy2 = $oy1 + 7 * $moduleSize - 1;
            $this->drawRoundedRectPNG($img, $ox1, $oy1, $ox2, $oy2, $outerRadPx, $outerColor);

            $gx1 = $ox1 + $moduleSize;
            $gy1 = $oy1 + $moduleSize;
            $gx2 = $gx1 + 5 * $moduleSize - 1;
            $gy2 = $gy1 + 5 * $moduleSize - 1;
            $this->drawRoundedRectPNG($img, $gx1, $gy1, $gx2, $gy2, $gapRadPx, $bgColor);

            $ix1 = $ox1 + 2 * $moduleSize;
            $iy1 = $oy1 + 2 * $moduleSize;
            $ix2 = $ix1 + 3 * $moduleSize - 1;
            $iy2 = $iy1 + 3 * $moduleSize - 1;
            $this->drawRoundedRectPNG($img, $ix1, $iy1, $ix2, $iy2, $innerRadPx, $innerColor);
        }
    }

    private function drawRoundedRectPNG($img, $x1, $y1, $x2, $y2, $rad, $color) {
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

        imagefilledrectangle($img, $x1 + $rad, $y1, $x2 - $rad, $y2, $color);
        imagefilledrectangle($img, $x1, $y1 + $rad, $x2, $y2 - $rad, $color);

        $d = $rad * 2;
        imagefilledarc($img, $x1 + $rad, $y1 + $rad, $d, $d, 180, 270, $color, IMG_ARC_PIE);
        imagefilledarc($img, $x2 - $rad, $y1 + $rad, $d, $d, 270, 360, $color, IMG_ARC_PIE);
        imagefilledarc($img, $x1 + $rad, $y2 - $rad, $d, $d, 90, 180, $color, IMG_ARC_PIE);
        imagefilledarc($img, $x2 - $rad, $y2 - $rad, $d, $d, 0, 90, $color, IMG_ARC_PIE);
    }

    private function clearCornerPNG($img, $cx, $cy, $rad, $corner, $fgColor, $bgColor) {
        $r2 = $rad * $rad;
        imagealphablending($img, false);
        for ($oy = 0; $oy < $rad; $oy++) {
            for ($ox = 0; $ox < $rad; $ox++) {
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

    private function fillInnerCornerPNG($img, $cx, $cy, $rad, $corner, $fgColor, $bgColor) {
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

    private function buildSVGModules($total, $moduleSize, $offset, $foreground) {
        $fg = htmlspecialchars($foreground);
        $ms = round($moduleSize, 4);
        $hasFinderStyle = ($this->finderStyle !== null);
        $shape = $this->moduleShape;

        $finderOuterFg = ($hasFinderStyle && $this->finderStyle['outerColor'] !== null)
            ? htmlspecialchars($this->finderStyle['outerColor']) : $fg;
        $finderInnerFg = ($hasFinderStyle && $this->finderStyle['innerColor'] !== null)
            ? htmlspecialchars($this->finderStyle['innerColor']) : $fg;

        $defaultRad = round($moduleSize * $this->moduleRadius, 4);

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

        if ($shape === 'dot' || $shape === 'diamond') {
            $d = '';
            $dotR = round($moduleSize * 0.40, 4);
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
                        $d2 = round($dotR * 2, 4);
                        $d .= 'M' . round($cx - $dotR, 4) . ',' . $cy;
                        $d .= 'a' . $dotR . ',' . $dotR . ' 0 1,0 ' . $d2 . ',0';
                        $d .= 'a' . $dotR . ',' . $dotR . ' 0 1,0 -' . $d2 . ',0Z ';
                    } else {
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

            if ($hasFinderStyle) {
                $svg .= $this->buildSVGFinderRects($total, $moduleSize, $offset, $fg, $finderOuterFg, $finderInnerFg);
            }
            return $svg;
        }

        $d = '';

        for ($row = 0; $row < $total; $row++) {
            for ($col = 0; $col < $total; $col++) {
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

        $svg = '';
        if ($d !== '') {
            $svg .= '<path d="' . $d . '" fill="' . $fg . '"/>';
        }

        if ($hasFinderStyle) {
            $svg .= $this->buildSVGFinderRects($total, $moduleSize, $offset, $fg, $finderOuterFg, $finderInnerFg);
        }

        return $svg;
    }

    private function buildSVGFinderRects($total, $moduleSize, $offset, $fg, $outerFg, $innerFg) {
        $outerRatio = (isset($this->finderStyle['outerRadius']) ? $this->finderStyle['outerRadius'] : $this->moduleRadius);
        $innerRatio = (isset($this->finderStyle['innerRadius']) ? $this->finderStyle['innerRadius'] : $this->moduleRadius);

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

            $svg .= '<rect x="' . $ox . '" y="' . $oy . '" width="' . $outerW . '" height="' . $outerW . '"';
            if ($outerRx > 0) $svg .= ' rx="' . $outerRx . '" ry="' . $outerRx . '"';
            $svg .= ' fill="' . $outerFg . '"/>';

            $svg .= '<rect x="' . $gx . '" y="' . $gy . '" width="' . $gapW . '" height="' . $gapW . '"';
            if ($gapRx > 0) $svg .= ' rx="' . $gapRx . '" ry="' . $gapRx . '"';
            $svg .= ' fill="' . $bgFill . '"/>';

            $svg .= '<rect x="' . $ix . '" y="' . $iy . '" width="' . $innerW . '" height="' . $innerW . '"';
            if ($innerRx > 0) $svg .= ' rx="' . $innerRx . '" ry="' . $innerRx . '"';
            $svg .= ' fill="' . $innerFg . '"/>';
        }

        return $svg;
    }

    private function toPNG($filename = null, $size = 200, $foreground = '#000000', $background = '#ffffff')
    {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('GD extension is required for PNG output.');
        }

        $total = $this->getModuleCount();

        $hasFinderRadius = ($this->finderStyle !== null && (
            ((isset($this->finderStyle['outerRadius']) ? $this->finderStyle['outerRadius'] : 0)) > 0 ||
            ((isset($this->finderStyle['innerRadius']) ? $this->finderStyle['innerRadius'] : 0)) > 0
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
            $result = imagepng($img, $filename);
            imagedestroy($img);
            return $result;
        }

        ob_start();
        imagepng($img);
        imagedestroy($img);
        return ob_get_clean();
    }

    private function toPNGWithLogo($logoPath, $filename = null, $size = 200, $logoRatio = 0.2, $foreground = '#000000', $background = '#ffffff', $logoPadding = 6, $logoRadius = 8
    ) {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('GD extension is required for PNG output.');
        }
        if (!file_exists($logoPath)) {
            throw new \RuntimeException("Logo file not found: {$logoPath}");
        }

        $total = $this->getModuleCount();

        $hasFinderRadius = ($this->finderStyle !== null && (
            ((isset($this->finderStyle['outerRadius']) ? $this->finderStyle['outerRadius'] : 0)) > 0 ||
            ((isset($this->finderStyle['innerRadius']) ? $this->finderStyle['innerRadius'] : 0)) > 0
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

        $logoImg = $this->loadImage($logoPath);
        if ($logoImg) {
            $logoRatio = max(0.05, min(0.4, $logoRatio));
            $logoSize = (int)($rSize * $logoRatio);

            $logoBgSize = $logoSize + $rPad * 2;
            $logoBgX = (int)(($rSize - $logoBgSize) / 2);
            $logoBgY = (int)(($rSize - $logoBgSize) / 2);

            if ($logoRadius > 0) {
                $this->imageFilledRoundedRect($img, $logoBgX, $logoBgY, $logoBgX + $logoBgSize, $logoBgY + $logoBgSize, $rRad, $logoBgClr);
            } else {
                imagefilledrectangle($img, $logoBgX, $logoBgY, $logoBgX + $logoBgSize - 1, $logoBgY + $logoBgSize - 1, $logoBgClr);
            }

            $logoX = $logoBgX + $rPad;
            $logoY = $logoBgY + $rPad;
            $origW = imagesx($logoImg);
            $origH = imagesy($logoImg);

            if ($logoRadius > 0 && $rRad > 0) {
                $clipRad = min($rRad, (int)($logoSize / 2));

                $mask = imagecreatetruecolor($logoSize, $logoSize);
                $maskBlack = imagecolorallocate($mask, 0, 0, 0);
                $maskWhite = imagecolorallocate($mask, 255, 255, 255);
                imagefilledrectangle($mask, 0, 0, $logoSize - 1, $logoSize - 1, $maskBlack);
                $this->imageFilledRoundedRect($mask, 0, 0, $logoSize - 1, $logoSize - 1, $clipRad, $maskWhite);

                $logoResized = imagecreatetruecolor($logoSize, $logoSize);
                imagealphablending($logoResized, false);
                imagesavealpha($logoResized, true);
                $transResized = imagecolorallocatealpha($logoResized, 0, 0, 0, 127);
                imagefilledrectangle($logoResized, 0, 0, $logoSize - 1, $logoSize - 1, $transResized);
                imagealphablending($logoResized, true);
                imagecopyresampled($logoResized, $logoImg, 0, 0, 0, 0, $logoSize, $logoSize, $origW, $origH);

                $logoTmp = imagecreatetruecolor($logoSize, $logoSize);
                imagealphablending($logoTmp, false);
                imagesavealpha($logoTmp, true);
                $trans = imagecolorallocatealpha($logoTmp, 0, 0, 0, 127);
                imagefilledrectangle($logoTmp, 0, 0, $logoSize - 1, $logoSize - 1, $trans);

                for ($py = 0; $py < $logoSize; $py++) {
                    for ($px = 0; $px < $logoSize; $px++) {
                        if ((imagecolorat($mask, $px, $py) & 0xFF) > 0) {
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

                imagealphablending($img, true);
                imagecopy($img, $logoTmp, $logoX, $logoY, 0, 0, $logoSize, $logoSize);
                imagedestroy($logoTmp);
            } else {
                imagecopyresampled($img, $logoImg, $logoX, $logoY, 0, 0, $logoSize, $logoSize, $origW, $origH);
            }
            imagedestroy($logoImg);
        }

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

    private function toPNGWithLabel($label, $filename = null, $size = 200, $labelSize = 0.1, $fontColor = '#000000', $foreground = '#000000', $background = '#ffffff', $fontPath = null, $strip = false
    ) {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('GD extension is required for PNG output.');
        }

        $total = $this->getModuleCount();

        $hasFinderRadius = ($this->finderStyle !== null && (
            ((isset($this->finderStyle['outerRadius']) ? $this->finderStyle['outerRadius'] : 0)) > 0 ||
            ((isset($this->finderStyle['innerRadius']) ? $this->finderStyle['innerRadius'] : 0)) > 0
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

        $fcRGB = $this->hexToRgb($fontColor);
        $txtColor = imagecolorallocate($img, $fcRGB[0], $fcRGB[1], $fcRGB[2]);
        if ($isTransparent) {
            $labelBgClr = imagecolorallocate($img, 255, 255, 255);
        } else {
            $bgRGB = $this->hexToRgb($background);
            $labelBgClr = imagecolorallocate($img, $bgRGB[0], $bgRGB[1], $bgRGB[2]);
        }

        $labelSize = max(0.05, min(0.3, $labelSize));
        $fontSize = (int)($size * $labelSize);
        $padding = (int)($fontSize * 0.4);

        if ($fontPath !== null && file_exists($fontPath)) {
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $label);
            $textW = abs($bbox[2] - $bbox[0]);
            $textH = abs($bbox[7] - $bbox[1]);

            $textX = (int)(($size - $textW) / 2);
            $textY = (int)($size / 2) + (int)($textH / 2);

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
            $gdFont = 5;
            $nativeCharW = imagefontwidth($gdFont);
            $nativeCharH = imagefontheight($gdFont);
            $nativeTextW = $nativeCharW * strlen($label);
            $nativeTextH = $nativeCharH;

            if ($nativeTextW < 1) $nativeTextW = 1;

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

            $labelBgRGB = $isTransparent ? [255, 255, 255] : $this->hexToRgb($background);
            $textCanvas = imagecreatetruecolor($nativeTextW, $nativeTextH);
            $tcBg = imagecolorallocate($textCanvas, $labelBgRGB[0], $labelBgRGB[1], $labelBgRGB[2]);
            $tcFg = imagecolorallocate($textCanvas, $fcRGB[0], $fcRGB[1], $fcRGB[2]);
            imagefilledrectangle($textCanvas, 0, 0, $nativeTextW - 1, $nativeTextH - 1, $tcBg);
            imagestring($textCanvas, $gdFont, 0, 0, $label, $tcFg);

            imagecopyresampled($img, $textCanvas, $textX, $textY, 0, 0, $textW, $textH, $nativeTextW, $nativeTextH);
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

    private function toSVGWithLogo($logoPath, $size = 200, $logoRatio = 0.2, $foreground = 'black', $background = 'white', $logoPadding = 4, $logoRadius = 4, $scalable = false
    ) {
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

        $logoBg = ($background === 'transparent') ? 'white' : htmlspecialchars($background);
        $bgRad = min($logoRadius, (int)($logoBgSize / 2));
        $svg .= '<rect x="' . $logoBgX . '" y="' . $logoBgY . '" width="' . $logoBgSize . '" height="' . $logoBgSize . '"';
        $svg .= ' fill="' . $logoBg . '" rx="' . $bgRad . '" ry="' . $bgRad . '"/>';

        $mime = $this->getMimeType($logoPath);
        $logoData = base64_encode(file_get_contents($logoPath));

        if ($logoRadius > 0) {
            $clipRad = min($logoRadius, (int)($logoSz / 2));

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

    private function toSVGWithLabel($label, $size = 200, $labelSize = 0.1, $fontColor = 'black', $foreground = 'black', $background = 'white', $fontFamily = 'Inter, Arial, sans-serif', $strip = false, $scalable = false
    ) {
        $total = $this->getModuleCount();
        $moduleSize = $size / $total;

        $labelSize = max(0.05, min(0.3, $labelSize));
        $fontSize = $size * $labelSize;
        $padding = $fontSize * 0.5;

        $estTextW = strlen($label) * $fontSize * 0.6;
        $boxW = $strip ? $size : $estTextW + $padding * 2;
        $boxH = $fontSize + $padding * 2;
        $boxX = ($size - $boxW) / 2;
        $boxY = ($size - $boxH) / 2;

        $textX = $size / 2;
        $textY = $size / 2 + $fontSize * 0.35;

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

        $labelBg = ($background === 'transparent') ? 'white' : htmlspecialchars($background);
        $svg .= '<rect x="' . $boxX . '" y="' . $boxY . '" width="' . $boxW . '" height="' . $boxH . '"';
        $svg .= ' fill="' . $labelBg . '" rx="4" ry="4"/>';

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

    private function toSVG($size = 200, $foreground = 'black', $background = 'white', $scalable = false, $title = null, $alt = null) {
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

    private function toHTML($size = 200, $foreground = '#000000', $background = '#ffffff') {
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

    private function renderGifDataUri($size = 200) {
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

    private function toWEBP($filename = null, $size = 200, $foreground = '#000000', $background = '#ffffff', $quality = 90)
    {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('GD extension is required for WEBP output.');
        }
        if (!function_exists('imagewebp')) {
            throw new \RuntimeException('WEBP support is not available in your GD installation.');
        }

        $total = $this->getModuleCount();

        $hasFinderRadius = ($this->finderStyle !== null && (
            ((isset($this->finderStyle['outerRadius']) ? $this->finderStyle['outerRadius'] : 0)) > 0 ||
            ((isset($this->finderStyle['innerRadius']) ? $this->finderStyle['innerRadius'] : 0)) > 0
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

    private function toASCII($margin = 2) {
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

    private function detectMode($data) {
        if (preg_match('/^\d+$/', $data)) {
            return 'Numeric';
        }
        if (preg_match('/^[0-9A-Z $%*+\-.\/\:]+$/', $data)) {
            return 'Alphanumeric';
        }
        return 'Byte';
    }

    private function addData($data, $mode = 'Byte') {
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

    private function make() {
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

    private function makeImpl($test, $maskPattern) {
        $this->moduleCount = $this->typeNumber * 4 + 17;

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

    private function setupPositionProbePattern($row, $col) {
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

    private function getBestMaskPattern() {
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

    private function setupTimingPattern() {
        for ($r = 8; $r < $this->moduleCount - 8; $r++) {
            if ($this->modules[$r][6] !== null) continue;
            $this->modules[$r][6] = ($r % 2 == 0);
        }
        for ($c = 8; $c < $this->moduleCount - 8; $c++) {
            if ($this->modules[6][$c] !== null) continue;
            $this->modules[6][$c] = ($c % 2 == 0);
        }
    }

    private function setupPositionAdjustPattern() {
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

    private function setupTypeNumber($test) {
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

    private function setupTypeInfo($test, $maskPattern) {
        $data = ($this->errorCorrectionLevel << 3) | $maskPattern;
        $bits = $this->getBCHTypeInfo($data);

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

        $this->modules[$this->moduleCount - 8][8] = !$test;
    }

    private function mapData(array $data, $maskPattern) {
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

    private function createQR8BitByte($data) {
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

    private function createQRNumber($data) {
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

    private function createQRAlphaNum($data) {
        $getCode = function ($c) {
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

    private function stringToUTF8Bytes($str) {
        $bytes = [];
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $bytes[] = ord($str[$i]);
        }
        $utf8 = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
        $bytes = [];
        $rawBytes = unpack('C*', $utf8);
        if ($rawBytes) {
            $bytes = array_values($rawBytes);
        }
        return $bytes;
    }

    private function createData($typeNumber, $errorCorrectionLevel, array $dataList) {
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

        if ($buffer['getLengthInBits']() + 4 <= $totalDataCount * 8) {
            $buffer['put'](0, 4);
        }

        while ($buffer['getLengthInBits']() % 8 != 0) {
            $buffer['putBit'](false);
        }

        while (true) {
            if ($buffer['getLengthInBits']() >= $totalDataCount * 8) break;
            $buffer['put']($PAD0, 8);
            if ($buffer['getLengthInBits']() >= $totalDataCount * 8) break;
            $buffer['put']($PAD1, 8);
        }

        return $this->createBytes($buffer, $rsBlocks);
    }

    private function createBytes(array &$buffer, array $rsBlocks) {
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

    private function getRSBlocks($typeNumber, $errorCorrectionLevel) {
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

    private function getRsBlockTable($typeNumber, $errorCorrectionLevel) {
        switch ($errorCorrectionLevel) {
            case 1: return self::$RS_BLOCK_TABLE[($typeNumber - 1) * 4 + 0];
            case 0: return self::$RS_BLOCK_TABLE[($typeNumber - 1) * 4 + 1];
            case 3: return self::$RS_BLOCK_TABLE[($typeNumber - 1) * 4 + 2];
            case 2: return self::$RS_BLOCK_TABLE[($typeNumber - 1) * 4 + 3];
            default: return null;
        }
    }

    private function createBitBuffer() {
        $state = ['buffer' => [], 'length' => 0];

        $buf = [
            'getBuffer' => function () use (&$state) {
                return $state['buffer'];
            },
            'getAt' => function ($index) use (&$state) {
                $bufIndex = (int)floor($index / 8);
                return ((self::uRightShift($state['buffer'][$bufIndex], 7 - $index % 8)) & 1) == 1;
            },
            'put' => function ($num, $length) use (&$state) {
                for ($i = 0; $i < $length; $i++) {
                    $bit = ((self::uRightShift($num, $length - $i - 1)) & 1) == 1;
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
            'getLengthInBits' => function () use (&$state) {
                return $state['length'];
            },
            'putBit' => function ($bit) use (&$state) {
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

    private function createPolynomial(array $num, $shift) {
        $offset = 0;
        while ($offset < count($num) && $num[$offset] == 0) {
            $offset++;
        }
        $coefficients = [];
        $numLen = count($num) - $offset;
        for ($i = 0; $i < $numLen; $i++) {
            $coefficients[$i] = $num[$i + $offset];
        }
        for ($i = 0; $i < $shift; $i++) {
            $coefficients[] = 0;
        }

        $self = null;
        $self = [
            'getAt' => function ($index) use (&$coefficients) {
                return $coefficients[$index];
            },
            'getLength' => function () use (&$coefficients) {
                return count($coefficients);
            },
            'multiply' => function (array $e) use (&$self) {
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
            'mod' => function (array $e) use (&$self) {
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

                $newPoly = $this->createPolynomial($num, 0);
                return $newPoly['mod']($e);
            },
        ];

        return $self;
    }

    private function getErrorCorrectPolynomial($errorCorrectLength) {
        $a = $this->createPolynomial([1], 0);
        for ($i = 0; $i < $errorCorrectLength; $i++) {
            $a = $a['multiply']($this->createPolynomial([1, self::gexp($i)], 0));
        }
        return $a;
    }

    private static function initMathTables() {
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

    private static function glog($n) {
        if ($n < 1) {
            throw new \RuntimeException("glog({$n})");
        }
        return self::$LOG_TABLE[$n];
    }

    private static function gexp($n) {
        while ($n < 0) {
            $n += 255;
        }
        while ($n >= 256) {
            $n -= 255;
        }
        return self::$EXP_TABLE[$n];
    }

    private static function getBCHDigit($data) {
        $digit = 0;
        while ($data != 0) {
            $digit++;
            $data = self::uRightShift($data, 1);
        }
        return $digit;
    }

    private function getBCHTypeInfo($data) {
        $G15 = (1 << 10) | (1 << 8) | (1 << 5) | (1 << 4) | (1 << 2) | (1 << 1) | (1 << 0);
        $G15_MASK = (1 << 14) | (1 << 12) | (1 << 10) | (1 << 4) | (1 << 1);

        $d = $data << 10;
        while (self::getBCHDigit($d) - self::getBCHDigit($G15) >= 0) {
            $d ^= ($G15 << (self::getBCHDigit($d) - self::getBCHDigit($G15)));
        }
        return (($data << 10) | $d) ^ $G15_MASK;
    }

    private function getBCHTypeNumber($data) {
        $G18 = (1 << 12) | (1 << 11) | (1 << 10) | (1 << 9) | (1 << 8) | (1 << 5) | (1 << 2) | (1 << 0);

        $d = $data << 12;
        while (self::getBCHDigit($d) - self::getBCHDigit($G18) >= 0) {
            $d ^= ($G18 << (self::getBCHDigit($d) - self::getBCHDigit($G18)));
        }
        return ($data << 12) | $d;
    }

    private function getMaskValue($maskPattern, $i, $j) {
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

    private function getLostPoint() {
        $moduleCount = $this->moduleCount;
        $lostPoint = 0;

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

    private function getLengthInBits($mode, $type) {
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

    private function createGifBase64($width, $height, array $data) {
        $gif = $this->createGifBinary($width, $height, $data);
        return base64_encode($gif);
    }

    private function createGifBinary($width, $height, array $data) {
        $out = '';

        $out .= 'GIF87a';

        $out .= pack('v', $width);
        $out .= pack('v', $height);
        $out .= chr(0x80);
        $out .= chr(0);
        $out .= chr(0);

        $out .= chr(0x00) . chr(0x00) . chr(0x00);
        $out .= chr(0xff) . chr(0xff) . chr(0xff);

        $out .= ',';
        $out .= pack('v', 0);
        $out .= pack('v', 0);
        $out .= pack('v', $width);
        $out .= pack('v', $height);
        $out .= chr(0);

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

        $out .= ';';

        return $out;
    }

    private function getLZWRaster($lzwMinCodeSize, array $data) {
        $clearCode = 1 << $lzwMinCodeSize;
        $endCode = (1 << $lzwMinCodeSize) + 1;
        $bitLength = $lzwMinCodeSize + 1;

        $table = [];
        $tableSize = 0;
        for ($i = 0; $i < $clearCode; $i++) {
            $table[chr($i)] = $tableSize++;
        }
        $table[chr($clearCode)] = $tableSize++;
        $table[chr($endCode)] = $tableSize++;

        $outBytes = [];
        $bitBuffer = 0;
        $bitLen = 0;

        $writeBits = function ($data, $length) use (&$outBytes, &$bitBuffer, &$bitLen) {
            if (($data >> $length) != 0 && $length < 32) {
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

        $writeBits($endCode, $bitLength);

        $flushBits();

        return $outBytes;
    }

    private static function uRightShift($a, $b) {
        if ($b == 0) return $a;
        if ($a >= 0) return $a >> $b;
        return ($a >> $b) & (PHP_INT_MAX >> ($b - 1));
    }

    private function hexToRgb($hex) {
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

    private function loadImage($path)
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

    private function imageFilledRoundedRect($img, $x1, $y1, $x2, $y2, $radius, $color) {
        $radius = min($radius, (int)(($x2 - $x1) / 2), (int)(($y2 - $y1) / 2));

        imagefilledrectangle($img, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
        imagefilledrectangle($img, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);

        imagefilledellipse($img, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    }

    private function getMimeType($path) {
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
        return isset($map[$ext]) ? $map[$ext] : 'application/octet-stream';
    }
}
