<?php

namespace backend\modules\insurance\forms;

use common\helpers\AliyunHelper;
use common\helpers\StringHelper;
use common\models\insurance\ProductQa;
use common\models\insurance\ProductQaLang;
use Yii;
use yii\base\Model;

/**
 * Class
 * @package backend\modules\insurance\models
 */
class ProductQaForm extends Model
{
    public $sync;
    public $id;
    public $product_id;
    public $lang;
    public $question;
    public $answer;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['product_id'], 'required'],
            [['sync'], 'boolean'],
            [['id', 'product_id', 'lang', 'question', 'answer'], 'string'],
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
            'question' => '问题',
            'answer' => '解答',
        ];
    }

    /**
     * 加载默认数据
     * @param $id
     * @throws \Exception
     */
    public function loadData($id)
    {
        if ($productQaLang = ProductQaLang::findOne(['id'=> $id])) {
            $this->id = $productQaLang->id;
            $this->lang = $productQaLang->lang;
            $this->question = $productQaLang->question;
            $this->answer = $productQaLang->answer;
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

            $productQaLang = null;
            if($this->id){
                $productQaLang = ProductQaLang::findOne(['id' => $this->id]);
            }

            //更新主表
            if($productQaLang){
                $productQa = $productQaLang->productQa;
                $productQa->updated_user = Yii::$app->user->identity->username;
            }else{
                $productQa = new ProductQa();
                $productQa->id = StringHelper::uuid('uniqid');
                $productQa->product_id = $this->product_id;
                $productQa->created_user = Yii::$app->user->identity->username;
                $productQa->updated_user = Yii::$app->user->identity->username;
            }
            $productQa->save();

            //更新语言表
            foreach (ProductQaLang::LANGS as $lang){
                $langItem = $productQa->findByLang($lang);

                //未勾选同步，不处理其它语言
                if(!$this->sync && $this->lang != $lang){
                    continue;
                }

                if(!$langItem){
                    $langItem = new ProductQaLang();
                    $langItem->id = StringHelper::uuid('uniqid');
                    $langItem->pid = $productQa->id;
                    $langItem->lang = $lang;
                    $langItem->created_user = Yii::$app->user->identity->username;
                    $langItem->updated_user = Yii::$app->user->identity->username;
                }

                $this->question && $langItem->question = $this->lang == $lang ? $this->question : AliyunHelper::translate($this->question, $lang, 'text', $this->lang);
                $this->answer && $langItem->answer = $this->lang == $lang ? $this->answer : AliyunHelper::translate($this->answer, $lang, 'text', $this->lang);

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