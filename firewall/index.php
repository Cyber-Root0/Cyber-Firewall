<?php
//variaveis essenciais para o funcionamento e usabilidade do sistema
// Mini Firewall em PHP que bloqueia Ataques de DoS & Scans NMAP e IPS Estrangeiros
require 'email.php';
function limpa_caracter($str)
{
    $json = str_split($str);
    for ($i = 0; $i < count($json); $i++) {
        if ($json[$i] == "[" or $json[$i] == "]" or $json[$i] == "-" or $json[$i] == "(" or $json[$i] == ")" or $json[$i] == "/") {
            $json[$i] = "";
        }
    }
    return implode($json);
}
$ips = array();
$log = file("log.txt");
$lines = count($log) - 1;
$r = 0;
for ($i = $lines; $i > $lines - 100; $i--) {

    $log_info = $log[$i];
    $j = explode(" ", $log_info);
    $ip = $j[0];
    $data = $j[3];
    $data = limpa_caracter($data);
    $request = $j[5];
    $page = $j[6];
    $status_code = $j[8];
    $porta = $j[9];
    //tratamento do user agent....
    $user_agent = "";
    for ($p = 11; $p < count($j); $p++) {
        $user_agent .= $j[$p];
    }
    //validando as informacoes na função...
    check_user_agent($user_agent, $ip);
    check_ip($ip);

    //tratamento de ip
    if (array_key_exists($ip, $ips)) {
        $ips[$ip] = $ips[$ip] + 1;
    } else {
        $ips[$ip] = 1;
    }
}
// executando as validaçoes de IP
for ($i = 30; $i < 100; $i++) {
    if (in_array($i, $ips)) {
        $key = array_search($i, $ips);
        block_ip(1,$key);
    }
}

function block_ip($ip)
{
    $ip = $ip;
     // setando variaveis recebidas
    $query = "netsh advfirewall firewall add rule name=BLOCK-IP-ADDRESS-$ip dir=in action=block remoteip=$ip";
    system($query);
}

function check_user_agent($user, $ip)
{
    $ip = $ip;
    $verificacao=false;
    $user = $user; //setando variaveis recebidas
    $str = limpa_caracter($user);
    //verifica se essas strings esta dentro do user agent
    $user_validos = array("Mozilla", "Google", "Android", "Chrome", "Yandex", "Mobile", "Desktop", "Linux", "Safari");
    for ($i = 0; $i < count($user_validos); $i++) {
        $id = $user_validos[$i];
        if (strpos($str, $id)) {
        $verificacao=true;
        break;
        }


    }
    // executa a ação conforme a analise anterior  
    if($verificacao==false){
        EnvioEmail(2,"Identificamos Uma Possivel Ameaça e a Neutralizamos",$ip);
        block_ip($ip);
    }
    
}

function check_ip($ip){
    $ip=$ip; //setando variaveis da função
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch,CURLOPT_URL,"http://ip-api.com/json/$ip");
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
    $data = curl_exec($ch);
    curl_close($ch);
    //checando se o ip é de algum Pais Diferente do Brasil, caso positivo ele vai enviar um alerta no Email
    $json=json_decode($data);
    $pais=$json->country;
    if($pais!=="Brazil"){
    EnvioEmail(3,"Tentativa de Acesso De Ip Estrangeiro",$ip);
    }

}
function EnvioEmail($id, $mensagem,$ip)
{
    $ip=$ip; //setando variaveis da função
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch,CURLOPT_URL,"http://ip-api.com/json/$ip");
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
    $data = curl_exec($ch);
    curl_close($ch);
    //checando se o ip é de algum Pais Diferente do Brasil, caso positivo ele vai enviar um alerta no Email
    $json=json_decode($data);
    $pais=$json->country;
    $lat=$json->lat;
    $lon=$json->lon;
    $city=$json->city;
    $isp=$json->isp;

    //configurando o email com base nas informaçoes recebidas na api
    $template="<!DOCTYPE html
    PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
    <title>Cyber Firewall - EMAIL HTML</title>
    <meta name='viewport' content='width=device-width, initial-scale=1.0' />
</head>
<body>
    <table border='1' cellpadding='0' cellspacing='0' width='100%' style='border: 1px solid black;'>
        <tr style='height: 50px;'>
            <td style='width: auto;' >
                
            </td>
            <td style='background-color: rgb(41, 147, 252);'>
                <p style='font-family: sans-serif;font-size: 23px; color: white; text-align: center; text-shadow: 2px 2px 5px navy;'>Information DNS</p>
           </td>
        </tr>
        <tr style='height: 50px;'>
            <td>
                <p style='font-family: sans-serif;font-size: 17px; color:black; text-align: center;'>IP</p>
            </td>
            <td>
                <p style='font-family: sans-serif;font-size: 17px; color:black; text-align: center; text-shadow: 2px 2px 5px navy;'>$ip</p>
            </td>
        </tr>
        <tr style='height: 50px;'>
            <td>
                <p style='font-family: sans-serif;font-size: 17px; color:black; text-align: center;'>Pais</p>
            </td>
            <td>
                <p style='font-family: sans-serif;font-size: 17px; color:black; text-align: center; text-shadow: 2px 2px 5px navy;'>$pais</p>
            </td>
        </tr>
        <tr style='height: 50px;'>
            <td>
                <p style='font-family: sans-serif;font-size: 17px; color:black; text-align: center;'>Latitude</p>
            </td>
            <td>
                <p style='font-family: sans-serif;font-size: 17px; color:black; text-align: center; text-shadow: 2px 2px 5px navy;'>$lat</p>
            </td>
        </tr>
        <tr style='height: 50px;'>
            <td>
                <p style='font-family: sans-serif;font-size: 17px; color:black; text-align: center;'>Longitude</p>
            </td>
            <td>
                <p style='font-family: sans-serif;font-size: 17px; color:black; text-align: center; text-shadow: 2px 2px 5px navy;'>$lon</p>
            </td>
        </tr>
        <tr style='height: 50px;'>
            <td>
                <p style='font-family: sans-serif;font-size: 17px; color:black; text-align: center;'>Cidade</p>
            </td>
            <td>
                <p style='font-family: sans-serif;font-size: 17px; color:black; text-align: center; text-shadow: 2px 2px 5px navy;'>$city</p>
            </td>
        </tr>
        <tr style='height: 50px;'>
            <td>
                <p style='font-family: sans-serif;font-size: 17px; color:black; text-align: center;'>ISP</p>
            </td>
            <td>
                <p style='font-family: sans-serif;font-size: 17px; color:black; text-align: center; text-shadow: 2px 2px 5px navy;'>$isp</p>
            </td>
        </tr>
       </table>
</body>
</html>";
//enviando o EMAIL para o Destinatario
SendEmail("boteistem@yandex.com",$mensagem, $template);

}
