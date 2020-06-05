<?php

include_once "./config/db_config.php";
include_once "./include/DB.php";

$date = new DateTime();
$date = $date->format('Y-m-d H:i:sP');

$db = new DB($db_settings['host'], $db_settings['db_name'], $db_settings['user'], $db_settings['password']);
$statistics = $db->get_statistics();

 ?>
<div class="container pt-3">
  <form class="" action="" method="post">
    <div class="row justify-content-md-center m-3">
      <input class="btn-success col-md-4" type="button" name="submit_get_log" value="Export log file as CSV" onclick='window.location = "./downloads/log_csv.php"; return false;'>
      <input class="btn-success col-md-4" type="button" name="submit_get_log" value="Export log file as PDF" onclick='window.location = "./downloads/log_pdf.php"; return false;'>
    </div>
    <div class="row justify-content-md-center m-3">
      <label class="col-md-4" for="ext">Get statistics on email:</label>
      <input class="col-md-4" type="text" name="email" value="" placeholder="Your email">
      <input class="btn-info col-md-4" type="button" name="submit_get_email" value="Get statistics on email" onclick='send(this); return false;'>
    </div>
  </form>
  <div class="row justify-content-md-center">
    <p class='h3 mt-3 '>Usage statistics</p>
    <table class="table">
      <thead>
        <th></th>
        <th>Inverted pendulum</th>
        <th>Ball and beam</th>
        <th>Suspension dynamic system</th>
        <th>Aircraft pitch</th>
      </thead>
      <tbody>
        <tr>
          <td>Number of launches:</td>
          <td><?php echo $statistics['pendulum']; ?></td>
          <td><?php echo $statistics['ball']; ?></td>
          <td><?php echo $statistics['suspension']; ?></td>
          <td><?php echo $statistics['aircraft']; ?></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<script type="text/javascript">
function send(event){
  var send_data = {
    'code': $("textarea[name='code_raw']")[0].value,
    'token': 'Y29tcHV0ZXJfYWlkZWRfc3lzdGVt'
  };
  $.ajax({
    url:     "api/execute.php",
    type:     "POST",
    dataType: "json",
    contentType: "application/json",
    data: JSON.stringify(send_data),
    success: function(response) {
        $("textarea[name='code_used']")[0].value += "\n" + $("textarea[name='code_raw']")[0].value;
        $("textarea[name='code_raw']")[0].value = "";
        $("textarea[name='result'")[0].value += response.raw + "\n";
    },
    error: function(response) {}
  });
}

function export_log(){

}
</script>
