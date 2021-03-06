<?php
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
error_reporting(E_ALL);

include_once '../lib/include.php';
include_once 'epiphany/src/Epi.php';
include_once 'controllers/ApiController.php';
include_once 'controllers/MeetingController.php';
include_once 'controllers/DevelopmentApp.php';
include_once 'controllers/LobbyistController.php';
include_once 'controllers/LoginController.php';
include_once 'controllers/UserController.php';
include_once 'controllers/ChartController.php';

Epi::setPath('base', 'epiphany/src');
Epi::init('route');
Epi::init('api');
Epi::init('route','session-php');

getApi()->get('/api/about', array('ApiController', 'about'), EpiApi::external);
getApi()->get('/api/point', array('ApiController', 'point'), EpiApi::external);
getApi()->get('/api/roads/(\d+)/([^/]+)', array('ApiController', 'road'), EpiApi::external);
getApi()->get('/api/roads/(\d+)/([^/]+)/(.*)', array('ApiController', 'road'), EpiApi::external);
getApi()->get('/api/wards/(\d+)', array('ApiController', 'ward'), EpiApi::external);
getApi()->get('/api/wards/(\d+)/polls', array('ApiController', 'wardPolls'), EpiApi::external);
getApi()->get('/api/wards/(\d+)/polls/(\d+)/([\d-\.]+)', array('ApiController', 'wardPoll'), EpiApi::external);
getApi()->get('/api/wards/(\d+)/polls/(\d+)/([\d-\.]+)/map/live', array('ApiController', 'wardPollMapLive'), EpiApi::external);
getApi()->get('/api/wards/(\d+)/polls/(\d+)/([\d-\.]+)/map/static', array('ApiController', 'wardPollMapStatic'), EpiApi::external);
getRoute()->get('/api/wards/(\d+)/polls/(\d+)/([\d-\.]+)/map/img', array('ApiController', 'wardPollMapStatic302'), EpiApi::external);
getApi()->get('/api/wards', array('ApiController', 'listWards'), EpiApi::external);
getApi()->get('/api/committees', array('ApiController', 'committees'), EpiApi::external);
getApi()->get('/api/councillors/(\d+)', array('ApiController', 'councillorById'), EpiApi::external);
getApi()->get('/api/councillors/([^/]+)/(.*)', array('ApiController', 'councillorByName'), EpiApi::external);

getApi()->get('/api/devapps/all', array('ApiController', 'devAppAll'), EpiApi::external);
getApi()->get('/api/devapps/([D_].*)', array('ApiController', 'devApp'), EpiApi::external);

getRoute()->get('/', 'dashboard');
getRoute()->get('/about', 'about');
getRoute()->get('/ideas', 'ideas');
#getRoute()->get('/dashboard', 'dashboard');

getRoute()->get('/user/home', array('UserController','home'));
getRoute()->post('/user/add/place', array('UserController','addPlace'));

getRoute()->get('/user/register', array('LoginController','displayRegister'));
getRoute()->post('/user/register', array('LoginController','doRegister'));
getRoute()->get('/user/login', array('LoginController','display'));
getRoute()->post('/user/login', array('LoginController','doLogin'));
getRoute()->get('/user/logout', array('LoginController','logout'));

getRoute()->get('/lobbying/latereport', array('LobbyistController','latereport'));
getRoute()->get('/lobbying/search/(.*)', array('LobbyistController','search'));
getRoute()->get('/lobbying/lobbyists/(.*)', array('LobbyistController','showLobbyist'));
getRoute()->get('/lobbying/clients/(.*)', array('LobbyistController','showClient'));
getRoute()->get('/lobbying/thelobbied/(.*)', array('LobbyistController','showLobbied'));
getRoute()->get('/lobbying/files/(.*)', array('LobbyistController','showFile'));
getRoute()->get('/lobbyist/([^\/]*)', 'lobbyist'); # legacy REST location

#getRoute()->get('/lobbyist/(.*)/details', 'lobbyistDetails');
#getRoute()->get('/lobbyist/(.*)/link', 'lobbyistLink');

getRoute()->get('/devapps', array('DevelopmentAppController','listAll'));
getRoute()->get('/devapps/([^\/]+)', array('DevelopmentAppController','viewDevApp'));

getRoute()->get('/meetings/votes', array('MeetingController','votesIndex'));
getRoute()->get('/meetings/votes/member/([^\/]*)', array('MeetingController','votesMember'));

