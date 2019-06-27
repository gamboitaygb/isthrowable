const $ = require('jquery');
require('bootstrap');
require('ionicons/dist/css/ionicons.min.css');
require('bootstrap/dist/css/bootstrap.min.css');
require('datatables.net')
require('../css/admin.css');
const swal = require('sweetalert');

    $(document).ready(function () {
        var admin={
            init:function(){
                this.isLogged();
                this.loadInfo();
            },
            isLogged:function () {
                $('.open-nav').on('click',function () {
                    $('#mySidenav').addClass('show-nav');
                    $("#main").css({'margin-left':' 250px'});
                });

                $('.close-nav').on('click',function () {
                    $('#mySidenav').removeClass('show-nav');
                    $("#main").css({'margin-left':'0px'})
                });
            },
            deluser:function (_el) {
                $.ajax({
                    method: 'POST',
                    url: location.protocol+"//"+location.hostname+"/es/async/deluser/",
                    data:{id:$(_el).data('id')},

                })
                    .done(function(data) {
                        $(_el).parents('tr').toggle().remove();
                    })

            },
            delQuestion:function (_el) {
                $.ajax({
                    method: 'POST',
                    url: location.protocol+"//"+location.hostname+"/es/async/delquestion/",
                    data:{id:$(_el).data('id')},

                })
                    .done(function(data) {
                        $(_el).parents('tr').toggle().remove();
                    })

            },
            active:function (type,id,action) {
                var act = action == 1 ? 0 : 1;

                $.ajax({
                    method: 'POST',
                    url: location.protocol+"//"+location.hostname+"/es/async/active/"+type+"-"+id+"/"+act+"/",
                })
                    .done(function(data) {

                        var _el=$("#"+type+"-"+id);
                        _el.removeClass('yes no').addClass(data.class);
                        _el.attr('data-action',data.action);
                        _el.html(data.text);
                    })
            },
            loadInfo:function () {
                $.ajax({
                    method: 'GET',
                    url: location.protocol+"//"+location.hostname+"/es/async/load/info/",
                })
                    .done(function(data) {

                        $('.show-user h3').html(data.user);
                        $('.show-post h3').html(data.post);
                        $('.show-question h3').html(data.question);
                        $('.show-comment h3').html(data.comments);
                    })
            }

        }

        admin.init();

        var acc = document.getElementsByClassName("accordion");
        var i;

        for (i = 0; i < acc.length; i++) {
            acc[i].addEventListener("click", function() {
                this.classList.toggle("active");
                var panel = this.nextElementSibling;
                if (panel.style.maxHeight){
                    panel.style.maxHeight = null;
                } else {
                    panel.style.maxHeight = panel.scrollHeight + "px";
                }
            });
        }


        //event actions
        /*Delete user*/
        $('.del-user').on('click',function (ev) {
            ev.preventDefault();
            admin.deluser(this);
        });
        //delete question
        $('.del-question').on('click',function (ev) {
            ev.preventDefault();
            admin.delQuestion(this);
        });

        $('.is-enabled').on('click',function (ev) {
            ev.preventDefault();
            var type = $(this).data('type');
            var id = $(this).data('id');
            var action = $(this).attr('data-action');

            admin.active(type,id,action);
        })

    });

    $('#table_id').DataTable(
        {
            "order": [[ 3, "desc" ]],
            "pageLength": 10,
            "lengthChange": false,
            "language": {
                "info":           "",
                "search":"Buscar:",
                "paginate": {
                    "first":      "Primera",
                    "last":       "Ultima",
                    "next":       "Siguiente",
                    "previous":   "Anterior"
                },
            }
        }
    );

