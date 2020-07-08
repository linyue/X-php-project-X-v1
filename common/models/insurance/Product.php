<?php

namespace common\models\insurance;

use common\enums\LangEnum;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%insurance_product}}".
 *
 * @property string $id
 * @property string $company_id 公司ID
 * @property string $cate_id 产品类别ID，多个用（ , ）分隔
 * @property int $sort 排序
 * @property int $sale_status 在售状态(1:在售,2:停售)
 *
 * @property string $premium_currency 保费币种
 * @property string $premium_payment 缴费方式
 * @property int $premium_model 保费模式(1:固定模式,2:递增模式)
 *
 * @property int $published_status 发布状态(-1:停用,1:草稿,2:全量发布,3:定时发布,11:灰度发布)
 * @property string $published_user 发布者
 * @property string $published_at 发布时间
 * @property string $created_user 创建者
 * @property string $created_at 创建时间
 * @property string $updated_user 修改者
 * @property string $updated_at 修改时间
 * @property int $status 状态(-1:已删除,0:禁用,1:正常)
 *
 * @property ProductLang $langs
 * @property Company $company
 * @property Cate $cates
 * @property int $qaCount
 * @property int $planCount
 */
class Product extends ActiveRecord
{
    const SALE_STATUS = [
        LangEnum::CN => [
            '1' => '在售',
            '2' => '停售',
        ],
        LangEnum::TW => [
            '1' => '在售',
            '2' => '停售',
        ],
    ];

    const PREMIUM_CURRENCY = [
        LangEnum::CN => [
            'RMB' => '人民币',
            'USD' => '美金',
            'HKD' => '港币',
            'MOP' => '澳门元',
        ],
        LangEnum::TW => [
            'RMB' => '人民幣',
            'USD' => '美金',
            'HKD' => '港幣',
            'MOP' => '澳門元',
        ],
    ];

    const PREMIUM_MODEL = [
        LangEnum::CN => [
            '1' => '固定模式',
            '2' => '递增模式',
        ],
        LangEnum::TW => [
            '1' => '固定模式',
            '2' => '遞增模式',
        ],
    ];

    const PREMIUM_PAYMENT = [
        LangEnum::CN => [
            '1' => '月供',
            '2' => '季供',
            '3' => '半年供',
            '4' => '年供',
            '5' => '一次性缴付',
            '6' => '每5年供',
            '7' => '按年缴及预测剩余年期',
            '8' => '预交保费',
            '9' => '按年缴交及预缴保费',
        ],
        LangEnum::TW => [
            '1' => '月供',
            '2' => '季供',
            '3' => '半年供',
            '4' => '年供',
            '5' => '一次性繳付',
            '6' => '每5年供',
            '7' => '按年繳及預測剩餘年期',
            '8' => '預交保費',
            '9' => '按年繳交及預繳保費',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%insurance_product}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'premium_currency', 'premium_payment', 'published_user', 'created_user', 'updated_user'], 'string', 'max' => 32],
            [['cate_id'], 'string'],
            [['sort', 'sale_status', 'premium_model', 'status', 'published_status', 'published_at', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => '公司ID',
            'cate_id' => '品类ID',
            'sort' => '排序',
            'sale_status' => '在售状态',
            'premium_currency' => '保费币种',
            'premium_model' => '保费模式',
            'premium_payment' => '缴费方式',

            'published_status' => '发布状态',
            'published_user' => '发布者',
            'published_at' => '发布时间',
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
    public function getLangs(){
        return $this->hasMany(ProductLang::class, ['pid' => 'id']);
    }

    /**
     * @param $lang
     * @return ProductLang|null
     */
    public function findByLang($lang){
        return ProductLang::findOne(['pid' => $this->id, 'lang' => $lang]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany(){
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * @return Cate[]
     */
    public function getCates(){
        return Cate::findAll(['id'=> explode(',', $this->cate_id), 'status' => '1']);
    }

    public function getQaCount(){
        return $this->hasMany(ProductQa::class, ['product_id' => 'id'])->andWhere(['status' => 1])->count();
    }

    public function getPlanCount(){
        return $this->hasMany(ProductPlan::class, ['product_id' => 'id'])->andWhere(['status' => 1])->count();
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