getRoute()->get('/meetings/calendar', array('MeetingController','calendarView'));
getRoute()->get('/meetings/calendar.ics', array('MeetingController','calendar'));
getRoute()->get('/meetings/file/(\d+)', array('MeetingController','getFileCacheUrl'));
getRoute()->get('/meetings', array('MeetingController','dolist')); // meetings
getRoute()->get('/meetings/([^\/]*)', array('MeetingController','dolist')); // meetings/CATEGORY
getRoute()->get('/meetings/meetid/(\d+)', array('MeetingController','meetidForward')); // meetings/CATEGORY/ID
getRoute()->get('/meetings/([^\/]*)/(\d+)', array('MeetingController','meetingDetails')); // meetings/CATEGORY/ID
getRoute()->get('/meetings/([^\/]*)/(\d+)/item/(\d+)', array('MeetingController','meetingDetails')); // meetings/CATEGORY/ID
getRoute()->get('/meetings/([^\/]*)/(\d+)/item/(\d+)/(files|files.json)', array('MeetingController','itemFiles')); // meetings/CATEGORY/ID

getRoute()->get('/chart/test', array('ChartController','test'));
getRoute()->get('/chart/lobbying/daily', array('ChartController','lobbyingDaily'));

getRoute()->get('.*', 'error404');
getRoute()->run();

