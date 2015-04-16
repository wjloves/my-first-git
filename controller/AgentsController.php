<?php
/**
 * Created by PhpStorm.
 * User: raby
 * Date: 15-04-06
 * Time: 下午12:45
 * 域名管理
 */

header("content-type:text/html;charset=utf-8");

class AgentsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->_domainModel = FrontDomainModel::getInstance();
        $this->_redirectModel = FrontRedirectModel::getInstance();
    }

    /**
     * [defaultAction 新增域名]
     * @author [raby]
     * @date(2015/04/06)
     */
    public function defaultAction()
    {
        //判断权限
        if(!in_array(50,$this->_priv_arr)){
            echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
            return;
        }
        include_once("./tpl/agents-add.html");
    }
} 