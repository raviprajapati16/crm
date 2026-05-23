<?php
/* Author : Ashish H */
defined('BASEPATH') OR  exit('No direct script access allowed');

class Product_management extends AdminController{
    public $products;
    public function __construct()
	{
		parent::__construct();
		$this->load->model("product_management_model");
	}
	public function index(){
		redirect(admin_url(product_management_module_name ."/products"));
	}
	public function products()
	{
		$data["title"] = "Product Management";
		$data["products"] = $this->product_management_model->get_products();
		//$data["products1"] = $this->product_management_model->get_product(1);
        $this->load->view('products', $data);	
	}
	public function product($id = "")
	{
		if($id=="")
		{
			$data["title"] = "Add Product";
		}
		else
		{
			$data["title"] = "Edit Product";
		}
		$data["product"] = $this->product_management_model->get_product($id);
		if ($this->input->post())
        {
        	if($this->product_management_model->save_product())
        	{
        		set_alert('success','Product Saved Sucessfully');
        	}
        	redirect(admin_url(product_management_module_name."/products"));
        }
		$this->load->view('product', $data);	

	}
}
?>