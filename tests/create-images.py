import sys
from subprocess import Popen, PIPE

def differentFormats ( formats, resolutions ) :
    for f in formats :
        for reso in resolutions :
            # magick convert -size 1x1 xc:red ./img/2-different-formats/ext_1x1.ext
            process = Popen(
                ['magick', 'convert', '-size', reso, 'gradient:white-red',
                 './img/2-different-formats/{1}-{0}.{1}'.format(reso,f)],
                stdout=PIPE, stderr=PIPE
            )
            output = process.communicate()
            print (output)
    return

def differentResolutions ( resolutions ) :
    for reso in resolutions :
        # magick convert -size {resolution} xc:red ./img/3-different-resolutions/jpeg_{reso}.jpeg
        process = Popen(
            [ 'magick', 'convert', '-size', reso, 'gradient:blue-purple',
            './img/3-different-resolutions/jpeg-{0}.jpeg'.format(reso) ],
            stdout=PIPE, stderr=PIPE
        )
        output = process.communicate()
        print (output)
    return

def bigImages ( numberOfImages ) :
    # magick convert -size 1920x1080 plasma:fractal -blur 0x2 -swirl 180 ./img/4-big-images/png_1920x1080.png
    for i in range(0, numberOfImages) :
        process = Popen(
            [ 'magick', 'convert', '-size', "7680x4320",
            'plasma:fractal', '-blur', '0x1', '-swirl', '250',
             './img/4-big-images/big-plasma_fractal-{0}.jpeg'.format(i) ],
            stdout=PIPE, stderr=PIPE
        )
        output = process.communicate()
        print ( "{0}->{1}".format(i,output) )

    for i in range(0, numberOfImages) :
        process = Popen(
            [ 'magick', 'convert', '-size', "3840x2160",
            'xc:', '+noise', 'Random', '-blur', '0x1',
             './img/4-big-images/big-random_noise-{0}.jpeg'.format(i) ],
            stdout=PIPE, stderr=PIPE
        )
        output = process.communicate()
        print ( "{0}->{1}".format(i,output) )

    return

def manyImages ( numberOfImages ) :
    # magick convert -size 20x20 gradient:red-black ./img/5-many-images/jpeg-20x20-i.png
    for i in range(0,numberOfImages) :
        process = Popen(
            [ 'magick', 'convert', '-size', "20x20", 'gradient:red-black',
             './img/5-many-images/jpeg-20x20-{0}.jpeg'.format(i) ],
            stdout=PIPE, stderr=PIPE
        )
        output = process.communicate()
        print ( "{0}->{1}".format(i,output) )
    return

commonFormats = [
    "png", "png8", "png00", "png24", "png32", "png48", "png64", "jpeg",
    "ico", "gif", "gif87", "bmp", "bmp2", "bmp3", "tiff", "webp", "svg", "flif"
]

formatTestResolutions = [
    "1x1", "100x100", "640x480", "1280x720"
]

allResolutions = [
    # 1:1
    "1x1", "400x400", "600x600", "1000x1000", "5000x5000",
    # 3:2
    "1080x720", "1152x768", "1200x800", "1440x960", "1728x1152", "1800x1200", "1920x1280", "2736x1824",
    # 4:3
    "640x480", "800x600", "960x720", "1024x768", "1280x960", "1400x1050", "1440x1080", "1600x1200", "1856x1392", "1920x1440", "2048x1536",
    # 16:9
    "1024x576", "1152x648", "1280x720", "1366x768", "1600x900", "1920x1080", "2560x1440", "3840x2160", "7680x4320",
    # 16:10
    "1280x800", "1440x900", "1680x1050", "1920x1200", "2560x1600",
    # special -- panorama, potrait
    "13312x6656", "1080x1920"
]

if ( len(sys.argv) < 2 ) :
    print ('please give argument')

elif ( sys.argv[1] == 'all' ) :
    differentFormats( commonFormats, formatTestResolutions )
    differentResolutions( allResolutions )
    bigImages()
    manyImages()

elif ( sys.argv[1] == 'format' ) :
    differentFormats( commonFormats, formatTestResolutions )

elif ( sys.argv[1] == 'resolution' ) :
    differentResolutions( allResolutions )

elif ( sys.argv[1] == 'big' ) :
    bigImages( int(sys.argv[2]) if (len(sys.argv) > 2) else 1 )

elif ( sys.argv[1] == 'many' ) :
    manyImages( int(sys.argv[2]) if (len(sys.argv) > 2) else 100 )

print ("done")
