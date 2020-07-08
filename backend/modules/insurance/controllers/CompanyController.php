<?php

namespace backend\modules\insurance\controllers;

use backend\modules\insurance\forms\CompanyForm;
use common\helpers\TranslateHelper;
use common\models\insurance\Company;
use Yii;
use common\enums\StatusEnum;
use common\models\base\SearchModel;
use common\traits\Curd;
use backend\controllers\BaseController;

/**
 * Class CompanyController
 * @package backend\modules\insurance\controllers
 */
class CompanyController extends BaseController
{
    use Curd;

    /**
     * @var Company
     */
    public $modelClass = Company::class;

    /**
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionIndex()
    {
        $searchModel = new SearchModel([
            'model' => $this->modelClass,
            'scenario' => 'default',
            'partialMatchAttributes' => ['name'], // 模糊查询
            'defaultOrder' => [
                'sort' => SORT_DESC,
                'created_at' => SORT_DESC,
            ],
            'pageSize' => 20,
        ]);

        $dataProvider = $searchModel
            ->search(Yii::$app->request->queryParams);
        $dataProvider->query
            ->andWhere(['>=', 'status', StatusEnum::DISABLED]);

        return $this->render($this->action->id, [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }


    /**
     * 编辑/创建
     *
     * @return mixed
     */
    public function actionEdit(){
        $id = Yii::$app->request->get('id', null);
        $model = new CompanyForm();

        //保存
        if($model->load(Yii::$app->request->post()) && $model->save()){
            return $this->redirect(['index']);
        }

        //编辑
        if($id){
            $model->loadData($id);
        }

        return $this->render($this->action->id, [
            'model' => $model,
        ]);
    }
}