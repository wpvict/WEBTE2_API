<div class="container">
  <div class="alert alert-success">

    <form class="" id="code" action="" method="post">
      <p class='h5 m-1 mb-3 text-center'>Interactive mode</p>

      <div class="row justify-content-md-center">

        <textarea name="code_used" rows="4" cols="80" readonly></textarea>

        <textarea name="code_raw" rows="4" cols="80" placeholder='Type your code here'></textarea>

        <input class="btn-info col-md-8 m-1" type="button" name="submit_get_coords" value="Get result" onclick='get_result(this); return false;'>

        <textarea name='result' rows="8" cols="80" readonly></textarea>
      </div>


      <script type="text/javascript">
      function get_result(event){
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
      </script>

    </form>

 </div>

</div>
