<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/**
 * Included in application/views/admin/settings/includes/tatatel_js.php
 */
?>

<script>
    function CallPhoneNumber(phoneNumber) {
        var modal = document.getElementById('callPermissionModal');
        var display = document.getElementById('phoneNumberDisplay');
        var adminurl = '<?php echo admin_url() ?>';
        // var closeButton = document.getElementById('closeButton');

        display.textContent = "Calling Phone Number: " + phoneNumber;
        modal.style.display = "block";

        let requesturl = 'Tatatel/CallPhoneNumber?phoneNumber=' + phoneNumber;

        fetch(requesturl)
            .then(response => response.json())
            .then(data => {
                if (data.status === 200 || data.success) {
                    //closecallModal();
                    alert_float('info', data.resultext);
                } else {
                    alert_float('danger', data.resultext);
                }
            })
            .catch(error => {
            console.error('Error:', error);
        });
    }

    function closeModal() {
        var modal = document.getElementById('callPermissionModal');
        modal.style.display = 'none';
    }
</script>