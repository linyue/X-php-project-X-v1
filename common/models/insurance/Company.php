<?php

namespace common\models\insurance;

use common\enums\LangEnum;
use common\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%insurance_company}}".
 *
 * @property string $id
 * @property int $sort 排序
 * @property int $published_status 发布状态(-1:停用,1:草稿,2:全量发布,3:定时发布,11:1级灰度发布)
 * @property string $published_user 发布者
 * @property string $published_at 发布时间
 * @property string $created_user 创建者
 * @property string $created_at 创建时间
 * @property string $updated_user 修改者
 * @property string $updated_at 修改时间
 * @property int $status 状态(-1:已删除,0:禁用,1:正常)
 *
 * @property CompanyLang $langs
 * @property int $productCount
 */
class Company extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%insurance_company}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'published_user', 'created_user', 'updated_user'], 'string', 'max' => 32],
            [['sort', 'status', 'published_status', 'published_at', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sort' => '排序',
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
        return $this->hasMany(CompanyLang::class, ['pid' => 'id']);
    }

    /**
     * @param $lang
     * @return CompanyLang|null
     */
    public function findByLang($lang){
        return CompanyLang::findOne(['pid' => $this->id, 'lang' => $lang]);
    }

    public function getProductCount(){
        return $this->hasMany(Product::class, ['company_id' => 'id'])->andWhere(['status' => 1])->count();
    }

    /**
     * 获得名称和ID的列表，用于下拉选项
     * @return array
     */
    public static function getMap(){
        $list = Company::find()->alias('a')
            ->leftJoin(CompanyLang::tableName() . ' b', 'a.id = b.pid' )
            ->select('a.id, b.name')
            ->where(['b.lang' => LangEnum::CN, 'a.status' => 1, 'b.status' =>1 ])
            ->orderBy('name asc')->asArray()->all();
        return ArrayHelper::map($list,'id','name');
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
