<?php

namespace backend\modules\insurance\controllers;

use backend\modules\insurance\forms\ProductQaForm;
use common\helpers\TranslateHelper;
use common\models\insurance\ProductLang;
use common\models\insurance\ProductQa;
use Yii;
use common\enums\StatusEnum;
use common\models\base\SearchModel;
use common\traits\Curd;
use backend\controllers\BaseController;

/**
 * Class ProductQaController
 * @package backend\modules\insurance\controllers
 */
class ProductQaController extends BaseController
{
    use Curd;

    /**
     * @var ProductQa
     */
    public $modelClass = ProductQa::class;

    /**
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionIndex()
    {
        $product_lang_id = Yii::$app->request->get('product_lang_id', null);
        if(!$product_lang_id || !$product_lang = ProductLang::findOne(['id' => $product_lang_id])){
            throw new \Exception('产品不存在');
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
            ->andWhere(['product_id' => $product_lang->product->id])
            ->andWhere(['>=', 'status', StatusEnum::DISABLED]);

        return $this->render($this->action->id, [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'product_lang_id' => $product_lang_id,
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

        if(!$product_lang_id || !$product_lang = ProductLang::findOne(['id' => $product_lang_id])){
            throw new \Exception('产品不存在');
        }

        $model = new ProductQaForm();
        $model->product_id = $product_lang->product->id;

        //保存
        if($model->load(Yii::$app->request->post()) && $model->save()){
            return $this->redirect(['index?product_lang_id='.$product_lang_id]);
        }

        //编辑
        if($id){
            $model->loadData($id);
        }

        return $this->render($this->action->id, [
            'model' => $model,
            'product_lang_id' => $product_lang_id,
        ]);
    }

    /**
     * 伪删除
     *
     * @param $id
     * @return mixed
     */
    public function actionDestroy($id, $product_lang_id)
    {
        if (!($model = $this->modelClass::findOne($id))) {
            return $this->message("找不到数据", $this->redirect(['index?product_lang_id='.$product_lang_id]), 'error');
        }

        $model->status = StatusEnum::DELETE;
        if ($model->save()) {
            return $this->message("删除成功", $this->redirect(['index?product_lang_id='.$product_lang_id]));
        }

        return $this->message("删除失败", $this->redirect(['index?product_lang_id='.$product_lang_id]), 'error');
    }
}