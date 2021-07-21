# Обновление PHP на RHEL8 до 7.4 

Проверяем какой модуль стоит в ОС
```shell script
yum module list php
```

Отключаем версию 7.2 по умолчанию и активируем 7.4
```shell script
yum module reset php:7.2
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
rm /etc/php.d/40-zip.ini
```

Проверяем версию php:
```shell script
[root@rhel8 tmp]# php -v
PHP 7.4.6 (cli) (built: May 12 2020 08:09:15) ( NTS )
Copyright (c) The PHP Group
Zend Engine v3.4.0, Copyright (c) Zend Technologies
    with Zend OPcache v7.4.6, Copyright (c), by Zend Technologies

```


```shell script
[root@94a32ff5e3de tmp]# pecl upgrade apcu
Устанавливаем libzip
[root@94a32ff5e3de tmp]# yum install libzip
Обновляем zip
[root@94a32ff5e3de tmp]# pecl upgrade zip
```

Устанавливаем расширение oci8
```shell script
pecl uninstall oci8
pecl install oci8-2.2.0
```
Устанавливаем расширение SQLSRV
```shell script
pecl uninstall sqlsrv
pecl install sqlsrv-5.8.1

pecl uninstall pdo_sqlsrv
pecl install pdo_sqlsrv-5.8.1
```

Устанавливаем расширение smbclient
```shell script
pecl uninstall smbclient
pecl install smbclient
```

Устанавливаем расширение Informix
```shell script
mkdir -p /tmp/php_pecl
cd /tmp/php_pecl/
curl https://pecl.php.net/get/PDO_INFORMIX-1.3.4.tgz --output PDO_INFORMIX-1.3.4.tgz
tar -zxvf PDO_INFORMIX-1.3.4.tgz
cd PDO_INFORMIX-1.3.4
phpize
./configure --with-pdo-informix=/opt/IBM/Informix_Client-SDK/
make
make install
```

Устанавливаем расширение MQSeries
```shell script
cd /tmp/php_pecl/
curl https://pecl.php.net/get/mqseries-0.15.0.tgz --output mqseries-0.15.0.tgz
tar -zxvf mqseries-0.15.0.tgz
cd mqseries-0.15.0
phpize
./configure --with-libdir=lib64
make
make install
```
