<?php

namespace backend\modules\insurance\controllers;

use backend\modules\insurance\forms\ProductPlanRightForm;
use common\helpers\TranslateHelper;
use common\models\insurance\ProductLang;
use common\models\insurance\ProductPlan;
use common\models\insurance\ProductPlanRight;
use Yii;
use common\enums\StatusEnum;
use common\models\base\SearchModel;
use common\traits\Curd;
use backend\controllers\BaseController;

/**
 * Class ProductPlanRightController
 * @package backend\modules\insurance\controllers
 */
class ProductPlanRightController extends BaseController
{
    use Curd;

    /**
     * @var ProductPlanRight
     */
    public $modelClass = ProductPlanRight::class;

    /**
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionIndex()
    {
        $product_lang_id = Yii::$app->request->get('product_lang_id', null);
        $product_plan_id = Yii::$app->request->get('product_plan_id', null);

        if(!$product_lang_id || !$product_lang = ProductLang::findOne(['id' => $product_lang_id])){
            throw new \Exception('产品不存在');
        }

        if(!$product_plan_id || !$product_plan = ProductPlan::findOne(['id' => $product_plan_id])){
            throw new \Exception('保障计划不存在');
        }

        $searchModel = new SearchModel([
            'model' => $this->modelClass,
            'scenario' => 'default',
            'partialMatchAttributes' => ['name'], // 模糊查询
            'defaultOrder' => [
                'sort' => SORT_ASC,
                'created_at' => SORT_DESC,
            ],
            'pageSize' => $this->pageSize,
        ]);

        $dataProvider = $searchModel
            ->search(Yii::$app->request->queryParams);
        $dataProvider->query
            ->andWhere(['product_plan_id' => $product_plan_id])
            ->andWhere(['>=', 'status', StatusEnum::DISABLED]);

        return $this->render($this->action->id, [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'product_lang_id' => $product_lang_id,
            'product_plan_id' => $product_plan_id,
        ]);
    }


    /**
     * 编辑/创建
     *
     * @return mixed
     */
    public function actionEdit(){
        $id = Yii::$app->request->get('id', null);
        $product_lang_id = Yii::$app->request->get('product_lang_id', null);
        $product_plan_id = Yii::$app->request->get('product_plan_id', null);

        if(!$product_lang_id || !$product_lang = ProductLang::findOne(['id' => $product_lang_id])){
            throw new \Exception('产品不存在');
        }
        if(!$product_plan_id || !$product_plan = ProductPlan::findOne(['id' => $product_plan_id])){
            throw new \Exception('保障计划不存在');
        }

        $model = new ProductPlanRightForm();
        $model->product_plan_id = $product_plan->id;

        //保存
        if($model->load(Yii::$app->request->post()) && $model->save()){
            return $this->redirect(['index?product_lang_id=' . $product_lang_id . '&product_plan_id=' . $product_plan_id]);
        }

        //编辑
        if($id){
            $model->loadData($id);
        }

        return $this->render($this->action->id, [
            'model' => $model,
            'product_lang_id' => $product_lang_id,
            'product_plan_id' => $product_plan_id,
        ]);
    }

    /**
     * 伪删除
     *
     * @param $id
     * @return mixed
     */
    public function actionDestroy($id, $product_lang_id, $product_plan_id)
    {
        if (!($model = $this->modelClass::findOne($id))) {
            return $this->message("找不到数据", $this->redirect(['index?product_lang_id=' . $product_lang_id . '&product_plan_id=' . $product_plan_id]), 'error');
        }

        $model->status = StatusEnum::DELETE;
        if ($model->save()) {
            return $this->message("删除成功", $this->redirect(['index?product_lang_id=' . $product_lang_id . '&product_plan_id=' . $product_plan_id]));
        }

        return $this->message("删除失败", $this->redirect(['index?product_lang_id=' . $product_lang_id . '&product_plan_id=' . $product_plan_id]), 'error');
    }
}