{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
*}

{* Financial search component. *}
<div id="enableDisableStatusMsg" class="crm-container" style="display:none"></div>
<div class="action-link">
  <a accesskey="N" href="{crmURL p='civicrm/financial/batch' q="reset=1&action=add&context=$batchStatus"}" id="newBatch" class="button"><span><i class="crm-i fa-plus-circle" aria-hidden="true"></i> {ts}New Accounting Batch{/ts}</span></a>
</div>
<div class="crm-form-block crm-search-form-block">
  <details class="crm-accordion-bold crm-activity_search-accordion" open>
    <summary>
      {ts}Filter Results{/ts}
    </summary>
    <div class="crm-accordion-body">
      <div id="financial-search-form" class="crm-block crm-form-block">
        <table class="form-layout-compressed">
          {if !empty($elements)}
          {* Loop through all defined search criteria fields (defined in the buildForm() function). *}
          {foreach from=$elements item=element}
            <tr class="crm-financial-search-form-block-{$element}">
              <td class="label">{$form.$element.label}</td>
              <td>{$form.$element.html}</td>
            </tr>
          {/foreach}
          {/if}
        </table>
      </div>
    </div>
  </details>
</div>
{if !empty($form.batch_update)}<div class="form-layout-compressed">{$form.batch_update.html}&nbsp;{$form.submit.html}</div><br/>{/if}
<table id="crm-batch-selector-{$batchStatus}" class="row-highlight">
  <thead>
    <tr>
      <th class="crm-batch-checkbox">{if !empty($form.toggleSelect.html)}{$form.toggleSelect.html}{/if}</th>
      <th class="crm-batch-name">{ts}Batch Name{/ts}</th>
      <th class="crm-batch-payment_instrument">{ts}Payment Method{/ts}</th>
      <th class="crm-batch-item_count">{ts}Item Count{/ts}</th>
      <th class="crm-batch-total">{ts}Total Amount{/ts}</th>
      <th class="crm-batch-status">{ts}Status{/ts}</th>
      <th class="crm-batch-created_by">{ts}Created By{/ts}</th>
      <th></th>
    </tr>
  </thead>
