<div class="container-fluid">
  <div class="row">

  </div>
  <div class="row">
    <div class="col-md-9">
      <div class="mt-2" id="graph" style="height: 300px; width: 100%"></div>
      <canvas class='ml-5' id="wheel" width="1000" height="300"></canvas>
    </div>
    <form class="col-md-3 board" action="" method="post">
      <p class='h3 m-1 mb-3 text-center'>Input position</p>

      <div class="row justify-content-md-center">

        <p class="col-md-12">Use values between -20 and +20 for the best experience.</p>
        <div id="tooltip" class="col-md-12 alert-danger fade"></div>
        <input class="col-md-4" type="text" name="position_new" value="" placeholder='Type position here'>
        <input class="btn-info col-md-4 ml-1" type="button" name="submit_get_coords" value="Move wheel" onclick='get_result(this); return false;'>

        <label class='col-md-6 m-2' for="is_graph">Enable plot</label>
        <input checked type="checkbox" name="is_graph" value="" onchange="change_graph(this); return false;">

        <script type="text/javascript">
          function change_graph(event){
            if($("#is_graph").is(":checked")){
              $("#graph").toggle();
            } else {
              $("#graph").toggle();
            }
          }
        </script>

        <label class='col-md-6 m-2' for="is_animation">Enable animation</label>
        <input checked type="checkbox" name="is_animation" value="" onchange="change_animation(this); return false;">

        <script type="text/javascript">
          function change_animation(event){
            if($("#is_animation").is(":checked")){
              $("#wheel").toggle();
            } else {
              $("#wheel").toggle();
            }
          }
        </script>

        <input type="text" name="auto_position_0" value="0" hidden>
        <input type="text" name="auto_position_1" value="0" hidden>
        <input type="text" name="wheel_position_0" value="0" hidden>
        <input type="text" name="wheel_position_1" value="0" hidden>

      </div>

      <script type="text/javascript">
      var allow = true;
      function get_result(event){

        $("#tooltip").addClass('fade');
        if(!parseFloat($("input[name='position_new']")[0].value)){
          $("#tooltip").removeClass('fade');
          $("#tooltip").text("Only digits allowed!");
          return false;
        }

        if($("input[name='position_new']")[0].value < -20 || $("input[name='position_new']")[0].value > 20){
          $("#tooltip").removeClass('fade');
          $("#tooltip").text("Wrong parameters!");
          return false;
        }

        if(allow){
          allow = false;
        }
        var send_data = {
          'auto_position': [$("input[name='auto_position_0']")[0].value, $("input[name='auto_position_1']")[0].value],
          'wheel_position': [$("input[name='wheel_position_0']")[0].value, $("input[name='wheel_position_1']")[0].value],
          'position': $("input[name='position_new']")[0].value / 100,
          'token': 'Y29tcHV0ZXJfYWlkZWRfc3lzdGVt'
        };
        $.ajax({
          url:     "api/suspension.php",
          type:     "POST",
          dataType: "json",
          contentType: "application/json",
          data: JSON.stringify(send_data),
          success: function(response) {
            allow = true;
            var t = 0;
            var inter = setTimeout(function moveImage(){
              animate(ctx, 1000 * response.data.x[t][2], 100 * response.data.x[t][0]);
              t++;
              if(t < response.data.x.length){
                position_points.push({
                  x: graph_index,
                  y: 10 * response.data.x[t][0]
                });
                angle_points.push({
                  x: graph_index,
                  y: 10 * response.data.x[t][2]
                });
                graph_index++;
                if((position_points.length + angle_points.length) > 100){
                  position_points.shift();
                  angle_points.shift();
                }
                chart.render();
                $("input[name='auto_position_0']")[0].value = response.data.x[t][0];
                $("input[name='auto_position_1']")[0].value = response.data.x[t][1];
                $("input[name='wheel_position_0']")[0].value = response.data.x[t][2];
                $("input[name='wheel_position_1']")[0].value = response.data.x[t][3];

                if(allow){
                  setTimeout(moveImage, 20);
                } else {
                  return 1;
                }
              }
            }, 20);
          },
          error: function(response) {}
        });
      }
      </script>

    </form>

  </div>

</div>



