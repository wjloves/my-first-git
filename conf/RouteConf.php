<?php
/**
 * Created by PhpStorm.
 * User: ws
 * Date: 14-10-23
 * Time: 下午1:12
 */

class RouteConf{
    public static function get(){
        return array(
            'appendController'=>'Controller',
            'appendAction'=>'Action',
            'defaultController'=>'DefaultController',
            'defaultAction'=>'defaultAction'
        );
    }
}