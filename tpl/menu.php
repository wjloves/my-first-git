<?php   $priv_arr = unserialize($_COOKIE['priv']);?>
<div class="left">
	<div class="logo"></div>
	<dl class="active">
		<dt><a href="#" class="first-menu">运营管理</a></dt>
		<?php if(in_array('8', $priv_arr)){?>
		<dd><a href="./index.php?do=index-index" class="second-menu" tabindex="1" id="index">即时数据</a></dd>
		<?php }
		    if(in_array('9', $priv_arr)){?>
		    <dd><a href="./index.php?do=member" class="second-menu" tabindex="2">用户数据</a></dd>
		<?php }
		   if(in_array('10', $priv_arr)){?>
		   <dd><a href="./index.php?do=bill" class="second-menu" tabindex="3">营收数据</a></dd>
		<?php }
		   if(in_array('11', $priv_arr)){?>
		   <dd><a href="./index.php?do=props" class="second-menu" tabindex="4">道具销售</a></dd>
		<?php }
		   if(in_array('40', $priv_arr)){?>
		   <dd><a href="./index.php?do=props-log" class="second-menu" tabindex="31">道具销售日志</a></dd>
		<?php }
		   if(in_array('12', $priv_arr)){?>
		<!--<a href="#" class="second-menu" tabindex="5">节目点播</a>-->
		<?php }
		   if(in_array('13', $priv_arr)){?>
		   <dd><a href="./index.php?do=host" class="second-menu" tabindex="6">主播业绩</a></dd>
		<?php }?>
	</dl>
	<dl>
		<dt><a href="#" class="first-menu">财务管理</a></dt>
		<?php  if(in_array('14', $priv_arr)){?>
		<dd><a href="./index.php?do=recharge" class="second-menu" tabindex="7">充值明细</a></dd>
		<?php }
		   if(in_array('16', $priv_arr)){?>
		      <dd><a href="./index.php?do=cash" class="second-menu" tabindex="201">提款申请</a></dd>
		<?php }
		      if(in_array('15', $priv_arr)){?>
	    	<dd><a href="./index.php?do=cash-audit" class="second-menu" tabindex="202">提款审批</a>	</dd>
		<?php }
		   if(in_array('17', $priv_arr)){?>
		   <dd><a href="./index.php?do=handrecharge" class="second-menu" tabindex="9">手动充值</a></dd>
		<?php }
		   if(in_array('43', $priv_arr)){?>
		   <dd><a href="./index.php?do=props-management" class="second-menu" tabindex="33">道具管理</a></dd>
		<?php }

		   if(in_array('47', $priv_arr)){?>
		   <dd><a href="./index.php?do=recharge-variable" class="second-menu" tabindex="37">帐变列表</a></dd>
		<?php }
	    	if(in_array('48', $priv_arr)){?>
		   <dd><a href="./index.php?do=recharge-audit" class="second-menu" tabindex="38">充值申请</a></dd>

		<?php }
		   if(in_array('53', $priv_arr)){?>
            <dd><a href="./index.php?do=mrecharge#mrecharg" class="second-menu" tabindex="103">人工充值（申请）</a></dd>
        <?php }
        if(in_array('54', $priv_arr)){?>
            <dd><a href="./index.php?do=mrecharge-check" class="second-menu" tabindex="104">人工充值（审核）</a></dd>
        <?php }
        if(in_array('55', $priv_arr)){?>
            <dd><a href="./index.php?do=mrecharge-minusapply" class="second-menu" tabindex="105">人工扣减（申请）</a></dd>
        <?php }
        if(in_array('56', $priv_arr)){?>
            <dd><a href="./index.php?do=mrecharge-checkminus" class="second-menu" tabindex="106">人工扣减（审核）</a></dd>
        <?php }?>
	</dl>
	<dl>
		<dt><a href="javascript:;" class="first-menu">主播管理</a></dt>
		<?php if(in_array('18', $priv_arr)){?>
		<dd><a href="./index.php?do=host-audit" class="second-menu" tabindex="10">签约审核</a></dd>
		<?php }
		   if(in_array('19', $priv_arr)){?>
		   <dd><a href="./index.php?do=host-list" class="second-menu" tabindex="11">主播清单</a></dd>
		<?php }
		   if(in_array('20', $priv_arr)){?>
		   <dd><a href="./index.php?do=host-details" class="second-menu" tabindex="12">主播详情</a></dd>
		<?php }
		   if(in_array('21', $priv_arr)){?>
		   <dd><a href="./index.php?do=percentage" class="second-menu" tabindex="13">分成机制</a></dd>
		<?php }
			if(in_array('45', $priv_arr)){?>
		   <dd><a href="./index.php?do=host-daily" class="second-menu" tabindex="35">主播日报表</a></dd>
		<?php }
            if(in_array('58', $priv_arr)){?>
            <dd><a href="./index.php?do=host-month" class="second-menu" tabindex="40">主播日报表</a></dd>
        <?php }
			if(in_array('46', $priv_arr)){?>
		   <dd><a href="./index.php?do=host-statis" class="second-menu" tabindex="36">直播记录查询</a></dd>
		<?php }
            if(in_array('63', $priv_arr)){?>
		   <dd> <a href="./index.php?do=extract" class="second-menu" tabindex="30">主播提现申请</a></dd>
		<?php }
            if(in_array('57', $priv_arr)){?>
        <dd><a href="./index.php?do=hostincome-main" class="second-menu" tabindex="39">主播礼物提成设置</a></dd>
        <?php }?>
	</dl>
	<dl>
		<dt><a href="#" class="first-menu">会员管理</a></dt>
		<?php if(in_array('22', $priv_arr)){?>
		<dd><a href="./index.php?do=vip" class="second-menu" tabindex="14">会员清单</a></dd>
		<?php }
		   if(in_array('25', $priv_arr)){?>
		   <dd><a href="./index.php?do=vip-details" class="second-menu" tabindex="15">会员详情</a></dd>
		<?php }
		   if(in_array('26', $priv_arr)){?>
		   <dd><a href="./index.php?do=customService" class="second-menu" tabindex="16">客服消息</a></dd>
		<?php }
		   if(in_array('27', $priv_arr)){?>
		   <dd><a href="./index.php?do=complaints" class="second-menu" tabindex="17">投诉建议</a></dd>
		<?php }?>
		<!--<a href="#" class="first-menu">内容管理</a>-->
		<?php if(in_array('28', $priv_arr)){?>
		<!--<a href="#" class="second-menu" tabindex="18">内容分类</a>-->
		<?php }
		   if(in_array('29', $priv_arr)){?>
		<!--<a href="#" class="second-menu" tabindex="19">道具管理</a>-->
		<?php }
		   if(in_array('30', $priv_arr)){?>
		<!--<a href="#" class="second-menu" tabindex="20">场景管理</a>-->
		<?php }
		   if(in_array('31', $priv_arr)){?>
		<!--<a href="#" class="second-menu" tabindex="21">广告位管理</a>-->
		<?php }?>
	</dl>
	<dl>
		<dt><a href="#" class="first-menu">推广管理</a></dt>
		<?php if(in_array('32', $priv_arr)){?>
		<!--<a href="#" class="second-menu"  tabindex="22">广告配置</a>-->
		<?php }
		   if(in_array('33', $priv_arr)){?>
		   <dd><a href="./index.php?do=channel#channel" class="second-menu"  tabindex="23" id="channel">推广渠道</a></dd>
		<?php }
		   if(in_array('36', $priv_arr)){?>
		   <dd><a href="./index.php?do=channel-management#channel-management" class="second-menu"  tabindex="28" id="channel-management">渠道管理</a></dd>
		<?php }
		   if(in_array('37', $priv_arr)){?>
		   <dd><a href="./index.php?do=channel-personal#channel-personal" class="second-menu"  tabindex="29" id="channel-personal">个人推广</a></dd>
		<?php }
		   if(in_array('35', $priv_arr)){?>
		<!--<a href="#" class="second-menu"  tabindex="24">费用添加</a>-->
		<?php }
		   if(in_array('44', $priv_arr)){?>
		   <dd><a href="./index.php?do=announcement" class="second-menu" tabindex="34">房间公告</a></dd>
		<?php }?>
	</dl>

   <!-- <dl>
        <a href="#" class="second-menu"   tabindex="26">素材数据</a>
        <dt><a href="#" class="first-menu">代理管理</a></dt>
        <?php if(in_array('50', $priv_arr)){?>

            <dd><a href="./index.php?do=domain#domain" id="domain" class="second-menu"   tabindex="101">新增域名</a></dd>
        <?php }
        if(in_array('51', $priv_arr)){?>
            <dd><a href="./index.php?do=domain-redirectSet#domain-redirectSet" id="domain-redirectSet" class="second-menu"   tabindex="102">跳转设定</a></dd>
        <?php }?>
    </dl>-->

    <dl>
        <!--<a href="#" class="second-menu"   tabindex="25">素材数据</a>-->
        <dt><a href="#" class="first-menu">权限管理</a></dt>
        <?php if(in_array('38', $priv_arr)){?>
            <dd><a href="./index.php?do=admin#admin" id="admin" class="second-menu"   tabindex="26">后台用户</a></dd>
        <?php }
        if(in_array('39', $priv_arr)){?>
            <dd><a href="./index.php?do=priv#priv" id="priv" class="second-menu"   tabindex="27">权限设定</a></dd>
        <?php }?>
        <dd><a href="./index.php?do=log#log" id="log" class="second-menu"   tabindex="32">后台操作日志</a></dd>
    </dl>

</div>
<script type="text/javascript">

    $(function(){$(".second-menu").click(function(){var COOKIE_NAME='active';var type=1;type=$(this).attr('tabindex');$.cookie(COOKIE_NAME,type,{path:'/',expires:10})})});$(".left dl").click(function(){var COOKIE_NAME='dlActive';var type=100;type=$(this).index();$.cookie(COOKIE_NAME,type,{path:'/',expires:10})});$(document).ready(function(){var COOKIE_VALUE=$.cookie("active");var COOKIE_DL=$.cookie("dlActive")-1;if(COOKIE_DL!=99){$('.left dl').removeClass('active');}
        $('.left dl').eq(COOKIE_DL).addClass('active');$(".second-menu").each(function(){if($(this).attr('tabindex')==COOKIE_VALUE){$(this).css({"background-color":"#27a1fc","color":"#fff"}).prepend("<img src='img/secondList.jpg' width='4' height='4'>")}})});
</script>