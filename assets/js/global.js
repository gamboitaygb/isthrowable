//const Person = require('./Person');
const $ = require('jquery');
require('bootstrap');
//require('ionicons/dist/ionicons.js');
require('ionicons/dist/css/ionicons.min.css');
require('bootstrap/dist/css/bootstrap.min.css');
require('../css/global.css');
const swal = require('sweetalert');


var isThrowable={
    init:function(){
        this.show();
        this.paginate();
        //this.hidde();
       // this.seach();
    },
    check:function () {
        if($('#is-home-logged').length>0){
            return true;
        }
        return false;
    },
    show:function () {
        if(this.check()){
            $("a#is-home-logged").on('click',function (event) {
                event.preventDefault();
                if($('#is-configuration').hasClass('is-hidden')){
                    $('#is-configuration').fadeToggle();
                }
            });
        }
    },
    hidde:function () {
        if(this.check()){
            $(document).on('click',function (event) {
                event.stopPropagation();
                event.preventDefault();
                if(!$('#is-configuration').hasClass('is-hidden')){
                    console.log(event.target.id);
                    if(event.target.id!='menu'){
                        $('#is-configuration').fadeToggle().addClass('is-hidden');
                    }
                }
            });
        }
    },
    seach:function () {
            $.ajaxSetup(
                {
                    cache: false
                });

            $('#search').keyup(function(){
                $('#result').html('');
                $('#state').val('');
                var searchField = $('#search').val();
                var expression = new RegExp(searchField, "i");

                $.getJSON('search.json', function(data) {
                    $.each(data, function(key, value){
                        if (value.name.search(expression) != -1 || value.location.search(expression) != -1)
                        {
                            $('#result').append('<li class="list-group-item link-class"><img src="'+value.image+'" height="40" width="40" class="img-thumbnail" /> '+value.name+' | <span class="text-muted">'+value.location+'</span></li>');
                        }
                    });
                });
            });

            $('#result').on('click', 'li', function() {
                var click_text = $(this).text().split('|');
                $('#search').val($.trim(click_text[0]));
                $("#result").html('');
            });
    },
    paginate:function () {

           if($('.paginate').length>0){
               console.clear();
                   var bulletClasses = {
                       elements: {
                           container: ".pindicator",
                           bullet: ".bullet",
                       },
                       helpers: {
                           past: "past",
                           current: "current",
                           next: "next",
                           future: "future",
                       }
                   };

                   var bulletEls;
               initBullets();

                   function initBullets() {
                       bulletEls = Array.prototype.slice.call(
                           document.body.querySelectorAll(bulletClasses.elements.bullet)
                       );


                       bulletEls.forEach(function(el) {
                           el.addEventListener("mousedown", function(event) {
                               gotoPage(bulletEls.indexOf(this) + 1);
                           });
                           el.addEventListener("touchstart", function(event) {
                               event.preventDefault();
                               gotoPage(bulletEls.indexOf(this) + 1);
                           });
                       });
                   }

                   function gotoPage(pageNum) {

                       bulletEls.forEach(function(e) {
                           e.classList.remove.apply(e.classList,
                               Object.keys(bulletClasses.helpers).map(function(e){
                                   return bulletClasses.helpers[e];
                               })
                           )
                       });

                       bulletEls[pageNum - 1].classList.add(bulletClasses.helpers.current);
                       if(pageNum > 1) {
                           for(var i = 0; i < pageNum; i++) {
                               bulletEls[i].classList.add(bulletClasses.helpers.past);
                           }
                       }
                       if(pageNum < bulletEls.length) {
                           bulletEls[pageNum].classList.add(bulletClasses.helpers.next);
                           for(var i = bulletEls.length - 1; i >= pageNum; i--) {
                               bulletEls[i].classList.add(bulletClasses.helpers.future);
                           }
                       }

                       hideShowPage(pageNum);
                   }

                   function hideShowPage(pageNum) {
                       var num = 10
                       var until = (pageNum * num);
                       var from = until-num;

                       $('ul.is-pagination li.is-show-p').removeClass('is-show-p').addClass('is-hide-p');

                       while (from<=until){
                           from++;
                           $('.page-'+from).addClass('is-show-p');
                       }

                   }

           }


    },
    lazyImg:function () {
        var lazyImages = [].slice.call(document.querySelectorAll("img.img-lazy"));

        if ("IntersectionObserver" in window
            && "IntersectionObserverEntry" in window
            && "intersectionRatio" in window.IntersectionObserverEntry.prototype) {

            let lazyImageObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        let lazyImage = entry.target;
                        lazyImage.src = lazyImage.dataset.src;
                        lazyImage.srcset = lazyImage.dataset.srcset;
                        lazyImage.classList.remove("img-lazy");
                        lazyImageObserver.unobserve(lazyImage);
                    }
                });
            });

            lazyImages.forEach(function(lazyImage) {
                lazyImageObserver.observe(lazyImage);
            });
        } else {
            // LLamada a otros metodos en caso que quiera hacer algo
        }

    }

};

(function () {
  $(document).ready(function () {
      isThrowable.init();
      isThrowable.lazyImg();
     /* swal(
          'Good job!',
          'You clicked the button!',
          'success'
      )*/

      if ($('#back-to-top').length) {
          var scrollTrigger = 100, // px
              backToTop = function () {
                  var scrollTop = $(window).scrollTop();
                  if (scrollTop > scrollTrigger) {
                      $('#back-to-top').addClass('show');
                  } else {
                      $('#back-to-top').removeClass('show');
                  }
              };
          $(window).on('scroll', function () {
              backToTop();
          });
          $('#back-to-top').on('click', function (e) {
              e.preventDefault();
              $('html,body').animate({
                  scrollTop: 0
              }, 700);
          });
      }

  });
})();
