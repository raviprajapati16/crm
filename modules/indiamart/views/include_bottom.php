<?php init_tail(); ?>
<script type="text/javascript">
    var FETCH_URL = "<?php echo $fetch_url; ?>";
    var IMPORT_URL = "<?php echo $import_url; ?>";

	$("#indiamart_fetch_leads").on('submit',function(e){
        $("#response,#instructions").addClass('hide');
        $("#error").html('');
        $("#submit_btn").button('loading');
        e.preventDefault();
        $.ajax({
            url: FETCH_URL,
            type: 'POST',
            data:new FormData(this),
            dataType: 'json',
            processData:false,
            contentType:false,
            success:function(resJSON){
                $("#submit_btn").button('reset');
                if(resJSON.status)
                {
                    $("#head_title").html(resJSON.head_title);
                    $('#leads_table').DataTable().destroy();

                    $("#leads_table tbody").html(resJSON.table_rows);
                    initDataTableInline('#leads_table');
                    $("#response").removeClass('hide');
                }
                else
                {
                    $("#error").html(resJSON.message);
                }
            }
        });
    });

    $("#select_all").on('click',function(e){
        var is_checked = $(this).prop('checked');
        $("#leads_table .import_id").prop('checked', is_checked);
    });

    $("#indiamart_import_leads").on('submit',function(e){
        e.preventDefault();
        var import_leads_count = $("#leads_table .import_id").filter(':checked').length;
        var has_error = false;
        if(!$(this).valid())
        {
            $("#import_btn").button('reset');
            has_error = true;
            return false;
        }
        if(import_leads_count <= 0)
        {
            has_error = true;
            alert_float('danger','Please select leads to import!',5000);
            resetButton('#import_btn');
        }
        if(!has_error)
        {
            $.ajax({
                url: IMPORT_URL,
                type: 'POST',
                data:new FormData(this),
                dataType: 'json',
                processData:false,
                contentType:false,
                success:function(resJSON){
                    resetButton('#import_btn');
                    if(resJSON.status)
                    {
                        if(resJSON.imported_leads.length > 0)
                        {
                            $.each(resJSON.imported_leads, function(i, lead_id) {
                                var tr_selector = "body #lead_"+lead_id;
                                $('#leads_table').DataTable().row($(tr_selector)).remove().draw();
                            });
                        }
                    }
                    else
                    {

                    }
                    alert_float(resJSON.message_type,resJSON.message);
                }
            });
        }
    });

    var validationObject = {
       source: 'required',
       status: 'required',
    };
    appValidateForm($('#indiamart_import_leads'), validationObject);
    initDataTableInline('#leads_table');
    function resetButton(selector)
    {
        setTimeout(function(){
            $(selector).button('reset');
        },500);
    }
</script>