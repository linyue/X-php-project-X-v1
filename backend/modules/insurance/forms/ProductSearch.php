<?php

namespace backend\modules\insurance\forms;

use common\models\insurance\Product;
use common\models\insurance\ProductLang;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class
 * @package backend\modules\insurance\models
 */
class ProductSearch extends Model
{
    public $name;
    public $company_id;
    public $cate_id;
    public $published_status;

        /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'company_id', 'cate_id'], 'string'],
            [['published_status'], 'integer'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'name' => '产品名称',
            'company_id' => '所属公司',
            'cate_id' => '产品类别',
            'status' => '发布状态',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Product::find()
            ->alias('p')
            ->leftJoin(ProductLang::tableName() . ' pl', 'p.id = pl.pid')
            ->where(['p.status' => 1, 'pl.status' => 1])
            ->orderBy('p.sort desc, p.created_at desc')
            ->groupBy('p.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if($this->name){
            $query->andWhere(['like', 'pl.name', $this->name]);
        }
        if($this->company_id){
            $query->andWhere(['p.company_id' =>$this->company_id]);
        }
        if($this->cate_id){
            $query->andWhere(['like', 'p.cate_id', $this->cate_id]);
        }
        if($this->published_status){
            $query->andWhere(['p.published_status' =>$this->published_status]);
        }

        return $dataProvider;
    }
}