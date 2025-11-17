## API-сервис обработки форм обратной связи

Данный проект реализует универсальный REST API-сервис на базе **Laravel 12**, принимающий данные из различных форм обратной связи, определяющий сценарий обработки по конфигурационным правилам и отправляющий письмо соответствующим получателям.

Сервис поддерживает расширяемую конфигурацию правил, асинхронную отправку писем через очередь и аккуратную валидацию входящих данных.

---

## 1. Возможности сервиса

* Универсальная точка входа:
  **POST /api/form**
* Валидация входящих данных.
* Определение получателей по конфигурационным правилам:

    * основной получатель (primary rule)
    * дополнительные получатели (extra rules)
    * fallback при отсутствии совпадений
* Поддержка `address`-override:
  если поле `address` содержит корректный email, письмо направляется только туда.
* Поддержка правил вида:

    * `equals: string`
    * `equals: string[]` (множественные значения)
* Очистка payload от лишних полей.
* Асинхронная отправка писем (Laravel Queue).
* Подробные unit- и feature-тесты.
* Гибкость: все правила описываются в конфиге, код менять не требуется.

---

## 2. Системные требования

* Docker
* Docker Compose
* Git

Локальная установка PHP/Composer не требуется.

---

## 3. Установка и запуск

### 3.1. Клонирование репозитория

```bash
git clone https://github.com/IslamBzh/kodeks-test.git
cd kodeks-test
```

### 3.2. Запуск контейнеров

```bash
docker compose up -d
```

### 3.3. Установка зависимостей

```bash
docker compose exec app composer install
```

### 3.4. Генерация ключа

```bash
docker compose exec app php artisan key:generate
```

### 3.5. Миграции

```bash
docker compose exec app php artisan migrate
```

Очередь запускается автоматически отдельным контейнером.

---

## 4. Конфигурация правил маршрутизации

**Правила описаны в файле:**
[config/form_routing.php](config/form_routing.php)

### Структура

```php
return [
    'primary' => [
        [   // profession = "студент" → student@example.com
            'field'     => 'profession',
            'equals'    => 'студент',
            'recipient' => 'student@example.com',
        ],
        [   // region in ["Санкт-Петербург", "Москва"] → center@example.com
            'field'     => 'region',
            'equals'    => ['Санкт-Петербург', 'Москва'],
            'recipient' => 'center@example.com',
        ],
        [   // product = "promo" → promo@example.com
            'field'     => 'product',
            'equals'    => 'promo',
            'recipient' => 'promo@example.com',
        ],
    ],

    'extra' => [
        [   // product = "special" → добавить дополнительного получателя special@example.com
            'field'     => 'product',
            'equals'    => 'special',
            'recipient' => 'special@example.com',
        ],
    ],

    'default' => 'all@example.com',
];
```

### Логика работы правил

1. Если передано `address` и это валидный email → он используется как primary, остальные правила игнорируются.
2. Primary-правила обрабатываются **по порядку**; первое совпавшее определяет основного получателя.
3. Extra-правила могут добавить несколько дополнительных получателей.
4. Если primary не найден — используется `default`.

### Поддерживаемые типы условий

* `equals: string`
  значение поля должно строго соответствовать строке

* `equals: string[]`
  значение поля должно входить в список допустимых

---

## 5. Использование API

### Endpoint

```
POST /api/form
Content-Type: application/json
```

### Пример запроса

```json
{
  "name": "Иван",
  "email": "ivan@example.com",
  "phone": "+7 900 000-00-00",
  "profession": "студент",
  "region": "Москва",
  "product": "promo",
  "address": "override@example.com"
}
```

### Ответ

```
HTTP/1.1 202 Accepted
(no content)
```

---

## 6. Формирование письма

В payload письма включаются только типовые поля:

* name
* email
* phone
* profession
* region
* product

Поле `address` и любые дополнительные поля не включаются в тело письма.

---

## 7. Тестирование

### Запуск тестов

```bash
docker compose exec app php artisan test
```

### Покрытие

Покрыты:

* Primary- правила
* Extra- правила
* Fallback
* Address-override
* Payload-фильтрация
* Очередь отправки писем
* Endpoint `/api/form` (feature-тесты)

---

## 8. Структура проекта

```
app/
  Http/
    Controllers/FormSubmissionController.php
    Requests/SubmitFormRequest.php
  Services/FormRoutingService.php
  Jobs/SendFormEmailJob.php
  Mail/FormSubmissionMail.php

config/
  form_routing.php

routes/
  api.php

tests/
  Unit/Services/FormRoutingServiceTest.php
  Feature/Api/FormSubmissionTest.php
```

---

## 9. Возможные улучшения

* Добавить Swagger/Scribe для автогенерации API-документации.
* Логирование всех заявок в БД.
* Расширение подсистемы правил (операторы contains, startsWith, regexp).
* Отдельные rule-providers по типу формы.
* Расширение почтовых каналов (Mail Manager).

---
