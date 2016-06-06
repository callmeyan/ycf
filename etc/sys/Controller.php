<?php
/**
 * Created by PhpStorm.
 * User: yancheng (cheng@love.xiaoyan.me)
 * Date: 14-9-16
 * Time: 下午3:58
 */

abstract class Controller{
    private $twig;
    private $authLib;

    public abstract function init();

    protected function getFunction($name){
        if(!method_exists($this,$name)){
            //throw new AppException('not found method '.$name);
            return null;
        }
        $method = new ReflectionMethod($this,$name);
        return $method;
    }


    public function getAuth(){
        if(!class_exists('Auth')){
            include LIB_DIR.'/libs/Auth.php';
        }
        if($this->authLib != null){
            return $this->authLib;
        }
        $lib = new Auth();
        $this->authLib = $lib;
        return $this->authLib;
    }
    public function __setTwig($twig){
        $this->twig = $twig;
    }

    protected function getInput(){
        return YCLoader::init()->getInput();
    }
    protected function input(){
        return YCLoader::init()->getInput();
    }
    protected function assign($key,$value = null){
        if(is_array($key)){
            foreach($key as $k => $v){
                $this->twig->assign($k,$v);
            }
        }else{
            $this->twig->assign($key,$value);
        }
    }
    /**
     * 检查权限
     * @param name string|array  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
     * @param uid  int           认证用户的id
     * @param relation string
     *      如果为 'or' 表示满足任一条规则即通过验证;
     *      如果为 'and'则表示需满足所有规则才能通过验证
     * @param string mode        执行check的模式
     * @return boolean           通过验证返回true;失败返回false
     */
    protected function verifyPermission($name, $uid, $relation = 'or', $mode = 'url', $type = 1){
        return $this->getAuth()->check($name,$uid,$type,$mode,$relation);
    }

    /**
     * @param $url
     * @param callback $function <p>
     * The function to be called. Class methods may also be invoked
     * statically using this function by passing
     * array($classname, $methodname) to this parameter.
     * Additionally class methods of an object instance may be called by passing
     * array($objectinstance, $methodname) to this parameter.
     * </p>
     */
    protected function addRoute($url,$function){
        $obj = $function;
        if(is_string($function)){
            $obj = $this->getFunction($function);
        }
        RouterCore::getRouter()->addRoute($url, $obj);
    }
    protected function render($templateFile,$vars = array()){
        if($this->twig == null){
            throw new TemplateException('Template engine not initialization');
        }
        global $start_time;
        $endTime = microtime(true);
        $mem = sprintf('%.2f M',memory_get_usage() / 1024 / 1024);
        $this->assign('RunTimes',array(
            'time'=> round($endTime - $start_time,3),
            'sql' => round(DB()->getRunTime(),3),
            'mem' => $mem
        ));
        $this->assign('SITE_URL',URL());
        if(strpos($templateFile,'.') != -1) $templateFile = str_replace('.','/',$templateFile);

        try{$this->twig->render($templateFile,$vars);}catch (Exception $e){
            throw $e;
        }
    }

    protected function getConfig($key){
        return new Config($key);
    }

    private $startTime;
    protected function spentStart(){
        $this->startTime = microtime(true);
    }

    protected function calcSpent()
    {
        printf(" total run: %.2f s" .
                "memory usage: %.2f M<br> ",
            microtime(true) - $this->startTime,
            memory_get_usage() / 1024 / 1024);
    }

    protected function cacheExists($key){
        return Cache::getInstance()->exists($key);
    }

    protected function getCache($key){
        return Cache::getInstance()->get($key);
    }

    protected function setCache($key,$v){
        return Cache::getInstance()->set($key,$v);
    }
}