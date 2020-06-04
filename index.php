<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CAS</title>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>
    <script
			  src="https://code.jquery.com/jquery-3.4.1.min.js"
			  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
			  crossorigin="anonymous"></script>
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js" integrity="sha384-6khuMg9gaYr5AxOqhkVIODVIvm9ynTT5J4V1cfthmT+emCG6yVmEZsRHdxlotUnm" crossorigin="anonymous"></script>
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-light nav-cont">
        <div class="container-fluid">
          <div class="navbar-header col-md-2">
            <a class="navbar-brand" href="#"><h2>CAS</h2></a>
          </div>
          <div class="navbar-collapse col-md-8">
            <?php include_once "pages/menu.php" ?>
          </div>
          <div class="col-md-2"></div>
        </div>
      </nav>

    <?php
      if(isset($_GET['p'])){
        $page = NULL;
        switch(htmlspecialchars($_GET['p'])){
          case 'interactive': {
            include_once "pages/interactive.php";
            break;
          };
          case 'pendulum': {
            include_once "pages/pendulum.php";
            $page = $_GET['p'];
            break;
          };
          case 'ball': {
            include_once "pages/ball.php";
            $page = $_GET['p'];
            break;
          };
          case 'suspension': {
            include_once "pages/suspension.php";
            $page = $_GET['p'];
            break;
          };
          case 'aircraft': {
            include_once "pages/aircraft.php";
            $page = $_GET['p'];
            break;
          };
          case 'statistics': {
            include_once "pages/statistics.php";
            break;
          };
          case 'docs': {
            include_once "pages/docs.php";
            break;
          };
          default: {
            include_once "pages/404.php";
            break;
          };
        }

        if($page){
            include_once "./config/db_config.php";
            include_once "./include/DB.php";

            $date = new DateTime();
            $date = $date->format('Y-m-d H:i:sP');

            $db = new DB($db_settings['host'], $db_settings['db_name'], $db_settings['user'], $db_settings['password']);
            $db->add_statistics($page, $_SERVER['REMOTE_ADDR'], $date);
            unset($db);
        }
      } else {
        include_once "pages/pendulum.php";

      }



     ?>

  </body>

</html>
