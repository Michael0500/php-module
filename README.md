Установка PHP на RHEL8
=

Для начала обновим установленные по умолчанию пакеты:
```shell script
yum update
```

Устанавливаем php и библиотеки доступные в репозитории:
```shell script
yum install php php-mysqlnd php-bcmath php-devel php-gd php-intl php-json php-ldap php-mbstring php-odbc php-pgsql php-xml php-xmlrpc php-soap php-dba php-pecl-zip php-gmp php-opcache php-pecl-apcu php-pear
```

Библиотека smbclient:
-
https://github.com/eduardok/libsmbclient-php

Для установки расширения `smbclient` необходимо добавить репозиторий, для этого в папке `/etc/yum.repos.d/` создать файл `rhel-localrepo.repo` со след. содержимым:
```ini
[rhel8-smbclient]
name=SmbclientRepo for RHEL 8
baseurl=https://<server-path>
enabled=1
gpgcheck=0
```
Устанавливаем зависимости:
```shell script
yum install samba-client libsmbclient libsmbclient-devel make
```
Теперь необходимо установить само расширение:
```shell script
pecl install smbclient
```
В папке `/usr/lib64/php/modules` появится файл `smbclient.so`


Библиотека Informix:
-
https://www.php.net/manual/ru/ref.pdo-informix.php

```shell script
yum install ncurses-compat-libs
```
Скачиваем Informix Client SDK с сайта IBM https://www.ibm.com/products/informix/developer-tools (я использовал версию ibm.csdk.4.50.FC5.LNX.tar) распаковываем архив и переходим в эту папку
```shell script
mkdir -p /tmp/php_pecl/InformixClientSDK/
cd /tmp/php_pecl/InformixClientSDK/
tar -xvf /home/ibm.csdk.4.50.FC5.LNX.tar
./installclientsdk
```
SDK для клиента должна установиться в директорию `/opt/IBM/Informix_Client-SDK`

Скачиваем PDO_INFORMIX:
```shell script
cd /tmp/php_pecl
curl https://pecl.php.net/get/PDO_INFORMIX-1.3.4.tgz --output PDO_INFORMIX-1.3.4.tgz
# Распаковываем архив:
tar -zxvf PDO_INFORMIX-1.3.4.tgz
cd /tmp/php_pecl/PDO_INFORMIX-1.3.4/
# Конфигурируем php-расширение informix
phpize
./configure --with-pdo-informix=/opt/IBM/Informix_Client-SDK
make
make install
```
В папке `/usr/lib64/php/modules` должен появиться файл `pdo_informix.so`


