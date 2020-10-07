#/bin/bash
#
# Start via 'docker run'.

docker run -ti --rm \
	-p 8080:8080 \
	-p 8443:8443 \
	--env-file environment.sh \
	readingroom:new
