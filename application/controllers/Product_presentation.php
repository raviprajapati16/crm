<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product_presentation extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('product_presentation_model');
    }

    public function index()
    {
        show_404();
    }

    public function view($hash)
    {
        if ($hash) {
            $getData = $this->product_presentation_model->get_single($hash);
            if (!$getData || !$getData->file_name) {
                redirect('/');
            }
            $data['presentation'] = $getData;
            $data['pdf_url'] = base_url('uploads/product_presentations/' . $getData->file_name);
            $this->load->view('admin/product_presentation/viewer', $data);
        } else {
            redirect('/');
        }
    }
}
