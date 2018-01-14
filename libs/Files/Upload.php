<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 14-4-3
 * Time: 下午11:47
 * 文件上传
 */

namespace Inhere\LibraryPlus\Files;

/**
 * Class Upload
 * @package Inhere\LibraryPlus\Files
 */
class Upload
{
    /**
     * $_FILES
     * @var array
     */
    private $_data = [];

    /**
     * 错误信息
     * @var string
     */
    private $error;

    /**
     * 上传成功的 文件信息
     * @var array
     */
    private $uploadedFiles = [];

    /**
     * @var array
     */
    private $result = [];

    /**
     * @var array
     */
    public $config = [
        // 保存文件路径
        'path' => '',

        'basePath' => '',

        // 允许的文件类型 e.g. ['jpg', 'png']
        'ext' => ['jpg', 'jpeg', 'gif', 'bmp', 'png'],

        // 文件上传大小 最大值
        'maxSize' => 0,
    ];

    /**
     * @param array $config
     * @return static
     */
    public static function make(array $config = [])
    {
        return new static($config);
    }

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->_data = &$_FILES;
        $this->setConfig($config);
    }

    /**
     * upload file to a dir
     * @param string $name
     * @param string $dir
     * @return $this
     */
    public function saveTo($name, $dir)
    {
        return $this->fetch($name)->storeTo($dir);
    }

    /**
     * upload file and save as file
     * @param string $name
     * @param string $file
     * @return $this
     */
    public function saveAs($name, $file)
    {
        return $this->fetch($name)->storeAs($file);
    }

    /**
     * @param $name
     * @return $this
     */
    public function fetch($name)
    {
        if (!$name || !isset($this->_data[$name])) {
            $this->error = "name [$name] don't exists of the _FILES";

            return $this;
        }

        $this->result = $this->handle($this->_data[$name]);

        return $this;
    }

    /**
     * @param $dir
     * @return $this
     */
    public function storeTo($dir)
    {
        if (!$this->hasError()) {
            $this->moveToDir($this->result, $dir);
        }

        return $this;
    }

    /**
     * @param $file
     * @return $this
     */
    public function storeAs($file)
    {
        if (!$this->hasError()) {
            $this->moveToFile($this->result, $file);
        }

        return $this;
    }

    /**
     * @param array $sourceFile
     * @return array
     */
    protected function handle($sourceFile)
    {
        if ($this->hasError()) {
            return [];
        }

        $filename = $sourceFile['name'];

        if (is_array($filename)) {
            $metas = [];
            $file = $this->_decodeData([
                $filename,
                $sourceFile['type'],
                $sourceFile['tmp_name'],
                $sourceFile['error'],
                $sourceFile['size']
            ]);

            foreach ($file as $key => $value) {
                $metas[$key] = $this->handle($value);
            }

            return $metas;
        }

        $meta = $sourceFile;

        //文件信息
        $meta['ext'] = pathinfo($meta['name'], PATHINFO_EXTENSION);

        //没有文件 || 文件不合法，跳过
        if (!$this->validateFile($meta)) {
            return [];
        }

        return $meta;
    }

    /**
     * 文件上传
     * 返回文件信息多维数组|没有文件则返回FALSE(可用于判断)
     * @param array $names 取指定的name对应的文件
     * @param string $targetDir 存储目录路径
     * @return Upload
     */
    public function multi(array $names = [], $targetDir = '')
    {
        if (!$this->_data) {
            return $this;
        }

        !$targetDir && ($targetDir = $this->config['path']);

        foreach ($this->_data as $key => $file) {
            if ($names && !in_array($key, $names)) {
                continue;
            }

            $this->result[$key] = $this->_uploadHandle($file, $targetDir);
        }

        return $this;
    }

    /**
     * @param array $sourceFile
     * @param string $targetDir
     * @param \Closure $nameHandler
     * @return array
     */
    protected function _uploadHandle($sourceFile, $targetDir, \Closure $nameHandler = null)
    {
        if ($this->hasError()) {
            return [];
        }

        $filename = $sourceFile['name'];

        if (is_array($filename)) {
            $result = [];
            $file = $this->_decodeData([
                $filename,
                $sourceFile['type'],
                $sourceFile['tmp_name'],
                $sourceFile['error'],
                $sourceFile['size']
            ]);

            foreach ($file as $key => $value) {
                $result[$key] = $this->_uploadHandle($value, $targetDir);
            }
        } else {
            $file = $sourceFile;

            //文件信息
            $file['ext'] = pathinfo($file['name'], PATHINFO_EXTENSION);

            //没有文件 || 文件不合法，跳过
            if (!$this->validateFile($file)) {
                return [];
            }

            $result = $this->moveToDir($file, $targetDir, $nameHandler);
        }

        return $result;
    }

    /**
     * Method to decode a data array.
     * @param   array $data The data array to decode.
     * @return  array
     */
    protected function _decodeData(array $data)
    {
        $result = [];

        if (is_array($data[0])) {
            foreach ($data[0] as $k => $v) {
                $result[$k] = $this->_decodeData([
                    $data[0][$k], $data[1][$k], $data[2][$k], $data[3][$k], $data[4][$k]
                ]);
            }

            return $result;
        }

        return [
            'name' => $data[0],
            'type' => $data[1],
            'tmp_name' => $data[2],
            'error' => $data[3],
            'size' => $data[4]
        ];
    }

    /**
     * 存储文件 -- 移动临时文件到指定文件
     * @param array $file 上传文件信息数组
     * @param $targetFile
     * @return bool|array
     */
    private function moveToFile(array $file, $targetFile)
    {
        $dir = dirname($targetFile);

        if (!$this->_makeDir($dir)) {
            $this->error = "目录创建失败或者不可写. DIR: [$dir]";

            return false;
        }

        if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
            $this->error = '移动上传文件失败！';

            return false;
        }

        $file['newFile'] = $targetFile;

        return $file;
    }

    /**
     * 存储文件 -- 移动临时文件到指定目录
     * @param array $file 上传文件信息数组
     * @param string $targetDir 目标目录
     * @param \Closure $nameHandler 使用闭包回调自定义存储文件的命名
     * e.g:
     * $nameHandler = function ($fileInfo) {
     *      return date('Ymd'). '.' . $file['ext'];
     * };
     * @return bool|array
     */
    private function moveToDir(array $file, $targetDir = '', \Closure $nameHandler = null)
    {
        if ($nameHandler) {
            $nowName = $nameHandler($file, $targetDir);
        } else {
            $nowName = time() . '_' . mt_rand(1000, 9999) . '.' . $file['ext'];
        }

        $filePath = ($targetDir ?: $this->config['path']) . DIRECTORY_SEPARATOR . $nowName;
        $dir = dirname($filePath);

        if (!$this->_makeDir($dir)) {
            $this->error = "目录创建失败或者不可写.[$dir]";

            return false;
        }

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            $this->error = '移动上传文件失败！';

            return false;
        }

        $file['newFile'] = $filePath;
        $file['newName'] = $nowName;

        return $file;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasFile($name): bool
    {
        return isset($this->_data[$name]);
    }

    /**
     * 目录创建 目录验证
     * @param $path
     * @return bool
     */
    protected function _makeDir($path): bool
    {
        return $path && (is_dir($path) || @mkdir($path, 0664, true)) && is_writable($path);
    }

    /**
     * 验证上传文件,是否有文件、上传类型、文件大小
     * @param $file
     * @return bool
     */
    private function validateFile($file)
    {
        // check system error
        if ((int)$file['error'] !== 0) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:   # 1
                    $this->error = '超过php.ini 配置文件指定大小！';
                    break;
                case UPLOAD_ERR_FORM_SIZE:  # 2
                    $this->error = '上传文件超过Html表单指定大小！';
                    break;
                case UPLOAD_ERR_PARTIAL:    # 3
                    $this->error = '没有上传任何文件！';
                    break;
                case UPLOAD_ERR_NO_FILE:    # 4
                    $this->error = '文件只上传了一部分！';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR: # 5
                    $this->error = '没有文件上传的临时目录！';
                    break;
                case UPLOAD_ERR_CANT_WRITE: # 6
                    $this->error = '不能写入临时上传文件！';
                    break;
            }

            return false;
        }

        $maxSize = $this->config['maxSize'] > 0 ? $this->config['maxSize'] : 0;
        $extList = $this->config['ext'];
        $fileExt = strtolower($file['ext']);

        if ($extList && !in_array($fileExt, $extList)) {
            $this->error = '不允许的上传文件类型！';
        } elseif ($maxSize && $file['size'] > $maxSize) {
            $this->error = '上传文件超出允许大小！';
        } elseif (!is_uploaded_file($file['tmp_name'])) {
            $this->error = '非法文件！';
        }

        return $this->error === null;
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return $this->error !== null;
    }

    /**
     * @return bool
     */
    public function isOk()
    {
        return !$this->hasError();
    }

    /**
     * @return bool
     */
    public function isFail()
    {
        return $this->hasError();
    }

    /**
     * 返回上传时发生的错误原因
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    /**
     * @return array
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * @param null|string $key
     * @return array|string
     */
    public function getResult($key = null)
    {
        return $key && isset($this->result[$key]) ? $this->result[$key] : $this->result;
    }
}
