<?php

include_once "./config/db_config.php";
include_once "./include/DB.php";

$date = new DateTime();
$date = $date->format('Y-m-d H:i:sP');

$db = new DB($db_settings['host'], $db_settings['db_name'], $db_settings['user'], $db_settings['password']);
$docs = $db->get_docs();

 ?>
<div class="container pt-3">
  <form class="" action="" method="post">
    <div class="row justify-content-md-center m-3">
      <input class="btn-success col-md-4" type="button" name="submit_get_log" value="Export docs file as PDF" onclick='window.location = "./downloads/docs_pdf.php"; return false;'>
    </div>
  </form>
  <div class="row justify-content-md-center">
    <p class='h3 mt-3'>API reference</p>
  </div>
  <div class="row justify-content-md-center">
    <p class='m-3'>All methods use URI <span style="font-style: italic">http://wt128.fei.stuba/</span>.</br>All methods returns either values, either error with message.</br>Token is requered for API usage.</p>
    <table class="table">
      <thead>
        <th>HTTP method</th>
        <th>API method</th>
        <th>Parameters</th>
        <th>Response</th>
        <th>Description</th>
      </thead>
      <tbody>
        <?php
          foreach($docs as $method){
         ?>
        <tr>
          <td><?php echo $method[1];?></td>
          <td><?php echo $method[2];?></td>
          <td><?php echo $method[3];?></td>
          <td><?php echo $method[4];?></td>
          <td><?php echo $method[5];?></td>
        </tr>

      <?php
        }
     ?>
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
