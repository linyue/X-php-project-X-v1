<?php

namespace common\models\insurance;

use common\enums\LangEnum;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%insurance_cate_lang}}".

 * @property string $id
 * @property string $pid
 * @property string $lang 语言
 * @property string $name 类别名称
 * @property string $icon 类别图标
 * @property string $created_user 创建者
 * @property string $created_at 创建时间
 * @property string $updated_user 修改者
 * @property string $updated_at 修改时间
 * @property int $status 状态(-1:已删除,0:禁用,1:正常)
 *
 * @property Cate $cate
 */
class CateLang extends ActiveRecord
{

    const LANGS = [LangEnum::CN, LangEnum::TW];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%insurance_cate_lang}}';
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
            [['name', 'icon'], 'string'],
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
            'pid' => 'Cate ID',
            'lang' => '语言',
            'name' => '类别名称',
            'icon' => '类别图标',
            'status' => '状态',
            'created_user' => '创建者',
            'created_at' => '创建者',
            'updated_user' => '修改者',
            'updated_at' => '修改时间',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCate(){
        return $this->hasOne(Cate::class, ['id' => 'pid']);
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
