;<?php
;exit(); // For further security
;/*

[Database]
host = "localhost"
name = "database"
user = "root"
pass = ""

[Settings]
; Username length
username_min_len = 1
username_max_len = 50
; Password length
password_min_len = 8
password_max_len = 300
; Collection name/description length
; (min length is 0 (optional))
coll_name_max_len = 50
coll_descr_max_len = 300

[Misc]
; Localhost / server difference in Perl setup
perl = "./path/to/perl"
; Localhost / server differences in ImageMagick setup
imagemagick = "imagemagick convert"
; Where are the images saved on the server.
; Thumbnails for images are saved under each collections own directory
path_to_collections = "./path/to/collections/"
; Where are the mopsi images saved on the server
; This location is technically public if you know where to look
; but for easy of access, I put it here
path_to_mopsi_photos = "./path/to/mopsi_photos/"
; Where the code is after the web-root
web_root_path = "/mopsi_dev/mymopsi/"
; Google maps API key
gmaps_api_key = ""

[Config]
; The config-file on server is not located on web-root
; Different location for localhost and online-server
config = "config.ini"

[Testing]
perl = "path/to/perl"
exiftool = "./exiftool/exiftool"
testimg = "path/to/test/image"

;*/
;?>
