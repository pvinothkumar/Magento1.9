Review.prototype.save = function() {
    if (checkout.loadWaiting!=false) return;
    var params = Form.serialize(payment.form);
    if ($('assurant-review')) { // patch to add assurant review form data to checkout submit
        params += '&'+$('assurant-review').serialize();
        if ($('assurant-review').down('[name="_s-terms"]') && !$('assurant-review').down('[name="_s-terms"]').checked && $('assurant-termscondition').visible()) {
            swal({title: "Assurant Product Protection:",text: assurant_accept_terms_alert});
            return false;
        }
    }
    checkout.setLoadWaiting('review');
    if (this.agreementsForm) {
        params += '&'+Form.serialize(this.agreementsForm);
    }
    params.save = true;
    var request = new Ajax.Request(
        this.saveUrl,
        {
            method:'post',
            parameters:params,
            onComplete: this.onComplete,
            onSuccess: this.onSave,
            onFailure: checkout.ajaxFailure.bind(checkout)
        }
    );
}

function termsToggle(ele){
    $j('._s-agreement').toggle();
    $j('._s-checkbox').attr('checked', false);
}

function dataToggle(btnEle){
    $j('.protect-information').toggle();
    $j(btnEle).toggleClass("open");
}
