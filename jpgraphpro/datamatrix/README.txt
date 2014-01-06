17 Oct 2007,

Verification
============
The verification of the module for both ECC200 and the older ECC140 is fully
completed according to the ISO/IEC 16022E specification and no issues have
been found.

Note:
The verification has been able to uncover an error in the official
"black box" reference implementation used to verify the datamatrix
standard in the largest 144x144 size of datamatrix code. This means that the
result of the JpGraph for the largest 144x144 may differ from other
implementations. However, we have received confirmation from the ISO committee
overseeing this standard that our interpretation is correct and they have
contacted other manufacturers responsible for the defunct implementation to
update there offering. In practice this is not an issue since today there are
no widely spread scanners with the ability to read the very large 144x144
datamatrix code.

Documentation
=============
The documentation is available in the main "docs/" directory and examples
on the usage of the module is available under "examples/".

Demo application
================
There is a demo application available under "datamatrix_demoapp/". 
Just point your browser to the directory where the files have been installed.

Omissions
=========

The documentation for the older datamatrix standard (which uses convolutional
error correcting coding) is not included. Since the standard mandates that
all new system implementation using Datamatrix encoding should use the newer
ECC-200 error correcting coding schema this should pose no problem for the
majority of the users.


