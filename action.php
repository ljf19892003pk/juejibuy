<?php
header("Content-type:text/html;charset=utf-8");

$sourceurl = $_GET["u"];

if($sourceurl == ""){

  //echo "<p>输入京东商品链接，手机京东点击分享-复制链接</p>";
  exit();
}

$method = "jd.union.open.goods.promotiongoodsinfo.query"; //获取信息
$channel = "PC";
$type = 7;
$unionId = "1001465615"; //联盟ID
$webId = "1843478313"; //网站ID
$token = "";
$appkey = "9f30f6a0de87393e3c60d6046c1a1ba1";
$appSecret = "d1a1a56d8da842f78331db315b098acb";
$format = "json";
$sign_method = "md5";
$v = "1.0";
//$time = date('Y-m-d H:i:s');

$return_inf=[
	'goodsName' =>'',
	'unitPrice' => 0,
	'commisionRatioWl' => 0,
	'commisionRatioPc' => 0,
	'imgUrl'=> '',
	'clickURL'=> '',
];




$return_inf=get_goods_inf($sourceurl);
$return_inf['clickURL']=get_goods_link($sourceurl);

echo json_encode($return_inf);

function get_goods_inf($goodsID){

	$param = array();
	$data['skuIds'] = $goodsID; 
	$param['param_json'] = json_encode($data);
	$GoodsList = sign_method('jd.union.open.goods.promotiongoodsinfo.query',$param);
	$GoodsList = curl_post('https://router.jd.com/api', $GoodsList);
	$GoodsListV = json_decode($GoodsList,true);

	$GoodsListInf=json_decode($GoodsListV["jd_union_open_goods_promotiongoodsinfo_query_response"]["result"],true);
	//print_r($GoodsListInf['data'][0]);
	// foreach ($GoodsListInf['data'][0] as $key => $value) {
	// 	echo "<p>+++ $key ==> $value</p>";
	// }
	$return_inf['goodsName']=$GoodsListInf['data'][0]['goodsName'];
	$return_inf['unitPrice']=$GoodsListInf['data'][0]['unitPrice'];
	$return_inf['commisionRatioWl']=$GoodsListInf['data'][0]['commisionRatioWl'];
	$return_inf['commisionRatioPc']=$GoodsListInf['data'][0]['commisionRatioPc'];
	$return_inf['imgUrl']=$GoodsListInf['data'][0]['imgUrl'];
	
	return $return_inf;

}


function get_goods_link($goodsID){
	$param = array();
	$data['materialId'] = "https://item.jd.com/$goodsID.html";
	//$data['materialId'] = $goodsID;
	$data['siteId'] = "1843478313"; 
	$param_json["promotionCodeReq"]=$data;
	$param['param_json'] = json_encode($param_json);
	$GoodsList = sign_method('jd.union.open.promotion.common.get',$param);
	$GoodsList = curl_post('https://router.jd.com/api', $GoodsList);
//	echo $GoodsList;
	$GoodsListV = json_decode($GoodsList,true);
	//echo "<p></p>";
	//print_r(json_decode($GoodsListV["jd_union_open_promotion_common_get_response"]["result"],true));
    $GoodsLink=json_decode($GoodsListV["jd_union_open_promotion_common_get_response"]["result"],true);
    //return $GoodsLink;
	 if ($GoodsLink['code']==200){
		return $GoodsLink['data']['clickURL'];
	 }else{
	 	return 0;
	 }

}

function sign_method($method,$data=array()){
	global $appkey;
    global $appSecret;
	date_default_timezone_set("PRC");
	$sign_method = array(
					'app_key'=> $appkey,
					'timestamp'=> date('Y-m-d H:i:s'),
					'format' => 'json',
					'method' => $method,
					'v' => '1.0',
					'sign_method'=>'md5',
				);
	$sign_method = array_merge($sign_method,$data);
	ksort($sign_method);
	// var_dump($array_merge);die();
	$data = $appSecret;
	foreach ($sign_method as $key => $value) {
		//$data .= trim($key) .trim($value);
		$data .= $key.$value;
	}
	$data .= $appSecret;
	$sign_method['sign'] = strtoupper(md5($data));
	// echo "<pre>";
	// var_dump($sign_method);//die();
	return $sign_method;
}
  // post请求
function curl_post($url, $curlPost)
    {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    $result = curl_exec($ch);
	curl_close($ch);
	//$result=str_replace("\\","",$result);
	return $result;
}

?>