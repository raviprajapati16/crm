<?php
defined('BASEPATH') or exit('No direct script access allowed');
include_once(__DIR__ . '/App_mpdf.php');
set_time_limit(0);
class Contract_pdf
{
    public $contract;
    protected $ci;

    public function __construct($contract)
    {
        $this->contract = $contract;
        $this->ci = &get_instance();
    }

    public function prepare()
    {
        set_time_limit(0);
        $temp_paths = [];
        $html_content = $this->renderHTML();
        $output_type = $this->ci->input->get('output_type');
        $output_mode = ($output_type && $output_type == 'I') ? 'I' : 'D';

        $contractpdf = new App_mpdf();
        $contractpdf->setType('agreement');
        $contractpdf->setHTMLContent($html_content);
        $contractpdf->setTitle($this->contract->subject);
        $contractpdf->setOutputMode($output_mode);
        $contractpdf->setExtraFooter($this->extraFooterContent());
        $mpdf_html = $contractpdf->generatePDF();

        $html_temp_path = 'uploads/temp_mail_attachments/html_part_' . time() . '_' . uniqid() . '.pdf';
        $this->createTempDirectory();
        $mpdf_html->Output($html_temp_path, \Mpdf\Output\Destination::FILE);

        //Other content pdf
        if (!empty($this->contract->other_content)) {
            $otherContentPDF = new \Mpdf\Mpdf();
            $otherContentPDF->WriteHTML($this->contract->other_content);
            $otherContentPDF->SetTitle($this->contract->subject);
            $other_temp_path = FCPATH . 'uploads/temp_mail_attachments/contract_other_' . time() . '_' . uniqid() . '.pdf';
            $otherContentPDF->Output($other_temp_path, 'F');
            if (empty($this->contract->content)) {
                $mpdf_html = $otherContentPDF;
                $html_temp_path = $other_temp_path;
            } else {
                $temp_paths[] = $other_temp_path;
            }
        }

        $proposal_ids = get_contract_linked_proposals($this->contract->id);
        if (!empty($proposal_ids)) {
            foreach ($proposal_ids as $proposal_id) {
                $proposal_temp_path = FCPATH . 'uploads/temp_mail_attachments/proposal_' . time() . '_' . uniqid() . '.pdf';
                $proposal = $this->ci->proposals_model->get($proposal_id);
                $proposal_pdf = proposal_mpdf($proposal);
                $proposal_pdf->Output($proposal_temp_path, 'F');
                $temp_paths[] = $proposal_temp_path;
            }
        }


        $mergedPdfPath = $this->mergeFiles($html_temp_path, ...$temp_paths);
        if ($mergedPdfPath && file_exists($mergedPdfPath)) {
            return $this->generateFinalPDF($mergedPdfPath);
        } else {
            return $mpdf_html;
        }
    }

    protected function renderHTML()
    {
        $html_content = $this->ci->load->view('themes/' . active_clients_theme() . '/mpdf/contracts/contractpdf', ['contract' => $this->contract], true);

        //dynamic proposal item table
        if (!empty($this->contract->proposal_id)) {
            $proposal = $this->ci->proposals_model->get($this->contract->proposal_id);
            $contract_items = $this->ci->load->view(
                'themes/' . active_clients_theme() . '/mpdf/contracts/contract-items-table',
                ['proposal' => $proposal],
                true
            );
            $html_content = str_replace('{contract_items_table}', $contract_items, $html_content);
        }
        $html_content = str_replace('{page_break}', '<pagebreak />', $html_content);

        // dynamic partners sign section
        $sign_html = $this->ci->load->view(
            'themes/' . active_clients_theme() . '/mpdf/contracts/contract-partners-sign-section',
            ['contract' => $this->contract],
            true
        );
        $html_content = str_replace('{contract_customer_sign_section}', $sign_html, $html_content);

        // dynamic company sign section
        $sign_html = $this->ci->load->view(
            'themes/' . active_clients_theme() . '/mpdf/contracts/company-sign-stamp-section',
            ['contract' => $this->contract],
            true
        );
        $html_content = str_replace('{company_sign_stamp_section}', $sign_html, $html_content);



        return $html_content ?: "<h1>File Not Found</h1>";
    }

