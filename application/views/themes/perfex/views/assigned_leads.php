<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$status_arr = get_lead_customer_assign_status();
?>
<style>
  .dropup-fix .dropdown-menu {
    top: 100% !important;
    bottom: auto !important;
    transform: translateY(0) !important;
  }

  .modal-title {
    color: #fff;
  }

  .btn-default {
    color: #fff;
  }
</style>
<div class="panel_s section-heading section-leads">
  <div class="panel-body">
    <h4 class="no-margin section-text">Assigned Leads</h4>
  </div>
</div>
<div class="panel_s">
  <div class="panel-body">
    <table class="table table-leads" data-order-col="0" data-order-type="desc">
      <thead>
        <tr>
          <th class="th-leads-number">Lead ID</th>
          <th class="th-leads-subject">Name</th>
          <th class="th-leads-total">Company</th>
          <th class="th-leads-open-till">Phone</th>
          <th class="th-leads-date">Email</th>
          <th class="th-leads-status">Address</th>
          <th class="th-leads-status">Status</th>
          <th class="th-leads-status">Last Updated By</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($leads as $lead) { ?>
          <tr>
            <td><?= $lead->id ?></td>

            <td>
              <a href="javascript:;" onclick="view_lead_data(<?= $lead->id ?>);return false;">
                <?= htmlspecialchars($lead->name) ?>
              </a>
            </td>

            <td><?= !empty($lead->company) ? htmlspecialchars($lead->company) : '-' ?></td>
            <td><?= !empty($lead->phonenumber) ? htmlspecialchars($lead->phonenumber) : '-' ?></td>
            <td><?= !empty($lead->email) ? htmlspecialchars($lead->email) : '-' ?></td>

            <td>
              <?= htmlspecialchars($lead->address) ?>
              <br>
              <?= htmlspecialchars($lead->city) ?>, <?= htmlspecialchars($lead->state) ?>, <?= get_country_name($lead->country) ?> - <?= htmlspecialchars($lead->zip) ?>
            </td>

            <td>
              <?php
              $selectedStatus = (isset($lead->assigned_customer_status) && isset($status_arr[$lead->assigned_customer_status]))
                ? $status_arr[$lead->assigned_customer_status]
                : (isset($status_arr[0]) ? $status_arr[0] : ['name' => '-', 'color' => '']);

              $statusColor = !empty($selectedStatus['color']) ? $selectedStatus['color'] : '';
              $statusName  = !empty($selectedStatus['name']) ? $selectedStatus['name'] : '-';
              $labelClass = empty($statusColor) ? 'default' : '';
              $styleAttr = '';
              if (!empty($statusColor)) {
                $styleAttr = 'style="color:' . $statusColor . ';border:1px solid ' . $statusColor . ';"';
              }
              ?>
              <span class="inline-block label label-<?= $labelClass ?>" <?= $styleAttr ?>>
                <?= htmlspecialchars($statusName) ?>

                <div class="dropdown inline-block mleft5 table-export-exclude dropup-fix">
                  <a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsStatus-<?= $lead->id ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span data-toggle="tooltip" title="<?= _l('ticket_single_change_status') ?>">
                      <i class="fa fa-caret-down" aria-hidden="true"></i>
                    </span>
                  </a>

                  <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsStatus-<?= $lead->id ?>">
                    <?php foreach ($status_arr as $key => $status) {
                      if (isset($lead->assigned_customer_status) && $lead->assigned_customer_status == $key) continue;
                      $safeName = isset($status['name']) ? $status['name'] : '-';
                    ?>
                      <li>
                        <a href="#" onclick="customer_lead_change_status(<?= $key ?>, <?= $lead->id ?>); return false;">
                          <?= htmlspecialchars($safeName) ?>
                        </a>
                      </li>
                    <?php } ?>
                  </ul>
                </div>
              </span>

            </td>

            <td>
              <?php
              $updatedBy = !empty($lead->assigned_customer_last_updated_by) ? htmlspecialchars($lead->assigned_customer_last_updated_by) : '-';
              if (!empty($lead->assigned_customer_last_updated_at) && $lead->assigned_customer_last_updated_at !== '0000-00-00 00:00:00') {
                $updatedAt = date('d-m-Y h:i A', strtotime($lead->assigned_customer_last_updated_at));
              } else {
                $updatedAt = date('d-m-Y h:i A'); // fallback to now (same behavior as table.php)
              }
              echo $updatedBy . " At " . $updatedAt;
              ?>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
<div class="modal fade" id="leadDataModal" tabindex="-1" role="dialog" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
            aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"></h4>
      </div>
      <div class="modal-body">
        <div class="row" id="leadDataContent">
          <!-- JS will inject lead fields here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        </div>
      </div>

    </div>
  </div>
</div>
<script>
  $(document).ready(function() {
    $('.table-leads').DataTable({
      dom: 'Bfrtip',
      buttons: [{
          extend: 'excelHtml5',
          text: '<i class="fa fa-file-excel-o"></i> Excel',
          titleAttr: 'Export to Excel',
          className: 'btn btn-default btn-sm',
          exportOptions: {
            columns: ':not(.no-export)'
          },
          title: 'Assigned Leads Report',
          filename: 'assigned_leads_' + new Date().getTime()
        },
        {
          extend: 'csvHtml5',
          text: '<i class="fa fa-file-text-o"></i> CSV',
          titleAttr: 'Export to CSV',
          className: 'btn btn-default btn-sm',
          exportOptions: {
            columns: ':not(.no-export)'
          },
          title: 'Assigned Leads Report',
          filename: 'assigned_leads_' + new Date().getTime()
        },
        {
          extend: 'pdfHtml5',
          text: '<i class="fa fa-file-pdf-o"></i> PDF',
          titleAttr: 'Export to PDF',
          className: 'btn btn-default btn-sm',
          exportOptions: {
            columns: ':not(.no-export)'
          },
          title: 'Assigned Leads Report',
          filename: 'assigned_leads_' + new Date().getTime(),
          orientation: 'landscape',
          pageSize: 'A4',
          customize: function(doc) {
            doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');
            doc.styles.tableHeader = {
              bold: true,
              fontSize: 11,
              color: 'white',
              fillColor: '#2d4154',
              alignment: 'center'
            };
          }
        },
        {
          extend: 'print',
          text: '<i class="fa fa-print"></i> Print',
          titleAttr: 'Print Table',
          className: 'btn btn-default btn-sm',
          exportOptions: {
            columns: ':not(.no-export)'
          },
          title: 'Assigned Leads Report',
          customize: function(win) {
            $(win.document.body).css('font-size', '10pt');
            $(win.document.body).find('table')
              .addClass('compact')
              .css('font-size', 'inherit');
          }
        }
      ],
      order: [
        [0, 'desc']
      ],
      responsive: false,
      scrollX: true,
      autoWidth: false,
      pageLength: 25,
      columnDefs: [{
          targets: '_all',
          visible: true
        }
      ],
      lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, "All"]
      ],
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search leads...",
        lengthMenu: "Show _MENU_ entries",
        info: "Showing _START_ to _END_ of _TOTAL_ leads",
        infoEmpty: "No leads found",
        infoFiltered: "(filtered from _MAX_ total leads)"
      }
    });
  });
  (function($) {
    var appendedMenus = new Map();

    function positionMenu($toggle, $menu) {
      var offset = $toggle.offset();
      var top = offset.top + $toggle.outerHeight();
      var left = offset.left;

      var menuWidth = $menu.outerWidth();
      var docWidth = $(window).width();
      if (left + menuWidth > docWidth - 10) {
        left = Math.max(10, docWidth - menuWidth - 10);
      }

      $menu.css({
        display: 'block',
        position: 'absolute',
        top: top + 'px',
        left: left + 'px',
        zIndex: 999999,
        width: '5%'
      });
    }

    $('body').on('show.bs.dropdown', '.table .dropdown', function(e) {
      var $dropdown = $(this);
      var $toggle = $dropdown.find('[data-toggle="dropdown"], .dropdown-toggle').first();
      var $menu = $dropdown.find('.dropdown-menu').first();

      if (!$menu.length || appendedMenus.has($menu.get(0))) return;
      var original = {
        parent: $menu.parent(),
        nextSibling: $menu.next().length ? $menu.next() : null
      };
      appendedMenus.set($menu.get(0), original);
      $menu.appendTo('body');
      $menu.css({
        position: 'absolute',
        transform: 'none'
      });
      setTimeout(function() {
        positionMenu($toggle, $menu);
      }, 0);
    });

    $('body').on('shown.bs.dropdown', '.table .dropdown', function(e) {
      var $dropdown = $(this);
      var $toggle = $dropdown.find('[data-toggle="dropdown"], .dropdown-toggle').first();
      var $menu = $('body').find('.dropdown-menu').filter(function() {
        return appendedMenus.has(this);
      }).first();

      if ($menu && $menu.length) {
        positionMenu($toggle, $menu);
      }
    });

    $('body').on('hidden.bs.dropdown', '.table .dropdown', function(e) {
      var $dropdown = $(this);
      var $menu = $('body').find('.dropdown-menu').filter(function() {
        return appendedMenus.has(this) && $(this).data('__origin_dropdown') === undefined;
      }).first();

      if (!$menu.length) {
        appendedMenus.forEach(function(orig, menuEl) {
          var $m = $(menuEl);
          if ($m.closest('body').length && $m.length) {
            if (orig.nextSibling && orig.nextSibling.length) {
              $m.insertBefore(orig.nextSibling);
            } else {
              $m.appendTo(orig.parent);
            }
            $m.removeAttr('style');
            appendedMenus.delete(menuEl);
          }
        });
        return;
      }

      var menuEl = $menu.get(0);
      var orig = appendedMenus.get(menuEl);
      if (orig) {
        if (orig.nextSibling && orig.nextSibling.length) {
          $menu.insertBefore(orig.nextSibling);
        } else {
          $menu.appendTo(orig.parent);
        }
        $menu.removeAttr('style');
        appendedMenus.delete(menuEl);
      } else {
        $menu.removeAttr('style');
      }
    });

    $(window).on('scroll resize', function() {
      $('body').find('.dropdown-menu').each(function() {
        if (appendedMenus.has(this)) {
          var $menu = $(this);
          var possibleToggles = $('.dropdown-toggle, [data-toggle="dropdown"]');
          var chosen = null;
          possibleToggles.each(function() {
            var off = $(this).offset();
            if (!off) return;
            var top = off.top + $(this).outerHeight();
            if (Math.abs(top - $menu.offset().top) < 50) {
              chosen = $(this);
              return false;
            }
          });
          if (chosen) positionMenu(chosen, $menu);
        }
      });
    });

  })(jQuery);

  function view_lead_data(lead_id) {
    $.ajax({
      url: "<?php echo site_url('clients/get_lead_data') ?>",
      method: "POST",
      data: {
        lead_id: lead_id
      },
      dataType: 'json'
    }).done(function(result) {
      if (result.success) {
        var data = result.lead_data || {};

        var fields = [{
            k: 'name',
            label: 'Name'
          },
          {
            k: 'title',
            label: 'Position'
          },
          {
            k: 'company',
            label: 'Company'
          },
          {
            k: 'phonenumber',
            label: 'Phone'
          },
          {
            k: 'email',
            label: 'Email'
          },
          {
            k: 'website',
            label: 'Website'
          },
          {
            k: 'address',
            label: 'Address'
          },
          {
            k: 'city',
            label: 'City'
          },
          {
            k: 'state',
            label: 'State'
          },
          {
            k: 'country',
            label: 'Country'
          },
          {
            k: 'zip',
            label: 'ZIP / Postal Code'
          },
          {
            k: 'gst_in',
            label: 'GST IN'
          }
        ];

        var html = '<div class="col-md-12"><table class="table table-striped table-condensed">';
        html += '<tbody>';
        fields.forEach(function(f) {
          var raw = (typeof data[f.k] !== 'undefined' && data[f.k] !== null && data[f.k] !== '') ? data[f.k] : '-';

          var safe = $('<div>').text(raw).html();

          if (f.k === 'phonenumber' && raw !== '-') {
            safe = '<a href="tel:' + $('<div>').text(raw).html() + '">' + safe + '</a>';
          } else if (f.k === 'email' && raw !== '-') {
            safe = '<a href="mailto:' + $('<div>').text(raw).html() + '">' + safe + '</a>';
          }

          html += '<tr>';
          html += '<th style="width:30%; vertical-align:middle;">' + f.label + '</th>';
          html += '<td>' + safe + '</td>';
          html += '</tr>';
        });
        html += '</tbody></table></div>';

        $('#leadDataContent').html(html);

        var title = 'Lead #' + (data.id || lead_id);
        if (data.name) title += ' — ' + $('<div>').text(data.name).html();
        $('#leadDataModal .modal-title').html(title);

        $('#leadDataModal').modal('show');
      } else {
        alert_float('danger', result.message);
      }
    }).fail(function(xhr, status, err) {
      alert_float('danger', 'Request failed: ' + status);
    });
  }

  function customer_lead_change_status(status, lead_id) {
    $.ajax({
      url: "<?php echo site_url('clients/customer_lead_status_change') ?>",
      method: "POST",
      data: {
        status: status,
        lead_id: lead_id,
      },
      dataType: 'json'
    }).done(function(result) {
      if (result.success) {
        alert_float('success', result.message);
        setTimeout(() => {
          location.reload();
        }, 1000);
      } else {
        alert_float('danger', result.message);
      }
    });
  }
</script>