function dashboard() {
  // This is the Home Page Content
  global $OTT_WWW;
  top();
  ?>
  <div class="row-fluid">
  <div class="span4">
  <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
  <?php 
  $meetings = getDatabase()->all(" select id,meetid,category,date(starttime) starttime from meeting where date(starttime) = date(CURRENT_TIMESTAMP) order by starttime ");
  if (count($meetings) > 0) {
    ?>
    <tr>
    <td colspan="3">
    <h4>Today's Meetings</h4>
    </td>
    </tr>
    <?php
    foreach ($meetings as $m) {
      $mtgurl = htmlspecialchars("http://sire.london.ca/mtgviewer.aspx?meetid={$m['meetid']}&doctype");
      ?>
      <tr>
        <td><?php print meeting_category_to_title($m['category']); ?></td>
        <td style="text-align: center; width: 90px;"><?php print $m['starttime']; ?></td>
        <td style="text-align: center;"><a class="btn btn-mini" href="<?php print "$OTT_WWW/meetings/{$m['category']}/{$m['meetid']}"; ?>">Agenda</a></td>
      </tr>
      <?php
    }
  }
  # sometimes ottawa.ca ppl create meetings *way* in advance for testing purposes.
  # only look 2 months in advance. Typically meetings aren't created until 2 wks in advance anyway
  $meetings = getDatabase()->all(" select id,category,date(starttime) starttime,meetid from meeting where date(starttime) > date(CURRENT_TIMESTAMP) and datediff(starttime,current_timestamp()) < 60 order by starttime ");
  if (count($meetings) > 0) {
    ?>
    <tr>
    <td colspan="3">
    <h4>Upcoming Meetings</h4>
    </td>
    </tr>
    <?php
    foreach ($meetings as $m) {
      $mtgurl = htmlspecialchars("http://sire.london.ca/mtgviewer.aspx?meetid={$m['meetid']}&doctype");
      ?>
      <tr>
        <td><?php print meeting_category_to_title($m['category']); ?></td>
        <td style="text-align: center; width: 90px;"><?php print $m['starttime']; ?></td>
        <td style="text-align: center;"><a class="btn btn-mini" href="<?php print "$OTT_WWW/meetings/{$m['category']}/{$m['meetid']}"; ?>">Agenda</a></td>
      </tr>
      <?php
    }
  }
  ?>
  <tr>
  <td colspan="3">
  <h4>Previous Meetings</h4>
  </td>
  </tr>
  <?php
  $meetings = getDatabase()->all(" select id,meetid,category,date(starttime) starttime from meeting where date(starttime) < date(CURRENT_TIMESTAMP) order by starttime desc limit 15 ");
  foreach ($meetings as $m) {
    $mtgurl = htmlspecialchars("http://sire.london.ca/mtgviewer.aspx?meetid={$m['meetid']}&doctype");
    ?>
    <tr>
      <td><?php print meeting_category_to_title($m['category']); ?></td>
      <td style="text-align: center; width: 90px;"><?php print $m['starttime']; ?></td>
      <td style="text-align: center;"><a class="btn btn-mini" href="<?php print "$OTT_WWW/meetings/{$m['category']}/{$m['meetid']}"; ?>">Agenda</a></td>
    </tr>
    <?php
  }
  ?>
  <tr>
  <td colspan="3">  
  <a class="btn-mini btn" href="<?php print $OTT_WWW; ?>/meetings/all"><i class="icon-list"></i> All Meetings</a>
  <a class="btn-mini btn" href="<?php print $OTT_WWW; ?>/meetings/calendar"><i class="icon-calendar"></i> Calendar</a>
  </td>
  </tr>
  </table>
  </div>

  <div class="span4">
  <?php 
    //$url = "http://www.ldnpressreleases.tumblr.com/rss";
    $url = "http://www.feedyes.com/feed.php?f=97uC9k92aVc03l17";
    $rss = file_get_contents($url);
    $xml = simplexml_load_string($rss);
    if (!is_object($xml)) {
      # could not load RSS; just fail silently
      print "<h4>London.ca News</h4>\n";
      print "<i>Could not load media releases. Probably a temporary error.</i>";
      return;
    }
    $items = $xml->xpath('//item');
    print "<h4>London.ca News</h4>\n";
    $max = 7;
    $x = 0;
    foreach ($items as $item) {
      if ($x++ < $max) {
        $title = $item->xpath("title"); $title = $title[0].'';
        $link = $item->xpath("link"); $link = $link[0].'';
        $description = $item->xpath("description"); $description = $description[0].'';
        $description = strip_tags($description);
        $string = substr($description,0,252).'...';
        print "<p><a href=\"$link\" target=\"_blank\">$title</a></p><small>". $string . "</small><hr />\n";
      }
    }
  ?>
  <?php 
    /*$url = "http://www.ldnplanning.tumblr.com/rss";
    $rss = file_get_contents($url);
    $xml = simplexml_load_string($rss);
    if (!is_object($xml)) {
      # could not load RSS; just fail silently
      print "<h4>Planning Notices</h4>\n";
      print "<i>Could not load planning notices. Probably a temporary error.</i>";
      return;
    }
    $items = $xml->xpath('//item');
    print "<h4>Planning Notices</h4>\n";
    $max = 4;
    $x = 0;
    foreach ($items as $item) {
      if ($x++ < $max) {
        $title = $item->xpath("title"); $title = $title[0].'';
        $link = $item->xpath("link"); $link = $link[0].'';
        $description = $item->xpath("description"); $description = $description[0].'';
        $description = strip_tags($description);
        $string = substr($description,0,252).'...';
        print "<p><a href=\"$link\" target=\"_blank\">$title</a></p><small>". $string . "</small><hr />\n";
      }
    }*/
  ?>  
  <?php /* <script>
  function devapp_search_form_submit() {
    v = document.getElementById('devapp_search_value').value;
    if (v == '') {
      alert('Cannot perform an empty search');
      return;
    }
    document.location.href = 'devapps?since=999&match=' + encodeURIComponent(v);
  }
  function lobbyist_search_form_submit() {
    v = document.getElementById('lobbyist_search_value').value;
    if (v == '') {
      alert('Cannot perform an empty search');
      return;
    }
    document.location.href = 'lobbying/search/'+encodeURIComponent(v);
  }
  </script>
  <h4>Development Applications</h4>
  <table class="table table-bordered table-hover table-condensed" style="width: 100%;">
  <?php
  $count = getDatabase()->one(" select count(1) c from devapp ");
  $count = $count['c'];
  $apps = getDatabase()->all(" select * from devapp order by updated desc limit 5 ");
  foreach ($apps as $a) {
    # $url = DevelopmentAppController::getLinkToApp($a['appid']);
    $url = OttWatchConfig::WWW."/devapps/{$a['devid']}"; # DevelopmentAppController::getLinkToApp($a['appid']);
    $addr = json_decode($a['address']);
    $addrcount = count($addr);
    $addr = $addr[0];
    $addr = $addr->addr;
    ?>
    <tr>
    <td><small><a href="<?php print $url; ?>"><?php print $a['devid']; ?></a></small></td>
    <td><small><?php print $a['apptype']; ?></small></td>
    <td><small><?php print $addr; ?></small></td>
    </tr>
    <?php
    #print "<a href=\"$url\">{$a['devid']}</a> {$a['apptype']}: {$addr}<br/>";
    #pr($a);
  }
  ?>
  </td>
  </tr>
  </table> */ ?>
  </div>

  <div class="span4">
  <a class="twitter-timeline" href="https://twitter.com/ldnwatch" data-widget-id="368778109140492288">Tweets by @ldnwatch</a>
  <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
  </div>

  </div>
  <?php
  bottom();
}

function ideas() {
  top();
  ?>
  <h1>Got an idea for Open Council?</h1>
  <h4>Let me know by leaving a (public) comment below.</h4>
  <p>
  Open Council is focused on the political and governance of London Ontario.
  </p>
  <?php
  disqus();
  bottom();
}

