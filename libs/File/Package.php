<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/27
 * Time: 下午3:21
 */

namespace ToolkitPlus\File;

use ToolkitPlus\File\Compress\AbstractCompressor;

/**
 * dir compress | file uncompressed
 * Class Compress
 * @package ToolkitPlus\File
 */
class Package
{
    /**
     * dir path, wait compress ...
     * @var string
     */
    protected $sourcePath;

    /**
     * the compressed file
     * @var string
     */
    protected $compressedFile = '';

    const TYPE_ZIP = 'zip';

    protected $type = 'zip';

    /**
     * @var AbstractCompressor
     */
    protected $compressor;

    /**
     * 包含的可发布的 文件 文件扩展匹配 目录
     * 比 {@see $exlude} 优先级更高
     * @var array
     */
    protected $include = [
        'file' => [],
        'ext' => [],
        'dir' => [], // ['dist'],
    ];

    /**
     * 排除发布的 文件 文件扩展匹配 目录
     * @var array
     */
    protected $exclude = [
        'file' => [],
        'ext' => [],
        'dir' => [],
    ];

//    public function __construct(array $config = [])
//    {}

    public function pack($sourcePath, $saveTo, $type = self::TYPE_ZIP)
    {

    }

    public function unpack($pack, $extractTo)
    {

    }

    public static function getTypes()
    {
        return [

        ];
    }
}
