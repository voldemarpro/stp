<?php
use yii\helpers\Html;

$this->title = 'Ошибка';
?>
<div class="site-error col-lg-12">

    <div class="alert alert-danger">
        <?php echo nl2br(Html::encode($message)) ?>
    </div>

</div>
