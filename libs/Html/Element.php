<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 15-4-1
 * Time: 上午10:08
 * Used: create html Element
 * file: Element.php
 */

namespace ToolkitPlus\Html;

use Toolkit\ObjUtil\Configurable;
use Toolkit\ObjUtil\Traits\ArrayAccessByPropertyTrait;
use Traversable;

/*

$form = new Element('form');

$form->addAttr('id','test-id');
$form->setAttr('class','test dfdf kkkkk')->appendAttr('class','hhhhh');

$input = $form->addChild('input',null, [ 'value'=>'test-id' , 'name' => 'username', 'type' => 'text'];
$div1 = $form->addChild('div','test-id',['id'=>'div-1']);
$div2->setContent('set content');
$div2 = $form->addChild('div','test-iddf',['id'=>'div-2']);
$div2->addContent('add content');

var_dump((string)$form);
*/

/**
 * Class Element
 * @package ToolkitPlus\Html
 */
class Element extends Configurable implements \ArrayAccess, \IteratorAggregate
{
    use ArrayAccessByPropertyTrait;

    const BEFORE_TEXT = 'before';
    const AFTER_TEXT = 'after';
    const REPLACE_TEXT = 'replace';

    /**
     * tag name
     * @var string
     */
    protected $name = 'div';

    /**
     * tag attribute
     * e.g. [
     *   'id'    => 'div-1'
     *   'class' => 'class-1 class-2 class-3'
     * ]
     * @var array
     */
    protected $attrs = [];

    /**
     * tag content
     * @var string
     */
    protected $content;

    /**
     * current tag's parent element
     * @var null|self
     */
    protected $parent;

    /**
     * current tag's child elements
     * @var array[]
     */
    protected $children = [];

    /**
     * 如果当前元素有内容的话，添加子元素位置默认规则
     * before  -- 添加在内容之前
     * after  -- 添加在内容之后
     * replace -- 替换覆盖掉内容
     */
    protected $defaultAddRule = 'after';

    public function __construct($name = null, $content = null, array $attrs = [])
    {
        $this->name = $name;
        $this->content = $content;
        $this->attrs = $attrs;

        // $this->children = new \SplObjectStorage();
        $this->children = [];

        parent::__construct();
    }

///////////////////////////////////////// generate element /////////////////////////////////////////

    /**
     * generate element sting
     * @throws \InvalidArgumentException
     */
    public function getString()
    {
        if (!$name = strtolower(trim($this->name))) {
            throw new \InvalidArgumentException('请设置标签元素的名称！');
        }

        $attrString = $this->getAttrs(true);
        $content = $this->_handleChildAndContent();

        $eleString = sprintf("\n<{$name}%s>%s", $attrString, $content);
        $eleString .= $this->isAloneTag($name) ? "\n" : "</{$name}>\n";

        // has parent
        if ($parent = $this->parent) {

            if ($this->isAloneTag($parent->name)) {
                throw new \InvalidArgumentException('不能设置单标签元素 ' . $parent->name . '为父元素！');
            }

            $parent->setContent($eleString);
            $eleString = $parent->getString();
        }

        unset($name, $attrString, $content, $parent);

        return $eleString;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->getString();
        } catch (\Throwable $e) {
            // return $e->getMessage();
            return '';
        }
    }

    /**
     * @return null|string
     */
    protected function _handleChildAndContent()
    {
        if (!($children = $this->children)) {
            return $this->content;
        }

        $content = $this->content;

        // 替换 直接占有 内容的位置
        if (isset($children[self::REPLACE_TEXT])) {
            $string = '';
            foreach ($children[self::REPLACE_TEXT] as $child) {
                $string .= rtrim((string)$child);
            }

            $content = $string . "\n";
        }

        if (isset($children[self::BEFORE_TEXT])) {
            $string = '';
            foreach ($children[self::BEFORE_TEXT] as $child) {
                $string .= rtrim((string)$child);
            }

            $content = $string . $content;
        }

        if (isset($children[self::AFTER_TEXT])) {
            $string = '';
            foreach ($children[self::AFTER_TEXT] as $child) {
                $string .= rtrim((string)$child);
            }

            $content .= $string . "\n";
        }

        return $content;
    }

    /**
     * @param $name
     * @return bool
     */
    public function isAloneTag($name): bool
    {
        return Html::isAloneTag($name);
    }

