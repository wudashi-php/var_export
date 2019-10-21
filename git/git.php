<?php
$post=file_get_contents('php://input');
if(!$post){
	error('bad error');
}
$post=json_decode($post,true);
if(!$post || !$post['password'] || $post['password']!='722544'){
	error('bad password:'.$post['password']);
}
if($post['ref']!='refs/heads/master'){
	error('bad branch:'.$post['ref']);
}
if($post['commits']['0']['committer']['username']!='wudashi-php'){
	//error('bad user:'.$post['commits']['0']['committer']['username']);
}
$added=implode("\n\t",$post['commits']['0']['added']);
$removed=implode("\n\t",$post['commits']['0']['removed']);
$modified=implode("\n\t",$post['commits']['0']['modified']);
$log=shell_exec('cd /alidata/www/var_export && sudo /usr/local/git/bin/git pull origin master');
file_put_contents(__DIR__.'/git.log',"**********************************\n".date('Y/m/d H:i:s')."\npull logs:\n{$log}\nadded:\n\t{$added}\nremoved:\n\t{$removed}\nmodified:\n\t{$modified}\n",FILE_APPEND);
exit('success');
function error($msg){
	file_put_contents(__DIR__.'/error.log',date('Y/m/d H:i:s')."\n{$msg}\n",FILE_APPEND);
	exit('success');
}