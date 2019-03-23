MyMopsi
---

## Folders and files:

- `cfg/` : Possible config files
    - `config.ini.php` : contains config info. File in .php format, for security reasons. 

- `class/` : PHP class files
    - `collection.class.php` : For handling various collection stuff
    - `dbconnection.class.php` : DBConnection-class, for PDO/MySQL

- `components/`
    - `_start.php` : Some starting PHP logic for all pages, initializing classes and such.
    - `html-head.php` : HTML-head component. All pages have the same head.
    - `html-header.php` : HTML-header component. All pages have the same header.
    - `html-footer.php` : HTML-footer component. All pages have same footer.

- `css/`
    - `main.css` : Main CSS for the site
    - Each page might have their own css-file, dunno yet.

- `db-mysql/` : MySQL database info
    - `database.sql`
    - `install.php` : automatic installation script for above database.
    
- `js/`
    - `main.js` : Main javascript for the site
    - Each page might also have their own js-file, dunno yet.

- `ajax_handler.php` : all ajax-requests are handled by this file.

- Plus some other various files for pages and stuff.

## 3rd party components:

- Exiftool by Phil Harvey
    - for reading metadata of images
    
- Google Material Icons
    - included as a web-font

- Modern-normalize CSS (v 0.5.0)
