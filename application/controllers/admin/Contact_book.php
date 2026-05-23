<?php
class Contact_book extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('contact_book_model');
        $this->load->model('contact_book_category_model');
        $this->load->model('staff_model');
    }


    public function index()
    {
        if (!has_permission('contact_book', '', 'view') && !has_permission('contact_book', '', 'view_own')) {
            access_denied('contact_book');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('contact_book');
        }
        $data['categories'] = $this->contact_book_category_model->get_category();
        $this->load->view('admin/contact_book/manage', $data);
    }

    public function get($id)
    {
        if (!has_permission('contact_book', '', 'view') && !has_permission('contact_book', '', 'view_own')) {
            echo json_encode([
                'success' => false,
                'message' => 'Access denied: You do not have permission to view contacts.'
            ]);
            return;
        }

        if (!$id) {
            echo json_encode([
                'success' => false,
                'message' => 'No ID provided.'
            ]);
            return;
        }

        $contact = $this->contact_book_model->get($id);

        if ($contact && !empty($contact['category'])) {
            $category = $this->contact_book_category_model->get($contact['category']);
            if ($category) {
                $contact['category_name'] = $category['name'];
            }
        }

        if ($contact) {
            $contact['country_id'] = $contact['country'];
            $contact['country'] = get_country_name($contact['country']);
            if (!empty($contact['created_by'])) {
                $this->load->model('staff_model');
                $staff = $this->staff_model->get($contact['created_by']);
                if ($staff) {
                    $contact['contact_owner'] = $staff->full_name;
                } else {
                    $contact['contact_owner'] = '-';
                }
            } else {
                $contact['contact_owner'] = '-';
            }
            echo json_encode([
                'success' => true,
                'data' => $contact
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Contact not found.'
            ]);
        }
    }

    public function save()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $attachments = [];

            // Check if there are attachments data
            if (isset($data['attachments']) && !empty($data['attachments'])) {
                $attachments = json_decode($data['attachments'], true);
                unset($data['attachments']);
            }

            if (empty($data['id'])) {
                if (!has_permission('contact_book', '', 'create')) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Access denied: You do not have permission to create contacts.'
                    ]);
                    return;
                }

                $id = $this->contact_book_model->insert($data);

                if ($id) {
                    // Update any existing attachments with the new contact ID
                    if (!empty($attachments)) {
                        foreach ($attachments as $attachment) {
                            $this->contact_book_model->update_attachment($attachment['id'], ['contact_id' => $id]);
                        }
                    }

                    echo json_encode([
                        'success' => true,
                        'message' => 'Contact created successfully',
                        'id' => $id
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error: Contact not created.'
                    ]);
                }
            } else {
                if (!has_permission('contact_book', '', 'edit')) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Access denied: You do not have permission to edit contacts.'
                    ]);
                    return;
                }

                $id = $data['id'];
                unset($data['id']);
                $check = $this->contact_book_model->update($id, $data);

                echo json_encode([
                    'success' => true,
                    'message' => 'Contact successfully updated',
                    'id' => $id
                ]);
            }
            return;
        }

        echo json_encode([
            'success' => false,
            'message' => 'No POST data received.'
        ]);
    }

    public function delete($id)
    {
        if (!has_permission('contact_book', '', 'delete')) {
            echo json_encode([
                'success' => false,
                'message' => 'Access denied: You do not have permission to delete contacts.'
            ]);
            return;
        }

        if (!$id) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid request: No contact ID provided.'
            ]);
            return;
        }

        // First, delete all attachments related to this contact
        $this->contact_book_model->delete_contact_attachments($id);

        // Then delete the contact
        $success = $this->contact_book_model->delete($id);

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Contact deleted successfully.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error: Contact not deleted.'
            ]);
        }
    }

    public function upload_attachment()
    {
        // Check permissions
        if (!has_permission('contact_book', '', 'create') && !has_permission('contact_book', '', 'edit')) {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode([
                'success' => false,
                'message' => 'Access denied: You do not have permission.'
            ]);
            die();
        }

        $contactId = $this->input->post('contact_id');
        $response = [];

        if (isset($_FILES['file']) && is_array($_FILES['file']['name'])) {
            $fileCount = count($_FILES['file']['name']);

            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['file']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['file']['tmp_name'][$i];
                    $originalName = $_FILES['file']['name'][$i];

                    $path = 'uploads/contact_book/' . $contactId . '/';
                    $filename = unique_filename($path, $originalName);
                    _maybe_create_upload_path($path);

                    $destination = $path . $filename;
                    if (move_uploaded_file($tmpName, $destination)) {
                        $ext = pathinfo($filename, PATHINFO_EXTENSION);

                        $attachment_data = [
                            'file_name' => $filename,
                            'filetype' => $ext,
                            'original_file_name' => $originalName,
                            'contact_id' => $contactId ?: null,
                            'dateadded' => date('Y-m-d H:i:s'),
                            'addedfrom' => get_staff_user_id()
                        ];

                        $attachment_id = $this->contact_book_model->add_attachment($attachment_data);

                        if ($attachment_id) {
                            $response[] = [
                                'success' => true,
                                'attachment_id' => $attachment_id,
                                'file_name' => $originalName
                            ];
                        } else {
                            @unlink($destination);
                            $response[] = [
                                'success' => false,
                                'file_name' => $originalName,
                                'message' => 'Error: Could not save attachment to database.'
                            ];
                        }
                    } else {
                        $response[] = [
                            'success' => false,
                            'file_name' => $originalName,
                            'message' => 'Error: Failed to move uploaded file.'
                        ];
                    }
                } else {
                    $response[] = [
                        'success' => false,
                        'file_name' => $_FILES['file']['name'][$i],
                        'message' => 'Upload error code: ' . $_FILES['file']['error'][$i]
                    ];
                }
            }
        } else {
            $response[] = [
                'success' => false,
                'message' => 'No files uploaded.'
            ];
        }

        echo json_encode($response);
    }


    public function get_attachments($contactId)
    {
        if (!has_permission('contact_book', '', 'view') && !has_permission('contact_book', '', 'view_own')) {
            echo json_encode([
                'success' => false,
                'message' => 'Access denied: You do not have permission to view attachments.'
            ]);
            return;
        }

        if (!$contactId) {
            echo json_encode([
                'success' => false,
                'message' => 'No contact ID provided.'
            ]);
            return;
        }

        $attachments = $this->contact_book_model->get_contact_attachments($contactId);

        echo json_encode([
            'success' => true,
            'attachments' => $attachments
        ]);
    }

    public function delete_attachment($attachmentId)
    {
        if (!has_permission('contact_book', '', 'delete')) {
            echo json_encode([
                'success' => false,
                'message' => 'Access denied: You do not have permission to delete attachments.'
            ]);
            return;
        }

        if (!$attachmentId) {
            echo json_encode([
                'success' => false,
                'message' => 'No attachment ID provided.'
            ]);
            return;
        }

        $attachment = $this->contact_book_model->get_attachment($attachmentId);

        if (!$attachment) {
            echo json_encode([
                'success' => false,
                'message' => 'Attachment not found.'
            ]);
            return;
        }

        $file_path = 'uploads/contact_book/' . $attachment->contact_id . '/' . $attachment->file_name;

        // Delete the file
        if (file_exists($file_path)) {
            @unlink($file_path);
        }

        // Delete from database
        $success = $this->contact_book_model->delete_attachment($attachmentId);

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Attachment deleted successfully.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error: Attachment not deleted from database.'
            ]);
        }
    }

    public function download_attachment($attachmentId)
    {
        if (!has_permission('contact_book', '', 'view') && !has_permission('contact_book', '', 'view_own')) {
            access_denied('contact_book');
        }
        $attachment = $this->contact_book_model->get_attachment($attachmentId);
        if (!$attachment) {
            set_alert('warning', 'Attachment not found.');
            redirect(admin_url('contact_book'));
        }

        $file_path = 'uploads/contact_book/' . $attachment->contact_id . '/' . $attachment->file_name;
        redirect('download/file_download?path=' . $file_path);
    }
}