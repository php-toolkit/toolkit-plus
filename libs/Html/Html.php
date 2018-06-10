<?php
/**
 * Created by sublime 3.
 * Auth: Inhere
 * Date: 14-6-28
 * Time: 10:35
 * Use: 主要功能是 html 标签元素创建
 */

namespace ToolkitPlus\Html;

/**
 * Class Html
 * @package ToolkitPlus\html
 */
class Html
{
    // Independent tag
    public static $aloneTags = [
        'area', 'br', 'base', 'col', 'frame', 'hr', 'img', 'input', 'link', 'mate', 'param'
    ];

    // need close tags
    public static $closeTags = [
        'html', 'head', 'body', 'a', 'div', 'p', 'ul', 'li', 'ol', 'dl', 'table', 'tr', 'th', 'td'
    ];

    public static $tagAttrs = ['id', 'class', 'style', 'type', 'href', 'src'];

    /**
     * @param $name
     * @return bool
     */
    public static function isAloneTag(string $name): bool
    {
        return \in_array(trim($name), static::$aloneTags, true);
    }

    /**
     * link tag
     * @param array $attrs
     * @return string
     */
    public static function link(array $attrs = []): string
    {
        return static::tag('link', null, $attrs);
    }

    /**
     * css link tag
     * @param $href
     * @param array $attrs
     * @return string
     */
    public static function css(string $href, array $attrs = []): string
    {
        $attrs = array_merge([
            'type' => 'text/css',
            'rel' => 'stylesheet',
            'href' => $href,
        ],
            $attrs);

        return static::tag('link', null, $attrs);
    }

    /**
     * style tag
     * @param  string $content
     * @param array $attrs
     * @return string
     */
    public static function style(string $content, array $attrs = []): string
    {
        $attrs = array_merge(['type' => 'text/css'], $attrs);

        return static::tag('style', PHP_EOL . trim($content) . PHP_EOL, $attrs);
    }

    public static function cssCode(string $content, array $attrs = []): string
    {
        return static::style($content, $attrs);
    }

    /**
     * javascript tag
     * @param  string $src
     * @param array $attrs
     * @return string
     */
    public static function script(string $src, array $attrs = []): string
    {
        $attrs = array_merge(
            [
                'type' => 'text/javascript',
                'src' => $src
            ],
            $attrs);

        return static::tag('script', null, $attrs);
    }

    /**
     * javascript tag
     * @param  string $content
     * @param array $attrs
     * @return string
     */
    public static function scriptCode(string $content = null, array $attrs = []): string
    {
        $attrs = array_merge(array('type' => 'text/javascript'), $attrs);

        return static::tag('script', PHP_EOL . trim($content) . PHP_EOL, $attrs);
    }

    /**
     * @param string|null $content
     * @param array $attrs
     * @return string
     */
    public static function jsCode(string $content = null, array $attrs = []): string
    {
        return static::scriptCode($content, $attrs);
    }

    /**
     * @param string $url
     * @return string
     */
    public static function siteIcon(string $url): string
    {
        return <<<EOF
    <link rel="icon" href="$url" type="image/x-icon"/>
    <link rel="shortcut icon" href="$url" type="image/x-icon"/>
EOF;
    }

    /**
     * @param $content
     * @param $url
     * @param array $attrs
     * @return string
     */
    public static function a($content, $url, array $attrs = []): string
    {
        $url = $url ?: 'javascript:void(0);';

        return static::tag('a', $content, array_merge([
            'href' => $url,
            'title' => $content,
        ], $attrs));
    }

    /**
     * @param $src
     * @param array $attrs
     * @return string
     * @internal param string $alt
     */
    public static function img($src, array $attrs = []): string
    {
        $newAttrs = array_merge(['src' => $src], $attrs);

        return static::tag('img', null, $newAttrs);
    }

    /**
     * @param string $content
     * @param array $attrs
     * @return string
     * @internal param string $type
     */
    public static function button($content, array $attrs = []): string
    {
        $attrs = array_merge(['type' => 'button'], $attrs);

        return static::tag('button', $content, $attrs);
    }

//////////////////////////////////////// form tag ////////////////////////////////////////

    /**
     * @param string $action
     * @param string $method
     * @param array $attrs
     * @return string
     */
    public static function startForm(string $action = '', string $method = 'post', array $attrs = []): string
    {
        $attrs = array_merge([
            'action' => $action,
            'method' => $method,
        ], $attrs);

        return static::startTag('form', $attrs);
    }

    /**
     * @return string
     */
    public static function endForm(): string
    {
        return static::endTag('form');
    }

    /**
     * @param string $type
     * @param array $attrs
     * @return string
     * @internal param string $content
     */
    public static function input(string $type = 'text', array $attrs = []): string
    {
        $attrs = array_merge(['type' => $type], $attrs);

        return static::tag('input', null, $attrs);
    }

    /**
     * @param string $content
     * @param array $attrs
     * @return string
     * @internal param string $content
     */
    public static function textarea(string $content, array $attrs = []): string
    {
        return static::tag('textarea', $content, $attrs);
    }

//////////////////////////////////////// create tag ////////////////////////////////////////

    //Independent element
    /**
     * @param $name
     * @param string $content
     * @param array $attrs
     * @return string
     */
    public static function tag(string $name, $content = '', array $attrs = []): string
    {
        if (!$name = strtolower(trim($name))) {
            return '';
        }

        if (isset($attrs['content'])) {
            $content = $attrs['content'];
            unset($attrs['content']);
        } elseif (isset($attrs['text'])) {
            $content = $attrs['text'];
            unset($attrs['text']);
        }

        $eleString = static::startTag($name, $attrs) . $content;
        $eleString .= static::isAloneTag($name) ? "\n" : static::endTag($name);

        return $eleString;
    }

    /**
     * 开始标签
     * @param  string $name
     * @param array $attrs
     * @return string
     */
    public static function startTag(string $name, array $attrs = []): string
    {
        return \sprintf("\n<%s %s>", \trim($name), static::_buildAttr($attrs));
    }

    /**
     * @param $name
     * @return string
     */
    public static function closeTag(string $name): string
    {
        return static::endTag($name);
    }

    /**
     * 结束标签
     * @param  string $name
     * @return string
     */
    public static function endTag(string $name): string
    {
        return '</' . \trim($name) . ">\n";
    }

    /**
     * 属性添加
     * @param string|array $attr
     * @param string|bool $value
     * @return string
     */
    protected static function _buildAttr($attr, $value = ''): string
    {
        if (\is_string($attr)) {
            if (\is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            return "{$attr}=\"{$value}\"";
        }

        if (\is_array($attr)) {
            $attrs = [];

            /** @var array $attr */
            foreach ($attr as $name => $val) {
                $attrs[] = static::_buildAttr($name, $val);
            }

            return \implode(' ', $attrs);
        }

        return '';
    }
}
