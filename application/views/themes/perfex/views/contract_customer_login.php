<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
   body {
      user-select: none;
      -moz-user-select: none;
      -webkit-user-select: none;
      -ms-user-select: none;
      background: linear-gradient(90deg, rgba(13, 87, 46, 1) 35%, rgba(253, 201, 0, 1) 100%);
   }

   .logo {
      width: 90%;
      margin: auto;
      margin-bottom: 10px;
      padding: 20px;
      border-radius: 20px;
   }

   .navbar-fixed-bottom {
      background: linear-gradient(90deg, rgba(13, 87, 46, 1) 35%, rgba(253, 201, 0, 1) 100%);
      color: #fff;
      font-weight: 500;
      border-top: 1px solid #000;
   }

   @media (max-width: 767px) {
      .logo {
         width: 90%;
         padding: 10px;
      }
   }
</style>
<div class="container-fluid vh-100 d-flex mtop20 justify-content-center">
   <div>
      <div class="row">
         <div class="col-md-12 text-center">
            <div class="mbot30">
               <div class="contract-html-logo">
                  <?php echo get_white_company_logo(); ?>
               </div>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s mtop20">
               <div class="panel-body tc-content contract-html-content">
                  <div class="row">
                     <div class="col-md-12">
                        <h2 class="text-center">Customer Authentication</h2>
                     </div>
                     <div class="col-md-12">
                        <form action="#" method="POST" id="authForm">
                           <div class="form-group">
                              <label for="email">Email:</label>
                              <input type="email" id="email" name="email" class="form-control" required>
                           </div>
                           <button type="button" id="sendOtpBtn" class="btn btn-primary">Send OTP</button>
                           <div id="otpSection" class="hide" style="margin-top: 20px;">
                              <div class="form-group">
                                 <label for="otp">Enter OTP:</label>
                                 <div id="otpInputs" class="d-flex justify-content-between">
                                    <input type="text" class="otp-input form-control" maxlength="1" required>
                                    <input type="text" class="otp-input form-control" maxlength="1" required>
                                    <input type="text" class="otp-input form-control" maxlength="1" required>
                                    <input type="text" class="otp-input form-control" maxlength="1" required>
                                    <input type="text" class="otp-input form-control" maxlength="1" required>
                                    <input type="text" class="otp-input form-control" maxlength="1" required>
                                 </div>
                              </div>
                              <button type="button" id="verifyOtpBtn" class="btn btn-success">Verify OTP</button>
                              <button type="button" id="resendOtpBtn" class="btn btn-secondary" disabled>Resend OTP (<span id="resendTimer">60</span>s)</button>
                           </div>
                        </form>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="row">
