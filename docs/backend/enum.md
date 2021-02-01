# Enum

Перечисления - это специальный тип данных, хранящий множество значений. Поскольку такого типа данных в PHP нет, то
на замену им обычно создают классы с константами и статичными методами, в которых хранятся множества значений.

В Steroids создан специальный базовый класс `\steroids\core\base\Enum`, от которого необходимо наследоваться для
создания перечислений. Создавать их можно, в том числе через интерфейс `gii`.

Пример такого Enum:

```php
abstract class UserRole extends \steroids\core\base\Enum
{
    const ADMIN = 'admin';
    const USER = 'user';

    public static function getLabels()
    {
        return [
            self::ADMIN => Yii::t('app', 'Администратор'),
            self::USER => Yii::t('app', 'Пользователь')
        ];
    }
}
```

Обязательные данные Enum - это набор констант, которые необходимо использовать в проекте и метод `getLabels()`, в котором
имеется массив с указанием заголовков для каждого из ключей. Эти заголовки могут автоматически подтягиваться в пользовательскую
часть, чтобы более красиво отобразить `Администратор`, а не писать системное имя `admin`.

Помимо этого в базовом классе `\steroids\core\base\Enum` метод `getLabels` используется для получений всех констант
перечисления и в методе `getLabel($id)` для получения человекопонятного заголовка.

Помните, что перечисления нужно создавать всегда в случаях, когда у вас появляется несколько связанных констант, которые
могут храниться в одном поле. Это может быть, к примеру, роль пользователя, статус заявки, валюты, типы,
системные имена аккаунтов и так далее.

Архитектура и смысл `Enum` в Steroids не ограничивает их методом `getLabels()`, вы можете создавать другие методы под
свои задачи. Например, если мы хотим для каждого статуса иметь свой цвет, то можно сделать статичный метод `getColors()`:

```php
abstract class OrderStatusMeta extends \steroids\core\base\Enum
{
    const CREATED = 'created';
    const PROCESS = 'process';
    const SUCCESS = 'success';
    const FAILURE = 'failure';

    public static function getLabels()
    {
        return [
            self::CREATED => Yii::t('app', 'Создан'),
            self::PROCESS => Yii::t('app', 'В процессе'),
            self::SUCCESS => Yii::t('app', 'Успешно выполнен'),
            self::FAILURE => Yii::t('app', 'Произошла ошибка')
        ];
    }

    public static function getColors()
    {
        return [
            self::CREATED => 'gray',
            self::PROCESS => 'orange',
            self::SUCCESS => 'green',
            self::FAILURE => 'red'
        ];
    }
    
    public static function getColor($id)
    {
        return \yii\helpers\ArrayHelper::getValue(static::getColors(), $id);
    }
}
```