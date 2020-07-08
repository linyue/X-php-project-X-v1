<?php

namespace common\models\insurance;

use common\enums\LangEnum;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%insurance_product_plan_right}}".
 *
 * @property string $id
 * @property string $product_plan_id
 * @property int $sort 排序
 * @property string $created_user 创建者
 * @property string $created_at 创建时间
 * @property string $updated_user 修改者
 * @property string $updated_at 修改时间
 * @property int $status 状态(-1:已删除,0:禁用,1:正常)
 *
 * @property ProductPlanRightLang $langs
 */
class ProductPlanRight extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%insurance_product_plan_right}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'product_plan_id', 'created_user', 'updated_user'], 'string', 'max' => 32],
            [['sort', 'status', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_plan_id' => '保障计划ID',
            'sort' => '排序',
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
        return $this->hasMany(ProductPlanRightLang::class, ['pid' => 'id']);
    }

    /**
     * @param $lang
     * @return CateLang|null
     */
    public function findByLang($lang){
        return ProductPlanRightLang::findOne(['pid' => $this->id, 'lang' => $lang]);
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
