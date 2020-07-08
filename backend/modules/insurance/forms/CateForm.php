<?php

namespace backend\modules\insurance\forms;

use common\components\uploaddrive\OSS;
use common\helpers\AliyunHelper;
use common\helpers\StringHelper;
use common\models\insurance\Cate;
use common\models\insurance\CateLang;
use Yii;
use yii\base\Model;

/**
 * Class
 * @package backend\modules\insurance\models
 */
class CateForm extends Model
{
    public $sync;
    public $id;
    public $lang;
    public $name;
    public $icon;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['lang'], 'required'],
            [['sync'], 'boolean'],
            [['id', 'lang', 'name', 'icon'], 'string'],
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
            'lang' => '语言',
            'name' => '类别名称',
            'icon' => '类别图标',
        ];
    }

    /**
     * 加载默认数据
     * @param $id
     * @throws \Exception
     */
    public function loadData($id)
    {
        if ($cateLang = CateLang::findOne(['id'=> $id])) {
            $this->id = $cateLang->id;
            $this->lang = $cateLang->lang;
            $this->name = $cateLang->name;
            $this->icon = OSS::fullPath($cateLang->icon);
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
            //oss文件只存储路径
            if($this->icon){
                $this->icon = parse_url($this->icon)['path'];
            }

            $cateLang = null;
            if($this->id){
                $cateLang = CateLang::findOne(['id' => $this->id]);
            }

            //更新主表
            if($cateLang){
                $cate = $cateLang->cate;
                $cate->updated_user = Yii::$app->user->identity->username;
            }else{
                $cate = new Cate();
                $cate->id = StringHelper::uuid('uniqid');
                $cate->created_user = Yii::$app->user->identity->username;
                $cate->updated_user = Yii::$app->user->identity->username;
            }
            $cate->save();

            //更新语言表
            foreach (CateLang::LANGS as $lang){
                $langItem = $cate->findByLang($lang);

                //未勾选同步，不处理其它语言
                if(!$this->sync && $this->lang != $lang){
                    continue;
                }

                if(!$langItem){
                    $langItem = new CateLang();
                    $langItem->id = StringHelper::uuid('uniqid');
                    $langItem->pid = $cate->id;
                    $langItem->lang = $lang;
                    $langItem->created_user = Yii::$app->user->identity->username;
                    $langItem->updated_user = Yii::$app->user->identity->username;
                }

                $this->name && $langItem->name = $this->lang == $lang ? $this->name : AliyunHelper::translate($this->name, $lang);
                $this->icon && $langItem->icon = $this->icon;

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