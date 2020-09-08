<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/8
 * Time: 16:36
 */
//去掉文本中的HTML标签
function cutstr_html($string, $length = 0, $ellipsis = '…')
{
    $string = strip_tags($string);
    $string = preg_replace('/\n/is', '', $string);
    $string = preg_replace('/ |　/is', '', $string);
    $string = preg_replace('/ /is', '', $string);
    $string = preg_replace('/<br \/>([\S]*?)<br \/>/', '<p>$1<\/p>', $string);
    preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $string, $string);
    if (is_array($string) && !empty($string[0])) {
        if (is_numeric($length) && $length) {
            $string = join('', array_slice($string[0], 0, $length)) . $ellipsis;
        } else {
            $string = implode('', $string[0]);
        }
    } else {
        $string = '';
    }
    return $string;
}