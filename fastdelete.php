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
require_once(__DIR__.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');
require_once(INCL_DIR.'user_functions.php');
require_once(INCL_DIR . 'function_memcache.php');
dbconn(false);
loggedinorreturn();
/*
fastdelete by Froggaard
*/
$lang = array_merge( load_language('global'), load_language('fastdelete') );
     
 if (!in_array($CURUSER['id'], $INSTALLER09['allowed_staff']['id'])) stderr($lang['fastdelete_error'], $lang['fastdelete_no_acc']);

 if (!isset($_GET['id']) || !is_valid_id($_GET['id']))
        stderr("{$lang['fastdelete_error']}", "{$lang['fastdelete_error_id']}");
         
    $id = (int)$_GET["id"];

    function deletetorrent($id)
{
    global $INSTALLER09, $mc1, $CURUSER, $lang;
    sql_query("DELETE peers.*, files.*, comments.*, snatched.*, thanks.*, bookmarks.*, coins.*, rating.*, thumbsup.*, torrents.* FROM torrents 
				 LEFT JOIN peers ON peers.torrent = torrents.id
				 LEFT JOIN files ON files.torrent = torrents.id
				 LEFT JOIN comments ON comments.torrent = torrents.id
				 LEFT JOIN thanks ON thanks.torrentid = torrents.id
				 LEFT JOIN bookmarks ON bookmarks.torrentid = torrents.id
				 LEFT JOIN coins ON coins.torrentid = torrents.id
				 LEFT JOIN rating ON rating.torrent = torrents.id
                                 LEFT JOIN thumbsup ON thumbsup.torrentid = torrents.id
				 LEFT JOIN snatched ON snatched.torrentid = torrents.id
				 WHERE torrents.id =" . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    unlink("{$INSTALLER09['torrent_dir']}/$id.torrent");
    $mc1->delete_value('MyPeers_' . $CURUSER['id']);
}
function deletetorrent_ocelot($id)
{
   global $INSTALLER09, $mc1, $CURUSER, $lang;
   $res = sql_query('SELECT info_hash FROM torrents WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
   $row = mysqli_fetch_assoc($res);
   sql_query("DELETE files.*, comments.*, thankyou.*, thanks.*, thumbsup.*, bookmarks.*, coins.*, rating.*, xbt_files_users.*, torrents.* FROM torrents
                                    LEFT JOIN files ON files.torrent = torrents.id
                                    LEFT JOIN comments ON comments.torrent = torrents.id
                                    LEFT JOIN thankyou ON thankyou.torid = torrents.id
                                    LEFT JOIN thanks ON thanks.torrentid = torrents.id
                                    LEFT JOIN bookmarks ON bookmarks.torrentid = torrents.id
                                    LEFT JOIN coins ON coins.torrentid = torrents.id
                                    LEFT JOIN rating ON rating.torrent = torrents.id
                                    LEFT JOIN thumbsup ON thumbsup.torrentid = torrents.id
                                    LEFT JOIN xbt_files_users ON torrents.id = xbt_files_users.fid
                                    WHERE torrents.id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    unlink("{$INSTALLER09['torrent_dir']}/$id.torrent");
    $mc1->delete_value('MyPeers_OCELOT_' . $CURUSER['id']);
    require_once(CLASS_DIR . 'tracker.class.php');
    Tracker::update_tracker('delete_torrent', array('info_hash' => rawurlencode($row['info_hash']), 'reason' => -1, 'id' => $id));
    }
     
   
    $q = mysqli_fetch_assoc(sql_query("SELECT name, owner FROM torrents WHERE id =".sqlesc($id))) or sqlerr(__FILE__, __LINE__);
    if (!$q)
    stderr('Oopps','Something went Pete Tong - Contact admin !!');
     
    $sure = (isset($_GET['sure']) && (int) $_GET['sure']);
        if (!$sure)
            stderr("{$lang['fastdelete_sure']}", "{$lang['fastdelete_sure_msg']}");
          
    if (OCELOT_TRACKER == true) {
    deletetorrent_ocelot($id);
    } else {
    deletetorrent($id);
    remove_torrent_peers($id);
    }
    $mc1->delete_value('top5_tor_');
    $mc1->delete_value('last5_tor_');
    $mc1->delete_value('scroll_tor_');
    $mc1->delete_value('torrent_details_' . $id);
    $mc1->delete_value('torrent_details_text' . $id);
    if ($CURUSER['id'] != $q['owner'])
    {
    $msg = sqlesc("{$lang['fastdelete_msg_first']} [b]{$q['name']}[/b] {$lang['fastdelete_msg_last']} {$CURUSER['username']}");
    sql_query("INSERT INTO messages (sender, receiver, added, msg) VALUES (0, ".sqlesc($q['owner']).", ".TIME_NOW.", {$msg})") or sqlerr(__FILE__, __LINE__);
    }
    write_log("{$lang['fastdelete_log_first']} {$q['name']} {$lang['fastdelete_log_last']} {$CURUSER['username']}");
    if ($INSTALLER09['seedbonus_on'] == 1) {
    //===remove karma
    sql_query("UPDATE users SET seedbonus = seedbonus-".sqlesc($INSTALLER09['bonus_per_delete'])." WHERE id = " . sqlesc($q["owner"])) or sqlerr(__FILE__, __LINE__);
    $update['seedbonus'] = ($CURUSER['seedbonus'] - $INSTALLER09['bonus_per_delete']);
    $mc1->begin_transaction('userstats_' . $q["owner"]);
    $mc1->update_row(false, array(
        'seedbonus' => $update['seedbonus']
    ));
    $mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
    $mc1->begin_transaction('user_stats_' . $q["owner"]);
    $mc1->update_row(false, array(
        'seedbonus' => $update['seedbonus']
    ));
    $mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
    //===end
}

    if (isset($_GET["returnto"]))
        $ret = "<a href='".htmlsafechars($_GET["returnto"])."'>{$lang['fastdelete_returnto']}</a>";
    else
        $ret = "<a href='{$INSTALLER09['baseurl']}/index.php'>{$lang['fastdelete_index']}</a>";
     
    $HTMLOUT = '';
    $HTMLOUT .= "<h2>{$lang['fastdelete_deleted']}</h2>
    <p>{$ret}</p>";
     
echo stdhead("{$lang['fastdelete_head']}") . $HTMLOUT . stdfoot();
?>
