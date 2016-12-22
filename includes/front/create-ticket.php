<?php 

if ( ! defined( 'ABSPATH' ) ) {

    exit; // Exit if accessed directly

}

function customer_create_ticket() {  

     $uv=new UvdeskProtected();
    $client_key=$uv->get_client_key();
    $secret_key=$uv->get_secret_key();

    $uvdesk_access_token=get_option('uvdesk_access_token');
    $company_domain=get_option('uvdesk_company_domain');
    if(!empty($uvdesk_access_token) && !empty($company_domain)){

            if(isset($_POST['submit1'])){ 

                $captcha = "";
                        
                if(isset($_POST['g-recaptcha-response'])) {
               
                    $captcha=$_POST['g-recaptcha-response'];
                           
                }
               
                if(!$captcha) {

                    echo '<span class="captcha-empty">Please check the the captcha form.</span>';
               
                    return false;
               
                }
 
               
                $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret_key."&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']);

                $responseData = json_decode($response);

                if ($responseData->success==false) {
               
                    echo "<script>";
                    ?>

                    jQuery(".captcha-error").css('display','block');

                    <?php echo "</script>";
               
                    return false;
               
                }

                if ( isset( $_POST['uvdesk_create_ticket_nonce'] )) {
                    if (! wp_verify_nonce( $_POST['uvdesk_create_ticket_nonce'], 'uvdesk_create_ticket_nonce_action')) {
                        
                       print 'Sorry, your nonce did not verify.';

                       exit;
                    
                    } else {
                       
                       if(isset($_POST['subject']) && !empty($_POST['subject']) && isset($_POST['reply']) && !empty($_POST['reply']) && isset($_POST['type']) && !empty($_POST['type'])){
                            $user=wp_get_current_user();
                            $user_name=$user->user_nicename;
                            $user_email=$user->user_email; 
                            $_POST_data=array('name'=>$user_name,'from'=>$user_email,'subject'=>$_POST['subject'],'reply'=>$_POST['reply'],'type'=>'4');
                            if (!empty($user)) {
                                if(empty($_FILES)){
                                    $obj=new UVDESK_API();
                                    $ticket_status=UVDESK_API::create_new_ticket($_POST_data);                 
                                    $ticket_status=json_decode($ticket_status);
                                     echo '<div class="alert alert-success alert-fixed alert-load">
                                            <button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button>
                                            <span>
                                                <i class="fa fa-check pull-left" aria-hidden="true"></i>
                                                '.$ticket_status->message.'
                                            </span>
                                        </div>'; ?>
                                    <script>
                                     setTimeout(function() {
                                        jQuery(".alert-fixed").fadeOut()
                                    }, 4000);
                                    </script>
                                    <?php

                                }
                                else{
                                     if(isset($_FILES['attachments']) && !empty($_FILES['attachments'])){
                                        $ticket_status=UVDESK_API::create_new_ticket_with_attachement($_POST_data,$_FILES['attachments']);                 
                                        $ticket_status=json_decode($ticket_status); 
                                        
                                        echo '<div class="alert alert-success alert-fixed alert-load">
                                            <button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button>
                                            <span>
                                                <i class="fa fa-check pull-left" aria-hidden="true"></i>
                                                    '.$ticket_status->message.'
                                                </span>
                                            </div>'; ?>
                                            <script>
                                             setTimeout(function() {
                                                jQuery(".alert-fixed").fadeOut()
                                            }, 4000);
                                            </script>

                                        <?php                            
                                    }     
                                }
                                
                             } 
                             else{

                             }
                                

                       }
                    }
                }
            }
        }  
        else{

            echo "<h1>Please Enter a valid Access Token<h1>";

        }  

?>

<div class="main-body">
  
    <div class="container">
        
        <div class="col-sm-9 form">
         
            <div class="title">
         
                <h2>Create a Ticket</h2>
         
            </div>
         
            <div>
                
                <form name="" method="post" action="" enctype="multipart/form-data" novalidate="false" id="createTicketForm">

                    <?php wp_nonce_field( 'uvdesk_create_ticket_nonce_action', 'uvdesk_create_ticket_nonce' ); ?> 
                
                    <div enctype="multipart/form-data" novalidate="false" id="createTicketForm">
                
                        <div class="col-sm-12">
                
                            <div class="form-group required ">
                
                                <label for="type" class="required">Type</label>
                                
                                <select id="type" name="type" required="required" data-role="tagsinput" data-live-search="data-live-search" class="selectpicker" tabindex="-98">
                                
                                    <option value="" selected="selected">Choose query type</option>
                                
                                    <option value="87">Support</option>
                                
                                    <option value="88">Test</option>
                                
                                </select> 

                            </div>
                        
                        </div>
                        
                        <div class="col-sm-12">
                        
                            <div class="form-group required ">
                        
                                <label for="subject" class="required">Subject</label>
                        
                                <input type="text" id="subject" name="subject" required="required" placeholder="Enter Subject" class="form-control">
                        
                            </div>
                        
                        </div>
                        
                        <div class="col-sm-12">
                        
                            <div class="form-group required ">
                        
                                <label for="reply" class="required">Message</label>
                        
                                <textarea id="reply" name="reply" required="required" placeholder="Brief Description about your query" data-iconlibrary="fa" data-height="250" class="form-control"></textarea>
                        
                            </div>
                        
                        </div>
                        
                        <div class="col-sm-12">
                        
                            <div class="form-group required ">
                        
                                <div class="labelWidget">
                        
                                    <input type="file" id="attachments" name="attachments[]" required="required" infolabel="right" infolabeltext="+ Attach File" decoratefile="decorateFile" decoratecss="attach-file" enableremoveoption="enableRemoveOption" class="fileHide">
                        
                                    <label class="attach-file pointer"></label><i class="fa fa-times remove-file pointer"></i></div><span class="label-right pointer" id="addFile">+ Attach File</span></div>
                        </div>
                        
                        <input type="hidden" id="_token" name="_token" value="eJPW5s_yBH1S6iTM1eLI18Kdb304tl-IwIqE0ktJTd8">
                    
                    </div>
                    
                    <div class="clearfix"></div>
                    <?php 
                       
                        if(empty($client_key)){
                            $client_key='Check for client Keys';
                        }

                    ?>
                    <div class="g-recaptcha" id="recaptcha" data-sitekey="<?php echo $client_key; ?>" style="transform:scale(0.77);transform-origin:0;-webkit-transform:scale(0.77);transform:scale(0.77);-webkit-transform-origin:0 0;transform-origin:0 0;"></div>  
                    <div class="clearfix"></div>
                    <div class="col-sm-12">

                        <div class="captcha-error">Please verify that you are not a robot.</div>

                        <button type="submit" id="submit1" name="submit1" class="btn btn-md btn-info">Create Ticket</button>
                    </div>
                </form>
            </div>
        </div>
           
    </div>

</div>

<?php } ?>