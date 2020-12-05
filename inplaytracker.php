<?php

if(!defined("IN_MYBB"))
{
        die("Direct initialization of this file is not allowed.");
}

$plugins->add_hook("newthread_start", "inplaytracker_newthread");
$plugins->add_hook("newthread_do_newthread_end", "inplaytracker_do_newthread");
$plugins->add_hook("editpost_end", "inplaytracker_editpost");
$plugins->add_hook("editpost_do_editpost_end", "inplaytracker_do_editpost");
$plugins->add_hook("newreply_do_newreply_end", "inplaytracker_do_newreply");
$plugins->add_hook("forumdisplay_thread_end", "inplaytracker_forumdisplay");
$plugins->add_hook("misc_start", "inplaytracker_misc");
if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
        $plugins->add_hook("global_start", "inplaytracker_alerts");
}
$plugins->add_hook("global_intermediate", "inplaytracker_global");
$plugins->add_hook("member_profile_end", "inplaytracker_profile");
$plugins->add_hook("showthread_start", "inplaytracker_showthread");

$plugins->add_hook("admin_tools_menu", "inplaytracker_tools_menu");
$plugins->add_hook("admin_tools_action_handler", "inplaytracker_tools_action_handler");


function inplaytracker_info()
{
        return array(
                "name"                        => "Inplaytracker",
                "description"        => "Eintragen von Postpartnern in eine Szene, PN-Benachrichtigung bei neuen Szenen/Posts & Komplette Übersicht offener Szenen.",
                "website"                => "https://github.com/its-sparks-fly",
                "author"                => "sparks fly",
                "authorsite"        => "https://github.com/its-sparks-fly",
                "version"                => "2.0",
                "compatibility" => "*"
        );
}

function inplaytracker_install()
{
  global $db, $mybb;

                if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

                if (!$alertTypeManager) {
                        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
                }

                $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
                $alertType->setCode('inplaytracker_newthread'); // The codename for your alert type. Can be any unique string.
                $alertType->setEnabled(true);
                $alertType->setCanBeUserDisabled(true);

                $alertTypeManager->add($alertType);

                $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
                $alertType->setCode('inplaytracker_newreply'); // The codename for your alert type. Can be any unique string.
                $alertType->setEnabled(true);
                $alertType->setCanBeUserDisabled(true);

                $alertTypeManager->add($alertType);
        }

  // Tabellen erstellen
  $db->query("ALTER TABLE `".TABLE_PREFIX."threads` ADD `partners` VARCHAR(1155) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `attachmentcount`;");
  $db->query("ALTER TABLE `".TABLE_PREFIX."posts` ADD `partners` VARCHAR(1155) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `visible`;");
  $db->query("ALTER TABLE `".TABLE_PREFIX."threads` ADD `ipdate` VARCHAR(155) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `partners`;");
  $db->query("ALTER TABLE `".TABLE_PREFIX."posts` ADD `ipdate` VARCHAR(155) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `partners`;");
        $db->query("ALTER TABLE `".TABLE_PREFIX."threads` ADD `iport` VARCHAR(155) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `ipdate`;");
  $db->query("ALTER TABLE `".TABLE_PREFIX."posts` ADD `iport` VARCHAR(155) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `ipdate`;");
        $db->query("ALTER TABLE `".TABLE_PREFIX."threads` ADD `ipdaytime` VARCHAR(155) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `iport`;");
  $db->query("ALTER TABLE `".TABLE_PREFIX."posts` ADD `ipdaytime` VARCHAR(155) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `iport`;");
        $db->query("ALTER TABLE `".TABLE_PREFIX."threads` ADD `openscene` int(11) NOT NULL DEFAULT '-1';");
        $db->query("ALTER TABLE `".TABLE_PREFIX."posts` ADD `openscene` int(11) NOT NULL DEFAULT '-1';");
        $db->query("ALTER TABLE `".TABLE_PREFIX."threads` ADD `postorder` int(11) NOT NULL DEFAULT '1';");
        $db->query("ALTER TABLE `".TABLE_PREFIX."posts` ADD `postorder` int(11) NOT NULL DEFAULT '1';");

  // Einstellungen
  $setting_group = array(
          'name' => 'inplaytracker',
    'title' => 'Inplaytracker',
    'description' => 'Einstellungen für das Inplaytracker-Plugin',
    'disporder' => 5, // The order your setting group will display
    'isdefault' => 0
  );

  $gid = $db->insert_query("settinggroups", $setting_group);

  $setting_array =
                array(
            'inplaytracker_forum' => array(
            'title' => 'Inplay-Kategorie',
            'description' => 'Gib die ID deiner Inplay-Kategorie an.',
            'optionscode' => 'text',
            'value' => '998', // Default
            'disporder' => 1
          ),
            'inplaytracker_archiv' => array(
      'title' => 'Archiv-Forum',
      'description' => 'Gib die ID deines Inplay-Archivs an.',
      'optionscode' => 'text',
      'value' => '999', // Default
      'disporder' => 2
    ),
                        'inplaytracker_location' => array(
      'title' => 'Ort-Feld hinzufügen?',
      'description' => 'Sollen User ein Feld ausfüllen können, in dem siie zusätzlich den Spielort angeben können?',
      'optionscode' => 'yesno',
      'value' => 1,
      'disporder' => 3
    ),
                        'inplaytracker_daytime' => array(
                        'title' => 'Tageszeit-Feld hinzufügen?',
                        'description' => 'Sollen User ein Feld ausfüllen können, in dem siie zusätzlich die Tageszeit angeben können?',
                        'optionscode' => 'yesno',
                        'value' => 0,
                        'disporder' => 4
                ),
                        'inplaytracker_timeformat' => array(
                        'title' => 'Eigene Zeitrechnung nutzen?',
                        'description' => 'Soll das Plugin eine Zeitrechnung abseits vom gregorianischen Kalender nutzen (auch ankreuzen, wenn euer Forum vor 1970 oder nach 2038 spielt!)?',
                        'optionscode' => 'yesno',
                        'value' => 0,
                        'disporder' => 5
                ),
                        'inplaytracker_months' => array(
                                'title' => 'Monatsnamen',
                                'description' => '<b>Nur bei eigener Zeitrechnung:</b> Welche Monate sollen in deinem Forum auswählbar sein? Besonders interessant für Fantasy- und/oder Fandomforen!',
                                'optionscode' => 'text',
                                'value' => 'Januar, Februar, März, April, Mai, Juni, Juli, August, September, Oktober, November, Dezember',
                                'disporder' => 6
                ),
  );

  foreach($setting_array as $name => $setting)
  {
    $setting['name'] = $name;
    $setting['gid'] = $gid;
    $db->insert_query('settings', $setting);
  }

  rebuild_settings();

}

function inplaytracker_is_installed()
{
  global $db;
  if($db->field_exists("partners", "threads"))
  {
      return true;
  }
  return false;
}

function inplaytracker_uninstall()
{
  global $db;

        if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

                if (!$alertTypeManager) {
                        $alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
                }

                $alertTypeManager->deleteByCode('inplaytracker_newthread');
                $alertTypeManager->deleteByCode('inplaytracker_newreply');
        }

  // Tabellen entfernen
        if($db->field_exists("partners", "threads"))
  {
    $db->drop_column("threads", "partners");
  }
  if($db->field_exists("partners", "posts"))
  {
    $db->drop_column("posts", "partners");
  }
  if($db->field_exists("ipdate", "threads"))
  {
    $db->drop_column("threads", "ipdate");
  }
  if($db->field_exists("ipdate", "posts"))
  {
    $db->drop_column("posts", "ipdate");
  }
        if($db->field_exists("iport", "threads"))
  {
    $db->drop_column("threads", "iport");
  }
  if($db->field_exists("iport", "posts"))
  {
    $db->drop_column("posts", "iport");
  }
        if($db->field_exists("ipdaytime", "threads"))
  {
    $db->drop_column("threads", "ipdaytime");
  }
  if($db->field_exists("ipdaytime", "posts"))
  {
    $db->drop_column("posts", "ipdaytime");
  }
        if($db->field_exists("openscene", "threads"))
  {
    $db->drop_column("threads", "openscene");
  }
  if($db->field_exists("openscene", "posts"))
  {
    $db->drop_column("posts", "openscene");
  }
        if($db->field_exists("postorder", "threads"))
        {
                $db->drop_column("threads", "postorder");
        }
        if($db->field_exists("postorder", "posts"))
        {
                $db->drop_column("posts", "postorder");
        }

  // Einstellungen entfernen
  $db->delete_query('settings', "name LIKE '%inplaytracker%'");
  $db->delete_query('settinggroups', "name = 'inplaytracker'");

  rebuild_settings();

}

