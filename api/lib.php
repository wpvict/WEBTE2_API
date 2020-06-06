<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../include/phpmail/Exception.php';
require '../include/phpmail/PHPMailer.php';
require '../include/phpmail/SMTP.php';

include_once "../include/DB.php";

class API{

  protected $db;
  // protected $path_bash = "cd C:\WEB\localhost\cas\scripts\??? && bash script.sh";
  protected $path_bash = "sudo octave /home/xruban/public_html/cas/scripts/???/octave_script.sh";
  // protected $path_script = '../scripts/???/octave_script.sh';
  protected $path_script = '/home/xruban/public_html/cas/scripts/???/octave_script.sh';
  protected $data;
  protected $allow;

  function __construct(){

    include_once "../config/db_config.php";

    $this->data = json_decode(file_get_contents("php://input"));

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");

    $this->allow = false;
    if(isset($this->data->token)){
      if($this->data->token == $api_valid_token){
        $this->allow = true;
      }
    } else {
      $this->allow = false;
      http_response_code(403);
      echo json_encode(array("message" => "Invalid token."), JSON_UNESCAPED_UNICODE);
    }

    $this->db = new DB($db_settings['host'], $db_settings['db_name'], $db_settings['user'], $db_settings['password']);
  }

  public function prepare_raw_output($output){
    $final = [];
    for($i = 0; $i < count($output); $i++){
      $final[] = $output[$i] . "\n";
    }
    return $final;
  }

  public function prepare_code($code){
    $code = "pkg load control;\r\n" . $code;
    $pattern = "/plot\(.*\)/";
    $include = "plot(";
    $include_back = ")";

    if(preg_match_all($pattern, $code, $matches)){
      foreach($matches as $item){
        $values = $item;
        $values = str_replace($include, "", $values);
        $values = str_replace($include_back, "", $values);

        $code = str_replace($item, $values, $code);
      }
    }

    return $code;
  }

  public function parse_output($arg){
    $result = [];
    $length_arg = count($arg);
    if($length_arg == 1){
      $exp_var = str_replace(" ", "", $arg[0]);
      $exp_var = explode("=", $exp_var);
      $result[$exp_var[0]] = $exp_var[1];
    } else {
      for($i = 0; $i < $length_arg; $i++){
        if(strstr($arg[$i], "=")){
          $name = str_replace(" =", "", $arg[$i]);
          $count = 1;
          while(isset($result[$name])){
            $count++;
            $name .= "_" . $count;
          }
          $j = $i + 1;
          while(!strstr($arg[$j], "=")){
            if(!empty($arg[$j])){
              $arg[$j] = trim($arg[$j]);
              $middle = explode(" ", $arg[$j]);
              $init_len = count($middle);
              for($t = 0; $t < $init_len; $t++){
                if(empty($middle[$t])){
                  unset($middle[$t]);
                }
              }
              $result[$name][] = array_values($middle);
            }
            if($j < (count($arg) - 1)){
              $j++;
            } else {
              $i = $j;
              break;
            }

          }
        }
      }
    }

    return $result;
  }

  protected function logging($commands, $success, $error = NULL){
    $date = new DateTime();
    $date = $date->format('Y-m-d H:i:sP');
    $this->db->add_log($commands, $success, $error, $date);
  }

  public function execute(){
    if(!$this->allow){
      return 1;
    }

    $path_script = str_replace("???", "interactive", $this->path_script);
    $path_bash = str_replace("???", "interactive", $this->path_bash);

    if(!empty($this->data->code)){

      $result = [];

      $code = $this->prepare_code($this->data->code);

      file_put_contents($path_script, $code);

      exec($path_bash, $var, $var_stat);

      $result['data'] = $this->parse_output($var);

      $result['raw'] = $this->prepare_raw_output($var);

      if(count($var) && $var_stat == 127){
        $status = true;
        $error = NULL;
      } else {
        $status = false;
        $result['error'] = "Error in code!";
        $error = "Error in code!";
      }

      $result['status'] = $status;

      http_response_code(200);
      echo json_encode($result);
    } else {
      http_response_code(404);
      echo json_encode(array("message" => "Invalid parameters."), JSON_UNESCAPED_UNICODE);
    }

    $this->logging($code, $status, $error);
  }

