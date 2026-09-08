<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="QR Code Generator — Generate QR codes with logos, labels, finder pattern styling, custom colors, and multiple output formats.">
    <title>QR Code Generator (Old Method)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
        :root{
            --bg:#0a0a0f;--card:rgba(22,22,35,.7);--input:rgba(255,255,255,.04);
            --bdr:rgba(255,255,255,.06);--bdr-f:rgba(139,92,246,.5);
            --t1:#f0f0f5;--t2:#8b8b9e;--t3:#5a5a6e;
            --p:#8b5cf6;--pd:#6d28d9;--pg:rgba(139,92,246,.15);
            --cy:#06b6d4;--em:#10b981;--ro:#f43f5e;--am:#f59e0b;
            --r:12px;--rx:24px
        }
        body{font-family:'Inter',-apple-system,sans-serif;background:var(--bg);color:var(--t1);min-height:100vh;-webkit-font-smoothing:antialiased}

        /* BG */
        .bgfx{position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden}
        .orb{position:absolute;border-radius:50%;filter:blur(100px);animation:of 20s ease-in-out infinite}
        .o1{width:600px;height:600px;background:radial-gradient(circle,var(--p),transparent 70%);top:-200px;right:-100px;opacity:.3}
        .o2{width:500px;height:500px;background:radial-gradient(circle,var(--cy),transparent 70%);bottom:-150px;left:-100px;opacity:.2;animation-delay:-7s}
        .o3{width:400px;height:400px;background:radial-gradient(circle,var(--ro),transparent 70%);top:50%;left:50%;transform:translate(-50%,-50%);opacity:.08;animation-delay:-14s}
        .grd{position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.02) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.02) 1px,transparent 1px);background-size:60px 60px}
        @keyframes of{0%,100%{transform:translate(0,0) scale(1)}25%{transform:translate(30px,-40px) scale(1.05)}50%{transform:translate(-20px,20px) scale(.95)}75%{transform:translate(40px,30px) scale(1.02)}}

        .wrap{position:relative;z-index:1;max-width:1200px;margin:0 auto;padding:40px 24px 80px}

        /* Header */
        .hdr{text-align:center;margin-bottom:48px;animation:fu .6s ease}
        .badge{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:100px;background:var(--pg);border:1px solid rgba(139,92,246,.2);font-size:12px;font-weight:600;color:var(--p);letter-spacing:.5px;text-transform:uppercase;margin-bottom:20px}
        .hdr h1{font-size:clamp(32px,5vw,48px);font-weight:800;letter-spacing:-1.5px;line-height:1.1;margin-bottom:12px;background:linear-gradient(135deg,var(--t1) 0%,var(--p) 50%,var(--cy) 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .hdr p{font-size:16px;color:var(--t2);max-width:560px;margin:0 auto;line-height:1.6}

        .grid{display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start}
        @media(max-width:768px){.grid{grid-template-columns:1fr}}

        /* Card */
        .card{background:var(--card);border:1px solid var(--bdr);border-radius:var(--rx);padding:32px;backdrop-filter:blur(20px);transition:.3s}
        .card:hover{border-color:rgba(255,255,255,.1);box-shadow:0 0 40px rgba(139,92,246,.1)}
        .ct{font-size:18px;font-weight:700;margin-bottom:24px;display:flex;align-items:center;gap:10px}
        .ico{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px}
        .ico-p{background:rgba(139,92,246,.15);color:var(--p)}
        .ico-c{background:rgba(6,182,212,.15);color:var(--cy)}

        /* Form */
        .fg{margin-bottom:16px}.fg:last-child{margin-bottom:0}
        .fl{display:block;font-size:13px;font-weight:600;color:var(--t2);margin-bottom:7px;letter-spacing:.3px}
        .fi,.fs,.ft{width:100%;padding:11px 15px;background:var(--input);border:1px solid var(--bdr);border-radius:var(--r);color:var(--t1);font-family:inherit;font-size:14px;outline:none;transition:.15s}
        .ft{resize:vertical;min-height:76px;line-height:1.5}
        .fi:focus,.fs:focus,.ft:focus{border-color:var(--bdr-f);background:rgba(139,92,246,.04);box-shadow:0 0 0 3px rgba(139,92,246,.1)}
        .fs{cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238b8b9e' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;padding-right:36px}
        .fs option{background:#12121a;color:var(--t1)}
        .r2{display:grid;grid-template-columns:1fr 1fr;gap:12px}

        /* Color */
        .ci{display:flex;align-items:center;gap:10px;padding:8px 12px;background:var(--input);border:1px solid var(--bdr);border-radius:var(--r);transition:.15s}
        .ci:focus-within{border-color:var(--bdr-f);box-shadow:0 0 0 3px rgba(139,92,246,.1)}
        .cs{width:28px;height:28px;border-radius:6px;border:2px solid rgba(255,255,255,.1);cursor:pointer;padding:0;overflow:hidden;flex-shrink:0}
        .cs::-webkit-color-swatch-wrapper{padding:0}.cs::-webkit-color-swatch{border:none;border-radius:4px}
        .ch{background:none;border:none;color:var(--t1);font-family:'Inter',monospace;font-size:13px;font-weight:500;width:100%;outline:none}

        /* Range */
        .rw{display:flex;align-items:center;gap:12px}
        .rs{flex:1;-webkit-appearance:none;appearance:none;height:6px;border-radius:3px;background:rgba(255,255,255,.08);outline:none}
        .rs::-webkit-slider-thumb{-webkit-appearance:none;width:18px;height:18px;border-radius:50%;background:var(--p);cursor:pointer;box-shadow:0 2px 8px rgba(139,92,246,.4)}
        .rv{font-size:13px;font-weight:600;color:var(--p);min-width:32px;text-align:center;background:rgba(139,92,246,.1);padding:4px 8px;border-radius:6px}

        /* File */
        .flbl{display:flex;align-items:center;justify-content:center;gap:8px;padding:11px 15px;border:1px dashed rgba(255,255,255,.12);border-radius:var(--r);color:var(--t2);font-size:13px;cursor:pointer;transition:.15s}
        .flbl:hover{border-color:var(--p);color:var(--p);background:rgba(139,92,246,.04)}
        .flbl svg{width:16px;height:16px}
        .finp{position:absolute;inset:0;opacity:0;cursor:pointer}
        .fname{font-size:12px;color:var(--em);margin-top:6px;word-break:break-all}

        /* Buttons */
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:14px 28px;border:none;border-radius:var(--r);font-family:inherit;font-size:14px;font-weight:600;cursor:pointer;transition:.3s;letter-spacing:.2px}
        .btn-p{width:100%;background:linear-gradient(135deg,var(--p),var(--pd));color:#fff;box-shadow:0 4px 16px rgba(139,92,246,.3)}
        .btn-p:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(139,92,246,.45)}
        .btn-p:active{transform:translateY(0)}
        .btn-p:disabled{opacity:.6;cursor:not-allowed;transform:none;box-shadow:none}
        .btn-p svg{width:18px;height:18px}

        /* Toggle switch */
        .tgl{display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;font-weight:500;color:var(--t2)}
        .tgl input{display:none}
        .tgl-sw{position:relative;width:40px;height:22px;background:rgba(255,255,255,.08);border-radius:11px;transition:.3s;flex-shrink:0}
        .tgl-sw::after{content:'';position:absolute;top:3px;left:3px;width:16px;height:16px;background:#666;border-radius:50%;transition:.3s}
        .tgl input:checked+.tgl-sw{background:rgba(139,92,246,.4)}
        .tgl input:checked+.tgl-sw::after{transform:translateX(18px);background:var(--p)}

        /* Preview */
        .prev{display:flex;flex-direction:column;align-items:center;gap:24px}
        .qrf{position:relative;background:#fff;border-radius:16px;padding:24px;box-shadow:0 8px 32px rgba(0,0,0,.4),0 0 40px rgba(139,92,246,.1);transition:box-shadow .3s;display:flex;align-items:center;justify-content:center;min-width:240px;min-height:240px}
        .qrf:hover{box-shadow:0 16px 48px rgba(0,0,0,.5),0 0 60px rgba(139,92,246,.15)}
        #qrResult{display:flex;align-items:center;justify-content:center}
        #qrResult img,#qrResult svg{display:block;max-width:100%;height:auto}
        .qph{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;color:var(--t3);text-align:center;padding:20px}
        .qph svg{width:48px;height:48px;opacity:.3}
        .qph p{font-size:14px;line-height:1.5}

        /* Spinner */
        .spin{width:36px;height:36px;border:3px solid rgba(139,92,246,.2);border-top-color:var(--p);border-radius:50%;animation:sp .7s linear infinite;display:none}
        @keyframes sp{to{transform:rotate(360deg)}}

        /* Downloads */
        .dlg{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;width:100%;display:none}
        .dl{display:flex;align-items:center;justify-content:center;gap:6px;padding:10px 16px;background:rgba(255,255,255,.05);border:1px solid var(--bdr);border-radius:var(--r);color:var(--t1);font-family:inherit;font-size:13px;font-weight:500;cursor:pointer;transition:.15s;text-decoration:none}
        .dl:hover{background:rgba(139,92,246,.1);border-color:rgba(139,92,246,.3);color:var(--p)}
        .dl svg{width:16px;height:16px}
        .dl.fw{grid-column:1/-1}

        /* Info */
        .infobar{display:none;flex-wrap:wrap;gap:8px;justify-content:center;padding-top:16px;border-top:1px solid var(--bdr);width:100%}
        .chip{display:flex;align-items:center;gap:6px;font-size:11px;color:var(--t2);padding:4px 10px;background:rgba(255,255,255,.03);border-radius:100px}
        .dot{width:6px;height:6px;border-radius:50%}
        .dp{background:var(--p)}.dc{background:var(--cy)}.de{background:var(--em)}.da{background:var(--am)}.dr{background:var(--ro)}

        /* Embed sections */
        .eopt{display:none;margin-top:12px;padding-top:12px;border-top:1px solid var(--bdr)}
        .eopt.on{display:block}
        .sep{height:1px;background:var(--bdr);margin:14px 0}
        .checker{background-color:#fff;background-image:linear-gradient(45deg,#d0d0d0 25%,transparent 25%,transparent 75%,#d0d0d0 75%),linear-gradient(45deg,#d0d0d0 25%,transparent 25%,transparent 75%,#d0d0d0 75%);background-size:16px 16px;background-position:0 0,8px 8px}

        /* Section header */
        .sh{font-size:13px;font-weight:700;color:var(--t2);letter-spacing:.5px;text-transform:uppercase;margin-bottom:12px;display:flex;align-items:center;gap:8px}
        .sh::after{content:'';flex:1;height:1px;background:var(--bdr)}

        /* Toast */
        .toast{position:fixed;bottom:24px;right:24px;padding:12px 20px;background:var(--card);border:1px solid rgba(16,185,129,.3);border-radius:var(--r);color:var(--em);font-size:13px;font-weight:500;backdrop-filter:blur(20px);box-shadow:0 8px 32px rgba(0,0,0,.4);transform:translateY(100px);opacity:0;transition:.3s;z-index:1000}
        .toast.show{transform:translateY(0);opacity:1}
        .toast.err{border-color:rgba(244,63,94,.3);color:var(--ro)}

        /* Error msg */
        .errmsg{color:var(--ro);font-size:13px;text-align:center;padding:8px;display:none}

        /* Anim */
        @keyframes fu{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
        .anim{animation:fu .6s ease forwards}
        .ad1{animation-delay:.1s;opacity:0}
        .ad2{animation-delay:.2s;opacity:0}

        .footer{text-align:center;margin-top:48px;padding-top:24px;border-top:1px solid var(--bdr);color:var(--t3);font-size:13px}
        .footer a{color:var(--p);text-decoration:none}
        .footer a:hover{text-decoration:underline}

        /* Sticky preview on desktop */
        @media(min-width:769px){.card.sticky{position:sticky;top:24px}}
    </style>
</head>
<body>
    <div class="bgfx"><div class="orb o1"></div><div class="orb o2"></div><div class="orb o3"></div><div class="grd"></div></div>

    <div class="wrap">
        <header class="hdr">
            <div class="badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="3" height="3"/><line x1="21" y1="14" x2="21" y2="21"/><line x1="14" y1="21" x2="21" y2="21"/></svg>
                PHP QR Code Generator
            </div>
            <h1>Generate QR Codes</h1>
            <p>Create beautiful QR codes with embedded logos, text labels, finder pattern styling, and custom colors — powered by pure PHP.</p>
        </header>

        <div class="grid">
            <!-- ========== Settings ========== -->
            <div class="card anim ad1">
                <div class="ct"><div class="ico ico-p">⚙</div> Settings</div>

                <div class="fg">
                    <label class="fl">Content</label>
                    <textarea class="ft" id="text" placeholder="Enter URL, text, or any data…">https://cyberzilla.github.io/qrcode</textarea>
                </div>

                <div class="r2">
                    <div class="fg">
                        <label class="fl">Error Correction</label>
                        <select class="fs" id="ec">
                            <option value="L">Low (7%)</option>
                            <option value="M" selected>Medium (15%)</option>
                            <option value="Q">Quartile (25%)</option>
                            <option value="H">High (30%)</option>
                        </select>
                    </div>
                    <div class="fg">
                        <label class="fl">Output Format</label>
                        <select class="fs" id="format">
                            <option value="png" selected>PNG Image</option>
                            <option value="svg">SVG Vector</option>
                            <option value="webp">WEBP Image</option>
                        </select>
                    </div>
                </div>

                <!-- Format-dependent options -->
                <div class="fg" id="webpQualityGroup" style="display:none">
                    <label class="fl">WEBP Quality: <span id="wqv">80</span>%</label>
                    <div class="rw"><input type="range" class="rs" id="webpQuality" min="10" max="100" value="80"><span class="rv" id="wqb">80</span></div>
                </div>
                <div id="svgOpts" style="display:none">
                    <div class="fg">
                        <label style="display:flex;align-items:center;gap:6px;font-size:.82rem;color:var(--t2);cursor:pointer">
                            <input type="checkbox" id="svgScalable" style="accent-color:var(--p)" checked> Scalable (responsive SVG)
                        </label>
                    </div>
                    <div class="r2">
                        <div class="fg">
                            <label class="fl">SVG Title</label>
                            <input type="text" class="fi" id="svgTitle" placeholder="e.g. QR Code">
                        </div>
                        <div class="fg">
                            <label class="fl">SVG Desc</label>
                            <input type="text" class="fi" id="svgDesc" placeholder="e.g. Scan to visit…">
                        </div>
                    </div>
                </div>

                <div class="fg">
                    <label class="fl">Output Size: <span id="sv">200</span>px</label>
                    <div class="rw"><input type="range" class="rs" id="size" min="50" max="600" value="200" step="10"><span class="rv" id="sb">200</span></div>
                </div>

                <div class="fg">
                    <label class="fl">Quiet Zone: <span id="qv">2</span></label>
                    <div class="rw"><input type="range" class="rs" id="quiet" min="0" max="8" value="2"><span class="rv" id="qb">2</span></div>
                </div>

                <div class="fg">
                    <label class="fl">Extra Margin: <span id="mgv">0</span>px</label>
                    <div class="rw"><input type="range" class="rs" id="margin" min="0" max="40" value="0" step="2"><span class="rv" id="mgb">0</span></div>
                </div>

                <div class="r2">
                    <div class="fg">
                        <label class="fl">Foreground</label>
                        <div class="ci">
                            <input type="color" class="cs" id="fgSw" value="#000000">
                            <input type="text" class="ch" id="fg" value="#000000" maxlength="7">
                        </div>
                    </div>
                    <div class="fg">
                        <label class="fl">Background</label>
                        <div class="ci">
                            <input type="color" class="cs" id="bgSw" value="#ffffff">
                            <input type="text" class="ch" id="bg" value="#ffffff" maxlength="7">
                        </div>
                        <label style="display:flex;align-items:center;gap:6px;margin-top:6px;font-size:.82rem;color:var(--t2);cursor:pointer">
                            <input type="checkbox" id="bgTransparent" style="accent-color:var(--p)"> Transparent
                        </label>
                    </div>
                </div>

                <div class="fg">
                    <label class="fl">Module Shape</label>
                    <select class="sel" id="modShape">
                        <option value="square" selected>■ Square</option>
                        <option value="dot">● Dot</option>
                        <option value="diamond">◆ Diamond</option>
                    </select>
                </div>

                <div class="fg" id="radiusGroup">
                    <label class="fl">Module Radius: <span id="mrv">0</span>% <span id="mrl" style="color:var(--t3);font-weight:400">— Sharp</span></label>
                    <div class="rw"><input type="range" class="rs" id="modRadius" min="0" max="50" value="0"><span class="rv" id="mrb">0</span></div>
                </div>

                <div class="sep"></div>

                <!-- ===== Finder Pattern Styling ===== -->
                <div class="fg">
                    <label class="tgl">
                        <input type="checkbox" id="finderEnabled">
                        <span class="tgl-sw"></span>
                        Finder Pattern (Eye) Styling
                    </label>
                </div>

                <div class="eopt" id="finderOpts">
                    <div class="r2">
                        <div class="fg">
                            <label class="fl">Outer Frame Color</label>
                            <div class="ci">
                                <input type="color" class="cs" id="finderOuterSw" value="#FF0000">
                                <input type="text" class="ch" id="finderOuterColor" value="#FF0000" maxlength="7">
                            </div>
                        </div>
                        <div class="fg">
                            <label class="fl">Inner Dot Color</label>
                            <div class="ci">
                                <input type="color" class="cs" id="finderInnerSw" value="#0000FF">
                                <input type="text" class="ch" id="finderInnerColor" value="#0000FF" maxlength="7">
                            </div>
                        </div>
                    </div>
                    <div class="r2">
                        <div class="fg">
                            <label class="fl">Outer Radius: <span id="forv">25</span>%</label>
                            <div class="rw"><input type="range" class="rs" id="finderOuterRadius" min="0" max="50" value="25"><span class="rv" id="forb">25</span></div>
                        </div>
                        <div class="fg">
                            <label class="fl">Inner Radius: <span id="firv">25</span>%</label>
                            <div class="rw"><input type="range" class="rs" id="finderInnerRadius" min="0" max="50" value="25"><span class="rv" id="firb">25</span></div>
                        </div>
                    </div>
                </div>

                <div class="sep"></div>

                <!-- Embed Mode -->
                <div class="fg">
                    <label class="fl">Center Embed</label>
                    <select class="fs" id="mode">
                        <option value="none" selected>None</option>
                        <option value="logo">Logo / Image</option>
                        <option value="label">Text Label</option>
                    </select>
                </div>

                <!-- Logo opts -->
                <div class="eopt" id="logoOpts">
                    <div class="fg">
                        <label class="fl">Upload Logo</label>
                        <div style="position:relative">
                            <label class="flbl" id="logoLbl">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                Choose image file…
                            </label>
                            <input type="file" class="finp" id="logo" accept="image/png,image/jpeg,image/gif,image/webp">
                            <div class="fname" id="logoName"></div>
                        </div>
                    </div>
                    <div class="fg">
                        <label class="fl">Logo Size: <span id="lsv">20</span>%</label>
                        <div class="rw"><input type="range" class="rs" id="logoRatio" min="5" max="35" value="20"><span class="rv" id="lsb">20</span></div>
                    </div>
                    <div class="r2">
                        <div class="fg">
                            <label class="fl">Padding: <span id="lpv">6</span>px</label>
                            <div class="rw"><input type="range" class="rs" id="logoPad" min="0" max="20" value="6"><span class="rv" id="lpb">6</span></div>
                        </div>
                        <div class="fg">
                            <label class="fl">Radius: <span id="lrv">15</span>%</label>
                            <div class="rw"><input type="range" class="rs" id="logoRad" min="0" max="50" value="15"><span class="rv" id="lrb">15</span></div>
                        </div>
                    </div>
                </div>

                <!-- Label opts -->
                <div class="eopt" id="labelOpts">
                    <div class="fg">
                        <label class="fl">Label Text</label>
                        <input type="text" class="fi" id="label" placeholder="e.g. SCAN ME">
                    </div>
                    <div class="r2">
                        <div class="fg">
                            <label class="fl">Font Size: <span id="fsv">10</span>%</label>
                            <div class="rw"><input type="range" class="rs" id="labelSize" min="5" max="25" value="10"><span class="rv" id="fsb">10</span></div>
                        </div>
                        <div class="fg">
                            <label class="fl">Label Color</label>
                            <div class="ci">
                                <input type="color" class="cs" id="fcSw" value="#000000">
                                <input type="text" class="ch" id="fontColor" value="#000000" maxlength="7">
                            </div>
                        </div>
                    </div>
                    <div class="fg" style="margin-top:8px">
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500;color:var(--t2);cursor:pointer">
                            <input type="checkbox" id="labelStrip" style="accent-color:var(--p);width:16px;height:16px">
                            Full-width strip
                        </label>
                    </div>
                </div>

                <div class="fg" style="margin-top:16px">
                    <button class="btn btn-p" id="genBtn" onclick="generate()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                        Generate QR Code
                    </button>
                </div>
            </div>

            <!-- ========== Preview ========== -->
            <div class="card anim ad2 sticky">
                <div class="ct"><div class="ico ico-c">◫</div> Preview</div>

                <div class="prev">
                    <div class="qrf" id="qrFrame">
                        <div class="qph" id="placeholder">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="3" height="3"/><line x1="21" y1="14" x2="21" y2="21"/><line x1="14" y1="21" x2="21" y2="21"/></svg>
                            <p>Enter your content and click<br><strong>Generate QR Code</strong></p>
                        </div>
                        <div class="spin" id="spinner"></div>
                        <div id="qrResult"></div>
                        <div class="errmsg" id="errMsg"></div>
                    </div>

                    <div class="dlg" id="dlGrid">
                        <button class="dl" onclick="downloadAs('png')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> PNG
                        </button>
                        <button class="dl" onclick="downloadAs('svg')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> SVG
                        </button>
                        <button class="dl" onclick="downloadAs('webp')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> WEBP
                        </button>
                        <button class="dl fw" onclick="copyURI()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg> Copy Data URI
                        </button>
                    </div>

                    <div class="infobar" id="infoBar"></div>
                </div>
            </div>
        </div>

        <footer class="footer">
            QRCode.php — Single-file PHP QR Code Generator • MIT License
        </footer>
    </div>

    <div class="toast" id="toast"></div>

    <script>
    // ============================================================
    // Helpers
    // ============================================================
    const $ = id => document.getElementById(id);
    const v = id => $(id)?.value ?? '';

    // Range sync
    [['size','sv','sb'],['quiet','qv','qb'],['margin','mgv','mgb'],['modRadius','mrv','mrb'],['webpQuality','wqv','wqb'],['logoRatio','lsv','lsb'],['logoPad','lpv','lpb'],['logoRad','lrv','lrb'],['labelSize','fsv','fsb'],['finderOuterRadius','forv','forb'],['finderInnerRadius','firv','firb']].forEach(([id,a,b])=>{
        const el=$(id); if(!el) return;
        el.addEventListener('input',()=>{ if($(a))$(a).textContent=el.value; if($(b))$(b).textContent=el.value; });
    });

    // Module radius label
    $('modRadius').addEventListener('input', ()=>{
        const v = parseInt($('modRadius').value);
        $('mrl').textContent = v === 0 ? '— Sharp' : v >= 50 ? '— Circle' : '— Rounded';
    });

    // Module shape toggle — hide radius for dot/diamond
    $('modShape').addEventListener('change', ()=>{
        const s = v('modShape');
        $('radiusGroup').style.display = s === 'square' ? '' : 'none';
    });

    // Color sync
    [['fgSw','fg'],['bgSw','bg'],['fcSw','fontColor'],['finderOuterSw','finderOuterColor'],['finderInnerSw','finderInnerColor']].forEach(([sw,hex])=>{
        const s=$(sw), h=$(hex); if(!s||!h) return;
        s.addEventListener('input',()=>h.value=s.value);
        h.addEventListener('input',()=>{ if(/^#[0-9a-fA-F]{6}$/.test(h.value)) s.value=h.value; });
    });

    // Embed toggle
    $('mode').addEventListener('change', ()=>{
        const m = v('mode');
        $('logoOpts').classList.toggle('on', m==='logo');
        $('labelOpts').classList.toggle('on', m==='label');
    });

    // Format toggle — show/hide format-dependent options
    $('format').addEventListener('change', ()=>{
        const f = v('format');
        $('webpQualityGroup').style.display = f === 'webp' ? '' : 'none';
        $('svgOpts').style.display = f === 'svg' ? '' : 'none';
    });

    // Finder pattern toggle
    $('finderEnabled').addEventListener('change', ()=>{
        $('finderOpts').classList.toggle('on', $('finderEnabled').checked);
    });

    // File name
    $('logo').addEventListener('change', function(){
        const name = this.files[0]?.name || '';
        $('logoName').textContent = name;
        if(name) $('logoLbl').innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg> ${name}`;
    });

    // Store last result for copy/download
    let lastResult = null;
    let lastFormData = null;

    // ============================================================
    // Generate (Fetch API)
    // ============================================================
    async function generate() {
        const text = v('text').trim();
        if (!text) { toast('Please enter content', true); return; }

        // UI: loading state
        const btn = $('genBtn');
        btn.disabled = true;
        btn.innerHTML = '<div class="spin" style="display:block;width:18px;height:18px;border-width:2px"></div> Generating…';
        $('placeholder').style.display = 'none';
        $('qrResult').innerHTML = '';
        $('errMsg').style.display = 'none';
        $('spinner').style.display = 'block';
        $('dlGrid').style.display = 'none';
        $('infoBar').style.display = 'none';

        // Build FormData
        const fd = new FormData();
        fd.append('text', text);
        fd.append('ec', v('ec'));
        fd.append('format', v('format'));
        fd.append('size', v('size'));
        fd.append('quiet', v('quiet'));
        fd.append('fg', v('fg'));
        fd.append('bg', $('bgTransparent').checked ? 'transparent' : v('bg'));
        fd.append('module_shape', v('modShape'));
        fd.append('module_radius', v('modRadius'));
        fd.append('margin', v('margin'));
        fd.append('mode', v('mode'));
        fd.append('label', v('label'));
        fd.append('logo_ratio', v('logoRatio'));
        fd.append('logo_padding', v('logoPad'));
        fd.append('logo_radius', v('logoRad'));
        fd.append('label_size', v('labelSize'));
        fd.append('font_color', v('fontColor'));
        fd.append('label_strip', $('labelStrip').checked ? '1' : '0');
        fd.append('webp_quality', v('webpQuality'));
        fd.append('svg_scalable', $('svgScalable').checked ? '1' : '0');
        fd.append('svg_title', v('svgTitle'));
        fd.append('svg_desc', v('svgDesc'));

        // Finder pattern styling
        fd.append('finder_enabled', $('finderEnabled').checked ? '1' : '0');
        if ($('finderEnabled').checked) {
            fd.append('finder_outer_color', v('finderOuterColor'));
            fd.append('finder_inner_color', v('finderInnerColor'));
            fd.append('finder_outer_radius', v('finderOuterRadius'));
            fd.append('finder_inner_radius', v('finderInnerRadius'));
        }

        const logoFile = $('logo').files[0];
        if (v('mode') === 'logo' && logoFile) {
            fd.append('logo', logoFile);
        }

        try {
            const res = await fetch('api.php', { method: 'POST', body: fd });
            const json = await res.json();

            $('spinner').style.display = 'none';
            btn.disabled = false;
            btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg> Generate QR Code';

            if (!json.success) {
                $('errMsg').textContent = '⚠ ' + (json.error || 'Unknown error');
                $('errMsg').style.display = 'block';
                $('placeholder').style.display = 'flex';
                return;
            }

            lastResult = json;
            lastFormData = fd;

            // Render result
            if (json.type === 'svg') {
                $('qrResult').innerHTML = json.data;
            } else {
                const hasSmooth = parseInt(v('modRadius')) > 0 || v('modShape') !== 'square';
                const imgRender = hasSmooth ? 'auto' : 'pixelated';
                $('qrResult').innerHTML = `<img src="${json.data}" alt="QR Code" style="image-rendering:${imgRender}">`;
            }

            // Show checkerboard if transparent
            $('qrFrame').classList.toggle('checker', $('bgTransparent').checked);

            // Show download buttons
            $('dlGrid').style.display = 'grid';

            // Info bar — enhanced with getInfo() data
            const info = json.info || {};
            const modeLabel = info.mode || '—';
            const util = info.utilization ? Math.round(info.utilization * 100) + '%' : '—';
            const ver = info.version ? 'v' + info.version : '—';

            let chips = `
                <span class="chip"><span class="dot dp"></span> ${json.modules}×${json.modules}</span>
                <span class="chip"><span class="dot dc"></span> ${ver}</span>
                <span class="chip"><span class="dot de"></span> EC: ${json.ec}</span>
                <span class="chip"><span class="dot da"></span> ${modeLabel}</span>
                <span class="chip"><span class="dot dr"></span> ${util} used</span>
                <span class="chip"><span class="dot dp"></span> ${json.format}</span>
            `;
            if (json.embed) chips += `<span class="chip"><span class="dot de"></span> ${esc(json.embed)}</span>`;
            $('infoBar').innerHTML = chips;
            $('infoBar').style.display = 'flex';

        } catch (err) {
            $('spinner').style.display = 'none';
            btn.disabled = false;
            btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg> Generate QR Code';
            $('errMsg').textContent = '⚠ Network error: ' + err.message;
            $('errMsg').style.display = 'block';
            $('placeholder').style.display = 'flex';
        }
    }

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    // ============================================================
    // Download helper
    // ============================================================
    function triggerDownload(url, filename) {
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    async function downloadAs(fmt) {
        if (!lastResult && !lastFormData) { toast('Generate a QR code first', true); return; }

        // If we already have the right format, download directly
        if (lastResult && lastResult.type === fmt) {
            if (fmt === 'svg') {
                const blob = new Blob([lastResult.data], { type: 'image/svg+xml' });
                triggerDownload(URL.createObjectURL(blob), 'qrcode.svg');
            } else {
                triggerDownload(lastResult.data, 'qrcode.' + fmt);
            }
            return;
        }

        // Need different format — re-request via POST
        if (!lastFormData) { toast('Generate a QR code first', true); return; }
        const fd = new FormData();
        for (const [k, val] of lastFormData.entries()) {
            if (k === 'format') continue;
            fd.append(k, val);
        }
        fd.append('format', fmt);

        try {
            toast('⏳ Converting…');
            const res = await fetch('api.php', { method: 'POST', body: fd });
            const json = await res.json();
            if (!json.success) { toast('⚠ ' + (json.error || 'Error'), true); return; }

            if (fmt === 'svg') {
                const blob = new Blob([json.data], { type: 'image/svg+xml' });
                triggerDownload(URL.createObjectURL(blob), 'qrcode.svg');
            } else {
                triggerDownload(json.data, 'qrcode.' + fmt);
            }
            toast('✓ Downloaded');
        } catch (err) {
            toast('⚠ Download failed: ' + err.message, true);
        }
    }

    // ============================================================
    // Copy Data URI
    // ============================================================
    function copyURI() {
        if (!lastResult) return;
        let data = '';
        if (lastResult.type === 'svg') {
            data = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(lastResult.data)));
        } else {
            data = lastResult.data;
        }
        navigator.clipboard.writeText(data)
            .then(() => toast('✓ Copied to clipboard'))
            .catch(() => {
                const ta = document.createElement('textarea');
                ta.value = data; document.body.appendChild(ta);
                ta.select(); document.execCommand('copy');
                document.body.removeChild(ta);
                toast('✓ Copied to clipboard');
            });
    }

    // ============================================================
    // Toast
    // ============================================================
    function toast(msg, isErr = false) {
        const t = $('toast');
        t.textContent = msg;
        t.className = 'toast show' + (isErr ? ' err' : '');
        setTimeout(() => t.classList.remove('show'), 2500);
    }

    // Enter key = generate
    $('text').addEventListener('keydown', e => {
        if (e.ctrlKey && e.key === 'Enter') generate();
    });
    </script>
</body>
</html>
