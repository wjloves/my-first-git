<?php
/**
 * Created by PhpStorm.
 * User: kid
 * Date: 14-10-23
 * Time: 下午12:45
 */

header("content-type:text/html;charset=utf-8");
class MemberController extends BaseController{
	public function __construct(){
        parent::__construct();
        $this->_memberModel = EndMemberModel::getInstance(); 
        
    }
    public function defaultAction(){
        //判断权限
        if(!in_array(9,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        
        //查询搜索条件  （未封装）
        if(empty($_GET['search']['order'])){
            $_GET['search']['order'] = 'ctime';
        }
        $data = $this->lastTime();

        Search::getCondition($this->_memberModel);
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$page_size = 20;
		$offset = $page_size*($page-1);
        $data['list']  = $this->_memberModel->limit($offset,$page_size)->getAll();
        foreach ($data['list'] as $key => $value) {
                 $data['list'][$key]['avg_time'] = $this->global->sec2time($value['avg_time']); 
        }
        //查询搜索条件 
        $this->lastTime($data);
        Search::getCondition($this->_memberModel);
        $count  = $this->_memberModel->fields(array('count(auid) as nums'))->getOne();
        $nums   = $count['nums'];
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);

        include_once("./tpl/member-stat.html");
    }

    public function listAction(){
        $data['order'] = $_GET['search']['order'];
        Search::getCondition($this->_memberModel);
        $list = $this->_memberModel->getAll();
        $i = 0;
        foreach ($list as $key => $value) {
                $data['registed_users'] += $value['registed_users'];
                $data['login_users'] += $value['login_users'];
                $data['users_in_room'] += $value['users_in_room'];
                $data['num_in_room'] += $value['num_in_room'];
                $data['apex_num'] += $value['apex_num'];
                $data['apex_time'] += $value['apex_time'];
                $data['avg_time'] += $value['avg_time'];
                $i++;
        }
        $data['avg_time'] = round(($data['avg_time']/$i),2);
        $data['search_time'] = $_GET['search']['ctime']['ge'].' --- '.$_GET['search']['ctime']['le'];
      
        include_once("./tpl/member-list.html");
    }

    public  static  function lastTime($data = array()){
        if( !empty($_GET['search']['ctime']['ge'])){
            if(isset($data['ge']) && !empty($data['ge'])){
                $_GET['search']['ctime']['ge'] = $data['ge'];;
            }
            $data['ge'] = $_GET['search']['ctime']['ge'];
            $_GET['search']['ctime']['ge'] = date('Y-m-d H:i:s',strtotime("+1 day",strtotime($_GET['search']['ctime']['ge'])));    
        }
        if( !empty($_GET['search']['ctime']['le']) ){
            if(isset($data['le'])  && !empty($data['le']) ){
                $_GET['search']['ctime']['le'] = $data['le'];
            }
            $data['le'] = $_GET['search']['ctime']['le'];
            $_GET['search']['ctime']['le'] = date('Y-m-d H:i:s',strtotime("+1 day",strtotime($_GET['search']['ctime']['le'])));    
        }
        return $data;
    }
} 