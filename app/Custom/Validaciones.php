<?php

namespace App\Custom;

class Validaciones
{
    //Obtiene la IP del cliente
    function getRealIP()
    {

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) != '') {
            $client_ip =
                (!empty($_SERVER['REMOTE_ADDR'])) ?
                $_SERVER['REMOTE_ADDR']
                : ((!empty($_ENV['REMOTE_ADDR'])) ?
                    $_ENV['REMOTE_ADDR']
                    :
                    "unknown");

            // los proxys van añadiendo al final de esta cabecera
            // las direcciones ip que van "ocultando". Para localizar la ip real
            // del usuario se comienza a mirar por el principio hasta encontrar 
            // una dirección ip que no sea del rango privado. En caso de no 
            // encontrarse ninguna se toma como valor el REMOTE_ADDR

            $entries = preg_split('/[, ]/', $_SERVER['HTTP_X_FORWARDED_FOR']);

            reset($entries);
            while (list(, $entry) = each($entries)) {
                $entry = trim($entry);
                if (preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $entry, $ip_list)) {
                    // http://www.faqs.org/rfcs/rfc1918.html
                    $private_ip = array(
                        '/^0\./',
                        '/^127\.0\.0\.1/',
                        '/^192\.168\..*/',
                        '/^172\.((1[6-9])|(2[0-9])|(3[0-1]))\..*/',
                        '/^10\..*/'
                    );

                    $found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);

                    if ($client_ip != $found_ip) {
                        $client_ip = $found_ip;
                        break;
                    }
                }
            }
        } else {
            $client_ip =
                (!empty($_SERVER['REMOTE_ADDR'])) ?
                $_SERVER['REMOTE_ADDR']
                : ((!empty($_ENV['REMOTE_ADDR'])) ?
                    $_ENV['REMOTE_ADDR']
                    :
                    "unknown");
        }
        return $client_ip;
    }

    /*******************************************************************************************/

    function idDis()
    {
        $tablet_browser = 0;
        $mobile_browser = 0;
        $body_class = 'desktop';

        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $tablet_browser++;
            $body_class = "tablet";
        }

        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $mobile_browser++;
            $body_class = "mobile";
        }

        if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
            $mobile_browser++;
            $body_class = "mobile";
        }

        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
        $mobile_agents = array(
            'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac',
            'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno',
            'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-',
            'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-',
            'newt', 'noki', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox',
            'qwap', 'sage', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar',
            'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-',
            'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp',
            'wapr', 'webc', 'winw', 'winw', 'xda ', 'xda-'
        );

        if (in_array($mobile_ua, $mobile_agents)) {
            $mobile_browser++;
        }

        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'opera mini') > 0) {
            $mobile_browser++;
            //Check for tablets on opera mini alternative headers
            $stock_ua = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']) ? $_SERVER['HTTP_X_OPERAMINI_PHONE_UA'] : (isset($_SERVER['HTTP_DEVICE_STOCK_UA']) ? $_SERVER['HTTP_DEVICE_STOCK_UA'] : ''));
            if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) {
                $tablet_browser++;
            }
        }
        if ($tablet_browser > 0) {
            // Si es tablet has lo que necesites
            $res = 'Tablet';
        } else if ($mobile_browser > 0) {
            // Si es dispositivo mobil has lo que necesites
            $res = 'Celular';
        } else {
            // Si es ordenador de escritorio has lo que necesites
            $res = 'Computadora';
        }
        return $res;
    }

    // $user_agent = $_SERVER['HTTP_USER_AGENT'];

    /*******************************************************************************************/
    function getSO($user_agent)
    {
        $plataformas = array(
            'Windows 10' => 'Windows NT 10.0+',
            'Windows 8.1' => 'Windows NT 6.3+',
            'Windows 8' => 'Windows NT 6.2+',
            'Windows 7' => 'Windows NT 6.1+',
            'Windows Vista' => 'Windows NT 6.0+',
            'Windows XP' => 'Windows NT 5.1+',
            'Windows 2003' => 'Windows NT 5.2+',
            'Windows' => 'Windows otros',
            'iPhone' => 'iPhone',
            'iPad' => 'iPad',
            'Mac OS X' => '(Mac OS X+)|(CFNetwork+)',
            'Mac otros' => 'Macintosh',
            'Android' => 'Android',
            'BlackBerry' => 'BlackBerry',
            'Linux' => 'Linux',
        );
        foreach ($plataformas as $plataforma => $pattern) {
            if (preg_match('/' . $pattern . '/', $user_agent))
                return $plataforma;
        }
        return 'Otras';





        // $SO = getPlatform($user_agent);


        // return $SO;
    }
}
