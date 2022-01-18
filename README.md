# Anticycle-strategy

An PHP/Go application to check economic cycles. [More description](https://drive.google.com/file/d/1a71yh43BYtDIFGnXii-pgl3QbdPZqlaR/view?usp=sharing)

## Installation

```
$ docker-composer up -d
$ docker-compose exec php composer app.init
```

## Quick start

Open main page http://localhost:8081/

to sync data manually run

```
$ docker-compose exec bin/console instruments:sync
```

## Used tools
- Docker, docker-compose
- Golang (load external data), PHP 8.1 (web UI)
- Mysql (main storage)
- Redis (store data sync interval) 
- Nginx + php-fpm
- Symfony 6.0, Doctrine

## Upcoming
- tests
- gRPC
