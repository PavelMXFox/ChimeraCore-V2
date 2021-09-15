<?php
namespace fox;
require_once 'coreAPI.php';
use Exception;


class fileConverter {
    public static function convert($src, $dst=null, $type, $url=null) {
        if (empty($url)) {
            $url=config::get("converterURL");
            if (empty($url)) {
                throw new \Exception("Converter URL can't be empty");
            }
        }
        
        if (!file_exists($src) || is_dir($src)) {
            throw new \Exception("Invalid source file $src");
        }

        $boundary = '------------chimera---' . substr(md5(rand(0, 32000).'---fox-----'), 0, 10);

        
        // "засечка" :P
        $boundary = md5(rand(0,32000));
        
        // контент для отправки
        $content = '';
        $postData=["format"=>$type];
        $files=["userfile"=>$src];
        
        // данные для отправки
        foreach($postData as $key => $val) {
            $content .= '--' . $boundary . "\n";
            $content .= 'Content-Disposition: form-data; name="' . $key . '"' . "\n\n" . $val . "\n";
        }
        
        // файлы для отправки
        foreach($files as $key => $file) {
            $content .= '--' . $boundary . "\n";
            $content .= 'Content-Disposition: form-data; name="' . $key . '"; filename="' . basename($file) . '"' . "\n";
            $content .= 'Content-Type: ' . "text/xml" . "\n";
            $content .= 'Content-Transfer-Encoding: binary' . "\n\n";
            $content .= file_get_contents($file) . "\n";
        }
        
        // завершаем контент
        $content .= "--$boundary--\n";
        
        $params = array
        (
            'http' => array
            (
                'method' => 'POST',
                'content' => $content,
                'header' => array
                (
                    'Content-Type: multipart/form-data; boundary=' . $boundary
                )
            )
        );
        
        $context = stream_context_create($params);
        
        if ($remote = fopen($url, 'rb', false, $context)) {
            $response = @stream_get_contents($remote);
        } else {
            throw new \Exception("Converter failed!");
        }
           
        $res= json_decode($response);
        if ($res->status == 'OK') {
            $raw = file_get_contents($url.$res->result);
            if (empty($dst)) { return $raw; } else {
                file_put_contents($dst, $raw);
                return true;
            }
        } else {
            throw new \ErrorException("Converter failed: ".$res->message);
        }
    }
}