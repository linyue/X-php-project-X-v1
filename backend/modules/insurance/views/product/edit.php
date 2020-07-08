<?php

use common\helpers\Html;
use common\widgets\webuploader\Files;
use yii\widgets\ActiveForm;
use common\helpers\Url;
use common\enums\StatusEnum;
use common\enums\LangEnum;
use common\models\insurance\Company;
use common\models\insurance\Cate;
use common\models\insurance\Product;

$this->title = '基本信息';
$this->params['breadcrumbs'][] = ['label' => '保险产品管理', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $this->title];

?>

<div class="row">
    <div class="col-lg-12">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active"><a href="edit?id=<?=$model->id?>"> 基本信息</a></li>
                <?php if ($model->id): ?>
                    <li><a href="../product-plan/index?product_lang_id=<?=$model->id?>"> 保障计划</a></li>
                    <li><a href="../product-qa/index?product_lang_id=<?=$model->id?>"> 常见问题</a></li>
                <?php endif; ?>
            </ul>
            <div class="tab-content">
                <div class="active tab-pane">
                    <div class="box">
                        <?php $form = ActiveForm::begin([
                            'fieldConfig' => [
                                'template' => "<div class='col-sm-4 text-right'>{label}</div><div class='col-sm-8'>{input}\n{hint}\n{error}</div>",
                            ],
                        ]); ?>
                        <div class="box-body">
                            <?= Html::activeHiddenInput($model, 'id') ?>

                            <?php if ($model->id): ?>
                                <?= $form->field($model, 'lang')->textInput(['disabled'=>'disabled', 'value' => LangEnum::getValue($model->lang)]) ?>
                                <?= Html::activeHiddenInput($model, 'lang') ?>

                                <!-- 图片分语言，默认不同步 -->
                                <?= $form->field($model, 'sync')->checkbox() ?>
                            <?php else: ?>
                                <?= $form->field($model, 'lang')->dropDownList(LangEnum::getMap()) ?>

                                <?= $form->field($model, 'sync')->checkbox(['checked' => 'checked']) ?>
                            <?php endif; ?>

                            <?= $form->field($model, 'name')->textInput() ?>

                            <?= $form->field($model, 'company_id')->dropDownList(Company::getMap()) ?>

                            <?= $form->field($model, 'cate_id')->checkboxList(Cate::getMap()) ?>

                            <?= $form->field($model, 'sale_status')->radioList(Product::SALE_STATUS[LangEnum::CN]) ?>

                            <?= $form->field($model, 'abstract')->textInput() ?>

                            <?= $form->field($model, 'tags')->textInput()->hint('标签之间用英文逗号（ , ）分隔'); ?>

                            <?= $form->field($model, 'premium_currency')->checkboxList(Product::PREMIUM_CURRENCY[LangEnum::CN]) ?>

                            <?= $form->field($model, 'premium_payment')->checkboxList(Product::PREMIUM_PAYMENT[LangEnum::CN]) ?>

                            <?= $form->field($model, 'premium_model')->dropDownList(Product::PREMIUM_MODEL[LangEnum::CN]) ?>

                            <?= $form->field($model, 'buy_period')->textInput() ?>

                            <?= $form->field($model, 'guarantee_period')->textInput() ?>

                            <?= $form->field($model, 'renewal_period')->textInput() ?>

                            <?= $form->field($model, 'calm_period')->textInput() ?>

                            <?= $form->field($model, 'waiting_period')->textInput() ?>

                            <?= $form->field($model, 'min_insurance')->textInput() ?>

                            <?= $form->field($model, 'death_compensation')->textInput() ?>

                            <?= $form->field($model, 'thumb_img')->widget(Files::class, [
                                'config' => [
                                    'path' => 'oss/insurance/images/',
                                    'fileSingleSizeLimit' => 1024 * 1024 * 2,// 图片大小限制
                                ]
                            ])->hint('只支持 png/jpeg/jpg 格式,大小不超过为500KB'); ?>

                            <?= $form->field($model, 'banner_img')->widget(Files::class, [
                                'config' => [
                                    'path' => 'oss/insurance/images/',
                                    'fileSingleSizeLimit' => 1024 * 1024 * 2,// 图片大小限制
                                ]
                            ])->hint('只支持 png/jpeg/jpg 格式,大小不超过为1MB'); ?>

                            <?= $form->field($model, 'marketing_img')->widget(Files::class, [
                                'config' => [
                                    'path' => 'oss/insurance/images/',
                                    'fileSingleSizeLimit' => 1024 * 1024 * 2,// 图片大小限制
                                ]
                            ])->hint('只支持 png/jpeg/jpg 格式,大小不超过为1MB'); ?>

                            <?= $form->field($model, 'special_img')->widget(Files::class, [
                                'config' => [
                                    'path' => 'oss/insurance/images/',
                                    'fileSingleSizeLimit' => 1024 * 1024 * 2,// 图片大小限制
                                ]
                            ])->hint('只支持 png/jpeg/jpg 格式,大小不超过为1MB'); ?>

                            <?= $form->field($model, 'case_img')->widget(Files::class, [
                                'config' => [
                                    'path' => 'oss/insurance/images/',
                                    'fileSingleSizeLimit' => 1024 * 1024 * 2,// 图片大小限制
                                ]
                            ])->hint('只支持 png/jpeg/jpg 格式,大小不超过为1MB'); ?>
                        </div>

                        <div class="box-footer text-center">
                            <button class="btn btn-primary" type="submit" onclick="sendForm()">保存</button>
                            <span class="btn btn-white" onclick="history.go(-1)">返回</span>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>