function about() {
  top();
  include("about_content.html");
  bottom();
}

function home() {
}


function lobbyist($name) {
  # move to new REST location
  header("Location: ".OttWatchConfig::WWW."/lobbying/lobbyists/$name");
}

function error404() {
  top();
  ?>
  <div class="row-fluid">

  <div class="span4">&nbsp;</div>
  <div class="span4">
  <h1>Error!</h1>
  <h4>Somehow, you've found a page that does not work.</h4>
  <h5>I should put a fail-whale here or something.</h5>
  </div>
  <div class="span4">&nbsp;</div>

  </div>
  <?php
  bottom();
}

function top($title = '') {
  global $OTT_WWW;
?>
<!DOCTYPE html>
<html>
<head>
<title><?php print $title; ?></title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="<?php print $OTT_WWW; ?>/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen" type="text/css">
<link href="<?php print $OTT_WWW; ?>/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
<style type="text/css">
  body {
  padding: 20px;
}
</style>
<script src="http://code.jquery.com/jquery.js" type="text/javascript"></script>
<script src="<?php print $OTT_WWW; ?>/bootstrap/js/bootstrap.min.js"></script>
<script>
function copyToClipboard (text) {
  window.prompt ("Copy to clipboard: Ctrl+C, Enter", text);
}
</script>
<!--
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php print OttWatchConfig::GOOGLE_API_KEY; ?>&sensor=false"></script>
-->
</head>
<body>
  <a href='/'><img src='/img/opencouncil.png' alt='Open Council' /></a><br />&nbsp;
<div class="row-fluid">
<div class="span12">
<div class="navbar"><div class="navbar-inner">
<ul class="nav">
<li><a href="<?php print $OTT_WWW; ?>">Home</a></li>
<!--<li><a href="<?php print $OTT_WWW; ?>/dashboard">Dashboard</a></li>-->
<li><a href="<?php print $OTT_WWW; ?>/meetings/all">Meetings</a></li>
<li><a href="<?php print $OTT_WWW; ?>/meetings/votes">Voting History</a></li>
<li><a href="<?php print $OTT_WWW; ?>/about">About</a></li>
<!--li><a href="<?php print $OTT_WWW; ?>/ideas">Ideas</a></li-->
<!--li><a href="<?php print $OTT_WWW; ?>/api/about">API</a></li-->
<?php
//if (!LoginController::isLoggedIn()) {
  /*
  ?>
  <li><a href="<?php print $OTT_WWW; ?>/user/login">Login</a></li>
  <?php
  */
//} else {
  /*
  ?>
  <li><a href="<?php print $OTT_WWW; ?>/user/home"><?php print getSession()->get('user_email'); ?></a></li>
  <li><a href="<?php print $OTT_WWW; ?>/user/logout">Logout</a></li>
  <?php
  */
//}
?>
</ul>
</div></div>
</div>
</div>

<?php
  if ($title != '') {
    if (0) {
    ?>
    <div style="background: #fcfcfc; padding: 10px; border: #c0c0c0 solid 1px;">
    <div class="row-fluid">
    <div class="lead span6">
    <?php print $title; ?>
    </div>
    </div>
    </div>
    <?php
    }
  }
}

function bottom() {
  global $OTT_WWW;
  ?>
<div class="well">
<a href="<?php print $OTT_WWW; ?>"><img style="float: right; padding-left: 5px; width: 50px; height: 50px;" src="<?php print $OTT_WWW; ?>/img/ottwatch.png"/></a>
<i>Ottwatch Created by <a href="http://kevino.ca"><b>Kevin O'Donnell</b></a> - <b><a href="http://twitter.com/ODonnell_K">@ODonnell_K</a></b></i><br/>
<i>Open Council Created by <a href="http://gavinblair.github.io"><b>Gavin Blair</b></a> - <b><a href="http://twitter.com/gavinblair">@gavinblair</a></b></i><br/>
On Twitter? Follow <b><a href="http://twitter.com/ldnwatch">@ldnwatch</a></b></i>


<div class="clearfix"></div>
</div>
  <?php
  googleAnalytics();
  ?>

    <script type="text/javascript">
    /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
    var disqus_shortname = 'ldnwatch'; // required: replace example with your forum shortname

    /* * * DON'T EDIT BELOW THIS LINE * * */
    (function () {
        var s = document.createElement('script'); s.async = true;
        s.type = 'text/javascript';
        s.src = '//' + disqus_shortname + '.disqus.com/count.js';
        (document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
    }());
    </script>

  </body>
  </html>
  <?php
}