<?php

defined('BASEPATH') or exit('No direct script access allowed');
// create some HTML content

$quotation_date = (!empty($form_data['quotation_date'])) ? date('d-m-Y', strtotime($form_data['quotation_date'])) : "";

$html = '<div>
<div style="text-align: center;"><img style="width:250px" src="' . get_upload_path_by_type('company') . get_option('company_logo') . '" /></div>
<div style="font-size: 29px; font-weight: bold; text-align: center;">' . get_option('invoice_company_name') . '</div>
<div style="font-size: 15px; font-weight: 500; text-align: center;">' . get_option('invoice_company_address') . get_option('invoice_company_city') . ', ' . get_option('invoice_company_postal_code') . '</div>
</div>';
$pdf->writeHTML($html, true, false, true, false, '');

$html = '<div style="border: 2px solid #000;">
<div style="text-align: center; font-size: 22px; font-weight: bold;">Quotation</div></div>';
$pdf->writeHTML($html, true, false, true, false, '');


$html = "<br><br><table>
<tr>
    <td><strong>Supplier Name</strong></td>
    <td style='text-align: left;'>".$form_data['supplier_name']."</td>
    <td><strong>GST IN</strong></td>
    <td style='text-align: left;'>".$form_data['gst_in']."</td>

</tr><br>
<tr>
    <td><strong>Address</strong></td>
    <td>".$form_data['address']."</td>
    <td><strong>Date</strong></td>
    <td style='text-align: left;'>".$quotation_date."</td>
</tr>
<table>
";

$pdf->writeHTML($html, true, false, true, false, '');

$html = '<div>
<table border="0.1" cellspacing="0" cellpadding="5" style="border-collapse:collapse;">
    <thead>
        <tr>
            <th><strong>Sr. No.</strong></th>
            <th><strong>Description Of Service</strong></th>
            <th><strong>HSN / SAC</strong></th>
            <th><strong>Quantity</strong></th>
            <th><strong>Unit</strong></th>
            <th><strong>Price INR</strong></th>
            <th><strong>Amount INR</strong></th>
        </tr>
    </thead>
    <tbody>';
if (!empty($item_data)) {
    foreach ($item_data as $key => $item) {
        $html .= '<tr>
            <td>' . ($key + 1) . '</td>
            <td>' . $item['service_description'] . '</td>
            <td>' . $item['hsn_sac'] . '</td>
            <td>' . $item['qty'] . '</td>
            <td>' . $item['unit'] . '</td>
            <td>' . $item['price_in_inr'] . '</td>
            <td>' . $item['amount_in_inr'] . '</td>
            </tr>';
    }
}

$html .= '</tbody>
</table></div>';
$pdf->writeHTML($html, true, false, true, false, '');

if (!empty($form_data['terms_conditions'])) {
    $pdf->setMargins(10, 0, 10, 0);
    $html = '<div style="text-align: left; font-size: 18px; font-weight: bold;">Terms & Conditions : </div>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Cell(0, 0, '', 'T', 10, 'C');
    $pdf->setMargins(10, 0, 10, 0);
    $pdf->writeHTML($form_data['terms_conditions'], true, false, true, false, '');
}

if (!empty($form_data['notes'])) {
    $html = '<br><br><div style="text-align: left; font-size: 18px; font-weight: bold;">Notes : </div>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Cell(0, 0, '', 'T', 10, 'C');
    $pdf->setMargins(10, 0, 10, 0);
    $pdf->writeHTML($form_data['notes'], true, false, true, false, '');
}

$sigantureText = "";
$staffData = get_staff($form_data['created_by']);
if (!empty($staffData)) {
    if (!empty($staffData->email_signature)) {
        $pdf->setMargins(10, 0, 10, 0);
        $pdf->writeHTML("<br><br><br>".$staffData->email_signature, true, false, true, false, '');
    }
}