    private function mergeFiles($html_temp_path, ...$temp_paths)
    {
        try {
            $mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . '/tmp']);

            // Merge the contract PDF
            $htmlPageCount = $mpdf->setSourceFile($html_temp_path);
            for ($i = 1; $i <= $htmlPageCount; $i++) {
                $tplId = $mpdf->importPage($i);
                $mpdf->AddPage();
                $mpdf->useTemplate($tplId);
            }

            // Merge each PDFs
            foreach ($temp_paths as $temp_path) {
                if (!empty($temp_path) && file_exists($temp_path)) {
                    $proposalPageCount = $mpdf->setSourceFile($temp_path);
                    for ($i = 1; $i <= $proposalPageCount; $i++) {
                        $tplId = $mpdf->importPage($i);
                        $mpdf->AddPage();
                        if (!strpos($temp_path, 'contract_other') !== false) {
                            $mpdf->useTemplate($tplId, 5, 5, 200, 270);
                            $mpdf->SetHTMLFooter($this->extraFooterContent());
                        } else {
                            $mpdf->useTemplate($tplId, -5, 0, 220, 270);
                        }
                    }
                }
            }

            // Merge any additional files associated with the contract
            $files = $this->ci->misc_model->get_files($this->contract->id, 'contract');
            if (!empty($files)) {
                foreach ($files as $file) {
                    $fileExtension = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
                    $filepath = get_upload_path_by_type('contract') . $this->contract->id . '/' . $file->file_name;

                    if (file_exists($filepath)) {
                        if (in_array($fileExtension, ['jpg', 'jpeg', 'png'])) {
                            $this->addImageToPdf($mpdf, $filepath);
                        } elseif ($fileExtension === 'pdf') {
                            $attachmentPageCount = $mpdf->setSourceFile($filepath);
                            for ($i = 1; $i <= $attachmentPageCount; $i++) {
                                $tplId = $mpdf->importPage($i);
                                $mpdf->AddPage();
                                $mpdf->useTemplate($tplId, 5, 5, 200, 270); // x, y, width, height
                                $mpdf->SetHTMLFooter($this->extraFooterContent());
                            }
                        }
                    }
                }
            }

            // Output the merged PDF to a temporary file
            $mergedPdfPath = 'uploads/temp_mail_attachments/merged_files_' . time() . '_' . uniqid() . '.pdf';
            $this->createTempDirectory();
            $mpdf->Output($mergedPdfPath, \Mpdf\Output\Destination::FILE);

            if (file_exists($mergedPdfPath)) {
                return $mergedPdfPath;
            } else {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }
    }


    private function addImageToPdf($pdf, $imagePath)
    {
        list($width, $height) = getimagesize($imagePath);
        $aspectRatio = $height / $width;
        $defaultWidth = 210;
        $newHeight = $defaultWidth * $aspectRatio;
        $pageWidth = 210;
        $pageHeight = 297;

        if ($newHeight > $pageHeight) {
            $newHeight = $pageHeight;
            $defaultWidth = $newHeight / $aspectRatio;
        }

        $x = ($pageWidth - $defaultWidth) / 2;
        $y = ($pageHeight - $newHeight) / 2;

        $pdf->AddPage();
        $pdf->Image($imagePath, $x, $y, $defaultWidth, $newHeight, '', '', true, false);
        $pdf->SetHTMLFooter($this->extraFooterContent());
    }

    private function createTempDirectory()
    {
        $tempDir = 'uploads/temp_mail_attachments';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
    }

    private function generateFinalPDF($mergedPdfPath)
    {
        try {
            $mpdf = new \Mpdf\Mpdf();
            $pageCount = $mpdf->setSourceFile($mergedPdfPath);
            for ($i = 1; $i <= $pageCount; $i++) {
                $tplId = $mpdf->importPage($i);
                $mpdf->AddPage();
                $mpdf->useTemplate($tplId);
            }
            unlink($mergedPdfPath);
            $mpdf->SetTitle($this->contract->subject);
            return $mpdf;
        } catch (Exception $e) {
            return null;
        }
    }

    private function extraFooterContent()
    {

        $html_content = $this->ci->load->view('themes/' . active_clients_theme() . '/mpdf/contracts/all-page-sign', ['contract' => $this->contract], true);
        return $html_content;
    }
}
