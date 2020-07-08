<?php

namespace backend\modules\insurance;

use Yii;

/**
 * insurance module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'backend\modules\insurance\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Yii::$app->services->merchant->addId(0);
        // custom initialization code goes here
    }
}
