<?php

namespace backend\modules\insurance\forms;

use common\helpers\AliyunHelper;
use common\helpers\StringHelper;
use common\models\insurance\ProductPlan;
use common\models\insurance\ProductPlanLang;
use Yii;
use yii\base\Model;

/**
 * Class
 * @package backend\modules\insurance\models
 */
class ProductPlanForm extends Model
{
    public $sync;
    public $id;
    public $product_id;
    public $lang;
    public $name;
    public $type;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['product_id'], 'required'],
            [['sync'], 'boolean'],
            [['id', 'product_id', 'lang', 'name'], 'string'],
            [['type'], 'integer'],
        ];
    }


    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'sync' => '同步修改其它语言',
            'id' => 'Lang ID',
            'product_id' => 'Product ID',
            'lang' => '语言',
            'name' => '计划名称',
            'type' => '计划类型',
        ];
    }

    /**
     * 加载默认数据
     * @param $id
     * @throws \Exception
     */
    public function loadData($id)
    {
        if ($productPlanLang = ProductPlanLang::findOne(['id'=> $id])) {
            $this->id = $productPlanLang->id;
            $this->lang = $productPlanLang->lang;
            $this->name = $productPlanLang->name;
            $this->type = $productPlanLang->productPlan->type;
        }
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function save()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {

            $productPlanLang = null;
            if($this->id){
                $productPlanLang = ProductPlanLang::findOne(['id' => $this->id]);
            }

            //更新主表
            if($productPlanLang){
                $productPlan = $productPlanLang->productPlan;
                $productPlan->updated_user = Yii::$app->user->identity->username;
            }else{
                $productPlan = new ProductPlan();
                $productPlan->id = StringHelper::uuid('uniqid');
                $productPlan->product_id = $this->product_id;
                $productPlan->created_user = Yii::$app->user->identity->username;
                $productPlan->updated_user = Yii::$app->user->identity->username;
            }

            $this->type && $productPlan->type = $this->type;

            $productPlan->save();

            //更新语言表
            foreach (ProductPlanLang::LANGS as $lang){
                $langItem = $productPlan->findByLang($lang, 'text', $this->lang);

                //未勾选同步，不处理其它语言
                if(!$this->sync && $this->lang != $lang){
                    continue;
                }

                if(!$langItem){
                    $langItem = new ProductPlanLang();
                    $langItem->id = StringHelper::uuid('uniqid');
                    $langItem->pid = $productPlan->id;
                    $langItem->lang = $lang;
                    $langItem->created_user = Yii::$app->user->identity->username;
                    $langItem->updated_user = Yii::$app->user->identity->username;
                }

                $this->name && $langItem->name = $this->lang == $lang ? $this->name : AliyunHelper::translate($this->name, $lang, 'text', $this->lang);

                $langItem->save();
            }

            $transaction->commit();

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw new \Exception($e->getMessage());
        }
    }
}