<?php
/**
 * Created by PhpStorm.
 * User: kid
 * Date: 14-10-23
 * Time: 下午12:45
 */

header("content-type:text/html;charset=utf-8");
class PercentageController extends BaseController{
    public function __construct(){
        parent::__construct();
        $this->_bonusruleModel = FrontBonusRuleModel::getInstance();
        $this->_hostsplitModel = FrontHostSplitIntoModel::getInstance();
        $this->_splitlogModel = EndSplitIntoLogModel::getInstance();
        $this->_hostratingModel = EndHostRatingModel::getInstance();
        $this->_endadminModel  = EndAdminModel::getInstance();
    }

    public function defaultAction(){
        //判断权限
        if(!in_array(21,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        $list = $this->_bonusruleModel->order(' host_level asc')->getAll();
        include_once("./tpl/percentage-list.html");
    }

    /**
     * [splitHostAction 主播分成]
     * @param Array $priv [当前用户权限]
     * @author morgan
     * date  2015/02/11
     */
    public function splitHostAction(){
        $priv = $this->_priv_arr;
        //判断权限
        if(!in_array(41,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        } 
        $data['list'] = $this->_hostsplitModel->order(' rating asc')->getAll($sql);
        foreach ($data['list'] as $key => $value) {
                //查看是否有此评级   无则不显示
                $host_rating = $this->_hostratingModel->fields(array('rating_name'))->eq(array('auid'=>$value['rating']))->getOne();
                if($host_rating){
                    $data['list'][$key]['rating_name'] = $host_rating['rating_name'];
                }else{
                     unset($data['list'][$key]);
                }
        }
        include_once("./tpl/host-split.html");
    }

    /**
     * [ajaxEditAction AJAX修改等级比例]
     * @param Int $auid                   [业务ID]
     * @param Int $contion['host_shares'] [修改的比例]
     * @param Int $type                   [类型  1： 主播  2：家族长]
     * @author morgan
     * date   2014/11/07
     * update 2015/02/11
     */
    public function ajaxEditAction(){
    	$auid = intval($_GET['id']);
        $type = intval($_GET['type']);
        $rating_name = addslashes($_GET['rating']);
        if($type == 1){
            $rating = $this->_hostsplitModel->eq(array('auid'=>$auid))->getOne();
            $model = $this->_hostsplitModel;
        }
    	$contion['shares'] = intval($_GET['shares']);
        $contion['salary'] = intval($_GET['salary']);
    	$status = $model->update($contion,' auid = '.$auid);
        //修改成功  记录详细操作记录
    	if($status){
            $data['before_salary'] = $rating['salary'];
            $data['rating_name']   = $rating_name;
            $data['before_shares'] = $rating['shares'];
            $data['after_salary']  = $contion['salary'];
            $data['after_shares']  = $contion['shares'];
            $data['editor']        = $this->_login['admin_id'];
            $data['created']       = date("Y-m-d H:i:s",time()); 
            $data['type']          = $type;
            $this->_splitlogModel->insert($data);
        }
        echo  $status;
    }

    /**
     * [ajaxAddAction AJAX新增等级比例]
     * @param Int $contion['host_level']  [新增等级]
     * @param Int $contion['host_shares'] [新增比例]
     * @author morgan
     * date   2014/12/11
     */
    public function ajaxAddAction(){
        $data['rating_name']  =  addslashes($_POST['rating']);
        $contion['salary']    = intval($_POST['salary']);
        $contion['shares']    = intval($_POST['shares']);
        $is_had = $this->_hostratingModel->eq(array('rating_name'=>$data['rating_name']))->getOne();
        if($is_had){
           echo json_encode("已有此评级");return;
        }
        $data['creator'] = $this->_login['admin_id'];
        $data['created'] = date('Y-m-d H:i:s',time());
        $status = $this->_hostratingModel->insert($data);
        
        if($status){
           $contion['rating']  =  $this->_hostratingModel->insert_id();
           $status = $this->_hostsplitModel->insert($contion);
           if($status){
                echo json_encode("添加成功");
           }else{
                $this->_hostratingModel->delete(" auid =  ".$contion['rating']);
                echo json_encode("添加失败");
           }
           
        }else{
           echo json_encode("添加失败");
        }
    }

    /**
     * [ajaxSplitLogAction ajax回显操作日志]
     * @return [type] [description]
     */
    public function  ajaxSplitLogAction(){
        $type = intval($_POST['type']);
        $id   = addslashes($_POST['id']);
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $page_size = 4;
        $offset = $page_size*($page-1);
        $list  = $this->_splitlogModel->eq(array('rating_name'=>$id,"type"=>$type))->order('created desc')->limit($offset,$page_size)->getAll();
        foreach ($list as $key => $value) {
            $admin = $this->_endadminModel->fields(array('name'))->eq(array('admin_id'=>$value['editor']))->getOne();
            if($admin){
                $list[$key]['admin_user'] = $admin['name'];
            }
        }
        $count = $this->_splitlogModel->fields(array('count(auid) as nums'))->eq(array('rating_name'=>$id,"type"=>$type))->getOne();
        $nums = $count['nums'];
        $pages = $this->global->ajax_pages($page_size,$nums,5,$page,$type);
        $data['pages'] = $pages;
        $data['num'] = count($list);
        $data['list'] = $list;
        echo json_encode($data);
    }
} 