$(document).ready(function () {
    $.ajax({
        method: 'GET',
        url: location.href+"async/p/comments/"

    })
        .done(function(data) {
            //$(_self).prev().html(data.hearts);
            var comments = data.list;

            $.each(comments,function (index,value) {
                $('#comment-'+value.id).html(parseInt($('#comment-'+value.id).html())+1);
               });
        })


});