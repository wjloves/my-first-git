<?php
/**
 * Created by PhpStorm.
 * User: ws
 * Date: 14-11-11
 * Time: 上午10:21
 */

class Search {
    /**
     * [searchContion 公用搜索日期（垃圾版本，后期KID会做优化）]
     * @param  [type] $condition [数据表-对象类型]
     * @param  string $type    [前后台库：afert-后台，before-前台]
     * @return [type]          [对象,搜索参数]
     */
    public static function getCondition($model){
        if(!empty($_GET['search']['order'])){
            $model = $model->order($_GET['search']['order'].' desc');
        }

        if(isset($_GET['search'])){
            foreach($_GET['search'] as $key=>$val){
                if(empty($val)||empty($key))
                    continue;
                if(is_array($val)){
                    foreach($val as $k1=>$v1){
                        if(empty($k1)||$v1==='')
                            continue;
                        call_user_func_array(array($model, strtolower($k1)), array(array($key=>$v1)));
                    }
                }
            }
        }

        
        return $model;
        /*
    	$conditions = $condition->order($order);
        if(!empty($_GET['start_time'])){
            $conditions =   $condition->GT(array($time=>addslashes($_GET['start_time'])));
        }
        if(!empty($_GET['end_time'])){
            $conditions =   $condition->LT(array($time=>addslashes($_GET['end_time'])));
        }
        return $conditions;
        */
    }
} 