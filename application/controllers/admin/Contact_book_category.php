<?php
class Contact_book_category extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('contact_book_category_model');
    }


    public function index()
    {
        if (!is_admin()) {
            access_denied('contact_book_category');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('contact_book_category');
        }
        $this->load->view('admin/contact_book_category/manage');
    }


    public function save()
    {
        if (!is_admin()) {
            access_denied('contact_book_category');
        }

        $data = $this->input->post();
        if (empty($data['id'])) {
            $check = $this->contact_book_category_model->insert(["name" => $data['name']]);
            if ($check) {
                set_alert('success', "Category created successfully");
            } else {
                set_alert('danger', 'Error: Category not created.');
            }
            redirect(admin_url('contact_book_category'));
        } else {
            $id = $data['id'];
            $check = $this->contact_book_category_model->update($id, ["name" => $data['name']]);
            set_alert('success', "Category successfully updated");
        }
        redirect(admin_url('contact_book_category'));
    }


    public function save_ajax()
    {
        $data = $this->input->post();
        $insert = $this->contact_book_category_model->insert(['name' => $data['name']]);
        if ($insert) {
            echo json_encode(['success' => true, 'id' => $insert, 'message' => 'Category created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: Category not created.']);
        }
    }

    public function delete($id)
    {
        if (!is_admin()) {
            access_denied('contact_book_category');
        }

        if (!$id) {
            redirect(admin_url('contact_book_category'));
        }

        $this->db->where('category', $id);
        $used = $this->db->count_all_results(db_prefix().'contact_book');

        if ($used > 0) {
            set_alert('danger', 'Error: Category cannot be deleted because it is associated with one or more contacts.');
            redirect(admin_url('contact_book_category'));
        }

        $success = $this->contact_book_category_model->delete($id);
        if ($success) {
            set_alert('success', 'Category deleted successfully.');
        } else {
            set_alert('danger', 'Error: Category could not be deleted.');
        }

        redirect(admin_url('contact_book_category'));
    }
}
