from subprocess import Popen, PIPE

# magick convert -size 1x1 xc:red ./img/test_test.jpeg

process = Popen(
    ['magick', 'convert', '-size', '10x10', 'xc:red', './img/test_test.jpeg'],
    stdout=PIPE, stderr=PIPE
)

output = process.communicate()

print (output)
