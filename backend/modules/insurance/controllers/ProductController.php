<?php

namespace backend\modules\insurance\controllers;

use backend\modules\insurance\forms\ProductForm;
use backend\modules\insurance\forms\ProductSearch;
use common\enums\LangEnum;
use common\helpers\AliyunUploadHelper;
use common\helpers\OSSUploadHelper;
use common\helpers\StringHelper;
use common\helpers\TranslateHelper;
use common\models\insurance\Product;
use common\models\insurance\ProductLang;
use common\models\insurance\ProductPlan;
use common\models\insurance\ProductPlanLang;
use common\models\insurance\ProductPlanRight;
use common\models\insurance\ProductPlanRightLang;
use common\models\insurance\ProductQa;
use common\models\insurance\ProductQaLang;
use Yii;
use common\traits\Curd;
use backend\controllers\BaseController;

/**
 * Class ProductController
 * @package backend\modules\insurance\controllers
 */
class ProductController extends BaseController
{
    use Curd;

    /**
     * @var Product
     */
    public $modelClass = Product::class;

    /**
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionIndex()
    {
        $searchModel = new ProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * 编辑/创建
     *
     * @return mixed
     */
    public function actionEdit(){
        $id = Yii::$app->request->get('id', null);
        $model = new ProductForm();

        //保存
        if($model->load(Yii::$app->request->post()) && $model->save()){
            return $this->redirect(['index']);
        }

        //编辑
        if($id){
            $model->loadData($id);
        }

        return $this->render($this->action->id, [
            'model' => $model,
        ]);
    }

