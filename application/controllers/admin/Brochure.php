<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Brochure extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('brochure_model');
    }

    public function index()
    {
        if (!has_permission('brochure', '', 'view')) {
            access_denied('brochure');
        }
        if ($this->input->post()) {
            $this->app->get_table_data('brochure');
        }
        $this->load->view('admin/brochure/index');
    }


    public function save()
    {
        if (!has_permission('brochure', '', 'create') || !has_permission('brochure', '', 'edit')) {
            access_denied('brochure');
        }
        $post_data = $this->input->post();
        if ($post_data) {
            if (!empty($post_data['id'])) {
                $getData = $this->brochure_model->get_single($post_data['id']);
                $updateArr["title"] = $post_data['title'];
                if (isset($_FILES['file']) && $_FILES['file']['size']) {
                    $upload_path = 'uploads/brochures/';
                    $new_filename =  unique_filename($upload_path, $_FILES['file']['name']);
                    _maybe_create_upload_path($upload_path);
                    $new_path = $upload_path . $new_filename;
                    if (move_uploaded_file($_FILES['file']['tmp_name'], $new_path)) {
                        $thumb_path = 'uploads/brochures/thumbnails';
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
                        $path = 'uploads/brochures/' . $getData->file_name;
                        if (file_exists($path)) {
                            unlink($path);
                        }

                        //delete old thumbnail file
                        $path = 'uploads/brochures/thumbnails/' . $getData->thumbnail;
                        if (file_exists($path)) {
                            unlink($path);
                        }
                    }
                }
                $check = $this->brochure_model->update($post_data['id'], $updateArr);
                if ($check) {
                    set_alert('success', "Presentation update Successfully.");
                    redirect($_SERVER['HTTP_REFERER']);
                } else {
                    set_alert('danger', "Error : Presentation not update.");
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } else if (empty($post_data['id'])) {
                if (isset($_FILES['file']) && $_FILES['file']['size']) {
                    $upload_path = 'uploads/brochures/';
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
                        $thumb_path = 'uploads/brochures/thumbnails';
                        _maybe_create_upload_path($thumb_path);
                        $thumbnail_name =  unique_filename($thumb_path, "thumbnail_" . time() . "_" . uniqid()) . ".jpg";
                        $final_thumbnail_path = $thumb_path . '/' . $thumbnail_name;
                        $thumbCheck = createPdfThumbnail($new_path, $final_thumbnail_path);
                        if ($thumbCheck) {
                            $insertArr["thumbnail"] =  $thumbnail_name;
                        }

                        $check = $this->brochure_model->add($insertArr);
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
        if (!has_permission('brochure', '', 'delete')) {
            access_denied('brochure');
        }
        if ($id) {
            $getData = $this->brochure_model->get_single($id);
            $delete = $this->brochure_model->delete($id);
            if ($delete) {
                if (!empty($getData)) {
                    $path = 'uploads/brochures/' . $getData->file_name;
                    if (file_exists($path)) {
                        unlink($path);
                    }
                    $path = 'uploads/brochures/thumbnails/' . $getData->thumbnail;
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
        if (!has_permission('brochure', '', 'view')) {
            access_denied('brochure');
        }

        if ($id) {
            $getData = $this->brochure_model->get_single($id);
            if (!$getData || !$getData->file_name) {
                set_alert('danger', "Error: File not found");
                redirect($_SERVER['HTTP_REFERER']);
            }
            $data['brochure'] = $getData;
            $data['pdf_url'] = base_url('uploads/brochures/' . $getData->file_name);
            $this->load->view('admin/brochure/viewer', $data);
        } else {
            set_alert('danger', "Error: Invalid product presentation");
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function get_data()
    {
        if (!has_permission('brochure', '', 'edit')) {
            access_denied('brochure');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $getData = $this->brochure_model->get_single($data['id']);

            if ($getData) {
                $result['data'] =  $getData;
                $path = 'uploads/brochures/' . $getData->file_name;
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
