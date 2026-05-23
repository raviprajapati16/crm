<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product_presentation extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('product_presentation_model');
    }

    public function index()
    {
        if (!has_permission('product_presentation', '', 'view')) {
            access_denied('product_presentation');
        }
        if ($this->input->post()) {
            $this->app->get_table_data('product_presentation');
        }
        $this->load->view('admin/product_presentation/index');
    }


    public function save()
    {
        if (!has_permission('product_presentation', '', 'create') || !has_permission('product_presentation', '', 'edit')) {
            access_denied('product_presentation');
        }
        $post_data = $this->input->post();
        if ($post_data) {
            if (!empty($post_data['id'])) {
                $getData = $this->product_presentation_model->get_single($post_data['id']);
                $updateArr["title"] = $post_data['title'];
                if (isset($_FILES['file']) && $_FILES['file']['size']) {
                    $upload_path = 'uploads/product_presentations/';
                    $new_filename =  unique_filename($upload_path, $_FILES['file']['name']);
                    _maybe_create_upload_path($upload_path);
                    $new_path = $upload_path . $new_filename;
                    if (move_uploaded_file($_FILES['file']['tmp_name'], $new_path)) {
                        $thumb_path = 'uploads/product_presentations/thumbnails';
                        _maybe_create_upload_path($thumb_path);
                        $thumbnail_name =  unique_filename($thumb_path, "thumbnail_" . time() . "_" . uniqid()) . ".jpg";
                        $final_thumbnail_path = $thumb_path . '/' . $thumbnail_name;
                        $thumbCheck = createPdfThumbnail($new_path, $final_thumbnail_path);
                        $updateArr["thumbnail"] =  NULL;
                        if ($thumbCheck) {
                            $updateArr["thumbnail"] =  $thumbnail_name;
                        }

                        $updateArr["file_name"] =  $new_filename;
                        //delete old file
                        $path = 'uploads/product_presentations/' . $getData->file_name;
                        if (file_exists($path)) {
                            unlink($path);
                        }

                        //delete old thumbnail file
                        $path = 'uploads/product_presentations/thumbnails/' . $getData->thumbnail;
                        if (file_exists($path)) {
                            unlink($path);
                        }
                    }
                }
                $check = $this->product_presentation_model->update($post_data['id'], $updateArr);
                if ($check) {
                    set_alert('success', "Presentation update Successfully.");
                    redirect($_SERVER['HTTP_REFERER']);
                } else {
                    set_alert('danger', "Error : Presentation not update.");
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } else if (empty($post_data['id'])) {
                if (isset($_FILES['file']) && $_FILES['file']['size']) {
                    $upload_path = 'uploads/product_presentations/';
                    $new_filename =  unique_filename($upload_path, $_FILES['file']['name']);
                    _maybe_create_upload_path($upload_path);
                    $new_path = $upload_path . $new_filename;
                    if (move_uploaded_file($_FILES['file']['tmp_name'], $new_path)) {
                        $insertArr = [
                            "hash" => app_generate_hash(),
                            "title" => $post_data['title'],
                            "file_name" => $new_filename,
                            "thumbnail" => NULL,
                        ];
                        $thumb_path = 'uploads/product_presentations/thumbnails';
                        _maybe_create_upload_path($thumb_path);
                        $thumbnail_name =  unique_filename($thumb_path, "thumbnail_" . time() . "_" . uniqid()) . ".jpg";
                        $final_thumbnail_path = $thumb_path . '/' . $thumbnail_name;
                        $thumbCheck = createPdfThumbnail($new_path, $final_thumbnail_path);
                        if ($thumbCheck) {
                            $insertArr["thumbnail"] =  $thumbnail_name;
                        }

                        $check = $this->product_presentation_model->add($insertArr);
                        if ($check) {
                            set_alert('success', "Presentation Created Successfully.");
                            redirect($_SERVER['HTTP_REFERER']);
                        } else {
                            set_alert('danger', "Error : Presentation not created.");
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                    }
                } else {
                    set_alert('danger', "Error : Enable to get the file.");
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } else {
                set_alert('danger', "Invalid Request");
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            set_alert('danger', "Invalid Request");
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete($id)
    {
        if (!has_permission('product_presentation', '', 'delete')) {
            access_denied('product_presentation');
        }
        if ($id) {
            $getData = $this->product_presentation_model->get_single($id);
            $delete = $this->product_presentation_model->delete($id);
            if ($delete) {
                if (!empty($getData)) {
                    $path = 'uploads/product_presentations/' . $getData->file_name;
                    if (file_exists($path)) {
                        unlink($path);
                    }
                    $path = 'uploads/product_presentations/thumbnails/' . $getData->thumbnail;
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
                set_alert('success', "Product dresentation successfully deleted.");
            } else {
                set_alert('danger', "Error : something went wrong.");
            }
        } else {
            set_alert('danger', "Error : Invalid product dresentation");
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function view($id)
    {
        if (!has_permission('product_presentation', '', 'view')) {
            access_denied('product_presentation');
        }

        if ($id) {
            $getData = $this->product_presentation_model->get_single($id);
            if (!$getData || !$getData->file_name) {
                set_alert('danger', "Error: File not found");
                redirect($_SERVER['HTTP_REFERER']);
            }
            $data['presentation'] = $getData;
            $data['pdf_url'] = site_url('uploads/product_presentations/' . $getData->file_name);
            $this->load->view('admin/product_presentation/viewer', $data);
        } else {
            set_alert('danger', "Error: Invalid product presentation");
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function get_data()
    {
        if (!has_permission('product_presentation', '', 'edit')) {
            access_denied('product_presentation');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $getData = $this->product_presentation_model->get_single($data['id']);

            if ($getData) {
                $result['data'] =  $getData;
                $path = 'uploads/product_presentations/' . $getData->file_name;
                if (file_exists($path)) {
                    $result['data']->previewLink = site_url($path);
                }
                $result['success'] = true;
            } else {
                $result['success'] = false;
                $result['message'] = "Invalid data";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request";
        }
        echo json_encode($result);
    }
}