    public function actionSync(){

        $url = 'https://static.iberhk.com/';
        $upload = new OSSUploadHelper();
        $upload->saveDir = 'oss/insurance/images';

        $idb = Yii::$app->get('iBerDB');

        $list = $idb->createCommand("select * from product where status = 4 and deleted = 0;")->queryAll();

        echo '<ul>';
        foreach ($list as $item){
            echo '<li>正在同步：product_id=>'.$item['uuid'].'</li>';
            echo '<ul>';
            $product = Product::findOne(['id' => $item['uuid']]);
            if(!$product){
                echo '<li>主产品不存在，开始创建</li>';
                $product = new Product();

                $product->id = $item['uuid'];
                $product->company_id = $item['company_uuid'];
                $product->cate_id = $item['product_category_uuids'];
                $product->sale_status = $item['sale_status'];
                $product->premium_currency = $this->convertCurrency($item['premiun_currency']);
                $product->premium_model = $item['premiun_model'];
                $product->premium_payment = $this->convertPayment($item['payment_method']);
                $product->published_status = 2;
                $product->published_user = 'Adam';
                $product->published_at = time();
                $product->save();
            }else{
                echo '<li>主产品已存在，跳过</li>';
            }

            $cnp = $idb->createCommand("select * from product_lang where product_uuid = '{$item['uuid']}' and lang = 'zh-cn';")->queryOne();

            $productCN = ProductLang::findOne(['id' => $cnp['uuid']]);
            if(!$productCN){
                echo '<li>CN子产品不存在，开始创建</li>';

                $productLangCN = new ProductLang();

                $productLangCN->id = $cnp['uuid'];
                $productLangCN->pid = $cnp['product_uuid'];
                $productLangCN->lang = LangEnum::CN;
                $productLangCN->name = $cnp['name'];
                $productLangCN->abstract = $cnp['abstract'];
                $productLangCN->tags = $cnp['tags'];
                $productLangCN->buy_period = $cnp['guarantee_age_l'] . ' ~ ' . $cnp['guarantee_age_r'];
                $productLangCN->guarantee_period = $cnp['guarantee_period'];
                $productLangCN->renewal_period = $cnp['renewal_perion'];
                $productLangCN->calm_period = $cnp['calm_period'];
                $productLangCN->waiting_period = $cnp['waiting_period'];
                $productLangCN->min_insurance = $cnp['min_insurance_limit'];
                $productLangCN->death_compensation = $cnp['death_compensation'];

                if($cnp['thumbnail']){
                    $productLangCN->thumb_img = $upload->uploadFormUrl($url.$cnp['thumbnail'])['path'];
                }
                if($cnp['picture']){
                    $productLangCN->banner_img = $upload->uploadFormUrl($url.$cnp['picture'])['path'];
                }
                if($cnp['h5_banner']){
                    $productLangCN->marketing_img = $upload->uploadFormUrl($url.$cnp['h5_banner'])['path'];
                }
                if($cnp['special']){
                    $productLangCN->special_img = $upload->uploadFormUrl($url.$cnp['special'])['path'];
                }
                if($cnp['customer_case']){
                    $productLangCN->case_img = $upload->uploadFormUrl($url.$cnp['customer_case'])['path'];
                }

                if($item['next_birthday_age'] == 1){
                    $productLangCN->buy_period .= '（年龄以下次生日为准）';
                }elseif ($item['last_birthday_age'] == 1){
                    $productLangCN->buy_period .= '（年龄以上次生日为准）';
                }

                $productLangCN->save();
            }else{
                echo '<li>CN子产品已存在，跳过</li>';
            }

            $twp = $idb->createCommand("select * from product_lang where product_uuid = '{$item['uuid']}' and lang = 'zh-tw';")->queryOne();

            $productTW = ProductLang::findOne(['id' => $twp['uuid']]);
            if(!$productTW){
                echo '<li>TW子产品不存在，开始创建</li>';

                $productLangTW = new ProductLang();

                 $productLangTW->id = $twp['uuid'];
                 $productLangTW->pid = $twp['product_uuid'];
                 $productLangTW->lang = LangEnum::TW;
                 $productLangTW->name = $twp['name'];
                 $productLangTW->abstract = $twp['abstract'];
                 $productLangTW->tags = $twp['tags'];

                 $productLangTW->buy_period = $twp['guarantee_age_l'] . ' ~ ' . $twp['guarantee_age_r'];
                 $productLangTW->guarantee_period = $twp['guarantee_period'];
                 $productLangTW->renewal_period = $twp['renewal_perion'];
                 $productLangTW->calm_period = $twp['calm_period'];
                 $productLangTW->waiting_period = $twp['waiting_period'];

                 $productLangTW->min_insurance = $twp['min_insurance_limit'];
                 $productLangTW->death_compensation = $twp['death_compensation'];

                if($twp['thumbnail']){
                    $productLangTW->thumb_img = $upload->uploadFormUrl($url.$twp['thumbnail'])['path'];
                }
                if($twp['picture']){
                    $productLangTW->banner_img = $upload->uploadFormUrl($url.$twp['picture'])['path'];
                }
                if($twp['h5_banner']){
                    $productLangTW->marketing_img = $upload->uploadFormUrl($url.$twp['h5_banner'])['path'];
                }
                if($twp['special']){
                    $productLangTW->special_img = $upload->uploadFormUrl($url.$twp['special'])['path'];
                }
                if($twp['customer_case']){
                    $productLangTW->case_img = $upload->uploadFormUrl($url.$twp['customer_case'])['path'];
                }

                if($item['next_birthday_age'] == 1){
                    $productLangTW->buy_period .= '（年齡以下次生日為準）';
                }elseif ($item['last_birthday_age'] == 1){
                    $productLangTW->buy_period .= '（年齡以上次生日為準）';
                }

                $productLangTW->save();
            }else{
                echo '<li>TW子产品已存在，跳过</li>';
            }


            //同步常见问题
            $qaCount = $product->getQaCount();
            $icnqa = json_decode($cnp['common_question'], true);
            $itwqa = json_decode($twp['common_question'], true);
            $icnqaCount = count($icnqa);
            $itwqaCount = count($itwqa);

            if($icnqaCount != $itwqaCount){
                echo '<li style="color: red;">CN和TW的常见问题数量不一致</li>';
            }else{
                if($qaCount == $icnqaCount){
                    echo '<li>常见问题已同步，跳过</li>';
                }else{
                    echo '<li>开始同步常见问题</li>';

                    //先删除原有的所有常见问题
                    ProductQa::deleteAll(['product_id' => $product->id]);

                    foreach ($icnqa as $k => $v){
                        $qa = new ProductQa();
                        $qa->id = StringHelper::uuid('uniqid');
                        $qa->product_id = $product->id;
                        $qa->sort = $k + 1;
                        $qa->created_user = Yii::$app->user->identity->username;
                        $qa->updated_user = Yii::$app->user->identity->username;
                        $qa->save();

                        $cnQaLang = new ProductQaLang();
                        $cnQaLang->id = StringHelper::uuid('uniqid');
                        $cnQaLang->pid = $qa->id;
                        $cnQaLang->lang = LangEnum::CN;
                        $cnQaLang->question = $v['question'];
                        $cnQaLang->answer = $v['answer'];
                        $cnQaLang->created_user = Yii::$app->user->identity->username;
                        $cnQaLang->updated_user = Yii::$app->user->identity->username;
                        $cnQaLang->save();

                        $twQaLang = new ProductQaLang();
                        $twQaLang->id = StringHelper::uuid('uniqid');
                        $twQaLang->pid = $qa->id;
                        $twQaLang->lang = LangEnum::TW;
                        $twQaLang->question = $itwqa[$k]['question'];
                        $twQaLang->answer = $itwqa[$k]['answer'];
                        $twQaLang->created_user = Yii::$app->user->identity->username;
                        $twQaLang->updated_user = Yii::$app->user->identity->username;
                        $twQaLang->save();
                    }
                }
            }

            //同步保单计划
            $ipls = $idb->createCommand("select * from product_guarantee_plan where product_uuid = '{$item['uuid']}' order by sort desc;")->queryAll();
            echo '<li><ul>';
            foreach ($ipls as $ipl){
                $pl = ProductPlan::findOne(['id' => $ipl['uuid']]);
                if(!$pl){
                    echo '<li>开始同步计划：' . $ipl['name_zh_cn'] .'</li>';
                    $pl = new ProductPlan();
                    $pl->id = $ipl['uuid'];
                    $pl->product_id = $product->id;
                    $pl->type = $ipl['is_primary'] ? '1' : 2;
                    $pl->created_user = Yii::$app->user->identity->username;
                    $pl->updated_user = Yii::$app->user->identity->username;

                    $pl->save();

                    $cnPl = new ProductPlanLang();
                    $cnPl->id = StringHelper::uuid('uniqid');
                    $cnPl->pid = $pl->id;
                    $cnPl->lang = LangEnum::CN;
                    $cnPl->name = $ipl['name_zh_cn'];
                    $cnPl->created_user = Yii::$app->user->identity->username;
                    $cnPl->updated_user = Yii::$app->user->identity->username;
                    $cnPl->save();

                    $twPl = new ProductPlanLang();
                    $twPl->id = StringHelper::uuid('uniqid');
                    $twPl->pid = $pl->id;
                    $twPl->lang = LangEnum::TW;
                    $twPl->name = $ipl['name_zh_tw'];
                    $twPl->created_user = Yii::$app->user->identity->username;
                    $twPl->updated_user = Yii::$app->user->identity->username;
                    $twPl->save();


                    //同步保障权益
                    $irs = $idb->createCommand("select * from product_guarantee_plan_right where product_guarantee_plan_uuid = '{$ipl['uuid']}' group by lang_hash order by sort desc;")->queryAll();

                    foreach ($irs as $k => $ir){
//                        print_r($ir);
                        $ppr = ProductPlanRight::findOne(['id' => $ir['lang_hash']]);

                        if(!$ppr){
                            $ppr = new ProductPlanRight();
                            $ppr->id = $ir['lang_hash'];
                            $ppr->product_plan_id = $pl->id;
                            $ppr->sort = $k + 1;
                            $ppr->created_user = Yii::$app->user->identity->username;
                            $ppr->updated_user = Yii::$app->user->identity->username;

                            $ppr->save();

                            $cnIr = $idb->createCommand("select * from product_guarantee_plan_right where lang_hash = '{$ppr->id}' and lang='zh-cn';")->queryOne();
                            $cnPpr = ProductPlanRightLang::findOne(['id' => $cnIr['uuid']]);
                            if(!$cnPpr){
                                $cnPpr = new ProductPlanRightLang();
                                $cnPpr->id = $cnIr['uuid'];
                                $cnPpr->pid = $ppr->id;
                                $cnPpr->lang = LangEnum::CN;
                                $cnPpr->title = $cnIr['title'];
                                $cnPpr->content = $cnIr['content'] ? $cnIr['content'] : $cnIr['premium'];
                                $cnPpr->created_user = Yii::$app->user->identity->username;
                                $cnPpr->updated_user = Yii::$app->user->identity->username;
                                $cnPpr->save();
                            }

                            $twIr = $idb->createCommand("select * from product_guarantee_plan_right where lang_hash = '{$ppr->id}' and lang='zh-tw';")->queryOne();
                            $twPpr = ProductPlanRightLang::findOne(['id' => $twIr['uuid']]);
                            if(!$twPpr){
                                $twPpr = new ProductPlanRightLang();
                                $twPpr->id = $twIr['uuid'];
                                $twPpr->pid = $ppr->id;
                                $twPpr->lang = LangEnum::TW;
                                $twPpr->title = $twIr['title'];
                                $twPpr->content = $twIr['content'] ? $twIr['content'] : $twIr['premium'];
                                $twPpr->created_user = Yii::$app->user->identity->username;
                                $twPpr->updated_user = Yii::$app->user->identity->username;
                                $twPpr->save();
                            }
                        }
                    }
                }else{
                    echo '<li>计划：' . $ipl['name_zh_cn'] .'已存在，跳过</li>';
                }
            }
            echo '</ul></li>';


            echo '</ul>';
        }
        echo '</ul>';
        exit;
    }

    private function convertCurrency($old){
        $currencyMap = [
            '3' => 'RMB',
            '1' => 'USD',
            '2' => 'HKD',
            '4' => 'MOP',
        ];

        $new = [];
        foreach (explode(',', $old) as $item){
            $new[] = $currencyMap[$item];
        }

        return join(',', $new);
    }

    private function convertPayment($old){
        $new = [];
        foreach (explode(',', $old) as $item){
            $new[] = $item + 1;
        }

        return join(',', $new);
    }
}