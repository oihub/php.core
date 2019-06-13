<?php

namespace oihub\helper;

/**
 * Class StringHelper.
 * 
 * @author sean <maoxfjob@163.com>
 */
class StringHelper
{
    /**
     * UUID.
     * @return string
     */
    public static function uuid(): string
    {
        $chars = md5(uniqid(mt_rand(0, mt_getrandmax()), true));
        $uuid = substr($chars, 0, 8) . '-';
        $uuid .= substr($chars, 8, 4) . '-';
        $uuid .= substr($chars, 12, 4) . '-';
        $uuid .= substr($chars, 16, 4) . '-';
        $uuid .= substr($chars, 20, 12);
        return $uuid;
    }

    /**
     * 唯一字符串.
     * @return string
     */
    public static function unique(): string
    {
        $chars = md5(uniqid(mt_rand(0, mt_getrandmax()), true));
        $uuid = substr($chars, 0, 8);
        $uuid .= substr($chars, 8, 4);
        $uuid .= substr($chars, 12, 4);
        $uuid .= substr($chars, 16, 4);
        $uuid .= substr($chars, 20, 12);
        return $uuid;
    }

    /**
     * 简单编号.
     * 例如：2653 9689 3739 0570.
     * @return string
     */
    public static function simpleCode(): string
    {
        return join('', array_map(function ($item) {
            return substr($item + 1, -1);
        }, str_split(str_replace('.', '', array_sum(
            explode(' ', microtime())
        )) . mt_rand(10, 99))));
    }

    /**
     * 检查传递的字符串是否与给定的通配符模式匹配.
     * @param string $pattern 通配符.
     * @param string $string 字符串.
     * @param array $options 用于匹配的选项.
     * @param   bool $caseSensitive 是否区分大小写，默认为'true'.
     * @param   bool $escape 反斜杠是否转义，默认为'true'.
     * @param   bool $filePath 斜线是否匹配，默认为'false'.
     * @return bool
     */
    public static function matchWildcard(
        string $pattern,
        string $string,
        array $options = []
    ): bool {
        if ($pattern === '*' && empty($options['filePath'])) {
            return true;
        }

        $replacements = [
            '\\\\\\\\' => '\\\\',
            '\\\\\\*' => '[*]',
            '\\\\\\?' => '[?]',
            '\*' => '.*',
            '\?' => '.',
            '\[\!' => '[^',
            '\[' => '[',
            '\]' => ']',
            '\-' => '-',
        ];

        if (isset($options['escape']) && !$options['escape']) {
            unset($replacements['\\\\\\\\']);
            unset($replacements['\\\\\\*']);
            unset($replacements['\\\\\\?']);
        }

        if (!empty($options['filePath'])) {
            $replacements['\*'] = '[^/\\\\]*';
            $replacements['\?'] = '[^/\\\\]';
        }

        $pattern = strtr(preg_quote($pattern, '#'), $replacements);
        $pattern = '#^' . $pattern . '$#us';

        if (isset($options['caseSensitive']) && !$options['caseSensitive']) {
            $pattern .= 'i';
        }

        return preg_match($pattern, $string) === 1;
    }
}
