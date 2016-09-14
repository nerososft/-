<?php
/**
 *
 */
class IndexAction extends Action
{

public function Index(){
  $this->display();
}
public function in(){
  $new['title'] = $_POST['title'];
  $new['content'] = $_POST['content'];
  $new['type'] = $_POST['type'];
  $tongzhi_model = M('Tongzhi');
  $isadd = $tongzhi_model->add($new);
  if($isadd){
    echo "<script>window.location.href='http://121.42.157.180/qgfdyjnds/index.php/Index';</script>";
  }else{
    echo "error".mysql_error();
  }
}
public function pa(){
  //$tongzhi_model = M('Tongzhi');
  header("Content-type: text/html; charset=utf-8");
  for($j = 51233;$j<51234;$j++){
  $url='http://news.twt.edu.cn/?c=default&a=pernews&id='.$j;
  $s = file_get_contents($url);
  //去掉换行、制表等特殊字符，可以echo一下看看效果
  $s=preg_replace("/[\t\n\r]+/","",$s);
  preg_match_all('/<title>(.+?)<\/title>/',$s,$a);
  //var_dump($a);
  //echo $a[1][0];
  preg_match_all('/<p style="text-align: left; text-indent: 2em;">(.+?)<\/p>/',$s,$f);
  //var_dump($f);
  //echo $f[1][0];
  $i = 1;
  $new['title'] = $a[1][0];
  $new['subtitle'] = $a[1][0];
  $new['content'] =  $f[1][0];
  if($i%2 == 0){
      $new['type'] = 'tongzhi';
  }else if($i%3 == 0){
      $new['type'] = 'dongtai';
  }else{
      $new['type'] = 'huiwu';
  }
  var_dump($new);
  /*
  //$isadd = $tongzhi_model->add($new);
  //if($isadd){
    echo "success";
    $i++;
  }else{
    echo "error".mysql_error();
  }*/
}
}
public function paixu(){
	$schedule = M("Schedule");
	$sche_list = $schedule->order('data')->select();
	for($i = 0;$i<count($sche_list);$i++){
		$new['paixu'] = $i+1;
		$ispaixu = $schedule->where('id=%d',$sche_list[$i]['id'])->save($new);
		if($ispaixu){
			echo "排序成功";
		}
	}
}
public function schedule(){
  $la = $_GET['la'];
  $ln = $_GET['ln'];
  $this->assign('ll',array(array('la'=>$la,'ln'=>$ln)));
  $this->display();
}
public function map(){

  $this->assign('data',array(array('location'=>$l,'lo'=>$_GET['lo'],'la'=>$_GET['la'],'lla'=>$_GET['lla'],'lln'=>$_GET['lln'])));
  $this->display();
}
public function scheduleios(){
  $this->display();
}
}

?>
