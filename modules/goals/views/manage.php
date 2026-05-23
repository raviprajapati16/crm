<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php if (has_permission('goals', '', 'create')) { ?>
                            <div class="_buttons">
                                <a href="<?php echo admin_url('goals/goal'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('new_goal'); ?></a>
                            </div>
                            <div class="clearfix"></div>
                            <hr class="hr-panel-heading" />
                        <?php } ?>

                        <?php
                        if (has_permission('goals', '', 'edit')) {
                            render_datatable(array(
                                _l('goal_subject'),
                                _l('goal_achievement'),
                                "Goal Duration Type",
                                _l('goal_type'),
                                "Overall Progress",
                                "Action",
                            ), 'goals');
                        } else {
                            render_datatable(array(
                                _l('goal_subject'),
                                _l('goal_achievement'),
                                "Goal Duration Type",
                                _l('goal_type'),
                                "Overall Progress",
                            ), 'goals');
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        var table = initDataTable('.table-goals', window.location.href, [0], []);
        $('.table-goals').DataTable().on('draw', function() {
            var rows = $('.table-goals').find('tr');
            $.each(rows, function() {
                var td = $(this).find('td').eq(4);
                var percent = $(td).find('input[name="percent"]').val();
                $(td).find('.goal-progress').circleProgress({
                    value: percent,
                    size: 45,
                    animation: false,
                    fill: {
                        gradient: ["#28b8da", "#059DC1"]
                    }
                })
            })
        })

        $(document).on('change', '.onoffswitch', function() {
            var checkbox = $(this).find('input[type="checkbox"]');
            var status = "";
            if (checkbox.prop('checked')) {
                status = "1";
            } else {
                status = "0";
            }
            $.ajax({
                url: checkbox.attr("data-switch-url"),
                method: "POST",
                data: {
                    status: status,
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                    alert_float('success', result.message);
                } else {
                    alert_float('danger', result.message);
                }
                if (table) {
                    table.draw();
                }
            });
        });
    });
</script>
</body>

</html>