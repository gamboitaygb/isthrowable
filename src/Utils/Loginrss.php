<?php
/**
 * Created by PhpStorm.
 * User: yus
 * Date: 15/09/18
 * Time: 8:31
 */

namespace App\Utils;
use Aws\S3\S3Client;
use DirkGroenen\Pinterest\Pinterest;

class Loginrss
{
    private $amazon;
    private $printerest;



    public function __construct($name_sdk=null)
    {
        switch ($name_sdk){
            case 'amazon':
                $this->amazon=$this->amazonClient();
                break;
            case 'printerest':
                $this->printerest=$this->printerestClient();
                break;
        }
    }

    /**
     * @return S3Client
     */
    public function getAmazon(): S3Client
    {
        return $this->amazon;
    }


    public function amazonClient()
    {
        return new S3Client([
            'version' => 'latest',
            'region' => 'eu-central-1',
            'credentials' => [
                'key'    => 'AKIAJJDZ5EBIP3JHFRTA',
                'secret' => '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'
            ]
        ]);

    }

    public function uploadPicAmz($file_path)
    {
        $bucketName = 'isthrowable';
        $file_Path = $file_path;
        $key = basename($file_Path);

        // Upload a publicly accessible file. The file size and type are determined by the SDK.
        try {
            $result = $this->amazon->putObject([
                'Bucket' => $bucketName,
                'Key'    => $key,
                'Body'   => fopen($file_Path, 'r'),
                'ACL'    => 'public-read',
            ]);
            return $result->get('ObjectURL');
        } catch (Aws\S3\Exception\S3Exception $e) {
            echo "There was an error uploading the file.\n";
            echo $e->getMessage();
        }
    }

    /**
     * @return Pinterest
     */
    public function getPrinterest()
    {
        return $this->printerest;
    }



    public function printerestClient(){
        return new Pinterest('4993970691188484282', 'a0978b906a83b1be730e202d1202dcdd80a2794fbd2606baa4274ceef03c364f');
    }

    public function UrlPrinterestLogin()
    {
        return $this->printerestClient()->auth->getLoginUrl('https://isthrowable.com/es/p-callback', array('read_public'));
    }


    public static function objRss($name_sdk)
    {

        $obj=null;
        switch ($name_sdk){
            case 'amazon':
                $obj=new S3Client([
                    'version' => 'latest',
                    'region' => 'eu-central-1',
                    'credentials' => [
                        'key'    => 'AKIAJJDZ5EBIP3JHFRTA',
                        'secret' => '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'
                    ]
                ]);
                break;
            case 'printerest':
                $obj = new Pinterest('4993970691188484282', 'a0978b906a83b1be730e202d1202dcdd80a2794fbd2606baa4274ceef03c364f');
                break;
        }

        return $obj;
    }
}