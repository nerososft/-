<?php
// 本类由系统自动生成，仅供测试用途
class ApiAction extends Action {
	public function update() {
		$new_model = M('New');
		$type = $_POST['type'];
		$condition['type'] = $_POST['type'];
		$condition['isnew'] = 1;
		$is_new = $new_model -> where($condition) -> find();
		if (is_null($is_new)) {
			$result = array('result_code' => '10000', 'msg' => '已经是最新版');
		} else {
			$result = array('result_code' => '20000', 'msg' => '有新版本更新', 'url' => $is_new['url'], 'version' => $is_new['version'], 'detail' => $is_new['xinxi']);
		}
		echo json_encode($result);
	}

	//什么鬼注册
	public function register() {
		//echo json_encode(array('verify'=>$_POST['verify']));
		// echo json_encode(array('v'=>md5($_POST['verify']),'v_t'=>$_SESSION['verify']));
		if (md5($_POST['verify']) != $_SESSION['verify']) {
			$result = array('result_code' => '10010', 'msg' => '验证码错误');
			echo json_encode($result);
		} else {
			$newuser['phone'] = $_POST['phone'];
			$user_model = M('User');
			$ishave = $user_model -> where($newuser) -> find();
			if (!is_null($ishave)) {
				$result = array('result_code' => '10002', 'msg' => '电话已经存在');
				echo json_encode($result);
			} else {
				$newuser['username'] = $_POST['username'];
				$newuser['sex'] = $_POST['sex'];
				$newuser['old'] = $_POST['age'];
				$newuser['pwd'] = md5($_POST['pwd']);
				$newuser['danwei'] = $_POST['danwei'];
				$newuser['zhifu'] = $_POST['zhiwu'];
				if ($newuser['sex'] == 1) {
					$newuser['icon'] = 287;
				} else if ($newuser['sex'] == 0) {
					$newuser['icon'] = 288;
				}
				if ($user_model -> add($newuser)) {
					$result = array('result_code' => '10000', 'msg' => '注册成功');
					echo json_encode($result);
				} else {
					$result = array('result_code' => '10001', 'msg' => '注册失败');
					echo json_encode($result);
				}
			}
		}
	}

	public function getverify() {
		import("ORG.Util.Image");
		Image::buildImageVerify();
	}

	public function log_in() {
		$phone = $_POST['phone'];
		$pwd = $_POST['pwd'];
		$user_model = M('User');
		$user_info = $user_model -> where("phone='%s'", $phone) -> find();
		if (is_null($user_info)) {
			$result = array('result_code' => '00000', 'msg' => '用户不存在');
			echo json_encode($result);
		} else {
			if (md5($pwd) != $user_info['pwd']) {
				$result = array('result_code' => '00001', 'msg' => '密码错误');
				echo json_encode($result);
			} else {
				$user_update['token'] = sha1(time() . md5(get_randchar(16)));
				$isupdate = $user_model -> where("phone='%s'", $phone) -> save($user_update);
				if (!$isupdate) {
					$result = array('result_code' => '00002', 'msg' => '登陆失败，重试');
					echo json_encode($result);
				} else {
					session(array('name' => 'session_id', 'expire' => 48 * 3600));
					//session初始化
					session('phone', $phone);
					session('token', $user_update['token']);
					//设置session
					$result = array('result_code' => '00003', 'msg' => '登陆成功', 'token' => session('token'));
					echo json_encode($result);
				}
			}
		}
	}

	public function get_userinfo() {
		if (session('?phone')) {
			$user_model = M('User');
			$user_info = $user_model -> where("phone='%s'", session('phone')) -> find();
			if (session('token') != $user_info['token']) {
				$result = array('result_code' => '00007', 'msg' => '您的账号在别处登录，或者登录超时，您已被迫下线');
				echo json_encode($result);
			} else {
				echo json_encode($user_info);
			}
		} else {
			$result = array('result_code' => '00006', 'msg' => '未登录');
			echo json_encode($result);
		}
	}

