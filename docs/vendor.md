# Installing Vendor Libraries

This document explains how to install the required vendor libraries for the collab-web application's backend. The backend uses PHP and relies on Composer to manage dependencies.

## Folder structure

```bash
./vendor/
├── font
│   ├── courierbi.php
│   ├── courierb.php
│   ├── courieri.php
│   ├── courier.php
│   ├── helveticabi.php
│   ├── helveticab.php
│   ├── helveticai.php
│   ├── helvetica.php
│   ├── symbol.php
│   ├── timesbi.php
│   ├── timesb.php
│   ├── timesi.php
│   ├── times.php
│   └── zapfdingbats.php
├── fpdf.css
├── fpdf.php
├── Image.php
├── images
│   ├── layers-2x.png
│   ├── layers.png
│   ├── marker-icon-2x.png
│   ├── marker-icon.png
│   └── marker-shadow.png
├── leaflet.css
├── leaflet.js
├── leaflet.js.map
├── pell.min.css
├── pell.min.js
├── phpmailer
│   └── phpmailer
│       └── src
│           ├── Exception.php
│           ├── PHPMailer.php
│           └── SMTP.php
└── Uploader.php
```

# Vendor Folder Structure and Content

The vendor folder is used to store third-party libraries, scripts, and resources that are not directly maintained by the project developers. This structure helps to clearly separate external dependencies from the project's custom code. Here's an explanation of the correct way to organize the vendor folder based on the given structure:

## Folder Organization

### Font Files

The `font` directory contains PHP files for various fonts:

- Courier (regular, bold, italic, bold italic)
- Helvetica (regular, bold, italic, bold italic)
- Times (regular, bold, italic, bold italic)
- Symbol
- Zapf Dingbats

These font files are likely used for PDF generation or text rendering purposes[1].

### Image Resources

The `images` folder contains assets for map-related functionality:

- `layers-2x.png` and `layers.png`: Icons for layer controls
- `marker-icon-2x.png` and `marker-icon.png`: Map marker icons
- `marker-shadow.png`: Shadow image for map markers

These images are typically used with mapping libraries like Leaflet.

### CSS Files

- `fpdf.css`: Styling for the FPDF library
- `leaflet.css`: Styles for the Leaflet mapping library
- `pell.min.css`: Minified CSS for the Pell WYSIWYG editor

### JavaScript Files

- `leaflet.js` and `leaflet.js.map`: Leaflet mapping library and its source map
- `pell.min.js`: Minified JavaScript for the Pell WYSIWYG editor

### PHP Libraries

- `fpdf.php`: The main FPDF library file for PDF generation
- `Image.php`: an image uploader library (Class CoffeeCode Image)[https://github.com/robsonvleite]
- `Uploader.php`: A file upload handling library (Class CoffeeCode Uploader)[https://github.com/robsonvleite]

### PHPMailer

The `phpmailer` directory contains the PHPMailer library for sending emails from (The PHPMailer GitHub project)[https://github.com/PHPMailer/PHPMailer/] :

- `Exception.php`: Custom exception handling
- `PHPMailer.php`: Main PHPMailer class
- `SMTP.php`: SMTP mailing functionality


Citations:
[1] https://github.com/Collaboratorio-Alba/mapping-public-spac