Библиотека Oracle OCI8:
-
(https://www.php.net/manual/ru/oci8.installation.php)

Для установки расширения oci8 необходима Oracle Instantclient. 
Добавляем репозиторий, для этого в папке `/etc/yum.repos.d/` создать файл `oracle.repo` со след. содержимым:
```ini
[oracle_instantclient]
name=LocalRepo oracle client for RHEL 8
baseurl=http://<server-path>
enabled=1
gpgcheck=0
```

Устанавливаем зависимости:
```shell script
yum install oracle-instantclient19.6-basic oracle-instantclient19.6-devel libnsl
```
Я скачивал с сайта Oracle (https://www.oracle.com/database/technologies/instant-client/downloads.html) 
и устанавливал их через:
```shell script
yum install libnsl libaio
rpm -i /home/oracle-instantclient19.11-basic-19.11.0.0.0-1.x86_64.rpm /home/oracle-instantclient19.11-devel-19.11.0.0.0-1.x86_64.rpm
```
Скачиваем oci8 (необходимо использовать именно эту версию `2.2.0` т.к. версии выше этой не работают с php7.2):
```shell script
pecl install oci8-2.2.0.tgz
```
В папке `/usr/lib64/php/modules` должен появиться файл `oci8.so`


Библиотека sqlsrv:
-
https://www.php.net/manual/ru/ref.pdo-sqlsrv.php

https://www.php.net/manual/ru/sqlsrv.installation.php
```shell script
yum install unixODBC-devel
pecl install sqlsrv-5.8.1.tgz
pecl install pdo_sqlsrv-5.8.1.tgz
```
В папке `/usr/lib64/php/modules` должны появиться файлы `sqlsrv.so` и `pdo_sqlsrv.so`


Библиотека mqseries:
-
https://www.php.net/manual/ru/mqseries.configure.php

Устанавливаем клиентские библиотеки MQ (я скачивал с офф. сайта IBM (https://www.ibm.com/support/pages/recommended-fixes-ibm-mq) файл `9.1.0.6-IBM-MQC-LinuxX64.tar.gz`)
Распаковываем архив в папку `/tmp/php_pecl/MQC` и переходим в неё:
```shell script
tar -xvf 9.1.0.6-IBM-MQC-LinuxX64.tar.gz
./mqlicense.sh
rpm -i MQSeriesSDK-9.1.0-6.x86_64.rpm MQSeriesRuntime-9.1.0-6.x86_64.rpm MQSeriesClient-9.1.0-6.x86_64.rpm
```
Если выходит ошибка:

error: %prein(MQSeriesSDK-9.1.0-6.x86_64) scriptlet failed, exit status 255
error: MQSeriesSDK-9.1.0-6.x86_64: install failed

ERROR:   Use of rpm to install MQSeriesClient on the Ubuntu distribution is no longer supported
         Installation terminated
         
то редактируем файл /usr/bin/uname и приводим к след. виду (с сохранением оригинала под другим именем):
```shell script
#!/bin/bash
echo "Linux centos8 4.18.0-305.21.1.el8.x86_64 #1 SMP Wed Mar 7 19:03:37 UTC 2021 x86_64 x86_64 x86_64 GNU/Linux"
```

пробуем снова установить MQSeries
```shell script
rpm -i MQSeriesSDK-9.1.0-6.x86_64.rpm MQSeriesRuntime-9.1.0-6.x86_64.rpm MQSeriesClient-9.1.0-6.x86_64.rpm
```
и возвращаем файл /usr/bin/uname к оригинальному виду.

Скачиваем php-расширение и переходим в папку с расширением:
```shell script
cd /tmp/php_pecl/
curl https://pecl.php.net/get/mqseries-0.15.0.tgz --output mqseries-0.15.0.tgz
tar -zxvf mqseries-0.15.0.tgz
cd mqseries-0.15.0/
```
Конфигурируем mqseries:
```shell script
phpize
./configure --with-libdir=lib64
make
make install
```
В папке `/usr/lib64/php/modules` должен появиться файл `mqseries.so`


## Подключение установленных расширений:

Все сгенерированные файлы `mqseries.so` `sqlsrv.so` `pdo_sqlsrv.so` `oci8.so` `pdo_informix.so` `smbclient.so`
необходимо "включить" в php. Для этого создаем в папке `/etc/php.d/` для каждого расширения конфигурацию в виде ini-файла.

Например:  
создаем файл `50-smbclient.ini` со след. содержимым `extension=smbclient.so`  
создаем файл `50-pdo_informix.ini` со след. содержимым `extension=pdo_informix.so`  
создаем файл `50-oci8.ini` со след. содержимым `extension=oci8.so`  
создаем файл `50-pdo_sqlsrv.ini` со след. содержимым `extension=pdo_sqlsrv.so`  
создаем файл `50-sqlsrv.ini` со след. содержимым `extension=sqlsrv.so`  
создаем файл `50-mqseries.ini` со след. содержимым `extension=mqseries.so`

Или скриптом:
```shell script
echo "extension=smbclient.so" > /etc/php.d/50-smbclient.ini
echo "extension=pdo_informix.so" > /etc/php.d/50-pdo_informix.ini
echo "extension=oci8.so" > /etc/php.d/50-oci8.ini
echo "extension=pdo_sqlsrv.so" > /etc/php.d/50-pdo_sqlsrv.ini
echo "extension=sqlsrv.so" > /etc/php.d/50-sqlsrv.ini
echo "extension=mqseries.so" > /etc/php.d/50-mqseries.ini
```
