<?php
/*
	自研紧凑型PHP导出代码
	var_exports		导出函数
		$data		要导出的变量
		$t			基准tab值，最终导出字符串的tab将在此基础上进行缩进
	isAssocArray	判断是否为数值型数组
*/
function var_exports($data,$t=''){
	if(is_numeric($data)){
		return $data;
	}elseif(is_string($data)){
		$data=str_replace(array('\\',"'"),array('\\\\',"\\'"),$data);
		return "'$data'";
	}elseif(is_bool($data)){
		return $data?'true':'false';
	}
	if(!is_array($data)){
		
		return '';
	}
	if(empty($data)){
		return 'array()';
	}
	$string='array(';
	if(isAssocArray($data)){
		//数值型数组
		foreach($data as $value){
			$string.="\n{$t}\t".var_exports($value,$t."\t").',';
		}
		$string.="\n{$t})";
	}else{
		if(count($data)>5){
			foreach($data as $key=>$value){
				$string.="\n{$t}\t'{$key}'=>".var_exports($value,$t."\t").',';
			}
			$string.="\n{$t})";
		}else{
			foreach($data as $key=>$value){
				$string.="'{$key}'=>".var_exports($value,$t).',';
			}
			$string.=')';
		}
	}
	
	return $string;
}
function isAssocArray($arr){
	if(!is_array($arr)){
		return false;
	}
	if(!isset($arr[0])){
		//PHP会判断 0 和任意字符串相等，因此下面的判断无法正确判断只有一个元素的关联数组，此处补充判断！
		return false;
	}
	$arr=array_keys($arr);
	$index = 0;
	foreach ($arr as $key){
		if ($index++ != $key) return false;
	}
	return true;
}
