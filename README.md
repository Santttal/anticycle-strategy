# Anticycle-strategy

An PHP/Go application to check economic cycles. [More description](https://drive.google.com/file/d/1a71yh43BYtDIFGnXii-pgl3QbdPZqlaR/view?usp=sharing)

## Installation

```
$ docker-composer up -d
$ docker-compose exec php composer app.init
```

## Quick start

Open main page http://localhost:8000/

to sync data manually run

```
$ docker-compose exec bin/console instruments:sync
```

## Used tools
- Docker, docker-compose
- Golang, PHP 8
- Mysql
- Nginx + php-fpm
- Symfony 5.3, Doctrine
