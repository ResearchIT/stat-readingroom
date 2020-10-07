# Jason White - stat ReadingRoom app

# Use an official Python runtime as a parent image.
#FROM readingroom-php-centos7
FROM centos/php-72-centos7

USER root

WORKDIR /var/www/html

#COPY . /var/www/html
COPY . /opt/app-root/src

# Make port 8443 available to the world outside this container.
EXPOSE 8443

CMD ["/usr/libexec/s2i/run"]

