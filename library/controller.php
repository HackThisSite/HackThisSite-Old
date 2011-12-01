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

class Controller
{
    public $view = array();
    protected $parsedViewResult = '';
    protected $request;
    private $controllerState = array(
        'viewPath' => '',
        'viewClass' => '',
        'errors' => array()
    );

    // class wide error messages
    const E_404 = "404";

    public function __construct($request, $viewData = 0, $silent = 0)
    {
        if (is_array($viewData)) $this->view = $viewData;
        
        // The setting of $silent to non zero will allow us to
        // initialize a controller object without implicitly calling
        // a controller method, this is done by returning immediately.
        $this->request = $request;
        if ($silent) return;

        $this->processRequest();
    }

    public function processRequest()
    {
        // Pull view driver from extension
        $extension = explode('.', end($this->request));
        if (count($extension) == 2)
        {
            $this->driver = $extension[1];
            array_pop($this->request);
            $this->request[] = $extension[0];
        }
        else
        {
            $this->driver = 'traditional';
        }

        // If no method was specified default to index
        $method = (isset($this->request[0])) ?
                                             array_shift($this->request)
                                             : 'index';
        
        $this->__call($method, $this->request);
    }

    // A wrapper to call controller methods
    public function __call($name, $arguments)
    {
        $controller = substr(get_class($this), 11);

        if (!method_exists($this, $name)) {
            $name = 'nil';
            $this->setView('nil');
        } else {
            // Set the implicit view
            $this->setView($controller . '/' . $name);
        }

        // Call the actual function.
        $this->$name($arguments);
        
        // Load and parse view
        $this->parsedViewResult = new View(
            $this->controllerState['view'],
            $this->view,
            $this->driver
        );
        
        $observer = Observer::singleton();
        $observer->trigger("controller/ended");

        return $this->parsedViewResult;

    }

    // A method to call static controller methods
    public function callStatic($controller, $name)
    {
        $extension = explode('.', end($this->request));
        if (count($extension) == 2)
        {
            $this->driver = $extension[1];
            array_pop($this->request);
            $this->request[] = $extension[0];
        }
        else
        {
            $this->driver = 'traditional';
        }

        $this->setView($controller . '/' . $name);

        $this->parsedViewResult = new View(
            $this->controllerState['view'],
            $this->view,
            $this->driver
        );

        return $this->parsedViewResult;
    }

    public function getResult()
    {
        return $this->parsedViewResult;
    }

    public function setView($view)
    {
        $this->controllerState['view'] = $view;
    }


    public function getDriver()
    {
        return $this->driver;
    }

    public function __toString()
    {
        return (string)$this->parsedViewResult;
    }
    
    private function nil() {}
}
