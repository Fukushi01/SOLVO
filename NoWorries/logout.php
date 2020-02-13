<?php

require('function.php');

debug('..............................');
debug('ログアウトします');
debug('..............................');
debugLogStart();

debug('セッションを破棄します');
session_destroy();

debug('ログインページへ遷移します');
header('Location:login.php');