<?php namespace Twlan; ?>
/*5291315e7767ed9e683d31e7a766c168*/
var Register=
{
    checkInput:function(type,value)
    {
        $.ajax(
        {
                url:'register.php?action=checkInput',data:
                {
                    type:type,value:value
                }
            ,dataType:'json',type:'POST',success:function(data)
            {
                $('#'+type+'_error').css('display','none').text('');
                $('#password_info').css('display','none').text('');
                $('#password_errors').css('display','none').text('');
                if(data.status=='ERROR')
                {
                    var newline = '';
                    $(data.message).each(function(index,item)
                        {
                            $('#'+type+'_error').css('display',"").append(newline).append(item);
                            newline = '<br />';
                        })
                }
                else if(data.status=='INFO')
                {
                    $('#password_info').css('display','none');
                    if($('#password_confirm_error').text().length==0&&$('#password_errors').text().length==0)
                    	$('#password_info').css('display','').text(data.message)
                }
            }
        })
    }
    ,checkInputEqual:function(type,inputConfirm)
    {
        diff=(inputConfirm!=$('#'+type).val());
        $('#password_errors, #password_confirm_error').css('display','none');
        switch(type)
        {
        	case'password':
        		$('#password_confirm_error').css('display','').text(diff?'Du musst das Passwort zweimal exakt gleich eingeben.':'');
        		break;
        	case'name':
        		$('#password_errors').css('display','').text(diff?'':'Das Passwort darf nicht gleich deinem Benutzernamen sein!');
        		break;
        }
    }
    ,checkPasswordConfirm:function()
    {
        $('#password_error, #password_confirm_error').hide();
        var pw=$('#password').val(),pwRep=$('#password_repeat').val();
        if(pw.length < 4)
        {
            $('#password_error').text('Das Passwort muss mindestens 4 Zeichen lang sein!').show();
        }
        else if(pw.length > 50)
        {
            $('#password_error').text('Das Passwort darf höchstens 50 Zeichen lang sein!').show();
        }
        if(pw != pwRep)
        {
            $('#password_confirm_error').text('Du musst das Passwort zweimal exakt gleich eingeben.').show();
        }
        return true;
    }
}
