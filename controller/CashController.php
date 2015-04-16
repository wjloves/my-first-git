<?php
/**
 * Created by PhpStorm.
 * User: morgan
 * Date: 15-4-10
 * Time: 上午12:45
 */

header("content-type:text/html;charset=utf-8");
class CashController extends BaseController{
    public function __construct(){
        parent::__construct();
        $this->E_admin = EndAdminModel::getInstance();
        $this->F_drawal = FrontdrawalModel::getInstance();
        $this->E_cash   = EndCashModel::getInstance();
    }
    /**
     * [defaultAction 提现申请]
     * @author morgan 
     * date 2015/4/10
     */
    public function defaultAction(){
          //判断权限
          
          if(!in_array(16,$this->_priv_arr)){
                  echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                  return;   
          }
          $page   = isset($_GET['page']) ? intval($_GET['page']) : 1;
          $page_size = 20;
          $offset = $page_size*($page-1);
          $data['keyword']  = addslashes($_GET['keyword']);
          /*搜索条件*/
          
          Search::getCondition($this->E_cash);
          /*搜索关键字*/
          $this->keyword($data['keyword'],$this->E_cash);
          $list  = $this->E_cash->limit($offset,$page_size)->getAll();
          /*搜索条件*/
          Search::getCondition($this->E_cash);
          /*搜索关键字*/
          $this->keyword($data['keyword'],$this->E_cash);
          $count  = $this->E_cash->fields(array('count(id) as nums'))->getOne();
          $nums   = $count['nums'];
          $data['list']  = $list;
          
         // $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
        
          
          include_once("./tpl/cash-list.html");
    }

    /**
     * [keyword 搜索]
     * @return  object
     * @author  morgan
     * date   2014/04/10
     */
    public function keyword($keyword,$model){
        if($keyword){
               $model->eq(array('uid'=>$keyword));
        }
        return $model;
    }

} 