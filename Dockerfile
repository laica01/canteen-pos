FROM dunglas/frankenphp:latest-php8.2
WORKDIR /app
COPY . .
CMD ["frankenphp", "run", "--config", "/app/Caddyfile"]