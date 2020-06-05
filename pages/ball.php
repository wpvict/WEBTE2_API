
<div class="container-fluid">
  <div class="row">

  </div>
  <div class="row">
    <div class="col-md-9">
      <div class="mt-2" id="graph" style="height: 300px; width: 100%"></div>
      <canvas class='ml-5' id="ball" width="1000" height="300"></canvas>
    </div>
    <form class="col-md-3 board" action="" method="post">
      <p class='h3 m-1 mb-3 text-center'>Input position</p>

      <div class="row justify-content-md-center">

        <p class="col-md-12">Use values between -800 and +800 for the best experience.</p>
        <div id="tooltip" class="col-md-12 alert-danger fade"></div>
        <input class="col-md-4" type="text" name="position_new" value="" placeholder='Type position here'>
        <input class="btn-info col-md-4 ml-1" type="button" name="submit_get_coords" value="Move ball" onclick='get_result(this); return false;'>

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
              $("#ball").toggle();
            } else {
              $("#ball").toggle();
            }
          }
        </script>

        <input class="btn-info col-md-6 m-1" type="button" name="submit_set_translation" value="Set translation" onclick='set_translation(this); return false;'>
        <input class="btn-info col-md-6 m-1" type="button" name="submit_get_translation" value="Get translation" onclick='get_translation(this); return false;'>
        <input class="btn-danger col-md-6 m-1" type="button" name="submit_stop_translation" value="Stop translation" onclick='window.location = "/cas?p=ball"'>

        <input type="text" name="position" value="0" hidden>
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

        if($("input[name='position_new']")[0].value < -800 || $("input[name='position_new']")[0].value > 800){
          $("#tooltip").removeClass('fade');
          $("#tooltip").text("Wrong parameters!");
          return false;
        }

        if(allow){
          allow = false;
        }
        var send_data = {
          'position_new': $("input[name='position_new']")[0].value,
          'position': $("input[name='position']")[0].value,
          'angle': $("input[name='angle']")[0].value,
          'token': 'Y29tcHV0ZXJfYWlkZWRfc3lzdGVt'
        };
        $.ajax({
          url:     "api/ball.php",
          type:     "POST",
          dataType: "json",
          contentType: "application/json",
          data: JSON.stringify(send_data),
          success: function(response) {
            allow = true;
            var t = 0;
            var inter = setTimeout(function moveImage(){
              animate(ctx, 1000 * response.data.x[t][0], response.data.x[t][2]);
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
                $("input[name='position']")[0].value = response.data.x[t][0];
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

      function set_translation(event){
        setInterval(function tranlsate(){
          var send_data = {
            'position': $("input[name='position']")[0].value,
            'angle': $("input[name='angle']")[0].value,
            'token': 'Y29tcHV0ZXJfYWlkZWRfc3lzdGVt'
          };
          $.ajax({
            url:     "api/ball_t.php",
            type:     "POST",
            dataType: "json",
            contentType: "application/json",
            data: JSON.stringify(send_data),
            success: function(response) {},
            error: function(response) {}
          });
        }, 100);

      }

      function get_translation(event){
        var t = 0;
        var graph_index = 0;
        setInterval(function tranlsate(){
          var send_data = {
            'token': 'Y29tcHV0ZXJfYWlkZWRfc3lzdGVt'
          };
          $.ajax({
            url:     "api/ball_t.php",
            type:     "POST",
            dataType: "json",
            contentType: "application/json",
            data: JSON.stringify(send_data),
            success: function(response) {
              animate(ctx, 1000 * response.data.x[0][0], response.data.x[0][2]);
              t++;

                position_points.push({
                  x: graph_index,
                  y: 10 * response.data.x[0][0]
                });
                angle_points.push({
                  x: graph_index,
                  y: 10 * response.data.x[0][2]
                });
                graph_index++;
                if((position_points.length + angle_points.length) > 100){
                  position_points.shift();
                  angle_points.shift();
                }
                chart.render();
                $("input[name='position']")[0].value = response.data.x[0][0];
                $("input[name='angle']")[0].value = response.data.x[0][2];

            },
            error: function(response) {}
          });

      }, 100);
    }
      </script>

    </form>

  </div>

</div>



<script>

  var canvas_width = 1000;
  var canvas_height = 300;
  var grid_size = 25;

  function draw_ball(ctx, position, angle){

    var radius = 16;
    var lever_height = 10;


    ctx.fillStyle = "rgb(0,0,0)";

    // Calculating position
    var pos = {};
    pos['x_lever_start'] = 0;
    pos['y_lever_start'] = (canvas_height / 2) - canvas_width / 2 * Math.tan(angle);
    pos['x_lever_end'] = canvas_width;
    pos['y_lever_end'] = (canvas_height / 2) + canvas_width / 2 * Math.tan(angle);

    pos['x'] = canvas_width / 2 + position * Math.cos(angle);
    pos['y'] = canvas_height / 2 + position * Math.sin(angle) - radius;

    pos['x_d1'] = canvas_width / 2;
    pos['y_d1'] = canvas_height / 2;

    pos['x_d2'] = canvas_width / 2 - 25;
    pos['y_d2'] = canvas_height / 2 + 50;

    pos['x_d3'] = canvas_width / 2 + 25;
    pos['y_d3'] = canvas_height / 2 + 50;

    // Drawing lever
    ctx.strokeStyle = "rgb(0,0,0)";
    ctx.beginPath();
    ctx.moveTo(pos['x_lever_start'], pos['y_lever_start']);
    ctx.lineTo(pos['x_lever_end'], pos['y_lever_end']);
    ctx.lineTo(pos['x_lever_start'], pos['y_lever_start']);
    ctx.stroke();

    // Drawing ball
    ctx.fillStyle = "rgb(170,0,0)";
    ctx.beginPath();
    ctx.arc(pos['x'], pos['y'], radius, 0, Math.PI*2, true);
    ctx.fill()

    // Drawing base
    ctx.fillStyle = "rgb(0,0,0)";
    ctx.beginPath();
    ctx.moveTo(pos['x_d1'], pos['y_d1']);
    ctx.lineTo(pos['x_d2'], pos['y_d2']);
    ctx.lineTo(pos['x_d3'], pos['y_d3']);
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

  function animate(ctx, x, angle){
    clear_canvas(ctx);
    draw_grid(ctx);
    draw_ball(ctx, x, angle);
  }

  var canvas = document.getElementById('ball');
  var ctx = canvas.getContext('2d');

  draw_grid(ctx);
  draw_ball(ctx, 0, 0);


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
