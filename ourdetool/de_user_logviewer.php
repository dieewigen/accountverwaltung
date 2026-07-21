<?php
/**
 * Logviewer für die Zugriffe eines Users (Daten aus gameserverlogdata).
 *
 * @author Rainer Zerbe - rz.php-projects@i-it-s.de (Original 2009)
 *
 * Hinweis: nutzt jQuery 1.3 / jQuery UI 1.7 aus logviewer/jq/ –
 * inkompatibel mit modernem jQuery, daher bewusst NICHT portiert.
 * Die Daten liefert getAjax.php als HTML-Fragmente.
 */
include_once "../inccon.php";
include_once "../inc/sv.inc.php";
include_once "det_userdata.inc.php";
include_once "log_dbconnect.php";

$userID = req_int('uid');
$serverID = (int)$GLOBALS['sv_servid'];
$uid = $userID;

// Legacy-Assets des Logviewers unverändert in den <head> übernehmen
$page_head_extra = <<<'HTML'
<link rel="stylesheet" type="text/css" href="logviewer/css/all.css">
<script type="text/javascript" src="logviewer/jq/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="logviewer/jq/jquery-ui-1.7.custom.min.js"></script>
<script type="text/javascript" src="logviewer/jq/jquery.jHelperTip.1.0.min.js"></script>
<script type="text/javascript">
    $(function(){
        initGui.submit();
        initGui.tabs();
        initGui.tooltip();
        initGui.button();
    });
    var initGui = new function() {
        this.tooltip = function(){
            $(".tooltip").jHelperTip({
                 trigger: "click",
                source: "attribute",
                attrName: "alt",
                opacity: 1,
                autoClose:true

            });
        }
        this.button = function () {
            $('button').unbind('click').bind('click',function(){
                if(typeof submitter[$(this).attr('job')] == 'function') {
                    $('#job').val($(this).attr('job'));
                    $('form[name="allData"]').submit();
                }
                return false;
            });
        }
        this.submit = function() {
            $('form[name="allData"]').submit(function(){
                var data = {};
                $.each($('form[name="allData"] :input'),function(a,b) {
                    if((b.type=='checkbox') && (!b.checked)) return;
                    data[b.name] = b.value;
                });
                loading(true);
                $.get($(this).attr('action'), data, function(d) {
                    loading(false);
                    if(typeof submitter[data.job] == 'function') {
                        submitter[data.job](d);
                    }
                });

                return false;
            });
        }
        this.tabs = function() {
            $('#logViewerTabs').tabs({cache: false,height:'500px' });
        }
    }


    var submitter = new function() {
        this.loadDay = function(d) {
            $(d).appendTo('#daySelect .dayList');
            $('#daySelect .dayList td.remove').unbind('click').bind('click',function() {
                $(this).parent().remove();
            });
            $('#daySelect .dayList td.hour').unbind('click').bind('click',function() {
                $('input[name="startDate"]').val($(this).attr('hour'));
                $('#job').val('loadLog');
                $('form[name="allData"]').submit();
            });
        }
        this.loadLog = function(d) {
            $('#logs div.data').html(d);
            initGui.tooltip();
        }
        this.loadClicks = function(d) {
            $('#config div.data').html(d);
        }
        this.loadTopGenerator = function(d){
            $('#logOverview div.data').html(d);
        }
        this.loadTopSecstat = function(d){
            $('#logOverview div.data').html(d);
        }
        this.loadTopSysnews = function(d){
            $('#logOverview div.data').html(d);
        }
    }
    var loading = function(bool) {
        if(bool) $('#loading').slideDown();
        else $('#loading').stop().slideUp().css({'height':'20px'});
    }
