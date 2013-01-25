<?php
 include_once('./include/main.inc.php');
 include_once('./include/common.inc.php');
$app_per = 5;
	$applist = '';
    $i=1;
    $getlist = $_SGLOBAL['db']->query(" select * from ".tname('applist')." order by que asc ");
    $num = $_SGLOBAL['db'] ->num_rows($getlist);
	while($item = $_SGLOBAL['db']->fetch_array($getlist)){
		if($i==3){
    		$one = $item['bigname'];
    		$applist.='<div style="float:left" id="div_'.$i.'">
			<img id="img_'.$i.'" src="images/'.$item['name'].'_2.png" width="108" height="120" />
		</div>';
    		$make.='else if(btn=="img_'.$i.'"){
    			location.href="'.$item['url'].'";
    		}';
    		$move.='else if(btn=="img_'.($i-1).'"&&obj==4){
	    			changeleft();
	    			document.getElementById("img_b").src="images/'.$item['bigname'].'";
	    			document.getElementById("btn").value="img_'.$i.'";
	    		}else if(btn=="img_'.$i.'"&&obj==3){
	    			changeright();
	    			document.getElementById("img_b").src="images/'.$pre_bname.'";
	    			document.getElementById("btn").value="img_'.($i-1).'";
	    		}';	
    	}else{
    		if($i>$app_per){
    			$applist.='<div style="float:left;display:none" id="div_'.$i.'">
    				<img id="img_'.$i.'" src="images/'.$item['name'].'.png" width="108" height="120" />
    			</div>';
    		}else{
    			$applist.='<div style="float:left;" id="div_'.$i.'">
    				<img id="img_'.$i.'" src="images/'.$item['name'].'.png" width="108" height="120" />
    			</div>';
    		}
    		if($i==1){
    			$one_name = $item['name'];
    			$one_big = $item['bigname'];
	    		$make.='if(btn=="img_'.$i.'"){
	    			location.href="'.$item['url'].'";
	    		}';
    		}else{
    			$make.='else if(btn=="img_'.$i.'"){
    			location.href="'.$item['url'].'";
    		}';
    			if($i==2){
	    			$move.='if(btn=="img_'.($i-1).'"&&obj==4){
		    			changeleft();
		    			document.getElementById("img_b").src="images/'.$item['bigname'].'";
		    			document.getElementById("btn").value="img_'.$i.'";
		    			
		    		}else if(btn=="img_'.$i.'"&&obj==3){
		    			changeright();
		    			document.getElementById("img_b").src="images/'.$pre_bname.'";
		    			document.getElementById("btn").value="img_'.($i-1).'";
		    		}';	
	    		}else{
	    			if($i==$num){
	    				$move.='else if(btn=="img_'.($i-1).'"&&obj==4){
		    			changeleft();
		    			document.getElementById("img_b").src="images/'.$item['bigname'].'";
		    			document.getElementById("btn").value="img_'.$i.'";
		    		}else if(btn=="img_1"&&obj==3){
		    			changeright();
		    			document.getElementById("img_b").src="images/'.$item['bigname'].'";
		    			document.getElementById("btn").value="img_'.$i.'";
		    		}else if(btn=="img_'.$i.'"&&obj==3){
		    			changeright();
		    			document.getElementById("img_b").src="images/'.$pre_bname.'";
		    			document.getElementById("btn").value="img_'.($i-1).'";
		    		}else if(btn=="img_'.$i.'"&&obj==4){
		    			changeleft();
		    			document.getElementById("img_b").src="images/'.$one_big.'";
		    			document.getElementById("btn").value="img_1";
		    		}';	
	    			}else{
	    				$move.='else if(btn=="img_'.($i-1).'"&&obj==4){
		    			changeleft();
		    			document.getElementById("img_b").src="images/'.$item['bigname'].'";
		    			document.getElementById("btn").value="img_'.$i.'";
		    		}else if(btn=="img_'.$i.'"&&obj==3){
		    			changeright();
		    			document.getElementById("img_b").src="images/'.$pre_bname.'";
		    			document.getElementById("btn").value="img_'.($i-1).'";
		    		}';	
	    			}
	    		}
    		}
    		
    	}
		$pre_name =$item['name'];
		$pre_bname = $item['bigname'];
    	$i++;
    }
    $total = $i-1;
	print<<<EOF
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gbk" />
<style>
	body{ font-size:18px; font-weight:bold; margin:0; padding:0; background:url(images/bg.jpg); color:#fff; font-family:"幼圆"}
</style>
</head>
<body>
<input type="hidden" id="btn" name="btn" value="img_3">
<input type="hidden" id="now" name="now" value="1">
	<div id="app" style=" position:absolute; top:40px; left:510px; font-size:24px;">
		1/$total
	</div>
	<div style=" position:absolute; top:93px; left:126px; width:406px; height:232px;">
		<img id="img_b" src="images/$one" width="402" height="227" />
	</div>
	<div style=" position:absolute; top:350px; left:55px; width:540px; height:120px;">
		$applist
	</div>
<script type="text/javascript">
 var epgdomain=Authentication.CTCGetConfig('EPGDomain');
			window.document.onkeypress = function(keyEvent) {
				keyEvent = keyEvent ? keyEvent : window.event;
				keyEvent.which = keyEvent.which ? keyEvent.which : keyEvent.keyCode;
				keycontrol(keyEvent.which);
			}
 function makesure(){
	var btn = document.getElementById("btn").value;
 	$make
 }
 function keycontrol(obj){
	if( obj == 0x08 )	{
		/*  中兴遥控器返回键  */
		window.location.href=epgdomain;
	
	}else if( obj == 0x0026 ) {
	    /*  上  */
	    movebtn(1);
	}else if( obj == 0x0028 ) {
		 /*  下  */
		 movebtn(2);
	}else if( obj == 0x0025 ) {
		 /*  左  */
		 movebtn(3);
	}else if( obj == 0x0027 ) {
		 /*  右  */
		 movebtn(4);
	}else if( obj == 0x000D ){
		/*  确定  */
		makesure();
	}
}
function movebtn(obj){
	var btn = document.getElementById("btn").value;
	$move
}
var c = 1;
function changeleft(){
    var obj = $i-1;
    var begin = document.getElementById("div_1").innerHTML;
	for(var i=1;i<$i;i++){
		if(i==obj){
			document.getElementById("div_"+i).innerHTML = begin;
		}else{
			var ii = i+1;
			if(i==2){
				var temp = document.getElementById("div_"+ii).innerHTML;
				document.getElementById("div_"+i).innerHTML =temp.replace("_2.png",".png");
			}else if(i==3){
				var temp = document.getElementById("div_"+ii).innerHTML;
				document.getElementById("div_"+i).innerHTML =temp.replace(".png","_2.png");
			}else{
				document.getElementById("div_"+i).innerHTML = document.getElementById("div_"+ii).innerHTML;
			}
		}
	}
	c++;
	if(c==($total+1)){
		c=1;
	}
	document.getElementById("app").innerHTML=c+"/$total";
}
function changeright(){
    var obj = $i-1;
    var end = document.getElementById("div_"+obj).innerHTML;
	for(var i=obj;i>=1;i--){
		if(i==1){
			document.getElementById("div_"+i).innerHTML = end;
		}else{
			var ii = i-1;
			if(i==4){
				var temp = document.getElementById("div_"+ii).innerHTML;
				document.getElementById("div_"+i).innerHTML =temp.replace("_2.png",".png");
			}else if(i==3){
				var temp = document.getElementById("div_"+ii).innerHTML;
				document.getElementById("div_"+i).innerHTML =temp.replace(".png","_2.png");
			}else{
				document.getElementById("div_"+i).innerHTML = document.getElementById("div_"+ii).innerHTML;
			}
		}
	}
	c--;
	if(c==0){
		c=$total;
	}
	document.getElementById("app").innerHTML=c+"/$total";
}
</script>
</body>
</html>
EOF
?>