# Minetest to POVRAY exporter v0.1

# ToC:

-- INTRODUCTION

-- REQUIREMENTS

-- USAGE

-- KNOWN ERRORS, LIMITATIONS AND TO-DOs

===============================

# INTRODUCTION

"Minetest to POVRAY exporter" (named MtPe for short) is (going to be) a set of scripts to export maps (or parts of) to POVRAY-compatible
scenery file. Then one is allowed to make renders and animations of the scenery.
You might ask "Why did you use PHP?" I say "Why not!" It has got several functions that make your life easier (such as string-indexed arrays).

===============================

# REQUIREMENTS

MtPe requires python2, php5, mysql and povray >3.5.
It is designed to run on linux.
During development it was tested with povray 3.6 and 3.7, both working well.
MySQL currently stores world's data exported from sqlite dataset, but sqlite3 functions in php will work as well.

===============================

# USAGE

[TODO]

===============================

# KNOWN ERRORS, LIMITATIONS AND TO-DOs

- POVRAY uses a lot of memory and CPU - it is recommended NOT to run MtPe on the same server that holds the world itself.
- Due to RAM limitations you need to carefully set range to be exported. Exporting too many chunks will crash POVRAY or hang your machine. Export script will work correctly, as it reads one chunk at a time. Caution: it produces HUGE text files!
- It is recommended to have pre-existing set of textures and models for POVRAY to work with. The "export_dictionary.php" script will go through any chunk in the world and make POVRAY-compatible files (blocks.inc and blocks_textures.inc) to be modified by the user - one of which holds the blocks and objects, the other holds textures.
- MySQL is currently being used to store world's data. I'd like to stick with it for now, as I am going to recompile Minetest to use it as a backend. Beware NOT TO USE InnoDB engine there! Use MyISAM as it will run faster.
- As "export_dictionary.php" has no database to save it's results it will go through EVERY chunk every time you run it. Scanning of 990000 chunks took about 5 hours on a dual core pentium 4 machine with 3GB RAM. Currently it reads 100 chunks at a time from the dataset to the queue. Tuning this value can improve performance.
- "unzlib.py" and "unpack_coords.py" scripts are just workarounds for me being stupid and unable to write working procedures for unpacking chunk's coordinates and unpacking chunk's data.
