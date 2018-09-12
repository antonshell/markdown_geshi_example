# Настройка базовой авторизации Elastic Search

<div class="image-wrapper">
    <img src="img/elastic-search-nginx-proxy/1.png">
</div>

Иногда в процессе работы с elastic search может возникнуть 
необходимость ограничить доступ к нему. 
По умолчанию никакой защиты не настроено. 
Соответственно, кто угодно может смотреть ваши данные в elastic.
Впринципе это не так и страшно, т.к. elastic все-таки поисковый движок.
И данные для поиска могут быть доступны и не являются приватными.

Но в то же время, elastic доступен всем так же и на запись.
Таким образом злоумышеник может удалить все индексы или подменить данные в них.
Поэтому оставлять elastic открытым нельзя. 
Рекомендуется настроить firewall и ограничить доступ для определенных ip адресов или подсетей.
Также можно настроить базовую авторизацию по логину и паролю.

Elastic в базовой версии такой функционал не поддерживает.
Настройки безопасности реализованы в рамках платного дополнения x-pack.

Мы же настроим авторизацию с помощью web-сервера nginx.

<!--more-->

### Меняем порт elastic search

По-умолчанию elastic search работает на порту 9200. 
Перенесем его на другой порт, например 9100

Если elastic работает в docker, то нужно просто отредактировать конфиг docker-compose.yml

``` bash
nano docker-compose.yml
```

Меняем номер порта с ```- 9200:9200``` на ```- 9100:9200```
В данном случае elastic работает в docker контейнере на порту 9200.
Но пробрасывается на локальный порт 9100. И фактически мы можем обратиться к нему только по 9100 порту.

Дальше пересобираем контейнер:

``` bash
docker-compose up --build
```

Проверяем - открывем в браузере ```192.168.1.54:9100```
Должен вернуться json с базовой информацией о elastic.

``` json
{
    "name": "pin_sMK",
    "cluster_name": "elasticsearch",
    "cluster_uuid": "y2PIJuo7TySC4wIaBNjY5g",
    "version": {
        "number": "5.6.5",
        "build_hash": "6a37571",
        "build_date": "2017-12-04T07:50:10.466Z",
        "build_snapshot": false,
        "lucene_version": "6.6.1"
    },
    "tagline": "You Know, for Search"
}
```

### Настройка nginx

Если nginx еще не установлен, то устанавливаем

``` bash
apt-get update
apt-cache policy nginx
apt-get install nginx
service nginx start
```

Дальше нужно создать виртуальный хост

``` bash
cd /etc/nginx/conf.d
nano elastic_proxy.conf
```

``` bash
upstream elasticsearch {
    server 127.0.0.1:9100;
}

server {
    listen       9200;

    auth_basic "Restricted Content";
    auth_basic_user_file /etc/nginx/.htpasswd;

    location / {
      proxy_pass http://elasticsearch;
      proxy_redirect off;
    }
}
```

В качестве upstream указываем адрес elastic. Nginx будет работать на порту 9200.
Проксирование будет прозрачным для пользователя. Также указываем путь к файлу с логинами/паролями.

### Добавление пользователей

Дальше нужно создать файл с логинами и паролями. Пароли будут храниться в зашифрованом виде.
Теперь создим пользователя ```user``` с паролем ```passwd```. Для этого выполним команду:

``` bash
printf "user:$(openssl passwd -crypt passwd)" > /etc/nginx/.htpasswd
```

Перезапускаем nginx

``` bash
service nginx restart
```

### Настройка firewall

Осталось только настроить firewall и закрыть порт 9100, 
на котором работает elastic search для всех, кроме localhost.
Также имеет смысл закрыть управляющий порт 9300. Или повторить те же действия и закрыть паролем.
Настройка ufw под ubuntu описана 
<a href="https://www.digitalocean.com/community/tutorials/how-to-set-up-a-firewall-with-ufw-on-ubuntu-14-04">тут</a>

На этом пока все. Спасибо за внимание!