<?php
/**
 * This file is part of "Modernizing Legacy Applications in PHP".
 *
 * @copyright 2014 Paul M. Jones <pmjones88@gmail.com>
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Mlaphp;

/**
 * Encapsulates a plain old PHP response.
 */
class Response
{
    /**
     * The callable to be invoked with `call_user_func()` as the last step
     * in the `send()` process.
     *
     * @var callable
     */
    protected $func;

    /**
     * The buffer for HTTP header calls.
     *
     * @var array
     */
    protected $headers = array();

    /**
     * Variables to extract into the view scope.
     *
     * @var array
     */
    protected $vars = array();

    /**
     * A view file to require in its own scope.
     *
     * @var string
     */
    protected $view;

    /**
     * Sets the path to the view file.
     *
     * @param string $view The path to the view file.
     * @return null
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * Gets the path to the view file.
     *
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Sets the variables to be extracted into the view scope.
     *
     * @param array $vars The variables to be extracted into the view scope.
     * @return null
     */
    public function setVars(array $vars)
    {
        unset($vars['this']);
        $this->vars = $vars;
    }

    /**
     * Gets the variables to be extracted into the view scope.
     *
     * @return array
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * Sets the callable to be invoked with `call_user_func()` as the last step
     * in the `send()` process.
     *
     * @param callable $func The callable to be invoked.
     * @return null
     */
    public function setFunc($func)
    {
        $this->func = $func;
    }

    /**
     * Gets the callable to be invoked with `call_user_func()` as the last step
     * in the `send()` process.
     *
     * @return callable
     */
    public function getFunc()
    {
        return $this->func;
    }

    /**
     * Buffers a call to `header()`.
     *
     * @return null
     */
    public function header()
    {
        $args = func_get_args();
        array_unshift($args, 'header');
        $this->headers[] = $args;
    }

    /**
     * Buffers a call to `setcookie()`.
     *
     * @return bool
     */
    public function setCookie()
    {
        $args = func_get_args();
        array_unshift($args, 'setcookie');
        $this->headers[] = $args;
        return true;
    }

    /**
     * Buffers a call to `setrawcookie()`.
     *
     * @return bool
     */
    public function setRawCookie()
    {
        $args = func_get_args();
        array_unshift($args, 'setrawcookie');
        $this->headers[] = $args;
        return true;
    }

    /**
     * Returns the buffer for HTTP header calls.
     *
     * @return bool
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Outputs the buffered headers, buffered view, and calls the user function.
     *
     * @return null
     */
    public function send()
    {
        $buffered_output = $this->requireView();
        $this->sendHeaders();
        echo $buffered_output;
        $this->callFunc();
    }

    /**
     * Requires the view in its own scope with etracted variables and returns
     * the buffered output.
     *
     * @return string
     */
    public function requireView()
    {
        if (! $this->view) {
            return '';
        }

        extract($this->vars);
        ob_start();
        require $this->view;
        return ob_get_clean();
    }

    /**
     * Outputs the buffered calls to `header`, `setcookie`, etc.
     *
     * @return null
     */
    public function sendHeaders()
    {
        foreach ($this->headers as $args) {
            $func = array_shift($args);
            call_user_func_array($func, $args);
        }
    }

    /**
     * Calls `$this->func`, passing `$this` as the only argument.
     *
     * @return null
     */
    public function callFunc()
    {
        if (! $this->func) {
            return;
        }

        call_user_func($this->func, $this);
    }
}
