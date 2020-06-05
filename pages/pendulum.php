
<div class="container-fluid">
  <div class="row">

  </div>
  <div class="row">
    <div class="col-md-9">
      <div class="" id="graph" style="height: 300px; width: 100%"></div>
      <canvas class='ml-5' id="pendulum" width="1000" height="300"></canvas>
    </div>
    <form class="col-md-3 alert-success" action="" method="post">
      <p class='h3 m-1 mb-3 text-center'>Input position</p>

      <div class="row justify-content-md-center">

        <input class="col-md-4" type="text" name="position_new" value="" placeholder='Type position here'>
        <input class="btn-info col-md-4 m-1" type="button" name="submit_get_coords" value="Move lever" onclick='get_result(this); return false;'>

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
              $("#pendulum").toggle();
            } else {
              $("#pendulum").toggle();
            }
          }
        </script>

        <input type="text" name="position" value="0" hidden>
        <input type="text" name="angle" value="0" hidden>
      </div>

      <script type="text/javascript">
      var allow = true;
      function get_result(event){
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
          url:     "api/pendulum.php",
          type:     "POST",
          dataType: "json",
          contentType: "application/json",
          data: JSON.stringify(send_data),
          success: function(response) {
            allow = true;
            var t = 0;
            var inter = setTimeout(function moveImage(){
              animate(ctx, grid_size * response.data.x[t][0], response.data.x[t][2]);
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
      </script>

    </form>

  </div>

</div>



<script>

  var canvas_width = 1000;
  var canvas_height = 300;
  var grid_size = 25;

  function draw_pendulum(ctx, position, angle){

    var width = 100;
    var height = 25;
    var lever_height = 80;


    ctx.fillStyle = "rgb(0,0,0)";


    // Calculating position
    var pos = {};
    pos['x'] = (canvas_width / 2) - (width / 2) + position;
    pos['y'] = (canvas_height / 2) - (height / 2);
    pos['x_lever_start'] = (canvas_width / 2) + position;
    pos['y_lever_start'] = pos['y'];
    pos['x_lever_end'] = pos['x_lever_start'] + lever_height * Math.sin(angle);
    pos['y_lever_end'] = pos['y_lever_start'] - lever_height * Math.cos(angle);

    // Drawing block
    ctx.fillRect(pos['x'], pos['y'], width, height);

    // Drawing lever
    ctx.beginPath();
    ctx.moveTo(pos['x_lever_start'], pos['y_lever_start']);
    ctx.lineTo(pos['x_lever_end'], pos['y_lever_end']);
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

  function animate(ctx, x, angle){
    clear_canvas(ctx);
    draw_grid(ctx);
    draw_pendulum(ctx, x, angle);
  }

  var canvas = document.getElementById('pendulum');
  var ctx = canvas.getContext('2d');

  draw_grid(ctx);
  draw_pendulum(ctx, 0, 0);


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
