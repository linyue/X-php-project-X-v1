<?php

use yii\widgets\LinkPager;
use common\components\uploaddrive\OSS;
use common\helpers\Html;
use common\enums\LangEnum;
use common\enums\PublishedStatusEnum;


$this->title = '保险公司管理';
$this->params['breadcrumbs'][] = ['label' => $this->title];
?>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header">
                <?= Html::create(['edit']); ?>
            </div>

            <div class="box-body table-responsive">
                <table class="table table-bordered table-hover order-column">
                    <thead>
                        <tr>
                            <th>序号</th>
                            <th>类别图标</th>
                            <th>语言</th>
                            <th>类别名称</th>
                            <th class="col-md-1">产品数量</th>
                            <th class="col-md-1">排序</th>
                            <th class="col-md-1">发布状态</th>
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
                                            <td rowspan="<?=$langCount?>" style="text-align: center;"><?=$dataProvider->getPagination()->totalCount -$dataProvider->getPagination()->getPage()*$dataProvider->getPagination()->getLimit()- $k?></td>
                                            <td rowspan="<?=$langCount?>" style="text-align: center;"><img style="height: 64px;" src="<?=OSS::fullPath($langItem->icon)?>"></td>
                                        <?php endif; ?>
                                        <td style="text-align: center;"><?=LangEnum::getValue($langItem->lang)?></td>
                                        <td><?=$langItem->name?></td>
                                        <?php if ($isFirst): ?>
                                            <td rowspan="<?=$langCount?>" style="text-align: center;">
                                                <a class="blue" href="../product/index?ProductSearch[cate_id]=<?=$model->id?>"><?=$model->productCount?></a>
                                            </td>
                                            <td rowspan="<?=$langCount?>">
                                                <?=Html::textInput('sort', $model->sort, ['class' => 'form-control rf-sort-input']);?>
                                            </td>
                                            <td rowspan="<?=$langCount?>">
                                                <?=Html::dropDownList('published_status', $model->published_status, PublishedStatusEnum::getMap(), ['class' => 'form-control rf-publish-select'])?>
                                            </td>
                                        <?php endif; ?>
                                        <td style="text-align: center;">
                                            <a class="blue" href="edit?id=<?=$langItem->id?>">编辑</a>
                                        </td>
                                        <?php if ($isFirst): ?>
                                            <td rowspan="<?=$langCount?>" style="text-align: center;">
                                                <a class="red" href="destroy?id=<?=$model->id?>" onclick="rfDelete(this);return false;">删除</a>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php $isFirst = false;?>
                                <?php endforeach;?>
                            <?php endforeach;?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?= LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'maxButtonCount' => 5,
                    'disableCurrentPageButton' => true,
                ]);?>
            </div>
        </div>
    </div>
</div>