  public function pendulum(){
    if(!$this->allow){
      return 1;
    }

    $path_script = str_replace("???", "pendulum", $this->path_script);
    $path_bash = str_replace("???", "pendulum", $this->path_bash);

    if(isset($this->data->position) && isset($this->data->position_new) && isset($this->data->angle) ){

      $result = [];

      $code ="pkg load control
              M = .5;
              m = 0.2;
              b = 0.1;
              I = 0.006;
              g = 9.8;
              l = 0.3;
              p = I*(M+m)+M*m*l^2;
              A = [0 1 0 0; 0 -(I+m*l^2)*b/p (m^2*g*l^2)/p 0; 0 0 0 1; 0 -(m*l*b)/p m*g*l*(M+m)/p 0];
              B = [ 0; (I+m*l^2)/p; 0; m*l/p];
              C = [1 0 0 0; 0 0 1 0];
              D = [0; 0];
              K = lqr(A,B,C'*C,1);
              Ac = [(A-B*K)];
              N = -inv(C(1,:)*inv(A-B*K)*B);

              sys = ss(Ac,B*N,C,D);

              t = 0:0.05:10;
              r ={$this->data->position_new};
              initPozicia={$this->data->position};
              initUhol={$this->data->angle};
              [y,t,x]=lsim(sys,r*ones(size(t)),t,[initPozicia;0;initUhol;0]);
              x
              ";

      file_put_contents($path_script, $code);

      exec($path_bash, $var, $var_stat);

      $result['data'] = $this->parse_output($var);

      $result['raw'] = $this->prepare_raw_output($var);

      if(count($var)){
        $status = true;
        $error = NULL;
      } else {
        $status = false;
        $result['error'] = "Error in code!";
        $error = "Error in code!";
      }

      $result['status'] = $status;

      http_response_code(200);
      echo json_encode($result);
    } else {
      http_response_code(404);
      echo json_encode(array("message" => "Invalid parameters."), JSON_UNESCAPED_UNICODE);
    }

    $this->logging($code, $status, $error);
  }

  public function ball(){
    if(!$this->allow){
      return 1;
    }

    $path_script = str_replace("???", "ball", $this->path_script);
    $path_bash = str_replace("???", "ball", $this->path_bash);

    if(isset($this->data->position) && isset($this->data->position_new) && isset($this->data->angle) ){

      $result = [];

      $code ="pkg load control
              m = 0.111;
              R = 0.015;
              g = -9.8;
              J = 9.99e-6;
              H = -m*g/(J/(R^2)+m);
              A = [0 1 0 0; 0 0 H 0; 0 0 0 1; 0 0 0 0];
              B = [0;0;0;1];
              C = [1 0 0 0];
              D = [0];
              K = place(A,B,[-2+2i,-2-2i,-20,-80]);
              N = -inv(C*inv(A-B*K)*B);

              sys = ss(A-B*K,B,C,D);

              t = 0:0.01:5;
              r ={$this->data->position_new};
              initRychlost={$this->data->position};
              initZrychlenie={$this->data->angle};
              [y,t,x]=lsim(N*sys,r*ones(size(t)),t,[initRychlost;0;initZrychlenie;0]);
              x
              ";

      file_put_contents($path_script, $code);

      exec($path_bash, $var, $var_stat);

      $result['data'] = $this->parse_output($var);

      $result['raw'] = $this->prepare_raw_output($var);

      if(count($var)){
        $status = true;
        $error = NULL;
      } else {
        $status = false;
        $result['error'] = "Error in code!";
        $error = "Error in code!";
      }

      $result['status'] = $status;

      http_response_code(200);
      echo json_encode($result);
    } else {
      http_response_code(404);
      echo json_encode(array("message" => "Invalid parameters."), JSON_UNESCAPED_UNICODE);
    }

    $this->logging($code, $status, $error);
  }

  public function suspension(){
    if(!$this->allow){
      return 1;
    }

    $path_script = str_replace("???", "suspension", $this->path_script);
    $path_bash = str_replace("???", "suspension", $this->path_bash);

    if(isset($this->data->position) && isset($this->data->auto_position[0]) && isset($this->data->auto_position[1]) && isset($this->data->wheel_position[0]) && isset($this->data->wheel_position[1]) ){

      $result = [];

      $code ="pkg load control
              m1 = 2500; m2 = 320;
              k1 = 80000; k2 = 500000;
              b1 = 350; b2 = 15020;
              A=[0 1 0 0;-(b1*b2)/(m1*m2) 0 ((b1/m1)*((b1/m1)+(b1/m2)+(b2/m2)))-(k1/m1) -(b1/m1);b2/m2 0 -((b1/m1)+(b1/m2)+(b2/m2)) 1;k2/m2 0 -((k1/m1)+(k1/m2)+(k2/m2)) 0];
              B=[0 0;1/m1 (b1*b2)/(m1*m2);0 -(b2/m2);(1/m1)+(1/m2) -(k2/m2)];
              C=[0 0 1 0]; D=[0 0];
              Aa = [[A,[0 0 0 0]'];[C, 0]];
              Ba = [B;[0 0]];
              Ca = [C,0]; Da = D;
              K = [0 2.3e6 5e8 0 8e6];
              sys = ss(Aa-Ba(:,1)*K,Ba,Ca,Da);

              t = 0:0.01:5;
              r ={$this->data->position};
              initX1={$this->data->auto_position[0]};
              initX1d={$this->data->auto_position[1]};
              initX2={$this->data->wheel_position[0]};
              initX2d={$this->data->wheel_position[1]};
              [y,t,x]=lsim(sys*[0;1],r*ones(size(t)),t,[initX1;initX1d;initX2;initX2d;0]);
              x
              ";

      file_put_contents($path_script, $code);

      exec($path_bash, $var, $var_stat);

      $result['data'] = $this->parse_output($var);

      $result['raw'] = $this->prepare_raw_output($var);

      if(count($var)){
        $status = true;
        $error = NULL;
      } else {
        $status = false;
        $result['error'] = "Error in code!";
        $error = "Error in code!";
      }

      $result['status'] = $status;

      http_response_code(200);
      echo json_encode($result);
    } else {
      http_response_code(404);
      echo json_encode(array("message" => "Invalid parameters."), JSON_UNESCAPED_UNICODE);
    }

    $this->logging($code, $status, $error);
  }

  public function aircraft(){
    if(!$this->allow){
      return 1;
    }

    $path_script = str_replace("???", "aircraft", $this->path_script);
    $path_bash = str_replace("???", "aircraft", $this->path_bash);

    if(isset($this->data->position) && isset($this->data->angle_plane) && isset($this->data->angle_wing) && isset($this->data->angle)){

      $result = [];

      $code ="pkg load control
              A = [-0.313 56.7 0; -0.0139 -0.426 0; 0 56.7 0];
              B = [0.232; 0.0203; 0];
              C = [0 0 1];
              D = [0];

              p = 2;
              K = lqr(A,B,p*C'*C,1);
              N = -inv(C(1,:)*inv(A-B*K)*B);

              sys = ss(A-B*K, B*N, C, D);

              t = 0:0.1:40;
              r ={$this->data->position};
              initAlfa={$this->data->angle_plane};
              initQ={$this->data->angle_wing};
              initTheta={$this->data->angle};
              [y,t,x]=lsim(sys,r*ones(size(t)),t,[initAlfa;initQ;initTheta]);
              x
              z = r*ones(size(t))*N-x*K'
              ";

      file_put_contents($path_script, $code);

      exec($path_bash, $var, $var_stat);

      $result['data'] = $this->parse_output($var);

      $result['raw'] = $this->prepare_raw_output($var);

      if(count($var)){
        $status = true;
        $error = NULL;
      } else {
        $status = false;
        $result['error'] = "Error in code!";
        $error = "Error in code!";
      }

      $result['status'] = $status;

      http_response_code(200);
      echo json_encode($result);
    } else {
      http_response_code(404);
      echo json_encode(array("message" => "Invalid parameters."), JSON_UNESCAPED_UNICODE);
    }

    $this->logging($code, $status, $error);
  }

  public function pendulum_t(){
    if(!$this->allow){
      return 1;
    }

    if(isset($this->data->position) && isset($this->data->angle)){
      $this->db->pendulum_set_values($this->data->position, $this->data->angle);
    } else{
      if(true){

      $result = [];

      $x = $this->db->pendulum_get_values()[0];

      $result['data'] = ['x' => [[$x[1],0,$x[2]]]];

      $result['status'] = true;

      http_response_code(200);
      echo json_encode($result);
    } else {
      http_response_code(404);
      echo json_encode(array("message" => "Invalid parameters."), JSON_UNESCAPED_UNICODE);
    }
  }
  }

  public function ball_t(){
    if(!$this->allow){
      return 1;
    }

    if(isset($this->data->position) && isset($this->data->angle)){
      $this->db->ball_set_values($this->data->position, $this->data->angle);
    } else{
      if(true){

      $result = [];

      $x = $this->db->ball_get_values()[0];

      $result['data'] = ['x' => [[$x[1],0,$x[2]]]];

      $result['status'] = true;

      http_response_code(200);
      echo json_encode($result);
    } else {
      http_response_code(404);
      echo json_encode(array("message" => "Invalid parameters."), JSON_UNESCAPED_UNICODE);
    }
  }
  }

  public function mail(){

    if(isset($this->data->email)){

      $mail = new PHPMailer(true);

      try {
        //Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp.mail.com';                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'xstuba@null.net';                     // SMTP username
        $mail->Password   = 'Skuska20';                               // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

        //Recipients
        $mail->setFrom('xstuba@null.net', 'Stuba Sender');
        $mail->addAddress($this->data->email);     // Add a recipient

        // Content

        $statistics = $this->db->get_statistics();

        $body = "Tasks usage:<br/><br/>";
        foreach($statistics as $key => $value){
          $body .= $key . ": " . $value . "<br/>\r\n";
        }

        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'Statistics from wt128.fei.stuba.sk:8128/cas/';
        $mail->Body    = $body;

        $mail->send();

        $result = [];
        $result['status'] = true;

        http_response_code(200);
        echo json_encode($result);
      } catch (Exception $e) {
        // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        http_response_code(404);
        echo json_encode(array("status" => false), JSON_UNESCAPED_UNICODE);
      }

    } else {
      http_response_code(404);
      echo json_encode(array("message" => "Invalid parameters."), JSON_UNESCAPED_UNICODE);
    }
  }

};

 ?>
