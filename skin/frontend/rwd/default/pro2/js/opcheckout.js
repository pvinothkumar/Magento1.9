Review.prototype.nextStep = function(transport){
    if (transport && transport.responseText) {
        try{
            response = eval('(' + transport.responseText + ')');
        }
        catch (e) {
            response = {};
        }
        if (response.redirect) {
            this.isSuccess = true;
            location.href = response.redirect;
            return;
        }
        if (response.success) {
            this.isSuccess = true;
            window.location=this.successUrl;
        }
        else{
            var msg = response.error_messages;
            if (typeof(msg)=='object') {
                msg = msg.join("\n");
            }
            if (msg) {
                if (msg == assurant_limit_state_msg || msg == assurant_invalid_token ) {
                    swal({title: "Assurant Product Protection:",text: msg}, function() { window.location = assurant_clean_url; });
                }
                else {
                    alert(msg);
                }
            }
        }

        if (response.update_section) {
            $('checkout-'+response.update_section.name+'-load').update(response.update_section.html);
        }

        if (response.goto_section) {
            checkout.gotoSection(response.goto_section, true);
        }
    }
};
