<?php
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Origin', '*');
  header('Access-Control-Allow-Credentials', 'true');
  header('Access-Control-Allow-Methods', 'GET,HEAD,OPTIONS,POST,PUT');
  header('Access-Control-Allow-Headers', 'Access-Control-Allow-Headers, Origin,Accept, X-Requested-With,Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers');
  session_start();
  $pageWasRefreshed = isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';
  if ($pageWasRefreshed) {
    destroySession();
  }
  function destroySession() {
    // If user Session has lasted > 20 mins, delete all output files for this user
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 20)) {
      if (file_exists('./assets/output/'.$_SESSION['id'].'_output.html') == 1) {
        unlink('./assets/output/'.$_SESSION['id'].'_output.html');
      }
      if (file_exists('./assets/output/'.$_SESSION['id'].'_output.mixup') == 1) {
        unlink('./assets/output/'.$_SESSION['id'].'_output.mixup');
      }
      if (file_exists('./assets/output/'.$_SESSION['id'].'_output.csv') == 1) {
        unlink('./assets/output/'.$_SESSION['id'].'_output.csv');
      }
      if (file_exists('./assets/output/'.$_SESSION['id'].'_output.txt') == 1) {
        unlink('./assets/output/'.$_SESSION['id'].'_output.txt');
      }
      session_unset(); // unset $_SESSION variable for the run-time 
      session_destroy(); // destroy session data in storage
      echo '
        <script type="text/javascript">
        alert("Session expired");
        </script>
      ';
      session_start();
    }
    $_SESSION['last_activity'] = time(); 
  }
?>
<!DOCTYPE HTML>
<html>
<head>
<title>FlexiTerm Results</title>
  <link href='./assets/css/flexiterm.css' rel='stylesheet'>
  <link rel='stylesheet' type='text/css' href='https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css'>
  <link rel='stylesheet' href='//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css'>
  <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'>
  <link href='./assets/css/secondary.css' rel='stylesheet'>
  <script src='./assets/js/jquery.min.js' type='text/javascript'></script>
  <script src='https://code.jquery.com/ui/1.12.1/jquery-ui.js'></script>
  <script type='text/javascript' src='https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js'></script>
  <script src='./assets/js/skel.min.js'></script> 
  <script src='./assets/js/util.js'></script> 
  <script src='./assets/js/main.js'>
  <script src='./assets/flex.js'></script>
</head>
<script type='text/javascript'>
  $( function() {
    $( '#tabs' ).tabs();
  } );
</script>
<body>
  <!-- Wrapper -->
  <div id='wrapper'>
    <!-- Main -->
    <div id='main'>
      <div class='inner'>
      <div class='loading'>Loading&#8230;</div>
        <!-- Header -->
        <header id='header'>
          <a class='logo' href='flexiterm.html'><span><strong>Flexi</strong>Term</span></a>
        </header><!-- Section -->
        <section>
     <div class = 'result-data'>
    <div class = 'table-menu'>
    <span id='reset' class='reset-button'><a href='demo.html'>Reset</a></span>
          <div class = 'table-count'>
      <span id='results-count'>0 results</span>
      </div>
    </div>
      </div>
  <div id='tab-container'>
  <?php
    if (file_exists('./assets/semaphore.txt') == 1) unlink('./assets/semaphore.txt');
    // Load data from user session
    $count = $_SESSION['count'];
    $table = $_SESSION['table'];
    $text = $_SESSION['text'];
    $regex = '';
    $term_array = array();
    $rank = 1;
   if($count > 0) {// If there are any results
      echo '<div id="tabs" class="style-tabs">';
      echo '<ul>';
      echo '<li><a href="#tabs-1">View as table</a></li>';
      echo '<li><a href="#tabs-2">View as Html page</a></li>';
      echo '<li><a href="#tabs-3">Download</a></li>';
      echo '</ul>';
      // Dynamically create table rows from SQL results.
      echo '<div id="tabs-1">';
      echo '<table id="term-results" class="row-border hover" >';
      echo '<thead>';
      echo '<tr>';
      echo '<th>Rank</th>';
      echo '<th>Term</th>';
      echo '<th>Termhood Score</th>';
      echo '</thead>';
      echo '</tr>';
      $c = 1000000; // Max termhood score.
      foreach($table as $row) {
        $regex.=$row['phrase'].'|'; // Dynamically create regex from regonised terms.
        echo '<tr>';
        echo '<td>'.$rank.'</td>';
        echo '<td>'.$row['phrase'].'</td>';
        echo '<td>'.$row['c'].'</td>';
        echo '</tr>';
        // Create array with terms and rank
        $term_array[$row['phrase']] = $row['c'].'-'.$rank;
        // Increase rank if termhood score is greater than previous.
        if ($c > $row['c']) $rank = $rank+1;
        $c = $row['c'];
      }
      echo '</table>';
      echo '</div>';
      echo '<div id="tabs-2">';
      echo '<div id = "document">';
      echo $text;
      echo '</div>';
      echo '</div>';
      // Dynamically create download links.
      echo '<div id="tabs-3">';
      echo '<h4>Download Results</h4>';
      echo '<ul>';
      echo '<li class="download-link">';
      echo '<a href="./assets/output/'.$_SESSION["id"].'_output.mixup" download>';
      echo '<span class="dow-icon"><i class="fa fa-download" aria-hidden="true"></i></span>';
      echo '<span class="dow-text">Mixup file</span>';
      echo '</a>';
      echo '</li>';
      echo '<li class="download-link">';
      echo '<a href="./assets/output/'.$_SESSION["id"].'_output.csv" download>';
      echo '<span class="dow-icon"><i class="fa fa-download" aria-hidden="true"></i></span>';
      echo '<span class="dow-text">Csv file</span>';
      echo '</a>';
      echo '</li>';
      echo '<li class="download-link">';
      echo '<a href="./assets/output/'.$_SESSION["id"].'_output.html" download>';
      echo '<span class="dow-icon"><i class="fa fa-download" aria-hidden="true"></i></span>';
      echo '<span class="dow-text">Html file</span>';
      echo '</a>';
      echo '</li>';
      echo '<li class="download-link">';
      echo '<a href="./assets/output/'.$_SESSION["id"].'_output.txt" download>';
      echo '<span class = "dow-icon"><i class="fa fa-download" aria-hidden="true"></i></span>';
      echo '<span class = "dow-text">Plain text file</span>';
      echo '</a>';
      echo '</li>';
      echo '</ul>';
      echo '<div class="download-warning">';
      echo '<span id="download-warning">Note: Files will be deleted after session expires.</span>';
      echo '</div>';
      echo '</div>';
      echo '
        <script type="text/javascript">
        var session = '.$_SESSION["count"].';
        $("#results-count").text("'.$count.' results");
        $("#term-results").DataTable({
          "order": [[ 0, "asc" ]]
        });
        var regex_word = "'.$regex.'";
        var index = regex_word.lastIndexOf("|");
        regex_word = "\\\b("+regex_word.substr(0, index)+")\\\b";
        var regex = new RegExp(regex_word, "gi");
        var term_dict = '.json_encode($term_array).';
        $("#document").each(function(){
          $(this).html($(this).html().replace(regex,"<span class = \\"highlight\\" title=\\"\\" term=\\"$1\\">$1</span>"));
        });
        $(".highlight").each(function(){
          var term = $(this).attr("term").toLowerCase();
          var score = term_dict[term].substr(0, term_dict[term].indexOf("-"));
          var rank = term_dict[term].substr(term_dict[term].indexOf("-")+1, term_dict[term].length - 1 );
          $(this).attr("title", "Termhood Score: "+score+" (#"+rank+")");
        });
        $( document).tooltip();
        </script>
      ';
   }
