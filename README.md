<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii 2 Basic Project Template</h1>
    <br>
</p>

Yii 2 Basic Project Template is a skeleton [Yii 2](http://www.yiiframework.com/) application best for
rapidly creating small projects.

The template contains the basic features including user login/logout and a contact page.
It includes all commonly used configurations that would allow you to focus on adding new
features to your application.


# Развертывание проекта с сервисом управления пользователями

## Конфигурация

### Database

 `config/db.php` в конфиги базы данных записываем свои доступы

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
    'username' => 'root',
    'password' => '1234',
    'charset' => 'utf8',
];
```

### Для доступа к сервису нужно настроить папку web как корневую для сайта
~~~
site_directory_path/web/
~~~

### Либо можно развернуть на сервере под управлением Docker
~~~
Нужно чтобы были развернуты контейнеры с reverse-proxy, mysql, memcached
ну или настроить под свою конфигурацию

и затем запустить команду docker-compose up -d
~~~

### Клонируем проект на свой сервер
~~~
git clone https://github.com/Ruslan-Androsenko/user-management-service.git site_directory_path
~~~

### Устанавниваем зависимости проекта
~~~
composer install
~~~

### Запускаем миграции
~~~
cd site_directory_path
php yii migrate
~~~


## Список доступных эндпоинтов

### Создать новую учетную запись для пользователя
~~~
POST {{api-host}}/v1/user/
передавать нужно такие поля: username, email, password.
~~~

### Для всех запросов указанных ниже необходим токен доступа
#### Получить их можно следующим образом
~~~
GET {{api-host}}/v1/user/login/?username={username}&password={password}
Здесь мы передаем логин и пароль от нужной учетной записи

прийдет такой ответ
{
    "id": 5,
    "username": "test_user05",
    "token": "78VKc9C_xYv63G1iuxsjyR45YZ0be20I"
}
~~~

## Для всех запросов указанных ниже необходим токен доступа, как его получить смотрите выше

### Просмотреть конкретную запись по id
~~~
GET {{api-host}}/v1/user/{id}/
~~~

### Удалить конкретную запись по id
~~~
DELETE {{api-host}}/v1/user/{id}/
~~~

### Изменить данные для конкретной записи по id
~~~
PUT {{api-host}}/v1/user/{id}/
можно изменить такие поля: username, email, password.
~~~
