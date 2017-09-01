<?php
/**
 */

namespace inhere\libraryPlus\web;

/**
 * Class ViewRenderer
 * @package inhere\libraryPlus\web
 * Render PHP view scripts into a PSR-7 Response object
 */
class ViewRenderer
{
    /**
     * @var string
     */
    protected $viewsPath;

    /**
     * 布局
     * @var string
     */
    protected $layout;

    /**
     * @var array
     */
    protected $attributes;

    /*
    in layout file '...<body>{_CONTENT_}</body>...'
     */
    const CONTENT_MARK = '{_CONTENT_}';

    /**
     * SlimRenderer constructor.
     * @param string $viewsPath
     * @param string $layout
     * @param array $attributes
     */
    public function __construct($viewsPath = '', $layout = '', array $attributes = [])
    {
        $this->viewsPath = rtrim($viewsPath, '/\\') . '/';
        $this->layout = $layout;
        $this->attributes = $attributes;
    }

    /**
     * Render a view
     * $data cannot contain view as a key
     * throws RuntimeException if $viewsPath . $view does not exist
     * @param string $view
     * @param array $data
     * @return string
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function render($view, array $data = [])
    {
        $output = $this->fetch($view, $data);

        // render layout
        if ($this->layout) {
            $mark = self::CONTENT_MARK;
            $main = $this->fetch($this->layout, $data);
            $output = preg_replace("/$mark/", $output, $main, 1);
        }

        return $output;
    }

    /**
     * @param $view
     * @param array $data
     * @return string
     */
    public function renderPartial($view, array $data = [])
    {
        return $this->fetch($view, $data);
    }

    /**
     * @param string $output
     * @param array $data
     * @return string
     */
    public function renderBody($output, array $data = [])
    {
        // render layout
        if ($this->layout) {
            $mark = self::CONTENT_MARK;
            $main = $this->fetch($this->layout, $data);
            $output = preg_replace("/$mark/", $output, $main, 1);
        }

        return $output;
    }

    /**
     * Get the attributes for the renderer
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set the attributes for the renderer
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Add an attribute
     * @param $key
     * @param $value
     */
    public function addAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Retrieve an attribute
     * @param $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (!isset($this->attributes[$key])) {
            return false;
        }

        return $this->attributes[$key];
    }

    /**
     * Get the view path
     * @return string
     */
    public function getViewsPath()
    {
        return $this->viewsPath;
    }

    /**
     * Set the view path
     * @param string $viewsPath
     */
    public function setViewsPath($viewsPath)
    {
        $this->viewsPath = rtrim($viewsPath, '/\\') . '/';
    }

    /**
     * Get the layout file
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Set the layout file
     * @param string $layout
     */
    public function setLayout($layout)
    {
        $this->layout = rtrim($layout, '/\\');
    }

    /**
     * Renders a view and returns the result as a string
     * cannot contain view as a key
     * throws RuntimeException if $viewsPath . $view does not exist
     * @param $view
     * @param array $data
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function fetch($view, array $data = [])
    {
        if (isset($data['view'])) {
            throw new \InvalidArgumentException('Duplicate view key found');
        }

        $file = $this->viewsPath . $view;

        if (!is_file($file)) {
            throw new \RuntimeException("View cannot render '$view' because the view does not exist");
        }

        /*
        foreach ($data as $k=>$val) {
            if (in_array($k, array_keys($this->attributes))) {
                throw new \InvalidArgumentException("Duplicate key found in data and renderer attributes. " . $k);
            }
        }
        */
        $data = array_merge($this->attributes, $data);

        try {
            ob_start();
            $this->protectedIncludeScope($file, $data);
            $output = ob_get_clean();
        } catch (\Throwable $e) { // PHP 7+
            ob_end_clean();
            throw $e;
        } catch (\Exception $e) { // PHP < 7
            ob_end_clean();
            throw $e;
        }

        return $output;
    }

    /**
     * @param string $view
     * @param array $data
     */
    protected function protectedIncludeScope($view, array $data)
    {
        extract($data, EXTR_OVERWRITE);
        include $view;
    }
}
