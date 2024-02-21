# Сборка расширения для DB2


## Скачивание необходимого ПО

Для сборки расширения для работы с БД DB2 необходимо скачать:
1. Драйвер *IBM Data Server Driver Package (DS Driver)* для работы с DB2, с сайта [IBM](https://www.ibm.com/support/pages/download-initial-version-115-clients-and-drivers) - **ibm_data_server_driver_package_linuxx64_v11.5.tar.gz**
1. Pecl расширение [IBM_DB2](https://pecl.php.net/package/ibm_db2) - **ibm_db2-2.2.0.tgz**
1. Pecl расширение [PDO_IBM](https://pecl.php.net/package/PDO_IBM) - **PDO_IBM-1.6.1.tgz**


## Установка драйвера DS Driver

Создаем на сервере папку `/tmp/php_pecl`. Копируем в неё драйвер.

```shell script
mkdir /tmp/php_pecl
```

Устанавливаем IBM драйвер *DS Driver*

```shell script
tar -xzvf ibm_data_server_driver_package_linuxx64_v11.5.tar.gz
cd dsdriver
. ./installDSDriver
```

Запускаем скрипт для установки окружения

```shell script
chmod +x db2profile
./db2profile
```

Теперь у нас в папке `/tmp/php_pecl/dsdriver` есть необходимые библиотеки и 
заголовочные файлы для компиляции PHP расширения.


## Установка PHP расширения ibm_db2

Копируем в папку `/tmp/php_pecl` файл с PHP расширением - ibm_db2-2.2.0.tgz

```shell script
tar -xzvf ibm_db2-2.2.0.tgz
cd ibm_db2-2.2.0/
phpize
./configure --with-IBM_DB2=/tmp/php_pecl/dsdriver
make
make install
```

После выполнения команды `make install` файл с PHP расширением *ibm_db2.so*, 
который собрался в `/tmp/php_pecl/ibm_db2-2.2.0/modules`,
скопируется в папку с установленными модулями  `/usr/lib64/php/modules`


## Установка PHP расширения PDO_IBM

Копируем в папку /tmp/php_pecl файл с PHP расширением - PDO_IBM-1.6.1.tgz

```shell script
tar -xzvf PDO_IBM-1.6.1.tgz
cd PDO_IBM-1.6.1/
phpize
./configure --with-pdo-ibm=/tmp/php_pecl/dsdriver --includedir=/tmp/php_pecl/dsdriver
make
make install
```

После выполнения команды `make install` файл с PHP расширением *pdo_ibm.so*, 
который собрался в `/tmp/php_pecl/PDO_IBM-1.6.1/modules`,
скопируется в папку с установленными модулями  `/usr/lib64/php/modules`


## Подключение установленных расширений:

Все сгенерированные файлы ibm_db2.so и pdo_ibm.so  необходимо "включить" в php. 
Для этого создаем в папке /etc/php.d/ для каждого расширения 
конфигурацию в виде ini-файла.

Например:
создаем файл 30-ibm_db2.ini со след. содержимым extension=ibm_db2
создаем файл 30-pdo_ibm.ini со след. содержимым extension=pdo_ibm


## Проверка расширений DB2

Для проверки расширения можно воспользоваться 
командой просмотра установленных расширений: 

```shell script
[srvradw@vs1033 ~]$ php -m
```
