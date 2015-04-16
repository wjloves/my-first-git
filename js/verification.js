
$(function(){$("#sure-btn1").click(function(){var code=$('#code').val();if(code==''){alert("验证码不能为空");return false;}
$.post('./index.php?do=login-ajaxVcode',{code:code},function(msg){if(msg){if(msg.length>3){alert(msg);}else{$("#myform").submit();}}else{alert("验证码错误");}},'json');});})
$(function(){$("#next_code").click(function(){var t=Date.parse(new Date());$(".de-code").prop("src","./index.php?do=login-code&t="+t);});})