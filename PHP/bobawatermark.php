<?php
/**
 * BobaWatermark – Wasserzeichen-Generator mit Text oder Logo
 *
 * Entwickler:        Sven Owsianowski
 * Entwicklerprofil:  https://bobaro.de/page.php?p=ueber-den-entwickler
 * Entwickelt für:    Bobaro – Bloggen ohne Ballast | www.bobaro.de
 * Vorschau:          https://
 * Jahr:              2026
 */

// ── AJAX: Logo-Wasserzeichen löschen ─────────────────────────────────────────
if (!empty($_SERVER['HTTP_X_WZ_LOGO_DELETE']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $target = __DIR__ . '/medien/wasserzeichen/wz_logo.png';
    if (is_file($target) && unlink($target)) {
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Datei nicht gefunden oder konnte nicht gelöscht werden']);
    }
    exit;
}

// ── AJAX: Logo-Wasserzeichen hochladen ───────────────────────────────────────
if (!empty($_SERVER['HTTP_X_WZ_LOGO_UPLOAD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $uploadDir = __DIR__ . '/medien/wasserzeichen/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $target = $uploadDir . 'wz_logo.png';
    $f = $_FILES['wz_logo'] ?? null;
    if (!$f || $f['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['ok' => false, 'error' => 'Upload-Fehler']);
        exit;
    }
    $mime = mime_content_type($f['tmp_name']);
    if (!in_array($mime, ['image/png','image/jpeg','image/webp','image/gif','image/svg+xml'])) {
        echo json_encode(['ok' => false, 'error' => 'Ungültiges Dateiformat']);
        exit;
    }
    if (move_uploaded_file($f['tmp_name'], $target)) {
        echo json_encode(['ok' => true, 'url' => 'medien/wasserzeichen/wz_logo.png?t=' . time()]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Speichern fehlgeschlagen']);
    }
    exit;
}

$primaryColor = '#1EA3F2';
date_default_timezone_set('Europe/Berlin');
$appMode = 'light';

// Vorhandenes Logo prüfen
$wzLogoFile    = __DIR__ . '/medien/wasserzeichen/wz_logo.png';
$wzLogoExists  = is_file($wzLogoFile);
$wzLogoUrl     = $wzLogoExists ? ('medien/wasserzeichen/wz_logo.png?t=' . filemtime($wzLogoFile)) : '';
?>
<!DOCTYPE html>
<html lang="de" data-bs-theme="<?php echo htmlspecialchars($appMode); ?>">
<head>
    <script>
        (function() {
            var saved = localStorage.getItem('theme');
            if (saved === 'dark' || saved === 'light') {
                document.documentElement.setAttribute('data-bs-theme', saved);
            }
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BobaWatermark</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --wz-primary: <?php echo $primaryColor; ?>;
            --wz-primary-dim: <?php echo $primaryColor; ?>22;
        }

        body {
            background: var(--bs-body-bg);
            min-height: 100vh;
            font-size: .92rem;
        }

        /* ── Navbar ── */
        .wz-navbar {
            background: var(--bs-body-bg);
            border-bottom: 1px solid var(--bs-border-color);
        }

        /* ── Layout ── */
        .wz-layout {
            display: flex;
            height: calc(100vh - 57px);
            overflow: hidden;
        }

        /* ── Sidebar ── */
        .wz-sidebar {
            width: 280px;
            min-width: 280px;
            border-right: 1px solid var(--bs-border-color);
            background: var(--bs-body-bg);
            overflow-y: auto;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        /* ── Canvas Area ── */
        .wz-canvas-area {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bs-tertiary-bg);
            overflow: hidden;
            position: relative;
        }

        .wz-canvas-wrap {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            padding: 1.5rem;
        }

        #wz-canvas {
            max-width: 100%;
            max-height: 100%;
            border-radius: 10px;
            box-shadow: 0 8px 40px rgba(0,0,0,.25);
            display: block;
            cursor: crosshair;
            object-fit: contain;
        }

        /* ── Drop Zone ── */
        .wz-dropzone {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            color: var(--bs-secondary-color);
            border: 2px dashed var(--bs-border-color);
            border-radius: 16px;
            margin: 1.5rem;
            transition: border-color .2s, background .2s;
            cursor: pointer;
        }
        .wz-dropzone:hover, .wz-dropzone.drag-over {
            border-color: var(--wz-primary);
            background: var(--wz-primary-dim);
            color: var(--wz-primary);
        }
        .wz-dropzone i { font-size: 3rem; opacity: .4; }

        /* ── Section Labels ── */
        .wz-section-label {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--bs-secondary-color);
            margin-bottom: .5rem;
        }

        /* ── Controls ── */
        .wz-control-group {
            display: flex;
            flex-direction: column;
            gap: .75rem;
        }

        .wz-range-wrap {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .wz-range-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .wz-range-val {
            font-size: .75rem;
            font-weight: 600;
            color: var(--wz-primary);
            min-width: 36px;
            text-align: right;
        }
        .form-range::-webkit-slider-thumb { background: var(--wz-primary); }
        .form-range::-webkit-slider-runnable-track { background: var(--bs-border-color); }

        /* ── Farb-Buttons ── */
        .wz-color-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .wz-color-swatch {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: transform .1s, border-color .15s;
            flex-shrink: 0;
        }
        .wz-color-swatch:hover { transform: scale(1.15); }
        .wz-color-swatch.active { border-color: var(--wz-primary); box-shadow: 0 0 0 2px var(--wz-primary-dim); }

        /* ── Aktions-Buttons ── */
        .btn-primary {
            background-color: var(--wz-primary) !important;
            border-color: var(--wz-primary) !important;
        }
        .btn-outline-primary {
            color: var(--wz-primary) !important;
            border-color: var(--wz-primary) !important;
        }
        .btn-outline-primary:hover {
            background-color: var(--wz-primary) !important;
            color: #fff !important;
        }

        /* ── Hint Badge ── */
        .wz-hint {
            position: absolute;
            bottom: 1rem;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,.55);
            color: #fff;
            font-size: .72rem;
            padding: .3rem .8rem;
            border-radius: 20px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity .3s;
        }
        .wz-hint.visible { opacity: 1; }

        /* ── Preview Badge ── */
        .wz-preview-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: .72rem;
            padding: .25rem .7rem;
            border-radius: 20px;
            pointer-events: none;
        }

        /* ── Dark Mode ── */
        [data-bs-theme="dark"] .wz-sidebar { background: #212529; border-color: #444; }
        [data-bs-theme="dark"] .wz-navbar { background: #1a1d20 !important; border-color: #444 !important; }
        [data-bs-theme="dark"] .wz-canvas-area { background: #16191c; }
        [data-bs-theme="dark"] #wz-canvas { box-shadow: 0 8px 40px rgba(0,0,0,.5); }

        /* ── Modus-Toggle ── */
        .wz-mode-toggle {
            display: flex;
            background: var(--bs-secondary-bg);
            border-radius: 8px;
            padding: 3px;
            gap: 2px;
        }
        .wz-mode-btn {
            flex: 1;
            font-size: .78rem;
            font-weight: 500;
            padding: 5px 4px;
            border: none;
            border-radius: 6px;
            background: transparent;
            color: var(--bs-secondary-color);
            cursor: pointer;
            transition: background .15s, color .15s;
        }
        .wz-mode-btn.active {
            background: var(--bs-body-bg);
            color: var(--wz-primary);
            box-shadow: 0 0 0 1px var(--bs-border-color);
        }

        /* ── Logo-Dropzone (Sidebar) ── */
        .wz-logo-dropzone {
            border: 2px dashed var(--bs-border-color);
            border-radius: 10px;
            padding: 16px 8px;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s, background .2s;
            color: var(--bs-secondary-color);
        }
        .wz-logo-dropzone:hover, .wz-logo-dropzone.drag-over {
            border-color: var(--wz-primary);
            background: var(--wz-primary-dim);
            color: var(--wz-primary);
        }
        .wz-logo-dropzone i { font-size: 1.6rem; display: block; margin-bottom: 4px; opacity: .5; }

        /* ── Logo-Vorschau-Strip ── */
        .wz-logo-preview {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--bs-secondary-bg);
            border-radius: 8px;
            padding: 6px 8px;
            border: 1px solid var(--bs-border-color);
        }
        .wz-logo-thumb {
            width: 38px;
            height: 38px;
            border-radius: 5px;
            object-fit: contain;
            background: repeating-conic-gradient(#ccc 0% 25%, #fff 0% 50%) 0 0 / 8px 8px;
            border: 1px solid var(--bs-border-color);
            flex-shrink: 0;
        }
        .wz-logo-meta { flex: 1; min-width: 0; }
        .wz-logo-name { font-size: .78rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .wz-logo-sub  { font-size: .7rem; color: var(--bs-secondary-color); }
        .wz-logo-delete {
            background: none;
            border: none;
            color: var(--bs-secondary-color);
            cursor: pointer;
            padding: 4px 6px;
            border-radius: 6px;
            font-size: .9rem;
            flex-shrink: 0;
            transition: color .15s, background .15s;
        }
        .wz-logo-delete:hover { color: var(--bs-danger); background: var(--bs-danger-bg-subtle); }

        /* ── Upload Button ── */
        .wz-upload-btn {
            position: relative;
            overflow: hidden;
        }
        .wz-upload-btn input[type=file] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        /* ── Font-Auswahl ── */
        .wz-font-option {
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 6px;
            border: 2px solid transparent;
            font-size: .82rem;
            background: var(--bs-secondary-bg);
            color: var(--bs-body-color);
            transition: border-color .15s;
            white-space: nowrap;
        }
        .wz-font-option.active { border-color: var(--wz-primary); color: var(--wz-primary); font-weight: 600; }

        /* ── Fußzeile ── */
        .wz-footer {
            font-size: .7rem;
            color: var(--bs-secondary-color);
            text-align: left;
            padding-top: .5rem;
        }
        .wz-footer-link {
            color: var(--bs-secondary-color);
            text-decoration: underline;
            text-decoration-color: transparent;
            transition: text-decoration-color .15s;
        }
        .wz-footer-link:hover {
            color: var(--wz-primary);
            text-decoration-color: currentColor;
        }
    </style>
</head>
<body>

<!-- ── Navbar ── -->
<nav class="navbar wz-navbar sticky-top shadow-sm">
    <div class="container-fluid px-3 d-flex align-items-center gap-2">
        <span class="fw-bold" style="color: var(--wz-primary);">
            <i class="bi bi-droplet-half me-2"></i>Wasserzeichen Editor
        </span>
        <div class="ms-auto d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="wzPreviewBtn">
                <i class="bi bi-eye me-1"></i>Vorschau
            </button>
            <button class="btn btn-primary btn-sm rounded-pill px-3" id="wzDownloadBtn" disabled>
                <i class="bi bi-download me-1"></i>Download
            </button>
        </div>
    </div>
</nav>

<!-- ── Layout ── -->
<div class="wz-layout">

    <!-- ── Sidebar ── -->
    <div class="wz-sidebar">

        <!-- Bild laden -->
        <div>
            <div class="wz-section-label">Bild</div>
            <div class="wz-control-group">
                <div class="wz-upload-btn btn btn-outline-primary btn-sm rounded-pill">
                    <i class="bi bi-image me-1"></i>Bild auswählen…
                    <input type="file" id="wzFileInput" accept="image/*">
                </div>
                <div id="wzFileInfo" class="text-muted small" style="display:none;"></div>
            </div>
        </div>

        <!-- Modus-Toggle -->
        <div>
            <div class="wz-section-label">Wasserzeichen-Typ</div>
            <div class="wz-mode-toggle">
                <button class="wz-mode-btn active" id="wzModeText" onclick="setWzMode('text')">
                    <i class="bi bi-fonts me-1"></i>Text
                </button>
                <button class="wz-mode-btn" id="wzModeImage" onclick="setWzMode('image')">
                    <i class="bi bi-image me-1"></i>Bild / Logo
                </button>
            </div>
        </div>

        <!-- Text-Modus Einstellungen -->
        <div id="wzPanelText">

            <!-- Text -->
            <div class="mb-3">
                <div class="wz-section-label">Wasserzeichen-Text</div>
                <input type="text" id="wzText" class="form-control form-control-sm rounded-3"
                       value="© <?php echo date('Y'); ?> Sven Owsianowski" placeholder="Wasserzeichen-Text">
            </div>

            <!-- Schrift -->
            <div class="mb-3">
                <div class="wz-section-label">Schriftart</div>
                <div class="d-flex flex-wrap gap-1" id="wzFontOpts">
                    <span class="wz-font-option active" data-font="Arial" style="font-family:Arial">Arial</span>
                    <span class="wz-font-option" data-font="Georgia" style="font-family:Georgia">Georgia</span>
                    <span class="wz-font-option" data-font="'Courier New'" style="font-family:'Courier New'">Mono</span>
                    <span class="wz-font-option" data-font="Impact" style="font-family:Impact">Impact</span>
                    <span class="wz-font-option" data-font="Verdana" style="font-family:Verdana">Verdana</span>
                </div>
            </div>

            <!-- Einstellungen Text -->
            <div>
                <div class="wz-section-label">Einstellungen</div>
                <div class="wz-control-group">
                    <div class="wz-range-wrap">
                        <div class="wz-range-header">
                            <span class="small">Schriftgröße</span>
                            <span class="wz-range-val" id="wzSizeVal">36px</span>
                        </div>
                        <input type="range" id="wzSize" min="12" max="200" value="36" class="form-range">
                    </div>
                    <div class="wz-range-wrap">
                        <div class="wz-range-header">
                            <span class="small">Transparenz</span>
                            <span class="wz-range-val" id="wzOpacityVal">70%</span>
                        </div>
                        <input type="range" id="wzOpacity" min="0.05" max="1" step="0.05" value="0.7" class="form-range">
                    </div>
                </div>
            </div>

            <!-- Farbe -->
            <div class="mt-3">
                <div class="wz-section-label">Textfarbe</div>
                <div class="wz-color-grid" id="wzColorGrid"></div>
            </div>

        </div>

        <!-- Bild-Modus Einstellungen -->
        <div id="wzPanelImage" style="display:none;">

            <!-- Logo hochladen -->
            <div class="mb-3">
                <div class="wz-section-label">Logo / Bild</div>
                <div class="wz-logo-dropzone" id="wzLogoDrop">
                    <i class="bi bi-image-fill"></i>
                    <div class="small fw-semibold">Bild hier ablegen</div>
                    <div class="small opacity-75 mt-1">PNG mit Transparenz empfohlen</div>
                    <input type="file" id="wzLogoInput" accept="image/*" style="display:none;">
                </div>
                <div id="wzLogoPreview" class="wz-logo-preview mt-2" style="display:none;">
                    <img id="wzLogoThumb" class="wz-logo-thumb" src="" alt="Logo">
                    <div class="wz-logo-meta">
                        <div class="wz-logo-name" id="wzLogoName">logo.png</div>
                        <div class="wz-logo-sub" id="wzLogoSub"></div>
                    </div>
                    <button type="button" class="wz-logo-delete" id="wzLogoDeleteBtn" title="Logo vom Server löschen" onclick="wzLogoLoeschen()">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
                <div id="wzLogoUploadStatus" class="small mt-1" style="display:none;"></div>
            </div>

            <!-- Einstellungen Bild -->
            <div>
                <div class="wz-section-label">Einstellungen</div>
                <div class="wz-control-group">
                    <div class="wz-range-wrap">
                        <div class="wz-range-header">
                            <span class="small">Größe</span>
                            <span class="wz-range-val" id="wzLogoScaleVal">100%</span>
                        </div>
                        <input type="range" id="wzLogoScale" min="5" max="300" value="100" step="1" class="form-range">
                    </div>
                    <div class="wz-range-wrap">
                        <div class="wz-range-header">
                            <span class="small">Transparenz</span>
                            <span class="wz-range-val" id="wzLogoOpacityVal">70%</span>
                        </div>
                        <input type="range" id="wzLogoOpacity" min="0.05" max="1" step="0.05" value="0.7" class="form-range">
                    </div>
                    <div class="wz-range-wrap">
                        <div class="wz-range-header">
                            <span class="small">Drehung</span>
                            <span class="wz-range-val" id="wzLogoRotVal">0°</span>
                        </div>
                        <input type="range" id="wzLogoRot" min="-180" max="180" value="0" step="1" class="form-range">
                    </div>
                </div>
            </div>

        </div>

        <!-- Position Reset -->
        <div class="mt-auto pt-2 border-top">
            <button class="btn btn-outline-secondary btn-sm w-100 rounded-pill" id="wzResetBtn" disabled>
                <i class="bi bi-arrow-counterclockwise me-1"></i>Position zurücksetzen
            </button>
        </div>

        <!-- Fußzeile -->
        <div class="wz-footer">
            Bearbeitungen erfolgen lokal · Entwickelt von 2026 <a href="https://bobaro.de/page.php?p=ueber-den-entwickler" target="_blank" rel="noopener" class="wz-footer-link">Sven Owsianowski</a> als Teil von <a href="https://bobaro.de" target="_blank" rel="noopener" class="wz-footer-link">Bobaro.de</a>
        </div>

    </div>

    <!-- ── Canvas Bereich ── -->
    <div class="wz-canvas-area" id="wzCanvasArea">

        <!-- Drop Zone (solange kein Bild) -->
        <div class="wz-dropzone" id="wzDropzone">
            <i class="bi bi-cloud-upload"></i>
            <div class="fw-semibold">Bild hier ablegen</div>
            <div class="small opacity-75">oder oben „Bild auswählen" klicken</div>
            <div class="small opacity-50">JPG, PNG, WebP, GIF</div>
        </div>

        <!-- Canvas (versteckt bis Bild geladen) -->
        <div class="wz-canvas-wrap" id="wzCanvasWrap" style="display:none;">
            <canvas id="wz-canvas"></canvas>
            <div class="wz-hint" id="wzHint">
                <i class="bi bi-arrows-move me-1"></i>Ziehen · <i class="bi bi-arrows-angle-expand mx-1"></i>Skalieren · <i class="bi bi-arrow-repeat mx-1"></i>Rotieren
            </div>
            <span class="wz-preview-badge badge bg-warning text-dark" id="wzPreviewBadge" style="display:none;">
                <i class="bi bi-eye me-1"></i>Vorschau
            </span>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const PRIMARY      = <?php echo json_encode($primaryColor); ?>;
const WZ_LOGO_URL  = <?php echo json_encode($wzLogoUrl); ?>;
const WZ_LOGO_EXISTS = <?php echo $wzLogoExists ? 'true' : 'false'; ?>;

const canvas = document.getElementById('wz-canvas');
const ctx    = canvas.getContext('2d');

// ── State ─────────────────────────────────────────────────────────────────────
const state = {
    img:          null,
    x:            300,
    y:            200,
    rotation:     0,
    fontSize:     36,
    opacity:      0.7,
    text:         '© <?php echo date("Y"); ?> Sven Owsianowski',
    color:        '#ffffff',
    font:         'Arial',
    filename:     'wasserzeichen',
    wzMode:       'text',
    wzLogoImg:    null,
    wzLogoScale:  100,
    wzLogoOpacity: 0.7,
};

let showUI        = true;
let renderPending = false;

// ── Farb-Palette ──────────────────────────────────────────────────────────────
const COLORS = [
    { val: '#ffffff', label: 'Weiß' },
    { val: '#000000', label: 'Schwarz' },
    { val: PRIMARY,   label: 'Primär' },
    { val: '#ffe066', label: 'Gelb' },
    { val: '#ff6b6b', label: 'Rot' },
    { val: '#51cf66', label: 'Grün' },
    { val: '#74c0fc', label: 'Blau' },
    { val: '#cc5de8', label: 'Lila' },
];

function buildColorGrid() {
    const grid = document.getElementById('wzColorGrid');
    grid.innerHTML = '';
    COLORS.forEach(c => {
        const sw = document.createElement('div');
        sw.className = 'wz-color-swatch' + (c.val === state.color ? ' active' : '');
        sw.style.background = c.val;
        sw.title = c.label;
        if (c.val === '#ffffff') sw.style.border = '2px solid var(--bs-border-color)';
        sw.onclick = () => {
            state.color = c.val;
            document.querySelectorAll('.wz-color-swatch').forEach(s => s.classList.remove('active'));
            sw.classList.add('active');
            requestRender();
        };
        grid.appendChild(sw);
    });
    // Eigene Farbe
    const custom = document.createElement('input');
    custom.type = 'color';
    custom.value = '#ff8800';
    custom.title = 'Eigene Farbe';
    custom.style.cssText = 'width:28px;height:28px;border-radius:7px;border:2px solid var(--bs-border-color);padding:1px;cursor:pointer;background:none;';
    custom.oninput = () => {
        state.color = custom.value;
        document.querySelectorAll('.wz-color-swatch').forEach(s => s.classList.remove('active'));
        requestRender();
    };
    grid.appendChild(custom);
}

// ── Render ────────────────────────────────────────────────────────────────────
function requestRender() {
    if (renderPending) return;
    renderPending = true;
    requestAnimationFrame(() => { renderPending = false; render(); });
}

function render() {
    if (!state.img) return;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(state.img, 0, 0, canvas.width, canvas.height);
    drawWatermark(showUI);
}

function getFont() {
    return `bold ${state.fontSize}px ${state.font}`;
}

function getWatermarkSize() {
    if (state.wzMode === 'image' && state.wzLogoImg) {
        const scale = state.wzLogoScale / 100;
        return {
            width:  state.wzLogoImg.naturalWidth  * scale,
            height: state.wzLogoImg.naturalHeight * scale,
        };
    }
    ctx.save();
    ctx.font = getFont();
    const width  = ctx.measureText(state.text).width;
    const height = state.fontSize * 1.2;
    ctx.restore();
    return { width, height };
}

function getHandles() {
    const { width, height } = getWatermarkSize();
    return {
        resize: { x: width / 2,  y: height / 2 },
        rotate: { x: 0,          y: -height / 2 - 32 },
    };
}

function drawWatermark(showUIFlag = true) {
    ctx.save();
    ctx.translate(state.x, state.y);
    ctx.rotate(state.rotation * Math.PI / 180);

    const { width, height } = getWatermarkSize();
    const h = getHandles();

    if (state.wzMode === 'image' && state.wzLogoImg) {
        // ── Bild-Wasserzeichen ──
        ctx.globalAlpha = state.wzLogoOpacity;
        ctx.drawImage(state.wzLogoImg, -width / 2, -height / 2, width, height);
        ctx.globalAlpha = 1;
    } else {
        // ── Text-Wasserzeichen ──
        ctx.font         = getFont();
        ctx.textAlign    = 'center';
        ctx.textBaseline = 'middle';
        ctx.shadowColor   = 'rgba(0,0,0,0.4)';
        ctx.shadowBlur    = 6;
        ctx.shadowOffsetX = 2;
        ctx.shadowOffsetY = 2;
        ctx.fillStyle = hexToRgba(state.color, state.opacity);
        ctx.fillText(state.text, 0, 0);
        ctx.shadowColor = 'transparent';
    }

    if (showUIFlag) {
        // Rahmen
        ctx.strokeStyle = 'rgba(255,255,255,0.5)';
        ctx.lineWidth   = 1;
        ctx.setLineDash([4, 3]);
        ctx.strokeRect(-width / 2 - 8, -height / 2 - 6, width + 16, height + 12);
        ctx.setLineDash([]);

        // Resize-Handle (blau-primär)
        ctx.fillStyle = PRIMARY;
        ctx.beginPath();
        ctx.roundRect(h.resize.x - 6, h.resize.y - 6, 12, 12, 3);
        ctx.fill();
        ctx.strokeStyle = '#fff';
        ctx.lineWidth = 1.5;
        ctx.stroke();

        // Rotate-Handle (rot)
        ctx.beginPath();
        ctx.arc(h.rotate.x, h.rotate.y, 7, 0, Math.PI * 2);
        ctx.fillStyle = '#ff6b6b';
        ctx.fill();
        ctx.strokeStyle = '#fff';
        ctx.lineWidth = 1.5;
        ctx.stroke();

        // Verbindungslinie Rotate
        ctx.beginPath();
        ctx.moveTo(0, -height / 2 - 6);
        ctx.lineTo(h.rotate.x, h.rotate.y + 7);
        ctx.strokeStyle = 'rgba(255,255,255,0.4)';
        ctx.lineWidth = 1;
        ctx.stroke();
    }

    ctx.restore();
}

function hexToRgba(hex, alpha) {
    hex = hex.replace('#', '');
    if (hex.length === 3) hex = hex.split('').map(c => c+c).join('');
    const r = parseInt(hex.slice(0,2), 16);
    const g = parseInt(hex.slice(2,4), 16);
    const b = parseInt(hex.slice(4,6), 16);
    return `rgba(${r},${g},${b},${alpha})`;
}

// ── Interaktion ───────────────────────────────────────────────────────────────
function toLocal(mx, my) {
    const dx  = mx - state.x;
    const dy  = my - state.y;
    const cos = Math.cos(-state.rotation * Math.PI / 180);
    const sin = Math.sin(-state.rotation * Math.PI / 180);
    return { x: dx * cos - dy * sin, y: dx * sin + dy * cos };
}

function hitTest(mx, my) {
    const p = toLocal(mx, my);
    const { width, height } = getWatermarkSize();
    const h = getHandles();
    if (Math.abs(p.x - h.resize.x) < 12 && Math.abs(p.y - h.resize.y) < 12) return 'resize';
    if (Math.hypot(p.x - h.rotate.x, p.y - h.rotate.y) < 12)                 return 'rotate';
    if (p.x > -width / 2 - 8 && p.x < width / 2 + 8 && p.y > -height / 2 - 6 && p.y < height / 2 + 6) return 'drag';
    return null;
}

const CURSORS = { drag: 'grab', resize: 'nwse-resize', rotate: 'crosshair', null: 'default' };
let mode = null, start = {};

canvas.addEventListener('pointermove', e => {
    if (!mode) {
        const { mx, my } = getCanvasCoords(e);
        const hit = hitTest(mx, my);
        canvas.style.cursor = CURSORS[hit] || 'default';
        return;
    }
    const { mx, my } = getCanvasCoords(e);
    if (mode === 'drag') {
        state.x = start.sx + (mx - start.x);
        state.y = start.sy + (my - start.y);
    }
    if (mode === 'resize') {
        const d1 = Math.hypot(start.x - state.x, start.y - state.y);
        const d2 = Math.hypot(mx - state.x, my - state.y);
        if (d1 > 0) {
            if (state.wzMode === 'image') {
                state.wzLogoScale = Math.max(5, Math.min(300, start.size * (d2 / d1)));
                document.getElementById('wzLogoScale').value      = Math.round(state.wzLogoScale);
                document.getElementById('wzLogoScaleVal').textContent = Math.round(state.wzLogoScale) + '%';
            } else {
                state.fontSize = Math.max(12, Math.min(200, start.size * (d2 / d1)));
                document.getElementById('wzSize').value            = Math.round(state.fontSize);
                document.getElementById('wzSizeVal').textContent   = Math.round(state.fontSize) + 'px';
            }
        }
    }
    if (mode === 'rotate') {
        const a1 = Math.atan2(start.y - state.y, start.x - state.x);
        const a2 = Math.atan2(my - state.y, mx - state.x);
        state.rotation = start.rot + (a2 - a1) * 180 / Math.PI;
    }
    requestRender();
});

canvas.addEventListener('pointerdown', e => {
    const { mx, my } = getCanvasCoords(e);
    mode = hitTest(mx, my);
    if (!mode) return;
    start = { x: mx, y: my, sx: state.x, sy: state.y, rot: state.rotation,
              size: state.wzMode === 'image' ? state.wzLogoScale : state.fontSize };
    canvas.style.cursor = mode === 'drag' ? 'grabbing' : CURSORS[mode];
    canvas.setPointerCapture(e.pointerId);
    showHint(false);
});

canvas.addEventListener('pointerup', e => {
    mode = null;
    canvas.style.cursor = 'default';
    canvas.releasePointerCapture(e.pointerId);
});

function getCanvasCoords(e) {
    const rect = canvas.getBoundingClientRect();
    return {
        mx: (e.clientX - rect.left) * (canvas.width  / rect.width),
        my: (e.clientY - rect.top)  * (canvas.height / rect.height),
    };
}

// ── Hint ──────────────────────────────────────────────────────────────────────
let hintTimeout;
function showHint(visible) {
    const hint = document.getElementById('wzHint');
    clearTimeout(hintTimeout);
    if (visible) {
        hint.classList.add('visible');
        hintTimeout = setTimeout(() => hint.classList.remove('visible'), 3500);
    } else {
        hint.classList.remove('visible');
    }
}

// ── Datei laden ───────────────────────────────────────────────────────────────
function loadImage(file) {
    if (!file || !file.type.startsWith('image/')) return;
    const img = new Image();
    img.onload = () => {
        state.img = img;
        canvas.width  = img.width;
        canvas.height = img.height;
        state.x = canvas.width  / 2;
        state.y = canvas.height / 2;

        document.getElementById('wzDropzone').style.display   = 'none';
        document.getElementById('wzCanvasWrap').style.display = '';
        document.getElementById('wzDownloadBtn').disabled     = false;
        document.getElementById('wzResetBtn').disabled        = false;

        const kb = Math.round(file.size / 1024);
        const info = document.getElementById('wzFileInfo');
        info.textContent = `${file.name} · ${img.width}×${img.height}px · ${kb} KB`;
        info.style.display = '';
        state.filename = file.name.replace(/\.[^/.]+$/, '');

        requestRender();
        showHint(true);
    };
    img.src = URL.createObjectURL(file);
}

document.getElementById('wzFileInput').addEventListener('change', e => loadImage(e.target.files[0]));

// ── Drag & Drop ───────────────────────────────────────────────────────────────
const dropzone = document.getElementById('wzDropzone');
const canvasArea = document.getElementById('wzCanvasArea');

['dragover', 'dragenter'].forEach(ev => {
    canvasArea.addEventListener(ev, e => { e.preventDefault(); dropzone.classList.add('drag-over'); });
});
['dragleave', 'dragend', 'drop'].forEach(ev => {
    canvasArea.addEventListener(ev, e => { e.preventDefault(); dropzone.classList.remove('drag-over'); });
});
canvasArea.addEventListener('drop', e => {
    e.preventDefault();
    loadImage(e.dataTransfer.files[0]);
});
dropzone.addEventListener('click', () => document.getElementById('wzFileInput').click());

// ── Modus-Toggle ──────────────────────────────────────────────────────────────
function setWzMode(mode) {
    state.wzMode = mode;
    document.getElementById('wzModeText').classList.toggle('active', mode === 'text');
    document.getElementById('wzModeImage').classList.toggle('active', mode === 'image');
    document.getElementById('wzPanelText').style.display  = mode === 'text'  ? '' : 'none';
    document.getElementById('wzPanelImage').style.display = mode === 'image' ? '' : 'none';
    // Gespeichertes Logo laden falls noch nicht geladen
    if (mode === 'image' && !state.wzLogoImg && WZ_LOGO_EXISTS) {
        wzLogoVomServerLaden(WZ_LOGO_URL);
    }
    requestRender();
}

// ── Logo vom Server laden (URL) ───────────────────────────────────────────────
function wzLogoVomServerLaden(url) {
    const img = new Image();
    img.onload = () => {
        state.wzLogoImg = img;
        if (state.img) {
            state.wzLogoScale = Math.round((canvas.width * 0.25) / img.naturalWidth * 100);
            state.wzLogoScale = Math.max(5, Math.min(300, state.wzLogoScale));
            document.getElementById('wzLogoScale').value          = state.wzLogoScale;
            document.getElementById('wzLogoScaleVal').textContent = state.wzLogoScale + '%';
        }
        document.getElementById('wzLogoThumb').src            = url;
        document.getElementById('wzLogoName').textContent     = 'wz_logo.png';
        document.getElementById('wzLogoSub').textContent      = img.naturalWidth + '×' + img.naturalHeight + ' px';
        document.getElementById('wzLogoPreview').style.display = '';
        requestRender();
    };
    img.src = url;
}

// ── Logo vom Server löschen ───────────────────────────────────────────────────
function wzLogoLoeschen() {
    if (!confirm('Logo von medien/wasserzeichen/ löschen?')) return;
    fetch('bobawatermark.php', { method: 'POST', headers: { 'X-WZ-Logo-Delete': '1' } })
        .then(r => r.json())
        .then(d => {
            if (d.ok) {
                state.wzLogoImg = null;
                document.getElementById('wzLogoPreview').style.display = 'none';
                document.getElementById('wzLogoThumb').src = '';
                requestRender();
            } else {
                alert('Fehler: ' + (d.error || 'Unbekannter Fehler'));
            }
        })
        .catch(() => alert('Verbindungsfehler beim Löschen'));
}

// ── Logo-Upload (Sidebar Dropzone) ────────────────────────────────────────────
function loadLogoFile(file) {
    if (!file || !file.type.startsWith('image/')) return;

    // Sofort lokal vorschauen
    const reader = new FileReader();
    reader.onload = e => {
        const img = new Image();
        img.onload = () => {
            state.wzLogoImg   = img;
            // Startgröße: ca. 25% der Canvas-Breite
            if (state.img) {
                state.wzLogoScale = Math.round((canvas.width * 0.25) / img.naturalWidth * 100);
                state.wzLogoScale = Math.max(5, Math.min(300, state.wzLogoScale));
                document.getElementById('wzLogoScale').value          = state.wzLogoScale;
                document.getElementById('wzLogoScaleVal').textContent = state.wzLogoScale + '%';
            }
            // Vorschau-Strip
            document.getElementById('wzLogoThumb').src     = e.target.result;
            document.getElementById('wzLogoName').textContent = file.name;
            document.getElementById('wzLogoSub').textContent  =
                img.naturalWidth + '×' + img.naturalHeight + ' px · ' + Math.round(file.size / 1024) + ' KB';
            document.getElementById('wzLogoPreview').style.display = '';
            requestRender();
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);

    // Im Hintergrund auf Server speichern
    const status = document.getElementById('wzLogoUploadStatus');
    status.textContent = 'Wird gespeichert…';
    status.style.display = '';
    status.style.color = 'var(--bs-secondary-color)';
    const fd = new FormData();
    fd.append('wz_logo', file);
    fetch('bobawatermark.php', { method: 'POST', headers: { 'X-WZ-Logo-Upload': '1' }, body: fd })
        .then(r => r.json())
        .then(d => {
            status.textContent = d.ok ? '✓ Gespeichert in medien/wasserzeichen/' : '⚠ ' + (d.error || 'Fehler');
            status.style.color = d.ok ? 'var(--bs-success)' : 'var(--bs-danger)';
            setTimeout(() => { status.style.display = 'none'; }, 3000);
        })
        .catch(() => {
            status.textContent = '⚠ Verbindungsfehler';
            status.style.color = 'var(--bs-danger)';
            setTimeout(() => { status.style.display = 'none'; }, 3000);
        });
}

// Logo-Dropzone Events
const logoDrop  = document.getElementById('wzLogoDrop');
const logoInput = document.getElementById('wzLogoInput');
logoDrop.addEventListener('click', () => logoInput.click());
logoInput.addEventListener('change', e => loadLogoFile(e.target.files[0]));
['dragover','dragenter'].forEach(ev =>
    logoDrop.addEventListener(ev, e => { e.preventDefault(); logoDrop.classList.add('drag-over'); })
);
['dragleave','dragend','drop'].forEach(ev =>
    logoDrop.addEventListener(ev, e => { e.preventDefault(); logoDrop.classList.remove('drag-over'); })
);
logoDrop.addEventListener('drop', e => {
    e.preventDefault();
    loadLogoFile(e.dataTransfer.files[0]);
});

// ── Bild-Modus Slider ─────────────────────────────────────────────────────────
document.getElementById('wzLogoScale').addEventListener('input', e => {
    state.wzLogoScale = parseInt(e.target.value);
    document.getElementById('wzLogoScaleVal').textContent = e.target.value + '%';
    requestRender();
});

document.getElementById('wzLogoOpacity').addEventListener('input', e => {
    state.wzLogoOpacity = parseFloat(e.target.value);
    document.getElementById('wzLogoOpacityVal').textContent = Math.round(e.target.value * 100) + '%';
    requestRender();
});

document.getElementById('wzLogoRot').addEventListener('input', e => {
    state.rotation = parseInt(e.target.value);
    document.getElementById('wzLogoRotVal').textContent = e.target.value + '°';
    requestRender();
});

// ── Controls ──────────────────────────────────────────────────────────────────
document.getElementById('wzText').addEventListener('input', e => {
    state.text = e.target.value;
    requestRender();
});

document.getElementById('wzSize').addEventListener('input', e => {
    state.fontSize = parseInt(e.target.value);
    document.getElementById('wzSizeVal').textContent = e.target.value + 'px';
    requestRender();
});

document.getElementById('wzOpacity').addEventListener('input', e => {
    state.opacity = parseFloat(e.target.value);
    document.getElementById('wzOpacityVal').textContent = Math.round(e.target.value * 100) + '%';
    requestRender();
});

// Schriftarten
document.querySelectorAll('.wz-font-option').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.wz-font-option').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        state.font = btn.dataset.font;
        requestRender();
    });
});

// ── Vorschau ──────────────────────────────────────────────────────────────────
document.getElementById('wzPreviewBtn').addEventListener('click', () => {
    showUI = !showUI;
    const btn   = document.getElementById('wzPreviewBtn');
    const badge = document.getElementById('wzPreviewBadge');
    if (showUI) {
        btn.innerHTML   = '<i class="bi bi-eye me-1"></i>Vorschau';
        badge.style.display = 'none';
    } else {
        btn.innerHTML   = '<i class="bi bi-pencil me-1"></i>Bearbeiten';
        badge.style.display = '';
    }
    requestRender();
});

// ── Download ──────────────────────────────────────────────────────────────────
document.getElementById('wzDownloadBtn').addEventListener('click', () => {
    if (!state.img) return;
    const prevShowUI = showUI;
    showUI = false;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(state.img, 0, 0, canvas.width, canvas.height);
    drawWatermark(false);
    showUI = prevShowUI;
    const a = document.createElement('a');
    a.href     = canvas.toDataURL('image/png', 0.92);
    a.download = state.filename + '_wz.png';
    a.click();
    requestRender();
});

// ── Reset ─────────────────────────────────────────────────────────────────────
document.getElementById('wzResetBtn').addEventListener('click', () => {
    if (!state.img) return;
    state.x        = canvas.width  / 2;
    state.y        = canvas.height / 2;
    state.rotation = 0;
    requestRender();
});

// ── Init ──────────────────────────────────────────────────────────────────────
buildColorGrid();

// Vorhandenes Logo sofort im Preview-Strip anzeigen (noch nicht in Canvas laden)
if (WZ_LOGO_EXISTS) {
    const initImg = new Image();
    initImg.onload = () => {
        document.getElementById('wzLogoThumb').src             = WZ_LOGO_URL;
        document.getElementById('wzLogoName').textContent      = 'wz_logo.png';
        document.getElementById('wzLogoSub').textContent       = initImg.naturalWidth + '×' + initImg.naturalHeight + ' px';
        document.getElementById('wzLogoPreview').style.display = '';
    };
    initImg.src = WZ_LOGO_URL;
}
</script>
</body>
</html>