<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 1/28/2019
 * Time: 10:39 PM
 */

namespace que\utility\client;

abstract class Browser
{

    /**
     * @return array
     */
    public static function browserInfo(): array
    {

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $browser = "Unknown Browser";

        $browserList = array(
            '/msie/i' => 'Internet Explorer',
            '/edge/i' => 'Microsoft Edge',
            '/firefox/i' => 'Mozilla Firefox',
            '/chrome/i' => 'Google Chrome',
            '/opera/i' => 'Opera',
            '/safari/i' => 'Safari',
            '/netscape/i' => 'Netscape',
            '/maxthon/i' => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/mobile/i' => 'Handheld Browser'
        );

        foreach ($browserList as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                $browser = $value;
                break;
            }
        }

        $platform = "Unknown OS Platform";

        $osList = array(
            '/windows nt 10.0/i' => 'Windows 10',
            '/windows nt 6.2/i' => 'Windows 8',
            '/windows nt 6.1/i' => 'Windows 7',
            '/windows nt 6.0/i' => 'Windows Vista',
            '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i' => 'Windows XP',
            '/windows xp/i' => 'Windows XP',
            '/windows nt 5.0/i' => 'Windows 2000',
            '/windows me/i' => 'Windows ME',
            '/win98/i' => 'Windows 98',
            '/win95/i' => 'Windows 95',
            '/win16/i' => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i' => 'Mac OS 9',
            '/ipod/i' => 'iPod',
            '/iphone/i' => 'iPhone',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/ubuntu/i' => 'Ubuntu',
            '/linux/i' => 'Linux',
            '/webos/i' => 'Mobile'
        );

        foreach ($osList as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                $platform = $value;
                break;
            }
        }

        return [
            "browser" => $browser,
            "platform" => $platform
        ];
    }
}

