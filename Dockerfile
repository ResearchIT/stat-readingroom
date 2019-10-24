# Jason White - stat ReadingRoom app

# Use an official Python runtime as a parent image.
#FROM readingroom-php-centos7
FROM php-centos7

USER root

# Set the working directory to /app.
WORKDIR /var/www/html

# Install any needed packages specified in requirements.txt.
#RUN pip install --trusted-host pypi.python.org -r requirements.txt
#RUN yum install -y mysql-devel php-mysqli php-mysqld
RUN yum install -y postfix

RUN postconf -e inet_protocols=ipv4
RUN postconf -e inet_interfaces=localhost
RUN postconf -e relayhost=[mailhub.iastate.edu]
RUN newaliases

# Copy custom startup file to /root.
COPY startup.sh /root
RUN chmod +x /root/startup.sh

# Copy the current directory contents into the container at /app.
#COPY . /var/www/html
COPY . /opt/app-root/src

# Make port 8443 available to the world outside this container.
EXPOSE 8443

# Run app.py when the container launches
#CMD ["python", "app.py"]
CMD ["sh", "-c", "/root/startup.sh"]
