<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tutorials_videos extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('tutorials_videos_model');
    }

    public function index()
    {
        if (!has_permission('tutorials_videos', '', 'view')) {
            access_denied('tutorials_videos');
        }
        if ($this->input->post()) {
            $this->app->get_table_data('tutorials_videos');
        }
        $this->load->view('admin/tutorials_videos/index');
    }

    public function save()
    {
        if (!has_permission('tutorials_videos', '', 'create') || !has_permission('tutorials_videos', '', 'edit')) {
            access_denied('tutorials_videos');
        }
        $post_data = $this->input->post();
        if ($post_data) {
            if (!empty($post_data['id'])) {
                $check = $this->tutorials_videos_model->update($post_data['id'], $post_data);
                if ($check) {
                    set_alert('success', "Tutorial update Successfully.");
                } else {
                    set_alert('danger', "Error : Tutorial not update.");
                }
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                $check = $this->tutorials_videos_model->add($post_data);
                if ($check) {
                    set_alert('success', "Tutorial Created Successfully.");
                    redirect($_SERVER['HTTP_REFERER']);
                } else {
                    set_alert('danger', "Error : Tutorial not created.");
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
        } else {
            set_alert('danger', "Invalid Request");
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function get_data()
    {
        if (!has_permission('tutorials_videos', '', 'edit')) {
            access_denied('tutorials_videos');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $getData = $this->tutorials_videos_model->get_single($data['id']);
            if ($getData) {
                $result['data'] =  $getData;
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

    public function delete($id)
    {
        if (!has_permission('tutorials_videos', '', 'delete')) {
            access_denied('tutorials_videos');
        }
        if ($id) {
            if (total_rows(db_prefix() . 'tutorial_links', ['video_id' => $id, 'active' => '1']) > 0) {
                set_alert('danger', "Sorry ! This tutorial is used as tutorial link");
                redirect(admin_url('tutorials_videos'));
            }
            $delete = $this->tutorials_videos_model->delete($id);
            if ($delete) {
                set_alert('success', "Tutorial Video successfully deleted.");
            } else {
                set_alert('danger', "Error : something went wrong.");
            }
        } else {
            set_alert('danger', "Error : Invalid tutorial");
        }
        redirect(admin_url('tutorials_videos'));
    }

    public function links()
    {
        if (!has_permission('tutorials_links', '', 'view')) {
            access_denied('tutorials_links');
        }
        if ($this->input->post()) {
            $this->app->get_table_data('tutorials_links');
        }
        $data['tutorialVideos'] = $this->tutorials_videos_model->get_tutorial_videos();
        $this->load->view('admin/tutorials_videos/links', $data);
    }

    public function get_links_data()
    {
        if (!has_permission('tutorials_links', '', 'edit')) {
            ajax_access_denied('tutorials_links');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $getData = $this->tutorials_videos_model->get_single_link($data['id']);
            if ($getData) {
                $result['data'] =  $getData;
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

    public function link_save()
    {
        if (!has_permission('tutorials_links', '', 'edit')) {
            access_denied('tutorials_links');
        }
        $post_data = $this->input->post();
        if ($post_data) {
            if (!empty($post_data['id'])) {
                $post_data['active'] = (isset($post_data['active'])) ? "1" : "0";
                $check = $this->tutorials_videos_model->update_link($post_data['id'], $post_data);
                if ($check) {
                    set_alert('success', "Tutorial update Successfully.");
                } else {
                    set_alert('danger', "Error : Tutorial not update.");
                }
            } else {
                set_alert('danger', "Invalid Request");
            }
        } else {
            set_alert('danger', "Invalid Request");
        }
        redirect($_SERVER['HTTP_REFERER']);
    }
}
