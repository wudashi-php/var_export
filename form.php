<?php
/*
	表单校验
	校验内容：
		数据类型是否符合要求	整数、浮点数、字符串、数组、对象、布尔值、枚举
		数据范围是否符合要求，例如数字的大小是否符合要求，字符串的长度是否符合要求，字符串是否满足正则校验
		
	问题：
		枚举类型如果是字符串，用in_array匹配时可能会出现误差，例如0会匹配通过。
		如果用严格匹配也不好，因为如果枚举类型是数字，提交的值极有可能是字符串类型的数字，这时肯定是需要通过的。
		可以这样，增加一个数据类型限制。这样用起来麻烦，最好直接在内部判断。如果待匹配值是0，则逐个判断枚举项是否为0或者'0' is_numeric && ==0。
		
	使用方法
		见下面的代码
	总结，声明数组以字段名为键名，以类型限制声明作为键值。类型限制支持两种形式，一是简单的类型字符串，二是类型声明数组。
		类型声明数组第一个元素为类型标识，第二个元素以上为额外限制内容，例如数字的取值范围，对象的属性类型限制等
	
	已支持的数据校验类型
	int		整数
	float	浮点数
	number	数字
		上面三种支持字符串类型数字
	string	字符串
	enum	枚举
	bool	布尔值
	preg	正则匹配
		下面两种为复合数据类型。支持对下级数据做进一步规范
	arr		数组
	obj		对象
		以下为正则匹配拓展类型
	word	单词字符串（由字母数字下划线组成的字符串）
	mobile	手机号
	email	邮箱
	
	其他数据类型
	如需增加更多数据类型校验，可增加相应函数。例如增加身份证号码校验，数据类型标识为 idcard
		public function check_idcard($data){
			//这里执行校验代码，成功返回true，失败返回false
		}
*/
$form=new form_check();
//方法1，对收到的整个表单对象进行校验。调用check方法，传入表单对象，校验数组，错误消息数组（可不传）。
$data=array(
	'field1'=>'2222',//整数
	'field2'=>'123ss@qq.com',//浮点数
	'field3'=>332,//数字（整数、浮点数均可）
	'field4'=>1,//大于等于0，小于等于3的整数
	'field5'=>'sd',//字符串
	'field6'=>'fgd',//字符串长度大于等于0小于等于3
	'field7'=>'sd',//正则匹配。由字母数字下划线组成的字符串
	'field8'=>'alipay',//枚举
	'field9'=>array('arr','jhg'),//数组
	'field10'=>array(//对象
		'field11'=>322,//整数
		'field22'=>1.1,//浮点数
		'field33'=>23,//数字（整数、浮点数均可）
	),
	'field11'=>true,//布尔值
	'field19'=>array(
		array('field11'=>11,'field22'=>'1122','field33'=>'123ss@qq.com'),
		array('field11'=>32,'field22'=>'khjktyg','field33'=>'12334ss@qq.com'),
	),
);
$check=[
	'field1'=>'int',//整数
	'field2'=>'email',//浮点数
	'field3'=>'number',//数字（整数、浮点数均可）
	'field4'=>['int',0,3],//大于等于0，小于等于3的正整数
	'field5'=>'string',//字符串
	'field6'=>['string',0,3],//字符串长度大于等于0小于等于3
	'field7'=>['preg','/^\w+$/'],//正则匹配。由字母数字下划线组成的字符串
	'field8'=>['enum',['wechat','alipay','credit']],//枚举
	'field9'=>['arr',['preg','/^\w+$/']],//第二项参数为子元素的类型声明数组
	'field10'=>['obj',[//对象
		'field11'=>'int',//整数
		'field22'=>'float',//浮点数
		'field33'=>'number',//数字（整数、浮点数均可）
	]],
	'field11'=>'bool',//布尔值
	'field19'=>[
		'arr',//数组
		[
			'obj',//对象
			[
				'field11'=>'int',//整数
				'field22'=>'word',//浮点数
				'field33'=>'email',//数字（整数、浮点数均可）
			]
		]
	]
];

