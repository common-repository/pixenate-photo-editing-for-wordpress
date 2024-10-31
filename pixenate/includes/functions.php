<?php
//
// A collection of PHP functions used by pixenate/editor.php
//
//
// convert a url to an absolute file path - there's got to be a better way than this.
//
function pixenate_url2filepath($url)
{
	 $s = strpos($url, '//') + 2;

	 $s = $s + strpos(substr($url,$s),'/') ;

	 $rel = $_SERVER['DOCUMENT_ROOT']. substr($url,$s);

	 return $rel;
}
//
// Some hosts have curl some don't.
// See http://developer.yahoo.com/php/howto-reqRestPhp.html for a 
// description of the prerequisites of both curl and it's alternative,
// file_get_contents. Hat tip to Keola Donaghy ( http://www.culture-hack.com/ ) for the 
// info.
// 
function pixenate_can_get_remote_url(){

	 if (function_exists('curl_init')){
		  return true;
	 }
	 if (function_exists('file_get_contents')){
		  $allow_url_fopen = ini_get('allow_url_fopen');
		  if ($allow_url_fopen){
				return true;
		  }
	 }
	 return false;
}
// 
// get contents of a remote url
//
function pixenate_get_remote_url($url){

	 $result = false;

	 if (function_exists('curl_init')){

		  $ch = curl_init($url);
		  curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		  $result = curl_exec($ch);
		  curl_close($ch);
	 }else{
		  if (function_exists('file_get_contents')){
				$allow_url_fopen = ini_get('allow_url_fopen');
				if ($allow_url_fopen){
					 $result = file_get_contents($url);
				}
		  }
	 }
	 return $result;
}
?>
