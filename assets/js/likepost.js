$(document).ready(function () {
    if($('.like-post').length>0){
        $('.like-post').on('click', function(e) {
            e.preventDefault();
            var _self = this;
            var $link = $(e.currentTarget);
            $link.toggleClass('ion-md-heart').toggleClass('ion-md-heart-empty');

            $.ajax({
                method: 'GET',
                url: $link.attr('href')

            })
                .done(function(data) {
                    $(_self).prev().html(data.hearts);
                })

        });
    }

    $.ajax({
        method: 'GET',
        url: location.href+"async/p/like/"

    })
    .done(function(data) {
        //$(_self).prev().html(data.hearts);
        var like = data.likes;
        $.each(like,function (index,value) {
            $('#'+value.id).html(value.total);
            if(value.like){
                $('#'+value.id).next().toggleClass('ion-md-heart-empty').toggleClass('ion-md-heart');
            }
        });
    })


});

document.addEventListener("DOMContentLoaded", function(event) {
    console.log("DOM fully loaded and parsed");
});