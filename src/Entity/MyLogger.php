<?php
/**
 * Created by IntelliJ IDEA.
 * User: GOOGLE
 * Date: 27/08/2020
 * Time: 03:33
 */

namespace App\Entity;


class MyLogger
{

    protected $sender;

    public function __construct(){
       // $this->sender = $smsApi;
    }

    public static function writeLog($txt){
        //Save string to log, use FILE_APPEND to append.
        file_put_contents(__DIR__."/../../var/log/log_".date("j.n.Y").'.log', $txt, FILE_APPEND);
        file_put_contents(__DIR__."/../../var/log/log_".date("j.n.Y").'.log', "/n", FILE_APPEND);

    }

    public function adminLog($txt){
        $this->writeLog($txt);
        //$this->sender->sendAdminMsg($txt);
    }
}