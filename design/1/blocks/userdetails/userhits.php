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
//==profile hits
if ($CURUSER["id"] == $user["id"] || $user['paranoia'] < 2) $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_pviews']}</td><td align='left'><a href='staffpanel.php?tool=user_hits&amp;id=$id'>" . number_format((int)$user["hits"]) . "</a></td></tr>\n";
//==end
// End Class
// End File
