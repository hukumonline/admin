jQuery(document).ready(function()
{

    // Mini Login
    jQuery('#form-mini-login input[name="login[username]"]').focus(function(){

    if(jQuery(this).val() == 'Email')
    {
    jQuery(this).val('');
    }

    });

    jQuery('#form-mini-login input[name="login[username]"]').blur(function(){

    if(jQuery(this).val() == '')
    {
    jQuery(this).val('Email');
    }

    });

    jQuery('#form-mini-login input[name="password"]').focus(function(){
    if(jQuery(this).val() == 'Password')
    {
    jQuery(this).css('display', 'none');
    jQuery('#form-mini-login input[name="login[password]"]').css('display', '');
    jQuery('#form-mini-login input[name="login[password]"]').focus();
    }
    });

    jQuery('#form-mini-login input[name="login[password]"]').blur(function(){

    if(jQuery(this).val() == '')
    {
    jQuery(this).css('display', 'none');
    jQuery('#form-mini-login input[name="password"]').val('Password');
    jQuery('#form-mini-login input[name="password"]').css('display', '');
    }

    });

    // Tabs
    jQuery('#tabs').tabs();
    jQuery("#featured").tabs({fx:{opacity: "toggle"}}).tabs("rotate", 5000, true);
    jQuery("#featured").hover(
        function() {
            jQuery("#featured").tabs("rotate",0,true);
        },
        function() {
            jQuery("#featured").tabs("rotate",5000,true);
        }
    );
});

var gid = function(x) { return document.getElementById(x); };
function focus_text(msg, id){
    if(gid(id).value == msg){
        gid(id).value="";
    }
}
function blur_text(msg, id){
    if(gid(id).value.length < 1){
        gid(id).value=msg;
    }
}
