# Обновление PHP на RHEL8 до 7.4 

Проверяем какой модуль стоит в ОС
```shell script
yum module list php
```

Отключаем версию 7.2 по умолчанию
```shell script
yum module reset php:7.2
```
Активируем модуль 7.4
```shell script
yum module enable php:7.4
```
Проверяем
```shell script
yum module list php
```
Устанавливаем php версии 7.4:
```shell script
yum install php php-mysqlnd php-bcmath php-devel php-gd php-intl php-json php-ldap php-mbstring php-odbc php-pgsql php-xml php-xmlrpc php-soap php-dba php-pecl-zip php-gmp php-opcache php-pecl-apcu php-pear
```
Удаляем модули которые были в php 7.2 и ставим их по новой:
```shell script
rm /etc/php.d/50-mqseries.ini
rm /etc/php.d/50-oci8.ini
rm /etc/php.d/50-pdo_informix.ini'
rm /etc/php.d/50-pdo_sqlsrv.ini
rm /etc/php.d/50-smbclient.ini
rm /etc/php.d/50-sqlsrv.ini
```
