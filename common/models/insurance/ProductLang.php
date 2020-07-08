<?php

namespace common\models\insurance;

use common\enums\LangEnum;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%insurance_product_lang}}".

 * @property string $id
 * @property string $pid
 * @property string $lang 语言
 * @property string $name 产品名称
 * @property string $thumb_img 产品缩略图
 * @property string $banner_img 产品头图
 * @property string $special_img 产品特色图
 * @property string $case_img 客戶案例图
 * @property string $marketing_img 产品营销图
 * @property string $abstract 产品简介
 * @property string $tags 产品标签，多个以逗号隔开
 *
 * @property string $buy_period 投保年龄
 * @property string $guarantee_period 保障年限
 * @property string $renewal_period 缴费年限
 * @property string $calm_period 冷静期
 * @property string $waiting_period 等候期
 *
 * @property string $min_insurance 最低保额
 * @property string $death_compensation 身故赔偿
 * @property string $created_user 创建者
 * @property string $created_at 创建时间
 * @property string $updated_user 修改者
 * @property string $updated_at 修改时间
 * @property int $status 状态(-1:已删除,0:禁用,1:正常)
 *
 * @property Product $product
 */
class ProductLang extends ActiveRecord
{

    const LANGS = [LangEnum::CN, LangEnum::TW];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%insurance_product_lang}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'pid'], 'string', 'max' => 32],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['lang'], 'string', 'max' => 5],
            [['name', 'tags', 'thumb_img', 'banner_img', 'special_img', 'case_img', 'marketing_img', 'abstract'], 'string'],
            [['buy_period', 'guarantee_period', 'renewal_period', 'calm_period', 'waiting_period', 'min_insurance' , 'death_compensation'], 'string'],
            [['created_user', 'updated_user'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pid' => 'Product ID',
            'lang' => '语言',
            'name' => '产品名称',
            'abstract' => '产品简介',
            'tags' => '产品标签',

            'buy_period' => '投保年龄',
            'guarantee_period' => '保障年限',
            'renewal_period' => '缴费年限',
            'calm_period' => '冷静期',
            'waiting_period' => '等候期',

            'min_insurance' => '最低保额',
            'death_compensation' => '身故赔偿',

            'thumb_img' => '产品缩略图',
            'banner_img' => '产品头图',
            'marketing_img' => '产品营销图',
            'special_img' => '产品特色图',
            'case_img' => '客戶案例图',

            'created_user' => '创建者',
            'created_at' => '创建者',
            'updated_user' => '修改者',
            'updated_at' => '修改时间',
            'status' => '状态',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct(){
        return $this->hasOne(Product::class, ['id' => 'pid']);
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],  //创建时，初始化时间
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],                //修改时，更新时间
                ],
            ]
        ];
    }
}
