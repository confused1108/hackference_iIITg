<?php
/**
 * Created by PhpStorm.
 * User: Hitesh
 * Date: 16-Dec-18
 * Time: 2:55 AM
 * Coded by Hitesh with love
 * Find me at confused1108.github.io
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class General extends CI_Controller {

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
    }
    public function new_fir()
    {
        $this->load->Model('GeneralModel');
        $sheet = $this->GeneralModel->new_fir();
        $this->load->view('general/new_fir',$sheet);
    }
    public function file_fir(){
        $data=array();
        $data['aadhar']=$_POST['aadhar'];
        $data['issue']=$_POST['issue'];
        $data['station']=$_POST['station'];
        $this->load->Model('GeneralModel');
        $sheet = $this->GeneralModel->file_fir($data);
        redirect(CTRL."General/new_fir");

    }
    public function user_fir()
    {
        $this->load->view('user/user_fir');
    }

    public function user_old()
    {
        {
            $this->load->Model('UserModel');
            $sheet = $this->UserModel->user_old();
            $this->load->view('user/user_old',$sheet);
        }
    }
    public function user_old_view()
    {
        $this->load->view('user/user_old_view');
    }
    public function user_view_old($candidate_id){
        $this->load->Model('UserModel');
        $sheet = $this->UserModel->user_view_old($candidate_id);
        $this->load->view('user/user_view_old',$sheet);
    }
    public function user_details()
    {
        $user_id=$_SESSION['user_id'];
        $this->load->Model('UserModel');
        $sheet=$this->UserModel->User_profile($user_id);
        $this->load->view('user/user_details',$sheet);
    }
    public function update_profile(){
        $data=array();
        $data['name']=$_POST['name'];
        $data['address']=$_POST['address'];
        $data['email']=$_POST['email'];
        $data['phone']=$_POST['phone'];
        $data['user_id']=$_SESSION['user_id'];
        $this->load->Model('UserModel');
        $sheet=$this->UserModel->update_profile($data);
        redirect(CTRL."user/user_details");
    }
    public function change_password(){
        $password=sha1($_POST['password']);
        $this->load->Model('UserModel');
        $sheet=$this->UserModel->change_password($password);
        redirect(CTRL."user/user_details");
    }
    public function log_out(){
        $this->session->unset_userdata('user_id');
        $this->session->unset_userdata('org_id');
        $this->session->sess_destroy();
        redirect(CTRL."User/login_view");
    }
}
?>