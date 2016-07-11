$j(document).ready(function(){
    function addToCartAjax(flag){
        var addToCartButtonAction = $j('#product_addtocart_form').attr('action');
        var url = addToCartButtonAction.replace("checkout/cart/add","pro2/submit/add"); 

        var data = $j('#product_addtocart_form').serialize();

        data += '&add_coverage='+flag+'&isAjax=1'; 
        $j('.cssload-container').show();
        try {
            if (flag && $('assurant-products-field').value.length == 0) {
                swal({title: "Assurant Product Protection:",text: "Please select a product protection plan and then click Add Coverage."});
                $j('.cssload-container').hide();
                return false;
            }

            $j.ajax({
                url: url,
                dataType: 'json',
                type : 'post',
                data: data,
                success: function(data){
                    $j('.cssload-container').hide();
                    if(data.status == 'success' && data.assurant == 'true') {
                        location.href = data.redirect;
                    } else {
                        if (jQuery('#benefits-popup').size() > 0) {
                            $j('p.popup-cart-message').html(data.message);
                            jQuery.fancybox.open('#benefits-popup');
                        }
                        else {
                            location.href = data.redirect;
                        }
                    }
                }
            });
        } catch (e) {
        }
    }

    var ajaxLoaderContent = '<div style="display:none;" class="cssload-container"><div class="cssload-zenith"></div></div>';
    $j(ajaxLoaderContent).insertAfter('.add-to-cart-wrapper button.btn-cart');

    $j('.add-to-cart-wrapper button.btn-cart').removeAttr("onclick");

    /* add to cart */
    $j('.add-to-cart-wrapper button.btn-cart').click(function(){
        addToCartAjax(0);
    });

    /* add coverage */
    $j('a#protect-add-coverage').click(function(){
        addToCartAjax(1);
    });

    /* add coverage selected */
    $j('a#protect-add-coverage-selected').click(function(){
        addToCartAjax(1);
    });

    $j('.plan-details-fancybox').fancybox({
        'transitionIn':'elastic',
        'transitionOut':'elastic',
        'speedIn':100,
        'speedOut':100,
        afterClose : function(){
            $j('input#assurant-products-field').val('');
        },
        beforeLoad : function(){
            $j("div#protect-product-radio").find('input[type=radio]:first').trigger('click');
        }
    });

});
