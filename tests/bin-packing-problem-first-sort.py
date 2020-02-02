"""
This file is for testing the bin packing problem
for file uploads, and different solutions.
As I'm handling fake values here, and not assessing
the server side processing cost (in time) here at all,
this is purely for minimizing the number of requests.

This is greedy, but first sort the initial array
"""

import numpy as np # Used for random generation of files
from pprint import pprint # for pretty print array

nro_items = 100
min_file_size = 0
max_file_size = 10
max_batch_size = 20

# Our list of files, from 0-10 MB (10 being max)
array = np.random.uniform(min_file_size,max_file_size,nro_items)
#array = -np.sort(-array)
bins = []

bin = {'size':0,'items':[]}

for item in array:
    
    if ( (item + bin['size']) > max_batch_size ) :
        bins.append(bin)
        bin = {'size':0,'items':[]}

    bin['items'].append(item)
    bin['size'] += item

average_bin = 0
min_bin = 20
max_bin = 0

for bin in bins :
    average_bin += bin['size']
    if ( bin['size'] < min_bin ) :
        min_bin = bin['size']
    if ( bin['size'] > max_bin ) :
        max_bin = bin['size']

average_bin = average_bin / len(bins)

print("Nro of bins: ", len(bins))
print("Average bin: ", average_bin)
print("Range: ",min_bin, "-", max_bin)

# pprint(bins)

