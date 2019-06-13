<?php

namespace oihub\helper;

/**
 * Class XMLHelper.
 * 
 * @author sean <maoxfjob@163.com>
 */
class XMLHelper
{
    /**
     * XML转数组.
     * @param string $xml XML.
     * @return array
     */
    public static function toArray(string $xml): array
    {
        $backup = libxml_disable_entity_loader(true);
        $result = static::xml2Data(simplexml_load_string(
            $xml,
            'SimpleXMLElement',
            LIBXML_COMPACT | LIBXML_NOCDATA | LIBXML_NOBLANKS
        ));
        libxml_disable_entity_loader($backup);
        return $result;
    }

    /**
     * 转换XML.
     * @param array $data data.
     * @param string $root 根元素.
     * @param string $item 子元素.
     * @param string|array $attr 属性.
     * @param string $id 唯一属性.
     * @return string
     */
    public static function toXml(
        array $data,
        string $root = 'xml',
        string $item = 'item',
        $attr = '',
        string $id = 'id'
    ): string {
        if (is_array($attr)) {
            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = join(' ', $_attr ?? []);
        }
        $attr = trim($attr);
        $attr = empty($attr) ? '' : " {$attr}";
        $xml = "<{$root}{$attr}>";
        $xml .= static::data2Xml($data, $item, $id);
        $xml .= "</{$root}>";
        return $xml;
    }

    /**
     * 生成CDATA.
     * @param string $string 字符串.
     * @return string
     */
    public static function cdata(string $string): string
    {
        return sprintf('<![CDATA[%s]]>', $string);
    }

    /**
     * XML对象转数组.
     * @param SimpleXMLElement $xml XML.
     * @return array
     */
    protected static function xml2Data($xml)
    {
        $result = null;
        is_object($xml) and $xml = (array)$xml;
        if (is_array($xml)) {
            foreach ($xml as $key => $value) {
                $res = static::xml2Data($value);
                if (('@attributes' === $key) && ($key)) {
                    $result = $res;
                } else {
                    $result[$key] = $res;
                }
            }
        } else {
            $result = $xml;
        }
        return $result;
    }

    /**
     * 数组转XML.
     * @param array $data 数组.
     * @param string $item 子元素.
     * @param string $id 唯一属性.
     * @return string
     */
    protected static function data2Xml(
        array $data,
        string $item = 'item',
        string $id = 'id'
    ): string {
        $xml = $attr = '';
        foreach ($data as $key => $val) {
            if (is_numeric($key)) {
                $id && $attr = " {$id}=\"{$key}\"";
                $key = $item;
            }
            $xml .= "<{$key}{$attr}>";
            if ((is_array($val) || is_object($val))) {
                $xml .= static::data2Xml((array)$val, $item, $id);
            } else {
                $xml .= is_numeric($val) ? $val : static::cdata($val);
            }
            $xml .= "</{$key}>";
        }
        return $xml;
    }
}
