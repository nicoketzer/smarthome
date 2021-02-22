<?php
    //Include von allem
    include("/res/all.php");
    //Normaler Code 
    
?>
<html>
    <head>
        <title>Login - SmartHome</title>
        <meta charset="UTF-8">
        <meta name="description" content=""/>
        <meta name="author" content="Nico Ketzer"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes"/>
        <link href="style.css" type="text/css" rel="stylesheet"/>
        <link href="responsive.css" type="text/css" rel="stylesheet"/>
        <link href="loader.css" type="text/css" rel="stylesheet"/>
        <link href="print.css" type="text/css" rel="stylesheet"/>
        <link href="favicon.ico" type="image/x-icon" rel="shortcut icon"/>
        <script src="main.js"></script>
    </head>
    <body>
        <noscript>
            <p>Um diese Seite nutzen zu k&ouml;nnen musst du Javascript auf dieser Seite zulassen</p>
        </noscript>
        <div id="all">
            <div id="nav">
                <p id="nav_self">Login - SmartHome System</p>
            </div>
            <div id="nav_space">
            
            </div>
            <div id="content">
                <div id="login">
                    <div id="login_inner">
                        <div id="login_form">
                            <p>Benutzername:</p><br />
                            <input type="text" id="bn" maxlength="20" placeholder="Gib hier deinen Benutzernamen ein..." maxlength="10"/><br />
                            <p>Passwort:</p><br />
                            <input type="password" id="pw" maxlength="20" placeholder="Gib hier dein Passwort ein..." maxlength="20"/><br />
                            <p id="login_login_btn" onclick="start_login()">Login</p>
                            <p id="login_text_t"><a href="#" onclick="pw_forgot()">Passwort vergessen</a></a></p>
                        </div>
                    </div>
                </div>
            </div>
            <div id="footer">
                <div id="footer_nav">
                <p id="footer_nav_self">&copy; <a href="https://german-backup.de">German-Backup</a> &amp; <a href="https://firmaketzer.de">Designstudio Ketzer</a></p>
                </div>
            </div>
            <div id="error"></div>
            <div id="msg_all" onclick="close_msg()">
                <div id="msg_self">
                    <div id="msg_top">
                        <p id="msg_text_u"></p>
                        <button id="msg_btn" type="button" disabled="" onclick="close_msg()" onmouseover="setCookie('mouse_over_close','true',1)" onmouseout="setCookie('mouse_over_close','',-1)">X</button>
                    </div>
                    <p id="msg_text_text"></p>
                </div>
            </div>
            <button id="go_up" onclick="scroll_up();">Nach Oben</button>
        </div>
    </body>
</html>