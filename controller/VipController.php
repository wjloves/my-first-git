<?php
/**
 * Created by Sublime.
 * User: Morgan
 * Date: 14-11-03
 * Time: 下午18:11
 */

header("content-type:text/html;charset=utf-8");
class VipController extends BaseController{

	public function __construct(){
        parent::__construct();
        $this->_frontuserModel = FrontUserModel::getInstance();
        $this->_frontrechargeModel = FrontRechargeModel::getInstance();
        $this->_frontexitroomModel = FrontExitRoomModel::getInstance();
        $this->_frontconsumeModel  = FrontConsumeModel::getInstance();
        $this->_frontgoodsModel    = FrontGoodsModel::getInstance();
        $this->goods =  $goods = $this->_frontgoodsModel->getAll();
        foreach ($this->goods as $key => $value) {
        	      $this->goodsArray[$value['name']] = $value['gid'];
        }
        
    }
	/**
	 * [defaultAction 会员列表]
	 * @author [morgan] 
	 * @date(2014/11/03)
	 */
    public function defaultAction(){
        $priv = $this->_priv_arr;
        //判断权限
        if(!in_array(22,$priv)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        $page   = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $page_size = 20;
        $offset = $page_size*($page-1);
        /*搜索条件*/
        if(empty($_GET['search']['order'])){
            $_GET['search']['order'] = 'uid';
        }
        $time = date('Y-m-d H:i:s',time());
        Search::getCondition($this->_frontuserModel);
        /*搜索关键字*/
        $this->keyword();
        $list  = $this->_frontuserModel->limit($offset,$page_size)->getAll();
        foreach ($list as $key => $value) {
               // $is_online = $this->global->vedioInterface(array('uid'=>$value['uid']),$this->config['is_online']);
                if($is_online&&($is_online['ret']==1)){
                     $list[$key]['is_online'] = $is_online['loc'];
                }   
                $user_online = $this->_frontexitroomModel->fields(array('count(uid) as login_num','sum(duration) as online_times'))->eq(array('uid'=>$value['uid']))->getOne();
                
                //查看用户登录次数和累计在线时间
                if($user_online){
                    $list[$key]['online_times']  =  $this->global->sec2time($user_online['online_times']);
                }
        }
        /*搜索条件*/
        
        Search::getCondition($this->_frontuserModel);
        /*搜索关键字*/
        $this->keyword();
        $count  = $this->_frontuserModel->fields(array('count(uid) as nums'))->getOne();
        $nums   = $count['nums'];
        $data['list']  = $list;
        $data['pages'] = $this->global->pages($page_size,$nums,5,$page);
       
        include_once("./tpl/vip-list.html");
    }

    /**
     * [search 搜索]
     * @return [object] [对象]
     */
    public function keyword(){
        if(!empty($_GET['keyword'])){
            $keyword = addslashes($_GET['keyword']);
            $this->_frontuserModel->like(array('uid'=>$keyword,'nickname'=>$keyword,'username'=>$keyword));
        }
        if(!empty($_GET['vip_level'])){
            $level = intval($_GET['vip_level']);
            $this->_frontuserModel->ge(array('lv_rich'=>$level))->le(array('lv_rich'=>($level+9)));
        }
        if(isset($_GET['status'])  && $_GET['status'] != '' ){
            $status = intval($_GET['status']);
            $this->_frontuserModel->eq(array('status'=>$status));
        }
        return $this->_frontuserModel;
    }

    /**
     * [vipDetailsAction 会员详情]
     * @author [morgan] 
     * @date(2014/11/03)
     */
    public function  detailsAction(){
        //判断权限
        if(!in_array(25,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                
        }
    	if(isset($_GET['dosubmit'])){
    		$uid = addslashes($_GET['uid']);
            $list = $this->_frontuserModel->eq(array('uid'=>$uid))->getOne();
            if(!empty($list)){
             
            //查看用户充值次数和累计充值金额
           	$user = $this->_frontrechargeModel->fields(array('count(uid) as num','sum(points) as money'))->eq(array('uid'=>$list['uid']))->getOne();
           	$area = $this->_frontuserModel->getOne("SELECT area FROM `video_area` where code=".$list['city']);
            $list['area'] = $area['area'];
            $list['recharge_num'] = $user['num'];
            $list['recharge_sum'] = $user['money'];
            //计算用户年龄
            $list['age']       =  0;
            if($list['birthday']){
                $birthday_year =  date('Y',strtotime($list['birthday']));
                $list['age']   =  date('Y',time()) - $birthday_year;
            }
            $user_online = $this->_frontexitroomModel->fields(array('count(uid) as login_num','sum(duration) as online_times'))->eq(array('uid'=>$list['uid']))->getOne();
           
            //查看用户登录次数和累计在线时间
            if($user_online){
            	$list['online_times']  = $this->global->sec2time($user_online['online_times']);
            }
            //是否在线查询
            $is_online = $this->global->vedioInterface(array('uid'=>$list['uid']),$this->config['is_online']);

            if($is_online&&($is_online['ret']==1)){
                 $list['is_online'] = $is_online['loc'];
            } 
            
            //查询用户消费、充值记录
            $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    		$page_size = 20;
			$offset = $page_size*($page-1);
            $consume_list = $this->_frontconsumeModel->eq(array('send_uid'=>$uid))->order('id desc')->limit($offset,$page_size)->getAll();
            $count = $this->_frontconsumeModel->fields(array('count(id) as nums'))->eq(array('send_uid'=>$uid))->getOne();
            $nums = $count['nums'];
            if($consume_list){
                foreach ($consume_list as $key => $value) {
                	   $goods = $this->_frontgoodsModel->eq(array('gid'=>$value['gid']))->getOne();
                       $consume_list[$key]['goods_name'] = $goods['name'];
                }
            }
            $list['pages'] = $this->global->ajax_pages($page_size,$nums,5,$page,'"consume"');
            }else{
                $error = '没有此用户，或此用户不是主播';
            }
    	}
    	include_once("./tpl/vip-details.html");
    }

    /**
     * [ajax_vip ajax获取用户信息]
     * @author [morgan]
     * @date(2014/11/03) 
     */
    public function  ajaxAction(){
    		$uid  = addslashes($_POST['uid']);
    		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    		$type = $_POST['type'];
    		$page_size = 20;
			$offset = $page_size*($page-1);
			if($type=='"consume"'){
				$list = $this->_frontconsumeModel->eq(array('send_uid'=>$uid))->order('id desc')->limit($offset,$page_size)->getAll();
				foreach ($list as $key => $value) {
            	   $goods = $this->_frontgoodsModel->eq(array('gid'=>$value['gid']))->getOne();
                   $list[$key]['goods_name'] = $goods['name'];
            	}
                $count = $this->_frontconsumeModel->fields(array('count(id) as nums'))->eq(array('send_uid'=>$uid))->getOne();
			}else{
				$list  = $this->_frontrechargeModel->eq(array('uid'=>$uid))->limit($offset,$page_size)->getAll();
    			$count = $this->_frontrechargeModel->fields(array('count(id) as nums'))->eq(array('uid'=>$uid))->getOne(); 
                if($list){
                        foreach ($list as $key => $value) {
                                if($value['pay_status']==1){
                                    $list[$key]['pay_status'] = "充值成功";
                                }else{
                                    $list[$key]['pay_status'] = "充值失败";
                                }
                                $pay_type = $this->config['pay_type'];
                                foreach ($pay_type as $k => $val) {
                                    if($value['pay_type']==$k){
                                        $list[$key]['pay_type'] = $val;
                                    }
                                }
                                $list[$key]['points'] = round(($value['points']/10),2);
                        }
                }
			}
    		$nums = $count['nums'];
    		$pages = $this->global->ajax_pages($page_size,$nums,5,$page,$type);
    		$data['pages'] = $pages;
    		$data['num'] = count($list);
    		$data['list'] = $list;
    		echo json_encode($data);
    }

    /**
     * [ajaxResetAction ajax重置用户密码]
     * @return [type] [description]
     */
    public  function  ajaxResetAction(){
            //判断权限
            if(!in_array(23,$this->_priv_arr)){
                    echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                    return;   
            }
            $uid = intval($_POST['id']);
            $pd  = md5("qjgfbgqtd0a562");
            $state = $this->_frontuserModel->update(array('password'=>$pd), ' uid='.$uid);

            if($state){
                $redis = new redis();
                $redis->connect($this->config['redis_ip'],$this->config['redis_port']);
                $pipe = $redis->multi(Redis::PIPELINE);
                $_isHadRedis =  $pipe->hget('huser_info:'.$uid,'uid');
                if($_isHadRedis) {
                    $result  = $pipe->hset('huser_info:'.$uid,'password',$pd);
                }
                $pipe->exec();
                $redis->close();
                echo json_encode("重置密码成功");
            }else{
                echo json_encode("重置密码失败");
            }
    }

    /**
     * [ajaxShutterAction 封停账号]
     * @return [type] [description]
     */
    public function  ajaxShutterAction(){
        //判断权限
        if(!in_array(24,$this->_priv_arr)){
                echo "<script>alert('没有权限');location.href='./index.php?do=index-index';</script>";
                return;   
        }
        $uid = intval($_POST['id']);
        $data['status'] = intval($_POST['status']);
        $state = $this->_frontuserModel->update($data, ' uid='.$uid);
        if($state){
            $redis = new redis();
            $redis->connect($this->config['redis_ip'],$this->config['redis_port']);
            $pipe = $redis->multi(Redis::PIPELINE);
            $_isHadRedis =  $pipe->hget('huser_info:'.$uid,'uid');
            if($_isHadRedis) {
                $result  =  $pipe->hset('huser_info:'.$uid,'status',$data['status']);
            }
            $pipe->exec();
            $redis->close();
            echo json_encode("成功");
        }else{
            echo json_encode("失败");
        }
    }
} 