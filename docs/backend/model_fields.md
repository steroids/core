# Публичные поля и схемы

Во всех моделях есть методы `fields()` и `fieldsSchema()`, которые описывают какие данные будут возвращаться при
вызове метода `toFrontend()`.


## Метод `fields()`

Метод сохраняет поведение базового метода yii2 `\yii\base\Model::fields()`. Его формат лучше будет показать на примере:

```php
    public function fields() {
        return [
            'id', // Вернет атрибут id
            'label' => 'title', // Вернет значение атрибута title, но ключ будет label
            'amount' => fn ($model) => round($model->amount / 100), // Кастомизация значения через анонимную функцию
            'items' => [ // Подтянет данные из связи items, выбрав оттуда поля id, name и связь category
                'id',
                'name',
                'category' => [
                    'name',
                    'title',
                ],
            ],
        ];
    }
```

Если для связи не указывать набор получаемых атрибутов, то возьмутся дефолтные `fields()` из связанной модели.

Для `SearchModel` метод `fields()` будет использован для каждой модели списка.


### Формат `*`

Если в модели указаны дефолтные `fields()`, а в схеме или `SearchModel` нужно ее расширить (добавить дополнительные поля),
то необязательно перечислять все поля модели, можно воспользоваться звездочкой - `*`.

```php
class User extends UserMeta {
    public function fields() {
        return [
            'id',
            'name',
        ];
    }
}

class UserSearch extends UserSearchMeta {
    public function fields() {
        return [
            '*', // Атрибуты id и name будут взяты из User::fields()
            'avatarUrl', // Добавляем дополнительный атрибут
        ];
    }
}
```


### Формат `'*' => 'relation.*'`

Иногда мы делаем запрос с join и хотим выдавать только связанные данные. Например, мы ходим выбрать пользователей из статистики:

```php
class UserSearch extends UserSearchMeta {
    public function fields() {
        return [
            'id' => 'user.id',
            'name' => 'user.name',
            'avatarUrl' => 'user.avatarUrl',
        ];
    }
    public function prepare($query) {
        $query->joinWith('user');
    }
}
```

Чтобы не дублировать перечисление атрибутов, можно воспользоваться специальным форматом. Код ниже сделает тоже самое:

```php
class UserSearch extends UserSearchMeta {
    public function fields() {
        return [
            '*' => 'user.*',
        ];
    }
    public function prepare($query) {
        $query->joinWith('user');
    }
}
```


## Метод `fieldsSchema()`

Метод возвращает название класса [схемы](schema.md), описывающей выходные данные. При создании экземпляра схемы, ей будет передана
текущая модель.

```php
    public function fieldsSchema() {
        return UserSchema::class;
    }
 ```

Подробнее о схемах можно узнать в разделе [Схемы](schema.md)


## Методы `toFrontend()` и `anyToFrontend()`

Трейт `\steroids\core\traits\MetaTrait`, добавленный в каждую модель, реализует так же метод
`toFrontend($fields = null, $user = null)`, который в свою очередь использует и статичный метод
`anyToFrontend($model, $fields = null)`.

Задача этих методов аналогична обычному методу `\yii\helpers\BaseArrayHelper::toArray()`, который приводит данные к
массиву. Дополнительно метод поддерживает схемы, о которых говорилось выше, и дополнительные форматы в `fields()`.
