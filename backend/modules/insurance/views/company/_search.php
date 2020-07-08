<?php
    use common\helpers\Html;
    use yii\bootstrap\ActiveForm;
?>

<?php $form = ActiveForm::begin([
    'method' => 'get',
    'layout' => 'inline',
]); ?>

<?= $form->field($model, 'published_status')->label('发布状态：', ['class' => ''])
    ->dropDownList(\common\enums\PublishedStatusEnum::getMap(), ['prompt' => '全部']) ?>

<div class="form-group" style="margin-left: 24px;">
    <?= Html::search() ?>
</div>

<?php ActiveForm::end(); ?>
