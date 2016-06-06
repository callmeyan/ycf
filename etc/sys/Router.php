<?php
/**
 * Created by PhpStorm.
 * User: yancheng (cheng@love.xiaoyan.me)
 * Date: 14-9-16
 * Time: 下午3:53
 */
class Router extends YC{
    private $routeList = array();
    private $routerRecordList = array();
    private $currentFile = null;
    private $routerListFile = 'routers.data.php';

    public function getRouteList($controllerModulePath = null){
        $this->initRouters($controllerModulePath);
        return $this->routeList;
    }

    public function afterAddRoute($url){
        $this->routerRecordList[$url] = $this->currentFile;
    }

    private function initRouters($controllerModulePath){
        Logger::getLogger()->sys('init routers on @'.$controllerModulePath);
        $controllerModulePath = (defined('APP_PATH')?APP_PATH:APP_DIR).$controllerModulePath;
        if(!@file_exists($controllerModulePath)){
            throw new Exception("Controller modules not exist");
        }
        $files = getDirFiles($controllerModulePath,".php$");
        foreach ($files as $file) {
            $this->currentFile = $controllerModulePath.'/'.$file;
            Logger::getLogger()->sys('init controller :'.$controllerModulePath.'/'.$file);
            $this->initControl();
        }
        $this->saveRouter();
    }

    private function initControl(){
        Logger::getLogger('Router')->debug($this->currentFile);
        if(file_exists($this->currentFile)){
            $arr = include($this->currentFile);
            $name = getFileNameWithOutSuffix($this->currentFile);
            $cls = new ReflectionClass($name);
            $obj = $cls->newInstance();
            $method = new ReflectionMethod($obj,'init');
            $method->invoke($obj);
        }
    }

    private function saveRouter(){
        $content = "<?php\n\t"."return array(\n";
        $urls = array();
        foreach($this->routerRecordList as $url => $file){

            $urls[] ="\t\t'$url'=>'$file'";
        }
        $content .= implode(",\n",$urls)."\n\t);";
        file_put_contents($this->routerListFile,$content);
    }
    public function addRoute($url,$func){
        $this->routeList[$url] = $func;
        $this->afterAddRoute($url);
    }
    public function initRouter(){
        if (file_exists($this->routerListFile)) {
            $this->routeList = include($this->routerListFile);
            return $this->routeList;
        }
        return false;
    }
    public function getRouteFunction($url){
        if(isset($this->routeList[$url])){
            $this->currentFile = $this->routeList[$url];
            $this->initControl();
            return $this->routeList[$url];
        }
        return null;
    }
}