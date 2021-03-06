# CRUD контроллер

CRUD — акроним, обозначающий четыре базовые функции, используемые при работе с базами данных: создание (create),
чтение (read), модификация (update), удаление (delete). Как правило, такие функции используются для сущностей в личных
кабинетах или панелях администратора.

В Steroids создан базовый контроллер `\steroids\core\base\CrudApiController`, упрощающий создание экшенов для таких
функций. Пример использования такого контроллера:

```php
class TodosAdminController extends CrudApiController
{
    public static $modelClass = Todo::class;
    public static $searchModelClass = TodoAdminSearch::class;

    public static function apiMap()
    {
        return [
            'admin.content.todos' => parent::apiMapCrud('/api/v1/admin/content/todos'),
        ];
    }

    public function fields()
    {
        return [
            'id',
            'title',
            'date',
        ];
    }
}
```

Обязательными для переопределения являются:
- статичное свойство класса `$modelClass`, в котором нужно указать название AR модели;
- Статичный метод `apiMap()` для объявления методов API, который может использовать дополнительный метод `apiMapCurd()`

Этот минимальный набор данных позвонил создать CRUD экшены над этой сущностью. Для кастомизации этих экшенов можно
переопределять другие методы, которые опишем ниже:
- `static modelClass()` Метод, который возвращает название класса AR модели. Используется взамен статичного свойства
  `$modelClass`, когда в таком виде его использовать неудобно;
- `static $searchModelClass` или `static searchModelClass()` Аналогично указанию AR модели можно указать `SearchModel`,
  которая будет использоваться для получения списка по сущности;
- `static $viewSchema` или `static viewSchema()` И по аналогии можно указать [схему](schema.md), которая будет применяться
  для вывода сущности в методе `view` и при сохранении модели в методах `create` и `update`;
- `static controls()` В методе определяется список методов, которые доступны над сущностью, по-умолчанию это:
  - `index` Получение списка;
  - `create` Создание;
  - `update` Обновление;
  - `update-batch` Групповое обновление;
  - `view` Получение по PK;
  - `delete` удаление;
- `fields()` В методе указываются поля для вывода при получении списка, по аналогии аналогичного метода в `SearchModel`.
  Используется если нет отдельной SearchModel для по этой сущности;
- `detailFields()` Аналогично предыдущему, то применяется при отсутствии схемы для методов `view`, `create` и `update`;

Для переопределения остальных методов лучше посмотреть непосредственно в код класса `\steroids\core\base\CrudApiController`.