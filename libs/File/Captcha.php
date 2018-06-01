<?php
/**
 * Created by JetBrains PhpStorm.
 * User: simon
 * Date: 14-3-16
 * Time: 下午1:34
 * name: 生成图片验证码,Captcha-验证码 .
 * use :    1. $captcha = new Captcha(....); $captcha->show()
 *          2. Captcha::make(...)->show()
 */

namespace ToolkitPlus\File;

use Inhere\Exceptions\ExtensionMissException;
use Inhere\Exceptions\NotFoundException;

/**
 * Class Captcha
 * @package ToolkitPlus\File
 */
class Captcha
{
    private $img;               // 资源
    public $width;              // 画布宽
    public $height;             // 画布高
    public $bgColor;            // 背景色
    public $bgImage;            // 背景图 $bgColor $bgImage 二选一
    public $font;               // 字体

    public $pixelNum;           // 干扰点数量
    public $lineNum = 0;            // 干扰线条数量
    public $aecNum = 0;             // 干扰弧线数量
    public $fontNum;            // 干扰字体数量

    public $fontSize;           // 产生验证码字体大小
    public $charNum;            // 产生验证码字符个数
    public $codeStr;            // 产生验证码字符串, 验证码的随机种子
    public $captcha;            // 产生的验证码

    public $config = [];            // 配置


    // 存入SESSION的键值
    protected static $sessionKey = 'app_captcha';

    /**
     * @param array $config
     * @return static
     * @throws \Inhere\Exceptions\NotFoundException
     * @throws \Inhere\Exceptions\ExtensionMissException
     */
    public static function make(array $config = [])
    {
        return new static($config);
    }

    /**
     * @param array $config
     * @throws ExtensionMissException
     * @throws NotFoundException
     */
    public function __construct(array $config = [])
    {
        if (!$this->checkGd()) {
            throw new ExtensionMissException('This [gd] extension is required.');
        }

        $config = array_merge($this->defaultConfig(), $config);

        $this->config = $config;
        $this->font = $config['font'];

        if (!is_file($this->font)) {
            throw new NotFoundException('Verification code font file does not exist. FILE: ' . $this->font);
        }

        if (!empty($config['sessionKey'])) {
            static::$sessionKey = $config['sessionKey'];
        }

        $this->codeStr = $config['randStr'];
        $this->fontSize = $config['fontSize'];
        $this->charNum = $config['length'];
        $this->width = $config['width'];
        $this->height = $config['height'];
        $this->bgColor = $config['bgColor'];
        $this->bgImage = $config['bgImage'];
        $this->pixelNum = $config['pixelNum'];
        $this->fontNum = $config['fontNum'];
    }

    /**
     * @return array
     */
    public function defaultConfig(): array
    {
        return [
            // 字体文件
            'font' => \dirname(__DIR__) . '/resources/fonts/Montserrat-Bold.ttf',
            'randStr' => '23456789abcdefghigkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ',
            'width' => '120',
            'height' => '45',
            'bgColor' => '#eeeeee',
            'bgImage' => \dirname(__DIR__) . '/resources/images/captcha-bg/06.png',
            'length' => '4',
            'fontColor' => '',
            //  验证码字体大小
            'fontSize' => '24',
            // 干扰点数量
            'pixelNum' => '10',
            // 干扰字符数量
            'fontNum' => '50',
        ];
    }

    // 画干扰点-可选 imagesetpixel($this->img,x坐标,y坐标,颜色)

    /**
     * @return $this
     * @throws \Exception
     */
    public function drawPixel(): self
    {
        for ($i = 1; $i <= $this->pixelNum; $i++) {
            //$pixelColor = imagecolorallocate( $this->img,rand(100,240), random_int(100,240), random_int(100,
            //240) );//点颜色
            //imagesetpixel($this->img,rand(0,$this->width),rand(0,$this->height),$pixelColor);
            $char = '.';
            $pixelColor = imagecolorallocate($this->img, random_int(140, 200), random_int(140, 200), random_int(140, 200));
            imagefttext(
                $this->img, 8, random_int(-30, 30), random_int(6, $this->width), random_int(6, $this->height - 5),
                $pixelColor, $this->font, $char
            );
        }

        return $this;
    }

    /**
     * 画干扰直线-可选
     * @return $this
     * @throws \Exception
     */
    public function drawLine(): self
    {
        for ($i = 1; $i <= $this->lineNum; $i++) {
            $lineColor = imagecolorallocate($this->img, random_int(150, 250), random_int(150, 250), random_int(150, 250));
            //($this->img,起点坐标x.y，终点坐标x.y，颜色)
            imageline(
                $this->img, random_int(0, $this->width), random_int(0, $this->height),
                random_int(0, $this->width), random_int(0, $this->height), $lineColor
            );
        }

        return $this;
    }

