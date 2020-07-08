<?php

use common\enums\LangEnum;
use common\helpers\Html;
use common\models\insurance\ProductPlan;
use common\widgets\webuploader\Files;
use yii\widgets\ActiveForm;
use common\helpers\Url;
use common\enums\StatusEnum;

$this->title = '编辑';
$this->params['breadcrumbs'][] = ['label' => '保险产品管理', 'url' => ['product/index']];
$this->params['breadcrumbs'][] = ['label' => '保障计划', 'url' => ['index?product_lang_id=' . $product_lang_id]];
$this->params['breadcrumbs'][] = ['label' => $this->title];

?>

<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">基本信息</h3>
            </div>
            <?php $form = ActiveForm::begin([
                'fieldConfig' => [
                    'template' => "<div class='col-sm-4 text-right'>{label}</div><div class='col-sm-8'>{input}\n{hint}\n{error}</div>",
                ],
            ]); ?>
            <div class="box-body">
                <?= Html::activeHiddenInput($model, 'id') ?>
                <?= Html::activeHiddenInput($model, 'product_id') ?>

                <?php if ($model->id): ?>
                    <?= $form->field($model, 'lang')->textInput(['disabled'=>'disabled', 'value' => LangEnum::getValue($model->lang)]) ?>
                    <?= Html::activeHiddenInput($model, 'lang') ?>
                <?php else: ?>
                    <?= $form->field($model, 'lang')->dropDownList(LangEnum::getMap()) ?>
                <?php endif; ?>

                <?= $form->field($model, 'sync')->checkbox(['checked' => 'checked']) ?>

                <?= $form->field($model, 'name')->textInput() ?>

                <?= $form->field($model, 'type')->dropDownList(ProductPlan::TYPE[LangEnum::CN]) ?>
            </div>
            <div class="box-footer text-center">
                <button class="btn btn-primary" type="submit" onclick="sendForm()">保存</button>
                <span class="btn btn-white" onclick="history.go(-1)">返回</span>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
