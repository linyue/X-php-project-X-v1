<?php

namespace backend\modules\insurance\forms;

use common\components\uploaddrive\OSS;
use common\helpers\AliyunHelper;
use common\helpers\StringHelper;
use common\models\insurance\Company;
use common\models\insurance\CompanyLang;
use Yii;
use yii\base\Model;

/**
 * Class
 * @package backend\modules\insurance\models
 */
class CompanyForm extends Model
{
    public $sync;
    public $id;
    public $lang;
    public $name;
    public $tel;
    public $addr;
    public $website;
    public $logo;
    public $bgi;
    public $abstract;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['lang'], 'required'],
            [['sync'], 'boolean'],
            [['id', 'lang', 'name', 'tel', 'addr', 'website', 'logo', 'bgi', 'abstract'], 'string'],
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
            'name' => '公司名',
            'tel' => '公司电话',
            'addr' =>'公司地址',
            'website' => '公司官网',
            'logo' => '公司logo',
            'bgi' => '公司背景图',
            'abstract' => '公司简介',
        ];
    }

    /**
     * 加载默认数据
     * @param $id
     * @throws \Exception
     */
    public function loadData($id)
    {
        if ($companyLang = CompanyLang::findOne(['id'=> $id])) {
            $this->id = $companyLang->id;
            $this->lang = $companyLang->lang;
            $this->name = $companyLang->name;
            $this->tel = $companyLang->tel;
            $this->addr = $companyLang->addr;
            $this->website = $companyLang->website;
            $this->logo = OSS::fullPath($companyLang->logo);
            $this->bgi = OSS::fullPath($companyLang->bgi);
            $this->abstract = $companyLang->abstract;
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
            if($this->logo){
                $this->logo = parse_url($this->logo)['path'];
            }
            if($this->bgi){
                $this->bgi = parse_url($this->bgi)['path'];
            }

            $companyLang = null;
            if($this->id){
                $companyLang = CompanyLang::findOne(['id' => $this->id]);
            }

            //更新主表
            if($companyLang){
                $company = $companyLang->company;
                $company->updated_user = Yii::$app->user->identity->username;
            }else{
                $company = new Company();
                $company->id = StringHelper::uuid('uniqid');
                $company->created_user = Yii::$app->user->identity->username;
                $company->updated_user = Yii::$app->user->identity->username;
            }
            $company->save();

            //更新语言表
            foreach (CompanyLang::LANGS as $lang){
                $langItem = $company->findByLang($lang);

                //未勾选同步，不处理其它语言
                if(!$this->sync && $this->lang != $lang){
                    continue;
                }

                if(!$langItem){
                    $langItem = new CompanyLang();
                    $langItem->id = StringHelper::uuid('uniqid');
                    $langItem->pid = $company->id;
                    $langItem->lang = $lang;
                    $langItem->created_user = Yii::$app->user->identity->username;
                    $langItem->updated_user = Yii::$app->user->identity->username;
                }
                $this->name && $langItem->name = $this->lang == $lang ? $this->name : AliyunHelper::translate($this->name, $lang, 'text', $this->lang);
                $this->tel && $langItem->tel = $this->tel;
                $this->addr && $langItem->addr = $this->lang == $lang ? $this->addr : AliyunHelper::translate($this->addr, $lang, 'text', $this->lang);
                $this->website && $langItem->website = $this->website;
                $this->logo && $langItem->logo = $this->logo;
                $this->bgi && $langItem->bgi = $this->bgi;
                $this->abstract && $langItem->abstract = $this->lang == $lang ? $this->abstract : AliyunHelper::translate($this->abstract, $lang, 'text', $this->lang);

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