<?php
/**
Copyright (c) 2010, HackThisSite.org
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of the HackThisSite.org nor the
      names of its contributors may be used to endorse or promote products
      derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS ``AS IS'' AND ANY
EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

/**
* Authors:
*   Thetan ( Joseph Moniz )
**/

class View
{
    private $viewPath = '';
    private $data = array();
    private $parsed;
    private $driver;

    const VIEW_PATH     = '/application/views/';
    const VIEW_EXT      = '.php';
    const VIEW_SUFFIX   = '_view';

    const DRIVER_PREFIX      = 'driver_';
    const DRIVER_TRADITIONAL = 'traditional';

    public function __construct($viewPath, $data = array(), $driver = self::DRIVER_TRADITIONAL)
    {
        $this->viewPath = dirname(dirname(__FILE__)) . self::VIEW_PATH . 
            Layout::getLayout() . '/' . $viewPath . self::VIEW_EXT;
        
        if (!file_exists($this->viewPath))
            $this->viewPath = dirname(dirname(__FILE__)) . self::VIEW_PATH . 
                'main/' . $viewPath . self::VIEW_EXT;

        $this->driver = self::DRIVER_PREFIX . $driver . self::VIEW_SUFFIX;
        if (!class_exists($this->driver)) die('Invalid driver.');
        
        $this->data = $data;
    }

    // Wrapper for getting view variables
    public function __get($name)
    {
        return (isset($this->data[$name])) ? $this->data[$name] : false;
    }

    // Wrapper for setting view variables
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    // This function does the heavy lifting for the view.
    public function render()
    {
        if ($this->parsed) { return $this->parsed; }

        $driver = $this->driver;

        return $this->parsed = $driver::render($this->viewPath, $this->data);

    }

    public function __toString()
    {
        return $this->render();
    }
}
?>
