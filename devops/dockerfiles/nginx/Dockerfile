FROM nginx:1.9

COPY . /etc/nginx/
COPY web /srv/
COPY boot.sh /usr/local/bin/boot.sh

CMD ["bash", "/usr/local/bin/boot.sh"]