	public function log_out() {
		if (session('?phone')) {
			$user_model = M('User');
			$user_info = $user_model -> where("phone='%s'", session('phone')) -> find();
			$isupdate = $user_model -> where("phone='%s'", session('phone')) -> save($user_update);
			if (!$isupdate) {
				session('phone', null);
				session('token', null);
				$result = array('result_code' => '00004', 'msg' => '登出成功');
				echo json_encode($result);
			} else {
				$result = array('result_code' => '00005', 'msg' => '登出失败');
				echo json_encode($result);
			}
		}
	}

	public function change_pwd() {
		if (session('?phone')) {
			$user_model = M('User');
			$user_info = $user_model -> where("phone='%s'", session('phone')) -> find();
			if (session('token') != $user_info['token']) {
				$result = array('result_code' => '00007', 'msg' => '您的账号在别处登录，或者登录超时，您已被迫下线');
				echo json_encode($result);
			} else {
				$old_pwd = $_POST['old_pwd'];
				$new_pwd = $_POST['new_pwd'];
				if (md5($old_pwd) != $user_info['pwd']) {
					$result = array('result_code' => '00001', 'msg' => '旧密码错误');
					echo json_encode($result);
				} else {
					$user_update['pwd'] = md5($new_pwd);
					$is_pwd_change = $user_model -> where("phone='%s'", session('phone')) -> save($user_update);
					if (!$is_pwd_change) {
						$result = array('result_code' => '00008', 'msg' => '密码修改失败');
						echo json_encode($result);
					} else {
						$result = array('result_code' => '00009', 'msg' => '密码修改成功');
						echo json_encode($result);
					}
				}
			}
		} else {
			$result = array('result_code' => '00006', 'msg' => '未登录');
			echo json_encode($result);
		}
	}

	public function data() {
		if (session('?phone')) {
			$user_model = M('User');
			$user_info = $user_model -> where("phone='%s'", session('phone')) -> find();
			if (session('token') != $user_info['token']) {
				$result = array('result_code' => '00007', 'msg' => '您的账号在别处登录，或者登录超时，您已被迫下线');
				echo json_encode($result);
			} else {
				$type = $_POST['type'];
				$data = '';
				$cond = '';
				$tongzhi_model = M('Tongzhi');
				switch ($type) {
					case 'Tongzhi' :
						$cond = 'tongzhi';
						$cond_cond = array("type" => "tongzhi", "isup" => 1);
						$data = $tongzhi_model -> where("type='%s'", $cond) -> limit(12 * ($_POST['yeshu'] - 1), 12) -> order('createtime desc') -> select();
						$a_data = $tongzhi_model -> where($cond_cond) -> find();
						if (!is_null($data)) {
							for ($i = 0; $i < count($data); $i++) {
								$data[$i]['content'] = urldecode(base64_decode($data[$i]['content']));
							}
						} else {
							$data = array();
						}
						if (!is_null($a_data)) {
							if ($_POST['yeshu'] == 1) {
								$a_data['content'] = urldecode(base64_decode($a_data['content']));
								array_unshift($data, $a_data);
							}
						}

						break;
					case 'Huiwu' :
						$cond = 'huiwu';
						$cond_cond = array("type" => "huiwu", "isup" => 1);
						$data = $tongzhi_model -> where("type='%s'", $cond) -> limit(12 * ($_POST['yeshu'] - 1), 12) -> order('createtime desc') -> select();
						$a_data = $tongzhi_model -> where($cond_cond) -> find();
						if (!is_null($data)) {
							for ($i = 0; $i < count($data); $i++) {
								$data[$i]['content'] = urldecode(base64_decode($data[$i]['content']));
							}
						} else {
							$data = array();
						}
						if (!is_null($a_data)) {
							if ($_POST['yeshu'] == 1) {
								$a_data['content'] = urldecode(base64_decode($a_data['content']));
								array_unshift($data, $a_data);
							}
						}
						break;
					case 'Dongtai' :
						$cond = 'dongtai';
						$cond_cond = array("type" => "dongtai", "isup" => 1);
						$data = $tongzhi_model -> where("type='%s'", $cond) -> limit(12 * ($_POST['yeshu'] - 1), 12) -> order('createtime desc') -> select();
						$a_data = $tongzhi_model -> where($cond_cond) -> find();
						if (!is_null($data)) {
							for ($i = 0; $i < count($data); $i++) {
								$data[$i]['content'] = urldecode(base64_decode($data[$i]['content']));
							}
						} else {
							$data = array();
						}
						if (!is_null($a_data)) {
							if ($_POST['yeshu'] == 1) {
								$a_data['content'] = urldecode(base64_decode($a_data['content']));
								array_unshift($data, $a_data);
							}
						}
						break;
					default :
						$data = array('result_code' => '00020', 'msg' => '请求参数不对,分别为‘Tonfzhi’,‘Huiwu’,‘Dongtai’');
						break;
				}

				echo json_encode($data);
			}
		} else {
			$result = array('result_code' => '00006', 'msg' => '未登录');
			echo json_encode($result);
		}
	}

