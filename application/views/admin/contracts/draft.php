<?php
defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="col-md-6 left-column">
            <div class="panel_s">
               <div class="panel-heading">
                  <a href="<?= admin_url('contracts/drafts/' . $main_id . '/' . $sub_id) ?>"><i class="fa fa-arrow-left fa-1x" aria-hidden="true"></i></a>
                  &nbsp;&nbsp; <?= (isset($draft->id)) ? "Update draft" : 'Add New Draft' ?>
               </div>
               <div class="panel-body">
                  <div class="row">
                     <?php echo form_open(admin_url('contracts/draft/' . $main_id . '/' . $sub_id), array('id' => 'draft-form')); ?>
                     <input type="hidden" id="id" name="id" value="<?= (isset($draft->id)) ? $draft->id : '' ?>" />
                     <input type="hidden" id="main_type" name="main_type" value="<?= $main_id ?>" />
                     <input type="hidden" id="sub_type" name="sub_type" value="<?= $sub_id ?>" />

                     <?= all_type_input_render([
                        "label" => "Draft title",
                        "id" => "draft_title",
                        "name" => "draft_title",
                        "type" => "text",
                        "selected_value" => (isset($draft->draft_title)) ? $draft->draft_title : '',
                        "is_required" => true,
                        "form" => 'draft-form',
                     ], 'col-md-12', true);
                     ?>
                     <?= all_type_input_render([
                        "label" => "Terms & Conditions",
                        "id" => "content",
                        "name" => "content",
                        "type" => "textarea",
                        "rows" => 7,
                        "is_required" => true,
                        "selected_value" => (isset($draft->content)) ? $draft->content : '',
                        "className" => "text-editor",
                        "form" => 'draft-form',
                     ], 'col-md-12', false);
                     ?>
                     <div class="col-md-12">
                        <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                     </div>
                  </div>
                  <?php echo form_close(); ?>
               </div>
            </div>
         </div>
         <div class="col-md-6">
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin">
                     <?php echo _l('available_merge_fields'); ?>
                  </h4>
                  <hr class="hr-panel-heading" />
                  <div class="row">
                     <div class="col-md-12">
                        <div class="row available_merge_fields_container">
                           <?php
                           $templateSlug = "contract-drafts";
                           $templateType = "contract";
                           $mergeLooped = array();
                           foreach ($available_merge_fields as $field) {
                              foreach ($field as $key => $val) {
                                 if (in_array($key, ['contract', 'client', 'other','vendors'])) {
                                    echo '<div class="col-md-6 merge_fields_col">';
                                    echo '<h5 class="bold">' . ucfirst($key) . '</h5>';
                                    foreach ($val as $_field) {
                                       if (
                                          count($_field['available']) == 0
                                          && isset($_field['templates']) && in_array($templateSlug, $_field['templates'])
                                       ) {
                                          // Fake data to simulate foreach loop and check the templates key for the available slugs
                                          $_field['available'][] = '1';
                                       }
                                       foreach ($_field['available'] as $_available) {
                                          if (($_available == $templateType || isset($_field['templates']) && in_array($templateSlug, $_field['templates'])) && !in_array($_field['name'], $mergeLooped)) {
                                             $mergeLooped[] = $_field['name'];
                                             echo '<p>' . $_field['name'];
                                             echo '<span class="pull-right"><a href="#" class="add_merge_field">';
                                             echo $_field['key'];
                                             echo '</a>';
                                             echo '</span>';
                                             echo '</p>';
                                          }
                                       }
                                    }
                                    echo '</div>';
                                 }
                              }
                           }
                           ?>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php init_tail(); ?>
<script>
   $(function() {
      init_editor('.text-editor');

      appValidateForm($('#draft-form'), {
         draft_title: 'required',
         content: {
            tinymceRequired: true
         }
      });

      $.validator.addMethod("tinymceRequired", function(value, element, params) {
         var editorContent = tinyMCE.get($(element).attr("id")).getContent();
         return editorContent.trim() !== '';
      }, "This field is required.");

      $(document).on('input', 'input[name="draft_title"]', function() {
         $('button[type="submit"]').prop("disabled", false);
         var input = $('input[name="draft_title"]');
         input.closest('.form-group').find('#fielddraft_title-error').remove();
         var id = $('input[name="id"]').val();
         var type = 'draft';
         var value = $(this).val();
         var main_type = $('#main_type').val();
         var sub_type = $('#sub_type').val();
         if (value != "" && value != null) {
            $.ajax({
               url: "<?php echo admin_url('contracts/unique_check') ?>",
               method: "POST",
               data: {
                  type: type,
                  value: value,
                  id: id,
                  main_id: main_type,
                  sub_id: sub_type
               },
               dataType: 'json'
            }).done(function(result) {
               if (result.success) {
                  $('button[type="submit"]').prop("disabled", true);
                  input.closest('.form-group').append('<p id="fielddraft_title-error" class="text-danger">' + result.message + '</p>');
               }
            });
         }
      });

      var typingTimer;
      var doneTypingInterval = 1000;
      tinymce.activeEditor.on('input change keyup', function() {
         clearTimeout(typingTimer);
         typingTimer = setTimeout(save_contract_draft_data, doneTypingInterval);
      });

      function save_contract_draft_data() {
         var draft_id = "<?= (isset($draft->id)) ? $draft->id : '' ?>";
         if (draft_id != "" && draft_id != null) {
            var editor = tinyMCE.activeEditor;
            var data = {};
            data.id = draft_id;
            data.content = editor.getContent();
            $.post(admin_url + 'contracts/save_contract_draft_data', data).done(function(response) {
               response = JSON.parse(response);
               editor.save();
            }).fail(function(error) {
               console.log(error)
            });
         }
      }

      // Add merge field to tinymce
      $('.add_merge_field').on('click', function(e) {
         e.preventDefault();
         tinymce.activeEditor.execCommand('mceInsertContent', false, $(this).text());
      });

   });
</script>
<style>
   .fa-arrow-left {
      font-size: 15px;
   }
</style>
</body>

</html>