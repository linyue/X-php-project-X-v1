<?php

use common\components\uploaddrive\OSS;
use common\helpers\Html;
use common\enums\LangEnum;
use common\enums\PublishedStatusEnum;
use common\models\insurance\Cate;
use yii\widgets\LinkPager;

$this->title = '保险产品管理';
$this->params['breadcrumbs'][] = ['label' => $this->title];
?>

<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header">
                <?= Html::create(['edit']); ?>
                <?= Html::a('同步产品', ['sync'], ['class' => 'btn btn-white', 'target'=>'_blank'])?>
            </div>

            <div class="box-body table-responsive">
                <div class="table-tools">
                    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
                </div>

                <table class="table table-bordered table-hover order-column" style="margin-bottom: 12px;">
                    <thead>
                        <tr>
                            <th>序号</th>
                            <th>缩略图</th>
                            <th>语言</th>
                            <th>产品名称</th>
                            <th>所属公司</th>
                            <th>产品类别</th>
                            <th class="col-md-1">保障计划</th>
                            <th class="col-md-1">常见问题</th>
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
                                            <td rowspan="<?=$langCount?>" style="text-align: center;">
                                                <?=$dataProvider->getPagination()->totalCount - $dataProvider->getPagination()->getPage()*$dataProvider->getPagination()->getLimit() - $k?>
                                            </td>
                                            <td rowspan="<?=$langCount?>" style="text-align: center;" style="text-align: center;">
                                                <img style="height: 64px;" src="<?=OSS::fullPath($langItem->thumb_img)?>">
                                            </td>
                                        <?php endif; ?>
                                        <td style="text-align: center;"><?=LangEnum::getValue($langItem->lang)?></td>
                                        <td><?=$langItem->name?></td>
                                        <?php if ($isFirst): ?>
                                            <td rowspan="<?=$langCount?>"><?=$model->company->findByLang(LangEnum::CN)->name?></td>
                                            <td rowspan="<?=$langCount?>"><?=join('、', Cate::getMap(explode(',', $model->cate_id)))?></td>
                                            <td rowspan="<?=$langCount?>" style="text-align: center;">
                                                <a class="blue" href="../product-plan/index?product_lang_id=<?=$langItem->id?>"><?=$model->planCount?></a>
                                            </td>
                                            <td rowspan="<?=$langCount?>" style="text-align: center;">
                                                <a class="blue" href="../product-qa/index?product_lang_id=<?=$langItem->id?>"><?=$model->qaCount?></a>
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