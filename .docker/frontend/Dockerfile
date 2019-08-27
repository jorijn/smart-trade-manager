FROM node:latest as assets

WORKDIR /app/

COPY ./package.json ./webpack.config.js ./yarn.lock /app/
COPY ./assets /app/assets/

RUN npm install
RUN npm run build

FROM nginx:stable-alpine

COPY .docker/frontend/nginx.conf /etc/nginx/nginx.conf

COPY public /app/public/
COPY --from=assets /app/public/build /app/public/build

RUN chown -R nginx.nginx /app/
