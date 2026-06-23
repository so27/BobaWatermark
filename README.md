# BobaWatermark

![Screenshot](screenshot.png)

🇩🇪 **Deutsch** | 🇬🇧 [English](#english)

---

## 🇩🇪 Deutsch

Ein schlankes PHP-Tool, um Bilder direkt im Browser mit einem Text- oder Logo-Wasserzeichen zu versehen – lokal, ohne Datenbank, ohne externe Dienste.

### Funktionen

- **Text-Wasserzeichen**: frei wählbarer Text, Schriftart, Größe, Farbe und Transparenz
- **Logo-Wasserzeichen**: eigenes PNG/JPG/WebP/GIF/SVG als Logo hochladen
- Wasserzeichen per Drag & Drop frei positionieren, skalieren und rotieren
- Live-Vorschau direkt im Canvas
- Unterstützte Bildformate: JPG, PNG, WebP, GIF
- Fertig bearbeitetes Bild als Download
- Keine Datenbank, kein Tracking, läuft komplett lokal auf deinem Server

### Voraussetzungen

- PHP 7.4 oder neuer
- Webserver mit PHP-Unterstützung (Apache, nginx, o. ä.)

### Installation

1. Repository herunterladen oder klonen
2. Dateien auf den Webserver kopieren
3. Sicherstellen, dass der Ordner `medien/wasserzeichen/` vom Webserver beschreibbar ist (das Tool legt ihn bei Bedarf automatisch an, z. B. mit Rechten `0755`). Falls Probleme auftreten, manuell anlegen und Schreibrechte setzen:
   ```bash
   mkdir -p medien/wasserzeichen
   chmod 755 medien/wasserzeichen
   ```
4. Im Browser aufrufen – fertig

### Lizenz

MIT – siehe [LICENSE](LICENSE)

Entwickelt von [Sven Owsianowski](https://github.com/so27)

---

## 🇬🇧 English

A lightweight PHP tool for adding text or logo watermarks to images directly in the browser – local, database-free, no external services.

### Features

- **Text watermark**: custom text, font, size, color, and opacity
- **Logo watermark**: upload your own PNG/JPG/WebP/GIF/SVG logo
- Drag, scale, and rotate the watermark freely on the canvas
- Live preview directly on canvas
- Supported image formats: JPG, PNG, WebP, GIF
- Download the finished image
- No database, no tracking, runs entirely on your own server

### Requirements

- PHP 7.4 or newer
- A web server with PHP support (Apache, nginx, etc.)

### Installation

1. Download or clone this repository
2. Copy the files to your web server
3. Make sure the `medien/wasserzeichen/` folder is writable by the web server (the tool creates it automatically if needed, e.g. with `0755` permissions). If you run into issues, create it manually and set write permissions:
   ```bash
   mkdir -p medien/wasserzeichen
   chmod 755 medien/wasserzeichen
   ```
4. Open it in your browser – done

### License

MIT – see [LICENSE](LICENSE)

Made by [Sven Owsianowski](https://github.com/so27)
