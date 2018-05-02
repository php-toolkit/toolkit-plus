<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/27
 * Time: 下午6:23
 */

namespace ToolkitPlus\File\Compress;

use InvalidArgumentException;
use Toolkit\File\FileFinder;
use Toolkit\ObjUtil\Traits\PropertyAccessByGetterSetterTrait;

/**
 * Class AbstractCompressor
 * @package ToolkitPlus\File\Compress
 */
abstract class AbstractCompressor
{
    use PropertyAccessByGetterSetterTrait;

    /**
     * @var string
     */
    protected $suffix = '';

    /**
     * a directory path will compressed
     * @var string
     */
    protected $sourcePath;

    /**
     * the compressed file path
     * @var string
     */
    protected $archiveFile;

    /**
     * @var object
     */
    protected $driver;

    /**
     * @var FileFinder
     */
    protected $finder;

    /**
     * @return bool
     */
    abstract public function isSupported();

    /**
     * @param string $sourcePath
     * @param string $archiveFile
     * @param bool $override
     * @return bool
     */
    abstract public function encode($sourcePath, $archiveFile, $override = true);

    /**
     * @param string $archiveFile
     * @param string $extractTo
     * @param bool $override
     * @return bool
     */
    abstract public function decode($archiveFile, $extractTo = '', $override = true);

    /**
     * @return mixed
     */
    abstract public function getDriver();

    /**
     * @param array $options
     * @return FileFinder
     */
    public function getFinder(array $options = [])
    {
        if (!$this->finder) {
            $this->finder = new FileFinder($options);
        }

        return $this->finder;
    }

    /**
     * @param FileFinder $finder
     */
    public function setFinder(FileFinder $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * @return string
     */
    public function getSourcePath()
    {
        return $this->sourcePath;
    }


    /**
     * @param string $sourcePath
     * @throws InvalidArgumentException
     */
    public function setSourcePath($sourcePath)
    {
        if ($sourcePath) {
            if (!is_dir($sourcePath)) {
                throw new InvalidArgumentException('The source path must be an existing dir path. Input: ' . $sourcePath);
            }

            $this->sourcePath = $sourcePath;
        }
    }

    /**
     * @return string
     */
    public function getArchiveFile()
    {
        return $this->archiveFile;
    }

    /**
     * @param string $archiveFile
     */
    public function setArchiveFile($archiveFile)
    {
        $this->archiveFile = trim($archiveFile);
    }
}