function inplaytracker_activate()
{
  global $db, $mybb;

  // Variablen einfügen
  include MYBB_ROOT."/inc/adminfunctions_templates.php";
        find_replace_templatesets("newthread", "#".preg_quote('{$loginbox}')."#i", '{$loginbox} {$tracker_partners} {$tracker_date} {$tracker_ort} {$tracker_daytime}');
        find_replace_templatesets("editpost", "#".preg_quote('{$loginbox}')."#i", '{$loginbox} {$tracker_partners} {$tracker_date} {$tracker_ort} {$tracker_daytime}');
        find_replace_templatesets("header", "#".preg_quote('{$menu_calendar}')."#i", '{$menu_calendar} {$menu_inplaytracker} {$tracker_date}');
        find_replace_templatesets("member_profile", "#".preg_quote('{$awaybit}')."#i", '{$awaybit} {$inplaytracker}');
        find_replace_templatesets("member_profile", "#".preg_quote('{$referrals}')."#i", '{$referrals} {$inplaytracker_lastpost}');
        find_replace_templatesets("showthread", "#".preg_quote('<tr><td id="posts_container">')."#i", '{$inplaytracker}<tr><td id="posts_container">');

        $insert_array = array(
                'title'                => 'newthread_inplaytracker_partners',
                'template'        => $db->escape_string('<tr>
<td class="trow1" width="20%"><strong>{$lang->inplaytracker_partner}</strong></td>
<td class="trow1"><span class="smalltext">{$lang->inplaytracker_partner_desc} <input type="text" class="textbox" name="partners" id="partners" size="40" maxlength="1155" value="{$partners}" style="min-width: 347px; max-width: 100%;" /> {$lang->inplaytracker_partner_desc_2}</span> </td>
</tr>
<tr>
<td class="trow1" width="20%"><strong>{$lang->inplaytracker_settings}</strong></td>
        <td class="trow1"><select name="postorder">{$postorder_bit}</select> <select name="private">{$private_bit}</select></td>
</tr>

<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/select2/select2.css?ver=1807">
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/select2/select2.min.js?ver=1806"></script>
<script type="text/javascript">
<!--
if(use_xmlhttprequest == "1")
{
    MyBB.select2();
    $("#partners").select2({
        placeholder: "{$lang->search_user}",
        minimumInputLength: 2,
        maximumSelectionSize: \'\',
        multiple: true,
        ajax: { // instead of writing the function to execute the request we use Select2\'s convenient helper
            url: "xmlhttp.php?action=get_users",
            dataType: \'json\',
            data: function (term, page) {
                return {
                    query: term, // search term
                };
            },
            results: function (data, page) { // parse the results into the format expected by Select2.
                // since we are using custom formatting functions we do not need to alter remote JSON data
                return {results: data};
            }
        },
        initSelection: function(element, callback) {
            var query = $(element).val();
            if (query !== "") {
                var newqueries = [];
                exp_queries = query.split(",");
                $.each(exp_queries, function(index, value ){
                    if(value.replace(/\s/g, \'\') != "")
                    {
                        var newquery = {
                            id: value.replace(/,\s?/g, ","),
                            text: value.replace(/,\s?/g, ",")
                        };
                        newqueries.push(newquery);
                    }
                });
                callback(newqueries);
            }
        }
    })
}
// -->
</script>'),
                'sid'                => '-1',
                'version'        => '',
                'dateline'        => TIME_NOW
        );
        $db->insert_query("templates", $insert_array);

  $insert_array = array(
                'title'                => 'newthread_inplaytracker_date',
                'template'        => $db->escape_string('<tr>
<td class="trow1" width="20%"><strong>{$lang->inplaytracker_date}</strong></td>
<td class="trow1"><span class="smalltext"><select name="day">{$day_bit}</select> <select name="month">{$month_bit}</select>
<input type="text" name="year" value="{$year}" style="width: 55px;" /></td>
</tr>'),
                'sid'                => '-1',
                'version'        => '',
                'dateline'        => TIME_NOW
        );
        $db->insert_query("templates", $insert_array);

        $insert_array = array(
        'title'        => 'newthread_inplaytracker_ort',
        'template'    => $db->escape_string('<tr>
<td class="trow1" width="20%"><strong>{$lang->inplaytracker_location}</strong></td>
<td class="trow1"><span class="smalltext"><input type="text" class="textbox" name="iport" size="40" maxlength="155" value="{$iport}" /> <br />{$lang->inplaytracker_location_desc}</span> </td>
</tr>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

                $insert_array = array(
                'title'        => 'newthread_inplaytracker_daytime',
                'template'    => $db->escape_string('<tr>
        <td class="trow1" width="20%"><strong>{$lang->inplaytracker_daytime}</strong></td>
        <td class="trow1"><span class="smalltext"><input type="text" class="textbox" name="ipdaytime" size="40" maxlength="155" value="{$ipdaytime}" /> <br />{$lang->inplaytracker_daytime_desc}</span> </td>
        </tr>'),
                'sid'        => '-1',
                'version'    => '',
                'dateline'    => TIME_NOW
            );
            $db->insert_query("templates", $insert_array);

  $insert_array = array(
                'title'                => 'misc_inplaytracker',
                'template'        => $db->escape_string('<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->inplaytracker}</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->inplaytracker}</strong></td>
</tr>
<tr>
<td class="trow1" align="center">
<h1><i>{$countgesamt}</i> {$lang->inplaytracker_all_scenes}, <i>{$opengesamt}</i> {$lang->inplaytracker_open_scenes}</h1>
{$scenes_user}
</td>
</tr>
</table>
{$footer}
</body>
</html>'),
                'sid'                => '-1',
                'version'        => '',
                'dateline'        => TIME_NOW
        );
        $db->insert_query("templates", $insert_array);

  $insert_array = array(
                'title'                => 'misc_inplaytracker_user',
                'template'        => $db->escape_string('<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="5" class="tborder smalltext" style="width: 85%; margin: 10px;">
        <tr>
                <td class="tcat" colspan="3">
                        {$lang->inplaytracker_scenes_by} {$username} ({$lang->inplaytracker_all_scenes} <i>{$countscenes}</i>, <i>{$countactive}</i> {$lang->inplaytracker_open_scenes})
                </td>
        </tr>
        <tr class="trow2">
        <td width="20%">
                <strong>{$lang->inplaytracker_next_post}</strong>
        </td>
        <td>
                <strong>{$lang->inplaytracker_scene_information}</strong>
        </td>
        <td style="width: 25%">
                <strong>{$lang->inplaytracker_last_post}</strong>
        </td>
</tr>
{$scenes_bit}
</table>'),
                'sid'                => '-1',
                'version'        => '',
                'dateline'        => TIME_NOW
        );
        $db->insert_query("templates", $insert_array);

  $insert_array = array(
                'title'                => 'misc_inplaytracker_bit',
                'template'        => $db->escape_string('<tr class="trow1">
        <td width="20%">
                {$status}
        </td>
        <td>
                <strong>{$lang->inplaytracker_characters}</strong> {$szene[\'partners\']}<br />
                <strong>{$lang->inplaytracker_date}</strong> {$szene[\'ipdate\']}
        </td>
        <td style="width: 25%">
        <a href="showthread.php?tid={$szene[\'tid\']}&pid={$lastpost}#pid{$lastpost}" target="blank">{$szene[\'subject\']}</a><br />
        {$szene[\'lastpost\']}<br />
        {$szene[\'lastposter\']}
        </td>
</tr>'),
                'sid'                => '-1',
                'version'        => '',
                'dateline'        => TIME_NOW
        );
        $db->insert_query("templates", $insert_array);

        $insert_array = array(
                'title'                => 'misc_inplaytracker_overview',
                'template'        => $db->escape_string('<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->inplaytracker}</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->inplaytracker}</strong></td>
</tr>
<tr>
<td class="trow1" align="center"><br />
        <table class="tborder" cellspacing="5" cellpadding="3" style="width: 95%; font-size: 11px;">
        <tr class="thead"><td>{$lang->inplaytracker_order_setting}</td><td>{$lang->inplaytracker_open_setting}</td></tr>
<form method="get" id="search_scene">
        <input type="hidden" name="action" value="inplay" />
        <tr class="trow1" align="center">
                <td><select name="postorder">
                        <option value="1">{$lang->inplaytracker_order}</option>
                        <option value="0">{$lang->inplaytracker_no_order}
                        </option></select>
                </td>
                <td><select name="openscene">
                        <option value="-1">{$lang->inplaytracker_closed}</option>
                        <option value="0">{$lang->inplaytracker_halfopen}</option>
                        <option value="1">{$lang->inplaytracker_open}</option>
                        </select></td>
        </tr>
        <tr class="trow1" align="center"><td colspan="3"><input type="submit" class="button" value="{$lang->inplaytracker_search_scene}" /></td></tr>
</form>
</table><br />

        <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="3" class="tborder" style="width: 95%; font-size: 11px;">
                <tr>
                        <td class="tcat" colspan="3">
                                {$lang->inplaytracker} ({$count_scenes} {$lang->inplaytracker_all_scenes})
                        </td>
                </tr>
                <tr>
                        <td>
                                {$scene_bit}
                        </td>
                </tr>
        </table><br />
</td>
</tr>
</table>
{$footer}
</body>
</html>'),
                'sid'                => '-1',
                'version'        => '',
                'dateline'        => TIME_NOW
        );
        $db->insert_query("templates", $insert_array);

  $insert_array = array(
                'title'                => 'header_inplaytracker',
                'template'        => $db->escape_string('<li><a href="{$mybb->settings[\'bburl\']}/misc.php?action=scenes" class="search">{$lang->inplaytracker_scenes} ({$opengesamt}/{$countgesamt})</a></li><li><a href="{$mybb->settings[\'bburl\']}/misc.php?action=inplay" class="search">{$lang->inplaytracker}</a></li>'),
                'sid'                => '-1',
                'version'        => '',
                'dateline'        => TIME_NOW
        );
        $db->insert_query("templates", $insert_array);

  $insert_array = array(
                'title'                => 'member_profile_inplaytracker',
                'template'        => $db->escape_string('<br />
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="5px" class="tborder tfixed">
<tr>
<td class="thead"><strong>{$lang->inplaytracker_scenes} ({$inplayposts} {$lang->inplaytracker_posts} / {$numscenes} {$lang->inplaytracker_scenes})</strong></td>
</tr>
<tr>
<td class="trow1">
        <div style="max-height: 200px; overflow: auto;">
                <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="5px" class="tborder tfixed">
{$inplaytracker_bit}
                </table>
        </div>
</td>
</table>'),
                'sid'                => '-1',
                'version'        => '',
                'dateline'        => TIME_NOW
        );
        $db->insert_query("templates", $insert_array);

  $insert_array = array(
                'title'                => 'member_profile_inplaytracker_bit',
                'template'        => $db->escape_string('<tr class="trow1">
        <td><a href="showthread.php?tid=$szenen[tid]" target="blank">$szenen[subject]</a><br />
                <span class="smalltext">{$szenen[\'ipdate\']}<br /><i>{$lang->inplaytracker_characters}</i>: {$szenen[\'partners\']}</span>
        </td>
</tr>'),
                'sid'                => '-1',
                'version'        => '',
                'dateline'        => TIME_NOW
        );
        $db->insert_query("templates", $insert_array);

        $insert_array = array(
                'title'                => 'member_profile_inplaytracker_lastpost',
                'template'        => $db->escape_string('<tr>
                                        <td class="trow2"><strong>{$lang->inplaytracker_last_post}:</strong></td>
                                        <td class="trow2">{$last_inplaypost}</td>
                                </tr>'),
                'sid'                => '-1',
                'version'        => '',
                'dateline'        => TIME_NOW
        );
        $db->insert_query("templates", $insert_array);

        $insert_array = array(
                                'title'        => 'showthread_inplaytracker',
                                'template'    => $db->escape_string('<tr>
                                <td>
        <table cellpadding="5" cellspacing="5" width="100%">
<tr>
        <td class="tcat" colspan="2">
                {$lang->inplaytracker_scene_information}
        </td>
</tr>
<tr>
        <td class="trow2" style="width: 100px !important;">
                <strong>{$lang->inplaytracker_characters}</strong>
        </td>
        <td class="trow2">
                {$thread[\'partners\']}
        </td>
</tr>
<tr>
        <td class="trow2" style="width: 100px !important;">
                <strong>{$lang->inplaytracker_date}</strong>
        </td>
        <td class="trow2">
                {$thread[\'ipdate\']}
        </td>
</tr>
{$inplaytracker_location}
{$inplaytracker_daytime}
{$inplaytracker_openscene}
{$inplaytracker_order}
        </table>
        </td>
</tr>'),
                                'sid'        => '-1',
                                'version'    => '',
                                'dateline'    => TIME_NOW
                );
        $db->insert_query("templates", $insert_array);

        $insert_array = array(
                                'title'        => 'showthread_inplaytracker_location',
                                'template'    => $db->escape_string('<tr>
        <td class="trow2" style="width: 120px !important;">
                <strong>{$lang->inplaytracker_location}</strong>
        </td>
        <td class="trow2">
                {$thread[\'iport\']}
        </td>
</tr>'),
                                'sid'        => '-1',
                                'version'    => '',
                                'dateline'    => TIME_NOW
                );
        $db->insert_query("templates", $insert_array);

        $insert_array = array(
                                'title'        => 'showthread_inplaytracker_daytime',
                                'template'    => $db->escape_string('<tr>
        <td class="trow2" style="width: 120px !important;">
                <strong>{$lang->inplaytracker_daytime}</strong>
        </td>
        <td class="trow2">
                {$thread[\'ipdaytime\']}
        </td>
</tr>'),
                                'sid'        => '-1',
                                'version'    => '',
                                'dateline'    => TIME_NOW
                );
        $db->insert_query("templates", $insert_array);

        $insert_array = array(
                                'title'        => 'showthread_inplaytracker_halfopenscene',
                                'template'    => $db->escape_string('<tr>
        <td class="trow2" style="width: 100px !important;">
                <i class="fa fa-ticket" aria-hidden="true"></i> <strong>{$lang->inplaytracker_halfopen}</strong>
        </td>
        <td class="trow2">
                {$lang->inplaytracker_scene_halfopen}
        </td>
</tr>'),
                                'sid'        => '-1',
                                'version'    => '',
                                'dateline'    => TIME_NOW
                );
        $db->insert_query("templates", $insert_array);

        $insert_array = array(
                                'title'        => 'showthread_inplaytracker_openscene',
                                'template'    => $db->escape_string('<tr>
        <td class="trow2" style="width: 100px !important;">
                <i class="fa fa-ticket" aria-hidden="true"></i> <strong>{$lang->inplaytracker_open}</strong>
        </td>
        <td class="trow2">
                {$lang->inplaytracker_scene_open} <a href="misc.php?action=add_partner&tid={$thread[\'tid\']}&uid={$mybb->user[\'uid\']}">{$lang->inplaytracker_add_partner}</a>
        </td>
</tr>'),
                                'sid'        => '-1',
                                'version'    => '',
                                'dateline'    => TIME_NOW
                );
        $db->insert_query("templates", $insert_array);

        $insert_array = array(
                                'title'        => 'showthread_inplaytracker_order',
                                'template'    => $db->escape_string('<tr>
        <td class="trow2" style="width: 100px !important;">
                <i class="fa fa-ticket" aria-hidden="true"></i> <strong>{$lang->inplaytracker_no_order}</strong>
        </td>
        <td class="trow2">
                {$lang->inplaytracker_scene_no_order}</a>
        </td>
</tr>'),
                                'sid'        => '-1',
                                'version'    => '',
                                'dateline'    => TIME_NOW
                );
        $db->insert_query("templates", $insert_array);

}

function inplaytracker_deactivate()
{
  global $db, $mybb;

  // Variablen entfernen
  include MYBB_ROOT."/inc/adminfunctions_templates.php";
        find_replace_templatesets("newthread", "#".preg_quote('{$tracker_partners} {$tracker_date}')."#i", '', 0);
        find_replace_templatesets("newthread", "#".preg_quote('{$tracker_ort}')."#i", '', 0);
        find_replace_templatesets("newthread", "#".preg_quote('{$tracker_daytime}')."#i", '', 0);
        find_replace_templatesets("editpost", "#".preg_quote('{$tracker_partners} {$tracker_date}')."#i", '', 0);
        find_replace_templatesets("editpost", "#".preg_quote('{$tracker_ort}')."#i", '', 0);
        find_replace_templatesets("editpost", "#".preg_quote('{$tracker_daytime}')."#i", '', 0);
  find_replace_templatesets("header", "#".preg_quote('{$menu_inplaytracker}')."#i", '', 0);
  find_replace_templatesets("member_profile", "#".preg_quote('{$inplaytracker}')."#i", '', 0);
  find_replace_templatesets("member_profile", "#".preg_quote('{$inplaytracker_lastpost}')."#i", '', 0);
        find_replace_templatesets("showthread", "#".preg_quote('{$inplaytracker}')."#i", '', 0);

        // Templates entfernen
  $db->delete_query("templates", "title LIKE '%inplaytracker%'");
}

function inplaytracker_newthread()
{
  global $mybb, $lang, $templates, $post_errors, $forum, $thread, $tracker_partners, $tracker_date, $tracker_ort, $tracker_daytime;
        $lang->load('inplaytracker');

  $inplaykategorie = $mybb->settings['inplaytracker_forum'];

        $forum['parentlist'] = ",".$forum['parentlist'].",";
        if(preg_match("/,$inplaykategorie,/i", $forum['parentlist'])) {
                if(isset($mybb->input['previewpost']) || $post_errors)
                 {
                        $partners = htmlspecialchars_uni($mybb->get_input('partners'));
                        $ipdate = strtotime($mybb->get_input('day')." ".$mybb->get_input('month')." ".$mybb->get_input('year'));
                        $active_day = date("j", $ipdate);
                        $year = date("Y", $ipdate);
                        $active_day = $mybb->get_input('day');
                        $iport = htmlspecialchars_uni($mybb->get_input('iport'));
                        $ipdaytime = htmlspecialchars_uni($mybb->get_input('ipdaytime'));
                 }

                if($mybb->settings['inplaytracker_timeformat'] == "0") {
                                 for($i = 1 ; $i < 32 ; $i++) {
                                        $checked_day = "";

                                         if($active_day == $i) {
                                                 $checked_day = "selected=\"selected\"";
                                         }
                                         $day_bit .= "<option value=\"$i\" {$checked_day}>$i</option>";
                                 }

                                 $months = array(
                                        "January" => "Januar",
                                        "February" => "Februar",
                                        "March" => "März",
                                        "April" => "April",
                                        "May" => "Mai",
                                        "June" => "Juni",
                                        "July" => "Juli",
                                        "August" => "August",
                                        "September" => "September",
                                        "October" => "Oktober",
                                        "November" => "November",
                                        "December" => "Dezember"
                                );

                                foreach($months as $key => $month) {
                                        $checked_month = "";
                                        $active_month = date("F", $ipdate);
                                        if($active_month == $key) {
                                                $checked_month = "selected=\"selected\"";
                                        }
                                        $month_bit .= "<option value=\"$key\" {$checked_month}>$month</option>";
                                }
                        }
                        else {
                                for($i = 1 ; $i < 32 ; $i++) {
                                        $checked_day = "";
                                        if($active_day == $i) {
                                                $checked_day = "selected=\"selected\"";
                                        }
                                        $day_bit .= "<option value=\"$i\" {$checked_day}>$i</option>";
                                }

                                $months = explode(", ", $mybb->settings['inplaytracker_months']);
                                foreach($months as $month) {
                                        $checked_month = "";
                                        $active_month = $mybb->get_input('month');
                                        if($active_month == $month) {
                                                $checked_month = "selected=\"selected\"";
                                        }
                                        $month_bit .= "<option value=\"$month\" {$checked_month}>$month</option>";
                                }

                                $year = $mybb->get_input('year');
                        }

                $private = array("-1" => "{$lang->inplaytracker_closed}", "0" => "{$lang->inplaytracker_halfopen}", "1" => "{$lang->inplaytracker_open}");
                foreach($private as $key => $value) {
                        $private_bit .= "<option value=\"$key\">$value</option>";
                }
                $postorder = array("1" => "{$lang->inplaytracker_order}", "0" => "{$lang->inplaytracker_no_order}");
                foreach($postorder as $key => $value) {
                        $postorder_bit .= "<option value=\"$key\">$value</option>";
                }

                 eval("\$tracker_partners = \"".$templates->get("newthread_inplaytracker_partners")."\";");
                 eval("\$tracker_date = \"".$templates->get("newthread_inplaytracker_date")."\";");
                 if($mybb->settings['inplaytracker_location'] == "1")
                 {
                         eval("\$tracker_ort = \"".$templates->get("newthread_inplaytracker_ort")."\";");
          }
                if($mybb->settings['inplaytracker_daytime'] == "1")
                 {
                         eval("\$tracker_daytime = \"".$templates->get("newthread_inplaytracker_daytime")."\";");
          }
  }
}

function inplaytracker_do_newthread()
{
        global $db, $mybb, $lang, $tid, $pmhandler, $pm, $pminfo, $forum, $partners_new, $partner_uid;
        $lang->load('inplaytracker');

  $ownuid = $mybb->user['uid'];
        $inplaykategorie = $mybb->settings['inplaytracker_forum'];
        $forum['parentlist'] = ",".$forum['parentlist'].",";

        if(preg_match("/,$inplaykategorie,/i", $forum['parentlist'])) {
                $partners_new = explode(",", $mybb->get_input('partners'));
                $partners_new = array_map("trim", $partners_new);
                $partner_uids = array();
                foreach($partners_new as $partner) {
                        $db->escape_string($partner);
                        $partner_uid = $db->fetch_field($db->query("SELECT uid FROM ".TABLE_PREFIX."users WHERE username = '$partner'"), "uid");
                        $partner_uids[] = $partner_uid;
                }
                $partner_uids_imp = implode(",", $partner_uids);
                if($mybb->settings['inplaytracker_timeformat'] == "0") {
                        $ipdate = strtotime($mybb->get_input('day')." ".$mybb->get_input('month')." ".$mybb->get_input('year'));
                }
                else {
                        $ipdate = $mybb->get_input('day')." ".$mybb->get_input('month')." ".$mybb->get_input('year');
                }

                $new_record = array(
                        "partners" => $ownuid.",".$partner_uids_imp,
            "ipdate" => $ipdate,
                        "iport" => $db->escape_string($mybb->input['iport']),
                        "ipdaytime" => $db->escape_string($mybb->input['ipdaytime']),
                        "openscene" => (int)$mybb->input['private'],
                        "postorder" => (int)$mybb->input['postorder'],
                );
                $db->update_query("threads", $new_record, "tid='{$tid}'");

          $fromid = $ownuid;
                if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                        foreach($partner_uids as $tag) {
                                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('inplaytracker_newthread');
                    if ($alertType != NULL && $alertType->getEnabled() && $ownuid != $tag) {
                                        $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$tag, $alertType, (int)$tid);
                    MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                                }
                        }
                }
        }
}

function inplaytracker_editpost()
{
  global $mybb, $lang, $templates, $post_errors, $forum, $thread, $pid, $tracker_partners, $tracker_date, $tracker_ort, $tracker_daytime;
        $lang->load('inplaytracker');

  $inplaykategorie = $mybb->settings['inplaytracker_forum'];
  $archiv = $mybb->settings['inplaytracker_archiv'];

        $forum['parentlist'] = ",".$forum['parentlist'].",";
        if(preg_match("/,$inplaykategorie,/i", $forum['parentlist']) OR preg_match("/,$archiv,/i", $forum['parentlist'])) {
    $pid = $mybb->get_input('pid', MyBB::INPUT_INT);
          if($thread['firstpost'] == $pid) {
                  if(isset($mybb->input['previewpost']) || $post_errors) {
                          $partners = htmlspecialchars_uni($mybb->get_input('partners'));
                                if($mybb->settings['inplaytracker_timeformat'] == "0") {
                $ipdate = strtotime($mybb->get_input('day')." ".$mybb->get_input('month')." ".$mybb->get_input('year'));
                                }
                                $iport = htmlspecialchars_uni($mybb->get_input('iport'));
                                $ipdaytime = htmlspecialchars_uni($mybb->get_input('ipdaytime'));
                   }
                   else
                   {
                                $tags = explode(",", $thread['partners']);
                                $usernames = array();
                                foreach($tags as $tag) {
                                        $tagged_user = get_user($tag);
                                        $usernames[] = $tagged_user['username'];
                                }
                                $usernames = implode(",", $usernames);
                    $partners = $usernames;
                    $ipdate = htmlspecialchars_uni($thread['ipdate']);
                                $iport = htmlspecialchars_uni($thread['iport']);
                                $ipdaytime = htmlspecialchars_uni($thread['ipdaytime']);
                   }

                if($mybb->settings['inplaytracker_timeformat'] == "0") {
                                 for($i = 1 ; $i < 32 ; $i++) {
                                        $checked_day = "";
                                         $active_day = date("j", $ipdate);
                                         if($active_day == $i) {
                                                 $checked_day = "selected=\"selected\"";
                                         }
                                         $day_bit .= "<option value=\"$i\" {$checked_day}>$i</option>";
                                 }

                                 $months = array(
                                        "January" => "Januar",
                                        "February" => "Februar",
                                        "March" => "März",
                                        "April" => "April",
                                        "May" => "Mai",
                                        "June" => "Juni",
                                        "July" => "Juli",
                                        "August" => "August",
                                        "September" => "September",
                                        "October" => "Oktober",
                                        "November" => "November",
                                        "December" => "Dezember"
                                );

                                foreach($months as $key => $month) {
                                        $checked_month = "";
                                        $active_month = date("F", $ipdate);
                                        if($active_month == $key) {
                                                $checked_month = "selected=\"selected\"";
                                        }
                                        $month_bit .= "<option value=\"$key\" {$checked_month}>$month</option>";
                                }

                                $year = date("Y", $ipdate);
                        }
                        else {
                                for($i = 1 ; $i < 32 ; $i++) {
                                        $checked_day = "";
                                        $active_day = $mybb->get_input('day');
                                        if($active_day == $i) {
                                                $checked_day = "selected=\"selected\"";
                                        }
                                        $day_bit .= "<option value=\"$i\" {$checked_day}>$i</option>";
                                }

                                $months = explode(", ", $mybb->settings['inplaytracker_months']);
                                foreach($months as $month) {
                                        $checked_month = "";
                                        $active_month = $mybb->get_input('month');
                                        if($active_month == $month) {
                                                $checked_month = "selected=\"selected\"";
                                        }
                                        $month_bit .= "<option value=\"$month\" {$checked_month}>$month</option>";
                                }

                                $year = $mybb->get_input('year');
                        }

                        $private = array("-1" => "{$lang->inplaytracker_closed}", "0" => "{$lang->inplaytracker_halfopen}", "1" => "{$lang->inplaytracker_open}");
                        foreach($private as $key => $value) {
                                $checked = "";
                                if($thread['openscene'] == $key) {
                                        $checked = "selected=\"selected\"";
                                }
                                $private_bit .= "<option value=\"$key\" {$checked}>$value</option>";
                        }

                        $postorder = array("1" => "{$lang->inplaytracker_order}","0" => "{$lang->inplaytracker_no_order}");
                        foreach($postorder as $key => $value) {
                                $checked = "";
                                if($thread['postorder'] == $key) {
                                        $checked = "selected=\"selected\"";
                                }
                                $postorder_bit .= "<option value=\"$key\" {$checked}>$value</option>";
                        }


                  eval("\$tracker_partners = \"".$templates->get("newthread_inplaytracker_partners")."\";");
                   eval("\$tracker_date = \"".$templates->get("newthread_inplaytracker_date")."\";");
                        if($mybb->settings['inplaytracker_location'] == "1") {
                                eval("\$tracker_ort = \"".$templates->get("newthread_inplaytracker_ort")."\";");
                        }
                        if($mybb->settings['inplaytracker_daytime'] == "1") {
                                eval("\$tracker_daytime = \"".$templates->get("newthread_inplaytracker_daytime")."\";");
                        }
          }
        }
}

function inplaytracker_do_editpost()
{
    global $db, $mybb, $tid, $pid, $thread, $partners_new, $partner_uid;

    if($pid != $thread['firstpost']) {
                        return;
                }

                $partners_new = explode(",", $mybb->get_input('partners'));
                $partners_new = array_map("trim", $partners_new);
                $partner_uids = array();
                foreach($partners_new as $partner) {
                        $db->escape_string($partner);
                        $partner_uid = $db->fetch_field($db->query("SELECT uid FROM ".TABLE_PREFIX."users WHERE username = '$partner'"), "uid");
                        $partner_uids[] = $partner_uid;
                }
                $partner_uids = implode(",", $partner_uids);
                if($mybb->settings['inplaytracker_timeformat'] == "0") {
                        $ipdate = strtotime($mybb->input['day']." ".$mybb->input['month']." ".$mybb->input['year']);
                }
                else {
                        $ipdate = $mybb->input['day']." ".$mybb->input['month']." ".$mybb->input['year'];
                }

    $new_record = array(
        "partners" => $partner_uids,
        "ipdate" => $ipdate,
                                "iport" => $db->escape_string($mybb->input['iport']),
                                "ipdaytime" => $db->escape_string($mybb->input['ipdaytime']),
                                "openscene" => (int)$mybb->input['private'],
                                "postorder" => (int)$mybb->input['postorder'],
    );
    $db->update_query("threads", $new_record, "tid='{$tid}'");
}

function inplaytracker_do_newreply()
{
        global $db, $mybb, $lang, $username, $pmhandler, $pm, $pminfo, $thread, $forum;
        $lang->load('inplaytracker');

  $ownuid = $mybb->user['uid'];
        $partners = $thread['partners'];
        $inplaykategorie = $mybb->settings['inplaytracker_forum'];

        $last_post = $db->fetch_field($db->query("SELECT pid FROM ".TABLE_PREFIX."posts WHERE tid = '$thread[tid]' ORDER BY pid DESC LIMIT 1"), "pid");
        $forum['parentlist'] = ",".$forum['parentlist'].",";
        if(preg_match("/,$inplaykategorie,/i", $forum['parentlist'])) {
                $fromid = $ownuid;
                $tags = explode(",", $partners);
                if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                        foreach($tags as $tag) {
                                $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('inplaytracker_newreply');
                    if ($alertType != NULL && $alertType->getEnabled() && $ownuid != $tag) {
                                        $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$tag, $alertType, (int)$thread['tid']);
                               /*
                               $alert->setExtraDetails([
                                                'subject' => $thread['subject'],
                                                'lastpost' => $last_post
                                        ]);      */
                            MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                                }
                        }
                }
        }
}

function inplaytracker_forumdisplay(&$thread)
{
  global $db, $lang, $mybb, $thread, $foruminfo;
        $lang->load('inplaytracker');

  $inplaykategorie = $mybb->settings['inplaytracker_forum'];
  $inplayarchiv = $mybb->settings['inplaytracker_archiv'];

        $foruminfo['parentlist'] = ",".$foruminfo['parentlist'].",";
        if(preg_match("/,$inplaykategorie,/i", $foruminfo['parentlist']) OR preg_match("/,$inplayarchiv,/i", $foruminfo['parentlist']) ) {
          $partners = explode(",", $thread['partners']);
          $partnerusers = array();
          foreach ($partners as $partner) {
            $charakter = get_user($partner);
                  $taguser = build_profile_link($charakter['username'], $partner);
            $partnerusers[] = $taguser;
          }
                $partnerusers = implode(" &raquo; ", $partnerusers);
                if($mybb->settings['inplaytracker_timeformat'] == "0") {
                        $ipdate = date("d.m.Y", ($thread['ipdate']));
                }
                else {
                        $ipdate = $thread['ipdate'];
                }
                if($mybb->settings['inplaytracker_location'] == "1") {
                        $thread['profilelink'] =  "<b>{$lang->inplaytracker_characters}:</b> $partnerusers <br /> <b>{$lang->inplaytracker_date}:</b> $ipdate <br /> <b>{$lang->inplaytracker_location}:</b> $thread[iport]";
                }
                if($mybb->settings['inplaytracker_daytime'] == "1") {
                        $thread['profilelink'] =  "<b>{$lang->inplaytracker_characters}:</b> $partnerusers <br /> <b>{$lang->inplaytracker_date}:</b> $ipdate <br /> <b>{$lang->inplaytracker_daytime}:</b> $thread[ipdaytime]";
                }
                if($mybb->settings['inplaytracker_location'] == "1" && $mybb->settings['inplaytracker_daytime']) {
                        $thread['profilelink'] =  "<b>{$lang->inplaytracker_characters}:</b> $partnerusers <br /> <b>{$lang->inplaytracker_date}:</b> $ipdate <br /> <b>{$lang->inplaytracker_location}:</b> $thread[iport] <br /> <b>{$lang->inplaytracker_daytime}:</b> $thread[ipdaytime]";
                }
                else {
                  $thread['profilelink'] =  "<b>{$lang->inplaytracker_characters}:</b> $partnerusers <br /> <b>{$lang->inplaytracker_date}:</b> $ipdate";
                }
          return $thread;
  }
}

function inplaytracker_misc()
{
  global $mybb, $db, $lang, $templates, $headerinclude, $header, $footer, $scenes_bit, $scenes_user;
        $lang->load('inplaytracker');

  $mybb->input['action'] = $mybb->get_input('action');

        if($mybb->input['action'] == "inplay") {
                $postorder = $mybb->get_input('postorder');
                $openscene = $mybb->get_input('openscene');
                $ipforum = $mybb->settings['inplaytracker_forum'];
                if(empty($postorder) && $postorder != "0") {
                        $postorder = "%";
                }
                if(empty($openscene) && $openscene != "0") {
                        $openscene = "%";
                }
                $private = array("-1" => "{$lang->inplaytracker_closed}", "0" => "{$lang->inplaytracker_halfopen}", "1" => "{$lang->inplaytracker_open}");
                $order = array("0" => "{$lang->inplaytracker_no_order}", "1" => "{$lang->inplaytracker_order}",);
                if($mybb->settings['inplaytracker_timeformat'] == "0") {
                        $query = $db->query("SELECT *, ".TABLE_PREFIX."threads.lastpost, ".TABLE_PREFIX."threads.partners, ".TABLE_PREFIX."threads.iport, ".TABLE_PREFIX."threads.postorder, ".TABLE_PREFIX."threads.openscene, ".TABLE_PREFIX."threads.ipdaytime, ".TABLE_PREFIX."threads.subject, ".TABLE_PREFIX."threads.lastposter, ".TABLE_PREFIX."threads.ipdate, ".TABLE_PREFIX."threads.lastposteruid FROM ".TABLE_PREFIX."threads
                        LEFT JOIN ".TABLE_PREFIX."posts ON ".TABLE_PREFIX."threads.lastpost = ".TABLE_PREFIX."posts.dateline
                        LEFT JOIN ".TABLE_PREFIX."forums ON ".TABLE_PREFIX."threads.fid = ".TABLE_PREFIX."forums.fid
                        WHERE ".TABLE_PREFIX."forums.parentlist LIKE '$ipforum,%'
                        AND ".TABLE_PREFIX."threads.partners != ''
                        AND ".TABLE_PREFIX."threads.postorder LIKE '$postorder'
                        AND ".TABLE_PREFIX."threads.openscene LIKE '$openscene'
                        ORDER by ".TABLE_PREFIX."threads.ipdate");
                        $count_scenes = mysqli_num_rows($query);
                        while($szene = $db->fetch_array($query)) {
                                $status = "";
                                $partners = explode(",", $szene['partners']);
                                $partnerusers = array();
                                foreach ($partners as $partner) {
                                        $charakter = get_user($partner);
                                        $taguser = build_profile_link($charakter['username'], $partner);
                                        $partnerusers[] = $taguser;
                                }
                                $szene['partners'] = implode(" &raquo; ", $partnerusers);
                                if($mybb->settings['inplaytracker_timeformat'] == "0") {
                                        $szene['ipdate'] = date("d.m.Y", $szene['ipdate']);
                                }
                                $szene['lastpost'] = my_date('relative', $szene['lastpost']);
                                if(my_strlen($szene['subject']) > 35) {
                                        $szene['subject'] = my_substr($szene['subject'], 0, 35)."...";
                                }
                                $status = $private[$szene['openscene']];
                                $status .= "<br />".$order[$szene['postorder']];
                                $lastpost = $db->fetch_field($db->query("SELECT pid FROM ".TABLE_PREFIX."posts WHERE tid = '$szene[tid]' ORDER BY pid DESC LIMIT 1"), "pid");
                                eval("\$scene_bit .= \"".$templates->get("misc_inplaytracker_bit")."\";");
                        }
                }
                else {
                        $months = explode(", ", $mybb->settings['inplaytracker_months']);
                        $count_scenes = "0";
                        foreach($months as $month) {
                                $query = $db->query("SELECT *, ".TABLE_PREFIX."threads.lastpost, ".TABLE_PREFIX."threads.partners, ".TABLE_PREFIX."threads.iport, ".TABLE_PREFIX."threads.postorder, ".TABLE_PREFIX."threads.ipdaytime, ".TABLE_PREFIX."threads.subject, ".TABLE_PREFIX."threads.lastposter, ".TABLE_PREFIX."threads.ipdate, ".TABLE_PREFIX."threads.lastposteruid FROM ".TABLE_PREFIX."threads
                                LEFT JOIN ".TABLE_PREFIX."posts ON ".TABLE_PREFIX."threads.lastpost = ".TABLE_PREFIX."posts.dateline
                                LEFT JOIN ".TABLE_PREFIX."forums ON ".TABLE_PREFIX."threads.fid = ".TABLE_PREFIX."forums.fid
                                WHERE ".TABLE_PREFIX."forums.parentlist LIKE '$ipforum,%'
                                AND ".TABLE_PREFIX."threads.partners != ''
                                AND ".TABLE_PREFIX."threads.postorder LIKE '$postorder'
                                AND ".TABLE_PREFIX."threads.openscene LIKE '$openscene'
                                AND ".TABLE_PREFIX."threads.ipdate LIKE '%$month%'
                                ORDER by CAST(".TABLE_PREFIX."threads.ipdate AS signed)");
                                while($szene = $db->fetch_array($query)) {
                                        $count_scenes++;
                                        $partners = explode(",", $szene['partners']);
                                        $partnerusers = array();
                                        foreach ($partners as $partner) {
                                                $charakter = get_user($partner);
                                                $taguser = build_profile_link($charakter['username'], $partner);
                                                $partnerusers[] = $taguser;
                                        }
                                        $szene['partners'] = implode(" &raquo; ", $partnerusers);
                                        $szene['lastpost'] = my_date('relative', $szene['lastpost']);
                                        if(my_strlen($szene['subject']) > 35) {
                                                $szene['subject'] = my_substr($szene['subject'], 0, 35)."...";
                                        }
                                        $private = array("-1" => "{$lang->inplaytracker_closed}", "0" => "{$lang->inplaytracker_halfopen}", "1" => "{$lang->inplaytracker_open}");
                                        $status = $private[$szene[openscene]];
                                        $order = array("0" => "{$lang->inplaytracker_no_order}", "1" => "{$lang->inplaytracker_order}",);
                                        $status .= "<br />".$order[$szene[postorder]];
                                        $lastpost = $db->fetch_field($db->query("SELECT pid FROM ".TABLE_PREFIX."posts WHERE tid = '$szene[tid]' ORDER BY pid DESC LIMIT 1"), "pid");
                                        eval("\$scene_bit .= \"".$templates->get("misc_inplaytracker_bit")."\";");
                                }
                        }
                }
                eval("\$page = \"".$templates->get("misc_inplaytracker_overview")."\";");
                output_page($page);
        }

        if($mybb->input['action'] == "add_partner") {
                $uid = $mybb->get_input('uid');
                $tid = $mybb->get_input('tid');
                $thread = get_thread($tid);
                $partners = explode(",", $thread['partners']);
                if(!in_array($uid, $partners)) {
                        $lastposteruid = $thread['lastposteruid'];
                        $lastposterkey = array_search($lastposteruid, $partners);
                        array_splice($partners, $lastposterkey+1, 0, $uid);
                        $partners = implode(",", $partners);
                        $new_record = array(
                                "partners" => $partners
                        );
                        $db->update_query("threads", $new_record, "tid = '$tid'");
                }
                redirect("showthread.php?tid={$tid}", "Dein Charakter wurde hinzugefügt!");
        }

  if($mybb->input['action'] == "scenes") {
          $ipforum = $mybb->settings['inplaytracker_forum'];
    $email = $mybb->user['email'];
                $query = $db->query("SELECT username, uid FROM ".TABLE_PREFIX."users WHERE ".TABLE_PREFIX."users.email = '$email' ORDER By ".TABLE_PREFIX."users.username ASC");
                $countgesamt = 0;
                $opengesamt = 0;
                while($user = $db->fetch_array($query)) {
                        $username = $user['username'];
                        $ownuid = $user['uid'];
                        $query1 = $db->query("SELECT *, ".TABLE_PREFIX."threads.lastpost, ".TABLE_PREFIX."threads.partners, ".TABLE_PREFIX."threads.iport, ".TABLE_PREFIX."threads.postorder, ".TABLE_PREFIX."threads.ipdaytime, ".TABLE_PREFIX."threads.subject, ".TABLE_PREFIX."threads.lastposter, ".TABLE_PREFIX."threads.ipdate, ".TABLE_PREFIX."threads.lastposteruid FROM ".TABLE_PREFIX."threads
                        LEFT JOIN ".TABLE_PREFIX."posts ON ".TABLE_PREFIX."threads.lastpost = ".TABLE_PREFIX."posts.dateline
      LEFT JOIN ".TABLE_PREFIX."forums ON ".TABLE_PREFIX."threads.fid = ".TABLE_PREFIX."forums.fid
      WHERE ".TABLE_PREFIX."forums.parentlist LIKE '$ipforum,%'
                        ORDER by ".TABLE_PREFIX."threads.lastpost DESC");
                        $scenes_bit = "";
                        $countscenes = 0;
                        $countactive = 0;
                        while($szene = $db->fetch_array($query1)) {
                                $szenen_partner = ",".$szene['partners'].",";
                                if(preg_match("/,$ownuid,/i", $szenen_partner)) {
                                        $countgesamt++;
                                        $tagged = explode(",", $szene['partners']);
                                  $szene['lastpost'] = my_date('relative', $szene['lastpost']);
                                        if(my_strlen($szene['subject']) > 35) {
                                    $szene['subject'] = my_substr($szene['subject'], 0, 35)."...";
                                  }
                                        $key = array_search($szene['lastposteruid'], $tagged);
                      $key = $key + 1;
                      $next = $tagged[$key];
                      if(!$tagged[$key]) {
                        $next = $tagged[0];
                      }
                                        $next = get_user($next);
                                        $next = $next['username'];
                                        if($next == $username && $szene['postorder'] == "1") {
                  $status = "<center><span style=\"text-transform: uppercase; font-size: 12px; font-weight: bold; color: #66B84B\">DU BIST DRAN!</span></center>";
                                          $countactive++;
                                          $opengesamt++;
                                        }
                if($next != $username && $szene['postorder'] == "1") {
                  $status = "<center><span style=\"text-transform: uppercase; font-size: 12px; font-weight: bold; color: #B8664B\">$next</span></center>";
                }
                                        if($szene['postorder'] == "0") {
                                                $status = "<center><span style=\"text-transform: uppercase; font-size: 12px; font-weight: bold; color: #B8664B\">{$lang->inplaytracker_no_order}</span></center>";
                                        }
                                        $countscenes++;
                                        $szene['lastposter'] = build_profile_link($szene['lastposter'],$szene['lastposteruid']);
                                        $partners = explode(",", $szene['partners']);
                                        $partnerusers = array();
                                        foreach ($partners as $partner) {
                                                $charakter = get_user($partner);
                                                $taguser = build_profile_link($charakter['username'], $partner);
                                                $partnerusers[] = $taguser;
                                        }
                                        $szene['partners'] = implode(" &raquo; ", $partnerusers);
                                        if($mybb->settings['inplaytracker_timeformat'] == "0") {
                                                $szene['ipdate'] = date("d.m.Y", $szene['ipdate']);
                                        }
                                        $lastpost = $db->fetch_field($db->query("SELECT pid FROM ".TABLE_PREFIX."posts WHERE tid = '$szene[tid]' ORDER BY pid DESC LIMIT 1"), "pid");
                                        eval("\$scenes_bit .= \"".$templates->get("misc_inplaytracker_bit")."\";");
                                }
                        }
                        eval("\$scenes_user .= \"".$templates->get("misc_inplaytracker_user")."\";");
                }
    eval("\$page = \"".$templates->get("misc_inplaytracker")."\";");
    output_page($page);
  }
}

function inplaytracker_global()
{
  global $mybb, $db, $lang, $templates, $menu_inplaytracker, $test;
        $lang->load('inplaytracker');

  $ipforum = $mybb->settings['inplaytracker_forum'];
  $email = $mybb->user['email'];

  $query = $db->query("SELECT username, uid FROM ".TABLE_PREFIX."users WHERE ".TABLE_PREFIX."users.email = '$email' ORDER By ".TABLE_PREFIX."users.username ASC");

  $countgesamt = 0;
  $opengesamt = 0;

  while($user = $db->fetch_array($query)) {

    $username = $user['username'];
        $ownuid = $user['uid'];

    $query1 = $db->query("SELECT *, ".TABLE_PREFIX."threads.partners, ".TABLE_PREFIX."threads.postorder, ".TABLE_PREFIX."threads.lastposter, ".TABLE_PREFIX."threads.lastposteruid FROM ".TABLE_PREFIX."threads
    LEFT JOIN ".TABLE_PREFIX."posts ON ".TABLE_PREFIX."threads.lastpost = ".TABLE_PREFIX."posts.dateline
    LEFT JOIN ".TABLE_PREFIX."forums ON ".TABLE_PREFIX."threads.fid = ".TABLE_PREFIX."forums.fid
    WHERE ".TABLE_PREFIX."forums.parentlist LIKE '$ipforum,%'");

    while($szene = $db->fetch_array($query1)) {
                $next = "";
                        $szenen_partner = ",".$szene['partners'].",";
                        if(preg_match("/,$ownuid,/i", $szenen_partner)) {
      $countgesamt++;

      $tagged = explode(",", $szene['partners']);

      $key = array_search($szene['lastposteruid'], $tagged);
      $key = $key + 1;
      $next = $tagged[$key];
          if(!$tagged[$key]) {
        $next = $tagged[0];
      }

      $next = get_user($next);
          $next = $next['username'];

      if($next == $username && $szene['postorder'] == "1") {
        $opengesamt++;
                  $test .= $szene['subject'];
      }


                }
    }
  }

        eval("\$menu_inplaytracker = \"".$templates->get("header_inplaytracker")."\";");
}

function inplaytracker_profile() {
  global $db, $mybb, $lang, $templates, $memprofile, $inplaytracker_lastpost, $inplaytracker, $inplaytracker_bit, $numscenes;
        $lang->load('inplaytracker');

  $ipforum = $mybb->settings['inplaytracker_forum'];
  $archiv = $mybb->settings['inplaytracker_archiv'];

        $last_inplaypost = $db->fetch_field($db->query("SELECT ".TABLE_PREFIX."posts.dateline FROM ".TABLE_PREFIX."posts
        LEFT JOIN ".TABLE_PREFIX."threads on ".TABLE_PREFIX."threads.tid = ".TABLE_PREFIX."posts.tid
        LEFT JOIN ".TABLE_PREFIX."forums on ".TABLE_PREFIX."forums.fid = ".TABLE_PREFIX."threads.fid
        WHERE ".TABLE_PREFIX."posts.username = '$memprofile[username]'
  AND (".TABLE_PREFIX."forums.parentlist LIKE '$ipforum,%'
  OR ".TABLE_PREFIX."forums.parentlist LIKE '%,$archiv%')
        AND ".TABLE_PREFIX."posts.visible = '1'"), "dateline");
        $last_inplaypost = my_date("relative", $last_inplaypost);
        eval("\$inplaytracker_lastpost .= \"".$templates->get("member_profile_inplaytracker_lastpost")."\";");

  $inplayposts = $db->fetch_field($db->query("SELECT COUNT(*) AS inplayposts FROM ".TABLE_PREFIX."posts
        LEFT JOIN ".TABLE_PREFIX."threads on ".TABLE_PREFIX."threads.tid = ".TABLE_PREFIX."posts.tid
        LEFT JOIN ".TABLE_PREFIX."forums on ".TABLE_PREFIX."forums.fid = ".TABLE_PREFIX."threads.fid
        WHERE ".TABLE_PREFIX."posts.username = '$memprofile[username]'
  AND (".TABLE_PREFIX."forums.parentlist LIKE '$ipforum,%'
  OR ".TABLE_PREFIX."forums.parentlist LIKE '%,$archiv%')
        AND ".TABLE_PREFIX."posts.visible = '1'"), "inplayposts");
  if($inplayposts != "0") {
                if($mybb->settings['inplaytracker_timeformat'] == "0") {
                        $query = $db->query("SELECT * , ".TABLE_PREFIX."threads.partners, ".TABLE_PREFIX."threads.ipdate, ".TABLE_PREFIX."threads.iport, ".TABLE_PREFIX."threads.ipdaytime, ".TABLE_PREFIX."threads.subject FROM ".TABLE_PREFIX."threads
                        LEFT JOIN ".TABLE_PREFIX."posts ON ".TABLE_PREFIX."threads.tid = ".TABLE_PREFIX."posts.tid
                        LEFT JOIN ".TABLE_PREFIX."forums ON ".TABLE_PREFIX."forums.fid = ".TABLE_PREFIX."threads.fid
                        WHERE (".TABLE_PREFIX."forums.parentlist LIKE '%$ipforum%' OR ".TABLE_PREFIX."forums.parentlist LIKE '%,$archiv%')
                        AND ".TABLE_PREFIX."threads.visible = '1'
                        GROUP by ".TABLE_PREFIX."threads.tid
                        ORDER by ".TABLE_PREFIX."threads.ipdate ASC
                        ");

                        while($szenen = $db->fetch_array($query)) {
                                $ownuid = $memprofile['uid'];
                                $szenen_partner = ",".$szenen['partners'].",";
                                if(preg_match("/,$ownuid,/i", $szenen_partner)) {
                                        $partners = explode(",", $szenen['partners']);
                                        $partnerusers = array();
                                        foreach ($partners as $partner) {
                                                $charakter = get_user($partner);
                                                $taguser = build_profile_link($charakter['username'], $partner);
                                                if(empty($charakter)) {
                                                        $taguser = $db->fetch_field($db->query("SELECT username FROM ".TABLE_PREFIX."posts WHERE tid = '$szenen[tid]' AND uid = '$partner'"), "username");
                                                }
                                                $partnerusers[] = $taguser;
                                        }
                                        $szenen['partners'] = implode(" &raquo; ", $partnerusers);
                                        $szenen['ipdate'] = date("d.m.Y", $szenen['ipdate']);
                                        $numscenes++;
                            eval("\$inplaytracker_bit .= \"".$templates->get("member_profile_inplaytracker_bit")."\";");
                                }
                        }
                }
                else {
                        $months = explode(", ", $mybb->settings['inplaytracker_months']);
                        foreach($months as $month) {
                                $query = $db->query("SELECT * , ".TABLE_PREFIX."threads.partners, ".TABLE_PREFIX."threads.ipdate, ".TABLE_PREFIX."threads.iport, ".TABLE_PREFIX."threads.ipdaytime, ".TABLE_PREFIX."threads.subject FROM ".TABLE_PREFIX."threads
                                LEFT JOIN ".TABLE_PREFIX."posts ON ".TABLE_PREFIX."threads.tid = ".TABLE_PREFIX."posts.tid
                                LEFT JOIN ".TABLE_PREFIX."forums ON ".TABLE_PREFIX."forums.fid = ".TABLE_PREFIX."threads.fid
                                WHERE (".TABLE_PREFIX."forums.parentlist LIKE '%$ipforum%' OR ".TABLE_PREFIX."forums.parentlist LIKE '%,$archiv%')
                                AND ".TABLE_PREFIX."threads.ipdate LIKE '%$month%'
                                GROUP by ".TABLE_PREFIX."threads.tid
                                ORDER by CAST(".TABLE_PREFIX."threads.ipdate AS signed)
                                ");
                                while($szenen = $db->fetch_array($query)) {
                                        $ownuid = $memprofile['uid'];
                                        $szenen_partner = ",".$szenen['partners'].",";
                                        if(preg_match("/,$ownuid,/i", $szenen_partner)) {
                                                $partners = explode(",", $szenen['partners']);
                                                $partnerusers = array();
                                                foreach ($partners as $partner) {
                                                        $charakter = get_user($partner);
                                                        $taguser = build_profile_link($charakter['username'], $partner);
                                                        $partnerusers[] = $taguser;
                                                }
                                                $szenen['partners'] = implode(" &raquo; ", $partnerusers);
                                    eval("\$inplaytracker_bit .= \"".$templates->get("member_profile_inplaytracker_bit")."\";");
                                        }
                                }
                        }
                }
                eval("\$inplaytracker = \"".$templates->get("member_profile_inplaytracker")."\";");
        }
}

function inplaytracker_showthread() {
        global $mybb, $db, $lang, $user, $templates, $thread, $inplaytracker, $inplaytracker_location;
        $lang->load('inplaytracker');
        $uid = $mybb->user['uid'];

        $parentlist = $db->fetch_field($db->query("SELECT parentlist FROM ".TABLE_PREFIX."forums WHERE fid = '$thread[fid]'"), "parentlist");
         $inplaykategorie = $mybb->settings['inplaytracker_forum'];
         $archiv = $mybb->settings['inplaytracker_archiv'];
             $parentlist = ",".$parentlist.",";
             if(preg_match("/,$inplaykategorie,/i", $parentlist) OR preg_match("/,$archiv,/i", $parentlist)) {
                if($mybb->settings['inplaytracker_location'] == "1") {
                        eval("\$inplaytracker_location = \"".$templates->get("showthread_inplaytracker_location")."\";");
                }
                if($mybb->settings['inplaytracker_daytime'] == "1") {
                        eval("\$inplaytracker_daytime = \"".$templates->get("showthread_inplaytracker_daytime")."\";");
                }
                if($mybb->settings['inplaytracker_timeformat'] == "0") {
                        $thread['ipdate'] = date("d.m.Y", $thread['ipdate']);
                }
                $partners = explode(",", $thread['partners']);
                $partnerusers = array();
                foreach ($partners as $partner) {
                        $charakter = get_user($partner);
                        $taguser = build_profile_link($charakter['username'], $partner);
                        $partnerusers[] = $taguser;
                }
                $thread['partners'] = implode(" &raquo; ", $partnerusers);
                $thread['partners'] = implode(" &raquo; ", $partnerusers);
                if($thread['openscene'] == "1" && !in_array($uid, $partners)) {
                        eval("\$inplaytracker_openscene = \"".$templates->get("showthread_inplaytracker_openscene")."\";");
                }
                elseif($thread['openscene'] == "0"  && !in_array($uid, $partners)) {
                        eval("\$inplaytracker_openscene = \"".$templates->get("showthread_inplaytracker_halfopenscene")."\";");
                }
                else {
                        $inplaytracker_openscene = "";
                }
                if($thread['postorder'] == "0") {
                        eval("\$inplaytracker_order = \"".$templates->get("showthread_inplaytracker_order")."\";");
                }
                else {
                        $inplaytracker_order = "";
                }
                eval("\$inplaytracker = \"".$templates->get("showthread_inplaytracker")."\";");
        }
}
function inplaytracker_alerts() {
        global $mybb, $lang;
        $lang->load('inplaytracker');
        /**
         * Alert formatter for my custom alert type.
         */
        class MybbStuff_MyAlerts_Formatter_InplaytrackerNewthreadFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
        {
            /**
             * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
             *
             * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
             *
             * @return string The formatted alert string.
             */
            public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
            {
                return $this->lang->sprintf(
                    $this->lang->inplaytracker_newthread,
                    $outputAlert['from_user'],
                    $outputAlert['dateline']
                );
            }

            /**
             * Init function called before running formatAlert(). Used to load language files and initialize other required
             * resources.
             *
             * @return void
             */
            public function init()
            {
                if (!$this->lang->inplaytracker) {
                    $this->lang->load('inplaytracker');
                }
            }

            /**
             * Build a link to an alert's content so that the system can redirect to it.
             *
             * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
             *
             * @return string The built alert, preferably an absolute link.
             */
            public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
            {
                return $this->mybb->settings['bburl'] . '/' . get_thread_link($alert->getObjectId());
            }
        }

        if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
                $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

                if (!$formatterManager) {
                        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
                }

                $formatterManager->registerFormatter(
                                new MybbStuff_MyAlerts_Formatter_InplaytrackerNewthreadFormatter($mybb, $lang, 'inplaytracker_newthread')
                );
        }

        /**
         * Alert formatter for my custom alert type.
         */
        class MybbStuff_MyAlerts_Formatter_InplaytrackerNewreplyFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
        {
                        /**
                         * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
                         *
                         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
                         *
                         * @return string The formatted alert string.
                         */
                        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
                        {
                                        $alertContent = $alert->getExtraDetails();
                                        return $this->lang->sprintf(
                                                        $this->lang->inplaytracker_newreply,
                                                        $outputAlert['from_user'],
                                                        $alertContent['subject'],
                                                        $outputAlert['dateline']
                                        );
                        }

                        /**
                         * Init function called before running formatAlert(). Used to load language files and initialize other required
                         * resources.
                         *
                         * @return void
                         */
                        public function init()
                        {
                                        if (!$this->lang->inplaytracker) {
                                                        $this->lang->load('inplaytracker');
                                        }
                        }

                        /**
                         * Build a link to an alert's content so that the system can redirect to it.
                         *
                         * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
                         *
                         * @return string The built alert, preferably an absolute link.
                         */
                        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
                        {
                                        $alertContent = $alert->getExtraDetails();
                                        return $this->mybb->settings['bburl'] . '/' . get_post_link((int) $alertContent['lastpost'], (int) $alert->getObjectId()) . '#pid' . $alertContent['lastpost'];
                        }
        }

        if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
                $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

                if (!$formatterManager) {
                        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
                }

                $formatterManager->registerFormatter(
                                new MybbStuff_MyAlerts_Formatter_InplaytrackerNewreplyFormatter($mybb, $lang, 'inplaytracker_newreply')
                );
        }

}

function inplaytracker_tools_menu($sub_menu) {
        $found_free_index = 0;
        $index = 100;

        // Looking for unused Index in $sub_menu
        while($found_free_index == 0)
        {
                if(!isset($sub_menu[$index]))
                {
                        $sub_menu[$index] = array(
                                'id'        => 'inplaytracker',
                                'title'        => 'Inplaytracker',
                                'link'        => 'index.php?module=tools-inplaytracker'
                        );
                        $found_free_index = 1;
                }
                else
                {
                        $index++;
                }
                ksort($sub_menu);
                return $sub_menu;
        }
}

function inplaytracker_tools_action_handler($actions) {
        $actions['inplaytracker'] = array('active' => 'inplaytracker', 'file' => 'inplaytracker.php');
        return $actions;
}

?>