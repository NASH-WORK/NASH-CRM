function get(name) {
	var url = window.location.href;
    var urlArray = url.split('?');
	window.location.href = 'http://' + window.location.host+'/test/crm/contact_hash.php'+'?tagList=^'+name;
}

function phone(phone_num) {
	window.location.href = 'tel://' + phone_num;
}

function showLoading() {
  $('body').append('<div id="lean-overlay" style="display: block; opacity: 0.5;"><div class="progress" style="margin:0"><div class="indeterminate"></div></div></div>');
  return true;
}

function GetQueryString(name)
{
     var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
     var r = window.location.search.substr(1).match(reg);
     if(r!=null)return  unescape(r[2]); return null;
}

function getAjaxRequestAddress(functionName) {
	var host = window.location.host;
	return 'http://' + host +'/vstone/app/?r=' + functionName;
}

function SetCookie(name,value)//两个参数，一个是cookie的名子，一个是值
{
    var Days = 30; //此 cookie 将被保存 30 天
    var exp  = new Date();    //new Date("December 31, 9998");
    exp.setTime(exp.getTime() + Days*24*60*60*1000);
    document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
}
function getCookie(name)//取cookies函数        
{
    var arr = document.cookie.match(new RegExp("(^| )"+name+"=([^;]*)(;|$)"));
     if(arr != null) return unescape(arr[2]); return null;
}

function delCookie(name)//删除cookie
{
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval=getCookie(name);
    if(cval!=null) document.cookie= name + "="+cval+";expires="+exp.toGMTString();
}

