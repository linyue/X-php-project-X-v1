<?php
namespace common\helpers;


use OSS\Core\OssException;
use OSS\OssClient;
use Yii;

class OSSUploadHelper
{
    /**
     * @var OssClient $oss
     */
    private $oss;
    public $fileOptions = ['headers' => ['x-oss-object-acl' => 'public-read']];

    public $saveDir;                //存储路径
    public $allowMaxSize = null;    //文件大小限制

    public function __construct(){
        $config = Yii::$app->debris->configAll(true);
        $accessKeyId = $config['storage_aliyun_accesskeyid'];
        $accessKeySecret = $config['storage_aliyun_accesskeysecret'];
        $endpoint = $config['storage_aliyun_endpoint'];
        $bucket = $config['storage_aliyun_bucket'];

        $this->oss = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        $this->bucket = $bucket;
    }

    /**
     * 上传网络文件
     * @param $url
     * @return array
     */
    public function uploadFormUrl($url){

        $filePath = $this->saveFileToLocal($url);

        $object = $this->saveDir . '/' . date('Y') . '/' . date('m') . '/' . date('d') . '/' . basename($filePath);

        //上传oss
        $result = $this->aliyunUpload($object, $filePath);

        return $result;
    }

    /**
     * 将网络文件保存至本地
     * @param $url
     * @return string
     * @throws \Exception
     */
    private function saveFileToLocal($url){
        //获取网络文件
        $souce = CurlHelper::get($url);
        if (!$souce) {
            throw new \Exception('网络文件获取失败');
        }

        $localPath = Yii::$app->getRuntimePath() . '/upload/';
        if (!is_dir($localPath)) {
            mkdir($localPath, 0777, true);
        }

        $filePath = $localPath . date('Ymd') . uniqid() . $this->fileExt($url);

        file_put_contents($filePath, $souce);

        return $filePath;
    }

    /**
     * @param $filePath
     */
    private function uploadOssByLocal($filePath){

    }


    //上传文件
    public function uploadFile($fileName = '')
    {
        if ($this->allowMaxSize && $_FILES['file']['size'] > $this->allowMaxSize) {
            return array('code' => -1, 'msg' => '文件大小超過限制！');
        }
        $fileName = $fileName ? : $this->uploadFileNamed($this->fileExt($_FILES['file']['name']));
        $objectName = $this->fileSavePath() . $fileName;
        return $this->aliyunUpload($objectName, $_FILES['file']['tmp_name']);
    }

    public function putObj($content,$objectName = ''){
        $result = $this->oss->putObject($this->bucket,$objectName,$content);
        if ($result['info']['http_code'] == 200) {
            return ['code' => 0, 'object' => $objectName];
        } else {
            return ['code' => -1, 'msg' => '文件上传失败！- 3'];
        }
    }

    public function aliyunUpload($objectName, $filePath){
        try {
            $result = $this->oss->uploadFile($this->bucket, $objectName, $filePath, $this->fileOptions);

            if ($result['info']['http_code'] != 200) {
                throw new \Exception('文件上传失败');
            }

            return [
                'url' => $result['info']['url'],
                'path' => '/'.$objectName,
                'name' => basename($objectName),
                'size' => $result['info']['size_upload'],
                'type' => $result['oss-requestheaders']['Content-Type'],
            ];
        } catch (OssException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    //保存base64图片
    public function saveBase64Img($base64)
    {
        $base64Info = explode(',', $base64);
        $img = base64_decode($base64Info[1]);
        $uploadFileName = $this->uploadFileNamed('.jpeg');
        $object = $this->fileSavePath() . $uploadFileName;
        $localPath = $this->fileLocalPath() . $uploadFileName;
        file_put_contents($localPath, $img);
        $result = $this->aliyunUpload($object, $localPath);
        unlink($localPath);
        return $result;
    }

    public function saveFromBuffer(){
        $fileContent = file_get_contents('php://input');
    }

    //将本地图片上传到阿里云
    public function saveBitImg($imgData)
    {
        $savePath = $this->fileSavePath() . $this->uploadFileNamed('') . '.png';
        $localPath = $this->fileLocalPath().'temp'.rand(1,99999).'.png';

        file_put_contents($localPath, $imgData);
        //上传oss
        $result = $this->oss->uploadFile($this->bucket,$savePath,$localPath);
        if ($result['info']['http_code'] == 200) {
            unlink($localPath);
            return array('code' => 0, 'msg' => '文件上传成功！', 'object' => $savePath);
        } else {
            return array('code' => -1, 'msg' => '文件上传失敗！');
        }
    }

    //保存微信图片
    public function saveWxImg($url)
    {
        try {
            $response = (new CurlClient)->get($url)->send();

            if (!$response->content) {
                return array('code' => -1, 'msg' => '該URL圖片處理失敗！');
            }

            $ext = substr($url, -5);
            $ext = explode('=', $ext);

            if (isset($ext[1])) {
                $postfix = '.' . $ext[1];
            } else {
                $postfix = '.jpeg';
            };

            $uploadFileName = $this->uploadFileNamed($postfix);
            $object = $this->fileSavePath() . $uploadFileName;
            $localPath = $this->fileLocalPath() . $uploadFileName;

            file_put_contents($localPath, $response->content);

            //上传oss
            $this->fileOptions[OssClient::OSS_CONTENT_TYPE] = "image/jpeg";
            $result = $this->aliyunUpload($object, $localPath);
            unlink($localPath);

            return $result;

        } catch (\Exception $e) {
            Yii::error($e);
            return array('code' => -1, 'msg' => '該URL圖片處理失敗！');
        }
    }


    /**
     * 批量删除文件
     * $objects array
     */
    public function deleteFiles($objects)
    {
        $rst = $this->oss->deleteObjects($this->bucket, $objects, $options = null);
        return $rst;
    }

    //获取文件扩展名
    private function fileExt($fileName)
    {
        $fileNamedExt = explode('.', $fileName);
        return '.' . array_pop($fileNamedExt);
    }


    //文件保存完整目录
    private function fileSavePath()
    {
        return $this->saveDir . '/' . date('Y') . '/' . date('m') . '/';
    }

    private function uploadFileNamed($att)
    {
        return uniqid(). $att;
    }

    //文件本地保存完整目录
    private function fileLocalPath()
    {
        $saveLocalPath = Yii::$app->getRuntimePath() . '/upload/';

        if (!is_dir($saveLocalPath)) {
            mkdir($saveLocalPath, 0777, true);
        }
        return $saveLocalPath . date('Ymd');
    }
}