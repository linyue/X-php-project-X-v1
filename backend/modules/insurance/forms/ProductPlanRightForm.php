<?php

namespace backend\modules\insurance\forms;

use common\helpers\AliyunHelper;
use common\helpers\StringHelper;
use common\models\insurance\ProductPlanRight;
use common\models\insurance\ProductPlanRightLang;
use Yii;
use yii\base\Model;

/**
 * Class
 * @package backend\modules\insurance\models
 */
class ProductPlanRightForm extends Model
{
    public $sync;
    public $id;
    public $product_id;
    public $product_plan_id;
    public $lang;
    public $title;
    public $content;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['product_id'], 'required'],
            [['sync'], 'boolean'],
            [['id', 'product_id', 'product_plan_id', 'lang', 'title', 'content'], 'string'],
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
            'product_plan_id' => 'Product Plan ID',
            'lang' => '语言',
            'title' => '权益标题',
            'content' => '权益内容',
        ];
    }

    /**
     * 加载默认数据
     * @param $id
     * @throws \Exception
     */
    public function loadData($id)
    {
        if ($productPlanRightLang = ProductPlanRightLang::findOne(['id'=> $id])) {
            $this->id = $productPlanRightLang->id;
            $this->lang = $productPlanRightLang->lang;
            $this->title = $productPlanRightLang->title;
            $this->content = $productPlanRightLang->content;
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

            $productPlanRightLang = null;
            if($this->id){
                $productPlanRightLang = ProductPlanRightLang::findOne(['id' => $this->id]);
            }

            //更新主表
            if($productPlanRightLang){
                $productPlanRight = $productPlanRightLang->productPlanRight;
                $productPlanRight->updated_user = Yii::$app->user->identity->username;
            }else{
                $productPlanRight = new ProductPlanRight();
                $productPlanRight->id = StringHelper::uuid('uniqid');
                $productPlanRight->product_plan_id = $this->product_plan_id;
                $productPlanRight->created_user = Yii::$app->user->identity->username;
                $productPlanRight->updated_user = Yii::$app->user->identity->username;
            }

            $productPlanRight->save();

            //更新语言表
            foreach (ProductPlanRightLang::LANGS as $lang){
                $langItem = $productPlanRight->findByLang($lang);

                //未勾选同步，不处理其它语言
                if(!$this->sync && $this->lang != $lang){
                    continue;
                }

                if(!$langItem){
                    $langItem = new ProductPlanRightLang();
                    $langItem->id = StringHelper::uuid('uniqid');
                    $langItem->pid = $productPlanRight->id;
                    $langItem->lang = $lang;
                    $langItem->created_user = Yii::$app->user->identity->username;
                    $langItem->updated_user = Yii::$app->user->identity->username;
                }

                $this->title && $langItem->title = $this->lang == $lang ? $this->title : AliyunHelper::translate($this->title, $lang, 'text', $this->lang);
                $this->content && $langItem->content = $this->lang == $lang ? $this->content : AliyunHelper::translate($this->content, $lang, 'text', $this->lang);

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