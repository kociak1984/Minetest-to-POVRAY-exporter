#!/usr/bin/env python2

import zlib
import sys

str_object1 = open('/tmp/map', 'rb').read()
str_object2 = zlib.decompress(str_object1)
f = open('/tmp/map.uncompressed', 'wb')
f.write(str_object2)
f.close()

str_object1 = open('/tmp/dict', 'rb').read()
str_object2 = zlib.decompress(str_object1)
f = open('/tmp/dict.uncompressed', 'wb')
f.write(str_object2)
f.close()

decom = zlib.decompressobj()
d = decom.decompress(str_object1)
str_object3 = decom.unused_data
f = open('/tmp/leftover.uncompressed', 'wb')
f.write(str_object3)
f.close()
