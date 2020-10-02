<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.");
}

$plugins->add_hook("newthread_start", "inplaytracker_newthread");
$plugins->add_hook("newthread_do_newthread_end", "inplaytracker_do_newthread");
$plugins->add_hook("editpost_end", "inplaytracker_editpost");
$plugins->add_hook("editpost_do_editpost_end", "inplaytracker_do_editpost");
$plugins->add_hook("forumdisplay_thread_end", "inplaytracker_forumdisplay");
$plugins->add_hook("postbit", "inplaytracker_postbit");
$plugins->add_hook("member_profile_end", "inplaytracker_profile");
$plugins->add_hook("global_intermediate", "inplaytracker_global");
$plugins->add_hook("misc_start", "inplaytracker_misc");
$plugins->add_hook("newreply_do_newreply_end", "inplaytracker_do_newreply");
if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
	$plugins->add_hook("global_start", "inplaytracker_alerts");
}

function inplaytracker_info()
{
	global $lang;
	$lang->load('inplaytracker');
	
	return array(
		"name"			=> $lang->ipt_name,
		"description"	=> $lang->ipt_description,
		"website"		=> "https://github.com/ItsSparksFly",
		"author"		=> "sparks fly",
		"authorsite"	=> "https://github.com/ItsSparksFly",
		"version"		=> "3.0",
		"compatibility" => "18*"
	);
}

