<?php

namespace backend\modules\insurance\forms;

use common\components\uploaddrive\OSS;
use common\helpers\AliyunHelper;
use common\helpers\StringHelper;
use common\models\insurance\Product;
use common\models\insurance\ProductLang;
use Yii;
use yii\base\Model;

/**
 * Class
 * @package backend\modules\insurance\models
 */
class ProductForm extends Model
{
    public $sync;
    public $id;
    public $product_id;
    public $company_id;
    public $cate_id;
    public $lang;
    public $name;
    public $sale_status;
    public $abstract;
    public $tags;

    public $premium_currency;
    public $premium_model;
    public $premium_payment;

    public $buy_period;
    public $guarantee_period;
    public $renewal_period;
    public $calm_period;
    public $waiting_period;

    public $min_insurance;
    public $death_compensation;

    public $thumb_img;
    public $banner_img;
    public $marketing_img;
    public $special_img;
    public $case_img;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['lang'], 'required'],
            [['sync'], 'boolean'],
            [['id', 'product_id', 'company_id', 'lang', 'name', 'abstract', 'tags'], 'string'],
            [['sale_status', 'premium_model'], 'integer'],
            [['cate_id', 'premium_currency', 'premium_payment'], 'safe'],
            [['thumb_img', 'banner_img', 'marketing_img', 'special_img', 'case_img'], 'string'],
            [['buy_period', 'guarantee_period', 'renewal_period', 'calm_period', 'waiting_period', 'min_insurance', 'death_compensation'], 'string']
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
            'company_id' => '所属公司',
            'cate_id' => '产品类别',
            'lang' => '语言',
            'name' => '产品名称',
            'sale_status' => '在售状态',
            'abstract' => '产品简介',
            'tags' => '产品标签',

            'premium_currency' => '保费币种',
            'premium_payment' => '缴费方式',
            'premium_model' => '保费模式',

            'buy_period' => '投保年龄',
            'renewal_period' => '缴费年限',
            'guarantee_period' => '保障年限',
            'calm_period' => '冷静期',
            'waiting_period' => '等候期',

            'min_insurance' => '最低保额',
            'death_compensation' => '身故赔偿',

            'thumb_img' => '产品缩略图',
            'banner_img' => '产品头图',
            'marketing_img' => '产品营销图',
            'special_img' => '产品特色图',
            'case_img' => '客戶案例图',
        ];
    }

    /**
     * 加载默认数据
     * @param $id
     * @throws \Exception
     */
    public function loadData($id)
    {
        if ($productLang = ProductLang::findOne(['id'=> $id])) {

            $this->id = $productLang->id;
            $this->company_id = $productLang->product->company_id;
            $this->cate_id = explode(',', $productLang->product->cate_id);
            $this->lang = $productLang->lang;
            $this->name = $productLang->name;
            $this->sale_status = $productLang->product->sale_status;
            $this->abstract = $productLang->abstract;
            $this->tags = $productLang->tags;

            $this->premium_currency = explode(',', $productLang->product->premium_currency);
            $this->premium_model = $productLang->product->premium_model;
            $this->premium_payment = explode(',', $productLang->product->premium_payment);

            $this->buy_period = $productLang->buy_period;
            $this->guarantee_period = $productLang->guarantee_period;
            $this->renewal_period = $productLang->renewal_period;
            $this->calm_period = $productLang->calm_period;
            $this->waiting_period = $productLang->waiting_period;

            $this->min_insurance = $productLang->min_insurance;
            $this->death_compensation = $productLang->death_compensation;

            $this->thumb_img = OSS::fullPath($productLang->thumb_img);
            $this->banner_img = OSS::fullPath($productLang->banner_img);
            $this->marketing_img = OSS::fullPath($productLang->marketing_img);
            $this->special_img = OSS::fullPath($productLang->special_img);
            $this->case_img = OSS::fullPath($productLang->case_img);
        }
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    public function save(){
        $transaction = Yii::$app->db->beginTransaction();
        try {
            //oss文件只存储路径
            if($this->thumb_img){
                $this->thumb_img = parse_url($this->thumb_img)['path'];
            }
            if($this->banner_img){
                $this->banner_img = parse_url($this->banner_img)['path'];
            }
            if($this->marketing_img){
                $this->marketing_img = parse_url($this->marketing_img)['path'];
            }
            if($this->special_img){
                $this->special_img = parse_url($this->special_img)['path'];
            }
            if($this->case_img){
                $this->case_img = parse_url($this->case_img)['path'];
            }

            $productLang = null;
            if($this->id){
                $productLang = ProductLang::findOne(['id' => $this->id]);
            }

            //更新主表
            if($productLang){
                $product = $productLang->product;
            }else{
                $product = new Product();
                $product->id = StringHelper::uuid('uniqid');
                $product->created_user = Yii::$app->user->identity->username;
            }
            $product->updated_user = Yii::$app->user->identity->username;

            $this->company_id && $product->company_id = $this->company_id;
            $this->cate_id && $product->cate_id = join(',', $this->cate_id);
            $this->sale_status && $product->sale_status = $this->sale_status;
            $this->premium_currency && $product->premium_currency = join(',', $this->premium_currency);
            $this->premium_payment && $product->premium_payment = join(',', $this->premium_payment);
            $this->premium_model && $product->premium_model = $this->premium_model;

            $product->save();

            //更新语言表
            foreach (ProductLang::LANGS as $lang){
                $langItem = $product->findByLang($lang);

                //未勾选同步，不处理其它语言
                if(!$this->sync && $this->lang != $lang){
                    continue;
                }

                if(!$langItem){
                    $langItem = new ProductLang();
                    $langItem->id = StringHelper::uuid('uniqid');
                    $langItem->pid = $product->id;
                    $langItem->lang = $lang;
                    $langItem->created_user = Yii::$app->user->identity->username;
                    $langItem->updated_user = Yii::$app->user->identity->username;
                }

                $this->name && $langItem->name = $this->lang == $lang ? $this->name : AliyunHelper::translate($this->name, $lang, 'text', $this->lang);
                $this->abstract && $langItem->abstract = $this->lang == $lang ? $this->abstract : AliyunHelper::translate($this->abstract, $lang, 'text', $this->lang);
                $this->tags && $langItem->tags = $this->lang == $lang ? $this->tags : AliyunHelper::translate($this->tags, $lang, 'text', $this->lang);

                $this->buy_period && $langItem->buy_period = $this->lang == $lang ? $this->buy_period : AliyunHelper::translate($this->buy_period, $lang, 'text', $this->lang);
                $this->guarantee_period && $langItem->guarantee_period = $this->lang == $lang ? $this->guarantee_period : AliyunHelper::translate($this->guarantee_period, $lang, 'text', $this->lang);
                $this->renewal_period && $langItem->renewal_period = $this->lang == $lang ? $this->renewal_period : AliyunHelper::translate($this->renewal_period, $lang, 'text', $this->lang);
                $this->calm_period && $langItem->calm_period = $this->lang == $lang ? $this->calm_period : AliyunHelper::translate($this->calm_period, $lang, 'text', $this->lang);
                $this->waiting_period && $langItem->waiting_period = $this->lang == $lang ? $this->waiting_period : AliyunHelper::translate($this->waiting_period, $lang, 'text', $this->lang);
                $this->min_insurance && $langItem->min_insurance = $this->lang == $lang ? $this->min_insurance : AliyunHelper::translate($this->min_insurance, $lang, 'text', $this->lang);
                $this->death_compensation && $langItem->death_compensation = $this->lang == $lang ? $this->death_compensation : AliyunHelper::translate($this->death_compensation, $lang, 'text', $this->lang);

                $this->thumb_img && $langItem->thumb_img = $this->thumb_img;
                $this->banner_img && $langItem->banner_img = $this->banner_img;
                $this->marketing_img && $langItem->marketing_img = $this->marketing_img;
                $this->special_img && $langItem->special_img = $this->special_img;
                $this->case_img && $langItem->case_img = $this->case_img;

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