--------------------
LastModified — Custom dual-compat build by João Nogueira from original release 1.1.1 by Kudashev Sergey
--------------------

This package provides dual compatibility with MODX Revolution 2.x and MODX 3.x,
along with PHP 8.x hardening and security improvements for the lastModified snippet.

Changes in this build:
- Dual-compat: works on MODX 2.x and MODX 3.x without modification
- PHP 8.x: fixed undefined variable, added null guards, proper type checks
- Security: snippet now validates file paths stay within web root
- Lexicon: added Portuguese (pt) translations
- No behavioural changes to the core caching logic

--------------------
Original README by Kudashev Sergey:
--------------------
Author: Kudashev Sergey <kudashevs@gmail.com>
--------------------

This MODx Revolution plugin handles If-Modified-Since request header and returns Last-Modified response header
with the response code 304 when it is necessary (more info and site check on https://last-modified.com/en/)

--------------------
Feel free to suggest ideas/improvements/bugs on GitHub:
https://github.com/kudashevs/LastModified/issues
