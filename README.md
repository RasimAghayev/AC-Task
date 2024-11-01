# MagicPort

# Create Laravel project
```shell
docker-compose run --rm composer create-project --prefer-dist laravel/laravel .
```
# Create install
```shell
docker-compose run --rm composer install
```

# Build Container

```shell
docker-compose up -d --build nginx 
# other build problem
# DOCKER_BUILDKIT=0 docker-compose up --build nginx

```

# Folder permission problem
```shell
docker compose exec -it -u root php chown -R www-data:www-data .
```

### Create storage link
```bash
docker compose run --rm artisan  storage:link
```

### Migrate our migration
```bash
docker compose run --rm artisan  migrate
```

# Clear cache
```shell
 docker compose run --rm artisan optimize:clear
 ```


# JWT renew secret key
```shell
 docker compose run --rm artisan jwt:secret
 ```

