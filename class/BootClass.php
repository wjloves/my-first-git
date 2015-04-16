<?php
class BootClass{
    public static function start(){
        $_do = $_GET['do'];
        unset($_GET['do']);
        // if(isset($_GET['8f0d4c1d5f31b8340228caba1aab7486']) && !empty($_GET['8f0d4c1d5f31b8340228caba1aab7486'])){
        //     VideoInterFaceClass::FaceStart();return;
        // }
        $_conf = RouteConf::get();
        $controller = $_conf['defaultController'];
        $action = $_conf['defaultAction'];
        setcookie('action',serialize($_do));

        $_do = explode('-',$_do);
        if(!empty($_do[0]))
            $controller = ucfirst($_do[0]).$_conf['appendController'];
        if(!empty($_do[1]))
            $action = ucfirst($_do[1]).$_conf['appendAction'];
        $aController = new $controller;
        $aController->$action();
    }
}