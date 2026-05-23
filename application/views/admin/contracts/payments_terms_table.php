<?php defined('BASEPATH') or exit('No direct script access allowed');
?>
<style>
    .payment-table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
<div class="payment-table-header">
    <h4 class="no-mbot">Payment Terms</h4>
    <?php
    if ($pending_amount != 0 && has_permission('contracts', '', 'create')) {
    ?>
        <button type="button" class="btn btn-primary btn-payment-term-create">Create</button>
    <?php
    }
    ?>
</div>

<?php
if (count($payments_terms) === 0) {
    echo '<h4 class="text-center">Payments Not Available</h4>';
} else {
?>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th><b>ID</th>
                    <th><b>Percentage Of <br> Agreement Value</b></th>
                    <th><b>Amount</b></th>
                    <th><b>Payment <br> Due Date</b></th>
                    <th><b>Payment <br> Status</b></th>
                    <th><b>Received Amount</b></th>
                    <th><b>Action</b></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_received = get_contract_payment_data($contract_id)['total_received_amount'];
                foreach ($payments_terms as $item) { ?>
                    <tr>
                        <td>
                            <?php echo $item['id']; ?>
                        </td>
                        <td>
                            <?php echo $item['percentage'] . "%"; ?>
                        </td>
                        <td>
                            <?= app_format_money($item['amount'], get_contract_currency($item['contract_id']), false) . '/-'; ?>
                        </td>
                        <td>
                            <?php echo _d($item['scheduled_payment_date'], false); ?>
                        </td>
                        <td>
                            <?php if ($item['status'] == "Received") {
                                echo '<span class="label label-success">Received</span>';
                            } else if ($item['status'] == "Partially Received") {
                                echo '<span class="label label-warning">Partially Received</span>';
                            } else {
                                echo '<span class="label label-danger">Pending</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $received_for_term = get_contract_term_payment_data($item['id'])['received_for_term'];
                            echo app_format_money($received_for_term, get_contract_currency($item['contract_id']), false) . '/-';
                            ?>
                        </td>
                        <td>
                            <?php if ($item['status'] == "Pending") { ?>
                                <?php if (has_permission('contracts', '', 'edit')) { ?>
                                    <button type="button" data-id="<?= $item['id']; ?>" class="btn btn-xs btn-edit-payment-term btn-primary"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
                                <?php } ?>

                                <?php if (has_permission('contracts', '', 'delete')) { ?>
                                    <a href="<?= admin_url('contracts/delete_payment_term/' . $item['contract_id'] . '/' . $item['id']) ?>" class="btn btn-xs btn-danger"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                    <?php } ?>
                                <?php } else { ?>
                                    <button type="button" data-id="<?= $item['id']; ?>" class="btn btn-xs btn-edit-payment-term btn-success"><i class="fa fa-eye" aria-hidden="true"></i></button>
                                <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
<?php } ?>