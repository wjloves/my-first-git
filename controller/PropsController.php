<?php
/**
 * Created by PhpStorm.
 * User: morgan
 * Date: 14-10-30
 * Time: 下午13:22
 * 道具销售
 */
header("content-type:text/html;charset=utf-8");
class PropsController extends BaseController{
    public function __construct(){
        parent::__construct();
        $this->_propsModel = EndPropsModel::getInstance();   
        $this->_goodsModel = FrontGoodsModel::getInstance();     
        $this->_frontconsumeModel  = FrontConsumeModel::getInstance();
        $this->_userModel     = FrontUserModel::getInstance();
        $this->_propslogsModel     = EndPropsLogsModel::getInstance();
        $this->_endadminModel  = EndAdminModel::getInstance();
    }
    /**
     * [defaultAction 道具销售]
     * @author [morgan] 
     * @date(2014/10/31)
     */
    public function defaultAction(){
        //判断权限
        if(!in_array(11,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        /*搜索条件*/
        if(empty($_GET['search']['order'])){
            $_GET['search']['order'] = 'ctime';
        }
        $data = MemberController::lastTime(); 
        Search::getCondition($this->_propsModel);
        /*搜索关键字*/
        $this->keyword();
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $offset = $page_size*($page-1);
        $list= $this->_propsModel->limit($offset,$page_size)->getAll();

        /*搜索条件*/
        
        Search::getCondition($this->_propsModel);
        /*搜索关键字*/
        $this->keyword();
        $count  = $this->_propsModel->fields(array('count(props_id) as nums'))->getOne();
        
        $nums   = $count['nums'];   
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);

        foreach($list as  $key=>$val){
                $result = $this->_goodsModel->eq(array('gid'=>$val['props_id']))->getOne();
                if(!empty($result) and !empty($result['name'])){
                        $list[$key]['name'] = $result['name'];
                }   
        }
        $data['list']  = $list;
        include_once("./tpl/props-stat.html");
    }

    /**
     * [search 搜索]
     * @param  [type] $obj [表对象]
     * @author  morgan
     * @date(2014/11/07)
     */
     protected function  keyword(){
        if(!empty($_GET['keyword'])){ 
            $keyword = addslashes($_GET['keyword']);
            $gid = $this->_goodsModel->fields(array('gid'))->like(array('gid'=>$keyword,'name'=>$keyword))->getAll();
            $gidArr = array();
            foreach ($gid as $key => $value) {       
                    $gidArr[] = $value['gid']; 
            }
            if(!empty($gidArr)){
                $this->_propsModel->in(array('props_id'=>$gidArr));
            }
        }
        return $this->_propsModel;
    }

    /**
     * [logAction 道具销售日志]
     * @author morgan 
     */
    public function logAction(){
        //判断权限
        if(!in_array(40,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        /*搜索条件*/
        if(empty($_GET['search']['order'])){
            $_GET['search']['order'] = 'created';
        }
        if($_GET['search']['order'] == 'allmoney'){
            $_GET['search']['order'] = 'gnum desc,points';
            $data['order'] = 'allmoney';
        }
        Search::getCondition($this->_frontconsumeModel);
        
        /*搜索关键字*/
        $this->searchLog();

        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $offset = $page_size*($page-1);
        $list= $this->_frontconsumeModel->limit($offset,$page_size)->getAll();

        
        /*搜索条件*/
        if($_GET['search']['order'] == 'allmoney'){
            $_GET['search']['order'] = 'gnum desc,points';
        }
        Search::getCondition($this->_frontconsumeModel);

        /*搜索关键字*/
        $this->searchLog();
        $count  = $this->_frontconsumeModel->fields(array('count(id) as nums'))->getOne();
        $nums   = $count['nums'];   
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
        $data['page_nums'] = ceil($nums/$page_size);
        /*****查询用户信息和道具信息****/
        foreach($list as  $key=>$val){
                //用户信息
                $user  = $this->_userModel->fields(array('username','nickname'))->eq(array('uid'=>$val['send_uid']))->getOne();
                if($user){
                     $list[$key] = array_merge($list[$key],$user);
                }
                //道具信息
                $goods = $this->_goodsModel->fields(array('gid','name','category_name'))->eq(array('gid'=>$val['gid']))->getOne();
                if($goods){
                    $list[$key] = array_merge($list[$key],$goods);
                }
        }
        $data['list']  = $list;
        //查询道具分类
        $goodsType = $this->_goodsModel->fields(array('category_name'))->group('category_name')->getAll();
        foreach ($goodsType as $key => $value) {
            $data['goodsType'][] = $value['category_name'];
        }
        include_once("./tpl/props-log.html");
    }

    /**
     * [searchLog 道具销售日志查询]
     * @return [type] [description]
     */
    public function  searchLog(){
        if(!empty($_GET['props'])){ 
            $props = addslashes($_GET['props']);
            $gid = $this->_goodsModel->fields(array('gid'))->like(array('gid'=>$props,'name'=>$props))->getAll();
            foreach ($gid as $key => $value) {
                    $propsid_arr[] = $value['gid'];
            }
            $this->_frontconsumeModel->in(array('gid'=>$propsid_arr));
        }

        if(!empty($_GET['keyword'])){
            $keyword = addslashes($_GET['keyword']);
            $user = $this->_userModel->fields(array('uid'))->like(array('uid'=>$keyword,'username'=>$keyword,'nickname'=>$keyword))->getAll();
            foreach ($user as $k => $v) {
                    $uid_arr[] = $v['uid'];
            }
            $this->_frontconsumeModel->in(array('send_uid'=>$uid_arr));
        }

        if(!empty($_GET['goodsType'])){
            $goodsType = addslashes($_GET['goodsType']);
            $goodsResult = $this->_goodsModel->fields(array('gid'))->eq(array('category_name'=>$goodsType))->getAll();
            foreach ($goodsResult as $g => $good) {
                    $gid_arr[] = $good['gid'];
            }
            $this->_frontconsumeModel->in(array('gid'=>$gid_arr));
        }
        return  $this->_frontconsumeModel;
    }

    /**
     * [listAction 道具时间汇总统计]
     * @author morgan 
     */
    public function listAction(){
        if(!isset($_GET['search']['ctime'])){
            $_GET['search']['ctime']['ge'] = date("Y-m-d",strtotime("-1 day")).' 00:00:00';
            $_GET['search']['ctime']['le'] = date("Y-m-d",strtotime("-1 day")).' 23:59:59';
        }
        $data = MemberController::lastTime(); 
        Search::getCondition($this->_propsModel);
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $list = $this->_propsModel->getAll();

        foreach ($list as $key => $value) {
            $result = $this->_goodsModel->eq(array('gid'=>$value['props_id']))->getOne();
            if(!empty($result) and !empty($result['name'])){
                    $NewArr[$value['props_id']]['name'] = $result['name'];
            }
            $NewArr[$value['props_id']]['props_id']     =  $value['props_id'];
            $NewArr[$value['props_id']]['props_type']   =  $value['props_type'];
            if(isset($NewArr[$value['props_id']])){
                $NewArr[$value['props_id']]['sold_num']     +=  $value['sold_num'];
                $NewArr[$value['props_id']]['sold_amount']  +=  $value['sold_amount'];
                $NewArr[$value['props_id']]['props_price']  +=  $value['props_price'];
            }
            $i++;
        }   

        //数据分页
        $chunk_arr = array_chunk($NewArr,$page_size);
        $result    = $chunk_arr[($page-1)];
        $nums   = count($NewArr);   
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
        $search_time = $data['ge'].' --- '.$data['le'];
      
        include_once("./tpl/props-list.html");
    }

    /**
     * [managementAction 道具管理]
     * @param [type] [varname] [description]
     * @author morgan
     * date 2015/03/13
     */
    public function managementAction(){
        //判断权限
        if(!in_array(43,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                
        }
        if(isset($_GET['keyword']) && !empty($_GET['keyword'])){
                $keyword = addslashes($_GET['keyword']);
                $this->filtration($keyword); 
        }
        Search::getCondition($this->_propslogsModel);
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $offset = $page_size*($page-1);
        $list= $this->_propslogsModel->eq(array("status"=>1))->limit($offset,$page_size)->getAll();
        foreach ($list as $key => $value) {
            //用户信息
            $user  = $this->_userModel->fields(array('username','nickname'))->eq(array('uid'=>$value['uid']))->getOne();
            if($user){
                 $list[$key] = array_merge($list[$key],$user);
            }
            //道具信息
            $goods = $this->_goodsModel->fields(array('name'))->eq(array('gid'=>$value['gid']))->getOne();
            if($goods){
                $list[$key] = array_merge($list[$key],$goods);
            }
            $list[$key]['admin'] = $this->_endadminModel->fields(array('name'))->eq(array('status'=>0,'admin_id'=>$value['admin_id']))->getOne();
        }
        if(isset($_GET['keyword']) && !empty($_GET['keyword'])){
                $keyword = addslashes($_GET['keyword']);
                $this->filtration($keyword); 
        }
        Search::getCondition($this->_propslogsModel);
        /*搜索关键字*/
        $this->searchLog();
        $count  = $this->_propslogsModel->fields(array('count(id) as nums'))->eq(array("status"=>1))->getOne();
        $nums   = $count['nums'];   
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
        $data['page_nums'] = ceil($nums/$page_size);
        include_once("./tpl/props-management.html"); 
    }

    /**
     * [filtration 道具管理过滤搜索]
     * @param  string $keyword [关键字]
     * @return Object          [对象信息]
     */
    public function  filtration($keyword = ''){
        if($keyword){
            $users_id = $this->_endadminModel->fields(array('admin_id'))->eq(array('status'=>0))->like(array('name'=>$keyword))->getAll();
            $uids = array();
            if($users_id){
                foreach ($users_id  as  $v) {
                    $uids[] = $v['admin_id'];
                }
                $this->_propslogsModel->in(array('admin_id'=>$uids));
            }else{
                $this->_propslogsModel->eq(array('admin_id'=>md5('none')));
            }

            return $this->_propslogsModel;
        }
    }

    /**
     * [ajaxGetOneAction 查看道具和用户是否存在]
     * @param [type] [varname] [description]
     * @author morgan
     * @return INT | JSON [数字标示符或JSON格式字符串]
     * date 2015/03/13
     */
    public function  ajaxGetOneAction(){
        $user = addslashes($_GET['id']);
        $gid  = intval($_GET['gid']);
        $usermsg = $this->_userModel->getOne("SELECT uid,username,nickname,lv_rich FROM `video_user` where uid='".$user."' OR username='".$user."' OR nickname = '".$user."'");
        if(!$usermsg){
            echo 2;return;
        }
        if($gid){
            $gidmsg  = $this->_goodsModel->eq(array('gid'=>$gid))->getOne(); 
            if(!$gidmsg){
                echo 3;return;
            }
        }
        echo json_encode($usermsg);
    }

    /**
     * [addUsersPropsAction 增加用户道具]
     * @param Array $data [操作信息]
     * @author morgan
     * date 2015/03/13
     */
    public  function  addUsersPropsAction(){
        $data = $_POST['data'];
        $data['content'] = addslashes($data['content']);
        $data['created'] = date('Y-m-d H:i:s',time());
        $status  = $this->global->vedioInterface(array('uid'=>$data['uid'],'gid'=>$data['gid'],'num'=>$data['num']),$this->config['goods_url']);
        if($status && ($status['ret']==1)){
            $data['status'] = $status['ret'];
        }else{
            $data['status'] = 2;
        }
        //记录增加用户道具操作日志
        $state = $this->_propslogsModel->insert($data);
        if($state && ($status['ret']==1)){
            echo '<script>alert("操作成功");location.href="./index.php?do=props-management";</script>';
        }else{
            echo '<script>alert("操作失败");location.href="./index.php?do=props-management";</script>';
        }
    }
} 