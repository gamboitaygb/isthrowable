var forms = {
    createForm:function (_e) {
        CKEDITOR.replace( $('#'+_e).attr('name') );
    }
}

$(document).ready(function () {

    if($("#question_content").length>0){
        forms.createForm('question_content');
    }

    if($("#comments_content").length>0){
        forms.createForm('comments_content');
    }

    $('form button[type="submit"]' ).click(function() {
        $("form").submit();
    });
})