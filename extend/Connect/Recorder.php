<?php
/* PHP SDK
 * @version 2.0.0
 * @author connect@qq.com
 * @copyright © 2013, Tencent Corporation. All rights reserved.
 */
namespace Connect;
require_once(dirname(__FILE__)."/ErrorCase.php");
class Recorder{
    private static $data;
    private $inc;
    private $error;

    public function __construct(){
        $this->error = new ErrorCase();
        $this->inc = new \stdClass();
        $this->inc->appid = '101849408';
        $this->inc->appkey = 'd877709345b83271547ec9a161c32266';
        $this->inc->callback = url('user/qq_callback')->domain('www');
        $this->inc->callback = Request()->domain()."/user/qq_callback.html";
        $this->inc->scope = 'get_user_info';
        $this->inc->errorReport = true;
        $this->inc->storageType = 'file';
        if(empty($this->inc)){
            $this->error->showError("20001");
        }

        if(empty($_SESSION['QC_userData'])){
            self::$data = array();
        }else{
            self::$data = $_SESSION['QC_userData'];
        }
    }

    public function write($name,$value){
        self::$data[$name] = $value;
    }

    public function read($name){
        if(empty(self::$data[$name])){
            return null;
        }else{
            return self::$data[$name];
        }
    }

    public function readInc($name){
        if(empty($this->inc->$name)){
            return null;
        }else{
            return $this->inc->$name;
        }
    }

    public function delete($name){
        unset(self::$data[$name]);
    }

    function __destruct(){
        $_SESSION['QC_userData'] = self::$data;
    }
}
