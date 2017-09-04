server {
    listen 80;

    return         301 https://$http_host$request_uri;
}

server {

    listen 443 ssl;
    server_name _;

    proxy_connect_timeout 300s;
    proxy_read_timeout 300s;

    gzip             on;
    gzip_comp_level  2;
    gzip_min_length  1000;
    gzip_proxied     expired no-cache no-store private auth;
    gzip_types       text/plain application/x-javascript application/javascript text/xml text/css application/xml;

    ssl on;
    ssl_protocols               TLSv1 TLSv1.1 TLSv1.2;
    ssl_certificate             /certs/ssl.crt/server.crt;
    ssl_certificate_key         /certs/ssl.key/server.key;
    ssl_dhparam                 /certs/ssl.dhparam/dhparam.pem;
    ssl_prefer_server_ciphers   on;
    ssl_session_cache           shared:SSL:10m;
    # ssl_ciphers                 'ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA384:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA256';
    ssl_ciphers                 'EECDH+ECDSA+AESGCM EECDH+aRSA+AESGCM EECDH+ECDSA+SHA384 EECDH+ECDSA+SHA256 EECDH+aRSA+SHA384 EECDH+aRSA+SHA256 EECDH+aRSA+RC4 EECDH EDH+aRSA RC4 !aNULL !eNULL !LOW !3DES !MD5 !EXP !PSK !SRP !DSS';

    add_header                  X-Frame-Options "SAMEORIGIN" always;
    add_header                  X-Xss-Protection "1; mode=block" always;
    add_header                  X-Content-Type-Options "nosniff" always;

    client_max_body_size        100M;

    location ~* ^/(_profiler|_wdt|api)(?<api_path>/.*) {
        set $api_root /srv/web;
        set $api_entrypoint app.php;
        fastcgi_pass php:9000;
        include fastcgi_params;
        fastcgi_param  SCRIPT_FILENAME    $api_root/$api_entrypoint;
        fastcgi_param  SCRIPT_NAME        $api_entrypoint;
        fastcgi_read_timeout 600;
    }
}
