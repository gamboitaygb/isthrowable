$(document).ready(function () {
    const swal = require('sweetalert');
    var login = {
        init:function () {
            this.isRegister();
            this.recoveryMail();
        },
        isRegister:function () {
            $('#login_user').on('focusout',function (ev) {
                ev.preventDefault();
                 /*swal(
                      'Good job!',
                      window.location.protocol+"//"+window.location.hostname+"/"+$('html').attr('lang')+"/",
                      'success'
                  );*/
                 login.ajaxCheck(this);

            });
        },
        recoveryMail:function () {
            $('#send-pass').on('click',function () {

                login.ajaxCheck('#email-pwd');

                setTimeout(function () {
                    if($("#email-pwd").data('response')==true){
                        $('#form-recovery').submit();
                    }
                },1000);

            });
        },
        ajaxCheck:function (element) {
            $.ajax({
                method: "GET",
                url: window.location.protocol+"//"+window.location.hostname+"/"+$('html').attr('lang')+"/async/check_user/"+$(element).val(),
                dataType: 'json',
                success: function(data)
                {
                    if(data.hasOwnProperty("response") && data.response === "success")
                    {
                        if(data.hasOwnProperty("id"))
                        {

                            var id = JSON.parse(data.id);
                            $(element).removeClass();
                            if(id > 0)
                            {
                                $(element).addClass('form-control border border-success');
                                $(element).attr("data-response",true);
                            }else{
                                $(element).addClass('form-control border border-danger');
                                $(element).attr("data-response",false);
                            }


                        }
                        else
                        {
                            $(element).removeClass();
                            $(element).addClass('border border-danger');
                            $(element).attr("data-response",false);
                        }
                    }
                },
                error: function(jqXHR, exception)
                {
                    if(jqXHR.status === 405)
                    {
                        console.error("metodo no permitido!");
                    }
                }
            });

        }
    }
    login.init();
});