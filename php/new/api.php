<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/QRCode3.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['download'])) {
    try {
        $text   = isset($_GET['text']) ? $_GET['text'] : 'Hello';
        $ec     = isset($_GET['ec']) ? $_GET['ec'] : 'M';
        $type   = isset($_GET['type']) ? $_GET['type'] : 'png';
        $size   = max(50, min(2000, intval(isset($_GET['size']) ? $_GET['size'] : 200)));
        $quiet  = max(0, min(8, intval(isset($_GET['quiet']) ? $_GET['quiet'] : 2)));
        $fg     = isset($_GET['fg']) ? $_GET['fg'] : '#000000';
        $bg     = isset($_GET['bg']) ? $_GET['bg'] : '#ffffff';

        $qr = new QRCode($text, $ec, $quiet);
        $qr->size($size)->colors($fg, $bg);

        $mr = max(0, min(50, intval(isset($_GET['module_radius']) ? $_GET['module_radius'] : 0))) / 100;
        if ($mr > 0) $qr->moduleRadius($mr);

        $ms = isset($_GET['module_shape']) ? $_GET['module_shape'] : 'square';
        if ($ms !== 'square') $qr->moduleShape($ms);

        $foc = isset($_GET['finder_outer_color']) ? $_GET['finder_outer_color'] : null;
        $fic = isset($_GET['finder_inner_color']) ? $_GET['finder_inner_color'] : null;
        $forRaw = isset($_GET['finder_outer_radius']) ? intval($_GET['finder_outer_radius']) : null;
        $firRaw = isset($_GET['finder_inner_radius']) ? intval($_GET['finder_inner_radius']) : null;
        $fOR = $forRaw !== null ? max(0, min(50, $forRaw)) / 100 : null;
        $fIR = $firRaw !== null ? max(0, min(50, $firRaw)) / 100 : null;
        if ($foc || $fic || $fOR !== null || $fIR !== null) {
            $qr->finderStyle($foc, $fic, $fOR, $fIR);
        }

        switch ($type) {
            case 'svg':
                header('Content-Type: image/svg+xml');
                header('Content-Disposition: attachment; filename="qrcode.svg"');
                echo $qr->render('svg');
                break;
            case 'webp':
                header('Content-Type: image/webp');
                header('Content-Disposition: attachment; filename="qrcode.webp"');
                echo $qr->render('webp');
                break;
            default:
                header('Content-Type: image/png');
                header('Content-Disposition: attachment; filename="qrcode.png"');
                echo $qr->render('png');
        }
    } catch (Exception $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(array('error' => $e->getMessage()));
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $text   = isset($_POST['text']) ? $_POST['text'] : '';
        $ec     = isset($_POST['ec']) ? $_POST['ec'] : 'M';
        $format = isset($_POST['format']) ? $_POST['format'] : 'png';
        $size   = max(50, min(2000, intval(isset($_POST['size']) ? $_POST['size'] : 200)));
        $quiet  = max(0, min(8, intval(isset($_POST['quiet']) ? $_POST['quiet'] : 2)));
        $fg     = isset($_POST['fg']) ? $_POST['fg'] : '#000000';
        $bg     = isset($_POST['bg']) ? $_POST['bg'] : '#ffffff';
        $mode   = isset($_POST['mode']) ? $_POST['mode'] : 'none';

        if (empty($text)) throw new InvalidArgumentException('Text content is required.');

        $qr = new QRCode($text, $ec, $quiet);
        $qr->size($size)->colors($fg, $bg);

        $mr = max(0, min(50, intval(isset($_POST['module_radius']) ? $_POST['module_radius'] : 0))) / 100;
        if ($mr > 0) $qr->moduleRadius($mr);

        $ms = isset($_POST['module_shape']) ? $_POST['module_shape'] : 'square';
        if ($ms !== 'square') $qr->moduleShape($ms);

        $foc = !empty($_POST['finder_outer_color']) ? $_POST['finder_outer_color'] : null;
        $fic = !empty($_POST['finder_inner_color']) ? $_POST['finder_inner_color'] : null;
        $finderEnabled = (isset($_POST['finder_enabled']) ? $_POST['finder_enabled'] : '0') === '1';
        $forRaw = isset($_POST['finder_outer_radius']) ? intval($_POST['finder_outer_radius']) : null;
        $firRaw = isset($_POST['finder_inner_radius']) ? intval($_POST['finder_inner_radius']) : null;
        $fOR = $forRaw !== null ? max(0, min(50, $forRaw)) / 100 : null;
        $fIR = $firRaw !== null ? max(0, min(50, $firRaw)) / 100 : null;
        if ($finderEnabled && ($foc || $fic || $fOR !== null || $fIR !== null)) {
            $qr->finderStyle($foc, $fic, $fOR, $fIR);
        }

        $margin = max(0, min(40, intval(isset($_POST['margin']) ? $_POST['margin'] : 0)));
        if ($margin > 0) $qr->margin($margin);

        $webpQ = max(10, min(100, intval(isset($_POST['webp_quality']) ? $_POST['webp_quality'] : 80)));
        $qr->quality($webpQ);

        $svgScalable = (isset($_POST['svg_scalable']) ? $_POST['svg_scalable'] : '1') === '1';
        $qr->scalable($svgScalable);

        $svgTitle = !empty($_POST['svg_title']) ? $_POST['svg_title'] : null;
        $svgDesc = !empty($_POST['svg_desc']) ? $_POST['svg_desc'] : null;
        if ($svgTitle || $svgDesc) $qr->accessibility($svgTitle, $svgDesc);

        $logoTmp = null;
        $embedDesc = '';
        if ($mode === 'logo' && isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoTmp = $_FILES['logo']['tmp_name'];
            $embedDesc = 'Logo: ' . basename($_FILES['logo']['name']);
            $logoRatio = max(5, min(35, intval(isset($_POST['logo_ratio']) ? $_POST['logo_ratio'] : 20))) / 100;
            $logoPadding = max(0, min(20, intval(isset($_POST['logo_padding']) ? $_POST['logo_padding'] : 6)));
            $logoRadiusPct = max(0, min(50, intval(isset($_POST['logo_radius']) ? $_POST['logo_radius'] : 15)));
            $qr->logo($logoTmp, $logoRatio, $logoPadding, $logoRadiusPct);
        }

        $label = '';
        if ($mode === 'label' && !empty($_POST['label'])) {
            $label = $_POST['label'];
            $embedDesc = 'Label: ' . $label;
            $labelSize = max(5, min(25, intval(isset($_POST['label_size']) ? $_POST['label_size'] : 10))) / 100;
            $fontColor = isset($_POST['font_color']) ? $_POST['font_color'] : '#000000';
            $labelStrip = (isset($_POST['label_strip']) ? $_POST['label_strip'] : '0') === '1';
            $qr->label($label, $labelSize, $fontColor, null, 'Inter, Arial, sans-serif', $labelStrip);
        }

        $output = '';
        $contentType = $format;

        if ($format === 'svg') {
            $output = $qr->render('svg');
        } elseif ($format === 'webp') {
            $data = $qr->render('webp');
            $output = 'data:image/webp;base64,' . base64_encode($data);
        } else {
            $data = $qr->render('png');
            $output = 'data:image/png;base64,' . base64_encode($data);
            $contentType = 'png';
        }

        $qrInfo = $qr->info();

        echo json_encode(array(
            'success'     => true,
            'type'        => $contentType,
            'data'        => $output,
            'modules'     => $qr->getModuleCount(),
            'ec'          => $ec,
            'format'      => strtoupper($format),
            'embed'       => $embedDesc,
            'info'        => $qrInfo,
        ), JSON_UNESCAPED_SLASHES);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(array('success' => false, 'error' => $e->getMessage()));
    }
    exit;
}

http_response_code(405);
header('Content-Type: application/json');
echo json_encode(array('error' => 'Method not allowed. Use POST to generate, GET with ?download to download.'));
