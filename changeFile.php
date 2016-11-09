<?php 

header("content-type:text/html;charset=utf-8");

class changeFile
{
	//单例模式，禁止多次实例化对象，禁止克隆
	private function __construct(){

	}

	public static function getInstance(){

		static $obj = null;

		return is_null($obj) ? new self() : $obj;

	}

	public function __clone(){

		trigger_error("clone is not allow!!!" ,E_USER_ERROR);

	}

	//导入的文件数据
	public function get_content($filename){

		$handel = fopen("$filename","r");

		if($handel){

			$con ="";

			while ($str = fgets($handel)) {

				$con .= $str;

			}
			return $con;

		}else{

			die("undefind this file");
		}

		fclose($handel);
	}

	//去除需要解析的字符串中的特殊字符
	private function replaceSpecialChar($str){

	    $regex = "/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";

	    return preg_replace($regex,"",$str);

	}

	//循环读取接收的字符串并格式化输出，存储（单选）
	public function radio_conv($content ,$star ,$test_type ,$know_id ,$diff_num ,$out_file){

		$handel = fopen($out_file,"wb");

		while(strpos("$content",$star.".")!==false){

			$str = $this -> f_init($content ,$star);

			$star_anwser= strpos("$str" ,"*");

			$end_anwser = strrpos($str, "*");

			$anwser_length = $end_anwser - $star_anwser +1;

			$anwser_tmp = substr("$str",$star_anwser,$anwser_length);

			$anwser = $this -> replaceSpecialChar($anwser_tmp);

			$str = str_replace("$anwser_tmp","",$str);

			$option_star = strpos($str ,"A.");

			$option = substr($str ,$option_star);

			$question = substr($str, 0 ,$option_star);

			$question = str_replace(" ","&nbsp;",$question);

			$option = str_replace(" ","&nbsp;",htmlentities($option));

			$this -> write($handel,$test_type ,$question ,$option ,$anwser ,$know_id ,$diff_num);

			$star++;
		}

		$this -> close_file($handel);
	}

	//一行一行写入文件（覆盖写）
	protected function write($handel, $test_type ,$question ,$option ,$anwser ,$know_id ,$diff_num){
		fwrite($handel ,"======\r\n");
		fwrite($handel ,$test_type."\r\n");
		fwrite($handel ,"------\r\n");
		fwrite($handel ,"<pre>".$question."</pre>\r\n");
		fwrite($handel ,"------\r\n");
		fwrite($handel ,"<pre>".$option."</pre>\r\n");
		fwrite($handel ,"------\r\n4\r\n");
		fwrite($handel ,"------\r\n");
		fwrite($handel ,$anwser."\r\n");
		fwrite($handel ,"------\r\n");
		fwrite($handel ,"暂无\r\n");
		fwrite($handel ,"------\r\n");
		fwrite($handel ,$know_id."\r\n");
		fwrite($handel ,"------\r\n");
		fwrite($handel ,$diff_num."\r\n");
		fwrite($handel ,"======\r\n");
	}

	//接收数据的初始化操作
	protected function f_init($content ,$star){

		$first_position = strpos("$content" ,$star.".");

		$next = $star + 1;

		$next_position = strpos("$content" ,$next.".");

		$length = $next_position - $first_position;

		$str = substr($content ,$first_position,$length);

		$str = iconv("gbk","utf-8",$str);

		return $str;
	}

	//循环读取接收的字符串并格式化输出，存储（主观题）
	public function program_conv($content ,$star ,$test_type ,$know_id ,$diff_num ,$out_file){

		$question = "";

		$anwser = "";

		$option = "";

		$handel = fopen($out_file,"wb");

		while(strpos("$content",$star.".")!==false){

			$str = $this -> f_init($content ,$star);

			$question = $str;

			$question = str_replace(" ","&nbsp;",$question);

			$option = str_replace(" ","&nbsp;",htmlentities($option));

			$this -> write($handel,$test_type ,$question ,$option ,$anwser ,$know_id ,$diff_num);
			
			$star++;
		}

		$this -> close_file($handel);
	}

	//关闭文件资源
	protected function close_file($handel){

		fclose($handel);

		echo "OK";
	}

}

	//接收前台传递数据
	$data = $_POST;

	$obj = changeFile::getInstance();

	$string = $obj -> get_content("./{$data['in_name']}");

	//导出需要批量导入的数据到文件中
	//此处传参为：1、上一步解析的字符串
	//			  2、试题开始的题号
	//			  3、题目类型
	//			  4、知识点ID
	//			  5、题目难度系数，1、2、3、分别代表易、中、难
	//			  6、需要导出的文件（.txt）
	
	if($data['type'] == 1){

		$obj -> radio_conv($string ,1 ,$data['type'] ,$data['konw_id'] ,$data['diff_num'],$data['out_name']);

	}else{

		$obj -> program_conv($string ,1 ,$data['type'] ,$data['konw_id'] ,$data['diff_num'],$data['out_name']);

	}