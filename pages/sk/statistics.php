<?php

include_once "./config/db_config.php";
include_once "./include/DB.php";

$date = new DateTime();
$date = $date->format('Y-m-d H:i:sP');

$db = new DB($db_settings['host'], $db_settings['db_name'], $db_settings['user'], $db_settings['password']);
$statistics = $db->get_statistics();

 ?>
<div class="container pt-3">
  <form class="" action="#" method="post">
    <div class="row justify-content-md-center m-3">
      <input class="btn-success col-md-4  p-2" type="button" name="submit_get_log" value="Exportujte protokolovací súbor ako CSV" onclick='window.location = "./downloads/log_csv.php"; return false;'>
      <input class="btn-success col-md-4  p-2" type="button" name="submit_get_log" value="Exportujte protokolovací súbor ako PDF" onclick='window.location = "./downloads/log_pdf.php"; return false;'>
    </div>
    <div class="row justify-content-md-center m-3">
      <label class="col-md-4" for="ext">Získajte štatistiky na email:</label>
      <input class="col-md-4" type="text" name="email" value="" placeholder="Tvoj email">
      <input class="btn-info col-md-4" type="button" name="submit_get_email" value="Získajte štatistiky na email" onclick='send(this); return false;'>
      <div id="tooltip" class="col-md-12 alert-success fade h5">Successfully sent!</div>

    </div>
  </form>
  <div class="row justify-content-md-center">
    <p class='h3 mt-3 '>Štatistika použitia</p>
    <table class="table">
      <thead>
        <tr>
          <th></th>
          <th>Prevrátené kyvadlo</th>
          <th>Gulička na tyči</th>
          <th>Tlmič kolesa</th>
          <th>Náklon lietadla</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Počet spustení:</td>
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
    'email': $("input[name='email']")[0].value,
    'token': 'Y29tcHV0ZXJfYWlkZWRfc3lzdGVt'
  };
  $.ajax({
    url:     "api/mail.php",
    type:     "POST",
    dataType: "json",
    contentType: "application/json",
    data: JSON.stringify(send_data),
    success: function(response) {
      if(response.status == true){
        $("#tooltip").removeClass("fade");
        $("#tooltip").removeClass("alert-danger");
        $("#tooltip").addClass("alert-success");
        $("#tooltip").text("Message was successfully sent!");
      } else if(response.status == false){
        $("#tooltip").removeClass("fade");
        $("#tooltip").removeClass("alert-success");
        $("#tooltip").addClass("alert-danger");
      }

    },
    error: function(response) {
      $("#tooltip").removeClass("fade");
      $("#tooltip").removeClass("alert-success");
      $("#tooltip").addClass("alert-danger");
      $("#tooltip").text("Message was not sent!");
    }
  });
}

</script>
