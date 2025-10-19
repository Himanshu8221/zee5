<?php
  //=============================================================\\
 // Join https://t.me/cloudply, https://github.com/Himanshu8221   \\ 
//=================================================================\\


$tokenSourceUrl = "http://localhost:8080/zee5/main-token.php"; // Update your file path/location where the main-token.php is hosted
            //     ⬇️⬇️⬇️⬇️⬇️⬇️⬇️⬇️⬇️⬇️⬇️⬇️⬇️⬇️⬇️⬇️⬇
/////////////////// Update your file path/location where the main-token.php is hosted //////////////////


$m3uFile = __DIR__ . '/zee5-playlist.m3u';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tokenSourceUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$tokenResponse = curl_exec($ch);
curl_close($ch);
if (!$tokenResponse || strpos($tokenResponse, "hdntl=") === false) {
    header("HTTP/1.1 500 Internal Server Error");
    die("Failed to fetch token");
}
$parts = explode("|User-Agent=", $tokenResponse);
$token = trim($parts[0]);
$userAgent = isset($parts[1]) ? trim($parts[1]) : '';
$playlist = file_get_contents($m3uFile);
if (!$playlist) {
    header("HTTP/1.1 500 Internal Server Error");
    die("Failed to read playlist file");
}

$playlist = preg_replace('/(https?:\/\/[^\s]+)\?/', '$1?' . $token, $playlist);
if ($userAgent) {
    $playlist = preg_replace('/#EXTVLCOPT:http-user-agent=.*/', '#EXTVLCOPT:http-user-agent=' . $userAgent, $playlist);
}
header("Content-Type: text/plain; charset=utf-8");
header('Content-Disposition: inline; filename="playlist.m3u"');
echo $playlist;

  //=============================================================\\
 // Join https://t.me/cloudply, https://github.com/Himanshu8221   \\ 
//=================================================================\\