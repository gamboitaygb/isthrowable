var forms = {
    init:function () {
        this.init();
    },
    createForm:function (_e) {
        CKEDITOR.replace( $('#'+_e).attr('name'),
            {
                extraPlugins: 'easyimage',
                cloudServices_tokenUrl: 'https://isthrowable.com/',
                cloudServices_uploadUrl: 'https://isthrowable.com/build/upload/'
            });
    }
}


$(document).ready(function () {

    $('.card__share > a').on('click', function(e){
        e.preventDefault() // prevent default action - hash doesn't appear in url
        $(this).parent().find( 'div' ).toggleClass( 'card__social--active' );
        $(this).toggleClass('share-expanded');
    });


   if($("#post_content").length>0){
    forms.createForm('post_content');
   }

   if($("#comments_content").length>0){
       forms.createForm('comments_content');
   }

    $('form button[type="submit"]' ).click(function() {
        $("form" ).submit();
    });
})



