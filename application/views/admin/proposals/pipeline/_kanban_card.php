<style>
    .pipeline-card {
        background-color: transparent;
        padding: 6px 0;
        margin-bottom: 6px;
        font-size: 13px;
    }

    .pipeline-card a {
        color: #007bff;
        font-size: 13px;
    }

    .pipeline-customer {
        /* margin-bottom: 4px; */
        display: inline-block;
    }

    .pipeline-subject,
    .pipeline-total {
        font-size: 13px;
        color: #333;
    }

    .pipeline-card {
        background-color: #f7f9fc;
        /* light grey or use white: #ffffff */
        border-radius: 4px;
        padding: 10px;
        margin-bottom: 10px;
        font-size: 13px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        /* optional for card look */
    }
</style>


<div class="pipeline-card">
    <div class="row">
        <div class="col-md-12">
            <div class="pipeline-heading">
                <a href="<?php echo admin_url('proposals/list_proposals/' . $proposal['id']); ?>" data-toggle="tooltip"
                    data-title="<?php echo $proposal['subject']; ?>"
                    onclick="proposal_pipeline_open(<?php echo $proposal['id']; ?>); return false;">
                    <?php echo format_proposal_number($proposal['id']); ?>
                </a>
              <?php if (
    has_permission('proposals', '', 'edit') 
    && ($proposal['status'] == 6 || $proposal['status'] == 4)
) { ?>
    <a href="<?php echo admin_url('proposals/proposal/' . $proposal['id']); ?>" 
       target="_blank" 
       class="pull-right">
        <small><i class="fa fa-pencil-square-o" aria-hidden="true"></i></small>
    </a>
<?php } ?>

            </div>

            <div class="pipeline-customer">
                <a href="<?php echo admin_url('clients/client/' . $proposal['rel_id']); ?>" data-toggle="tooltip"
                    data-title="Client">
                    <?php echo $proposal['proposal_to']; ?>
                </a>
            </div>

            <div class="pipeline-subject">
                <?php echo _l('Subject') . ': ' . $proposal['subject']; ?>
            </div>

            <div class="pipeline-total">
                <?php echo _l('Total') . ': ' . app_format_money($proposal['total'], get_currency($proposal['currency'])); ?>
            </div>
        </div>
    </div>
</div>