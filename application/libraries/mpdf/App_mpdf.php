<?php
defined('BASEPATH') or exit('No direct script access allowed');
include_once(__DIR__ . './../mpdf_lib/autoload.php');

use Mpdf\Mpdf;

class App_mpdf
{
    protected $mpdf;
    protected $html_content;
    protected $title;
    protected $outputMode;
    protected $type;
    protected $extraFooter;

    // public function __construct($format = 'A4', $outputMode = 'I')
    // {
    //     $this->mpdf = new Mpdf([
    //         'format' => $format,
    //         'margin_left' => 15,
    //         'margin_right' => 15,
    //         'margin_top' => 7,
    //         'margin_bottom' => 7,
    //         'margin_header' => 7,
    //         'margin_footer' => 7,
    //         'setAutoBottomMargin' => 'stretch',
    //         'setAutoTopMargin' => 'pad',
    //     ]);
    //     $this->outputMode = $outputMode;
    // }
    
public function __construct($format = 'A4', $outputMode = 'I')
{
    $this->mpdf = new \Mpdf\Mpdf([
        'format' => $format, // 'A4' or 'Legal' or custom size [width_mm, height_mm]
        'margin_left' => 20,
        'margin_right' => 20,
        'margin_top' => 7,
        'margin_bottom' => 7,
        'margin_header' => 7,
        'margin_footer' => 7,
        'setAutoBottomMargin' => 'stretch',
        'setAutoTopMargin' => 'pad',
    ]);

    $this->pageFormat = $format;
    $this->outputMode = $outputMode;
}


    public function setHeader()
    {
        $pdf_settings = get_pdf_settings($this->type);
        if (!empty($pdf_settings)) {
            if ($pdf_settings->header_repeat == "1") {
                $this->mpdf->SetHTMLHeader($pdf_settings->header);
            } else {
                $firstPageHeader = $pdf_settings->header;
                $this->mpdf->SetHTMLHeader($firstPageHeader);
                $this->mpdf->AddPage();
                $this->mpdf->SetHTMLHeader('');
            }
        }
    }

    public function setFooter()
    {
        $pdf_settings = get_pdf_settings($this->type);
        if (!empty($pdf_settings)) {
            $footer = '
                <div style="border-top: 1px solid #000; padding-top: 5px; text-align: center; font-size: 12px;">
                    <table width="100%">
                        <tr>
                            <td style="text-align: left;">' . $pdf_settings->footer . '</td>
                            <td style="text-align: right;">Page {PAGENO} of {nbpg}</td>
                        </tr>
                    </table>
                </div>';
            if ($this->extraFooter) {
                $footer = $this->extraFooter . $footer;
            }
            $this->mpdf->SetHTMLFooter($footer);
        }
    }

    public function setExtraFooter($extraFooter)
    {
        $this->extraFooter = $extraFooter;
    }

    public function setWatermark()
    {
        $pdf_settings = get_pdf_settings($this->type);
        if (!empty($pdf_settings)) {
            if ($pdf_settings->watermark_type == "text") {
                $this->mpdf->SetWatermarkText($pdf_settings->watermark);
                $this->mpdf->showWatermarkText = true;
                $this->mpdf->watermark_font = 'Arial';
                $this->mpdf->watermarkTextAlpha = 0.2;
            }
            if ($pdf_settings->watermark_type == "image") {
                $watermarkImagePath = FCPATH . 'uploads/pdf_settings/' . $pdf_settings->id . '/' . $pdf_settings->watermark;
                if (file_exists($watermarkImagePath)) {
                    // $this->mpdf->SetWatermarkImage($watermarkImagePath, 0.2, 20);
                     $this->mpdf->SetWatermarkImage($watermarkImagePath, 0.2, [100, 60], 'F');
                    $this->mpdf->showWatermarkImage = true;
                    $this->mpdf->watermarkImgAlpha = 0.2;
                    $this->mpdf->watermarkImgBehind = true;
                }
            }
        }
    }

    public function setHTMLContent($html_content)
    {
        $this->html_content = $html_content;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setOutputMode($outputMode = 'I')
    {
        $this->outputMode = $outputMode;
    }

    public function generatePDF()
    {
        try {
            $this->setWatermark();
            $this->setHeader();
            $this->setFooter();
            $this->mpdf->SetTitle($this->title);
            $this->mpdf->WriteHTML($this->html_content);
            return $this->mpdf;
        } catch (\Mpdf\MpdfException $e) {
            echo $e->getMessage();
            die();
        }
    }
}
