# MagicPort

# Create Laravel project
```shell
docker-compose run --rm composer create-project --prefer-dist laravel/laravel .
```

# Build Container

```shell
docker-compose up -d --build nginx
```

# Folder permission problem
```shell
docker compose exec -it -u root php chown -R www-data:www-data .
```