	public function fangwen() {
		$id = $_POST['id'];
		$tongzhi_model = M('Tongzhi');
		$tongzhi_result = $tongzhi_model -> where('id=%d', $id) -> find();
		$fwl = array('fwl' => $tongzhi_result['fwl'] + 1);
		$tongzhi_model -> where('id=%d', $id) -> save($fwl);
	}

	public function wenjian() {
		if (session('?phone')) {
			$user_model = M('User');
			$user_info = $user_model -> where("phone='%s'", session('phone')) -> find();
			if (session('token') != $user_info['token']) {
				$result = array('result_code' => '00007', 'msg' => '您的账号在别处登录，或者登录超时，您已被迫下线');
				echo json_encode($result);
			} else {
				$wenjian_model = M('Wenjian');
				$wenjian_list = $wenjian_model -> order('createtime desc') -> select();
				if (!is_null($wenjian_list)) {
					for ($i = 0; $i < count($wenjian_list); $i++) {
						$wenjian_list[$i]['content'] = urldecode(base64_decode($wenjian_list[$i]['content']));
					}
				} else {
					$wenjian_list = array();
				}
				echo json_encode($wenjian_list);
			}
		} else {
			$result = array('result_code' => '00006', 'msg' => '未登录');
			echo json_encode($result);
		}
	}

	/*文件以及图片数据获取*/
	public function get_file_url() {
		if (session('?phone')) {
			$user_model = M('User');
			$user_info = $user_model -> where("phone='%s'", session('phone')) -> find();
			if (session('token') != $user_info['token']) {
				$result = array('result_code' => '00007', 'msg' => '您的账号在别处登录，或者登录超时，您已被迫下线');
				echo json_encode($result);
			} else {
				$fileid = $_POST['fileid'];
				$file_model = M('File');
				$file_info = $file_model -> where('id=%d', $fileid) -> find();
				if (is_null($file_info)) {
					$result = array('result_code' => '00010', 'msg' => '文件未找到');
					echo json_encode($result);
				} else {
					echo json_encode($file_info);
				}
			}
		} else {
			$result = array('result_code' => '00006', 'msg' => '未登录');
			echo json_encode($result);
		}
	}

	public function get_img_url() {
		if (session('?phone')) {
			$user_model = M('User');
			$user_info = $user_model -> where("phone='%s'", session('phone')) -> find();
			if (session('token') != $user_info['token']) {
				$result = array('result_code' => '00007', 'msg' => '您的账号在别处登录，或者登录超时，您已被迫下线');
				echo json_encode($result);
			} else {
				$imgid = $_POST['imgid'];
				$img_model = M('Img');
				$img_info = $img_model -> where('id=%d', $imgid) -> find();
				if (is_null($img_info)) {
					$result = array('result_code' => '00011', 'msg' => '图片未找到');
					echo json_encode($result);
				} else {
					// /var/www/html/qgfdyjnds/
					$img_info['url'] = substr($img_info['url'], 24);
					echo json_encode($img_info);
				}
			}
		} else {
			$result = array('result_code' => '00006', 'msg' => '未登录');
			echo json_encode($result);
		}
	}

}

//随机串
function get_randchar($len) {
	$str = null;
	$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz1234567890";
	$max = strlen($strPol) - 1;
	for ($i = 0; $i < $len; $i++) {
		$str .= $strPol[rand(0, $max)];
		//rand($min,$max)生成介于min和max两个数之间的一个随机整数
	}
	return $str;
}

function special_chars_replace($string) {
	return base64_decode($string);

	//return $string;
}
