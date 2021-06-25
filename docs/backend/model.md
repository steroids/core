# Модели

Как и в Yii2 фреймворке, базовые модели существуют нескольких видов:

- `steroids\base\Model` [Модель ActiveRecord](model_ar.md), наследуемая от `yii\db\ActiveRecord`
- `steroids\base\FormModel` [Модель формы](model_form.md), наследуемая от `yii\base\Model`
- `steroids\base\SearchModel` [Модель получения списков](model_search.md) с фильтрами, пагинацией и сортировкой
  (под капотом `\yii\data\ActiveDataProvider`), наследуемая от ранее упомянутой `steroids\base\FormModel`

Каждая из этих моделей наследуются сперва от [мета-модели](model_meta.md), а уже та в свою очередь от базового класса.