</div>
<script>
   $(function() {
      var contractId = "<?= (isset($contract)) ? $contract->id : '' ?>";

      let resendTimer;
      let countdown = 60;
      $('#sendOtpBtn').on('click', function() {
         otpSend("send")
      });

      $('#email').on('input', function() {
         $('#email').siblings(".text-danger").remove();
      });

      $('#resendOtpBtn').on('click', function() {
         otpSend(sendType = "resend")
      });

      $('.otp-input').on('input', function() {
         $(this).closest('.form-group').find('.text-message').remove()
         var $this = $(this);
         if ($this.val().length === 1) {
            $this.next('.otp-input').focus();
         }
      });

      $('.otp-input').on('keydown', function(e) {
         var $this = $(this);
         if (e.key === "Backspace" && $this.val().length === 0) {
            $this.prev('.otp-input').focus();
         }
      });

      function otpSend(sendType = "send") {
         var email = $('#email').val();
         if (email) {
            if (sendType == "send") {
               $('#sendOtpBtn').prop('disabled', true).html('Sending... <i class="fa fa-spinner fa-spin"></i>');
               $('#email').parent('div').find('.text-danger').remove();
            } else { //resend
               $('#resendOtpBtn').prop('disabled', true).html('Sending... <i class="fa fa-spinner fa-spin"></i>');
               $('#otpInputs').closest('.form-group').find('.text-message').remove();
            }
            $('#email').prop('readonly', true);
            $.ajax({
               url: "<?= site_url('contract/otp_verification') ?>",
               method: 'POST',
               dataType: 'JSON',
               data: {
                  email: email,
                  contract_id: contractId,
                  type: 'send-otp',
                  mode: 'contract_authenticate'
               },
               success: function(response) {
                  if (sendType == "send") {
                     if (response.success) {
                        $('#otpVerify').prop('disabled', false).html('Verify');
                        $('#otpSection').find('.text-danger').remove();
                        $('#otpSection').find('.otp-input').val('');
                        $('#sendOtpBtn').addClass('hide');
                        $('#otpSection').removeClass('hide');
                        $('#email').prop('readonly', true);
                        alert_float('success', response.message)
                        startResendTimer();
                     } else {
                        $('#email').prop('readonly', false);
                        $('#email').after('<p id="email-error" class="text-danger">' + response.message + '</p>');
                     }
                  } else { //resend
                     if (response.success) {
                        alert_float('success', 'OTP re-send successfully.')
                        startResendTimer();
                     } else {
                        $('#email').prop('readonly', false);
                        $('#email').after('<p id="email-error" class="text-danger">' + response.message + '</p>');
                     }
                  }
               },
               complete: function() {
                  if (sendType == "send") {
                     $('#sendOtpBtn').prop('disabled', false).html('Send OTP');
                  }
               }
            });
         } else {
            if (sendType == "send") {
               $('#email').siblings('.text-danger').remove();
               $('#email').after('<p id="email-error" class="text-danger">Please eneter email.</p>');
            }

         }
      }

      function startResendTimer() {
         $('#resendOtpBtn').prop('disabled', true);
         $('#resendTimer').text(countdown);
         $('#resendOtpBtn').prop('disabled', true).html('Resend OTP (<span id="resendTimer">' + countdown + '</span>)');
         resendTimer = setInterval(function() {
            countdown--;
            $('#resendTimer').text(countdown);
            if (countdown <= 0) {
               clearInterval(resendTimer);
               $('#resendOtpBtn').html('Resend OTP').prop('disabled', false);
               countdown = 60;
            }
         }, 1000);
      }

      $('#verifyOtpBtn').on('click', function() {
         var form_gorup = $('#otpInputs').closest('.form-group');
         form_gorup.find('.text-message').remove();
         var email = $('#email').val();
         var otp = $('.otp-input').map(function() {
            return $(this).val();
         }).get().join('');

         if (otp.length !== 6 || !/^\d+$/.test(otp)) {
            form_gorup.append('<p class="text-danger text-message text-center mtop10">Please enter a valid 6-digit OTP.</p>');
            return;
         }
         $('.otp-input').prop('disabled', true);
         $('#verifyOtpBtn').prop('disabled', true).html('Verifying... <i class="fa fa-spinner fa-spin"></i>');
         $.ajax({
            url: "<?= site_url('contract/otp_verification') ?>",
            method: 'POST',
            dataType: 'JSON',
            data: {
               email: email,
               contract_id: contractId,
               type: 'verify-otp',
               otp: otp,
               mode: 'contract_authenticate'
            },
            success: function(response) {
               if (response.success) {
                  $('#otpSection').addClass('hide');
                  $('#verifyOtpBtn').prop('disabled', true).html('Verify').addClass('hide');
                  $('#resendOtpBtn').prop('disabled', true).html('Verify').addClass('hide');
                  $('#authForm').append('<p class="text-success text-center text-message mtop10">Email succcesfully Verified <i class="fa fa-check-circle" aria-hidden="true"></i> <br> Redirecting... Please Wait <i class="fa fa-spinner fa-spin"></i></p>');
                  setTimeout(() => {
                     location.reload();
                  }, 1500);
               } else {
                  form_gorup.append('<p class="text-danger text-message text-center mtop10">' + response.message + '</p>');
                  $('.otp-input').prop('disabled', false);
                  $('#verifyOtpBtn').prop('disabled', false).html('Verify');
               }
            },
            error: function() {
               form_gorup.append('<p class="text-danger text-message text-center mtop10">Something went wrong. please try again.</p>');
               $('.otp-input').prop('disabled', false);
               $('#verifyOtpBtn').prop('disabled', false).html('Verify');
            },
         });


      });

   });
</script>
<style>
   .contract-html-logo {
      text-align: center;
      max-width: 450px;
      /* Adjust the max width as needed */
      margin: 0 auto;
      /* Center the logo horizontally */
   }

   #otpInputs input {
      width: 45px;
      text-align: center;
   }

   .vh-100 {
      height: 100vh;
   }

   .d-flex {
      display: flex;
   }

   #otpInputs {
      justify-content: space-evenly;
   }


   .justify-content-center {
      justify-content: center;
   }
</style>