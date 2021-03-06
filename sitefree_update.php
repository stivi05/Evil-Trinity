<?php
/**
 |--------------------------------------------------------------------------|
 |   https://github.com/3evils/                                             |
 |--------------------------------------------------------------------------|
 |   Licence Info: WTFPL                                                    |
 |--------------------------------------------------------------------------|
 |   Copyright (C) 2020 Evil-Trinity                                        |
 |--------------------------------------------------------------------------|
 |   A bittorrent tracker source based on an unreleased U-232               |
 |--------------------------------------------------------------------------|
 |   Project Leaders: AntiMidas,  Seeder                                    |
 |--------------------------------------------------------------------------|
                 _   _   _   _     _   _   _   _   _   _   _
                / \ / \ / \ / \   / \ / \ / \ / \ / \ / \ / \
               | E | v | i | l )-| T | r | i | n | i | t | y )
                \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/ \_/
*/
function docleanup($data)
{
    global $INSTALLER09, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(1);
    $oq = sql_query("SELECT * FROM `events` WHERE `oupdated` = 0 AND `endtime` < " . TIME_NOW) or sqlerr(__FILE__, __LINE__);
    while ($orow = mysqli_fetch_assoc($oq)) {
        if ($orow['freeleechEnabled'] == 1) {
            replaceInFile('^sitefree\s*= true', "sitefree\t\t\t= false", OCELOT_CONF);
        } elseif ($orow['duploadEnabled'] == 1) {
            replaceInFile('^sitedouble\s*= true', "sitedouble\t\t\t= false", OCELOT_CONF);
        } elseif ($orow['hdownEnabled'] == 1) {
            replaceInFile('^sitehalf\s*= true', "sitehalf\t\t\t= false", OCELOT_CONF);
        }
        exec('sudo pkill -1 ocelot');
        sql_query("UPDATE `events` SET `oupdated` = 1 WHERE `id` = " . sqlesc($orow['id'])) or sqlerr(__FILE__, __LINE__);
    }
    if ($queries > 0) write_log("Site Events Clean -------------------- Site Events Complete using $queries queries--------------------");
    if (false !== mysqli_affected_rows($GLOBALS["___mysqli_ston"])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS["___mysqli_ston"]) . " items deleted/updated";
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
?>
