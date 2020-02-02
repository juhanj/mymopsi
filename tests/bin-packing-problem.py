import numpy as np # Used for random generation of files
from pprint import pprint # for pretty print array

def fnc( array ) :
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
    nro_bins = len(bins)

    return [nro_bins, average_bin, min_bin, max_bin]

###############################################
    
nro_items = 2000
min_file_size = 0
max_file_size = 10
max_batch_size = 20
runs = 500

# Unsorted greedy
run_avg_nro_bins = 0
run_avg_bin_size = 0
run_min_bins = 2 * nro_items
run_max_bins = 0

for i in range(0,runs) :
    array = np.random.uniform(min_file_size,max_file_size,nro_items)
    r = fnc(array)

    if ( r[0] < run_min_bins ) :
        run_min_bins = r[0]
    if ( r[0] > run_max_bins ) :
        run_max_bins = r[0]

    run_avg_nro_bins += r[0]
    run_avg_bin_size += r[1]

run_avg_nro_bins = run_avg_nro_bins / runs
run_avg_bin_size = run_avg_bin_size / runs

print( run_avg_nro_bins, run_avg_bin_size, run_min_bins, run_max_bins )

# Sorted (desc) greedy
run_avg_nro_bins = 0
run_avg_bin_size = 0
run_min_bins = 2 * nro_items
run_max_bins = 0

for i in range(0,runs) :
    array = np.random.uniform(min_file_size,max_file_size,nro_items)
    array = -np.sort(-array)
    r = fnc(array)

    if ( r[0] < run_min_bins ) :
        run_min_bins = r[0]
    if ( r[0] > run_max_bins ) :
        run_max_bins = r[0]

    run_avg_nro_bins += r[0]
    run_avg_bin_size += r[1]

run_avg_nro_bins = run_avg_nro_bins / runs
run_avg_bin_size = run_avg_bin_size / runs

print( run_avg_nro_bins, run_avg_bin_size, run_min_bins, run_max_bins )
