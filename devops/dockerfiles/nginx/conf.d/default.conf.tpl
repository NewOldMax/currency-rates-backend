server {
    listen 444;
    port_in_redirect off;
    return         301 http://$host:8080/api/short-urls$request_uri;
}


server {

    listen 80;
    server_name _;

    proxy_connect_timeout 300s;
    proxy_read_timeout 300s;

    gzip             on;
    gzip_comp_level  2;
    gzip_min_length  1000;
    gzip_proxied     expired no-cache no-store private auth;
    gzip_types       text/plain application/x-javascript application/javascript text/xml text/css application/xml;

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
