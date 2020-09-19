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

    public function get_accounts() {
        $this->load->model('Account_model'); 

        $accounts = $this->Account_model->user(1)->getAll();

        return $accounts;
    }

    public function alu() {
        $this->load->model('Product_model');
        $alu = $this->Product_model->join_price()->getOne(13);
        return $alu;
    }

    public function product_groups() {
        $this->load->model('Product_groups_model');
        return $this->Product_groups_model->getAll();
    }
}
