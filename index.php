<?php
/**
 * Created by JetBrains PhpStorm.
 * User: xiaoyanore
 * Date: 2015-5-4
 * Time: 下午1:47
 * To change this template use File | Settings | File Templates.
 */
require 'etc/YCF.php';

Logger::setPriority(Logger::$LEVEL_SYS);

YCF::Loader()->loadUserLib(array(
    './usr/lib/Env.php',
    './usr/lib/BaseController.php'
));

RouterCore::getInstance()
    ->setRunMode(RunMode::DEV) // must be first
    ->init('./usr/var/conf.php')
    ->dispatch();