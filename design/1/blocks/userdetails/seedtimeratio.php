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
//=== testing concept of "share ratio"
$What_Cache = (OCELOT_TRACKER == true ? 'share_ratio_xbt_' : 'share_ratio_');
$What_Table = (OCELOT_TRACKER == true ? 'xbt_files_users' : 'snatched');
$What_String = (OCELOT_TRACKER == true ? 'fid' : 'id');
$What_User_String = (OCELOT_TRACKER == true ? 'uid' : 'userid');
$What_Expire = (OCELOT_TRACKER == true ? $INSTALLER09['expires']['share_ratio_ocelot'] : $INSTALLER09['expires']['share_ratio']);
if (($cache_share_ratio = $mc1->get_value($What_Cache.$id)) === false) {
    $cache_share_ratio = mysqli_fetch_assoc(sql_query("SELECT SUM(seedtime) AS seed_time_total, COUNT($What_String) AS total_number FROM $What_Table WHERE seedtime > '0' AND $What_User_String =" . sqlesc($user['id'])));
    $cache_share_ratio['total_number'] = (int)$cache_share_ratio['total_number'];
    $cache_share_ratio['seed_time_total'] = (int)$cache_share_ratio['seed_time_total'];
    $mc1->cache_value($What_Cache.$id, $cache_share_ratio, $What_Expire);
}
//=== get times per class
switch (true) {
    //===  member
    
case ($user['class'] == UC_USER):
    $days = 2;
    break;
    //=== Member +
    
case ($user['class'] == UC_POWER_USER):
    $days = 1.5;
    break;
    //=== Member ++
    
case ($user['class'] == UC_VIP || $user['class'] == UC_UPLOADER || $user['class'] == UC_STAFF || $user['class'] == UC_ADMINISTRATOR || $user['class'] == UC_SYSOP):
    $days = 0.5;
    break;
}
if ($cache_share_ratio['seed_time_total'] > 0 && $cache_share_ratio['total_number'] > 0) {
    $avg_time_ratio = (($cache_share_ratio['seed_time_total'] / $cache_share_ratio['total_number']) / 86400 / $days);
    $avg_time_seeding = mkprettytime($cache_share_ratio['seed_time_total'] / $cache_share_ratio['total_number']);
    if ($user["id"] == $CURUSER["id"] || $CURUSER['class'] >= UC_STAFF) {
        $HTMLOUT.= '<tr><td><strong>' . $lang['userdetails_time_ratio'] . '</strong></td>
		<td>' . (($user_stats['downloaded'] > 0 || $user_stats['uploaded'] > 2147483648) ? '<font color="' . get_ratio_color(number_format($avg_time_ratio, 3)) . '">' . number_format($avg_time_ratio, 3) . '</font>     ' . ratio_image_machine(number_format($avg_time_ratio, 3)) . '     [<font color="' . get_ratio_color(number_format($avg_time_ratio, 3)) . '"> ' . $avg_time_seeding . '</font>' . $lang['userdetails_time_ratio_per'] . '' : $lang['userdetails_inf']) . '</td></tr>';
    }
}
//==end
// End Class
// End File
