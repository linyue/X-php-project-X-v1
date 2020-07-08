<?php
namespace common\helpers;

class BaiduAiHelper{

    /**
     * 文章标签
     * 对文章的标题和内容进行深度分析，输出能够反映文章关键信息的主题、话题、实体等多维度标签
     * http://ai.baidu.com/ai-doc/NLP/7k6z52ggx
     * @param string $title
     * @param string $content
     * @return bool
     */
    public static function keyword($title = '', $content = ''){

        $token = self::token();

        $url = 'https://aip.baidubce.com/rpc/2.0/nlp/v1/keyword?charset=UTF-8&access_token='.$token;

        $data = CurlHelper::post($url, json_encode(['title' => mb_substr($title, 0, 80), 'content' => mb_substr(strip_tags($content), 0, 65535)]));

        $data = json_decode($data, true);

        if(isset($data['items'])){
            return $data['items'];
        }

        return false;
    }


    /**
     * 文章分类
     * 对文章按照内容类型进行自动分类
     * http://ai.baidu.com/ai-doc/NLP/wk6z52gxe
     * @param string $title
     * @param string $content
     * @return bool
     */
    public static function topic($title = '', $content = ''){

        $token = self::token();

        $url = 'https://aip.baidubce.com/rpc/2.0/nlp/v1/topic?charset=UTF-8&access_token='.$token;

        $data = CurlHelper::post($url, json_encode(['title' => mb_substr($title, 0, 80), 'content' => mb_substr(strip_tags($content), 0, 65535)]));

        $data = json_decode($data, true);

        if(isset($data['item'])){
            return $data['item'];
        }

        return false;
    }


    /**
     * 新闻摘要
     * 自动抽取新闻文本中的关键信息，进而生成指定长度的新闻摘要
     * http://ai.baidu.com/ai-doc/NLP/Gk6z52hu3
     * @param string $title 标题
     * @param string $content 内容
     * @param int $len 摘要限制长度
     * @return bool
     */
    public static function summary($title = '', $content = '', $len = 200){

        $token = self::token();

        $url = 'https://aip.baidubce.com/rpc/2.0/nlp/v1/news_summary?charset=UTF-8&access_token='.$token;

        $title = mb_substr($title, 0, 200);
        $content = mb_substr(strip_tags($content), 0, 3000);

        $data = CurlHelper::post($url, json_encode(['title' => $title, 'content' => $content, 'max_summary_len' => $len]));

        $data = json_decode($data, true);

        if(isset($data['summary'])){
            return $data['summary'];
        }

        return false;
    }

    /**
     * 词义相似度
     * 分析两个词的相似度
     * http://ai.baidu.com/ai-doc/NLP/Fk6z52fjc
     * @param $word1
     * @param $word2
     * @return bool|mixed
     */
    public static function wordEmbSim($word1, $word2){
        $token = self::token();

        $url = 'https://aip.baidubce.com/rpc/2.0/nlp/v2/word_emb_sim?charset=UTF-8&access_token='.$token;

        $data = CurlHelper::post($url, json_encode(['word_1' => mb_substr($word1, 0, 64), 'word_2' => mb_substr($word2, 0, 64)]));

        $data = json_decode($data, true);

        if(isset($data['score'])){
            return $data;
        }

        return false;
    }


    /**
     * 获取Token
     * @return bool
     */
    private static function token(){
        $key = "BaiduAiToken";

        $token = \Yii::$app->redis->get($key);

        if(!$token){
            $content = CurlHelper::post('https://aip.baidubce.com/oauth/2.0/token', [
                'grant_type' => 'client_credentials',
                'client_id' => \Yii::$app->params['baiduAi']['client_id'],
                'client_secret' => \Yii::$app->params['baiduAi']['client_secret'],
            ]);

            if(!$content){
                return false;
            }

            $data = json_decode($content, true);

            if(!$data['access_token']){
                return false;
            }

            $token = $data['access_token'];

            \Yii::$app->redis->set($key, $token);
            \Yii::$app->redis->expire($key, 3600 * 24 * 15);
        }

        return $token;
    }

    private static function sign($query, $salt){

    }
}