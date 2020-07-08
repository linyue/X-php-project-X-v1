<?php
namespace common\helpers;

use phpDocumentor\Reflection\Types\Array_;
use Yii;

class AliyunHelper{

    /**
     * 机器翻译
     * 语言支持（常用）：zh 简体中文；zh-tw 繁体中文；en 英文；
     * 语言支持（欧洲）：ru 俄语；pt 葡萄牙语；es 西班牙语；fr 法语；tr 土耳其语；pl 波兰语；it 意大利语；de 德语；
     * 语言支持（亚洲）：ar 阿拉伯语；th 泰语；vi 越南语；id 印尼语；ms 马来语；ja 日语；ko 韩语
     * https://help.aliyun.com/document_detail/158244.html
     * @param string $sourceText        需要翻译的内容
     * @param string $targetLanguage    翻译的目标语言，
     * @param string $formatType        内容的类型，默认text，支持：text、html
     * @param string $SourceLanguage    内容的原始语言，默认自动检测：auto
     * @return string
     */
    public static function translate($sourceText, $targetLanguage, $formatType = 'text', $SourceLanguage = 'auto' ) {
        $url = 'https://mt.cn-hangzhou.aliyuncs.com/';

        $params = [
            'SourceText' => $sourceText,
            'FormatType' => $formatType,

            'SourceLanguage' => $SourceLanguage,
            'TargetLanguage'=> $targetLanguage,

            'Scene' => 'general',
            'Action' => 'TranslateGeneral',
            'Version' => '2018-10-12',
        ];

        $ret = self::execute($url, $params);

        if($ret['Code'] == 200){
            return $ret['Data']['Translated'];
        }else{
            return $sourceText;
        }
    }

    /**
     * 发送短信
     * 手机号码格式：
     *  国内短信：11位手机号码，例如15951955195
     *  国际/港澳台消息：国际区号+号码，例如85200000000
     * @param String $signName      短信签名名称，需在阿里云后台配置
     * @param String $templateCode  短信模板ID，需在阿里云后台配置
     * @param String $phoneNumbers  接收短信的手机号码，支持对多个手机号码发送短信，手机号码之间以英文逗号（,）分隔
     * @param Array $templateParam  短信模板变量对应的实际值
     * @return mixed|string
     */
    public static function sms($signName, $templateCode, $phoneNumbers, $templateParam){
        $url = 'http://dysmsapi.aliyuncs.com/';

        $params = [
            'SignName' => $signName,
            'TemplateCode' => $templateCode,
            'PhoneNumbers' => $phoneNumbers,
            'TemplateParam' => json_encode($templateParam),

            'Action' => 'SendSms',
            'Version' => '2017-05-25',
        ];

        return self::execute($url, $params);
    }

    /**
     * 执行请求
     * @param $url
     * @param $params
     * @return mixed|string
     */
    private static function execute($url, $params){
        $config = Yii::$app->debris->configAll(true);
        $accessKeyId = $config['yun_ali_access_key_id'];
        $accessKeySecret = $config['yun_ali_access_key_secret'];

        $apiParams = array_merge([
            "AccessKeyId" => $accessKeyId,
            "Format" => "JSON",
            'RegionId' => 'default',
            "SecureTransport" => 'true',
            "SignatureMethod" => "HMAC-SHA1",
            "SignatureNonce" => uniqid(mt_rand(0,0xffff), true),
            "SignatureVersion" => "1.0",
            "Timestamp" => gmdate("Y-m-d\TH:i:s\Z"),
        ], $params);
        ksort($apiParams);

        $sortedQueryStringTmp = "";
        foreach ($apiParams as $key => $value) {
            $sortedQueryStringTmp .= "&" . self::encode($key) . "=" . self::encode($value);
        }

        $stringToSign = "GET&%2F&" . self::encode(substr($sortedQueryStringTmp, 1));

        //生成密钥
        $sign = base64_encode(hash_hmac("sha1", $stringToSign, $accessKeySecret . "&",true));

        $signature = self::encode($sign);

        try {
            $url = $url . '?Signature=' . $signature . $sortedQueryStringTmp;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $content = curl_exec($ch);

            curl_close($ch);

            return json_decode($content, true);
        } catch( \Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 转码
     * @param $str
     * @return string|string[]|null
     */
    private static function encode($str){
        $res = urlencode($str);
        $res = preg_replace("/\+/", "%20", $res);
        $res = preg_replace("/\*/", "%2A", $res);
        $res = preg_replace("/%7E/", "~", $res);
        return $res;
    }
}