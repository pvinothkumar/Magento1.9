jQuery.noConflict();

wizard_timer = null;
showprocess = function() {
    new Ajax.Request('/media/assurant_processing.log?_=' + new Date().getTime(), {
        method: 'get',
        onSuccess: function(content){
            $('wizard_process_output').update(content.responseText);
        }
    });
}
step = function(url) {
    postData = $('wizard_action_form') ? $('wizard_action_form').serialize() : null;
    new Ajax.Request(url, {
        method: 'post',
        parameters: postData,
        onLoading: wizard_timer = setInterval('showprocess()', 100),
        onSuccess: function(content){
            clearInterval(wizard_timer);
            if (content.responseText) {
                response = content.responseText.evalJSON();
                if (response.error) {
                    swal({title: "Assurant Product Protection:",text: response.output});
                }
                else if (response.output == 'finished') {
                    Dialog.closeInfo();
                }
                else {
                    $('wizard_step_content').update(response.output);
                }
            }
            else {
                swal({title: "Assurant Product Protection:",text: 'Unexpected error happened. please refresh and try again.'});
            }
        }
    });
}
