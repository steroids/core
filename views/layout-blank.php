<?php

namespace app\views;

use Yii;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */


?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= \Yii::$app->language ?>">
<head>
    <meta charset="<?= \Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode(Yii::$app->name) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<?= $content ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
