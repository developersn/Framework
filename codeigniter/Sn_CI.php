<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sn_CI extends CI_Controller {

  
     private $ERR;
     private $msg;
     private $callback;
     private $api="yourapi";// پین خود را در این مکان وارد نمایید
     private $url_request = 'https://developerapi.net/api/v1/request';
     private $url_verify = 'https://developerapi.net/api/v1/verify';


   
    public function getError()
    {
        return $this->ERR;
    }

   

    public function callback()
    {
                 $this->load->helper('url');     
                 $this->callback=site_url()."/sn_ci/sn_verify";
                 return $this->callback;
    }

    public function url_request()
    {
                     
                 $this->url_request='https://developerapi.net/api/v1/request';
                 return $this->url_request;
    }
     public function url_verify()
    {
                     
                 $this->url_verify='https://developerapi.net/api/v1/verify';
                 return $this->url_verify;
    }


    public function sn()
    {

                  
                   $amount=$_POST['amount'];//مبلغ پست شده 
                   $order=$_POST['order'];// شماره فاکتور پست شده


      
                    $callback=$this->callback();
                    $data_string = json_encode(array(
                    'pin'=> $this->api,
                    'price'=>$amount,
                    'callback'=>$callback,
                    'order_id'=> $order,
                    'ip'=> $_SERVER['REMOTE_ADDR'],
                    'callback_type'=>2
                    ));

                   $url_request=$this->url_request();
                    $ch = curl_init($url_request);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data_string))
                    );
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
                    $result = curl_exec($ch);
                    curl_close($ch);
                    $json = json_decode($result,true);


  
if(!empty($json['result']) AND $json['result'] == 1)

                    { 

                    //set sission
                      $this->load->library('session');
                      $newdata = array(
                            'au'  => $json['au'],
                             'amount'=>$_POST['amount'],
                             'order'=>$_POST['order']  
                                       );
                     $this->session->set_userdata($newdata);
                    //set sission    

                    echo "<div style='display:none'>{$json['form']}</div>Please wait ... <script language='javascript'>document.payment.submit(); </script>";          
                    }


                    else
                    {
                        
                        $msg=$this->get_msg($json['result']);
                        $this->ERR=$msg;
                        
                            echo $this->ERR;
                         
                    }

}//end function

    public function sn_verify()
    {
                        	  $this->load->library('session');
                              $au= $this->session->userdata('au');
                              $amount= $this->session->userdata('amount');
                              $order= $this->session->userdata('order');
                    

        
                                    $bank_return = $_POST + $_GET ;
                                    $data_string = json_encode(array (
                                    'pin' => $this->api,
                                    'price' =>  $amount,
                                    'order_id' => $order,
                                    'au' =>  $au,
                                    'bank_return' =>$bank_return,
                                    ));

                                    $ch = curl_init($this->url_verify);
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                    'Content-Type: application/json',
                                    'Content-Length: ' . strlen($data_string))
                                    );
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
                                    $result = curl_exec($ch);
                                    curl_close($ch);
                                    $json = json_decode($result,true);
                                 

                 if(!empty($json['result']) AND $json['result'] == 1)

                    { 

                       // اگر پرداخت موفق بود در این قسمت میتوانید عملیات یا  پیام ها مورد نظر خود را وارد نمایید.
                    echo "Payment operation successfully completed";          
                    }


                    else
                    {
                        
                        $msg=$this->get_msg($json['result']);
                        $newmsg=$this->ERR=$msg;
                       
                            echo $this->ERR;
                        
                    }
    }



  public function get_msg($msg)
    {

     $res=$msg;
                 
                     switch ($res) {

                         case 0:
                            $msg = "مشکلی از سمت بانک رخ داده است.پرداخت انجام نشد";
                            break;
                            case -1:
                            $msg = "پارامترهای ارسالی برای متد مورد نظر ناقص یا خالی هستند . پارمترهای اجباری باید ارسال گردد";
                            break;
                             case -2:
                            $msg = "دسترسی api برای شما مسدود است";
                            break;
                             case -6:
                            $msg = "عدم توانایی اتصال به گیت وی بانک از سمت وبسرویس";
                            break;

                             case -9:
                            $msg = "خطای ناشناخته";
                            break;

                             case -20:
                            $msg = "پین نامعتبر";
                            break;
                             case -21:
                            $msg = "ip نامعتبر";
                            break;

                             case -22:
                            $msg = "مبلغ وارد شده کمتر از حداقل مجاز میباشد";
                            break;


                            case -23:
                            $msg = "مبلغ وارد شده بیشتر از حداکثر مبلغ مجاز هست";
                            break;
                            
                              case -24:
                            $msg = "مبلغ وارد شده نامعتبر";
                            break;
                            
                              case -26:
                            $msg = "درگاه غیرفعال است";
                            break;
                            
                              case -27:
                            $msg = "آی پی مسدود شده است";
                            break;
                            
                              case -28:
                            $msg = "آدرس کال بک نامعتبر است ، احتمال مغایرت با آدرس ثبت شده";
                            break;
                            
                              case -29:
                            $msg = "آدرس کال بک خالی یا نامعتبر است";
                            break;
                            
                              case -30:
                            $msg = "چنین تراکنشی یافت نشد";
                            break;
                            
                              case -31:
                            $msg = "تراکنش ناموفق است";
                            break;
                            
                              case -32:
                            $msg = "مغایرت مبالغ اعلام شده با مبلغ تراکنش";
                            break;
                         
                            
                              case -35:
                            $msg = "شناسه فاکتور اعلامی order_id نامعتبر است";
                            break;
                            
                              case -36:
                            $msg = "پارامترهای برگشتی بانک bank_return نامعتبر است";
                            break;
                                case -38:
                            $msg = "تراکنش برای چندمین بار وریفای شده است";
                            break;
                            
                              case -39:
                            $msg = "تراکنش در حال انجام است";
                            break;
                            
                            case 1:
                            $msg = "پرداخت با موفقیت انجام گردید.";
                            break;

                            default:
                               $msg = 0;
                        }
                        return $msg;
                        }
            



}///class
?>