?>
</div>
</section>
</div>
</div>
    <div id='sidebar'>
      <div class='inner'>
        <!-- Menu -->
        <nav id='menu'>
          <header class='major'>
            <h2>Menu</h2>
          </header>
          <ul>
            <li><a href='flexiterm.html'>Home</a></li>
            <li><a href='flexiterm.html#about'>About</a></li>
            <li><a href='flexiterm.html#download'>Download</a></li>
            <li><a href='flexiterm.html#publications'>Publications</a></li>
            <li><a href='demo.html'>Demo</a></li>
          </ul>
        </nav><!-- Section -->
        <section>
          <header class='major'>
            <h2>Contact</h2>
          </header>
          <ul class='contact'>
            <li class='fa-envelope-o'>i.spasic@cs.cardiff.ac.uk</li>
            <li class='fa-phone'>+44(0)29 2087 0320</li>
            <li class='fa-home'>School of Computer Science &amp; Informatics, Cardiff University<br>
            Queen's Buildings<br>
            5 The Parade<br>
            Cardiff CF24 3AA<br>
            UK</li>
          </ul>
        </section><!-- Footer -->
        <footer id='footer'>
          <p class='copyright'>&copy; Irena Spasi&#263;. All rights reserved.</p>
        </footer>
        <div id='eXTReMe'>
          <a href='https://extremetracking.com/open?login=ispasic'><img alt='eXTReMe Tracker' height='38' id='EXim' src='https://t1.extreme-dm.com/i.gif' style='border: 0;' width='41'></a> 
          <script type='text/javascript'>
          <!--
          // var EXlogin='ispasic' // Login
          // var EXvsrv='s11' // VServer
          // EXs=screen;EXw=EXs.width;navigator.appName!='Netscape'?
          // EXb=EXs.colorDepth:EXb=EXs.pixelDepth;
          // navigator.javaEnabled()==1?EXjv='y':EXjv='n';
          // EXd=document;EXw?'':EXw='na';EXb?'':EXb='na';
          // EXd.write('<img src=http://e2.extreme-dm.com',
          // '/'+EXvsrv+'.g?login='+EXlogin+'&amp;',
          // 'jv='+EXjv+'&amp;j=y&amp;srw='+EXw+'&amp;srb='+EXb+'&amp;',
          // 'l='+escape(EXd.referrer)+' height=1 width=1>');//-->
          </script>
          <noscript>
          <div id='neXTReMe'><img alt='' height='1' src='https://e2.extreme-dm.com/s11.g?login=ispasic&amp;j=n&amp;jv=n' width='1'></div></noscript>
        </div>
      </div>
    </div>
</body>
</html>