</table>
{include file="CRM/Form/validate.tpl"}
{literal}
<script type="text/javascript">
CRM.$(function($) {
  var batchSelector;
  buildBatchSelector();
  $("#batch_update").prop('disabled', false);

  $('#financial-search-form :input')
    .change(function() {
      if (!$(this).hasClass('crm-inline-error')) {
        batchSelector.fnDraw();
      }
    })
    .keypress(function(event) {
      if (event.which == 13) {
        event.preventDefault();
        $(this).change();
        return false;
      }
    });

  var checkedRows = [];
  function buildBatchSelector() {
    var ZeroRecordText = {/literal}'<div class="status messages">{ts escape="js"}No Accounting Batches match your search criteria.{/ts}</div>'{literal};
    var sourceUrl = {/literal}'{crmURL p="civicrm/ajax/batchlist" h=0 q="snippet=4&context=financialBatch"}'{literal};

    batchSelector = $('#crm-batch-selector-{/literal}{$batchStatus}{literal}').dataTable({
      "bFilter" : false,
      "bAutoWidth" : false,
      "aaSorting" : [],
      "aoColumns" : [
        {sClass:'crm-batch-checkbox', bSortable:false},
        {sClass:'crm-batch-name'},
        {sClass:'crm-batch-payment_instrument'},
        {sClass:'crm-batch-item_count right', bSortable:false},
        {sClass:'crm-batch-total right', bSortable:false},
        {sClass:'crm-batch-status'},
        {sClass:'crm-batch-created_by'},
        {sClass:'crm-batch-links', bSortable:false},
       ],
      "bProcessing": true,
      "asStripClasses" : ["odd-row", "even-row"],
      "sPaginationType": "full_numbers",
      "sDom" : '<"crm-datatable-pager-top"lfp>rt<"crm-datatable-pager-bottom"ip>',
      "bServerSide": true,
      "bJQueryUI": true,
      "sAjaxSource": sourceUrl,
      "iDisplayLength": 25,
      "oLanguage": {
        "sZeroRecords": ZeroRecordText,
        "sProcessing": {/literal}"{ts escape='js'}Processing...{/ts}"{literal},
        "sLengthMenu": {/literal}"{ts escape='js'}Show _MENU_ entries{/ts}"{literal},
        "sInfo": {/literal}"{ts escape='js'}Showing _START_ to _END_ of _TOTAL_ entries{/ts}"{literal},
        "sInfoEmpty": {/literal}"{ts escape='js'}Showing 0 to 0 of 0 entries{/ts}"{literal},
        "sInfoFiltered": {/literal}"{ts escape='js'}(filtered from _MAX_ total entries) {/ts}"{literal},
        "sSearch": {/literal}"{ts escape='js'}Search:{/ts}"{literal},
        "oPaginate": {
          "sFirst": {/literal}"{ts escape='js'}First{/ts}"{literal},
          "sPrevious": {/literal}"{ts escape='js'}Previous{/ts}"{literal},
          "sNext": {/literal}"{ts escape='js'}Next{/ts}"{literal},
          "sLast": {/literal}"{ts escape='js'}Last{/ts}"{literal}
        }
      },
      "fnServerParams": function (aoData) {
        $('#financial-search-form :input').each(function() {
          if ($(this).val()) {
            aoData.push(
              {name:$(this).attr('id'), value: $(this).val()}
            );
          }
        });
        checkedRows = [];
        $("#crm-batch-selector-{/literal}{$batchStatus}{literal} input.select-row:checked").each(function() {
          checkedRows.push('#' + $(this).attr('id'));
        });
      },
      "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
        var box = $(aData[0]);
        var id = box.attr('id').replace('check_', '');
        $(nRow).addClass('crm-entity').attr('data-entity', 'batch').attr('data-id', id).attr('data-status_id', box.attr('data-status_id'));
        $('td:eq(1)', nRow).wrapInner('<div class="crm-editable crmf-title" />');
        return nRow;
      },
      "fnDrawCallback": function(oSettings) {
        $(this).trigger('crmLoad');
        $("#toggleSelect").prop('checked', false);
        if (checkedRows.length) {
          $(checkedRows.join(',')).prop('checked', true).change();
        }
      }
    });
  }

  function editRecords(records, op) {
    records = validateOp(records, op);
    if (records.length) {
      $("#enableDisableStatusMsg").dialog({
        title: {/literal}'{ts escape="js"}Confirm Changes{/ts}'{literal},
        modal: true,
        open:function() {
          switch (op) {{/literal}
            case 'reopen':
              var msg = '<h3>{ts escape="js"}Are you sure you want to re-open:{/ts}</h3>';
              break;
            case 'delete':
              var msg = '<h3>{ts escape="js"}Are you sure you want to delete:{/ts}</h3>';
              break;
            case 'close':
              var msg = '<h3>{ts escape="js"}Are you sure you want to close:{/ts}</h3>';
              break;
            case 'export':
              var msg = '<h3>{ts escape="js"}Export:{/ts}</h3>\
              <div>\
                <label>{ts escape="js"}Format:{/ts}</label>\
                <select class="export-format">\
                  <option value="IIF">IIF</option>\
                  <option value="CSV">CSV</option>\
                </select>\
              </div>';
              break;
          {literal}}
          msg += listRecords(records, op == 'close' || op == 'export');
          $('#enableDisableStatusMsg').show().html(msg);
        },
        buttons: {
          {/literal}"{ts escape='js'}Cancel{/ts}"{literal}: function() {
            $(this).dialog("close");
          },
          {/literal}"{ts escape='js'}OK{/ts}{literal}": function() {
            saveRecords(records, op);
            $(this).dialog("close");
          }
        }
      });
    }
  }

  function listRecords(records, compareValues) {
    var txt = '<ul>',
    mismatch = false;
    for (var i in records) {
      var $tr = $('tr[data-id=' + records[i] + ']');
      txt += '<li>' + $('.crmf-title', $tr).text();
      if (compareValues) {
        $('.actual-value.crm-error', $tr).each(function() {
          mismatch = true;
          var $th = $tr.closest('table').find('th').eq($(this).closest('td').index());
          var $expected = $(this).siblings('.expected-value');
          var label = $th.text();
          var actual = $(this).text();
          var expected = $expected.text();
          txt += {/literal}'<div class="messages crm-error"><strong>' +
          label + ' {ts escape="js"}mismatch.{/ts}</strong><br />{ts escape="js"}Expected{/ts}: ' + expected + '<br />{ts escape="js"}Current Total{/ts}: ' + actual + '</div>'{literal};
        });
      }
      txt += '</li>';
    }
    txt += '</ul>';
    if (mismatch) {
      txt += {/literal}'<div class="messages status">{ts escape="js"}Click OK to override and update expected values.{/ts}</div>'{literal}
    }
    return txt;
  }

  function saveRecords(records, op) {
    var postUrl = CRM.url('civicrm/ajax/rest', 'className=CRM_Financial_Page_AJAX&fnName=assignRemove&qfKey={/literal}{$financialAJAXQFKey}{literal}');
    //post request and get response
    $.post(postUrl, {records: records, recordBAO: 'CRM_Batch_BAO_Batch', op: op},
      function(response) {
        //this is custom status set when record update success.
        if (response.status == 'record-updated-success') {
	  //Redirect CRM-18169
          window.location.href = CRM.url('civicrm/financial/financialbatches', 'reset=1&batchStatus=' + response.status_id);
          CRM.alert(listRecords(records), op == 'delete' ? {/literal}'{ts escape="js"}Deleted{/ts}' : '{ts escape="js"}Updated{/ts}'{literal}, 'success');
        }
        else {
          CRM.alert({/literal}'{ts escape="js"}An error occurred while processing your request.{/ts}', $("#batch_update option[value=" + op + "]").text() + ' {ts escape="js"}Error{/ts}'{literal}, 'error');
        }
      },
      'json').fail(serverError);
  }

  function validateOp(records, op) {
    switch (op) {
      case 'reopen':
        var notAllowed = [1, 5];
        break;
      case 'close':
        var notAllowed = [2, 5];
        break;
      case 'export':
        var notAllowed = [5];
        break;
      default:
        return records;
    }
    var len = records.length;
    var invalid = {};
    var i = 0;
    while (i < len) {
      var status = $('tr[data-id='+records[i]+']').data('status_id');
      if ($.inArray(status, notAllowed) >= 0) {
        $('#check_' + records[i] + ':checked').prop('checked', false).change();
        invalid[status] = invalid[status] || [];
        invalid[status].push(records[i]);
        records.splice(i, 1);
        --len;
      }
      else {
        i++;
      }
    }
    for (status in invalid) {
      i = invalid[status];
      var msg = (i.length == 1 ? {/literal}'{ts escape="js"}This record already has the status{/ts}' : '{ts escape="js"}The following records already have the status{/ts}'{literal}) + ' ' + $('tr[data-id='+i[0]+'] .crm-batch-status').text() + ':' + listRecords(i);
      CRM.alert(msg, {/literal}'{ts escape="js"}Cannot{/ts} '{literal} + $("#batch_update option[value=" + op + "]").text());
    }
    return records;
  }

  function serverError() {
     CRM.alert({/literal}'{ts escape="js"}No response from the server. Check your internet connection and try reloading the page.{/ts}', '{ts escape="js"}Network Error{/ts}'{literal}, 'error');
  }

  $('#Go').click(function() {
    var op = $("#batch_update").val();
    if (op == "") {
       CRM.alert({/literal}'{ts escape="js"}Please select an action from the menu.{/ts}', '{ts escape="js"}No Action Selected{/ts}'{literal});
    }
    else if (!$("input.select-row:checked").length) {
       CRM.alert({/literal}'{ts escape="js"}Please select one or more batches for this action.{/ts}', '{ts escape="js"}No Batches Selected{/ts}'{literal});
    }
    else {
      records = [];
      $("input.select-row:checked").each(function() {
        records.push($(this).attr('id').replace('check_', ''));
      });
      if (op == 'export') {
        // No need for the modal pop-up, just proceed to the next screen.
        window.location = CRM.url("civicrm/financial/batch/export", {reset: 1, id: records[0], status: 1});
        return false;
      }
      editRecords(records, op);
    }
    return false;
  });

  $('#crm-container').on('click', 'a.action-item[href="#"]', function(event) {
    event.stopImmediatePropagation();
    editRecords([$(this).closest('tr').attr('data-id')], $(this).attr('rel'));
    return false;
  });

});

</script>
{/literal}
