# Yii Steroids (Бекенд часть)

Бекенд часть библиотеки представляет из себя набор базовых классов, валидаторов, поведений, модулей и других компонентов,
расширяющих базовый функционал PHP фреймворка [Yii](https://www.yiiframework.com/) версии 2.

Каждый новый функционал или компонент создается с максимальным приближением к идеологии Yii 2. Тем не менее, сейчас
набор компонентов уже настолько велик, что без отдельной документации для Steroids не обойтись.

## Обзор

- [Структура приложения](application_structure.md)
- [Инициализация приложения](bootstrap.md)
- [Внутренние типы (appType)](types.md)
- [Модели](model.md)
  - [Модель ActiveRecord](model_ar.md)
  - [Модель Form](model_form.md)
  - [Модель Search](model_search.md)
  - [Мета-модели (*Meta)](model_meta.md)
  - [Сохранение вложенной структуры данных](model_nested.md)
  - [Публичные поля и схемы](model_fields.md)
- [Перечисления (Enum)](enum.md)
- [Схемы (Scheme)](schema.md)
- [Контроллеры, экшены и разбор URL](controllers.md)
- [CRUD контроллер](crud.md)
- Обработчики запроса (Middleware)
  - [AccessMiddleware: Права доступа](middleware_access.md) !!!
  - [AjaxResponseMiddleware: JSON преобразование](middleware_response.md) !!!
- Компоненты
  - [AuthManager: Контроль прав доступа](component_authmanager.md) !!!
  - [Cors: Настройка CORS запросов](component_cors.md) !!!
- Модули
  - [Auth: Авторизация, 2FA, Captcha](module_auth.md)
  - [Billing: Биллинг](module_billing.md) !
  - [Cron: Менеджер крон задач](module_cron.md) !
  - [File: Модуль загрузки файлов](module_file.md) !
  - [Gii: Генератор кода](module_gii.md)
  - [Notifier: Отправка уведомлений](module_notifier.md) !
  - [Payment: Оплата и вывод через платежные системы](module_payment.md)
  - [Swagger: Автогенерация документации](module_swagger.md)
