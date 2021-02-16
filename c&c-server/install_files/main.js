var server = "[irgendwas]";
function start_install(){
    f_close_msg();
    var fillout_form = "";
    show_msg("Daten erforderlich:",fillout_form,false);
}
function test_mysql(){
    //Stage 2 angekommen
    f_close_msg();
    show_msg("Info","Es wird nun versucht eine Mysqli-Verbindung zum Server aufzubauen.<br /><br /> <div id='loader'></div>",false);
    $.post().fail();
}
function get_all_data(){
    //Stage 1
    f_close_msg();
    show_msg("Laden...","<div id='loader'></div>",false);
    //Hier müssten nun alle Daten ankommen
    var mysqli_bn = readid("m_bn");
    var mysqli_pw = readid("m_pw");
    var mysqli_server = readid("m_server");
    var mysqli_port = readid("m_port");
    if(is_set([mysqli_bn,mysqli_pw,mysqli_server,mysqli_port])){
        //Alle Daten gefunden
        f_close_msg();
        show_msg("Daten werden übertragen...","<div id='loader'></div>",false);
        //Übertragen an den Server
        var para = "[irgentwas]";
        $.post(server,para,function(res){
            if(res == "ok"){
                f_close_msg();
                show_msg("Fertig","Stage 1 Erfolgreich. Bitte warten...",false);
                setTimeout(function(){
                    test_mysql();    
                },1000);
            }else{
                //Irgent ein Fehler ist aufgetretten
                f_close_msg();
                show_msg("Fehler","Der Server hat einen Fehler zurückgegeben:<br /><br />"+res,false);
            }    
        },"text").fail(function(a,b,c){
            console.log(a);
            console.log(b);
            console.log(c);
            f_close_msg();
            show_msg("Fehler","Beim übertragen der Daten ist ein Fehler aufgetreten(siehe Konsole)",false);
        });
    }else{
        //Es gehen Daten ab
        f_close_msg();
        show_msg("Fehler","Es wurden nicht alle benötigten Daten angegeben. Bitte fülle gib alle Daten an",false);
        setTimeout(function(){
            f_close_msg();
            start_install();    
        },1000);
    }
}

//Nachrichten-Funktionen
function show_msg(string1,string2,dismiss){
    if(getCookie("msg_shown") == "" || getCookie("msg_shown") == "false"){
        //GET EL
        var text_u = _("msg_text_u");
        var text_text = _("msg_text_text");
        var button_dism = _("msg_btn");
        //RESET ALL
        text_u.innerHTML = text_text.innerHTML = "";
        button_dism.disabled = null;
        //SET NEW
        text_u.innerHTML = string1;
        text_text.innerHTML = string2;
        button_dism.disabled = !dismiss;
        //Einblenden
        _("msg_all").style.display = "block";
        document.getElementsByTagName("body")[0].style.overflow = "hidden";
        setCookie("msg_shown","true",1);
    }else{
        //WAIT TILL PREV MSG IS CLOSED
        setTimeout(function(){
            show_msg(string1,string2,dismiss);
        },500);
    }
}
function close_msg(){
    if(side_on){
        var state = _("msg_btn").disabled;
        if(!state){
            var a = getCookie("mouse_over_close");
            var b = $("#msg_self:hover").length;
            if(!b || a == "true"){
                document.getElementsByTagName("body")[0].style.overflow = "";
                _("msg_all").style.display = "none";
                setCookie("msg_shown","false",1);
            }
        }
    }
}
function f_close_msg(){
    _("msg_btn").disabled = false;
    setCookie("mouse_over_close","true",1);
    close_msg();
    setCookie("mouse_over_close","",-1);
}
//Cookie-Funktionen
function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/;domain=german-backup.de;SameSite=Strict;Secure";
}
function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}