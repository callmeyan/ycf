<?php

/**
 * Created by PhpStorm.
 * User: yancheng (cheng@love.xiaoyan.me)
 * Date: 14-9-18
 * Time: 上午11:22
 */
abstract class TemplateDriver
{
    public function __construct($config, $debug = RunMode::PRODUCT)
    {
        $this->init($config,$debug);
    }

    public abstract function init($config, $debug = RunMode::PRODUCT);

    public abstract function assign($key, $value);

    public abstract function render($file, $vars = array());
}

class TemplateCore
{
    private $tpl = null;

    public function initEnv($tplName, $config, $debug = RunMode::PRODUCT)
    {
        Logger::getLogger()->sys('init template driver');

        if (!is_file(LIB_DIR . '/libs/' . $tplName . '/' . $tplName . '.php')) {
            die('template driver not found');
        }
        include LIB_DIR . '/libs/' . $tplName . '/' . $tplName . '.php';
        if (!class_exists($tplName)) {die('template class name error'); }
        $cls = new ReflectionClass($tplName);
        if(!$cls->isSubclassOf('TemplateDriver')) die('template driver not extends TemplateDriver');
        $this->tpl = $cls->newInstanceArgs(array($config, $debug == RunMode::DEV));
        return $this->tpl;
    }

    private $vars = array();

    public function assign($key, $value)
    {
        return $this->tpl->assign($key, $value);
    }

    public function render($file, $vars = array())
    {
        return $this->tpl->render($file, $vars);
    }

}