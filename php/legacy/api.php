<?php
/**
 * QR Code Generator API
 * 
 * Endpoints:
 *   POST /api.php           → Generate QR code (returns JSON with data URI)
 *   GET  /api.php?download  → Download QR code file (PNG/SVG/WEBP)
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/QRCode.php';

// ================================================================
// GET → Download file
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['download'])) {
    try {
        $text   = $_GET['text'] ?? 'Hello';
        $ec     = $_GET['ec'] ?? 'M';
        $type   = $_GET['type'] ?? 'png';
        $size   = max(50, min(2000, intval($_GET['size'] ?? 200)));
        $quiet  = max(0, min(8, intval($_GET['quiet'] ?? 2)));
        $fg     = $_GET['fg'] ?? '#000000';
        $bg     = $_GET['bg'] ?? '#ffffff';

        $qr = new QRCode($text, $ec, $quiet);

        $moduleRadius = max(0, min(50, intval($_GET['module_radius'] ?? 0))) / 100;
        if ($moduleRadius > 0) {
            $qr->setModuleRadius($moduleRadius);
        }

        $moduleShape = $_GET['module_shape'] ?? 'square';
        if (in_array($moduleShape, ['dot', 'diamond'])) {
            $qr->setModuleShape($moduleShape);
        }

        // Finder pattern styling
        $finderOuterColor = $_GET['finder_outer_color'] ?? null;
        $finderInnerColor = $_GET['finder_inner_color'] ?? null;
        $finderOuterRadius = isset($_GET['finder_outer_radius']) ? max(0, min(50, intval($_GET['finder_outer_radius']))) / 100 : null;
        $finderInnerRadius = isset($_GET['finder_inner_radius']) ? max(0, min(50, intval($_GET['finder_inner_radius']))) / 100 : null;

        if ($finderOuterColor || $finderInnerColor || $finderOuterRadius !== null || $finderInnerRadius !== null) {
            $style = [];
            if ($finderOuterColor) $style['outerColor'] = $finderOuterColor;
            if ($finderInnerColor) $style['innerColor'] = $finderInnerColor;
            if ($finderOuterRadius !== null) $style['outerRadius'] = $finderOuterRadius;
            if ($finderInnerRadius !== null) $style['innerRadius'] = $finderInnerRadius;
            $qr->setFinderStyle($style);
        }

        if ($type === 'svg') {
            header('Content-Type: image/svg+xml');
            header('Content-Disposition: attachment; filename="qrcode.svg"');
            echo $qr->toSVG($size, $fg, $bg);
        } elseif ($type === 'webp') {
            header('Content-Type: image/webp');
            header('Content-Disposition: attachment; filename="qrcode.webp"');
            echo $qr->toWEBP(null, $size, $fg, $bg);
        } else {
            header('Content-Type: image/png');
            header('Content-Disposition: attachment; filename="qrcode.png"');
            echo $qr->toPNG(null, $size, $fg, $bg);
        }
    } catch (Exception $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// ================================================================
// POST → Generate QR code (JSON response)
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $text   = $_POST['text'] ?? '';
        $ec     = $_POST['ec'] ?? 'M';
        $format = $_POST['format'] ?? 'png';
        $size   = max(50, min(2000, intval($_POST['size'] ?? 200)));
        $quiet  = max(0, min(8, intval($_POST['quiet'] ?? 2)));
        $fg     = $_POST['fg'] ?? '#000000';
        $bg     = $_POST['bg'] ?? '#ffffff';
        $mode   = $_POST['mode'] ?? 'none';

        if (empty($text)) {
            throw new InvalidArgumentException('Text content is required.');
        }

        $qr = new QRCode($text, $ec, $quiet);

        // Module radius: 0 = square, 50 = circle
        $moduleRadius = max(0, min(50, intval($_POST['module_radius'] ?? 0))) / 100;
        if ($moduleRadius > 0) {
            $qr->setModuleRadius($moduleRadius);
        }

        // Module shape: square, dot, diamond
        $moduleShape = $_POST['module_shape'] ?? 'square';
        if (in_array($moduleShape, ['dot', 'diamond'])) {
            $qr->setModuleShape($moduleShape);
        }

        // Finder pattern styling
        $finderOuterColor = !empty($_POST['finder_outer_color']) ? $_POST['finder_outer_color'] : null;
        $finderInnerColor = !empty($_POST['finder_inner_color']) ? $_POST['finder_inner_color'] : null;
        $finderEnabled = ($_POST['finder_enabled'] ?? '0') === '1';
        $finderOuterRadiusRaw = isset($_POST['finder_outer_radius']) ? intval($_POST['finder_outer_radius']) : null;
        $finderInnerRadiusRaw = isset($_POST['finder_inner_radius']) ? intval($_POST['finder_inner_radius']) : null;
        $finderOuterRadius = ($finderOuterRadiusRaw !== null) ? max(0, min(50, $finderOuterRadiusRaw)) / 100 : null;
        $finderInnerRadius = ($finderInnerRadiusRaw !== null) ? max(0, min(50, $finderInnerRadiusRaw)) / 100 : null;

        if ($finderEnabled && ($finderOuterColor || $finderInnerColor || $finderOuterRadius !== null || $finderInnerRadius !== null)) {
            $style = [];
            if ($finderOuterColor) $style['outerColor'] = $finderOuterColor;
            if ($finderInnerColor) $style['innerColor'] = $finderInnerColor;
            if ($finderOuterRadius !== null) $style['outerRadius'] = $finderOuterRadius;
            if ($finderInnerRadius !== null) $style['innerRadius'] = $finderInnerRadius;
            $qr->setFinderStyle($style);
        }

        // Extra margin
        $margin = max(0, min(40, intval($_POST['margin'] ?? 0)));
        if ($margin > 0) {
            $qr->setMargin($margin);
        }

        // WEBP quality
        $webpQuality = max(10, min(100, intval($_POST['webp_quality'] ?? 80)));
        $qr->setQuality($webpQuality);

        // SVG scalable
        $svgScalable = ($_POST['svg_scalable'] ?? '1') === '1';
        $qr->setScalable($svgScalable);

        // SVG accessibility
        $svgTitle = !empty($_POST['svg_title']) ? $_POST['svg_title'] : null;
        $svgDesc = !empty($_POST['svg_desc']) ? $_POST['svg_desc'] : null;
        if ($svgTitle || $svgDesc) {
            $qr->setAccessibility($svgTitle, $svgDesc);
        }

        $embedDesc = '';
        $output = '';
        $contentType = '';

        // Handle logo upload
        $logoTmp = null;
        if ($mode === 'logo' && isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoTmp = $_FILES['logo']['tmp_name'];
            $embedDesc = 'Logo: ' . basename($_FILES['logo']['name']);
        }

        // Handle label
        $label = '';
        if ($mode === 'label' && !empty($_POST['label'])) {
            $label = $_POST['label'];
            $embedDesc = 'Label: ' . $label;
        }

        $logoRatio   = max(5, min(35, intval($_POST['logo_ratio'] ?? 20))) / 100;
        $logoPadding = max(0, min(20, intval($_POST['logo_padding'] ?? 6)));
        $logoRadiusPct = max(0, min(50, intval($_POST['logo_radius'] ?? 15)));
        $labelSize   = max(5, min(25, intval($_POST['label_size'] ?? 10))) / 100;
        $fontColor   = $_POST['font_color'] ?? '#000000';
        $labelStrip  = ($_POST['label_strip'] ?? '0') === '1';

        // Calculate logo radius in pixels from percentage of logo size
        $logoSizePx  = (int)($size * $logoRatio);
        $logoRadius  = (int)round($logoSizePx * $logoRadiusPct / 100);

        if ($format === 'svg') {
            $contentType = 'svg';
            if ($mode === 'logo' && $logoTmp) {
                $output = $qr->toSVGWithLogo($logoTmp, $size, $logoRatio, $fg, $bg, $logoPadding, $logoRadius, false);
            } elseif ($mode === 'label' && $label !== '') {
                $output = $qr->toSVGWithLabel($label, $size, $labelSize, $fontColor, $fg, $bg, 'Inter, Arial, sans-serif', $labelStrip, false);
            } else {
                $output = $qr->toSVG($size, $fg, $bg, false);
            }
        } elseif ($format === 'webp') {
            $contentType = 'webp';
            if ($mode === 'logo' && $logoTmp) {
                // For WEBP with logo, generate as PNG first, then note WEBP format
                $pngData = $qr->toPNGWithLogo($logoTmp, null, $size, $logoRatio, $fg, $bg, $logoPadding, $logoRadius);
                $output = 'data:image/png;base64,' . base64_encode($pngData);
                $contentType = 'png'; // Fallback for logo+WEBP
            } elseif ($mode === 'label' && $label !== '') {
                $pngData = $qr->toPNGWithLabel($label, null, $size, $labelSize, $fontColor, $fg, $bg, null, $labelStrip);
                $output = 'data:image/png;base64,' . base64_encode($pngData);
                $contentType = 'png';
            } else {
                $webpData = $qr->toWEBP(null, $size, $fg, $bg);
                $output = 'data:image/webp;base64,' . base64_encode($webpData);
            }
        } else {
            $contentType = 'png';
            if ($mode === 'logo' && $logoTmp) {
                $pngData = $qr->toPNGWithLogo($logoTmp, null, $size, $logoRatio, $fg, $bg, $logoPadding, $logoRadius);
            } elseif ($mode === 'label' && $label !== '') {
                $pngData = $qr->toPNGWithLabel($label, null, $size, $labelSize, $fontColor, $fg, $bg, null, $labelStrip);
            } else {
                $pngData = $qr->toPNG(null, $size, $fg, $bg);
            }
            $output = 'data:image/png;base64,' . base64_encode($pngData);
        }

        // Get QR info
        $qrInfo = $qr->getInfo();

        echo json_encode([
            'success'     => true,
            'type'        => $contentType,
            'data'        => $output,
            'modules'     => $qr->getModuleCount(),
            'ec'          => $ec,
            'format'      => strtoupper($format),
            'embed'       => $embedDesc,
            'info'        => $qrInfo,
        ], JSON_UNESCAPED_SLASHES);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Fallback
http_response_code(405);
header('Content-Type: application/json');
echo json_encode(['error' => 'Method not allowed. Use POST to generate, GET with ?download to download.']);
