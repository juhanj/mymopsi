from subprocess import Popen, PIPE

commonFormats = [
    "png", "png8", "png00", "png24", "png32", "png48", "png64", "jpeg", "ico", "gif", "bmp", "bmp2", "bmp3", "tiff", "webp", "svg"
]

allResolutions = {
    "1:1" : ["1x1", "400x400", "600x600", "1000x1000", "5000x5000"],
    "3:2" : ["1080x720", "1152x768", "1200x800", "1440x960", "1728x1152", "1800x1200", "1920x1280", "2736x1824"],
    "4:3" : ["640x480", "800x600", "960x720", "1024x768", "1280x960", "1400x1050", "1440x1080", "1600x1200", "1856x1392", "1920x1440", "2048x1536"],
    "16:9" : ["1024x576", "1152x648", "1280x720", "1366x768", "1600x900", "1920x1080", "2560x1440", "3840x2160", "7680x4320"],
    "16:10" : ["1280x800", "1440x900", "1680x1050", "1920x1200", "2560x1600"],
    "unique" : ["13312x6656"/*Panorama*/, "1080x1920"/*potrait*/]
}

formatTestResolution = [
    "1x1", "100x100", "640x480", "1280x720"
]

for f in commonFormats :
    for reso in formatTestResolution :
        # magick convert -size 1x1 xc:red ./img/2-different-formats/ext_1x1.ext
        process = Popen(
            ['magick', 'convert', '-size', reso, 'xc:red',
             './img/2-different-formats/{1}_{0}.{1}'.format(reso,f)],
            stdout=PIPE, stderr=PIPE
        )
        output = process.communicate()
        print (output)

for reso in allResolutions :
    # magick convert -size 1x1 xc:red ./img/3-different-resolution/ext_1x1.ext
    process = Popen(
        [ 'magick', 'convert', '-size', reso, 'xc:purple',
         './img/2-different-formats/jpeg_{0}.jpeg'.format(reso) ],
        stdout=PIPE, stderr=PIPE
    )
    output = process.communicate()
    print (output)

# magick convert -size 1920x1080 plasma:fractal -blur 0x2 -swirl 180 -shave 20x20 ./img/4-big-images/png_1920x1080.png