    /**
     * 画干扰弧线-可选
     * @return $this
     * @throws \Exception
     */
    public function drawAec(): self
    {
        for ($i = 1; $i <= $this->aecNum; $i++) {
            $arcColor = imagecolorallocate($this->img, random_int(150, 250), random_int(150, 250), random_int(150, 250));
            imagearc(
                $this->img, random_int(0, $this->width), random_int(0, $this->height), random_int(0, 100),
                random_int(0, 100), random_int(-90, 90), random_int(70, 360), $arcColor
            );
        }

        return $this;
    }

    /**
     * 产生随机字符,验证码,并写入图像
     * @throws \Exception
     */
    public function drawChar()
    {
        $x = ($this->width - 10) / $this->charNum;
        $captchaStr = '';//保存产生的字符串

        for ($i = 0; $i < $this->charNum; $i++) {
            $char = $this->codeStr[random_int(0, \strlen($this->codeStr) - 1)];
            $captchaStr .= $char;
            $fontColor = imagecolorallocate($this->img, random_int(80, 200), random_int(80, 200), random_int(80, 200));
            imagefttext(
                $this->img, $this->fontSize, random_int(-30, 30), $i * $x + random_int(6, 10),
                random_int($this->height / 1.3, $this->height - 5), $fontColor,
                $this->font, $char
            );
        }

        $this->captcha = strtolower($captchaStr);

        //把纯的验证码字符串放置到SESSION中进行保存，便于后面进行验证对比
        $_SESSION[static::$sessionKey] = md5($this->captcha);

        //设置cookie到前端浏览器，可用于前端验证
        setcookie(static::$sessionKey, md5($this->captcha));
    }

    /**
     * 填充干扰字符-可选
     * @throws \Exception
     */
    public function drawChars()
    {
        for ($i = 0; $i < $this->fontNum; $i++) {
            $char = $this->codeStr[random_int(0, \strlen($this->codeStr) - 1)];
            $fontColor = imagecolorallocate($this->img, random_int(180, 240), random_int(180, 240), random_int(180, 240));
            imagefttext(
                $this->img, random_int(4, 8), random_int(-30, 40), random_int(8, $this->width - 10),
                random_int(10, $this->height - 10), $fontColor, $this->font, $char
            );
        }
    }

    /**
     * 生成图像资源，Captcha-验证码
     * @return $this
     * @throws \Exception
     */
    public function create(): self
    {
        if ($this->bgImage && is_file($this->bgImage)) {
            // 从背景图片建立背景画布
            $this->img = imagecreatefrompng($this->bgImage);
        } else {
            // 手动建立背景画布,图像资源
            $this->img = imagecreatetruecolor($this->width, $this->height);

            //给画布填充矩形的背景色rgb(230, 255, 230);
            $bgColor = $this->bgColor;

            //背景色
            $bgColor = imagecolorallocate(
                $this->img, hexdec(substr($bgColor, 1, 2)),
                hexdec(substr($bgColor, 3, 2)), hexdec(substr($bgColor, 5, 2))
            );

            imagefilledrectangle($this->img, 0, 0, $this->width, $this->height, $bgColor);
        }

        //给资源画上边框-可选 rgb(153, 153, 255)
//        $borderColor = imagecolorallocate($this->img, 153, 153, 255); // 0-255
//        imagerectangle($this->img, 0, 0, $this->width-1, $this->height-1,$borderColor);

        $this->drawLine();
        $this->drawChar();
        $this->drawPixel();
        $this->drawChars();

        return $this;
    }

    /**
     * 显示
     * @return bool
     * @throws \Exception
     */
    public function show(): bool
    {
        $this->create();

        header('Cache-Control: max-age=1, s-maxage=1, no-cache, must-revalidate');
        header('Content-type: image/png;charset=utf8');//生成图片格式png jpeg 。。。

        ob_clean();
        //生成图片,在浏览器中进行显示-格式png，与上面的header声明对应
        $success = imagepng($this->img);
        // 已经显示图片后，可销毁，释放内存（可选）
        imagedestroy($this->img);

        return $success;
    }

    /**
     * @return mixed
     */
    public function getImg()
    {
        return $this->img;
    }

    /**
     * 返回验证码
     */
    public function getCaptcha()
    {
        return $this->captcha;
    }

    /**
     * @param $captcha
     * @return bool
     */
    public static function verify($captcha): bool
    {
        return isset($_SESSION[static::$sessionKey]) && md5($captcha) === $_SESSION[static::$sessionKey];
    }

    private function checkGd(): bool
    {
        return \extension_loaded('gd') && \function_exists('imagepng');
    }
}