</script>
<style>
    #daySelect .dayList td {
        cursor:pointer;
    }
    [alt] {
        cursor:pointer;
    }
    td.remove {
        cursor:pointer;
    }
    #logViewerTabs table {
        border:0;
    }
    #logViewerTabs table tr.row0{
        background:#000;
    }
    #logViewerTabs table tr.row1{
        background:#222;
    }
    #logViewerTabs table td{
        border:0;
        padding:0 20px;
    }
    #jHelperTipAttrContainer{
        background:#FFFFff;border:1px solid #FF3333; color:#000000; display:none; font-size:9pt;
    }
    .ui-widget-content {
        color:#fff;
    }
</style>
HTML;

$page_title = 'Logviewer';
$active_nav = 'usersearch';
include_once "inc.layout.top.php";
include_once "inc.usertoolbar.php";

if ((!$userID) || (!$serverID)) {
    echo '<div class="flash flash-warn">Bitte uid(userId) UND sid(serverId) angeben</div>';
    include_once "inc.layout.bottom.php";
    exit;
}
?>
        <form name="allData" action="getAjax.php">
            <input type="hidden" name="job" value="" id="job">
            <input type="hidden" name="uid" value="<?= $userID ?>">
            <input type="hidden" name="sid" value="<?= $serverID ?>">
            <div id="daySelect">
<?php

// ersten geloggten Tag des Users als Startdatum vorschlagen
$startDate = date('Y-m-d');
$result = mysqli_execute_query(
    $GLOBALS['dbi_log'],
    "SELECT time FROM gameserverlogdata WHERE serverid = ? AND userid = ? ORDER BY time ASC LIMIT 1",
    [$serverID, $userID]
);
if ($result && ($row = mysqli_fetch_assoc($result)) && !empty($row['time'])) {
    [$startDate] = explode(' ', $row['time']);
}

?>
                <input type="text" name="day" value="<?= htmlspecialchars($startDate) ?>">
                <button job="loadDay">Tag Laden</button>
                <table> <thead> <tr><th>entfernen</th><th>Tag</th>
                            <?php for ($i = 0; $i <= 23; $i++) { echo "<th>$i</th>"; } ?>
                    </tr></thead>
                    <tbody class="dayList">

                </tbody></table>

            </div>

            <div id="logViewerTabs" style="float:left; width:90%; background:url(bg_blue.gif);">
                <ul>
                    <li><a href="#logs"><span>Logs</span></a></li>
                    <li><a href="#config"><span>Konfiguration</span></a></li>
                    <li><a href="#logOverview"><span>Allgemeine Übersichten</span></a></li>
                </ul>


                <div id="logs">
                    Spezial Ansichten, teilweise nur die Aufrufe bei denen auch Daten eingegeben wurden, zB. bei Militär nur wenn Flotten verschickt werden<br>
                    <select name="logType">
                        <option value="all">alle(nach Konfiguration)</option>
                        <option value="communication">Kommunikation</option>
                        <option value="military">Militär</option>
                        <option value="scan">Agenten</option>
                        <option value="militaryscan">Militär und Scan</option>
                        <option value="sekstatsek">Sektor &amp; Sekstatus</option>
                        <option value="bk">BK Menu</option>

                    </select>
                    <button job="loadLog">weiter</button>
                    <div class="data"></div>
                </div>
                <div id="logOverview">
                    <button job="loadTopGenerator"> Top /imagegenerator.php </button>
                    <button job="loadTopSecstat"> Top /secstatus.php </button>
                    <button job="loadTopSysnews"> Top /sysnews.php </button>
                    <div class="data"></div>
                </div>
                <div id="config">
                <button job="loadClicks"> Klicks anzeigen </button><br>
                    <button onclick="$('#config .data input[type=checkbox]').attr('checked','checked');"> alle anwählen</button>
                    <button onclick="$('#config .data input[type=checkbox]').removeAttr('checked');">alle abwählen</button>

                    <div class="data"></div>
                </div>
            </div>
        </form>
        <div id="loading" style="position:absolute; top:0; right:0; width:100px; display:none; background:white; color:black; text-align:center;">
            loading....
        </div>
<?php
include_once "inc.layout.bottom.php";
