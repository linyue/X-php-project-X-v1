<?php

use common\components\uploaddrive\OSS;
use common\helpers\Html;
use common\enums\LangEnum;
use common\enums\PublishedStatusEnum;

$this->title = '常见问题';
$this->params['breadcrumbs'][] = ['label' => '保险产品管理', 'url' => ['product/index']];
$this->params['breadcrumbs'][] = ['label' => $this->title];
?>

<div class="row">
    <div class="col-xs-12">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li><a href="../product/edit?id=<?=$product_lang_id?>"> 基本信息</a></li>
                <li><a href="../product-plan/index?product_lang_id=<?=$product_lang_id?>"> 保障计划</a></li>
                <li class="active"><a href="index?product_lang_id=<?=$product_lang_id?>"> 常见问题</a></li>
            </ul>
            <div class="tab-content">
                <div class="active tab-pane">
                    <div class="box">
                        <div class="box-header">
                            <?= Html::create(['edit?product_lang_id='.$product_lang_id], '新建问题'); ?>
                        </div>

                        <div class="box-body table-responsive">
                            <table class="table table-bordered table-hover order-column">
                                <thead>
                                    <tr>
                                        <th>序号</th>
                                        <th>语言</th>
                                        <th>问题</th>
                                        <th>解答</th>
                                        <th class="col-md-1">排序</th>
                                        <th class="action-column" colspan="2">操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($models = $dataProvider->getModels()): ?>
                                        <?php foreach($models as $k => $model):?>
                                            <?php $isFirst = true;$langCount = count($model->langs);?>
                                            <?php foreach($model->langs as $langItem):?>
                                                <tr data-pid="<?=$model->id?>" data-id="<?=$langItem->id?>">
                                                    <?php if ($isFirst): ?>
                                                        <td rowspan="<?=$langCount?>" style="text-align: center;"><?=$dataProvider->getPagination()->getPage()*$dataProvider->getPagination()->getLimit() + $k + 1?></td>
                                                    <?php endif; ?>
                                                    <td style="text-align: center;"><?=LangEnum::getValue($langItem->lang)?></td>
                                                    <td><?=$langItem->question?></td>
                                                    <td><?=$langItem->answer?></td>
                                                    <?php if ($isFirst): ?>
                                                        <td rowspan="<?=$langCount?>">
                                                            <?=Html::textInput('sort', $model->sort, ['class' => 'form-control rf-sort-input']);?>
                                                        </td>
                                                    <?php endif; ?>
                                                    <td style="text-align: center;">
                                                        <a class="blue" href="edit?product_lang_id=<?=$product_lang_id?>&id=<?=$langItem->id?>">编辑</a>
                                                    </td>
                                                    <?php if ($isFirst): ?>
                                                        <td rowspan="<?=$langCount?>" style="text-align: center;">
                                                            <a class="red" href="destroy?product_lang_id=<?=$product_lang_id?>&id=<?=$model->id?>" onclick="rfDelete(this);return false;">删除</a>
                                                        </td>
                                                    <?php endif; ?>
                                                </tr>
                                                <?php $isFirst = false;?>
                                            <?php endforeach;?>
                                        <?php endforeach;?>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <?= \yii\widgets\LinkPager::widget([
                                'pagination' => $dataProvider->pagination,
                                'maxButtonCount' => 5,
                                'disableCurrentPageButton' => true,
                            ]);?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>