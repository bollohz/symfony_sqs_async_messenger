FROM docker.elastic.co/beats/filebeat:6.5.2

# Copy our custom configuration file
COPY filebeat.yaml /usr/share/filebeat/filebeat.yaml

USER root
# Create a directory to map volume with all docker log files
RUN mkdir -p /var/logs
RUN chown -R root /var/logs/
RUN chmod -R go-w /var/logs/
