<?php
/**
 * Created by PhpStorm.
 * User: kid
 * Date: 14-10-23
 * Time: 下午12:59
 */

error_reporting(0);
define('__VERSION__',"1.0.0");
define('__ROOT_DIR__',dirname(__FILE__));
session_start();
require_once(__ROOT_DIR__.'/common.php');
BootClass::start();