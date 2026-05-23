<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" tabindex="-1" role="dialog" id="identityConfirmationModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <?php echo form_open_multipart((isset($formAction) ? $formAction : $this->uri->uri_string()), array('id' => 'identityConfirmationForm', 'class' => 'form-horizontal')); ?>
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo _l('signature'); ?> &amp; <?php echo _l('confirmation_of_identity'); ?></h4>
      </div>
      <div class="modal-body">
        <?php hooks()->do_action('before_confirmation_identity_fields'); ?>
        <?php if (isset($formData)) {
          echo $formData;
        }; ?>
        <?php
        $emailreadonly = "";
        $firstname = "";
        $lastname = "";
        $email = "";
        if (strpos($this->uri->uri_string(), "contract") !== false) {
          if (isset($loggedin_contact) && !empty($loggedin_contact)) {
            $firstname = (isset($loggedin_contact['firstname']) && !empty($loggedin_contact['firstname'])) ? $loggedin_contact['firstname'] : '';
            $lastname = (isset($loggedin_contact['lastname']) && !empty($loggedin_contact['lastname'])) ? $loggedin_contact['lastname'] : '';
            $email = (isset($loggedin_contact['email']) && !empty($loggedin_contact['email'])) ? $loggedin_contact['email'] : '';
            if (!empty($email)) {
              $emailreadonly = "readonly='true'";
            }
          }
        }
        ?>
        <div id="identity_fields">
          <div class="form-group">
            <label for="acceptance_firstname" class="control-label col-sm-2">
              <span class="text-left inline-block full-width">
                <?php echo _l('client_firstname'); ?>
              </span>
            </label>
            <div class="col-sm-10">
              <input type="text" name="acceptance_firstname" id="acceptance_firstname" class="form-control" required="true" value="<?= $firstname ?>" <?= $readonly ?>>
            </div>
          </div>
          <div class="form-group">
            <label for="acceptance_lastname" class="control-label col-sm-2">
              <span class="text-left inline-block full-width">
                <?php echo _l('client_lastname'); ?>
              </span>
            </label>
            <div class="col-sm-10">
              <input type="text" name="acceptance_lastname" id="acceptance_lastname" class="form-control" required="true" value="<?= $lastname ?>" <?= $readonly ?>>
            </div>
          </div>
          <div class="form-group">
            <label for="acceptance_email" class="control-label col-sm-2">
              <span class="text-left inline-block full-width">
                <?php echo _l('client_email'); ?>
              </span>
            </label>
            <div class="col-sm-10">
              <input type="email" name="acceptance_email" id="acceptance_email" class="form-control" required="true" value="<?= $email ?>" <?= $emailreadonly ?>>
              <?php if (strpos($this->uri->uri_string(), "contract") !== false) { ?>
                <button type="button" id="sendOtpButton" class="btn btn-primary" style="margin-top: 10px;">Send OTP</button>
              <?php } ?>
            </div>
          </div>

          <?php if (strpos($this->uri->uri_string(), "contract") !== false) { ?>
            <div class="form-group">
              <label for="physical_signature" class="control-label col-sm-2">
                <span class="text-left inline-block full-width">
                  <?php echo _l('physical_signature'); ?>
                </span>
              </label>
              <div class="col-sm-10">
                <input type="file" name="physical_signature" id="physical_signature" class="form-control" required="true">
              </div>
            </div>
            <div class="form-group">
              <label for="selfie" class="control-label col-sm-2">
                <span class="text-left inline-block full-width">
                  Selfie
                </span>
              </label>
              <div class="col-sm-10">
                <button type="button" id="openSelfieModal" class="btn btn-primary">Click Selfie</button>
                <input type="hidden" name="selfie" id="selfieInput" required="true">
                <p id="selfieError" class="text-danger" style="display:none;">Selfie is required.</p>
                <div id="selfiePreviewContainer" style="margin-top: 10px; display: none;">
                  <img id="selfiePreview" src="" alt="Selfie Preview" style="width: 100%; max-width: 200px;">
                  <button type="button" id="removeSelfie" class="btn btn-danger btn-xs" style="position: absolute; top: 5px; right: 5px;">
                    <i class="fa fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          <?php } ?>
          <p class="bold" id="signatureLabel"><?php echo _l('signature'); ?></p>
          <div class="signature-pad--body">
            <canvas id="signature" height="130" width="550"></canvas>
          </div>
          <input type="text" style="width:1px; height:1px; border:0px;" tabindex="-1" name="signature" id="signatureInput">
          <div class="dispay-block">
            <button type="button" class="btn btn-default btn-xs clear" tabindex="-1" data-action="clear"><?php echo _l('clear'); ?></button>
            <button type="button" class="btn btn-default btn-xs" tabindex="-1" data-action="undo"><?php echo _l('undo'); ?></button>
          </div>
        </div>
        <?php hooks()->do_action('after_confirmation_identity_fields'); ?>
      </div>
      <div class="modal-footer">
        <?php if (strpos($this->uri->uri_string(), "contract") !== false) { ?>
          <label class="text-left text-muted e-sign-legal-text">
            <input type="checkbox" name="confirm" id="contractCheckbox" required="true">
            <?php echo _l(get_option('contract_sign_text'), '', false); ?>
          </label>
        <?php } else { ?>
          <p class="text-left text-muted e-sign-legal-text">
            <?php echo _l(get_option('e_sign_legal_text'), '', false); ?>
          </p>
        <?php } ?>

        <hr />
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('cancel'); ?></button>
        <button type="submit" data-loading-text="<?php echo _l('wait_text'); ?>" autocomplete="off" data-form="#identityConfirmationForm" class="btn btn-success"><?php echo _l('e_signature_sign'); ?></button>
      </div>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="selfieModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Take a Selfie</h4>
      </div>
      <div class="modal-body">
        <div id="cameraContainer">
          <video id="cameraView" autoplay></video>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="takeSelfie">Capture</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="otpModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">OTP Verification</h4>
      </div>
      <div class="modal-body">
        <form id="otpForm">
          <div class="form-group">
            <label for="otpInput">Enter OTP</label>
            <div id="otpInput" class="d-flex">
              <input type="text" class="form-control otp-input" maxlength="1">
              <input type="text" class="form-control otp-input" maxlength="1">
              <input type="text" class="form-control otp-input" maxlength="1">
              <input type="text" class="form-control otp-input" maxlength="1">
              <input type="text" class="form-control otp-input" maxlength="1">
              <input type="text" class="form-control otp-input" maxlength="1">
            </div>
          </div>
          <button type="button" class="btn btn-info" id="resendOtp">Resend OTP</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="otpVerify">Verify</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<?php
$this->app_scripts->theme('signature-pad', 'assets/plugins/signature-pad/signature_pad.min.js');
?>
<script>
  var contractId = "<?= (isset($contract)) ? $contract->id : '' ?>";
  $(function() {
    SignaturePad.prototype.toDataURLAndRemoveBlanks = function() {
      var canvas = this._ctx.canvas;
      // First duplicate the canvas to not alter the original
      var croppedCanvas = document.createElement('canvas'),
        croppedCtx = croppedCanvas.getContext('2d');

      croppedCanvas.width = canvas.width;
      croppedCanvas.height = canvas.height;
      croppedCtx.drawImage(canvas, 0, 0);

      // Next do the actual cropping
      var w = croppedCanvas.width,
        h = croppedCanvas.height,
        pix = {
          x: [],
          y: []
        },
        imageData = croppedCtx.getImageData(0, 0, croppedCanvas.width, croppedCanvas.height),
        x, y, index;

      for (y = 0; y < h; y++) {
        for (x = 0; x < w; x++) {
          index = (y * w + x) * 4;
          if (imageData.data[index + 3] > 0) {
            pix.x.push(x);
            pix.y.push(y);

          }
        }
      }
      pix.x.sort(function(a, b) {
        return a - b
      });
      pix.y.sort(function(a, b) {
        return a - b
      });
      var n = pix.x.length - 1;

      w = pix.x[n] - pix.x[0];
      h = pix.y[n] - pix.y[0];
      var cut = croppedCtx.getImageData(pix.x[0], pix.y[0], w, h);

      croppedCanvas.width = w;
      croppedCanvas.height = h;
      croppedCtx.putImageData(cut, 0, 0);

      return croppedCanvas.toDataURL();
    };

    function signaturePadChanged() {
      var input = document.getElementById('signatureInput');
      var $signatureLabel = $('#signatureLabel');
      $signatureLabel.removeClass('text-danger');

      if (signaturePad.isEmpty()) {
        $signatureLabel.addClass('text-danger');
        input.value = '';
        return false;
      }

      $('#signatureInput-error').remove();
      var partBase64 = signaturePad.toDataURLAndRemoveBlanks();
      partBase64 = partBase64.split(',')[1];
      input.value = partBase64;
    }

    var canvas = document.getElementById("signature");
    var clearButton = document.querySelector("[data-action=clear]");
    var undoButton = document.querySelector("[data-action=undo]");
    var identityFormSubmit = document.getElementById('identityConfirmationForm');

    var signaturePad = new SignaturePad(canvas, {
      maxWidth: 2,
      onEnd: function() {
        signaturePadChanged();
      }
    });

    clearButton.addEventListener("click", function(event) {
      signaturePad.clear();
      signaturePadChanged();
    });

    undoButton.addEventListener("click", function(event) {
      var data = signaturePad.toData();
      if (data) {
        data.pop();
        signaturePad.fromData(data);
        signaturePadChanged();
      }
    });

    $('#openSelfieModal').click(function() {
      navigator.mediaDevices.getUserMedia({
          video: true
        })
        .then(function(stream) {
          $('#selfieModal').modal('show');
          var video = document.getElementById('cameraView');
          video.srcObject = stream;
        })
        .catch(function(err) {
          $('#selfieError').text('Need permission to access camera').show();
          $('#selfieModal').modal('hide');
        });

    });

    $('#selfieModal').on('hidden.bs.modal', function(e) {
      var video = document.getElementById('cameraView');
      if (video.srcObject) {
        var stream = video.srcObject;
        var tracks = stream.getTracks();
        tracks.forEach(function(track) {
          track.stop();
        });
        video.srcObject = null;
      }
    });

    $('#takeSelfie').click(function() {
      var video = document.getElementById('cameraView');
      var canvas = document.createElement('canvas');
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      var ctx = canvas.getContext('2d');
      ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

      $('#selfieInput').val(canvas.toDataURL('image/jpeg').split(',')[1]);
      $('#selfiePreview').attr('src', canvas.toDataURL('image/jpeg')).show();
      $('#selfiePreviewContainer').show();
      $('#selfieError').hide();
      $('#openSelfieModal').hide();
      $('#selfieModal').modal('hide');
    });

    $('#removeSelfie').click(function() {
      $('#selfieInput').val('');
      $('#selfiePreview').attr('src', '').hide();
      $('#openSelfieModal').show();
      $('#selfiePreviewContainer').hide();
    });

    $('#identityConfirmationForm').submit(function() {
      signaturePadChanged();
      if (contractId != "" && contractId != null) {
        $('#sendOtpButton').closest('div').find('.text-danger').remove();
        if ($('#selfieInput').val() === '') {
          $('#selfieError').show();
          return false;
        }
        if ($('p[data-otpverified="true"]').length == 0) {
          $('#sendOtpButton').after('<div class="text-danger">Please verify your email using OTP verification.</div>');
          setTimeout(() => {
            $('#identityConfirmationForm').find('button[type="submit"]').html("Sign").removeClass('disabled').prop('disabled', false);
          }, 100);
          return false;
        }
      }
    });

    $('#sendOtpButton').click(function() {
      $('#sendOtpButton').closest('div').find('.text-danger').remove();
      otpSend("send");
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

    $('#resendOtp').on('click', function() {
      otpSend(sendType = "resend")
    });

    $('#otpVerify').on('click', function() {
      $('#otpInput').next('.text-message').remove();
      var email = $('#acceptance_email').val();
      var otp = $('.otp-input').map(function() {
        return $(this).val();
      }).get().join('');

      if (otp.length !== 6 || !/^\d+$/.test(otp)) {
        $('#otpInput').after('<p class="text-danger text-message">Please enter a valid 6-digit OTP.</p>');
        return;
      }
      $('#otpVerify').prop('disabled', true).html('Verifying... <i class="fa fa-spinner fa-spin"></i>');
      $.ajax({
        url: "<?= site_url('contract/otp_verification') ?>",
        method: 'POST',
        dataType: 'JSON',
        data: {
          email: email,
          contract_id: contractId,
          type: 'verify-otp',
          otp: otp,
          mode: 'contract_sign'
        },
        success: function(response) {
          if (response.success) {
            $('#otpModal').modal('hide');
            $('.otp-input').val("");
            $('#acceptance_email').prop('readonly', true);
            $('#acceptance_email').after('<p data-otpverified="true" class="text-success">' + response.message + '</p>');
            $('#sendOtpButton').hide();
          } else {
            $('#otpInput').after('<p class="text-danger text-message">' + response.message + '</p>');
          }
        },
        complete: function() {
          $('#otpVerify').prop('disabled', false).html('Verify');
        }
      });


    });

  });

  var resendTimeout;

  function otpSend(sendType = "send") {
    var email = $('#acceptance_email').val();
    if (email) {
      if (sendType == "send") {
        $('#sendOtpButton').prop('disabled', true).html('Sending... <i class="fa fa-spinner fa-spin"></i>');
        $('#acceptance_email').parent('div').find('.text-danger').remove();
      } else { //resend
        $('#resendOtp').prop('disabled', true).html('Sending... <i class="fa fa-spinner fa-spin"></i>');
        $('#otpInput').next('.text-message').remove();
      }
      $('#acceptance_email').prop('readonly', true);
      $.ajax({
        url: "<?= site_url('contract/otp_verification') ?>",
        method: 'POST',
        dataType: 'JSON',
        data: {
          email: email,
          contract_id: contractId,
          type: 'send-otp',
          mode: 'contract_sign'
        },
        success: function(response) {
          if (sendType == "send") {
            if (response.success) {
              $('#otpVerify').prop('disabled', false).html('Verify');
              $('#otpModal').find('.text-danger').remove();
              $('#otpModal').find('.otp-input').val('');
              $('#otpModal').modal('show');
              startResendCountdown();
            } else {
              $('#acceptance_email').after('<p id="acceptance_email-error" class="text-danger">' + response.message + '</p>');
            }
          } else { //resend
            if (response.success) {
              $('#otpInput').after('<p class="text-message text-success">OTP re-send successfully.</p>');
              startResendCountdown();
            } else {
              $('#acceptance_email').after('<p id="acceptance_email-error" class="text-danger">' + response.message + '</p>');
            }
          }

        },
        complete: function() {
          if (sendType == "send") {
            $('#sendOtpButton').prop('disabled', false).html('Send OTP');
            $('#acceptance_email').prop('readonly', false);
          }
        }
      });
    } else {
      if (sendType == "send") {
        $('#acceptance_email').parent('div').find('.text-danger').remove();
        $('#acceptance_email').after('<p id="acceptance_email-error" class="text-danger">Please eneter email.</p>');
      }

    }
  }

  function startResendCountdown() {
    clearInterval(resendTimeout);
    var secondsLeft = 60;
    $('#resendOtp').prop('disabled', true).html('Resend OTP (' + secondsLeft + 's)');
    resendTimeout = setInterval(function() {
      secondsLeft--;
      if (secondsLeft > 0) {
        $('#resendOtp').html('Resend OTP (' + secondsLeft + 's)');
      } else {
        clearInterval(resendTimeout);
        $('#resendOtp').prop('disabled', false).html('Resend OTP');
      }
    }, 1000);
  }
</script>
<style>
  #cameraView {
    width: 100%;
    height: 400px;
  }

  #selfiePreviewContainer {
    position: relative;
    margin-top: 10px;
    display: none;
    width: 100%;
    max-width: 200px;
    overflow: hidden;
    position: relative;
  }

  #selfiePreview {
    width: 100%;
    border: 1px solid #000;
    border-radius: 10px;
  }

  .modal {
    overflow: auto !important;
  }

  #otpInput {
    display: flex;
  }

  .otp-input {
    width: 4rem;
    text-align: center;
    margin-right: 0.5rem;
  }
</style>