function inplaytracker_install()
{
    global $db, $lang;
    $lang->load('inplaytracker');

    $db->query("CREATE TABLE ".TABLE_PREFIX."ipt_scenes (
        `sid` int(11) NOT NULL AUTO_INCREMENT,
        `tid` int(11) NOT NULL,
        `location` varchar(140) NOT NULL,
        `date` varchar(140) NOT NULL,
        `open` tinyint NOT NULL, 
        `shortdesc` varchar(140) NOT NULL,
        PRIMARY KEY (`sid`),
        KEY `lid` (`sid`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1");
	
     $db->query("CREATE TABLE ".TABLE_PREFIX."ipt_scenes_partners (
        `spid` int(11) NOT NULL AUTO_INCREMENT,
        `tid` int(11) NOT NULL,
        `uid` int(11) NOT NULL,
        PRIMARY KEY (`spid`),
        KEY `lid` (`spid`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1");

     $setting_group = [
		'name' => 'inplaytracker',
		'title' => $lang->ipt_name,
		'description' => $lang->ipt_settings,
		'disporder' => 5,
		'isdefault' => 0
	];

	$gid = $db->insert_query("settinggroups", $setting_group);
	
	$setting_array = [
		'inplaytracker_inplay' => [
			'title' => $lang->ipt_inplay,
			'description' => $lang->ipt_inplay_description,
			'optionscode' => 'forumselect',
			'disporder' => 1
        ],
        'inplaytracker_archive' => [
			'title' => $lang->ipt_archive,
			'description' => $lang->ipt_archive_description,
			'optionscode' => 'forumselect',
			'disporder' => 2
		]
    ];

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
	if($db->table_exists("ipt_scenes"))
	{
		return true;
	}

	return false;
}

function inplaytracker_uninstall()
{
	global $db;

    $db->query("DROP TABLE ".TABLE_PREFIX."ipt_scenes");
    $db->query("DROP TABLE ".TABLE_PREFIX."ipt_scenes_partners");

	$db->delete_query('settings', "name IN ('inplaytracker_inplay', 'inplaytracker_archive')");
	$db->delete_query('settinggroups', "name = 'inplaytracker'");

	rebuild_settings();

}

function inplaytracker_activate()
{
    global $db, $cache;

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
    
    // create templates
    $inplaytracker_newthread = [
        'title'        => 'inplaytracker_newthread',
        'template'    => $db->escape_string('<tr>
        <td class="tcat" colspan="2">
            <strong>{$lang->ipt_newthread_options}</strong>
        </td>
    </tr>
    <tr>
            <td class="trow1" width="20%">
                <strong>{$lang->ipt_newthread_partners}</strong>
            </td>
            <td class="trow1">
                <span class="smalltext">
                    <input type="text" class="textbox" name="partners" id="partners" size="40" maxlength="1155" value="{$partners}" style="min-width: 347px; max-width: 100%;" /> <br />
                    {$lang->ipt_newthread_partners_description}
                </span> 
            </td>
        </tr>
        <tr>
            <td class="trow1" width="20%">
                <strong>{$lang->ipt_newthread_date}</strong>
            </td>
            <td class="trow1">
                <select name="day">{$day_bit}</select> 
                <select name="month">{$month_bit}</select>
                <input type="text" name="year" value="{$ipyear}" style="width: 55px;" class="textbox" />			
            </td>
        </tr>
        <tr>
            <td class="trow1" width="20%">
                <strong>{$lang->ipt_newthread_location}</strong>
            </td>
            <td class="trow1">
                <input type="text" class="textbox" name="iport" size="40" maxlength="155" value="{$iport}" /> 
            </td>
        </tr>
        <tr>
            <td class="trow1" width="20%">
                <strong>{$lang->ipt_newthread_private}</strong>
            </td>
            <td class="trow1">
                <span class="smalltext">
                <select name="private" class="select">{$private_bit}</select>
                <br />
                 {$lang->ipt_newthread_private_description}
                </span>
            </td>
        </tr>
        <tr>
            <td class="trow1" width="20%">
                <strong>{$lang->ipt_newthread_description}</strong>
            </td>
            <td class="trow1">
                <span class="smalltext">
                    <textarea class="textarea" name="description" maxlength="140" style="min-width: 347px; max-width: 100%; height: 80px;">{$ipdescription}</textarea>
                <br />
                 {$lang->ipt_newthread_description_description}
                </span>
            </td>
        </tr>
    <tr>
        <td class="tcat" colspan="2">
            <strong>Themen-Optionen</strong>
        </td>
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
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    ];

    // TODO: bisschen aufhübschen?
    $inplaytracker_postbit = [
        'title'        => 'inplaytracker_postbit',
        'template'    => $db->escape_string('<br /><br />
        <center>
            <div class="thead">{$thread[\'subject\']}</div>
            <div class="smalltext" style="font-size: 9px; line-height: 1.3em; text-transform: uppercase;">
                {$partnerlist} <br /> am <strong>{$scene[\'playdate\']}</strong>
                <div style="margin: 2px auto; width: 35%; text-align: center;font-weight: bold; letter-spacing: 1px ">{$scene[\'shortdesc\']}</div>
            </div> 
        </center>
        <br /><br />'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    ];

    // TODO: member_profile-Variable an MyBB-Default-Profil anpassen
    $inplaytracker_member_profile = [
        'title'        => 'inplaytracker_member_profile',
        'template'    => $db->escape_string(''),
        'sid'        => '-1',
        'version'    => '<div class="m-tab-content">
        <div id="inplaytracker">
		{$scenes_bit}
</div>
</div>',
        'dateline'    => TIME_NOW
    ];

    $inplaytracker_member_profile_bit = [
        'title'        => 'inplaytracker_member_profile_bit',
        'template'    => $db->escape_string(''),
        'sid'        => '-1',
        'version'    => '<div class="ipbit">
        <table cellspacing="2px" cellpadding="0px" width="100%" style="font-size: 9px;">
            <tr>
                <td class="date">
                    {$ipdate}
                </td>
                <td class="subject">
                    <a href="showthread.php?tid={$thread[\'tid\']}" class="{$displaygroup}">{$thread[\'subject\']}</a>
                </td>
            </tr>
            <tr>
                <td class="date">
                    Cast
                </td>
                <td class="players">
                    {$user_bit}
                </td>
            </tr>
            <tr>
                <td colspan="2" class="shortdesc">
                    {$scene[\'shortdesc\']}
                </td>
            </tr>		
        </table>
    </div>',
        'dateline'    => TIME_NOW
    ];

    $inplaytracker_member_profile_bit_user = [
        'title'        => 'inplaytracker_member_profile_bit_user',
        'template'    => $db->escape_string('<div class="player">
        {$partnerlink}
    </div>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    ];

    $inplaytracker_header = [
        'title'        => 'inplaytracker_header',
        'template'    => $db->escape_string('<a href="misc.php?action=inplaytracker">{$lang->ipt_header_tracker} (<strong>{$openscenes}</strong>/{$countscenes})</a>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    ];

    $inplaytracker_misc = [
        'title'        => 'inplaytracker_misc',
        'template'    => $db->escape_string('<html>
        <head>
            <title>{$mybb->settings[\'bbname\']} - {$lang->ipt}</title>
            {$headerinclude}
        </head>
        <body>
            {$header}
                <table width="100%" cellspacing="5" cellpadding="5">
                    <tr>
                        <td valign="top" class="trow1">
                                {$user_bit}
                        </td>
                    </tr>
                </table>
            </div>
        {$footer}
        </body>
    </html>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    ];

    $inplaytracker_misc_bit = [
        'title'        => 'inplaytracker_misc_bit',
        'template'    => $db->escape_string('	<div id="forumdisplay">
		<div id="filmstreifen"></div>
		<div id="streifen"></div>
		<div class="name">{$user[\'username\']}</div>
		<div class="description">{$charscenes} {$lang->ipt_header_tracker}, {$charopenscenes} davon offen</div>
		<div class="line"></div>
	</div>
	{$scene_bit}'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    ];

    $inplaytracker_misc_bit_scene = [
        'title'        => 'inplaytracker_misc_bit_scene',
        'template'    => $db->escape_string('<div class="threadlist">
        <table width="100%">
            <tr>
                <td width="60%" valign="middle">
                    <a href="showthread.php?tid={$thread[\'tid\']}" class="threadlink">{$thread[\'subject\']}</a>
                    <br /><span class="threadauthor">{$thread[\'profilelink\']}</span>
                </td>
                <td width="40%" valign="middle" align="right">
                    <strong>LASTPOST</strong>
                        <table>
            <tr>
                <td><span class="lastpostname">DATE</span></td>
                <td><div class="lastpostline" style="width:70px;"></div></td>
                <td><span class="threadauthor">{$lastpostdate}</span></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><span class="lastpostname">DIRECTOR</span></td>
                <td><div class="lastpostline"></div></td>
                <td><span class="threadauthor"><a href="member.php?action=profile&uid={$thread[\'lastposteruid\']}">{$thread[\'lastposter\']}</a></span></td>
            </tr>
        </table>
                </td>
            </tr>
        </table>
    </div>'),
        'sid'        => '-1',
        'version'    => '',
        'dateline'    => TIME_NOW
    ];

    $db->insert_query("templates", $inplaytracker_newthread);
    $db->insert_query("templates", $inplaytracker_postbit);
    $db->insert_query("templates", $inplaytracker_member_profile);
    $db->insert_query("templates", $inplaytracker_member_profile_bit);
    $db->insert_query("templates", $inplaytracker_member_profile_bit_user);
    $db->insert_query("templates", $inplaytracker_header);
    $db->insert_query("templates", $inplaytracker_misc);
    $db->insert_query("templates", $inplaytracker_misc_bit);
    $db->insert_query("templates", $inplaytracker_misc_bit_scene);
	
	include MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("newthread", "#".preg_quote('{$loginbox}')."#i", '{$loginbox} {$newthread_inplaytracker}');
    find_replace_templatesets("editpost", "#".preg_quote('{$loginbox}')."#i", '{$loginbox} {$editpost_inplaytracker}');
    find_replace_templatesets("postbit", "#".preg_quote('{$post[\'message\']}')."#i", '{$post[\'inplaytracker\']} {$post[\'message\']}');
    find_replace_templatesets("postbit_classic", "#".preg_quote('{$post[\'message\']}')."#i", '{$post[\'inplaytracker\']} {$post[\'message\']}');
    // TODO: An Standard-MyBB-Profil anpassen
    find_replace_templatesets("member_profile", "#".preg_quote('<label for="m-tab-2"><i class="far fa-calendar-alt"></i></label>')."#i", '<label for="m-tab-2"><i class="far fa-calendar-alt"></i></label> {$member_profile_inplaytracker}');
    
}

function inplaytracker_deactivate()
{
    global $db, $cache;
    
	if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
		$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

		if (!$alertTypeManager) {
			$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
		}

		$alertTypeManager->deleteByCode('inplaytracker_newthread');
		$alertTypeManager->deleteByCode('inplaytracker_newreply');
	}

	include MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("newthread", "#".preg_quote('{$newthread_inplaytracker}')."#i", '', 0);
    find_replace_templatesets("editpost", "#".preg_quote('{$editpost_inplaytracker}')."#i", '', 0);

	$db->delete_query("templates", "title LIKE 'inplaytracker%'");
}

function inplaytracker_newthread()
{
    global $mybb, $lang, $templates, $post_errors, $forum, $newthread_inplaytracker;
    $lang->load('inplaytracker');

    $newthread_inplaytracker = "";

    // insert inplaytracker options
    $forum['parentlist'] = ",".$forum['parentlist'].",";   
    $selectedforums = explode(",", $mybb->settings['inplaytracker_inplay']);

    foreach($selectedforums as $selected) {
        if(preg_match("/,$selected,/i", $forum['parentlist'])) {
            // previewing new thread?
            if(isset($mybb->input['previewpost']) || $post_errors) {
                $partners = htmlspecialchars_uni($mybb->get_input('partners'));
                $iport = htmlspecialchars_uni($mybb->get_input('iport'));
                $ipdescription = htmlspecialchars_uni($mybb->get_input('description'));
                $ipday = (int)$mybb->get_input('day');
                $ipmonth = htmlspecialchars_uni($mybb->get_input('month'));
                $ipyear = (int)$mybb->get_input('year');
                $ipprivate = (int)$mybb->get_input('private');
            }

            // set up date options
            $day_bit = "";
            for($i = 1 ; $i < 32 ; $i++) {
                $selected = "";
                if($ipday == $i) {
                    $selected = "selected";
                }
                $day_bit .= "<option value=\"$i\" {$selected}>$i</option>";
            }
            
            $months = array(
                "January" => $lang->ipt_month_january,
                "February" => $lang->ipt_month_february,
                "March" => $lang->ipt_month_march,
                "April" => $lang->ipt_month_april,
                "May" => $lang->ipt_month_may,
                "June" => $lang->ipt_month_june,
                "July" => $lang->ipt_month_july,
                "August" => $lang->ipt_month_august,
                "September" => $lang->ipt_month_september,
                "October" => $lang->ipt_month_october,
                "November" => $lang->ipt_month_november,
                "December" => $lang->ipt_month_december
            );
            $month_bit = "";
            foreach($months as $key => $month) {
                $selected = "";
                if($ipmonth == $key) {
                    $selected = "selected";
                }
                $month_bit .= "<option value=\"$key\" {$selected}>$month</option>";
            }

            // is this thread public?
            $private_bit = "";
            $private = array("0" => "{$lang->ipt_newthread_private_closed}", "1" => "{$lang->ipt_newthread_private_open}");
            foreach($private as $key => $value) {
                $selected = "";
                if($key == $ipprivate) {
                    $selected = "selected";
                }
                $private_bit .= "<option value=\"{$key}\" {$selected}>{$value}</option>";
            }
           eval("\$newthread_inplaytracker = \"".$templates->get("inplaytracker_newthread")."\";");
        }
    }
}

function inplaytracker_do_newthread() {
    global $db, $mybb, $tid, $partners_new, $partner_uid;
    
    # FIXME: hier war noch was mit ' escapen.
    $ownuid = $mybb->user['uid'];
    if(!empty($mybb->get_input('partners'))) {
        // insert thread infos into database   
        $ipdate = strtotime($mybb->get_input('day')." ".$mybb->get_input('month')." ".$mybb->get_input('year'));
        $new_record = [
            "date" => $ipdate,
            "location" => $db->escape_string($mybb->get_input('iport')),
            "shortdesc" => $db->escape_string($mybb->get_input('description')),
            "open" => (int)$mybb->get_input('private'),
            "tid" => (int)$tid
        ];
        $db->insert_query("ipt_scenes", $new_record);
        
        // write scenes + players into database
        $new_record = [
            "tid" => (int)$tid,
            "uid" => (int)$ownuid
        ];
        $db->insert_query("ipt_scenes_partners", $new_record);
        $partners_new = explode(",", $mybb->get_input('partners'));
		$partners_new = array_map("trim", $partners_new);
		foreach($partners_new as $partner) {
			$db->escape_string($partner);
            $partner_uid = $db->fetch_field($db->query("SELECT uid FROM ".TABLE_PREFIX."users WHERE username = '$partner'"), "uid");
            $new_record = [
                "tid" => (int)$tid,
                "uid" => (int)$partner_uid
            ];
            $db->insert_query("ipt_scenes_partners", $new_record);

		}
    }
}

function inplaytracker_editpost() {

    global $mybb, $db, $lang, $templates, $post_errors, $forum, $thread, $pid, $editpost_inplaytracker;
    $lang->load('inplaytracker');
    
    $editpost_inplaytracker = "";

    // insert inplaytracker options
    $forum['parentlist'] = ",".$forum['parentlist'].",";   
    $all_forums = $mybb->settings['inplaytracker_inplay'].",".$mybb->settings['inplaytracker_archive'];
    $selectedforums = explode(",", $all_forums);

    foreach($selectedforums as $selected) {
        if(preg_match("/,$selected,/i", $forum['parentlist'])) {
        $pid = $mybb->get_input('pid', MyBB::INPUT_INT);
        if($thread['firstpost'] == $pid) {
            $query = $db->simple_select("ipt_scenes", "*", "tid='{$thread['tid']}'");
            $scene = $db->fetch_array($query);
            if(isset($mybb->input['previewpost']) || $post_errors) {
                $partners = htmlspecialchars_uni($mybb->get_input('partners'));
                $iport = htmlspecialchars_uni($mybb->get_input('iport'));
                $ipdescription = htmlspecialchars_uni($mybb->get_input('description'));
                $ipdate = strtotime($mybb->get_input('day')." ".$mybb->get_input('month')." ".$mybb->get_input('year'));
            }
            else
            {
                $query = $db->simple_select("ipt_scenes_partners", "uid", "tid='{$thread['tid']}'");
                $partners = [];
                while($result = $db->fetch_array($query)) {
                    $tagged_user = get_user($result['uid']);
                    $partners[] = $tagged_user['username'];
                }
                $partners = implode(",", $partners);
                $ipdate = htmlspecialchars_uni($scene['date']);
                $iport = htmlspecialchars_uni($scene['location']);
                $ipdescription = htmlspecialchars_uni($scene['shortdesc']);
            }

            $day_bit = "";
            for($i = 1 ; $i < 32 ; $i++) {
                $checked_day = "";
                $active_day = date("j", $ipdate);
                if($active_day == $i) {
                    $checked_day = "selected=\"selected\"";
                }
                $day_bit .= "<option value=\"$i\" {$checked_day}>$i</option>";
            }

            $months = array(
                "January" => $lang->ipt_month_january,
                "February" => $lang->ipt_month_february,
                "March" => $lang->ipt_month_march,
                "April" => $lang->ipt_month_april,
                "May" => $lang->ipt_month_may,
                "June" => $lang->ipt_month_june,
                "July" => $lang->ipt_month_july,
                "August" => $lang->ipt_month_august,
                "September" => $lang->ipt_month_september,
                "October" => $lang->ipt_month_october,
                "November" => $lang->ipt_month_november,
                "December" => $lang->ipt_month_december
            );

            $month_bit = "";
            foreach($months as $key => $month) {
                $checked_month = "";
                $active_month = date("F", $ipdate);
                if($active_month == $key) {
                    $checked_month = "selected=\"selected\"";
                }
                $month_bit .= "<option value=\"$key\" {$checked_month}>$month</option>";
            }

            $ipyear = date("Y", $ipdate);

            $private = array("0" => "{$lang->ipt_newthread_private_closed}", "1" => "{$lang->ipt_newthread_private_open}");
            foreach($private as $key => $value) {
                $checked = "";
                if($scene['open'] == $key) {
                    $checked = "selected=\"selected\"";
                }
                $private_bit .= "<option value=\"$key\" {$checked}>$value</option>";
            }
            eval("\$editpost_inplaytracker = \"".$templates->get("inplaytracker_newthread")."\";");
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

    // write partners into database
    if(!empty($mybb->get_input('partners'))) {
        $db->delete_query("ipt_scenes_partners", "tid='{$tid}'");

        $partners_new = explode(",", $mybb->get_input('partners'));
        $partners_new = array_map("trim", $partners_new);
        foreach($partners_new as $partner) {
            $db->escape_string($partner);
            $partner_uid = $db->fetch_field($db->query("SELECT uid FROM ".TABLE_PREFIX."users WHERE username = '$partner'"), "uid");
            $new_record = [
                "tid" => (int)$tid,
                "uid" => (int)$partner_uid
            ];
            $db->insert_query("ipt_scenes_partners", $new_record);
        }

        $ipdate = strtotime($mybb->input['day']." ".$mybb->input['month']." ".$mybb->input['year']);
        
        $new_record = [
            "date" => $ipdate,
            "location" => $db->escape_string($mybb->get_input('iport')),
            "shortdesc" => $db->escape_string($mybb->get_input('description')),
            "open" => (int)$mybb->get_input('private')
        ];

        $db->update_query("ipt_scenes", $new_record, "tid='{$tid}'");
    }
}

function inplaytracker_forumdisplay(&$thread)
{
    global $db, $lang, $mybb, $thread, $foruminfo;
	$lang->load('inplaytracker');

    $foruminfo['parentlist'] = ",".$foruminfo['parentlist'].",";   
    $all_forums = $mybb->settings['inplaytracker_inplay'].",".$mybb->settings['inplaytracker_archive'];
    $selectedforums = explode(",", $all_forums);

    foreach($selectedforums as $selected) {
        if(preg_match("/,$selected,/i", $foruminfo['parentlist'])) {
            $query = $db->simple_select("ipt_scenes_partners", "uid", "tid = '{$thread['tid']}'");
            $partnerusers = [];
            while($partners = $db->fetch_array($query)) {
                $charakter = get_user($partners['uid']);
                $taguser = build_profile_link($charakter['username'], $partners['uid']);
                $partnerusers[] = $taguser;
            }
            $partnerusers = implode(" &bull; ", $partnerusers);
            $ipdate = date("d.m.Y", $db->fetch_field($db->simple_select("ipt_scenes", "date", "tid = '{$thread['tid']}'"), "date"));
            $ipdescription = $db->fetch_field($db->simple_select("ipt_scenes", "shortdesc", "tid = '{$thread['tid']}'"), "shortdesc");
            $thread['profilelink'] =  "<b>{$lang->ipt_forumdisplay_characters}:</b> $partnerusers <br /> <b>{$lang->ipt_forumdisplay_date}:</b> $ipdate<br />
            <b>{$ipdescription}</b>";
            return $thread;
        }
    } 
}

function inplaytracker_postbit(&$post) {
    global $db, $mybb, $lang, $templates, $pid, $tid;
    $lang->load("inplaytracker");
 
    $thread = get_thread($tid);
    $foruminfo = get_forum($thread['fid']);
    $foruminfo['parentlist'] = ",".$foruminfo['parentlist'].",";   
    $all_forums = $mybb->settings['inplaytracker_inplay'].",".$mybb->settings['inplaytracker_archive'];
    $selectedforums = explode(",", $all_forums);

    foreach($selectedforums as $selected) {
        if(preg_match("/,$selected,/i", $foruminfo['parentlist'])) {   
            if($pid == $thread['firstpost']) {
                $query = $db->simple_select("ipt_scenes", "*", "tid='{$tid}'");
                $scene = $db->fetch_array($query);
                $scene['playdate'] = date("d.m.Y", $scene['date']);
                $query = $db->simple_select("ipt_scenes_partners", "uid", "tid='{$tid}'");
                while($partners = $db->fetch_array($query)) {
                    $partner = get_user($partners['uid']);
                    $username = format_name($partner['username'], $partner['usergroup'], $partner['displaygroup']);
                    $partnerlink = build_profile_link($username, $partner['uid']);
                    $partnerlist .= "&nbsp; &nbsp;".$partnerlink;
                }
            }
            eval("\$post['inplaytracker'] = \"".$templates->get("inplaytracker_postbit")."\";");
            return $post;
        }
    }
}

function inplaytracker_profile() {
    global $db, $mybb, $lang, $templates, $memprofile, $user_bit, $scenes_bit, $member_profile_inplaytracker;
    $lang->load('inplaytracker');

    $scenes_bit = "";
    $member_profile_inplaytracker = "";

    // get all scenes user is involved
    $query = $db->query("SELECT ".TABLE_PREFIX."ipt_scenes.tid FROM ".TABLE_PREFIX."ipt_scenes_partners
                        LEFT JOIN ".TABLE_PREFIX."ipt_scenes
                        ON ".TABLE_PREFIX."ipt_scenes.tid = ".TABLE_PREFIX."ipt_scenes_partners.tid
                        WHERE uid = '{$memprofile['uid']}'
                        ORDER BY date ASC");
    while($scenelist = $db->fetch_array($query)) {
        $thread = get_thread($scenelist['tid']);
        if($thread) {
            // get infos for scene
            $query_2 = $db->simple_select("ipt_scenes", "*", "tid = '{$thread['tid']}'");
            $scene = $db->fetch_array($query_2);
            $ipdate = date("d.m.Y", $scene['date']);
            // get all users in scene
            $query_3 = $db->simple_select("ipt_scenes_partners", "uid", "tid = '{$thread['tid']}'");
            $user_bit = "";
            while($users = $db->fetch_array($query_3)) {
                $partner = get_user($users['uid']);
                $username = format_name($partner['username'], $partner['usergroup'], $partner['displaygroup']);
                $partnerlink = build_profile_link($username, $partner['uid']);
                eval("\$user_bit .= \"".$templates->get("inplaytracker_member_profile_bit_user")."\";");
            }
            eval("\$scenes_bit .= \"".$templates->get("inplaytracker_member_profile_bit")."\";");
        }
    }
    eval("\$member_profile_inplaytracker = \"".$templates->get("inplaytracker_member_profile")."\";");
}

function inplaytracker_global() {
    global $db, $mybb, $lang, $templates, $header_inplaytracker;
    $lang->load('inplaytracker');
    $header_inplaytracker = "";
    $openscenes = 0;
    $countscenes = 0;

    // get all users that are linked via account switcher
    $as_uid = $db->fetch_field($db->simple_select("users", "as_uid", "uid = '{$mybb->user['uid']}'"), "as_uid");

    if(empty($as_uid)) {
        $as_uid = $mybb->user['uid'];
    }
    # FIXME: Keine Entwürfe mitzählen!
    $query = $db->simple_select("users", "uid", "uid = '{$as_uid}' OR as_uid = '{$mybb->user['uid']}'");
    while($userlist = $db->fetch_array($query)) {
        // get all scenes for this uid...
        $query_2 = $db->simple_select("ipt_scenes_partners", "tid", "uid = '{$userlist['uid']}'");
        while($scenelist = $db->fetch_array($query_2)) {
            // get thread infos
            $thread = get_thread($scenelist['tid']);
            if($thread) {
                $lastposter = $thread['lastposteruid'];
                // get spid matching lastposteruid
                $lastposter_spid = $db->fetch_field($db->simple_select("ipt_scenes_partners", "spid", "uid = '{$lastposter}' AND tid = '{$thread['tid']}'"), "spid");
                // now that we've got the spid, we can hopefully see who is next in line
                $next = $lastposter_spid + 1;
                $next_uid = $db->fetch_field($db->simple_select("ipt_scenes_partners", "uid", "tid = '{$thread['tid']}' AND spid = '{$next}'"), "uid");
                if(empty($next_uid)) {
                    $next_uid = $db->fetch_field($db->simple_select("ipt_scenes_partners", "uid", "tid = '{$thread['tid']}'", [ "order_by" => 'spid', "order_dir" => 'ASC', 'limit' => 1 ]), "uid");
                }
                if($next_uid == $userlist['uid']) {
                    $openscenes++;
                }
                $countscenes++;
            }
        }
    }
    eval("\$header_inplaytracker = \"".$templates->get("inplaytracker_header")."\";");
}

function inplaytracker_misc() {
    global $db, $mybb, $lang, $templates, $headerinclude, $header, $footer;
    $lang->load('inplaytracker');   
    $page = "";
    

    $mybb->input['action'] = $mybb->get_input('action');
	if($mybb->input['action'] == "inplaytracker") {
        // get all users that are linked via account switcher
        $as_uid = $db->fetch_field($db->simple_select("users", "as_uid", "uid = '{$mybb->user['uid']}'"), "as_uid");
        if(empty($as_uid)) {
            $as_uid = $mybb->user['uid'];
        }
        # FIXME: Keine Entwürfe mitzählen!
        $query = $db->simple_select("users", "uid", "uid = '{$as_uid}' OR as_uid = '{$mybb->user['uid']}'");
        $user_bit = "";
        while($userlist = $db->fetch_array($query)) {  
            // get all scenes for this uid...
            $user = get_user($userlist['uid']);
            $query_2 = $db->simple_select("ipt_scenes_partners", "tid", "uid = '{$userlist['uid']}'");
            $scene_bit = "";
            (int)$charscenes = 0;
            (int)$charopenscenes = 0;
            while($scenelist = $db->fetch_array($query_2)) {
                $query_3 = $db->simple_select("ipt_scenes", "*", "tid = '{$scenelist['tid']}'");
                $scene = $db->fetch_array($query_3);
                $thread = get_thread($scene['tid']);
                if($thread) {
                    $query_4 = $db->simple_select("ipt_scenes_partners", "uid", "tid = '{$thread['tid']}'");
                    $partnerusers = [];
                    while($partners = $db->fetch_array($query_4)) {
                        $charakter = get_user($partners['uid']);
                        $taguser = build_profile_link($charakter['username'], $partners['uid']);
                        $partnerusers[] = $taguser;
                    }
                    $partnerusers = implode(" &bull; ", $partnerusers);
                    $ipdate = date("d.m.Y", $scene['date']);
                    $ipdescription = $scene['shortdesc'];
                    $thread['profilelink'] =  "<b>{$lang->ipt_forumdisplay_characters}:</b> $partnerusers <br /> <b>{$lang->ipt_forumdisplay_date}:</b> $ipdate<br />
                    <b>{$ipdescription}</b>";
                    $lastpostdate = date("d.m.Y", $thread['lastpost']);
                    eval("\$scene_bit .= \"".$templates->get("inplaytracker_misc_bit_scene")."\";");
                    $lastposter = $thread['lastposteruid'];
                    // get spid matching lastposteruid
                    $lastposter_spid = $db->fetch_field($db->simple_select("ipt_scenes_partners", "spid", "uid = '{$lastposter}' AND tid = '{$thread['tid']}'"), "spid");
                    // now that we've got the spid, we can hopefully see who is next in line
                    $next = $lastposter_spid + 1;
                    $next_uid = $db->fetch_field($db->simple_select("ipt_scenes_partners", "uid", "tid = '{$thread['tid']}' AND spid = '{$next}'"), "uid");
                    if(empty($next_uid)) {
                        $next_uid = $db->fetch_field($db->simple_select("ipt_scenes_partners", "uid", "tid = '{$thread['tid']}'", [ "order_by" => 'spid', "order_dir" => 'ASC', 'limit' => 1 ]), "uid");
                    }
                    if($next_uid == $userlist['uid']) {
                        $charopenscenes++;
                    }
                    $charscenes++;
                }
            } 
            eval("\$user_bit .= \"".$templates->get("inplaytracker_misc_bit")."\";");          
        }
        eval("\$page = \"".$templates->get("inplaytracker_misc")."\";");
        output_page($page);
    }
    if($mybb->input['action'] == "editscene") {
        // TODO: Option, dass alle Mitspieler die Infos bearbeiten können einbauen
        eval("\$page = \"".$templates->get("inplaytracker_editscene")."\";");
        output_page($page);
    }
}

function inplaytracker_do_newreply()
{
	global $db, $mybb, $lang, $thread, $forum;
	$lang->load('inplaytracker');

    $forum['parentlist'] = ",".$forum['parentlist'].",";   
    $all_forums = $mybb->settings['inplaytracker_inplay'];
    $selectedforums = explode(",", $all_forums);
    foreach($selectedforums as $selected) {
        if(preg_match("/,$selected,/i", $forum['parentlist'])) {   
            $query = $db->simple_select("ipt_scenes_partners", "uid", "tid = '{$thread['tid']}'");
            $last_post = $db->fetch_field($db->query("SELECT pid FROM ".TABLE_PREFIX."posts WHERE tid = '$thread[tid]' ORDER BY pid DESC LIMIT 1"), "pid");  
            if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                while($partners = $db->fetch_array($query)) {
                    $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('inplaytracker_newreply');
                    if ($alertType != NULL && $alertType->getEnabled() && $mybb->user['uid'] != $partners['uid']) {
                        $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$partners['uid'], $alertType, (int)$thread['tid']);
                        $alert->setExtraDetails([
                            'subject' => $thread['subject'],
                            'lastpost' => $last_post
                        ]);
                    MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                    }
                }
            }
        }
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

?>