<?php
function ottawaMediaRSS() {
  $url = "http://www.feedyes.com/feed.php?f=NDv40itdxAFzeOVd";
  $rss = file_get_contents($url);
  $xml = simplexml_load_string($rss);
  if (!is_object($xml)) {
    # could not load RSS; just fail silently
    print "<h4>London.ca News</h4>\n";
    print "<i>Could not load media releases. Probably a temporary error.</i>";
    return;
  }
  $items = $xml->xpath("//item");
  print "<h4>London.ca News</h4>\n";
  $max = 4;
  $x = 0;
  foreach ($items as $item) {
    if ($x++ < $max) {
    $title = $item->xpath("title"); $title = $title[0].'';
    $link = $item->xpath("link"); $link = $link[0].'';
    print "<small><a href=\"$link\" target=\"_blank\">$title</a></small><br/>\n";
    }
  }
}

function dashboard() {
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
  $meetings = getDatabase()->all(" 
    select id,category,date(starttime) starttime,meetid 
    from meeting 
    where 
      date(starttime) > date(CURRENT_TIMESTAMP) 
      and datediff(starttime,current_timestamp()) < 60
    order by starttime ");
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
  $meetings = getDatabase()->all(" select id,meetid,category,date(starttime) starttime from meeting where date(starttime) < date(CURRENT_TIMESTAMP) order by starttime desc limit 5 ");
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
  ottawaMediaRSS();
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

  <div class="input-prepend input-append">
  <input type="text" id="devapp_search_value" placeholder="Search...">
  <a class="btn" onclick="devapp_search_form_submit()"><i class="icon-search"></i> Search</button>
  <a class="btn" href="devapps?since=999">Show All</a>
  </div>

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
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php print OttWatchConfig::GOOGLE_API_KEY; ?>&sensor=false"></script>
</head>
<body>

<div class="row-fluid">
<div class="span12">
<div class="navbar"><div class="navbar-inner">
<ul class="nav">
<li><a href="<?php print $OTT_WWW; ?>">Home</a></li>
<!--<li><a href="<?php print $OTT_WWW; ?>/dashboard">Dashboard</a></li>-->
<li><a href="<?php print $OTT_WWW; ?>/meetings/votes">Voting History</a></li>
<li><a href="<?php print $OTT_WWW; ?>/about">About</a></li>
<li><a href="<?php print $OTT_WWW; ?>/ideas">Ideas</a></li>
<li><a href="<?php print $OTT_WWW; ?>/api/about">API</a></li>
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
<i>Created by <a href="http://kevino.ca"><b>Kevin O'Donnell</b></a> - <b><a href="http://twitter.com/ODonnell_K">@ODonnell_K</a></b></i><br/>
On Twitter? Follow <b><a href="http://twitter.com/ldnwatch">@ldnwatch</a></b> and <br/>

<div id="clock">
<script language="JavaScript">
TargetDate = "10/27/2014 6:00 PM";
BackColor = "ffffff";
ForeColor = "ed1b24";
CountActive = true;
CountStepper = -1;
LeadingZero = true;
DisplayFormat = "<span class=\"clockdigit\">%%D%%</span> days until election day!";
FinishMessage = "It is finally here!";
</script>
<script language="JavaScript" src="http://scripts.hashemian.com/js/countdown.js"></script>
</div>
</i>


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
