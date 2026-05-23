<?php

use Mpdf\Tag\P;

defined('BASEPATH') or exit('No direct script access allowed');

class Leads_questionnaire_group extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Lead_questions_model');
        $this->load->model('Lead_inquiry_form_images_model');
        if (!is_admin()) {
            access_denied('Leads Questionnaire Group');
        }
    }

    public function index()
    {
        if (!is_admin()) {
            access_denied('Leads Questionnaire Group');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('leads_questionnaire_group');
        }
        $data['main_group_data'] = $this->Lead_questions_model->get_main_group();
        $data['sub_group_data'] = $this->Lead_questions_model->get_sub_group();
        $data['title'] = 'Leads Questionnaire Group';
        $this->load->view('admin/leads_questions/manage_questionnaire_group', $data);
    }

    public function manage_questions($main_group_id, $sub_group_id = "")
    {
        if (!is_admin()) {
            access_denied('Leads Questionnaire List');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('leads_questions', ["main_group_id" => $main_group_id, "sub_group_id"  => $sub_group_id]);
        }
        $data['title'] = 'Leads Questionnaire List';
        $data['main_group_data'] = $this->Lead_questions_model->get_main_group_by_id($main_group_id);
        if ($sub_group_id) {
            $data['sub_group_data'] = $this->Lead_questions_model->get_sub_group_by_id($sub_group_id);
        }
        $data['main_group_data_arr'] = $this->Lead_questions_model->get_main_group();
        $data['sub_group_data_arr'] = $this->Lead_questions_model->get_sub_group();
        $this->load->view('admin/leads_questions/manage_questions', $data);
    }

    public function delete($id)
    {
        if (!is_admin()) {
            access_denied('Delete Lead Question');
        }
        if (!$id) {
            redirect($_SERVER['HTTP_REFERER']);
        }
        $response = $this->Lead_questions_model->update_question([], $id, true);
        if ($response) {
            set_alert('success', _l('lead_question_deleted'));
        } else {
            set_alert('warning', _l('lead_question_not_deleted'));
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function delete_bulk()
    {
        if (!is_admin()) {
            access_denied('Delete Bulk Lead Question');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            if (!empty($data['ids'])) {
                $total = count($data['ids']);
                $deleted = 0;
                foreach ($data['ids'] as $id) {
                    $check = $this->Lead_questions_model->update_question([], $id, true);
                    if ($check) {
                        $deleted++;
                    }
                }
                if ($total == $deleted) {
                    $result['success'] = true;
                    $result['message'] = "Question deleted successfully";
                } else {
                    $result['success'] = true;
                    $result['message'] = "$deleted out of $total question deleted successfully";
                }
            } else {
                $result['success'] = false;
                $result['message'] = "Please select question";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request";
        }
        echo json_encode($result);
    }

    public function question()
    {
        if (!is_admin()) {
            access_denied('Leads Questions');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            if (isset($data['is_required'])) {
                $data['is_required'] = "1";
            } else {
                $data['is_required'] = "0";
            }
            $checkQuestion = $this->Lead_questions_model->check_question($data['question'], $data['main_group_id'], $data['sub_group_id'], $data['id']);
            $redirectLink = admin_url('leads_questionnaire_group/manage_questions/' . $data['main_group_id'] . '/' . $data['sub_group_id']);
            if ($data['id'] == '') {
                $data['order_no'] = 1;
                if ($checkQuestion == 0) {
                    $lastQuestion = $this->Lead_questions_model->get_last_question($data['main_group_id'], $data['sub_group_id']);
                    if ($lastQuestion) {
                        $data['order_no'] = $lastQuestion['order_no'] + 1;
                    }
                    $id = $this->Lead_questions_model->add_question($data);
                    if ($id) {
                        set_alert('success', _l('added_successfully', _l('lead_question')));
                        redirect($redirectLink);
                    }
                } else {
                    set_alert('danger', "This question is already exists in current group.");
                    redirect($redirectLink);
                }
            } else {
                if ($checkQuestion == 0) {
                    $success = $this->Lead_questions_model->update_question($data, $data['id']);
                    if ($success) {
                        set_alert('success', _l('updated_successfully', _l('lead_question')));
                        redirect($redirectLink);
                    }
                } else {
                    set_alert('danger', "This question is already exists in current group.");
                    redirect($redirectLink);
                }
            }
        }
        redirect(admin_url('leads_questionnaire_group/'));
    }

    public function get_question()
    {
        if (!is_admin()) {
            access_denied('Leads Questions');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $question_data =  $this->Lead_questions_model->get($data['id']);
            if ($question_data) {
                $result['success'] = true;
                $result['data'] =  $question_data;
            } else {
                $result['success'] = false;
                $result['message'] = "Invalid Question";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request";
        }
        echo json_encode($result);
    }

    public function change_question_status($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->Lead_questions_model->change_custom_field_status($id, $status);
        }
    }

    public function question_reorder()
    {
        if (!is_admin()) {
            access_denied('Leads Questions');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $i = 1;
            foreach ($data['order'] as $id) {
                $this->Lead_questions_model->update_question(["order_no" => $i], $id);
                $i++;
            }
            $result['success'] = true;
            $result['message'] =  "Question  re-ordered successfully";
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request";
        }
        echo json_encode($result);
    }

    public function copy()
    {
        if (!is_admin()) {
            access_denied('Copy Question Group');
        }
        $data = $this->input->post();
        if (isset($data['main_group_id']) && isset($data['sub_group_id']) && isset($data['type'])) {
            if (empty($data['sub_group_id'])) {
                $data['sub_group_id'] = 0;
            }
            if (empty($data['from_sub_group_id'])) {
                $data['from_sub_group_id'] = 0;
            }
            // Copy all questions as per selected group.
            $questionsData = [];
            if ($data['type'] == "group_copy") {
                $questionsData = $this->Lead_questions_model->get_questions_by_group($data['from_main_group_id'], $data['from_sub_group_id']);
            } else if ($data['type'] == "questions_copy") { // Copy selected questions
                $questionsData = $this->Lead_questions_model->get_questions_by_id($data['question_id']);
            }
            if (!empty($questionsData)) {
                foreach ($questionsData as $key => $item) {
                    $checkQuestion = $this->Lead_questions_model->check_question($item['question'], $data['main_group_id'], $data['sub_group_id']);
                    if ($checkQuestion == 0) {
                        $questionsData[$key]['main_group_id'] = $data['main_group_id'];
                        $questionsData[$key]['sub_group_id'] = $data['sub_group_id'];
                        unset($questionsData[$key]['updated_at']);
                        unset($questionsData[$key]['updated_by']);
                        unset($questionsData[$key]['deleted_by']);
                        unset($questionsData[$key]['datedeleted']);
                        unset($questionsData[$key]['id']);
                        $this->Lead_questions_model->add_question($questionsData[$key]);
                    }
                    set_alert('success', "Questions successfully copied.");
                }
            } else {
                set_alert('warning', "Sorry! Questions not available.");
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function get_suggestions()
    {
        $data = $this->input->post();
        $result = [];
        $suggestionData = $this->Lead_questions_model->get_questions_suggestions($data['text']);
        if (!empty($suggestionData)) {
            $result = array_values(array_unique(array_filter(array_column($suggestionData, 'question'))));
        }
        header('Content-Type: application/json');
        echo json_encode(array_values($result));
    }

    public function lead_inquiry_form_images()
    {
        if (!is_admin()) {
            access_denied('Leads Inquiry Form Images');
        }
        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            $type = isset($data['slider_images']) ? 'slider_images' : 'popup_images';
            $this->app->get_table_data('leads_inquiry_form_images', ["type" => $type]);
        }
        $this->load->view('admin/leads_questions/lead_inquiry_form_images');
    }

    public function image_status_change()
    {
        $data = $this->input->post();
        if (isset($data['id']) && isset($data['status']) && isset($data['type'])) {
            if ($data['status'] == "1" && $data['type'] == "popup_images") {
                $this->Lead_inquiry_form_images_model->deactive_pop_images();
            }
            $updateData =  $this->Lead_inquiry_form_images_model->update_image(["is_active" => $data['status']], $data['id']);
            $status = ($data['status'] == "1") ? "Active " : " In-Active";
            if ($updateData) {
                $result['success'] = true;
                $result['message'] = "Image $status successfully";
            } else {
                $result['success'] = false;
                $result['message'] = "Image $status failed.";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid Request";
        }
        echo json_encode($result);
    }

    public function background_slider_active_inactive()
    {
        $data = $this->input->post();
        if (isset($data['status'])) {
            $updateData = update_option("lead_forms_background_image_slider_active", $data['status']);
            $status = ($data['status'] == "1") ? "Active " : " In-Active";
            if ($updateData) {
                $result['success'] = true;
                $result['message'] = "Background Image Slider $status successfully";
            } else {
                $result['success'] = false;
                $result['message'] = "background_slider_active_inactive $status failed.";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid Request";
        }
        echo json_encode($result);
    }

    public function delete_image($id)
    {
        $getImage = $this->Lead_inquiry_form_images_model->get_image_by_id($id);
        if ($getImage) {
            $check = $this->Lead_inquiry_form_images_model->delete_image($id);
            if ($check) {
                $file_path = 'uploads/lead_inquiry_form_images/' . $getImage['value'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                set_alert('success', "Image successfully deleted");
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                set_alert('success', "Image not deleted");
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            set_alert('success', "Image not deleted");
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function add_image()
    {
        $post_data = $this->input->post();
        if ($post_data) {
            if ($post_data['id'] == "") {
                $is_active = '0';
                if ($post_data['type'] == "background-image-slider") {
                    $is_active = '1';
                }
                $insertArr = [
                    "title" => $post_data['title'],
                    "type" => $post_data['type'],
                    "is_active" => $is_active,
                    "value" => "",
                ];
                $id = $this->Lead_inquiry_form_images_model->add_image($insertArr);
                if ($id) {
                    if (isset($_FILES['image']) && $_FILES['image']['size']) {
                        $upload_path = 'uploads/lead_inquiry_form_images/';
                        $new_filename =  unique_filename($upload_path, $id . '_' . $_FILES['image']['name']);
                        _maybe_create_upload_path($upload_path);
                        $new_path = $upload_path . '/' . $new_filename;
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $new_path)) {
                            $this->Lead_inquiry_form_images_model->update_image(['value' => $new_filename], $id, false);
                        }
                    }
                    set_alert('success', "Image successfully add");
                    redirect($_SERVER['HTTP_REFERER']);
                } else {
                    set_alert('success', "Image not added");
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } else {
                $getImage = $this->Lead_inquiry_form_images_model->get_image_by_id($post_data['id']);
                if ($getImage) {
                    if (isset($_FILES['image']) && $_FILES['image']['size']) {
                        $upload_path = 'uploads/lead_inquiry_form_images/';
                        $new_filename =  unique_filename($upload_path, $post_data['id'] . '_' . $_FILES['image']['name']);
                        _maybe_create_upload_path($upload_path);
                        $new_path = $upload_path . '/' . $new_filename;
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $new_path)) {
                            $post_data['value'] = $new_filename;
                            //remove old file
                            if (file_exists($upload_path . $getImage['value'])) {
                                unlink($upload_path . $getImage['value']);
                            }
                        }
                    } else {
                        $post_data['value'] = $getImage['value'];
                    }
                    $updateArr = [
                        "title" => $post_data['title'],
                        "type" => $post_data['type'],
                        "value" => $post_data['value'],
                    ];
                    $checkUpdate = $this->Lead_inquiry_form_images_model->update_image($updateArr, $post_data['id'], true);
                    if ($checkUpdate) {
                        set_alert('success', "Image successfully updated");
                        redirect($_SERVER['HTTP_REFERER']);
                    } else {
                        set_alert('success', "Image not updated");
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                } else {
                    set_alert('success', "Image not updated");
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
        } else {
            $this->load->view('forms/lead_customer_inquiry_form', []);
        }
    }
}
