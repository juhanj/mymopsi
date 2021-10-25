import os
import sys
from subprocess import Popen, PIPE

def differentFormats ( formats ) :
    os.mkdir('./img-dataset/custom-format-test')
    for [format, ext] in formats :
        # magick convert -font Consolas -pointsize 72 label:'#' [outputfile]
        process = Popen(
            ['magick', 'convert', '-size', '400x400', '-font', 'Consolas',
             '-pointsize', '100', '-gravity', 'center', "caption:{0}".format(format),
            './img-dataset/custom-format-test/format-{0}.{1}'.format(ext,ext)],
            stdout=PIPE, stderr=PIPE
        )
        output = process.communicate()
        print (format, output)
    return

def differentResolutions ( allResolutions ) :
    os.mkdir('./img-dataset/custom-resolutions-test')
    for aspectRatio, resolutions in allResolutions.items() :
        for reso in resolutions :
	        # magick convert -size {resolution} caption:{resolution} [ouputfile]
	        process = Popen(
	            [ 'magick', 'convert', '-size', reso, '-font', 'Consolas',
	             '-pointsize', '100', '-gravity', 'center',
	             'caption:{0} {1}'.format(aspectRatio,reso),
	             './img-dataset/custom-resolutions-test/reso-{0}-{1}.png'.format(aspectRatio.replace(":",'_'),reso) ],
	            stdout=PIPE, stderr=PIPE
	        )
	        output = process.communicate()
	        print (aspectRatio, reso, output)
    return

def bigImages ( numberOfImages ) :
    os.mkdir('./img-dataset/custom-big-images-test')
    # magick convert -size 1920x1080 plasma:fractal -blur 0x2 -swirl 180 ./img/4-big-images/png_1920x1080.png
    for i in range(0, numberOfImages) :
        process = Popen(
            [ 'magick', 'convert', '-size', "7680x4320",
            'plasma:fractal', '-blur', '0x1', '-swirl', '250',
             './img-dataset/custom-big-images-test/big-plasma_fractal-{0}.jpeg'.format(i) ],
            stdout=PIPE, stderr=PIPE
        )
        output = process.communicate()
        print ( "{0}->{1}".format(i,output) )

    for i in range(0, numberOfImages) :
        process = Popen(
            [ 'magick', 'convert', '-size', "3840x2160",
            'xc:', '+noise', 'Random', '-blur', '0x1',
             './img-dataset/custom-big-images-test/big-random_noise-{0}.jpeg'.format(i) ],
            stdout=PIPE, stderr=PIPE
        )
        output = process.communicate()
        print ( "{0}->{1}".format(i,output) )

    return

def manyImages ( numberOfImages ) :
    os.mkdir('./img-dataset/custom-many-images')
    # magick convert -font Consolas -pointsize 72 label:'#' many-#.png
    for i in range(0,numberOfImages) :
        process = Popen(
            [ 'magick', 'convert', '-font', 'Consolas', '-pointsize', '72',
            'label:{0}'.format(i),
            './img-dataset/custom-many-images/number-{0}.png'.format(i) ],
            stdout=PIPE, stderr=PIPE
        )
        output = process.communicate()
        print ( i,output )
    return

commonFormats = [
    ['PNG',"png"], ['JPEG',"jpg"], ['GIF /ɡɪf/',"gif"],
    ['BMP',"bmp"], ['TIFF',"tiff"], ['WebP',"webp"],
    ['SVG',"svg"], ['AVIF','avif'], ['JPEG XL','jxl'],
    ['JPEG 2000', 'jp2'], ['HEIF','heif'],
]

allResolutions = {
    # 1:1
    "1:1":["50x50", "400x400", "600x600", "1000x1000", "5000x5000"],
    # 3:2
    "3:2":["1080x720", "1200x800", "1440x960", "1800x1200", "1920x1280"],
    # 4:3
    "4:3":["640x480", "800x600", "960x720", "1280x960", "1600x1200", "1920x1440", "2048x1536"],
    # 16:9
    "16:9":["1024x576", "1280x720", "1600x900", "1920x1080", "2560x1440", "3840x2160", "7680x4320"],
    # 16:10
    "16:10":["1280x800", "1440x900", "1680x1050", "1920x1200", "2560x1600"],
    # panorama
    "panorama":["13312x6656"],
    # potrait
    "potrait":["1080x2400"]
}

try:
	os.mkdir('./img-dataset/')
except FileExistsError:
	# nothing
	print ("nothing")

if ( len(sys.argv) < 2 ) :
    print ('please give argument')

elif ( sys.argv[1] == 'all' ) :
    differentFormats( commonFormats )
    differentResolutions( allResolutions )
    bigImages()
    manyImages()

elif ( sys.argv[1] == 'format' ) :
    differentFormats( commonFormats )

elif ( sys.argv[1] == 'resolution' ) :
    differentResolutions( allResolutions )

elif ( sys.argv[1] == 'big' ) :
    bigImages( int(sys.argv[2]) if (len(sys.argv) > 2) else 1 )

elif ( sys.argv[1] == 'many' ) :
    manyImages( int(sys.argv[2]) if (len(sys.argv) > 2) else 100 )

print ("done")
