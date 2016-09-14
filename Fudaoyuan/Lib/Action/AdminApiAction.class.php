<?php
/*
 *后台管理接口
 *neroyang
 **/

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-type');
header('Access-Control-Allow-Headers: X-Requested-With');
class AdminApiAction extends Action {
	//管理员登录
	public function login() {
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
				if ($user_info['isadmin'] != 1) {
					$result = array('result_code' => '00004', 'msg' => '不是管理员');
					echo json_encode($result);
				} else {
					$user_update['token'] = sha1(time() . md5(time()));
					$isupdate = $user_model -> where("phone='%s'", $phone) -> save($user_update);
					if (!$isupdate) {
						$result = array('result_code' => '00002', 'msg' => '登陆失败，重试');
						echo json_encode($result);
					} else {
						session(array('name' => 'session_id', 'expire' => 3600));
						//session初始化
						session('phone', $phone);
						session('token', $user_update['token']);
						//设置session
						$result = array('result_code' => '00003', 'msg' => '登陆成功', 'phone' => session('phone'), 'token' => session('token'));
						echo json_encode($result);
					}
				}
			}
		}
	}

	//用户相关
	public function adduser() {
		$user_model = M('User');
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();

		if ($_POST['token'] != $user_info['token']) {
			$result = array('result_code' => '00001', 'msg' => '登录超时或者其他设备登录，您被迫下线');
			echo json_encode($result);
		} else {
			if ($user_info['isadmin'] != 1) {
				$result = array('result_code' => '00004', 'msg' => '不是管理员');
				echo json_encode($result);
			} else {
				$isuser = $user_model -> where("phone='%s'", $_POST['phone']) -> find();
				if (!is_null($isuser)) {
					$result = array('result_code' => '10001', 'msg' => '添加失败,该号码已经存在');
					echo json_encode($result);
				} else {
					//添加用户
					$new_user = array('username' => $_POST['username'], 'phone' => $_POST['phone'], 'sex' => $_POST['sex'], 'old' => $_POST['old'], 'pwd' => md5($_POST['pwd']), 'danwei' => $_POST['danwei'], 'zhifu' => $_POST['zhiwu'], 'icon' => $_POST['avatar'], 'isadmin' => $_POST['isadmin']);
					//此处应该加过滤
					$isadd = $user_model -> add($new_user);
					if ($isadd) {
						$result = array('result_code' => '20001', 'msg' => '添加成功');
						echo json_encode($result);
					} else {
						$result = array('result_code' => '10001', 'msg' => '添加失败');
						echo json_encode($result);
					}
				}
			}
		}
	}

	public function deleteuser() {
		$user_model = M('User');
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();

		if ($user_info['token'] != $_POST['token']) {
			$result = array('result_code' => '00001', 'msg' => '登录超时或者其他设备登录，您被迫下线');
			echo json_encode($result);
		} else {
			if ($user_info['isadmin'] != 1) {
				$result = array('result_code' => '00004', 'msg' => '不是管理员');
				echo json_encode($result);
			} else {
				//添加用户
				//此处应该加过滤
				$isuser = $user_model -> where('id=%d', $_POST['id']) -> find();
				if ($isuser['phone'] == 'admin') {
					$result = array('result_code' => '10001', 'msg' => '超级管理员不可被删除');
					echo json_encode($result);
				} else {
					$isadd = $user_model -> where('id=%d', $_POST['id']) -> delete();
					if ($isadd) {
						$result = array('result_code' => '20001', 'msg' => '删除成功');
						echo json_encode($result);
					} else {
						$result = array('result_code' => '10001', 'msg' => '删除失败');
						echo json_encode($result);
					}
				}
			}
		}
	}

	public function modifyuser() {
		$phone = $_POST['phone'];
		$user_model = M('User');
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();

		if ($user_info['token'] != $_POST['token']) {
			$result = array('result_code' => '00001', 'msg' => '登录超时或者其他设备登录，您被迫下线');
			echo json_encode($result);
		} else {
			if ($user_info['isadmin'] != 1) {
				$result = array('result_code' => '00004', 'msg' => '不是管理员');
				echo json_encode($result);
			} else {
				//添加用户
				$isuser = $user_model -> where('id=%d', $_POST['uid']) -> find();
				if ($isuser['phone'] == 'admin') {
					$result = array('result_code' => '10001', 'msg' => '超级管理员密码不可被编辑，请联系数据库管理人员');
					echo json_encode($result);
				} else {
					$new_user = array('username' => $_POST['username'], 
					'phone' => $_POST['phone'], 
					'sex' => $_POST['sex'], 
					'old' => $_POST['old'], 
					'danwei' => $_POST['danwei'],
					 'zhifu' => $_POST['zhiwu'], 
					 'icon' => $_POST['avatar'], 
					 'isadmin' => $_POST['isadmin']);
					//此处应该加过滤
					$isadd = $user_model -> where('id=%d', $_POST['uid']) -> save($new_user);
					if ($isadd) {
						$result = array('result_code' => '20001', 'msg' => '修改成功');
						echo json_encode($result);
					} else {
						$result = array('result_code' => '10001', 'msg' => '修改失败' . M("User") -> getLastSql());
						echo json_encode($result);
					}
				}
			}
		}
	}

	public function change_pwd() {
		$phone = $_POST['phone'];
		$user_model = M('User');
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();

		if ($user_info['token'] != $_POST['token']) {
			$result = array('result_code' => '00001', 'msg' => '登录超时或者其他设备登录，您被迫下线');
			echo json_encode($result);
		} else {
			if ($user_info['isadmin'] != 1) {
				$result = array('result_code' => '00004', 'msg' => '不是管理员');
				echo json_encode($result);
			} else {
				//添加用户
				$new_user = array('pwd' => md5("666666"));
				//此处应该加过滤
				$isuser = $user_model -> where('id=%d', $_POST['uid']) -> find();
				if (md5("666666") == $isuser['pwd']) {
					$result = array('result_code' => '20001', 'msg' => '修改成功');
					echo json_encode($result);
				} else {
					if ($isuser['phone'] == 'admin') {
						$result = array('result_code' => '10001', 'msg' => '超级管理员密码不可被重置，请联系数据库管理人员');
						echo json_encode($result);
					} else {
						$isadd = $user_model -> where('id=%d', $_POST['uid']) -> save($new_user);

						if ($isadd) {
							$result = array('result_code' => '20001', 'msg' => '重置成功');
							echo json_encode($result);
						} else {
							$result = array('result_code' => '10001', 'msg' => '重置失败' . M("User") -> getLastSql());
							echo json_encode($result);
						}
					}
				}
			}
		}
	}

	public function getuserinfo() {
		$user_model = M('User');
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();

		if ($user_info['token'] != $_POST['token']) {
			$result = array('result_code' => '00001', 'msg' => '登录超时或者其他设备登录，您被迫下线');
			echo json_encode($result);
		} else {
			if ($user_info['isadmin'] != 1) {
				$result = array('result_code' => '00004', 'msg' => '不是管理员');
				echo json_encode($result);
			} else {
				$isadd = $user_model -> where('id=%d', $_POST['id']) -> find();
				echo json_encode($isadd);
			}
		}
	}

	public function getalluser() {
		$user_model = M('User');
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();

		if ($user_info['token'] != $_POST['token']) {
			$result = array('result_code' => '00001', 'msg' => '登录超时或者其他设备登录，您被迫下线');
			echo json_encode($result);
		} else {
			if ($user_info['isadmin'] != 1) {
				$result = array('result_code' => '00004', 'msg' => '不是管理员', 'phone' => session('phone'), 'token_y' => $_POST['token'], 'token_n' => $user_info['token']);
				echo json_encode($result);
			} else {
				$isadd = $user_model -> select();
				echo json_encode($isadd);
			}
		}
	}

	//添加通知
	public function addtongzhi() {
		$user_model = M('User');
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();

		if ($_POST['token'] != $user_info['token']) {
			$result = array('result_code' => '00001', 'msg' => '登录超时或者其他设备登录，您被迫下线');
			echo json_encode($result);
		} else {
			if ($user_info['isadmin'] != 1) {
				$result = array('result_code' => '00004', 'msg' => '不是管理员');
				echo json_encode($result);
			} else {
				//添加用户
				$tongzhi_model = M('Tongzhi');
				$new_user = array('title' => $_POST['title'], 'content' => replace_special_chars($_POST['content']), 'createtime' => date("Y-m-d H:i:s", time()), 'type' => $_POST['type'], 'subtitle' => $_POST['subtitl'], 'icon' => $_POST['icon'], 'isup' => $_POST['isup'], 'paixu' => 1);
				$tong_list = $tongzhi_model->where("type='%s'",$_POST['type'])->select();
				if($_POST['isup']==1){
					for($i=0;$i<count($tong_list);$i++){
						$tongzhi_model->where('id=%d',$tong_list[$i]['id'])->save(array('isup'=>0));	
					}
				}
				//此处应该加过滤
				$isadd = $tongzhi_model -> add($new_user);
				if ($isadd) {
					$result = array('result_code' => '20001', 'msg' => '添加成功');
					echo json_encode($result);
				} else {
					$result = array('result_code' => '10001', 'msg' => '添加失败');
					echo json_encode($result);
				}
			}
		}
	}

	public function addwenjian() {
		$user_model = M('User');
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();

		if ($user_info['token'] != $_POST['token']) {
			$result = array('result_code' => '00001', 'msg' => '登录超时或者其他设备登录，您被迫下线');
			echo json_encode($result);
		} else {
			if ($user_info['isadmin'] != 1) {
				$result = array('result_code' => '00004', 'msg' => '不是管理员');
				echo json_encode($result);
			} else {
				//添加用户
				$new_user = array('title' => $_POST['title'], 'content' => replace_special_chars($_POST['content']), 'createtime' => date("Y-m-d H:i:s", time()), 'file' => 1, 'type' => 'aa', 'tag' => $_POST['tag'], 'descrp' => "dd", 'r' => $_POST['r'], 'g' => $_POST['g'], 'b' => $_POST['b']);
				$tongzhi_model = M('Wenjian');
				//此处应该加过滤
				$isadd = $tongzhi_model -> add($new_user);
				if ($isadd) {
					$result = array('result_code' => '20001', 'msg' => '添加成功');
					echo json_encode($result);
				} else {
					$result = array('result_code' => '10001', 'msg' => '添加失败' . M('Wenjian') -> getLastSql());
					echo json_encode($result);
				}
			}
		}
	}

	public function modifywenjian() {
		$user_model = M('User');
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();

		if ($user_info['token'] != $_POST['token']) {
			$result = array('result_code' => '00001', 'msg' => '登录超时或者其他设备登录，您被迫下线');
			echo json_encode($result);
		} else {
			if ($user_info['isadmin'] != 1) {
				$result = array('result_code' => '00004', 'msg' => '不是管理员');
				echo json_encode($result);
			} else {
				//添加用户
				$new_user = array('title' => $_POST['title'], 'content' => replace_special_chars($_POST['content']), 'createtime' => date("Y-m-d H:i:s", time()), 'tag' => $_POST['tag'], 'descrp' => $_POST['descrp'], 'r' => $_POST['r'], 'g' => $_POST['g'], 'b' => $_POST['b']);
				$tongzhi_model = M('Wenjian');
				//此处应该加过滤
				$isadd = $tongzhi_model -> where('id=%d', $_POST['id']) -> save($new_user);
				if ($isadd) {
					$result = array('result_code' => '20001', 'msg' => '修改成功');
					echo json_encode($result);
				} else {
					$result = array('result_code' => '10001', 'msg' => '修改失败');
					echo json_encode($result);
				}
			}
		}
	}

	public function get_img_url() {
		$imgid = $_POST['imgid'];
		$img_model = M('Img');
		$img_info = $img_model -> where('id=%d', $imgid) -> find();
		if (is_null($img_info)) {
			$result = array('result_code' => '00011', 'msg' => '图片未找到');
			echo json_encode($result);
		} else {
			$img_info['url'] = substr($img_info['url'], 24);
			echo json_encode($img_info);
		}
	}

	public function delwenjian() {
		$user_model = M('User');
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();

		if ($user_info['token'] != $_POST['token']) {
			$result = array('result_code' => '00001', 'msg' => '登录超时或者其他设备登录，您被迫下线');
			echo json_encode($result);
		} else {
			if ($user_info['isadmin'] != 1) {
				$result = array('result_code' => '00004', 'msg' => '不是管理员');
				echo json_encode($result);
			} else {
				//添加用户
				$tongzhi_model = M('Wenjian');
				//此处应该加过滤
				$isadd = $tongzhi_model -> where('id=%d', $_POST['id']) -> delete();
				if ($isadd) {
					$result = array('result_code' => '20001', 'msg' => '删除成功');
					echo json_encode($result);
				} else {
					$result = array('result_code' => '10001', 'msg' => '删除失败');
					echo json_encode($result);
				}
			}
		}
	}

	public function getallwenjian() {
		$user_model = M('User');
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();

		if ($user_info['token'] != $_POST['token']) {
			$result = array('result_code' => '00001', 'msg' => '登录超时或者其他设备登录，您被迫下线');
			echo json_encode($result);
		} else {
			if ($user_info['isadmin'] != 1) {
				$result = array('result_code' => '00004', 'msg' => '不是管理员');
				echo json_encode($result);
			} else {
				//添加用户
				$tongzhi_model = M('Wenjian');
				//此处应该加过滤
				$isadd = $tongzhi_model -> select();
				//var_dump($isadd);
				$data = array();
				if (!is_null($isadd)) {
					for ($i = 0; $i < count($isadd); $i++) {
						$isadd[$i]['content'] =  urldecode(base64_decode($isadd[$i]['content']));
						array_unshift($data,$isadd[$i]);
					}
				}
				//var_dump($data);
				echo  json_encode($data);
			}
		}
	}

	public function getonewenjian() {
		$user_model = M('User');
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();

		if ($user_info['token'] != $_POST['token']) {
			$result = array('result_code' => '00001', 'msg' => '登录超时或者其他设备登录，您被迫下线');
			echo json_encode($result);
		} else {
			if ($user_info['isadmin'] != 1) {
				$result = array('result_code' => '00004', 'msg' => '不是管理员');
				echo json_encode($result);
			} else {
				//添加用户
				$new_user = array('title' => $_POST['title'], 'content' => $_POST['content'], 'createtime' => date("Y-m-d H:i:s", time()), 'tag' => $_POST['tag'], 'descrp' => $_POST['descrp'], 'r' => $_POST['r'], 'g' => $_POST['g'], 'b' => $_POST['b']);
				$tongzhi_model = M('Wenjian');
				//此处应该加过滤
				$isadd = $tongzhi_model -> where('id=%d', $_POST['wenjian']) -> find();
				$isadd['content'] = htmlspecialchars_decode($isadd['content']);
				echo json_encode($isadd);
			}
		}
	}

	public function addupdate() {
		$user_model = M('User');
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();

		if ($user_info['token'] != $_POST['token']) {
			$result = array('result_code' => '00001', 'msg' => '登录超时或者其他设备登录，您被迫下线');
			echo json_encode($result);
		} else {
			if ($user_info['isadmin'] != 1) {
				$result = array('result_code' => '00004', 'msg' => '不是管理员');
				echo json_encode($result);
			} else {
				//添加用户
				$new_user = array('type' => $_POST['version'], 'xinxi' => $_POST['descrp'], 'isnew' => $_POST['isnew'], 'url' => $_POST['url']);
				$tongzhi_model = M('New');
				//此处应该加过滤
				$isadd = $tongzhi_model -> add($new_user);
				if ($isadd) {
					$result = array('result_code' => '20001', 'msg' => '发布成功');
					echo json_encode($result);
				} else {
					$result = array('result_code' => '10001', 'msg' => '发布失败');
					echo json_encode($result);
				}
			}
		}
	}

	public function getallupdate() {
		$user_model = M('User');
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();

		if ($user_info['token'] != $_POST['token']) {
			$result = array('result_code' => '00001', 'msg' => '登录超时或者其他设备登录，您被迫下线');
			echo json_encode($result);
		} else {
			if ($user_info['isadmin'] != 1) {
				$result = array('result_code' => '00004', 'msg' => '不是管理员');
				echo json_encode($result);
			} else {
				//添加用户
				$new_user = array('type' => $_POST['version'], 'xinxi' => $_POST['descrp'], 'isnew' => $_POST['isnew'], 'url' => $_POST['url']);
				$tongzhi_model = M('New');
				//此处应该加过滤
				$isadd = $tongzhi_model -> select();
				echo json_encode($isadd);
			}
		}
	}

	public function modifytongzhi() {
		$user_model = M('User');
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();

		if ($user_info['token'] != $_POST['token']) {
			$result = array('result_code' => '00001', 'msg' => '登录超时或者其他设备登录，您被迫下线');
			echo json_encode($result);
		} else {
			if ($user_info['isadmin'] != 1) {
				$result = array('result_code' => '00004', 'msg' => '不是管理员');
				echo json_encode($result);
			} else {
				//添加用户
				$tongzhi_model = M('Tongzhi');
				
				$tz = $tongzhi_model -> where('id=%d', $_POST['id']) -> find();
				$new_user = array('title' => $_POST['title'], 'content' => replace_special_chars($_POST['content']), 'createtime' => $tz['createtime'], 'type' => $_POST['type'], 'icon' => $_POST['icon'], 'subtitle' => $_POST['subtitl'], 'paixu' => 1, 'isup' => $_POST['isup']);
				$tong_list = $tongzhi_model->where("type='%s'",$_POST['type'])->select();
				if($_POST['isup']==1){
					for($i=0;$i<count($tong_list);$i++){
						$tongzhi_model->where('id=%d',$tong_list[$i]['id'])->save(array('isup'=>0));	
					}
				}
				//此处应该加过滤
				$isadd = $tongzhi_model -> where('id=%d', $_POST['id']) -> save($new_user);
				if ($isadd) {
					$result = array('result_code' => '20001', 'msg' => '修改成功');
					echo json_encode($result);
				} else {
					$result = array('result_code' => '10001', 'msg' => '修改失败' . M("Tongzhi") -> getLastSql());
					echo json_encode($result);
				}
			}
		}
	}

	public function ordertongzhi() {
		$user_model = M('User');
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();

		if ($user_info['token'] != $_POST['token']) {
			$result = array('result_code' => '00001', 'msg' => '登录超时或者其他设备登录，您被迫下线');
			echo json_encode($result);
		} else {
			if ($user_info['isadmin'] != 1) {
				$result = array('result_code' => '00004', 'msg' => '不是管理员');
				echo json_encode($result);
			} else {
				//添加用户
				$new_user = array('order' => $_POST['order']);
				$tongzhi_model = M('Tongzhi');
				//此处应该加过滤
				$isadd = $tongzhi_model -> where('id=%d', $_POST['id']) -> save($new_user);
				if ($isadd) {
					$result = array('result_code' => '20001', 'msg' => '排序成功');
					echo json_encode($result);
				} else {
					$result = array('result_code' => '10001', 'msg' => '排序失败');
					echo json_encode($result);
				}
			}
		}
	}

	public function deletetongzhi() {
		$user_model = M('User');
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();

		if ($user_info['token'] != $_POST['token']) {
			$result = array('result_code' => '00001', 'msg' => '登录超时或者其他设备登录，您被迫下线');
			echo json_encode($result);
		} else {
			if ($user_info['isadmin'] != 1) {
				$result = array('result_code' => '00004', 'msg' => '不是管理员');
				echo json_encode($result);
			} else {
				//添加用户
				$tongzhi_model = M('Tongzhi');
				//此处应该加过滤
				$isadd = $tongzhi_model -> where('id=%d', $_POST['id']) -> delete();
				if ($isadd) {
					$result = array('result_code' => '20001', 'msg' => '删除成功');
					echo json_encode($result);
				} else {
					$result = array('result_code' => '10001', 'msg' => '删除失败');
					echo json_encode($result);
				}
			}
		}
	}

	public function data() {
		$type = $_POST['type'];
		$data = '';
		$cond = '';
		$tongzhi_model = M('Tongzhi');
		switch ($type) {
			case 'Tongzhi' :
				$cond = 'tongzhi';
				$cond_cond = array("type" => "tongzhi", "isup" => 1);
				$data = $tongzhi_model -> where("type='%s'", $cond) -> limit(24 * ($_POST['yeshu'] - 1), 24) -> order('createtime desc') -> select();
				$a_data = $tongzhi_model -> where($cond_cond) -> find();
				if (!is_null($data)) {
					for ($i = 0; $i < count($data); $i++) {
						$data[$i]['content'] = base64_decode($data[$i]['content']);
					}
				} else {
					$data = array();
				}
				if (!is_null($a_data)) {
					if ($_POST['yeshu'] == 1) {
						$a_data['content'] = base64_decode($a_data['content']);
						array_unshift($data, $a_data);
					}
				}
				break;
			case 'Huiwu' :
				$cond = 'huiwu';
				$cond_cond = array("type" => "huiwu", "isup" => 1);
				$data = $tongzhi_model -> where("type='%s'", $cond) -> limit(24 * ($_POST['yeshu'] - 1), 24) -> order('createtime desc') -> select();
				$a_data = $tongzhi_model -> where($cond_cond) -> find();
				if (!is_null($data)) {
					for ($i = 0; $i < count($data); $i++) {
						$data[$i]['content'] = base64_decode($data[$i]['content']);
					}
				} else {
					$data = array();
				}
				if (!is_null($a_data)) {
					if ($_POST['yeshu'] == 1) {
						$a_data['content'] = base64_decode($a_data['content']);
						array_unshift($data, $a_data);
					}
				}
				break;
			case 'Dongtai' :
				$cond = 'dongtai';
				$cond_cond = array("type" => "dongtai", "isup" => 1);
				$data = $tongzhi_model -> where("type='%s'", $cond) -> limit(24 * ($_POST['yeshu'] - 1), 24) -> order('createtime desc') -> select();
				$a_data = $tongzhi_model -> where($cond_cond) -> find();
				if (!is_null($data)) {
					for ($i = 0; $i < count($data); $i++) {
						$data[$i]['content'] = base64_decode($data[$i]['content']);
					}
				} else {
					$data = array();
				}
				if (!is_null($a_data)) {
					if ($_POST['yeshu'] == 1) {
						$a_data['content'] = base64_decode($a_data['content']);
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

	//导航接口

	public function maptitleadd() {
		$user_model = M('User');
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();

		if ($user_info['token'] != $_POST['token']) {
			$result = array('result_code' => '00001', 'msg' => '登录超时或者其他设备登录，您被迫下线');
			echo json_encode($result);
		} else {
			if ($user_info['isadmin'] != 1) {
				$result = array('result_code' => '00004', 'msg' => '不是管理员');
				echo json_encode($result);
			} else {
				//添加用户
				$new_user = array('time' => $_POST['time'], 'data' => $_POST['data'], 'content' => $_POST['content'], 'position' => $_POST['position'], 'person' => $_POST['person'], 'jingdu' => $_POST['longitude'], 'weidu' => $_POST['latitude']);
				$schedule_model = M('Schedule');
				//此处应该加过滤
				$isadd = $schedule_model -> add($new_user);
				if ($isadd) {
					$result = array('result_code' => '20001', 'msg' => '添加成功');
					echo json_encode($result);
				} else {
					$result = array('result_code' => '10001', 'msg' => '添加失败');
					echo json_encode($result);
				}
			}
		}
	}

	public function modifyschedule() {
		$user_model = M('User');
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();

		if ($user_info['token'] != $_POST['token']) {
			$result = array('result_code' => '00001', 'msg' => '登录超时或者其他设备登录，您被迫下线');
			echo json_encode($result);
		} else {
			if ($user_info['isadmin'] != 1) {
				$result = array('result_code' => '00004', 'msg' => '不是管理员');
				echo json_encode($result);
			} else {
				//添加用户
				$new_user = array(
				 'time' => $_POST['time'], 
				 'data' => $_POST['data'],
				 'content' => $_POST['content'], 
				 'position' => $_POST['position'], 
				 'person' => $_POST['person'], 
				 'jingdu' => $_POST['longitude'], 
				 'weidu' => $_POST['latitude']);
				$schedule_model = M('Schedule');
				//此处应该加过滤
				$isadd = $schedule_model -> where('id=%d', $_POST['id']) -> save($new_user);
				if ($isadd) {
					$result = array('result_code' => '20001', 'msg' => '修改成功');
					echo json_encode($result);
				} else {
					$result = array('result_code' => '10001', 'msg' => '修改失败' . M("Schedule") -> getLastSql());
					echo json_encode($result);
				}
			}
		}
	}

	public function delschedule() {
		$user_model = M('User');
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();

		if ($user_info['token'] != $_POST['token']) {
			$result = array('result_code' => '00001', 'msg' => '登录超时或者其他设备登录，您被迫下线');
			echo json_encode($result);
		} else {
			if ($user_info['isadmin'] != 1) {
				$result = array('result_code' => '00004', 'msg' => '不是管理员');
				echo json_encode($result);
			} else {
				$schedule_model = M('Schedule');
				//此处应该加过滤
				$isadd = $schedule_model -> where('id=%d', $_POST['id']) -> delete();
				if ($isadd) {
					$result = array('result_code' => '20001', 'msg' => '删除成功');
					echo json_encode($result);
				} else {
					$result = array('result_code' => '10001', 'msg' => '删除失败');
					echo json_encode($result);
				}
			}
		}
	}

	public function getSchedule() {
		$schedule_model = M('Schedule');
		$schedule_all = $schedule_model -> order('paixu') -> select();
		//$lastdata = "";
		$array = array();
		foreach ($schedule_all as $k => $v) {
			$result[$v['data']][] = $v;
		}
		echo json_encode($result);

	}

	public function img_upload() {
		$user_model = M("User");
		$user_info = $user_model -> where("token='%s'", $_POST['token']) -> find();
		if ($_POST['token'] == $user_info['token']) {
			import('ORG.Net.UploadFile');
			$upload = new UploadFile();
			// 实例化上传类
			$upload -> maxSize = 31457280;
			// 设置附件上传大小
			$upload -> allowExts = array('jpg', 'gif', 'png', 'jpeg','mp3','wav','WAV',"MP3");
			// 设置附件上传类型
			$upload -> savePath = $_SERVER['DOCUMENT_ROOT'] . '/qgfdyjnds/public/img/photo/';
			// 设置附件上传目录
			$info = $upload -> upload();
			if (!$info) {// 上传错误提示错误信息
				echo json_encode(array('resultcode' => '02001', 'msg' => $upload -> getErrorMsg()));
			} else {// 上传成功 获取上传文件信息
				$info = $upload -> getUploadFileInfo();
				$url = '';
				foreach ($info as $file) {
					$url .= $file['savepath'] . $file['savename'];
				}
				$imgs = M('Img');
				$data = array('url' => $url, 'uid' => $user_info['id'], 'ctime' => date('Y-m-d H:i:s', time()));
				$isupload = $imgs -> add($data);
				if ($isupload) {
					echo json_encode(array('result_code' => '00201', 'img_url' => $url, 'id' => $isupload, 'msg' => '上传成功'));
				} else {
					echo json_encode(array('result_code' => '00202', 'msg' => '上传失败'));
				}
			}
		} else {
			echo json_encode(array('result_code' => '01001', 'msg' => 'token超时'));
		}
	}
	

}

function replace_special_chars($string) {
	//	$converted = strtr($string, array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES)));
	//	$string = trim($converted, chr(0xc2) . chr(0xa0));
	$string = str_replace('&nbsp;', '', $string);
	return base64_encode($string);
	//return $string;
}
?>
