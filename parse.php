<?php
$urlBase='http://www.xe.com/';
//$url='https://www.xe.com/currencytables/?from=USD'; old link!!!
$url='https://www.xe.com/currencytables/?from=USD&date=';
$content = getContent($urlBase,$url);

if (filesize('temp.dat')>180000) {
$data = getCurrencyRates($content);
echo $xml=getXml($data);
include('load_xml.php');
} else {
$to      = 'rivex@ukr.net';
$subject = 'temp.dat to small';
$message = 'error in file temp.dat!';
$headers = 'From: info@devincome.com' . "\r\n" .
    'Reply-To: info@devincome.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);
}

function getContent($urlBase,$url){
echo " My server time is " . date("Y-m-d h:i:sa");
//	$today = '2021-04-22';
//	date_default_timezone_set('America/Anchorage');
// 	$today = date("Y-m-d");
	$today = date_create(date("Y-m-d"))->modify('-1 days')->format('Y-m-d');
echo " New server time is " . $today . ".";

	$header[]='User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.114 Safari/537.36';
	$header[]='Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9';
	$header[]='Sec-Fetch-Site: none';
	$header[]='Sec-Fetch-Mode: navigate';
	$header[]='Sec-Fetch-User: ?1';
	$header[]='Sec-Fetch-Dest: document';
	$header[]='Accept-Language: en-US,en;q=0.9';
	$header[]='Connection: keep-alive';
	$cookies='';	

	$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . $today);
        #curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');
	#curl_setopt($ch, CURLOPT_URL, $urlBase);
	curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
	#curl_setopt($ch, CURLOPT_NOBODY, true); 
//	curl_setopt($ch, CURLOPT_PROXY, '191.102.232.130:3128');
	#$content = curl_exec($ch);
	#preg_match_all('#Set-Cookie: (.*);#U', $content, $m);    
	#$cookies = implode(';', $m[1]);
	//sleep(3);
	#$header[]='Referer: http://www.xe.com/';
	
	#curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	#if($cookies)
	#	curl_setopt($ch, CURLOPT_COOKIE,  $cookies);
	#curl_setopt($ch, CURLOPT_NOBODY, false); 
	$response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        
        $header = substr($response, 0, $header_size);
        $content = substr($response, $header_size);
	curl_close($ch);
        #--var_dump($content);

	file_put_contents('temp.dat', $content);
	return $content;
}
function getCurrencyRates($content){
	$data=array();
	$now = date("Y-m-d H:i:s");
	//<table class="currencytables__Table-sc-xlq26m-3 khkgVm"> new
	//<table class="sc-b85fe18f-3 eYpjCx"> new2
	//table class="sc-8b336fdc-3 foLGOz"> new3
	//<table class="sc-f2b5952d-3 hidSsf"> new4
	preg_match('#<table[^>]*class=["\']sc-b41ce832-3\shnscMu["\'][^>]*>(.*?)</table>#si',$content,$m);
	$m=preg_replace('#<!--.*?-->#si','',$m[1]);
	preg_match_all('#<tr[^>]*>(.*?)</tr>#si',$m,$m);
	for($i=1,$n=count($m[1]);$i<$n;$i++){
		preg_match_all('#<td[^>]*>(.*?)</td>#si',$m[1][$i],$v1);
		preg_match_all('#<a[^>]*>(.*?)</a>#si',$m[1][$i],$v2);

                #--echo "---\r\n";
                #--var_dump($v1);
                #--var_dump($v2);
                #--echo "===\r\n";

		if(!$v2[0]) {
			$data[$i-1]['code'] = str_replace('TOP','T0P',str_replace('Chinese Yuan Renminbi Offshore','CNH',trim(strip_tags($v1[1][0]))));
	   		$data[$i-1]['description'] = trim(strip_tags($v1[1][0]));
			$data[$i-1]['rate'] = str_replace(',','',trim(strip_tags($v1[1][1])));
			$data[$i-1]['update'] = $now;
		}
		else {
			$data[$i-1]['code'] = str_replace('TOP','T0P',str_replace('Chinese Yuan Renminbi Offshore','CNH',trim(strip_tags($v2[1][0]))));
			$data[$i-1]['description'] = trim(strip_tags($v1[1][0]));
			$data[$i-1]['rate'] = str_replace(',','',trim(strip_tags($v1[1][1])));
			$data[$i-1]['update'] = $now;                        
		}
	}
        	
	return $data;
}
function getXml($data){
	$dom=simplexml_load_string("<forex></forex>");
	foreach($data as $k=>$v){
		$currency=$dom->addChild("data");
		foreach($v as $k1=>$v1){
			$currency->addChild($k1,$v1);
		}
	}
	$xml=$dom->asXML('forex.xml');
	return $xml;
}

?>