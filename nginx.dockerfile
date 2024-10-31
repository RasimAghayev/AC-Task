FROM nginx:stable-alpine

ADD ./server/nginx/default.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p /var/www/html/be /var/www/html/fe