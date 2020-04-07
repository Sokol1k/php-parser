# PHP Parser Advanced
Многопоточный, рекурсивный парсер.
Для запуска проекта неоходимо:
- Перейти в папку `app`
```sh
$ cd app/
```
- Установить пакеты
```sh
$ composer install
```
- Настроить файл .env:
```sh
REDIS_SCHEME=tcp
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

MYSQL_DRIVER=mysql
MYSQL_HOST=localhost
MYSQL_DATABASE=database
MYSQL_USERNAME=root
MYSQL_PASSWORD=password
MYSQL_CHARSET=utf8
MYSQL_COLLACTION=utf8_unicode_ci
MYSQL_PREFIX=''

QUANTITY_STREAMS=5 //Количество потоков

DEBUG=false
```
- Запускаем миграции 
```sh
$ composer run-script migration
```
- Запустить проект
```sh
$ composer run-script start
```