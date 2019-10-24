#/bin/bash

/usr/sbin/postfix start
exec /usr/libexec/s2i/run
