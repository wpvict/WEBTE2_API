<div class="container-fluid">
  <div class="row">

  </div>
  <div class="row">
    <div class="col-md-9">
      <div class="mt-2" id="graph" style="height: 300px; width: 100%"></div>
      <canvas class='ml-5' id="aircraft" width="1000" height="300"></canvas>
    </div>
    <form class="col-md-3 board" action="" method="post">
      <p class='h3 m-1 mb-3 text-center'>Input position</p>

      <div class="row justify-content-md-center">

        <p class="col-md-12">Use values between -0.5 and +0.5 for the best experience.</p>
        <div id="tooltip" class="col-md-12 alert-danger fade"></div>
        <input class="col-md-4" type="text" name="position_new" value="" placeholder='Type position here'>
        <input class="btn-info col-md-4 ml-1" type="button" name="submit_get_coords" value="Move plane" onclick='get_result(this); return false;'>

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
              $("#aircraft").toggle();
            } else {
              $("#aircraft").toggle();
            }
          }
        </script>

        <input type="text" name="angle_plane" value="0" hidden>
        <input type="text" name="angle_wing" value="0" hidden>
        <input type="text" name="angle" value="0" hidden>

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

        if($("input[name='position_new']")[0].value < -0.5 || $("input[name='position_new']")[0].value > 0.5){
          $("#tooltip").removeClass('fade');
          $("#tooltip").text("Wrong parameters!");
          return false;
        }

        if(allow){
          allow = false;
        }
        var send_data = {
          'angle_plane': $("input[name='angle_plane']")[0].value,
          'angle_wing': $("input[name='angle_wing']")[0].value,
          'angle': $("input[name='angle']")[0].value,
          'position': $("input[name='position_new']")[0].value,
          'token': 'Y29tcHV0ZXJfYWlkZWRfc3lzdGVt'
        };
        $.ajax({
          url:     "api/aircraft.php",
          type:     "POST",
          dataType: "json",
          contentType: "application/json",
          data: JSON.stringify(send_data),
          success: function(response) {
            allow = true;
            var t = 0;
            var inter = setTimeout(function moveImage(){
              animate(ctx, response.data.x[t][2], response.data.z[t][0]);
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
                $("input[name='angle_plane']")[0].value = response.data.x[t][0];
                $("input[name='angle_wing']")[0].value = response.data.x[t][1];
                $("input[name='angle']")[0].value = response.data.x[t][2];

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
  var canvas_height = 300;
  var grid_size = 25;

  function draw_plane(ctx, plane_angle, wing_angle){

    var plane_length = 600;
    var plane_height = 100;

    var wing_length = 170;
    var wing_height = 10;

    var tail_length = 130;
    var tail_width = 80;

    var top_width = 230;
    var bottom_width = 50;
    var point = 10;
    var tail = 20;

    var window_radius = 15;

    ctx.fillStyle = "rgb(0,0,0)";

    // Calculating position
    var pos = {};
    pos['x_center'] = canvas_width / 2;
    pos['y_center'] = canvas_height / 2;

    // Plane position

    pos['x_center_bottom'] = pos['x_center'] + (plane_height / 2) * Math.sin(plane_angle);
    pos['y_center_bottom'] = pos['y_center'] + (plane_height / 2) * Math.cos(plane_angle);

    pos['x_center_top'] = pos['x_center'] - (plane_height / 2) * Math.sin(plane_angle);
    pos['y_center_top'] = pos['y_center'] - (plane_height / 2) * Math.cos(plane_angle);

    pos['x_center_left'] = pos['x_center'] - (plane_length / 2) * Math.cos(plane_angle);
    pos['y_center_left'] = pos['y_center'] + (plane_length / 2) * Math.sin(plane_angle);

    pos['x_center_right'] = pos['x_center'] + (plane_length / 2) * Math.cos(plane_angle);
    pos['y_center_right'] = pos['y_center'] - (plane_length / 2) * Math.sin(plane_angle);

    pos['x_center_right_w'] = pos['x_center'] + (plane_length / 3.25) * Math.cos(plane_angle);
    pos['y_center_right_w'] = pos['y_center'] - (plane_length / 3.25) * Math.sin(plane_angle);

    // Tail position
    pos['x_center_up'] = pos['x_center'] - (plane_height / 1.3) * Math.sin(plane_angle);
    pos['y_center_up'] = pos['y_center'] - (plane_height / 1.3) * Math.cos(plane_angle);

    pos['x_tail_back'] = pos['x_center_up'] - (plane_length / 2) * Math.cos(plane_angle);
    pos['y_tail_back'] = pos['y_center_up'] + (plane_length / 2) * Math.sin(plane_angle);

    pos['x_tail_front'] = pos['x_center_up'] - ((plane_length - tail_width) / 2) * Math.cos(plane_angle);
    pos['y_tail_front'] = pos['y_center_up'] + ((plane_length - tail_width) / 2) * Math.sin(plane_angle);


    // Front position
    pos['x_center_mid'] = pos['x_center'] + (plane_height / 3.3) * Math.sin(plane_angle);
    pos['y_center_mid'] = pos['y_center'] + (plane_height / 3.3) * Math.cos(plane_angle);

    pos['x_front_top'] = pos['x_center_top'] + ((plane_length - top_width) / 2) * Math.cos(plane_angle);
    pos['y_front_top'] = pos['y_center_top'] - ((plane_length - top_width) / 2) * Math.sin(plane_angle);

    pos['x_front_bottom'] = pos['x_center_bottom'] + ((plane_length - bottom_width) / 2) * Math.cos(plane_angle);
    pos['y_front_bottom'] = pos['y_center_bottom'] - ((plane_length - bottom_width) / 2) * Math.sin(plane_angle);

    pos['x_front_point'] = pos['x_center_mid'] + ((plane_length - point) / 2) * Math.cos(plane_angle);
    pos['y_front_point'] = pos['y_center_mid'] - ((plane_length - point) / 2) * Math.sin(plane_angle);

    // Wings position

    pos['x_wing_bottom'] = pos['x_center'] + (wing_height / 2) * Math.sin(plane_angle);
    pos['y_wing_bottom'] = pos['y_center'] + (wing_height / 2) * Math.cos(plane_angle);

    pos['x_wing_top'] = pos['x_center'] - (wing_height / 2) * Math.sin(plane_angle);
    pos['y_wing_top'] = pos['y_center'] - (wing_height / 2) * Math.cos(plane_angle);

    pos['x_wing_left'] = pos['x_center'] + (wing_length / 2) * Math.cos(plane_angle);
    pos['y_wing_left'] = pos['y_center'] - (wing_length / 2) * Math.sin(plane_angle);

    pos['x_wing_right'] = pos['x_center'] - (wing_length / 2) * Math.cos(plane_angle);
    pos['y_wing_right'] = pos['y_center'] + (wing_length / 2) * Math.sin(plane_angle);

    // Elevator position
    pos['x_center_e'] = pos['x_center'] - (plane_height / 4) * Math.sin(plane_angle);
    pos['y_center_e'] = pos['y_center'] - (plane_height / 4) * Math.cos(plane_angle);

    pos['x_center_w1'] = pos['x_center_e'] + (30 * Math.cos(plane_angle));
    pos['y_center_w1'] = pos['y_center_e'] - (30 * Math.sin(plane_angle));

    pos['x_center_w2'] = pos['x_center_e'] + (80 * Math.cos(plane_angle));
    pos['y_center_w2'] = pos['y_center_e'] - (80 * Math.sin(plane_angle));

    pos['x_center_w3'] = pos['x_center_e'] + (130 * Math.cos(plane_angle));
    pos['y_center_w3'] = pos['y_center_e'] - (130 * Math.sin(plane_angle));

    pos['x_center_elevator'] = pos['x_center_e'] - ((plane_length - tail_width) / 2) * Math.cos(plane_angle);
    pos['y_center_elevator'] = pos['y_center_e'] + ((plane_length - tail_width) / 2) * Math.sin(plane_angle);

    pos['x_elevator'] = pos['x_center_elevator'] - (tail_width / 2) * Math.cos(wing_angle);
    pos['y_elevator'] = pos['y_center_elevator'] + (tail_width / 2) * Math.sin(wing_angle);

    // Plane

    ctx.fillStyle = "rgba(220, 0, 0, 0.9)";
    ctx.beginPath();
    ctx.moveTo(pos['x_center_left'], pos['y_center_left']);
    ctx.lineTo(pos['x_center_top'], pos['y_center_top']);
    ctx.lineTo(pos['x_front_top'], pos['y_front_top']);
    ctx.lineTo(pos['x_center_right'], pos['y_center_right']);
    ctx.lineTo(pos['x_front_point'], pos['y_front_point']);
    ctx.lineTo(pos['x_front_bottom'], pos['y_front_bottom']);
    ctx.lineTo(pos['x_center_bottom'], pos['y_center_bottom']);
    ctx.lineTo(pos['x_center_left'], pos['y_center_left']);
    ctx.lineTo(pos['x_tail_back'], pos['y_tail_back']);
    // ctx.lineTo(pos['x_tail_point'], pos['y_tail_point']);
    // ctx.lineTo(pos['x_tail_point_front'], pos['y_tail_point_front']);
    ctx.lineTo(pos['x_tail_front'], pos['y_tail_front']);
    ctx.lineTo(pos['x_center_bottom'], pos['y_center_bottom']);
    ctx.fill();

    // Drawing head window

    ctx.fillStyle = "rgba(153, 204, 255, 1)";
    ctx.beginPath();
    ctx.moveTo(pos['x_front_top'], pos['y_front_top']);
    ctx.lineTo(pos['x_center_right'], pos['y_center_right']);
    ctx.lineTo(pos['x_center_right_w'], pos['y_center_right_w']);
    ctx.fill();

    ctx.beginPath();
    ctx.arc(pos['x_center_w1'], pos['y_center_w1'], window_radius, 0, Math.PI * 2, true);
    ctx.fill();

    ctx.beginPath();
    ctx.arc(pos['x_center_w2'], pos['y_center_w2'], window_radius, 0, Math.PI * 2, true);
    ctx.fill();

    ctx.beginPath();
    ctx.arc(pos['x_center_w3'], pos['y_center_w3'], window_radius, 0, Math.PI * 2, true);
    ctx.fill();


    // Wings

    ctx.fillStyle = "rgb(0, 0, 255)";
    ctx.beginPath();
    ctx.moveTo(pos['x_wing_left'], pos['y_wing_left']);
    ctx.lineTo(pos['x_wing_top'], pos['y_wing_top']);
    ctx.lineTo(pos['x_wing_right'], pos['y_wing_right']);
    ctx.lineTo(pos['x_wing_bottom'], pos['y_wing_bottom']);
    ctx.fill();

    // Elevator

    ctx.strokeStyle = "rgb(256, 256, 0)";
    ctx.beginPath();
    ctx.moveTo(pos['x_center_elevator'], pos['y_center_elevator']);
    ctx.lineTo(pos['x_elevator'], pos['y_elevator']);
    ctx.stroke();

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
    draw_plane(ctx, auto_position, wheel_position);
  }

  var canvas = document.getElementById('aircraft');
  var ctx = canvas.getContext('2d');

  draw_grid(ctx);
  draw_plane(ctx, 0, 0);


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
