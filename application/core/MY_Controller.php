<?php

class MY_Controller extends CI_Controller
{
    protected $layout = 'layouts/main';
    
    public function __construct()
    {
        parent::__construct();
        
        if (ENVIRONMENT == "development") {
            $this->output->enable_profiler();
        }
    }
}
