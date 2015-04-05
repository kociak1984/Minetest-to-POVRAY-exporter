#!/usr/bin/env python2

import sys

def getIntegerAsBlock(i):
	x = unsignedToSigned(i % 4096, 2048)
	i = int((i - x) / 4096)
	y = unsignedToSigned(i % 4096, 2048)
	i = int((i - y) / 4096)
	z = unsignedToSigned(i % 4096, 2048)
	print str(x) + ' ' + str(y) + ' ' + str(z)
#	return x,y,z

def unsignedToSigned(i, max_positive):
	if i < max_positive:
		return i
	else:
		return i - 2*max_positive

a = int(sys.argv[1])

getIntegerAsBlock(a)
