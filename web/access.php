<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 31/05/2013
 * Time: 12:08
 * To change this template use File | Settings | File Templates.
 */

if (isset($_POST['password']) && !empty($_POST['password']) && $_POST['password'] == '@dentify$2013') {
    echo 'douze';
    $fp = fopen(__DIR__.'/../ip.ini', 'a');
    fwrite($fp, 'ip[] = '.$_SERVER['REMOTE_ADDR']."\n");
    echo $_SERVER['REMOTE_ADDR'];
    fclose($fp);

    header('Location: http://dev.adentify.com/');
}

?>
<form method="POST">
    <input type="password" name="password">
    <input type="submit">
</form>