<script>

  var canvas_width = 1000;
  var canvas_height = 550;
  var grid_size = 25;

  function draw_wheel(ctx, auto_pos, wheel_pos){

    var wheel_width = 40;
    var wheel_height = 90;

    var amo_width = 16;
    var amo_height = 2;
    var count_amo = 11;

    var distance = 110;

    var auto_width = 400;
    var auto_height = 75;

    ctx.fillStyle = "rgb(0,0,0)";

    // Calculating position
    var pos = {};
    pos['x_surface_start'] = 0;
    pos['y_surface_start'] = canvas_height / 2;

    pos['x_surface_middle'] = canvas_width / 2;
    pos['y_surface_middle'] = canvas_height / 2;

    pos['x_surface_end'] = canvas_width;
    pos['y_surface_end'] = (canvas_height / 2) - Math.tan( wheel_pos / (auto_width / 2 - wheel_width) ) * (canvas_width / 2);

    pos['x_wheel_left'] = (canvas_width / 2) - (auto_width / 2);
    pos['y_wheel_left'] = (canvas_height / 2) - wheel_height;

    pos['x_wheel_right'] = (canvas_width / 2) + (auto_width / 2) - wheel_width;
    pos['y_wheel_right'] = (canvas_height / 2) - wheel_height - wheel_pos;

    pos['x_auto'] = canvas_width / 2 - auto_width / 2;
    pos['y_auto'] = canvas_height / 2 - distance - auto_height - auto_pos;

    // Drawing surface
    ctx.fillRect(pos['x_surface_start'], pos['y_surface_start'], canvas_width / 2, 5);
    ctx.beginPath();
    ctx.moveTo(pos['x_surface_middle'], pos['y_surface_middle']);
    ctx.lineTo(pos['x_surface_end'], pos['y_surface_end']);
    ctx.lineTo(pos['x_surface_end'], pos['y_surface_end'] + 5);
    ctx.lineTo(pos['x_surface_middle'], pos['y_surface_middle'] + 5);
    ctx.fill();


    // Drawing wheels
    ctx.fillRect(pos['x_wheel_left'], pos['y_wheel_left'], wheel_width, wheel_height);
    ctx.fillRect(pos['x_wheel_right'], pos['y_wheel_right'], wheel_width, wheel_height);

    var mid_left = (pos['y_wheel_left'] + wheel_height / 2) - (pos['y_auto'] + auto_height);
    for(var i = 0; i < mid_left; i += (mid_left / count_amo)){
      ctx.fillRect(pos['x_wheel_left'] + wheel_width, pos['y_auto'] + auto_height + i, amo_width, amo_height);
    }

    var mid_right = (pos['y_wheel_right'] + wheel_height / 2) - (pos['y_auto'] + auto_height);
    for(var i = 0; i < mid_right; i += (mid_right / count_amo)){
      ctx.fillRect(pos['x_wheel_right'] - amo_width, pos['y_auto'] + auto_height + i, amo_width, amo_height);
    }


    // Drawing car
    ctx.fillStyle = "rgb(30,30,30)";
    ctx.fillRect(pos['x_auto'], pos['y_auto'], auto_width, auto_height);

    for(var i = 0; i < 40; i+= 10){
      ctx.fillStyle = "rgb(120, 120, 120)";
      ctx.fillRect(pos['x_auto'] + 70, pos['y_auto'] + 15 + i , 260, 5);
      ctx.fillStyle = "rgb(80, 80, 80)";
      ctx.fillRect(pos['x_auto'] + 70, pos['y_auto'] + 20 + i , 260, 5);
    }


    // Drawing head lights
    ctx.fillStyle = "rgb(30,50,0)";
    ctx.beginPath();
    ctx.arc(pos['x_auto'] + 40, pos['y_auto'] + 30, 20, 0, Math.PI * 2, true);
    ctx.fill();

    ctx.beginPath();
    ctx.arc(pos['x_auto'] + auto_width - 40, pos['y_auto'] + 30, 20, 0, Math.PI * 2, true);
    ctx.fill();

    // Drawing lights

    ctx.fillStyle = "rgb(255,255,0)";
    ctx.beginPath();
    ctx.arc(pos['x_auto'] + 40, pos['y_auto'] + 30, 15, 0, Math.PI * 2, true);
    ctx.fill();

    ctx.beginPath();
    ctx.arc(pos['x_auto'] + auto_width - 40, pos['y_auto'] + 30, 15, 0, Math.PI * 2, true);
    ctx.fill();

    // Drawing cover

    ctx.fillStyle = "rgb(60,60,60)";
    ctx.beginPath();
    ctx.moveTo(pos['x_auto'] + 30, pos['y_auto']);
    ctx.lineTo(pos['x_auto'] + 130, pos['y_auto'] - 200);
    ctx.lineTo(pos['x_auto'] + 270, pos['y_auto'] - 200);
    ctx.lineTo(pos['x_auto'] + 370, pos['y_auto']);
    ctx.fill();

    // Drawwing glass

    ctx.fillStyle = "rgba(0, 0,60, 0.3)";
    ctx.beginPath();
    ctx.moveTo(pos['x_auto'] + 50, pos['y_auto'] - 10);
    ctx.lineTo(pos['x_auto'] + 80, pos['y_auto'] - 80);
    ctx.lineTo(pos['x_auto'] + 320, pos['y_auto'] - 80);
    ctx.lineTo(pos['x_auto'] + 350, pos['y_auto'] - 10);
    ctx.fill();

  }

  function draw_grid(ctx){

    ctx.fillStyle = "rgba(0,0,0,0.3)";

    for(var i = 0; i < canvas_width; i += grid_size){
      ctx.fillRect(i, 0, 1, canvas_height);
    }

    for(var j = 0; j < canvas_height; j += grid_size){
      ctx.fillRect(0, j, canvas_width, 1);
    }
  }

  function clear_canvas(ctx){
    ctx.clearRect(0, 0, canvas_width, canvas_height);
  }

  function animate(ctx, auto_position, wheel_position){
    clear_canvas(ctx);
    draw_grid(ctx);
    draw_wheel(ctx, auto_position, wheel_position);
  }

  var canvas = document.getElementById('wheel');
  var ctx = canvas.getContext('2d');

  draw_grid(ctx);
  draw_wheel(ctx, 0, 0);


</script>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
<script type="text/javascript">
  var graph_index = 0;
  var position_points = [];
  var angle_points = [];
  var chart = new CanvasJS.Chart("graph", {
  title :{
    text: "Dynamic Data"
  },
  axisY: {
    includeZero: false
  },
  data: [{
    type: "line",
    dataPoints: position_points
  },
  {
    type: "line",
    dataPoints: angle_points
  }]
  });
  chart.render();
</script>