///////////////////////////////////////// parent element /////////////////////////////////////////

    /**
     * @param null $name
     * @param null $content
     * @param array $attrs
     * @return Element
     */
    public function setParent($name = null, $content = null, array $attrs = []): Element
    {
        if ($name instanceof self) {
            $parent = $name;
        } else {
            $parent = new self($name, $content, $attrs);
        }

        return ($this->parent = $parent);
    }

    /**
     * @return null|Element
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function removeParent()
    {
        $this->parent = null;
    }

///////////////////////////////////////// child element /////////////////////////////////////////

    /**
     * @param null $name
     * @param null $content
     * @param array $attrs
     * @param string $rule
     * @return Element
     */
    public function addChild($name = null, $content = null, array $attrs = [], $rule = self::AFTER_TEXT): Element
    {
        if ($name instanceof self) {
            $child = $name;
            $rule = trim($content);
        } else {
            $child = new static($name, $content, $attrs);
        }

        $rule = $this->isValidRule($rule) ? $rule : $this->defaultAddRule;
        $this->children[$rule][] = $child;

        return $child;
    }

    /**
     * @param self[] $children child list
     * @return $this
     */
    public function setChildren(array $children): self
    {
        $this->children = [];

        return $this->addChildren($children);
    }

    /**
     * @param self[] $children
     * @return $this
     */
    public function addChildren(array $children): self
    {
        foreach ($children as $child) {
            if ($child instanceof self) {
                $this->children[$this->defaultAddRule][] = $child;
            }
        }

        return $this;
    }

    /**
     * 如果当前元素有内容的话，添加子元素规则
     * before  -- 添加在内容之前
     * after  -- 添加在内容之后
     * replace -- 替换覆盖掉内容
     * @param $value
     * @return $this
     */
    public function setDefaultAddRule($value): self
    {
        if ($this->isValidRule($value)) {
            $this->defaultAddRule = $value;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultAddRule(): string
    {
        return $this->defaultAddRule;
    }

    /**
     * @return array
     */
    public function getChildAddRules(): array
    {
        return [
            self::AFTER_TEXT,
            self::BEFORE_TEXT,
            self::REPLACE_TEXT,
        ];
    }

    /**
     * @param $value
     * @return bool
     */
    protected function isValidRule(string $value): bool
    {
        return \in_array($value, $this->getChildAddRules(), true);
    }

///////////////////////////////////////// property /////////////////////////////////////////

    /**
     * @param $value
     * @return $this
     */
    public function setName(string $value): self
    {
        $this->name = $value;

        return $this;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getName($value)
    {
        return $this->name = $value;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setContent($value): self
    {
        $this->content = $value;

        return $this;
    }

    /**
     * @param $value
     * @param string $position
     * @return $this
     */
    public function addContent($value, $position = 'after'): self
    {
        if ($position === 'after') {
            $this->content .= $value;
        } else {
            $this->content = $value . $this->content;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        if ($content = $this->getAttr('content') ?: $this->getAttr('text')) {
            unset($this->attrs['content'], $this->attrs['text']);

            $this->content = $content;
        }

        return $this->content;
    }

///////////////////////////////////////// element attr /////////////////////////////////////////

    /**
     * @param array $value
     * @return $this
     */
    public function setAttrs(array $value): self
    {
        $this->attrs = $value;

        return $this;
    }

    /**
     * @param bool $toString
     * @return array|string
     */
    public function getAttrs($toString = false)
    {
        if ($content = $this->getAttr('content') ?: $this->getAttr('text')) {
            unset($this->attrs['content'], $this->attrs['text']);

            $this->content = $content;
        }

        if ((bool)$toString) {
            $attrString = '';

            foreach ($this->attrs as $name => $value) {
                $attrString .= " $name=\"$value\"";
            }

            return $attrString;
        }

        return $this->attrs;
    }


    /**
     * @param array $attrs
     * @return $this
     */
    public function addAttrs(array $attrs): self
    {
        foreach ($attrs as $name => $val) {
            $this->addAttr($name, $val);
        }

        return $this;
    }

    /**
     * @param $name
     * @return bool
     */
    public function existsAttr($name): bool
    {
        return isset($this->attrs[trim($name)]);
    }

    /**
     * @param $name
     * @return null
     */
    public function getAttr($name)
    {
        $name = trim($name);

        return $this->attrs[$name] ?? null;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setAttr($name, $value): self
    {
        $this->attrs[trim($name)] = trim($value);

        return $this;
    }

    /**
     * 属性添加
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function addAttr($name, $value): self
    {
        $name = trim($name);
        $value = trim($value);

        if ($value && !$this->existsAttr($name)) {
            $this->attrs[$name] = $value;
        }

        return $this;
    }

    /**
     * 在已有的属性值上追加值
     * e.g. ['class'=>'class-1 class-2']
     *  appendAttr('class', 'class-3') --> ['class'=>'class-1 class-2 class-3']
     * @param string $name
     * @param string $value
     * @param string $separator 追加的值与原有的值之间的分隔符 e.g. 两个class之间的空格
     * @return $this
     */
    public function appendAttr($name, $value, $separator = ''): self
    {
        if ($this->existsAttr($name)) {

            if ($name === 'class') {
                $separator = ' ';
            }

            $this->attrs[$name] .= $separator . trim($value);

        } else {
            $this->attrs[trim($name)] = trim($value);
        }

        return $this;
    }

    /**
     * @param $value
     * @return Element
     */
    public function setClass($value): Element
    {
        return $this->setAttr('class', $value);
    }

    /**
     * @param $value
     * @return Element
     */
    public function addClass($value): Element
    {
        return $this->appendAttr('class', $value, ' ');
    }

    /**
     * @param $name
     * @param null $value
     * @return Element
     */
    public function addStyle($name, $value = null): Element
    {
        if ($value) {
            $value = $name . ':' . $value;
        }

        return $this->appendAttr('style', $value);
    }

    /**
     * todo unused
     * 属性合并，多用于 class style $arr1 ，$arr2
     *  $arr1 = array(
     *       'class'  =>"navbar-form navbar-left",
     *       'action' =>'index.php/user/add',
     *       'method' =>'post'
     *       );
     *  $arr1 = array(
     *       'class'  =>"navbar-fixed",
     *       'action' =>'index.php/user/edit',
     *       'method' =>'get'
     *       );
     *   --->
     *   returns: array(
     *       'class'  =>"navbar-form navbar-left navbar-fixed",
     *       'action' =>'index.php/user/edit',
     *       'method' =>'get'
     *       );
     * @param  array $old [原属性组]
     * @param mixed $new [传入的新增属性组]
     * @param  array $attrs [需要合并的属性]
     * @return array
     */
    public function attrMerge($old, $new, array $attrs = []): array
    {
        if (!$old && !$new) {
            return [];
        }

        if (!$new) {
            return $old;
        }

        $default = ['class', 'style'];

        if ($attrs) {
            array_map(function ($value) use (&$default) {
                if (!\in_array($value, $default, true)) {
                    $default[] = $value;
                }
            }, $attrs);
        }

        $attrs = $default;
        $merges = [];

        // 交集, 都含有的属性
        $intersectAttrs = array_keys(array_intersect_key($old, $new));

        foreach ($attrs as $attr) {
            if (\in_array($attr, $intersectAttrs, true)) {
                $merges[$attr] = $old[$attr] . ' ' . $new[$attr];
            }
        }

        return array_merge($old, $new, $merges);
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->attrs);
    }
}// end class Element

