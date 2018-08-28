<?php
/**
 * Created by PhpStorm.
 * User: 95
 * Date: 2016/6/13
 * Time: 17:46
 * 敏感词汇过滤
 */

namespace Org\Util;


class SensitiveFilter
{
    public static $wordArr = array();
    public static $content = "";

    /**
     * 处理内容
     * @param $content
     *
     * @return bool
     */
    public static function filter($content)
    {
        if ($content == "") return false;
        self::$content = $content;
        empty(self::$wordArr) ? self::getWord() : "";
        foreach (self::$wordArr as $row) {
            if (false !== strstr(self::$content, $row)) return false;
        }
        return true;
    }

    public static function getWord()
    {
        self::$wordArr = include 'SensitiveThesaurus.php';
    }
}