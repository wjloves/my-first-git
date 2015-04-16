<?php
//手动充值接口URL
$config['recharge_url']    = 'http://10.146.180.42:8088/video_gs/web_api/add_point?';
//赠送道具接口URL
$config['goods_url']       = 'http://10.146.180.42:8088/video_gs/web_api/add_pack?';
//房间公告管理
$config['announcement_url'] = 'http://10.146.180.42:8088/video_gs/web_api/add_notice?';
//（即时数据）主播数据接口
$config['host_online_url'] = 'http://10.146.180.42:8088/video_gs/web_api/list_room?';
//设为主播接口
$config['add_host_url']    = 'http://10.146.180.42:8088/video_gs/web_api/add_room?';
//删除主播接口
$config['del_host_url']    = 'http://10.146.180.42:8088/video_gs/web_api/del_room?';
//是否在线接口
$config['is_online']       = 'http://10.146.180.42:8088/video_gs/web_api/get_online?';
//修改主播分类接口
$config['edit_host_type']  = 'http://10.146.180.42:8088/video_gs/web_api/add_tag?';
//活动接口
$config['activity_url']    = 'http://www.vf.com/activitySend';
//活动配置名称
$config['activity_name']  = 'firstcharge';
//系统消息类别 (业务类别)
$config['mail_type']       =  array("登录注册类","充值提款类","优惠活动类","客服服务类","投诉意见类");
//渠道类别
$config['channel_type']    =  array("sex8","百度","大媒体","论坛");
//充值方式

// 银行转账：  前台充值在用      后台充值：  后台充值在用       充值赠送：活动专用
$config['pay_type']        =  array("1"=>"银行转账","2"=>"支付宝","3"=>"paypal","4"=>"后台充值","5"=>"充值赠送");

//redis IP 
$config['redis_ip']        =  '10.146.180.42';
//redis 端口
$config['redis_port']      =  '6379';
//操作分类
$config['action_type']     = array(
								"Edit"=>"编辑",
								"Del"=>"删除",
								"Shutter"=>"封停账号",
								"Reset"=>"重置用户密码",
								"Add"=>"添加",
								"Mesaage"=>"系统消息",
								"Default"=>"访问",
								"Recharge"=>"充值"
								);

// 主播类型：  1 普通主播 2 中级主播 3 高级主播
$config['host_type']        =  array("1"=>"普通艺人", "2"=>"中级艺人", "3"=>"高级艺人");

// //查询统计人数
// $config['invit_num']       =  array(
// 								"1"=>"1-100",
// 								"101"=>"101-200",
// 								"201"=>"201-500",
// 								"501"=>"501-1000",
// 								"1001"=>"1001-2000",
// 								"2001"=>"2001-5000"
// 							  );

