<?php

use common\enums\PublishedStatusEnum;
use common\helpers\Html;
use common\models\insurance\Cate;
use common\models\insurance\Company;
use yii\bootstrap\ActiveForm;
?>

<?php $form = ActiveForm::begin([
    'method' => 'get',
    'layout' => 'inline',
]); ?>

<?= $form->field($model, 'name')->label('产品名称：', ['class' => ''])
    ->textInput() ?>

<?= $form->field($model, 'company_id')->label('所属公司：', ['class' => ''])
    ->dropDownList(Company::getMap(), ['prompt' => '全部']) ?>

<?= $form->field($model, 'cate_id')->label('产品类别：', ['class' => ''])
    ->dropDownList(Cate::getMap(), ['prompt' => '全部']) ?>

<?= $form->field($model, 'published_status')->label('发布状态：', ['class' => ''])
    ->dropDownList(PublishedStatusEnum::getMap(), ['prompt' => '全部']) ?>

<div class="form-group" style="margin-left: 24px;">
    <?= Html::search() ?>
</div>

<?php ActiveForm::end(); ?>
