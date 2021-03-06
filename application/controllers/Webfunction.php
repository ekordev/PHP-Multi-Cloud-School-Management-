<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webfunction extends CI_Controller {

public function __construct()
{
     parent::__construct();
  $this->load->model(array('websites_model','schools_model','users_model','blog_model','media_model','back_model'));
  $this->load->helper(array('url','form','blog_helper','page_helper','theme_helper'));
     $this->load->library(array('form_validation','user_agent'));


      if (!isset($this->session->id) || !isset($this->session->logged_in))
       {    
       show_page('login');     }


   $meta= $this->back_model->get_site_meta();

   
     $this->tagLine =$meta['title'];
     $this->siteName=$meta['sitename'];
     $this->keywords=$meta['keywords'];
     $this->description=$meta['description'];
     $this->author=$meta['author'];
      $this->noindex = '<META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">';
     $this->favicon = base_url('assets/images/favicon.ico');




}
 

public function add_news($address_id =NULL)
{
     $data['web_favicon_slug'] = "assets/images/favicon.ico";
      $data['title'] = $this->siteName." | Your website school in few minutes";
      $data['author'] =  $this->author;
      $data['keywords'] =  $this->keywords;
      $data['description'] =  $this->description;
      $data["noindex"] =  $this->noindex;
$data['show_editor'] = true;

$cond = array(
"subdomain" => $address_id
);
$theme_id = $this->websites_model->get_web_theme($cond)['theme_id'];

$theme = $this->websites_model->get_theme_by_id($theme_id);
$data["options"]  = json_decode($theme['feature_support'],true);

$data['theme_options'] = json_decode($theme['admin_options'],true);
//load theme settings here

     $this->form_validation->set_rules("title","Post Title","required");
     $this->form_validation->set_rules("contents","Post Contents","required");




  $config['upload_path'] = "assets/images/schoolsblog/";
  $config['allowed_types'] = 'gif|jpg|png|jpeg';
   $config['max_size'] = '5000';

 $this->load->library('upload', $config);

if($this->upload->do_upload('fimg'))
{
$up = 1;

}
  if($this->form_validation->run() == FALSE)
  {

    $this->load->view('users/admin/common/header_view',$data);
    $this->load->view('users/admin/common/nav_view',$data);
   $this->load->view('users/admin/common/web_sidebar_view',$data);
    $this->load->view('users/admin/common/content_top_view',$data);
    $this->load->view('users/admin/web/add_news_view',$data);
        $this->load->view('users/admin/common/media_sidebar_view',$data);
    $this->load->view('common/footer_view',$data);


  }else{
  //show next:input to db
  if($this->blog_model->insert_post($this->upload->data("file_name"),$this->uri->segment(3)))
  {
  //sucesspage
    $_SESSION['action_status_report'] = "<span class='w3-text-green'>Post Added successfully
  </span>";
$this->session->mark_as_flash('action_status_report');

      $slg = url_title($this->input->post('title'),"dash",TRUE);

  show_page("webfunction/edit_news/".$this->uri->segment(3)."/".$slg);

  }else{
  //error page
    $_SESSION['action_status_report'] = "<span class='w3-text-red'>Error Occurred
  </span>";
  $this->session->mark_as_flash('action_status_report');

  show_page("webfunction/add_news/".$this->uri->segment(3));

  }

}
}




public function add_event($address_id =NULL)
{

       $data['web_favicon_slug'] = "assets/images/favicon.ico";
      $data['title'] = $this->siteName." | Add Event";
      $data['author'] =  $this->author;
      $data['keywords'] =  $this->keywords;
      $data['description'] =  $this->description;
      $data["noindex"] =  $this->noindex;
$data['show_editor'] = true;

$cond = array(
"subdomain" => $address_id
);
$theme_id = $this->websites_model->get_web_theme($cond)['theme_id'];

$theme = $this->websites_model->get_theme_by_id($theme_id);
$data["options"]  = json_decode($theme['feature_support'],true);

$data['theme_options'] = json_decode($theme['admin_options'],true);
//load theme settings here

     $this->form_validation->set_rules("title","Event Title","required");
     $this->form_validation->set_rules("time","Event Time","required");
      $this->form_validation->set_rules("duration","Event Duration","required");
       $this->form_validation->set_rules("location","Event Location","required");

  if($this->form_validation->run() == FALSE)
  {

    $this->load->view('users/admin/common/header_view',$data);
    $this->load->view('users/admin/common/nav_view',$data);
   $this->load->view('users/admin/common/web_sidebar_view',$data);
    $this->load->view('users/admin/common/content_top_view',$data);
    $this->load->view('users/admin/web/add_event_view',$data);
        $this->load->view('users/admin/common/media_sidebar_view',$data);
    $this->load->view('common/footer_view',$data);


  }else{
  //show next:input to db
$user =$this->users_model->get_user_by_id();
$school = $this->schools_model->get_school_by_id($_SESSION['school_id']);
$ref =md5(time()/2.1);
      $event = array(
"title" =>  $this->input->post('title'),
"location" => $this->input->post('location'),
"duration" => $this->input->post('duration'),
"event_time" => $this->input->post('time'),
"subdomain"=>$this->uri->segment(3),
"ref"=> $ref,
"author" => $user['id'],
"school_id"=> $school['id'],
"status"=> "published",
"time"=> time()
 );


  if($this->websites_model->insert_newevent($event))
  {
  //sucesspage
    $_SESSION['action_status_report'] = "<span class='w3-text-green'>Event Added successfully
  </span>";
$this->session->mark_as_flash('action_status_report');


  show_page("webfunction/edit_event/".$this->uri->segment(3)."/".$ref);

  }else{
  //error page
    $_SESSION['action_status_report'] = "<span class='w3-text-red'>Error Occurred
  </span>";
  $this->session->mark_as_flash('action_status_report');

  show_page("webfunction/add_event/".$this->uri->segment(3));

  }

}

}



public function news_preview($address_id,$slug)
{

     $data['web_favicon_slug'] = "assets/images/favicon.ico";
      $data['title'] = $this->siteName." | News Preview";
      $data['author'] =  $this->author;
      $data['keywords'] =  $this->keywords;
      $data['description'] =  $this->description;
      $data["noindex"] =  $this->noindex;
$cond = array(
"subdomain" => $address_id
);
$theme_id = $this->websites_model->get_web_theme($cond)['theme_id'];

$theme = $this->websites_model->get_theme_by_id($theme_id);
$data["options"]  = json_decode($theme['feature_support'],true);

$data['theme_options'] = json_decode($theme['admin_options'],true);
//load theme settings here

$data['item'] = $this->blog_model->get_post_by_slug($slug);


if(empty($data['item']))
{

  show_404();
}

    $this->load->view('users/admin/common/header_view',$data);
    $this->load->view('users/admin/common/nav_view',$data);
   $this->load->view('users/admin/common/web_sidebar_view',$data);
    $this->load->view('users/admin/common/content_top_view',$data);
    $this->load->view('users/admin/web/preview_news_view',$data);
       // $this->load->view('users/admin/common/media_sidebar_view',$data);
    $this->load->view('common/footer_view',$data);
}
public function edit_news($address_id =NULL,$slug = NULL)
{

     $data['web_favicon_slug'] = "assets/images/favicon.ico";
      $data['title'] = $this->siteName." | Edit News";
      $data['author'] =  $this->author;
      $data['keywords'] =  $this->keywords;
      $data['description'] =  $this->description;
      $data["noindex"] =  $this->noindex;

$data['show_editor'] = true;

$cond = array(
"subdomain" => $address_id
);
$theme_id = $this->websites_model->get_web_theme($cond)['theme_id'];

$theme = $this->websites_model->get_theme_by_id($theme_id);
$data["options"]  = json_decode($theme['feature_support'],true);

$data['theme_options'] = json_decode($theme['admin_options'],true);
//load theme settings here





     $this->form_validation->set_rules("title","Post Title","required");
     $this->form_validation->set_rules("contents","Post Contents","required");



//edited:removed file upload function
  $config['upload_path'] = "assets/images/schoolsblog/";
  $config['allowed_types'] = 'gif|jpg|png|jpeg';
   $config['max_size'] = '1000';

 $this->load->library('upload', $config);
$data['item'] = $this->blog_model->get_post_by_slug($slug);

if($this->upload->do_upload('fimg'))
{
$up = 1;

}
  if($this->form_validation->run() == FALSE)
  {
if(empty($data['item']))
{

  show_404();
}

    $this->load->view('users/admin/common/header_view',$data);
    $this->load->view('users/admin/common/nav_view',$data);
   $this->load->view('users/admin/common/web_sidebar_view',$data);
    $this->load->view('users/admin/common/content_top_view',$data);
    $this->load->view('users/admin/web/edit_news_view',$data);
        $this->load->view('users/admin/common/media_sidebar_view',$data);
    $this->load->view('common/footer_view',$data);


  }else{
  //show next:input to db

    if(!empty($this->upload->data("file_name")))
    {
      $img_url = $this->upload->data("file_name");

    }else{

            $img_url =$item["img_url"];

    }
  if($this->blog_model->edit_post($img_url,$slug))
  {
  //sucesspage
    $_SESSION['action_status_report'] = "<span class='w3-text-green'>Post Edited successfully
  </span>";
$this->session->mark_as_flash('action_status_report');

  
  show_page("webfunction/edit_news/".$this->uri->segment(3)."/".$slug);

  }else{
  //error page
    $_SESSION['action_status_report'] = "<span class='w3-text-red'>Error Occurred
  </span>";
  $this->session->mark_as_flash('action_status_report');

  show_page("webfunction/edit_news/".$this->uri->segment(3)."/".$slug);

  }

}
}



public function edit_event($address_id =NULL,$ref = NULL)
{

     $data['web_favicon_slug'] = "assets/images/favicon.ico";
      $data['title'] = $this->siteName." | Edit Event";
      $data['author'] =  $this->author;
      $data['keywords'] =  $this->keywords;
      $data['description'] =  $this->description;
      $data["noindex"] =  $this->noindex;

$cond = array(
"subdomain" => $address_id
);
$theme_id = $this->websites_model->get_web_theme($cond)['theme_id'];

$theme = $this->websites_model->get_theme_by_id($theme_id);
$data["options"]  = json_decode($theme['feature_support'],true);

$data['theme_options'] = json_decode($theme['admin_options'],true);
//load theme settings here





          $this->form_validation->set_rules("title","Event Title","required");
     $this->form_validation->set_rules("time","Event Time","required");
      $this->form_validation->set_rules("duration","Event Duration","required");
       $this->form_validation->set_rules("location","Event Location","required");

  if($this->form_validation->run() == FALSE)
  {

$data['event'] = $this->websites_model->get_event_by_ref_id($ref);
if(empty($data['event']))
{
  show_404();
}
    $this->load->view('users/admin/common/header_view',$data);
    $this->load->view('users/admin/common/nav_view',$data);
   $this->load->view('users/admin/common/web_sidebar_view',$data);
    $this->load->view('users/admin/common/content_top_view',$data);
    $this->load->view('users/admin/web/edit_event_view',$data);
        $this->load->view('users/admin/common/media_sidebar_view',$data);
    $this->load->view('common/footer_view',$data);


  }else{
  //show next:input to db
 $event = array(
"title" =>  $this->input->post('title'),
"location" => $this->input->post('location'),
"subdomain"=>$this->uri->segment(3),
"duration" => $this->input->post('duration'),
"event_time" => $this->input->post('time'),
"status"=> "published"
);

  if($this->websites_model->edit_event($event,$ref))
  {
  //sucesspage
    $_SESSION['action_status_report'] = "<span class='w3-text-green'>Event Edited successfully
  </span>";
$this->session->mark_as_flash('action_status_report');

  
  show_page("webfunction/edit_event/".$this->uri->segment(3)."/".$ref);

  }else{
  //error page
    $_SESSION['action_status_report'] = "<span class='w3-text-red'>Error Occurred
  </span>";
  $this->session->mark_as_flash('action_status_report');

  show_page("webfunction/edit_event/".$this->uri->segment(3)."/".$ref);

  }

}





}



public function upload_image(){


  $config['upload_path'] = "assets/images/schoolsblog/";
  $config['allowed_types'] = 'gif|jpg|png|jpeg';
   $config['max_size'] = '3000';

 $this->load->library('upload', $config);

$this->upload->do_upload('image');
  echo base_url('assets/images/schoolsblog/'.$this->upload->data("file_name"));
   }

public function delete_item($address_id,$item_type = NULL,$id = NULL)
{

$this->blog_model->delete_item($item_type,$id,$address_id);

//sucesspage
    $_SESSION['action_status_report'] = "<span class='w3-text-green'>Item Deleted successfully
  </span>";
$this->session->mark_as_flash('action_status_report');

  if($item_type == "news")
  {
  show_page("webfunction/manage_news/".$this->uri->segment(3));

  }elseif($item_type == "page"){
  show_page("webfunction/manage_pages/".$this->uri->segment(3));


  }elseif ($item_type == "media") {
    show_page("webfunction/manage_media/".$this->uri->segment(3));
  }
elseif ($item_type == "subject") {
    show_page("dashboard/manage_subject");
  }elseif ($item_type == "fee") {
    show_page("dashboard/manage_fee");
  }elseif ($item_type == "staff") {
    show_page("dashboard/view_staff_list");
  }elseif ($item_type == "event") {
    show_page("webfunction/manage_events/".$this->uri->segment(3));
  }elseif ($item_type == "slider") {
    show_page("webfunction/manage_sliders/".$this->uri->segment(3));
  }

}


public function confirm_delete($address_id = NULL,$item_type = NULL,$id = NULL)
{

     $data['web_favicon_slug'] = "assets/images/favicon.ico";
      $data['title'] = $this->siteName." | Confirm Delete";
      $data['author'] =  $this->author;
      $data['keywords'] =  $this->keywords;
      $data['description'] =  $this->description;
      $data["noindex"] =  $this->noindex;
$data['item_type'] =$item_type;
$data['id'] =$id;
$data['address_id'] =$address_id;

$cond = array(
"subdomain" => $address_id
);
$theme_id = $this->websites_model->get_web_theme($cond)['theme_id'];

$theme = $this->websites_model->get_theme_by_id($theme_id);
$data["options"]  = json_decode($theme['feature_support'],true);

$data['theme_options'] = json_decode($theme['admin_options'],true);
//load theme settings here




    $this->load->view('users/admin/common/header_view',$data);
    $this->load->view('users/admin/common/nav_view',$data);
   //$this->load->view('users/admin/common/web_sidebar_view',$data);
   // $this->load->view('users/admin/common/content_top_view',$data);
    $this->load->view('users/admin/web/action_confirmation_view',$data);
    $this->load->view('common/footer_view',$data);

}

public function add_page($address_id =NULL)
{

     $data['web_favicon_slug'] = "assets/images/favicon.ico";
      $data['title'] = $this->siteName." | Add Page";
      $data['author'] =  $this->author;
      $data['keywords'] =  $this->keywords;
      $data['description'] =  $this->description;
      $data["noindex"] =  $this->noindex;
$data['show_editor'] = true;

$cond = array(
"subdomain" => $address_id
);
$theme_id = $this->websites_model->get_web_theme($cond)['theme_id'];

$theme = $this->websites_model->get_theme_by_id($theme_id);
$data["options"]  = json_decode($theme['feature_support'],true);

$data['theme_options'] = json_decode($theme['admin_options'],true);
//load theme settings here





     $this->form_validation->set_rules("title","Post Title","required");
     $this->form_validation->set_rules("contents","Post Contents","required");

  if($this->form_validation->run() == FALSE)
  {

    $this->load->view('users/admin/common/header_view',$data);
    $this->load->view('users/admin/common/nav_view',$data);
   $this->load->view('users/admin/common/web_sidebar_view',$data);
    $this->load->view('users/admin/common/content_top_view',$data);
    $this->load->view('users/admin/web/add_page_view',$data);
      $this->load->view('users/admin/common/media_sidebar_view',$data);
    $this->load->view('common/footer_view',$data);


  }else{
  //show next:input to db
  if($this->blog_model->insert_page($address_id))
  {
  //sucesspage
    $_SESSION['action_status_report'] = "<span class='w3-text-green'>Page Added successfully
  </span>";
$this->session->mark_as_flash('action_status_report');

  
  show_page("webfunction/add_page/".$this->uri->segment(3));

  }else{
  //error page
    $_SESSION['action_status_report'] = "<span class='w3-text-red'>Error Occurred
  </span>";
  $this->session->mark_as_flash('action_status_report');

  show_page("webfunction/add_page/".$this->uri->segment(3));

  }

}





}

public function change_favicon($address_id)
{

  $config['upload_path'] = "assets/media";
  $config['allowed_types'] = 'gif|jpg|png|jpeg|ico';
 $config['max_size'] = '2500';
   
 $this->load->library('upload', $config);
 $this->upload->do_upload('favicon');
if(!isset($_POST['submit']))
{

     $data['web_favicon_slug'] = "assets/images/favicon.ico";
      $data['title'] = $this->siteName." | Change Favicon";
      $data['author'] =  $this->author;
      $data['keywords'] =  $this->keywords;
      $data['description'] =  $this->description;
      $data["noindex"] =  $this->noindex;
$data['show_editor'] = true;

$cond = array(
"subdomain" => $address_id
);
$theme_id = $this->websites_model->get_web_theme($cond)['theme_id'];

$theme = $this->websites_model->get_theme_by_id($theme_id);
$data["options"]  = json_decode($theme['feature_support'],true);

$data['theme_options'] = json_decode($theme['admin_options'],true);
//load theme settings here

   $this->load->view('users/admin/common/header_view',$data);
    $this->load->view('users/admin/common/nav_view',$data);
   $this->load->view('users/admin/common/web_sidebar_view',$data);
    $this->load->view('users/admin/common/content_top_view',$data);
    $this->load->view('users/admin/web/add_favicon_view',$data);
    $this->load->view('common/footer_view',$data);




}else{
//process
  $image = $this->upload->data('file_name');

$this->websites_model->save_favicon($image);

$_SESSION['action_status_report']= "<span class='w3-text-green'>Action successfull</span>";
$this->session->mark_as_flash('action_status_report');
show_page('webfunction/change_favicon/'.$address_id);
}
}
public function add_media($address_id =NULL)
{
     $data['web_favicon_slug'] = "assets/images/favicon.ico";
      $data['title'] = $this->siteName." | Add Media";
      $data['author'] =  $this->author;
      $data['keywords'] =  $this->keywords;
      $data['description'] =  $this->description;
      $data["noindex"] =  $this->noindex;
$cond = array(
"subdomain" => $address_id
);
$theme_id = $this->websites_model->get_web_theme($cond)['theme_id'];

$theme = $this->websites_model->get_theme_by_id($theme_id);
$data["options"]  = json_decode($theme['feature_support'],true);

$data['theme_options'] = json_decode($theme['admin_options'],true);
//load theme settings here





     $this->form_validation->set_rules("title","Media Title","required");

  $config['upload_path'] = "assets/media";
  $config['allowed_types'] = 'gif|jpg|png|jpeg';
 $config['max_size'] = '2500';
   
 $this->load->library('upload', $config);
 $this->upload->do_upload('media');
  if($this->form_validation->run() == FALSE)
  {
//unset($_SESSION['action_status_report'] );

    $this->load->view('users/admin/common/header_view',$data);
    $this->load->view('users/admin/common/nav_view',$data);
   $this->load->view('users/admin/common/web_sidebar_view',$data);
    $this->load->view('users/admin/common/content_top_view',$data);
    $this->load->view('users/admin/web/add_media_view',$data);
      $this->load->view('users/admin/common/media_sidebar_view',$data);
    $this->load->view('common/footer_view',$data);


  }else{
  //show next:input to db
    $media_slug = $this->upload->data("file_name");
 
//work onthis later
 
$db_data = array('name'=> $this->input->post('title'),
'time'=>time(),
'link'=>  $media_slug,
'type' =>  $this->upload->data("image_type"),
'subdomain' => $this->uri->segment(3)
);
  if($this->db->insert('media',$db_data))
  {
  //sucesspage
    $_SESSION['action_status_report'] = "<span class='w3-text-green'>Media Added successfully
  </span>";
$this->session->mark_as_flash('action_status_report');

  
  show_page("webfunction/add_media/".$this->uri->segment(3));

  }else{
  //error page
    $_SESSION['action_status_report'] = "<span class='w3-text-red'>Error Occurred
  </span>";
  $this->session->mark_as_flash('action_status_report');

  show_page("webfunction/add_media/".$this->uri->segment(3));

  }


}
}



public function manage_sliders($address_id =NULL,$offset = 0)
{

    $limit = 7;
    $data['items'] = $this->websites_model->get_multiple_slider($offset,
    $limit,$address_id);

      $this->load->library('pagination');

      $config['base_url'] = site_url("webfunction/manage_events/".$address_id);

    $config['total_rows'] = count($this->websites_model->get_multiple_slider(NULL,
    NULL,$address_id));
    //  $config['total_rows'] = 10;
      $config['per_page'] = $limit;

      $config['uri_segment'] = 4;
      $config['first_tag_open'] = '<span class="w3-btn  w3-theme w3-text-white w3-round-xlarge">';
      $config['first_tag_close'] = '</span>';
      $config['last_tag_open'] = '<br><span class="w3-btn  w3-theme w3-text-white w3-round-xlarge">';
      $config['last_tag_close'] = '</span>';
      $config['first_link'] = 'First';



      $config['prev_link'] = 'Prev';
      $config['next_link'] = 'Next';
      $config['next_tag_open'] = '<span style="margin-left:20%" class="w3-btn w3-theme w3-text-white w3-round-xlarge">';
      $config['next_tag_close'] = '</span><br>';
      $config['prev_tag_open'] = '<span style="" class="w3-btn w3-theme w3-text-white w3-round-xlarge">';
      $config['prev_tag_close'] = '</span>';
      $config['num_tag_open'] = ' <span style="" class="w3-btn w3-theme w3-text-white w3-round-xlarge w3-tiny">';
      $config['num_tag_close'] = '</span>';
      $config['cur_tag_open'] = '<span style="" class="w3-btn w3-white w3-text-teal w3-round-xlarge">';
      $config['cur_tag_close'] = '</span>';
      $config['last_link'] = 'Last';
      $config['display_pages'] = TRUE;
      //$config["use_page_numbers"] = TRUE;


         $this->pagination->initialize($config);
    $data['pagination'] = $this->pagination->create_links();



     $data['web_favicon_slug'] = "assets/images/favicon.ico";
      $data['title'] = $this->siteName." | Manage Slider";
      $data['author'] =  $this->author;
      $data['keywords'] =  $this->keywords;
      $data['description'] =  $this->description;
      $data["noindex"] =  $this->noindex;
$cond = array(
"subdomain" => $address_id
);
$theme_id = $this->websites_model->get_web_theme($cond)['theme_id'];

$theme = $this->websites_model->get_theme_by_id($theme_id);
$data["options"]  = json_decode($theme['feature_support'],true);

$data['theme_options'] = json_decode($theme['admin_options'],true);
//load theme settings here


//data['items']

    $this->load->view('users/admin/common/header_view',$data);
    $this->load->view('users/admin/common/nav_view',$data);
   $this->load->view('users/admin/common/web_sidebar_view',$data);
    $this->load->view('users/admin/common/content_top_view',$data);
    $this->load->view('users/admin/web/manage_sliders_view',$data);
    $this->load->view('common/footer_view',$data);
}




public function add_slider($address_id =NULL)
{
     $data['web_favicon_slug'] = "assets/images/favicon.ico";
      $data['title'] = $this->siteName." | Add Slider";
      $data['author'] =  $this->author;
      $data['keywords'] =  $this->keywords;
      $data['description'] =  $this->description;
      $data["noindex"] =  $this->noindex;

$cond = array(
"subdomain" => $address_id
);
$theme_id = $this->websites_model->get_web_theme($cond)['theme_id'];

$theme = $this->websites_model->get_theme_by_id($theme_id);
$data["options"]  = json_decode($theme['feature_support'],true);

$data['theme_options'] = json_decode($theme['admin_options'],true);
//load theme settings here





     $this->form_validation->set_rules("title","Media Title","required");

  $config['upload_path'] = "assets/media";
  $config['allowed_types'] = 'gif|jpg|png|jpeg';
 $config['max_size'] = '2500';
   
 $this->load->library('upload', $config);
 $this->upload->do_upload('media');
  if($this->form_validation->run() == FALSE)
  {

    $this->load->view('users/admin/common/header_view',$data);
    $this->load->view('users/admin/common/nav_view',$data);
   $this->load->view('users/admin/common/web_sidebar_view',$data);
    $this->load->view('users/admin/common/content_top_view',$data);
    $this->load->view('users/admin/web/add_slider_view',$data);
      $this->load->view('users/admin/common/media_sidebar_view',$data);
    $this->load->view('common/footer_view',$data);


  }else{
  //show next:input to db
    $media_slug = $this->upload->data("file_name");
 
$user =$this->users_model->get_user_by_id();
$school = $this->schools_model->get_school_by_id($_SESSION['school_id']);
$ref =md5(time()/2.1);
 
$slider = array(
"title" =>  $this->input->post('title'),
"slug" => $media_slug,
"subdomain"=>$this->uri->segment(3),
"ref"=> $ref,
"author" => $user['id'],
"school_id"=> $school['id'],
"status"=> "published",
"time"=> time()
 );

  if($this->db->insert("sliders",$slider))
  {
  //sucesspage
    $_SESSION['action_status_report'] = "<span class='w3-text-green'>Slider Added successfully
  </span>";
$this->session->mark_as_flash('action_status_report');

  
  show_page("webfunction/add_slider/".$this->uri->segment(3));

  }else{
  //error page
    $_SESSION['action_status_report'] = "<span class='w3-text-red'>Error Occurred
  </span>";
  $this->session->mark_as_flash('action_status_report');

  show_page("webfunction/add_slider/".$this->uri->segment(3));

  }


}
}



public function edit_page($address_id =NULL,$slug = NULL)
{
     $data['web_favicon_slug'] = "assets/images/favicon.ico";
      $data['title'] = $this->siteName." | Edit Page";
      $data['author'] =  $this->author;
      $data['keywords'] =  $this->keywords;
      $data['description'] =  $this->description;
      $data["noindex"] =  $this->noindex;
$data['show_editor'] = true;

$cond = array(
"subdomain" => $address_id
);
$theme_id = $this->websites_model->get_web_theme($cond)['theme_id'];

$theme = $this->websites_model->get_theme_by_id($theme_id);
$data["options"]  = json_decode($theme['feature_support'],true);

$data['theme_options'] = json_decode($theme['admin_options'],true);
//load theme settings here





     $this->form_validation->set_rules("title","Post Title","required");
     $this->form_validation->set_rules("contents","Post Contents","required");
$data['item'] = $this->blog_model->get_page_by_slug($slug);

  if($this->form_validation->run() == FALSE)
  {

    $this->load->view('users/admin/common/header_view',$data);
    $this->load->view('users/admin/common/nav_view',$data);
   $this->load->view('users/admin/common/web_sidebar_view',$data);
    $this->load->view('users/admin/common/content_top_view',$data);
    $this->load->view('users/admin/web/edit_page_view',$data);
      $this->load->view('users/admin/common/media_sidebar_view',$data);
    $this->load->view('common/footer_view',$data);


  }else{
  //show next:input to db
  if($this->blog_model->edit_page($slug))
  {
  //sucesspage
    $_SESSION['action_status_report'] = "<span class='w3-text-green'>Page Edited successfully
  </span>";
$this->session->mark_as_flash('action_status_report');

  
  show_page("webfunction/edit_page/".$this->uri->segment(3)."/".$slug);

  }else{
  //error page
    $_SESSION['action_status_report'] = "<span class='w3-text-red'>Error Occurred
  </span>";
  $this->session->mark_as_flash('action_status_report');

  show_page("webfunction/edit_page/".$this->uri->segment(3)."/".$slug);

  }

}





}


public function manage_news($address_id =NULL,$offset = 0)
{

    $limit = 7;
    $data['items'] = $this->blog_model->get_multiple_news($offset,
    $limit,$address_id);

      $this->load->library('pagination');

      $config['base_url'] = site_url("webfunction/manage_news/".$address_id);

    $config['total_rows'] = count($this->blog_model->get_multiple_news(NULL,
    NULL,$address_id));
    //  $config['total_rows'] = 10;
      $config['per_page'] = $limit;

      $config['uri_segment'] = 4;
      $config['first_tag_open'] = '<span class="w3-btn  w3-theme w3-text-white w3-round-xlarge">';
      $config['first_tag_close'] = '</span>';
      $config['last_tag_open'] = '<br><span class="w3-btn  w3-theme w3-text-white w3-round-xlarge">';
      $config['last_tag_close'] = '</span>';
      $config['first_link'] = 'First';



      $config['prev_link'] = 'Prev';
      $config['next_link'] = 'Next';
      $config['next_tag_open'] = '<span style="margin-left:20%" class="w3-btn w3-theme w3-text-white w3-round-xlarge">';
      $config['next_tag_close'] = '</span><br>';
      $config['prev_tag_open'] = '<span style="" class="w3-btn w3-theme w3-text-white w3-round-xlarge">';
      $config['prev_tag_close'] = '</span>';
      $config['num_tag_open'] = ' <span style="" class="w3-btn w3-theme w3-text-white w3-round-xlarge w3-tiny">';
      $config['num_tag_close'] = '</span>';
      $config['cur_tag_open'] = '<span style="" class="w3-btn w3-white w3-text-teal w3-round-xlarge">';
      $config['cur_tag_close'] = '</span>';
      $config['last_link'] = 'Last';
      $config['display_pages'] = TRUE;
      //$config["use_page_numbers"] = TRUE;


         $this->pagination->initialize($config);
    $data['pagination'] = $this->pagination->create_links();



     $data['web_favicon_slug'] = "assets/images/favicon.ico";
      $data['title'] = $this->siteName." | Manage School News";
      $data['author'] =  $this->author;
      $data['keywords'] =  $this->keywords;
      $data['description'] =  $this->description;
      $data["noindex"] =  $this->noindex;

$cond = array(
"subdomain" => $address_id
);
$theme_id = $this->websites_model->get_web_theme($cond)['theme_id'];

$theme = $this->websites_model->get_theme_by_id($theme_id);
$data["options"]  = json_decode($theme['feature_support'],true);

$data['theme_options'] = json_decode($theme['admin_options'],true);
//load theme settings here


//data['items']

    $this->load->view('users/admin/common/header_view',$data);
    $this->load->view('users/admin/common/nav_view',$data);
   $this->load->view('users/admin/common/web_sidebar_view',$data);
    $this->load->view('users/admin/common/content_top_view',$data);
    $this->load->view('users/admin/web/manage_news_view',$data);
    $this->load->view('common/footer_view',$data);





}


public function manage_events($address_id =NULL,$offset = 0)
{

    $limit = 7;
    $data['items'] = $this->websites_model->get_multiple_events($offset,
    $limit,$address_id);

      $this->load->library('pagination');

      $config['base_url'] = site_url("webfunction/manage_events/".$address_id);

    $config['total_rows'] = count($this->websites_model->get_multiple_events(NULL,
    NULL,$address_id));
    //  $config['total_rows'] = 10;
      $config['per_page'] = $limit;

      $config['uri_segment'] = 4;
      $config['first_tag_open'] = '<span class="w3-btn  w3-theme w3-text-white w3-round-xlarge">';
      $config['first_tag_close'] = '</span>';
      $config['last_tag_open'] = '<br><span class="w3-btn  w3-theme w3-text-white w3-round-xlarge">';
      $config['last_tag_close'] = '</span>';
      $config['first_link'] = 'First';



      $config['prev_link'] = 'Prev';
      $config['next_link'] = 'Next';
      $config['next_tag_open'] = '<span style="margin-left:20%" class="w3-btn w3-theme w3-text-white w3-round-xlarge">';
      $config['next_tag_close'] = '</span><br>';
      $config['prev_tag_open'] = '<span style="" class="w3-btn w3-theme w3-text-white w3-round-xlarge">';
      $config['prev_tag_close'] = '</span>';
      $config['num_tag_open'] = ' <span style="" class="w3-btn w3-theme w3-text-white w3-round-xlarge w3-tiny">';
      $config['num_tag_close'] = '</span>';
      $config['cur_tag_open'] = '<span style="" class="w3-btn w3-white w3-text-teal w3-round-xlarge">';
      $config['cur_tag_close'] = '</span>';
      $config['last_link'] = 'Last';
      $config['display_pages'] = TRUE;
      //$config["use_page_numbers"] = TRUE;


         $this->pagination->initialize($config);
    $data['pagination'] = $this->pagination->create_links();




     $data['web_favicon_slug'] = "assets/images/favicon.ico";
      $data['title'] = $this->siteName." | Manage School Event";
      $data['author'] =  $this->author;
      $data['keywords'] =  $this->keywords;
      $data['description'] =  $this->description;
      $data["noindex"] =  $this->noindex;

$cond = array(
"subdomain" => $address_id
);
$theme_id = $this->websites_model->get_web_theme($cond)['theme_id'];

$theme = $this->websites_model->get_theme_by_id($theme_id);
$data["options"]  = json_decode($theme['feature_support'],true);

$data['theme_options'] = json_decode($theme['admin_options'],true);
//load theme settings here


//data['items']

    $this->load->view('users/admin/common/header_view',$data);
    $this->load->view('users/admin/common/nav_view',$data);
   $this->load->view('users/admin/common/web_sidebar_view',$data);
    $this->load->view('users/admin/common/content_top_view',$data);
    $this->load->view('users/admin/web/manage_events_view',$data);
    $this->load->view('common/footer_view',$data);





}


public function manage_media($address_id =NULL,$offset = 0)
{

  $limit = 4;
    $data['items'] = $this->media_model->get_media_contents($offset,
    $limit,$address_id);

      $this->load->library('pagination');

      $config['base_url'] = site_url("webfunction/manage_media/".$address_id);

    $config['total_rows'] = count($this->media_model->get_media_contents(NULL,
    NULL,$address_id));
    //  $config['total_rows'] = 10;
      $config['per_page'] = $limit;

      $config['uri_segment'] = 4;
      $config['first_tag_open'] = '<span class="w3-btn  w3-theme w3-text-white w3-round-xlarge">';
      $config['first_tag_close'] = '</span>';
      $config['last_tag_open'] = '<br><span class="w3-btn  w3-theme w3-text-white w3-round-xlarge">';
      $config['last_tag_close'] = '</span>';
      $config['first_link'] = 'First';



      $config['prev_link'] = 'Prev';
      $config['next_link'] = 'Next';
      $config['next_tag_open'] = '<span style="margin-left:20%" class="w3-btn w3-theme w3-text-white w3-round-xlarge">';
      $config['next_tag_close'] = '</span><br>';
      $config['prev_tag_open'] = '<span style="" class="w3-btn w3-theme w3-text-white w3-round-xlarge">';
      $config['prev_tag_close'] = '</span>';
      $config['num_tag_open'] = ' <span style="" class="w3-btn w3-theme w3-text-white w3-round-xlarge w3-tiny">';
      $config['num_tag_close'] = '</span>';
      $config['cur_tag_open'] = '<span style="" class="w3-btn w3-white w3-text-teal w3-round-xlarge">';
      $config['cur_tag_close'] = '</span>';
      $config['last_link'] = 'Last';
      $config['display_pages'] = TRUE;
      //$config["use_page_numbers"] = TRUE;


         $this->pagination->initialize($config);
    $data['pagination'] = $this->pagination->create_links();


     $data['web_favicon_slug'] = "assets/images/favicon.ico";
      $data['title'] = $this->siteName." | Manage Media";
      $data['author'] =  $this->author;
      $data['keywords'] =  $this->keywords;
      $data['description'] =  $this->description;
      $data["noindex"] =  $this->noindex;

$cond = array(
"subdomain" => $address_id
);
$theme_id = $this->websites_model->get_web_theme($cond)['theme_id'];

$theme = $this->websites_model->get_theme_by_id($theme_id);
$data["options"]  = json_decode($theme['feature_support'],true);

$data['theme_options'] = json_decode($theme['admin_options'],true);
//load theme settings here


//data['items']

    $this->load->view('users/admin/common/header_view',$data);
    $this->load->view('users/admin/common/nav_view',$data);
    $this->load->view('users/admin/common/web_sidebar_view',$data);
    $this->load->view('users/admin/common/content_top_view',$data);
    $this->load->view('users/admin/web/manage_media_view',$data);
    $this->load->view('common/footer_view',$data);

}
public function manage_pages($address_id =NULL,$offset = 0)
{

    $limit = 1;
    $data['items'] = $this->blog_model->get_multiple_pages($offset,
    $limit,$address_id);

      $this->load->library('pagination');

      $config['base_url'] = site_url("webfunction/manage_pages/".$address_id);

    $config['total_rows'] = count($this->blog_model->get_multiple_pages(NULL,
    NULL,$address_id));
    //  $config['total_rows'] = 10;
      $config['per_page'] = $limit;

      $config['uri_segment'] = 4;
      $config['first_tag_open'] = '<span class="w3-btn  w3-theme w3-text-white w3-round-xlarge">';
      $config['first_tag_close'] = '</span>';
      $config['last_tag_open'] = '<br><span class="w3-btn  w3-theme w3-text-white w3-round-xlarge">';
      $config['last_tag_close'] = '</span>';
      $config['first_link'] = 'First';



      $config['prev_link'] = 'Prev';
      $config['next_link'] = 'Next';
      $config['next_tag_open'] = '<span style="margin-left:20%" class="w3-btn w3-theme w3-text-white w3-round-xlarge">';
      $config['next_tag_close'] = '</span><br>';
      $config['prev_tag_open'] = '<span style="" class="w3-btn w3-theme w3-text-white w3-round-xlarge">';
      $config['prev_tag_close'] = '</span>';
      $config['num_tag_open'] = ' <span style="" class="w3-btn w3-theme w3-text-white w3-round-xlarge w3-tiny">';
      $config['num_tag_close'] = '</span>';
      $config['cur_tag_open'] = '<span style="" class="w3-btn w3-white w3-text-teal w3-round-xlarge">';
      $config['cur_tag_close'] = '</span>';
      $config['last_link'] = 'Last';
      $config['display_pages'] = TRUE;
      //$config["use_page_numbers"] = TRUE;


         $this->pagination->initialize($config);
    $data['pagination'] = $this->pagination->create_links();




     $data['web_favicon_slug'] = "assets/images/favicon.ico";
      $data['title'] = $this->siteName." | Manage Webpages";
      $data['author'] =  $this->author;
      $data['keywords'] =  $this->keywords;
      $data['description'] =  $this->description;
      $data["noindex"] =  $this->noindex;


$cond = array(
"subdomain" => $address_id
);
$theme_id = $this->websites_model->get_web_theme($cond)['theme_id'];

$theme = $this->websites_model->get_theme_by_id($theme_id);
$data["options"]  = json_decode($theme['feature_support'],true);

$data['theme_options'] = json_decode($theme['admin_options'],true);
//load theme settings here


//data['items']

    $this->load->view('users/admin/common/header_view',$data);
    $this->load->view('users/admin/common/nav_view',$data);
   $this->load->view('users/admin/common/web_sidebar_view',$data);
    $this->load->view('users/admin/common/content_top_view',$data);
    $this->load->view('users/admin/web/manage_pages_view',$data);
    $this->load->view('common/footer_view',$data);





}



}