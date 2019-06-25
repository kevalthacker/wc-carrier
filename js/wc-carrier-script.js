var active_instance = '';
var carrier_id_val = '';
var cid = '';
jQuery(document).ready(function($) {
    $("table.wc-shipping-zone-methods a.wc-shipping-zone-method-settings").live('click', function() {
        var id = $(this).parents("tr").attr("data-id");
        $(this).parents("forms").attr("onSubmit", "callme()");
        var data = flat_rate_id.split(',');
        var cdata = carrier_ids.split(',');
        $.each(data, function(index, value) {
            if (value == id) {
                active_instance = value;
                carrier_id_val = cdata[index];
                $(".wc-modal-shipping-method-settings table tbody").append('<tr valign="top"><th scope="row" class="titledesc"><label for="woocommerce_flat_rate_cost">Carrier ID</label></th><td class="forminp"><fieldset><legend class="screen-reader-text"><span>Carrier ID</span></legend><input class="input-text regular-input " type="text" name="woocommerce_flat_rate_cid" id="woocommerce_flat_rate_cid" style="" value="' + carrier_id_val + '" placeholder="Enter Carrier ID"></fieldset></td></tr>');
            }
        });
    });
    $("#woocommerce_flat_rate_cid").live('change', function() {
        cid = $(this).val();
    });
    $("#btn-ok").live('click', function() {
        $.ajax({
            url: wc_carrier.ajax_url,
            type: 'post',
            data: {
                action: 'wc_carrier_shipping_methods_carrier_id',
                instance_id: active_instance,
                cid: cid
            },
            success: function(response) {
                //console.log( response );
                carrier_ids = response;
            }
        });
        return false;
    });
});