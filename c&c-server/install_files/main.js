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