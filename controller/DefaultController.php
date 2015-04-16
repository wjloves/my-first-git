<?php
/**
 * Created by PhpStorm.
 * User: kid
 * Date: 14-10-23
 * Time: 下午12:45
 */


class DefaultController extends BaseController{
    public function defaultAction(){
        header("Location:index.php?do=index-index");
    }

    public function doAction(){
        echo "this is do action from index controller<br />";
    }

    public function modelAction(){
        $model = new TestModel();
        echo 'id:',$model->_getAttr('id'),"<br />";
    }
} 