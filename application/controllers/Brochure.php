<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Brochure extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('brochure_model');
    }

    public function index()
    {
        show_404();
    }

    public function view($hash)
    {
        if ($hash) {
            $getData = $this->brochure_model->get_single($hash);
            if (!$getData || !$getData->file_name) {
                redirect('/');
            }
            $data['brochure'] = $getData;
            $data['pdf_url'] = base_url('uploads/brochures/' . $getData->file_name);
            $this->load->view('admin/brochure/viewer', $data);
        } else {
            redirect('/');
        }
    }
}
