<?php

   //========================================================================================\\
  // THIS SCRIPT IS FOR EDUCATION PURPOSE ONLY. Don't Sell this Script, This is 100% Free.    \\
 // Join Community https://t.me/cloudply, https://github.com/Himanshu8221                      \\ 
//==============================================================================================\\

$userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36 @https://t.me/cloudply';
$outputFile = __DIR__ . '/token.txt';

function generateGuestToken() {
    $hex = '0123456789abcdef';
    $token = '';
    $segments = [8,4,4,4,12];
    foreach ($segments as $len) {
        for($i=0;$i<$len;$i++) $token .= $hex[mt_rand(0,15)];
        $token .= ($len<12)?'-':''; 
    }
    return $token;
}

function curl_with_opts($url,$method='GET',$headers=[],$postData=null,$ua=''){
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    if(!empty($ua)) curl_setopt($ch,CURLOPT_USERAGENT,$ua);
    if(!empty($headers)) curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
    curl_setopt($ch,CURLOPT_TIMEOUT,20);
    if($method==='POST'){ 
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST,'POST'); 
        curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
    }
    $resp=curl_exec($ch);
    $httpcode=curl_getinfo($ch,CURLINFO_HTTP_CODE);
    $err=curl_error($ch);
    curl_close($ch);
    return [$resp,$httpcode,$err];
}

function fetchPlatformToken($ua){
    $url='https://www.zee5.com/live-tv/anmol-cinema-2/0-9-bigganga';
    list($resp,$http,$err)=curl_with_opts($url,'GET',[],null,$ua);
    if($resp===false || $http!==200) exit("Cannot fetch platform token. HTTP:$http, CURL:$err");
    if(preg_match('/"gwapiPlatformToken"\s*:\s*"([^"]+)"/',$resp,$m)) return $m[1];
    exit("Platform token not found");
}

function fetchVideoToken($ua){
    $guest = generateGuestToken();
    $platform = fetchPlatformToken($ua);
    $ch_id='0-9-bigganga';
    $api="https://spapi.zee5.com/singlePlayback/getDetails/secure?channel_id=$ch_id&device_id=$guest&platform_name=desktop_web&translation=en&user_language=en,hi,hr,pa&country=IN&state=DL&app_version=4.26.1&user_type=guest&check_parental_control=false&ppid=$guest&version=12";
    $headers=[
        'accept: application/json',
        'content-type: application/json',
        'origin: https://www.zee5.com',
        'referer: https://www.zee5.com/',
        "user-agent: $ua"
    ];
    $post=json_encode([
        'x-access-token'=>$platform,
        'X-Z5-Guest-Token'=>$guest,
        'x-dd-token'=>''
    ]);
    list($resp,$http,$err)=curl_with_opts($api,'POST',$headers,$post,$ua);
    if($resp===false || $http!==200) exit("API failed. HTTP:$http, CURL:$err");
    $data=json_decode($resp,true);
    if(!$data || !isset($data['keyOsDetails']['video_token'])) exit("video_token missing");
    return $data['keyOsDetails']['video_token'];
}

function fetchHdntlFull($videoUrl,$ua){
    list($resp,$http,$err)=curl_with_opts($videoUrl,'GET',[],null,$ua);
    if($resp===false || $http!==200) exit("Cannot fetch m3u8. HTTP:$http, CURL:$err");
    if(preg_match('/(hdntl=[^"\r\n]+)/i',$resp,$m)) {
        $token = trim($m[1]);

        // --- CLEAN TOKEN ---
        $token = preg_replace('/~acl=[^~]*/i','~acl=/*',$token);
        $token = preg_replace('/,?non-ssai_[^~,]+/i','',$token);
        $token = urldecode($token);
        $token .= '|User-Agent=' . $ua;

        return $token;
    }
    exit("Full hdntl token not found in m3u8");
}

// ---------- RUN ----------
try{
    $videoUrl=fetchVideoToken($userAgent);
    $hdntlFull=fetchHdntlFull($videoUrl,$userAgent);
    @file_put_contents($outputFile,$hdntlFull);
    header('Content-Type: text/plain; charset=utf-8');
    echo $hdntlFull;
}catch(Exception $e){
    exit("Exception: ".$e->getMessage());
}

  //=============================================================\\
 // Join https://t.me/cloudply, https://github.com/Himanshu8221   \\ 
//=================================================================\\