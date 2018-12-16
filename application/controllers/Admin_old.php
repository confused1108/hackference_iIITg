<?php
/**
 * Created by PhpStorm.
 * User: Hitesh
 * Date: 20-Sep-18
 * Time: 10:48 PM
 */ 
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     *	- or -
     * 		http://example.com/index.php/welcome/index
     *	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->library('session');
        $this->load->helper('string');
    }
    public function login_view()
    {
        $this->load->view('admin/admin_login');
    }
    public function admin_login_check(){
        $data=array();
        $data['username']=$_POST['username'];
        $data['password']=sha1($_POST['password']);
        $this->load->Model('AdminModel');
        $sheet = $this->AdminModel->admin_login_check($data);

        if($sheet['status']==TRUE){
            $admin_id=$sheet['admin_id'];
            $this->session->set_userdata('admin_id',$admin_id);
            redirect(CTRL."Admin/admin_cat");
        }
        else{
            redirect(CTRL."Admin/login_view#error");
        }
    }
    public function admin_cat()
    {
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->admin_cat();
        $this->load->view('admin/admin_cat',$sheet);
    }
    public function admin_org()
    {
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->admin_org();
        $this->load->view('admin/admin_org',$sheet);
    }
    public function add_new_org(){
        $data=array();
        $data['org_name']=$_POST['org_name'];
        $data['type']=$_POST['type'];
        $data['address']=$_POST['address'];
        $data['city']=$_POST['city'];
        $data['district']=$_POST['district'];
        $data['state']=$_POST['state'];
        $data['country']=$_POST['country'];
        $data['pincode']=$_POST['pincode'];
        $data['email']=$_POST['email'];
        $data['website']=$_POST['website'];
        $data['phone']=$_POST['phone'];
        $data['phone_alt']=$_POST['phone_alt'];
        $data['contact_person']=$_POST['contact_person'];
        $data['contact_person_desg']=$_POST['contact_person_desg'];
        $data['contact_person_email']=$_POST['contact_person_email'];
        
        $cat=$_POST['cat'];
        $sports="";
        $coma=",";
        foreach ($cat as $key => $n) {
            $one="1";
            $sports=$sports."".$n."".$coma;
        }

        $ag=$_POST['age'];
        $age="";
        $coma=",";
        foreach ($ag as $key => $n) {
            $one="1";
            $age=$age."".$n."".$coma;
        }

        //remove the last 2 character at end of string
        $sports=substr($sports, 0, -1);
        $age=substr($age, 0, -1);
        $data['sports']=$sports;
        $data['age']=$age;
        $this->load->Model('AdminModel');
        $sheet = $this->AdminModel->add_new_org($data);
        redirect(CTRL."Admin/admin_org");
    }
    public function update_org($org_id){
        $data=array();
        $data['org_name']=$_POST['org_name'];
        $data['type']=$_POST['type'];
        $data['address']=$_POST['address'];
        $data['city']=$_POST['city'];
        $data['district']=$_POST['district'];
        $data['state']=$_POST['state'];
        $data['country']=$_POST['country'];
        $data['pincode']=$_POST['pincode'];
        $data['email']=$_POST['email'];
        $data['website']=$_POST['website'];
        $data['phone']=$_POST['phone'];
        $data['phone_alt']=$_POST['phone_alt'];
        $data['contact_person']=$_POST['contact_person'];
        $data['contact_person_desg']=$_POST['contact_person_desg'];
        $cat=$_POST['cat'];
        $sports="";
        $coma=",";
        foreach ($cat as $key => $n) {
            $one="1";
            $sports=$sports."".$n."".$coma;
        }

        //remove the last 2 character at end of string
        $sports=substr($sports, 0, -1);
        $ag=$_POST['age'];
        $age="";
        $coma=",";
        foreach ($ag as $key => $n) {
            $one="1";
            $age=$age."".$n."".$coma;
        }

        //remove the last 2 character at end of string
        $age=substr($age, 0, -1);
        $data['age']=$age;
        $data['sports']=$sports;
        $this->load->Model('AdminModel');
        $sheet = $this->AdminModel->update_org($org_id,$data);
        redirect(CTRL."Admin/admin_org_view/$org_id");
    }
    public function add_new_user(){
        $data=array();
        $org_id=$_POST['org_id'];
        $data['org_id']=$_POST['org_id'];
        $data['name']=$_POST['name'];
        $data['designation']=$_POST['designation'];
        $data['email']=$_POST['email'];
        $data['phone']=$_POST['phone'];
        $data['username']=$_POST['username'];
        $data['password']=sha1($_POST['password']);
        $this->load->Model('AdminModel');
        $sheet = $this->AdminModel->add_new_user($data);
        redirect(CTRL."Admin/admin_org_view/$org_id");
    }
    public function add_new_category(){
        $data=array();
        $data['name']=$_POST['name'];
        $this->load->Model('AdminModel');
        $sheet = $this->AdminModel->add_new_category($data);
        redirect(CTRL."Admin/admin_cat");
    }
    public function add_new_param($cat_id){
        $data=array();
        $data['cat_id']=$cat_id;
        $data['name']=$_POST['name'];
        $data['type']=$_POST['type'];
        $data['min']=$_POST['min'];
        $data['max']=$_POST['max'];
        $data['unit']=$_POST['type_parameter'];
        $data['active']=1;
        $priority=$_POST['priority'];
        $filename= $_FILES["how_vid"]["name"];
        $file_ext = pathinfo($filename,PATHINFO_EXTENSION);
        $data['how_to']=$_POST['how_to'];
        $video_name=$data['name'].'-'.$cat_id.'-'.$data['type'].''.random_string('numeric', 3);;


        $config['upload_path']          = 'uploads/';
        $config['allowed_types']        = 'mp4|3gp|avi|mts';
        $config['max_size']             = 100000;
        $config['overwrite'] = FALSE;
        $config['remove_spaces'] = TRUE;
        $config['file_name'] = $video_name;

        $this->load->library('upload', $config);
        //$how_vid=$_POST['how_vid'];
        if ( ! $this->upload->do_upload("how_vid"))
        {
            $error = array('error' => $this->upload->display_errors());
            print_r($error);
        }
        else {
            $video_name=$video_name.'.'.$file_ext;
            $data['video']=$video_name;
            $this->load->Model('AdminModel');
            $sheet = $this->AdminModel->add_new_param($data,$priority);
            redirect(CTRL."Admin/admin_cat_view/$cat_id");
        }

    }
    public function add_new_bmi($cat_id){
        $data=array();
        $data['cat_id']=$cat_id;
        $data['name']='BMI';
        $data['type']='specific';
        $data['min']=$_POST['low'];
        $data['max']=$_POST['high'];
//        $data['age']=$_POST['age'];
//        $data['gender']=$_POST['gender'];
        $data['unit']='Kg/m2';
        $data['active']=1;
        $priority='bmi';
        $med=$_POST['med'];

        $filename= $_FILES["how_vid"]["name"];
        $file_ext = pathinfo($filename,PATHINFO_EXTENSION);
        $data['how_to']=$_POST['how_to'];
        $video_name=$data['name'].'-'.$cat_id.'-'.$data['type'].''.random_string('numeric', 3);;


        $config['upload_path']          = 'uploads/';
        $config['allowed_types']        = 'mp4|3gp|avi|mts';
        $config['max_size']             = 100000;
        $config['overwrite'] = FALSE;
        $config['remove_spaces'] = TRUE;
        $config['file_name'] = $video_name;

        $this->load->library('upload', $config);
        //$how_vid=$_POST['how_vid'];
        if ( ! $this->upload->do_upload("how_vid"))
        {
            $error = array('error' => $this->upload->display_errors());
            print_r($error);
        }
        else {
            $video_name = $video_name . '.' . $file_ext;
            $data['video'] = $video_name;
            $this->load->Model('AdminModel');
            $sheet = $this->AdminModel->add_new_bmi($data, $med);
            redirect(CTRL . "Admin/admin_cat_view/$cat_id");
        }
    }
    public function add_new_param_1($cat_id){
        $data=array();
        $data['cat_id']=$cat_id;
        $data['name']=$_POST['name'];
        $data['type']=$_POST['type'];
        $data['min']=$_POST['min'];
        $data['max']=$_POST['max'];
//        $data['age']=$_POST['age'];
        $data['gender']=$_POST['gender'];
        $data['unit']=$_POST['type_parameter'];
        $data['active']=1;
        $priority=$_POST['priority'];
        $filename= $_FILES["how_vid"]["name"];
        $file_ext = pathinfo($filename,PATHINFO_EXTENSION);
        $data['how_to']=$_POST['how_to'];
        $video_name=$data['name'].'-'.$cat_id.'-'.$data['type'].''.random_string('numeric', 3);;


        $config['upload_path']          = 'uploads/';
        $config['allowed_types']        = 'mp4|3gp|avi|mts';
        $config['max_size']             = 100000;
        $config['overwrite'] = FALSE;
        $config['remove_spaces'] = TRUE;
        $config['file_name'] = $video_name;

        $this->load->library('upload', $config);
        //$how_vid=$_POST['how_vid'];
        if ( ! $this->upload->do_upload("how_vid"))
        {
//            $error = array('error' => $this->upload->display_errors());
//            print_r($error);
        }
        else {
            $video_name=$video_name.'.'.$file_ext;
            $data['video']=$video_name;
            $this->load->Model('AdminModel');
            $sheet = $this->AdminModel->add_new_param_1($data, $priority);
            //$param_id = $sheet['param_id'];
            redirect(CTRL . "Admin/admin_cat_view/$cat_id");
        }
    }

    public function add_new_group($cat_id){
        $data=array();
        $data['name']=$_POST['name'];
        $data['cat_id']=$cat_id;
        $data['param']=$_POST['param'];
        $data['group_reliability']=$_POST['group_reliability'];
        $this->load->Model('AdminModel');
        $sheet = $this->AdminModel->add_new_group($data);
        /*print_r($sheet);
        exit(1);*/
        if($sheet['status']==true){
            redirect(CTRL."Admin/admin_cat_view/$cat_id");
        }
        else{
            if($sheet['message']==true){
                redirect(CTRL."Admin/admin_cat_view/$cat_id#para");
            }
            else{
                redirect(CTRL."Admin/admin_cat_view/$cat_id#error");
            }
        }

    }
    public function admin_grading()
    {
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->view_grading();
        $this->load->view('admin/admin_grading',$sheet);
    }
    public function submit_grading(){
        $data=array();
        $data['min']=$_POST['min'];
        $data['max']=$_POST['max'];
        $data['criteria']=$_POST['criteria'];
        $this->load->Model('AdminModel');
        $sheet = $this->AdminModel->submit_grading($data);
        redirect(CTRL."Admin/admin_grading");
    }
    public function admin_profile()
    {
        $admin_id=$_SESSION['admin_id'];
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->admin_profile($admin_id);
        $this->load->view('admin/admin_profile',$sheet);
    }
    public function admin_org_view($org_id)
    {
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->admin_org_view($org_id);
        $this->load->view('admin/admin_org_view',$sheet);
    }
    public function admin_user_view($user_id)
    {
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->admin_user_view($user_id);
        $this->load->view('admin/admin_user_view',$sheet);
    }
    public function admin_cat_view($cat_id)
    {
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->admin_cat_view($cat_id);
        $this->load->helper('form');
        $this->load->view("admin/admin_cat_view",$sheet);
    }
    public function admin_sporting(){
        $cat_id=5;
        redirect(CTRL."Admin/admin_cat_view/$cat_id");
//        $this->load->Model('AdminModel');
//        $sheet=$this->AdminModel->admin_cat_view($cat_id);
//        $this->load->view("admin/admin_cat_view/$cat_id",$sheet);
    }
    public function view_group($group_id,$cat_id){
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->view_group($group_id,$cat_id);
//        print_r($sheet);
//        exit(1);
        $this->load->view("admin/admin_group_view",$sheet);
    }
    public function total_var($cat_id){
        $data=array();
        $data['total_var']=$_POST['total_var'];
        $data['cat_id']=$cat_id;
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->total_var($data);
        redirect(CTRL."Admin/admin_cat_view/$cat_id");
    }
    public function total_group_var($group_id,$cat_id){
        $data=array();
        $data['cat_id']=$cat_id;
        $data['group_id']=$group_id;
        $data['total_var']=$_POST['total_var'];
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->total_group_var($data);
        if($sheet['status']==true){
            redirect(CTRL."Admin/view_group/$group_id/$cat_id");
        }else{
            redirect(CTRL."Admin/view_group/$group_id/$cat_id#error");
        }

    }
    public function set_param_var($param_id,$group_id,$cat_id){
        $data=array();
        $data['loading']=$_POST['loading'];
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->set_param_var($data,$param_id,$group_id,$cat_id);
        redirect(CTRL."Admin/view_group/$group_id/$cat_id");
    }
    public function update_profile(){
        $data=array();
        $data['name']=$_POST['name'];
        $data['username']=$_POST['username'];
        $data['admin_id']=$_SESSION['admin_id'];
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->update_profile($data);
        redirect(CTRL."Admin/admin_profile");
    }
    public function update_user($user_id){
        $data=array();
        $data['user_id']=$user_id;
        $data['name']=$_POST['name'];
        $data['username']=$_POST['username'];
        $data['email']=$_POST['email'];
        $data['phone']=$_POST['phone'];
        $data['designation']=$_POST['designation'];
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->update_user($data);
        redirect(CTRL."Admin/admin_user_view/$user_id");
    }
    public function update_cat($cat_id){
        $name=$_POST['name'];
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->update_cat($cat_id,$name);
        redirect(CTRL."Admin/admin_cat");
    }
    public function update_param($param_id,$cat_id){
        $data=array();
        $data['cat_id']=$cat_id;
        $data['name']=$_POST['name'];
        $data['type']=$_POST['type'];
        $type=$data['type'];
        $data['min']=$_POST['min'];
        $data['max']=$_POST['max'];
        $data['how_to']=$_POST['how_to'];
        $priority=$_POST['priority'];
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->update_param($data,$param_id,$priority);
        redirect(CTRL."admin/admin_cat_view/$cat_id/");
//        redirect(CTRL."admin/select_show_new/$cat_id/$type/$age/$gender");
    }
    public function update_param_bmi($param_id,$cat_id){
        $data=array();
        $data['cat_id']=$cat_id;
        $data['name']=$_POST['name'];
        $data['type']=$_POST['type'];
        $type=$data['type'];
        $data['min']=$_POST['min'];
        $data['max']=$_POST['max'];
        $data['how_to']=$_POST['how_to'];
        $priority=$_POST['priority'];
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->update_param($data,$param_id,$priority);
        redirect(CTRL."admin/admin_cat_view/$cat_id/");
//        redirect(CTRL."admin/select_show_new/$cat_id/$type/$age/$gender");
    }
    public function update_param_select($param_id,$cat_id,$type,$age,$gender){
        $data=array();
        $data['cat_id']=$cat_id;
        $data['name']=$_POST['name'];
        $data['type']=$_POST['type'];
        $data['min']=$_POST['min'];
        $data['max']=$_POST['max'];
        $data['how_to']=$_POST['how_to'];
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->update_param($data,$param_id);
        redirect(CTRL."admin/select_show_new/$cat_id/$type/$age/$gender");
    }
    public function delete_cat($cat_id){
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->delete_cat($cat_id);
        redirect(CTRL."Admin/admin_cat");
    }
    public function change_password(){
        $password=sha1($_POST['password']);
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->change_password($password);
        redirect(CTRL."Admin/admin_profile");
    }
    public function view_norms($param_id,$cat_id){
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->view_norms($param_id,$cat_id);
        $this->load->view('admin/admin_view_norms',$sheet);
    }
    public function org_active($org_id){
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->org_active($org_id);
        redirect(CTRL."Admin/admin_org_view/$org_id");
    }
    public function activate_param($param_id,$cat_id){
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->param_active($param_id);
        redirect(CTRL."Admin/admin_cat_view/$cat_id");
    }
    public function select_show($cat_id){
        $data=array();
        $type=$_POST['type'];
        $age=$_POST['age'];
        $gender=$_POST['gender'];
        redirect(CTRL."admin/select_show_new/$cat_id/$type/$age/$gender");
    }
    public function select_show_new($cat_id,$type,$age,$gender){
        $data=array();
        $data['cat_id']=$cat_id;
        $data['type']=$type;
        $data['age']=$age;
        $data['gender']=$gender;
        $this->load->Model('AdminModel');
        $sheet = $this->AdminModel->select_show($data);
        $this->load->view('admin/admin_select_show',$sheet);
    }
    public function activate_param_select($param_id,$cat_id,$type,$age,$gender){
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->param_active($param_id);
        // redirect(CTRL."Admin/select_show/$cat_id");
        redirect(CTRL."admin/select_show_new/$cat_id/$type/$age/$gender");
    }
    public function deactivate_param($param_id,$cat_id){
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->param_inactive($param_id);
        redirect(CTRL."Admin/admin_cat_view/$cat_id");
    }
    public function deactivate_param_select($param_id,$cat_id,$type,$age,$gender){
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->param_inactive($param_id);
        redirect(CTRL."admin/select_show_new/$cat_id/$type/$age/$gender");
    }
    public function org_inactive($org_id){
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->org_inactive($org_id);
        redirect(CTRL."Admin/admin_org_view/$org_id");
    }
    public function org_delete($org_id){
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->org_delete($org_id);
        redirect(CTRL."Admin/admin_org");
    }
    public function user_delete($user_id){
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->user_delete($user_id);
        redirect(CTRL."Admin/admin_org");
    }
    public function user_active($user_id){
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->user_active($user_id);
        redirect(CTRL."Admin/admin_user_view/$user_id");
    }
    public function user_inactive($user_id){
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->user_inactive($user_id);
        redirect(CTRL."Admin/admin_user_view/$user_id");
    }
    public function delete_param($param_id,$cat_id){
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->delete_param($param_id);
        if($sheet['status']==true)
            redirect(CTRL."Admin/admin_cat_view/$cat_id");
        else
            redirect(CTRL."Admin/admin_cat_view/$cat_id#assessment");
    }
    public function delete_param_select($param_id,$cat_id,$type,$age,$gender){
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->delete_param($param_id);
        if($sheet['status']==true)
            redirect(CTRL."admin/select_show_new/$cat_id/$type/$age/$gender");
        else
            redirect(CTRL."admin/select_show_new/$cat_id/$type/$age/$gender#assessment");
    }
    public function admin_logout(){
        $this->session->unset_userdata('admin_id');
        $this->session->sess_destroy();
        redirect(CTRL."Admin/login_view");
    }
    public function delete_group($group_id,$cat_id){
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->delete_group($group_id,$cat_id);
        redirect(CTRL."Admin/admin_cat_view/$cat_id");
    }
	
	 public function search(){
      
        $this->load->view('admin/search');
    }
	public function Next_search()
	{
		$search_by=$_POST['search_by'];
		if($search_by=="Searching Individual")
		{
			 $this->load->view('admin/Searching_Individual');
		}
		else if($search_by=="Record of students")
		{
			 $this->load->Model('AdminModel');
             $sheet=$this->AdminModel->admin_org();
			 $this->load->view('admin/stu_record',$sheet);
		}
		else if($search_by=="Record of students Game Wise")
		{
			 $this->load->Model('AdminModel');
             $sheet=$this->AdminModel->all_games();
			 $this->load->view('admin/report_game_wise',$sheet);
		}
        else if($search_by=="Record of students Year Wise")
        {
            $this->load->Model('AdminModel');
            $sheet=$this->AdminModel->all_years();
            $this->load->view('admin/report_year_wise',$sheet);
        }
        else if($search_by=="Record of students Position Wise")
        {
            $this->load->Model('AdminModel');
            $sheet=$this->AdminModel->all_position();
            $this->load->view('admin/report_position_wise',$sheet);
        }
        else if($search_by=="Record of students Score Wise")
        {
            $this->load->view('admin/report_score_wise');
        }


		//echo $search_by;
	}
	public function student_report()
	{
		$data['search_Name']=$_POST['search_Name'];
        $data['search_Id']=$_POST['search_Id'];
		$this->load->Model('AdminModel');
        $sheet=$this->AdminModel->student_report($data);
		$this->load->view('admin/student_report',$sheet);
		
	}
	public function record_student()
	{
		
		$data['searchby']=$_POST['searchby'];
		$data['org_id']=$_POST['search_by'];
		$data['search_state_by']=$_POST['search_state_by'];
		$data['search_district_by']=$_POST['search_district_by'];
		//print_r($data);
		if($data['searchby']=="Organisation_wise")
		{
		
		$this->load->Model('AdminModel');
        $sheet=$this->AdminModel->record_student_by_org($data);
		$this->load->view('admin/Record_students',$sheet);
		
		}
		if($data['searchby']=="State_wise")
		{
		$this->load->Model('AdminModel');
        $sheet=$this->AdminModel->record_student_statewise($data);
		$this->load->view('admin/state_wise_students',$sheet);
		}
		
		if($data['searchby']=="District_wise")
		{
		$this->load->Model('AdminModel');
        $sheet=$this->AdminModel->record_student_districtwise($data);
		$sheet['district']=$data['search_district_by'];
		$this->load->view('admin/district_wise_students',$sheet);
		}

	}
	
	public function get_game_wise_report()
	{
		$data['search_by_game']=$_POST['search_by_game'];
       
		$this->load->Model('AdminModel');
        $sheet=$this->AdminModel->get_game_wise_report($data);
		$sheet['game_name']=$sheet['data1'][0]['name'];
		//exit();
		$this->load->view('admin/game_wise_report',$sheet);
		
	}
    public function get_year_wise_report()
    {
        $data['search_by_year']=$_POST['search_by_year'];

        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->get_year_wise_report($data);
        $sheet['game_name']=$data['search_by_year'];
        //exit();
        $this->load->view('admin/year_wise_report',$sheet);

    }
    public function get_position_wise_report()
    {
        $data['search_by_position']=$_POST['search_by_position'];

        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->get_position_wise_report($data);
        $sheet['game_name']=$data['search_by_position'];
        //exit();
        $this->load->view('admin/position_wise_report',$sheet);

    }
    public function get_score_wise_report()
    {
        $data['low']=$_POST['low'];
        $data['high']=$_POST['high'];
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->get_score_wise_report($data);
        $sheet['low']=$data['low'];
        $sheet['high']=$data['high'];
        //exit();
        $this->load->view('admin/score_wise_report',$sheet);

    }
	
}
?>