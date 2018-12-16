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
            redirect(CTRL."Admin/admin_org_view");
        }
        else{
            redirect(CTRL."Admin/login_view#error");
        }
    }
    public function add_new_user(){
        $data=array();
        $data['name']=$_POST['name'];
        $data['address']=$_POST['address'];
        $data['email']=$_POST['email'];
        $data['phone']=$_POST['phone'];
        $data['password']=sha1($_POST['password']);
        $this->load->Model('AdminModel');
        $this->AdminModel->add_new_user($data);
        redirect(CTRL."Admin/admin_org_view");
    }
    public function admin_profile()
    {
        $admin_id=$_SESSION['admin_id'];
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->admin_profile($admin_id);
        $this->load->view('admin/admin_profile',$sheet);
    }
    public function admin_org_view()
    {
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->admin_org_view();
        $this->load->view('admin/admin_org_view',$sheet);
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
    public function change_password(){
        $password=sha1($_POST['password']);
        $this->load->Model('AdminModel');
        $sheet=$this->AdminModel->change_password($password);
        redirect(CTRL."Admin/admin_profile");
    }
    public function admin_logout(){
        $this->session->unset_userdata('admin_id');
        $this->session->sess_destroy();
        redirect(CTRL."Admin/login_view");
    }
}
?>