$form->check($data,$check);
//方法二。校验单个数据，调用相应数据类型校验方法
if(!$form->check_mobile('13566666667')){
	exit('手机号不合法');
}
;
exit('success');
function error($message){
	exit($message);
}
class form_check{
	public function check($data,$check,$message=array(),$none_message=array()){
		if(!$none_message){
			$none_message=$message;
		}
		foreach($check as $key=>$params){
			if(!isset($data[$key])){
				error(is_string($none_message[$key])?$none_message[$key]:"字段{$key}数据不合法！");
			}
			if(is_string($params)){
				$check_method='check_'.$params;
				$check_params=[];
			}elseif(is_array($params)){
				$check_method='check_'.array_shift($params);
				$check_params=$params;
			}else{
				error("字段{$key}校验参数不合法！");
				return;
			}
			if(!method_exists($this,$check_method)){
				error("字段{$key}校验类型不存在！");
				return;
			}
			if(!$this->$check_method($data[$key],$check_params,$message[$key],$none_message[$key])){
				$res=is_string($message[$key])?$message[$key]:"字段{$key}数据不合法！";
				error($res);
			}
		}
		return true;
	}
	//整数或者整数字符串
	public function check_int($data,$params=[]){
		if(!is_numeric($data)){
			return false;
		}
		if(is_string($data)){
			if(strpos($data,'.')!==false){
				return false;
			}
		}else{
			if(!is_int($data)){
				return false;
			}
		}
		if(!$params){
			return true;
		}
		if(count($params)==1){
			if($data<$params[0]){
				return false;
			}
		}else{
			if($data<$params[0] || $data>$params[1]){
				return false;
			}
		}
		return true;
	}
	//浮点数或者浮点数字符串
	public function check_float($data,$params=[]){
		if(!is_numeric($data)){
			return false;
		}
		if(is_string($data)){
			if(strpos($data,'.')===false){
				return false;
			}
		}else{
			if(!is_float($data)){
				return false;
			}
		}
		if(!$params){
			return true;
		}
		if(count($params)==1){
			if($data<$params[0]){
				return false;
			}
		}else{
			if($data<$params[0] || $data>$params[1]){
				return false;
			}
		}
		return true;
	}
	//数字或者数字字符串
	public function check_number($data,$params=[]){
		if(!is_numeric($data)){
			return false;
		}
		if(!$params){
			return true;
		}
		if(count($params)==1){
			if($data<$params[0]){
				return false;
			}
		}else{
			if($data<$params[0] || $data>$params[1]){
				return false;
			}
		}
		return true;
	}
	//布尔值
	public function check_bool($data){
		if(!is_bool($data)){
			return false;
		}
		
		return true;
	}
	//字符串
	public function check_string($data,$params=[]){
		if(!is_string($data)){
			return false;
		}
		if(!$params){
			return true;
		}
		$len=strlen($data);
		if(count($params)==1){
			if($len<$params[0]){
				return false;
			}
		}else{
			if($len<$params[0] || $len>$params[1]){
				return false;
			}
		}
		return true;
	}
	//枚举
	public function check_enum($data,$params=[]){
		$params=$params[0];
		if($data==0 && is_numeric($data)){
			foreach($params as $row){
				if($row==0 && is_numeric($row)){
					return true;
				}
			}
			return false;
		}
		if(in_array($data,$params)){
			return true;
		}
		return false;
	}
	//正则匹配
	public function check_preg($data,$params=[]){
		if(!is_string($data)){
			return false;
		}
		if(!$params){
			return true;
		}
		if(!preg_match($params[0],$data)){
			return false;
		}
		return true;
	}
	
	//单词，由字母数字下划线组成的字符串
	public function check_word($data){
		if(!is_string($data)){
			return false;
		}
		if(!preg_match('/^\w+$/',$data)){
			return false;
		}
		return true;
	}
	//手机号
	public function check_mobile($data){
		if(!is_string($data)){
			return false;
		}
		if(!preg_match('/^1[3456789]\d{9}$/',$data)){
			return false;
		}
		return true;
	}
	//邮箱
	public function check_email($data){
		if(!is_string($data)){
			return false;
		}
		if(!preg_match('/^\w[\w\.]*@\w+\.\w+$/',$data)){
			return false;
		}
		return true;
	}
	//对象
	public function check_obj($data,$params=[],$message=[],$none_message=[]){
		if(!is_array($data)){
			return false;
		}
		if(!$params){
			return true;
		}
		$check=$params[0];
		return $this->check($data,$check,$message,$none_message);
	}
	//数组
	public function check_arr($data,$params=[],$message=[],$none_message=[]){
		if(!is_array($data)){
			return false;
		}
		if(!$params){
			return true;
		}
		$params=$params[0];
		if(is_string($params)){
			$check_method='check_'.$params;
			$check_params=[];
		}elseif(is_array($params)){
			$check_method='check_'.array_shift($params);
			$check_params=$params;
		}
		foreach($data as $row){
			if(!$this->$check_method($row,$check_params,$message,$none_message)){
				return false;
			}
		}
		return true;
	}
}