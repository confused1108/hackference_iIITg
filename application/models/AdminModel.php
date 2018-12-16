<?php
/**
 * Created by PhpStorm.
 * User: Hitesh
 * Date: 24-Sep-18
 * Time: 1:09 AM
 */

Class AdminModel extends CI_Model
{
    Public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function admin_login_check($data)
    { 
        $username = $data['username']; 
        $password = $data['password'];
        $result = array();
        $sql = "SELECT admin_id from admin where username='$username' AND password='$password'";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $data=$query->result_array();
            $result['admin_id']=$data[0]['admin_id'];
            $result['status'] = true;
        }
        else{
            $result['status']=false;
        }
        return $result;
    }
    public function add_new_org($data){
        $query=$this->db->insert('organization',$data);
        if($query){
            $result['status'] = true;
        }
        else{
            $result['status'] = false;
        }
    }
    public function add_new_user($data){
        $query=$this->db->insert('user',$data);
        if($query){
            $result['status'] = true;
        }
        else{
            $result['status'] = false;
        }
    }
    public function add_new_category($data){
        $query=$this->db->insert('categories',$data);
        if($query){
            $result['status'] = true;
        }
        else{
            $result['status'] = false;
        }
    }
    public function add_new_bmi($data,$medd){
        $gender=array("male","female");
        $cat_id=$data['cat_id'];
        $name=$data['name'];
        $type=$data['type'];
        $minn=$data['min'];
        $maxx=$data['max'];
        $unit=$data['unit'];
        $video=$data['video'];
        $active=$data['active'];
        $how_to=$data['how_to'];
        $sql="SELECT MAX(para_group_id) as para FROM parameter";
        $query=$this->db->query($sql);
        $data3=$query->result_array();
        $para_group_id=$data3[0]['para']+1;
        foreach ($minn as $key => $n) {
            $sql = "INSERT INTO parameter (cat_id,name,type,min,max,how_to,video,age,gender,unit,group_set,active,para_group_id) VALUES ('" . $cat_id . "','" . $name . "','" . $type . "','" . $n . "','" . $maxx[$key] . "','" . $how_to . "','" . $video . "','0','" . $gender[$key] . "','" . $unit . "','0','" . $active . "','" . $para_group_id . "')";
            $this->db->query($sql);
            $param_id=$this->db->insert_id();
            $result['status'] = true;
            $min=$n;
            $max=$maxx[$key];
            $med=$medd[$key];

            $diff=$med-$min;
            $per=$diff/20;

                $min=$min-$per;
                $upper=$min;
                $priority = "bmi";
                // $lower=0;
                // $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','0','" . $priority . "')";
                // $this->db->query($sql);
                $scale=array("1","4","6","8","11","14","17","21","25","29","33","38","43","48","54","60","66","72","78","85","92","100");
                for($i=0;$i<21;$i++){
                    $lower=$min+($i*$per);
                    $upper=$lower+$per;
                    $score=$scale[$i];
                    $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
                    $this->db->query($sql);
                }
                /*$lower=$upper;
                $upper=1000000;
                $score=$scale[21];
                $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
                $this->db->query($sql);*/


            $diff=$max-$med;
            $per=$diff/20;
            $priority = "bmi";
                $scale=array("100","92","85","78","72","66","60","54","48","43","38","33","29","25","21","17","14","11","8","6","4","1");
                for($i=0;$i<21;$i++){
                    $lower=$med+($i*$per);
                    $upper=$lower+$per;
                    $score=$scale[$i];
                    $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
                    $this->db->query($sql);
                }
                $lower=$upper;
                $upper=1000000;
                $score=1;
                $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
                $this->db->query($sql);

        }
        return $result;
    }
    public function add_new_param($data,$priority){
        $result['err']=false;
        $gender=array("male","female","male","female","male","female","male","female");
        $age=array("1","1","2","2","3","3","4","4");
        $cat_id=$data['cat_id'];
        $name=$data['name'];
        $type=$data['type'];
        $minn=$data['min'];
        $maxx=$data['max'];
        $unit=$data['unit'];
        $active=$data['active'];
        $how_to=$data['how_to'];
        $video=$data['video'];
        $sql="SELECT param_id FROM parameter WHERE name='$name' AND type='$type' AND cat_id='$cat_id'";
        $query=$this->db->query($sql);
        $num=$query->num_rows();
        if($num>0){
            $result['err']=true;
            return $result;
        }
        $sql="SELECT MAX(para_group_id) as para FROM parameter";
        $query=$this->db->query($sql);
        $data3=$query->result_array();
        $para_group_id=$data3[0]['para']+1;
        foreach ($minn as $key => $n) {
            $sql = "INSERT INTO parameter (cat_id,name,type,min,max,how_to,video,age,gender,unit,group_set,active,para_group_id) VALUES ('" . $cat_id . "','" . $name . "','" . $type . "','" . $n . "','" . $maxx[$key] . "','" . $how_to . "','" . $video . "','" . $age[$key] . "','" . $gender[$key] . "','" . $unit . "','0','" . $active . "','" . $para_group_id . "')";
            $this->db->query($sql);
            $param_id=$this->db->insert_id();
            $result['status'] = true;
            $min=$n;
            $max=$maxx[$key];
            $diff=$max-$min;
            $per=$diff/20;
            if($priority == "value_2"){
                $min=$min-$per;
                $upper=$min;
                // $lower=0;
                // $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','0','" . $priority . "')";
                // $this->db->query($sql);
                $scale=array("1","4","6","8","11","14","17","21","25","29","33","38","43","48","54","60","66","72","78","85","92","100");
                for($i=0;$i<21;$i++){
                    $lower=$min+($i*$per);
                    $upper=$lower+$per;
                    $score=$scale[$i];
                    $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
                    $this->db->query($sql);
                }
                $lower=$upper;
                $upper=1000000;
                $score=$scale[21];
                $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
                $this->db->query($sql);
            }else{
                // $lower=0;
                // $upper=$min;
                // $score=100;
                // $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
                // $this->db->query($sql);
                $scale=array("100","92","85","78","72","66","60","54","48","43","38","33","29","25","21","17","14","11","8","6","4","1");
                for($i=0;$i<21;$i++){
                    $lower=$min+($i*$per);
                    $upper=$lower+$per;
                    $score=$scale[$i];
                    $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
                    $this->db->query($sql);
                }
                $lower=$upper;
                $upper=1000000;
                $score=1;
                $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
                $this->db->query($sql);
            }
        }
        return $result;
    }
    public function add_new_param_1($data,$priority){
        $gender=array("male","female");
        $age=0;
        $cat_id=$data['cat_id'];
        $name=$data['name'];
        $type=$data['type'];
        $minn=$data['min'];
        $maxx=$data['max'];
        $unit=$data['unit'];
        $active=$data['active'];
        $how_to=$data['how_to'];
        $video=$data['video'];
        $result['err']=false;
        $sql="SELECT param_id FROM parameter WHERE name='$name' AND type='$type' AND cat_id='$cat_id'";
        $query=$this->db->query($sql);
        $num=$query->num_rows();
        if($num>0){
            $result['err']=true;
            return $result;
        }
        //$query=$this->db->insert('parameter',$data);
        //$param_id=$this->db->insert_id();
        //$result['param_id']=$param_id;
        foreach ($minn as $key => $n) {
            $sql = "INSERT INTO parameter (cat_id,name,type,min,max,how_to,video,age,gender,unit,group_set,active) VALUES ('" . $cat_id . "','" . $name . "','" . $type . "','" . $n . "','" . $maxx[$key] . "','" . $how_to . "','" . $video . "','" . $age . "','" . $gender[$key] . "','" . $unit . "','0','" . $active . "')";
            $this->db->query($sql);
            $param_id=$this->db->insert_id();
            $result['status'] = true;
            $min=$n;
            $max=$maxx[$key];
            $diff=$max-$min;
            $per=$diff/20;
            if($priority == "value_2"){
                $min=$min-$per;
                $upper=$min;
                // $lower=0;
                // $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','0','" . $priority . "')";
                // $this->db->query($sql);
                $scale=array("1","4","6","8","11","14","17","21","25","29","33","38","43","48","54","60","66","72","78","85","92","100");
                for($i=0;$i<21;$i++){
                    $lower=$min+($i*$per);
                    $upper=$lower+$per;
                    $score=$scale[$i];
                    $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
                    $this->db->query($sql);
                }
                $lower=$upper;
                $upper=1000000;
                $score=$scale[21];
                $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
                $this->db->query($sql);
            }else{
                // $lower=0;
                // $upper=$min;
                // $score=100;
                // $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
                // $this->db->query($sql);
                $scale=array("100","92","85","78","72","66","60","54","48","43","38","33","29","25","21","17","14","11","8","6","4","1");
                for($i=0;$i<21;$i++){
                    $lower=$min+($i*$per);
                    $upper=$lower+$per;
                    $score=$scale[$i];
                    $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
                    $this->db->query($sql);
                }
                $lower=$upper;
                $upper=1000000;
                $score=1;
                $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
                $this->db->query($sql);
            }
        }
        return $result;
    }

    public function select_show($data){
        $cat_id=    $data['cat_id'];
        $type=      $data['type'];
        $age=       $data['age'];
        $gender=    $data['gender'];
        $sql=       "SELECT * from parameter WHERE cat_id='$cat_id' AND type='$type' AND age='$age' AND gender='$gender'";
        $query=$this->db->query($sql);
        $result['select']=$query->result_array();
        return $result;
    }
//    public function add_new_group($data){
//        $name=$data['name'];
//        $cat_id=$data['cat_id'];
//        $param=$data['param'];
//        $group_reliability=$data['group_reliability'];
//        $sql="SELECT SUM(group_reliability) as sum FROM `dist_group` WHERE cat_id=$cat_id";
//        $query=$this->db->query($sql);
//        $data=$query->result_array();
//        $sum=$data[0]['sum'];
//        $result['message']=false;
//        foreach ($param as $id){
//            $sql="SELECT * FROM distribution WHERE param_id=$id";
//            $query=$this->db->query($sql);
//            $num=$query->num_rows();
//            if($num>0){
//                $result['status']=false;
//                $result['message']=true;
//            }
//        }
//        if($result['message']==false){
//            if(($sum+$group_reliability)>100){
//                $result['status']=false;
//            }
//            else{
//                $parameters="";
//                $coma=", ";
//                foreach ($param as $key => $n){
//                    $sql="SELECT group_set FROM parameter WHERE param_id=$n";
//                    $query=$this->db->query($sql);
//                    $data=$query->result_array();
//                    $group_set=$data[0]['group_set'];
//                    $one="1";
//                    if($group_set==$one){
//                        $result['status']=false;
//                        return $result;
//                    }
//                }
//                $parameters="";
//                $coma=", ";
//                foreach ($param as $key => $n) {
//                    $one="1";
//                    $sql="UPDATE parameter SET `group_set`=$one WHERE param_id=$n";
//                    $this->db->query($sql);
//                    $result['status']=true;
//                    $parameters=$parameters."".$n."".$coma;
//                }
//                $parameters=substr($parameters, 0, -2);
//                $sql = "INSERT INTO dist_group (name,cat_id,parameters,group_reliability) VALUES ('" . $name . "','" . $cat_id . "','" . $parameters . "','" . $group_reliability . "')";
//                $this->db->query($sql);
//                $group_id=$this->db->insert_id();
//                $zero=0;
//                foreach($param as $param_id){
//                    $sql="Insert into distribution (group_id,param_id,cat_id,loading) VALUES ('".$group_id."','".$param_id."','".$cat_id."','".$zero."')";
//                    $this->db->query($sql);
//                }
//                $sql="SELECT total_var FROM categories WHERE cat_id=$cat_id";
//                $query=$this->db->query($sql);
//                $data=$query->result_array();
//                $total_var=$data[0]['total_var'];
//                $total_var=$total_var+$group_reliability;
//                $sql="UPDATE categories SET `total_var`='$total_var' WHERE cat_id=$cat_id";
//                $this->db->query($sql);
//                $result['status']=true;
//            }
//        }
//
//        return $result;
//    }

    public function add_new_group($data){
        $name=$data['name'];
        $cat_id=$data['cat_id'];
        $param=$data['param'];
        $group_reliability=$data['group_reliability'];
        $sql="SELECT total_var as sum FROM `categories` WHERE cat_id=$cat_id";
        $query=$this->db->query($sql);
        $data=$query->result_array();
        $sum=$data[0]['sum'];

        $result['message']=false;
        $i=0;
        $parameter_id=array();
        foreach ($param as $key => $n) {
            $sql = "SELECT name,param_id FROM parameter WHERE para_group_id=$n";
            $query = $this->db->query($sql);
            $data = $query->result_array();
            $nam=$data[0]['name'];
            if($nam=='BMI'){
                $parameter_id[$i] = $data[0]['param_id'];
                $para_group[$i]=$n;
                $i++;
                $parameter_id[$i] = $data[1]['param_id'];
                $para_group[$i]=$n;
                $i++;
            }
            else{
                $parameter_id[$i] = $data[0]['param_id'];
                $para_group[$i]=$n;
                $i++;
                $parameter_id[$i] = $data[1]['param_id'];
                $para_group[$i]=$n;
                $i++;
                $parameter_id[$i] = $data[2]['param_id'];
                $para_group[$i]=$n;
                $i++;
                $parameter_id[$i] = $data[3]['param_id'];
                $para_group[$i]=$n;
                $i++;
                $parameter_id[$i] = $data[4]['param_id'];
                $para_group[$i]=$n;
                $i++;
                $parameter_id[$i] = $data[5]['param_id'];
                $para_group[$i]=$n;
                $i++;
                $parameter_id[$i] = $data[6]['param_id'];
                $para_group[$i]=$n;
                $i++;
                $parameter_id[$i] = $data[7]['param_id'];
                $para_group[$i]=$n;
                $i++;
            }

        }
        foreach ($parameter_id as $id){
            $sql="SELECT * FROM distribution WHERE param_id=$id";
            $query=$this->db->query($sql);
            $num=$query->num_rows();
            if($num>0){
                $result['status']=false;
                $result['message']=true;
                return $result;
            }
        }
        if($result['message']==false){
            if(($sum+$group_reliability)>100){
                $result['status']=false;
            }

            else{
                foreach ($param as $key => $n){
                    $sql="SELECT group_set FROM parameter WHERE para_group_id=$n";
                    $query=$this->db->query($sql);
                    $data=$query->result_array();
                    $group_set=$data[0]['group_set'];
                    $one="1";
                    if($group_set==$one){
                        $result['status']=false;
                        return $result;
                    }
                }
                $parameters="";
                $coma=", ";
                foreach ($param as $key => $n) {
                    $one="1";
                    $sql="UPDATE parameter SET `group_set`=$one WHERE para_group_id=$n";
                    $this->db->query($sql);
                    $result['status']=true;
                    $parameters=$parameters."".$n."".$coma;
                }
                $parameters=substr($parameters, 0, -2);
                $sql = "INSERT INTO dist_group (name,cat_id,parameters,group_reliability) VALUES ('" . $name . "','" . $cat_id . "','" . $parameters . "','" . $group_reliability . "')";
                $this->db->query($sql);
                $group_id=$this->db->insert_id();
                $zero=0;
                foreach($parameter_id as $key => $param_id){
                    $sql="Insert into distribution (group_id,param_id,cat_id,loading,para_group_id) VALUES ('".$group_id."','".$param_id."','".$cat_id."','".$zero."','".$para_group[$key]."')";
                    $this->db->query($sql);
                }
                $sql="SELECT total_var FROM categories WHERE cat_id=$cat_id";
                $query=$this->db->query($sql);
                $data=$query->result_array();
                $total_var=$data[0]['total_var'];
                $total_var=$total_var+$group_reliability;
                $sql="UPDATE categories SET `total_var`='$total_var' WHERE cat_id=$cat_id";
                $this->db->query($sql);
                $result['status']=true;
            }
        }

        return $result;
    }

    public function admin_org(){
        $sql="SELECT * FROM organization ORDER BY org_id DESC";
        $query=$this->db->query($sql);
        $result=array();
        $result['orgdata']=$query->result_array();
        $sql="SELECT * FROM categories WHERE cat_id!='5'";
        $query=$this->db->query($sql);
        $result['catdata']=$query->result_array();
        return $result;
    }
    public function admin_cat(){
        $sql="SELECT * FROM categories WHERE cat_id!='5' ORDER BY cat_id DESC";
        $query=$this->db->query($sql);
        $result=array();
        $result['catdata']=$query->result_array();
        return $result;
    }
    public function admin_org_view(){
        $sql="SELECT * FROM user";
        $query=$this->db->query($sql);
        $result['userdata']=$query->result_array();
        return $result;
    }
    public function admin_user_view($user_id){
        $result=array();
        $sql="SELECT * FROM user WHERE user_id='$user_id'";
        $query=$this->db->query($sql);
        $result['userdata']=$query->result_array();
        return $result;
    }
    public function admin_cat_view($cat_id){
        $result=array();
        $sql="SELECT * FROM categories WHERE cat_id='$cat_id'";
        $query=$this->db->query($sql);
        $result['catdata']=$query->result_array();
        $sql="SELECT *,(select priority from norms where norms.param_id=parameter.param_id LIMIT 1) as priority FROM parameter WHERE cat_id='$cat_id'";
        $query=$this->db->query($sql);
        $result['paramdata']=$query->result_array();
        $sql="SELECT DISTINCT name, para_group_id FROM parameter WHERE cat_id='$cat_id' AND type='specific'";
        $query=$this->db->query($sql);
        $result['paramdata1']=$query->result_array();
        $sql="SELECT total_var FROM categories WHERE cat_id='$cat_id'";
        $query=$this->db->query($sql);
        $result['vardata']=$query->result_array();
        $sql="SELECT * FROM dist_group WHERE cat_id='$cat_id'";
        $query=$this->db->query($sql);
        $result['groupdata']=$query->result_array();
        $sql="SELECT parameter.name as name,distribution.loading as loading,distribution.group_id as group_id FROM dist_group,distribution,parameter WHERE distribution.cat_id=$cat_id AND distribution.param_id=parameter.param_id AND distribution.group_id=dist_group.group_id GROUP BY parameter.para_group_id";
        $query=$this->db->query($sql);
        $result['coldata']=$query->result_array();
        return $result;
    }
    public function submit_grading($data){

        $min        =   $data['min'];
        $max        =   $data['max'];
        $criteria   =   $data['criteria'];
        if($min[0]!= 0 || $max[sizeof($max)-1]!= 100){

            $result['message'] = 'Error : Please Donot input Overlapsing Data';
            return $result;

        }
        else {
            for ($i=0; $i < sizeof($max) -1; $i++) {
                # code...
                if( $max[$i] != ($min[$i+1]-1))

                {
                    $result['message'] = 'Error : Please Donot input Overlapsing Data';
                    return $result;
                }
            }
        }
        $sql="DELETE FROM grading WHERE 1";
        $this->db->query($sql);
        foreach ($criteria as $key => $n) {
            $sql="Insert into grading (min,max,criteria) VALUES ('".$min[$key]."','".$max[$key]."','".$n."')";
            $this->db->query($sql);
        }
    }
    public function view_grading(){
        $result=array();
        $sql="SELECT * FROM grading";
        $query=$this->db->query($sql);
        $result['gradedata']=$query->result_array();
        return $result;
    }
    public function total_var($data){
        $cat_id=$data['cat_id'];
        $total_var=$data['total_var'];
        $sql="UPDATE categories SET `total_var`=$total_var WHERE cat_id=$cat_id";
        $query=$this->db->query($sql);
        return 1;
    }
    public function total_group_var($data){
        $cat_id=$data['cat_id'];
        $group_id=$data['group_id'];
        $total_var=$data['total_var'];
        $sql="SELECT SUM(group_reliability) as sum FROM `dist_group` WHERE cat_id=$cat_id";
        $query=$this->db->query($sql);
        $data=$query->result_array();
        $sum=$data[0]['sum'];
        $sql="SELECT group_reliability as re FROM `dist_group` WHERE group_id=$group_id";
        $query=$this->db->query($sql);
        $data=$query->result_array();
        $re=$data[0]['re'];
        if(($sum+$total_var-$re)>100){
            $result['status']=false;
        }
        else{
            $sql="UPDATE dist_group SET `group_reliability`=$total_var WHERE group_id=$group_id";
            $query=$this->db->query($sql);
            $new=$sum+$total_var-$re;
            $sql="UPDATE categories SET `total_var`=$new WHERE cat_id=$cat_id";
            $query=$this->db->query($sql);
            $result['status']=true;
        }
        return $result;
    }
    public function view_group($group_id,$cat_id){
        $result=array();
        $sql="SELECT * FROM dist_group WHERE group_id=$group_id";
        $query=$this->db->query($sql);
        $result['groupdata']=$query->result_array();
        $sql="SELECT * FROM parameter WHERE cat_id=$cat_id GROUP BY para_group_id";
        $query=$this->db->query($sql);
        $result['paramdata']=$query->result_array();
        $sql="SELECT * FROM categories WHERE cat_id=$cat_id";
        $query=$this->db->query($sql);
        $result['catdata']=$query->result_array();
        $sql="SELECT * FROM distribution WHERE group_id=$group_id GROUP BY para_group_id";
        $query=$this->db->query($sql);
        $result['distdata']=$query->result_array();
        return $result;
    }
    public function set_param_var($data,$param_id,$group_id,$cat_id){
        $loading=$data['loading'];
        $sql="SELECT * FROM distribution WHERE para_group_id=$param_id";
        $query=$this->db->query($sql);
        if($query->num_rows()>0){
            $sql="UPDATE distribution SET `loading`=$loading WHERE para_group_id=$param_id";
            $this->db->query($sql);
        }
        else{
            $sql="Insert into distribution (group_id,param_id,cat_id,loading) VALUES ('".$group_id."','".$param_id."','".$cat_id."','".$loading."')";
            $this->db->query($sql);
        }

        return 1;
    }
    public function admin_profile($admin_id){
        $result=array();
        $sql="SELECT * FROM admin WHERE admin_id=$admin_id";
        $query=$this->db->query($sql);
        $result['admindata']=$query->result_array();
        return $result;
    }
    public function update_profile($data){
        $name=$data['name'];
        $username=$data['username'];
        $admin_id=$data['admin_id'];
        $sql="UPDATE `admin` SET `name`='$name',`username`='$username' WHERE admin_id=$admin_id";
        $query=$this->db->query($sql);
        return 1;
    }
    public function update_user($data){
        $user_id=$data['user_id'];
        $this->db->where('user_id', $user_id);
        $this->db->update('user', $data);
        return 1;
    }
    public function update_param($data,$param_id,$priority){
        $sql="SELECT min,max,cat_id FROM parameter WHERE param_id=$param_id";
        $query=$this->db->query($sql);
        $new_data=$query->result_array();
        $min_1=$new_data[0]['min'];
        $max_1=$new_data[0]['max'];
        $cat_id=$new_data[0]['cat_id'];
        $min=$data['min'];
        $max=$data['max'];
        if($min_1!=$min || $max_1!=$max){
            $sql = "INSERT INTO parameter_history (param_id,cat_id,min,max) VALUES ('" . $param_id . "','" . $cat_id . "','" . $min_1 . "','" . $max_1 . "')";
            $this->db->query($sql);
        }
        $this->db->where('param_id', $param_id);
        $this->db->update('parameter', $data);
        $this->db->where('param_id', $param_id);
        $this->db->delete('norms');
//        $priority=$data['priority'];
        $diff=$max-$min;
        $per=$diff/20;
        if($priority == "value_2"){
            $min=$min-$per;
            $upper=$min;
            // $lower=0;
            // $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','0','" . $priority . "')";
            // $this->db->query($sql);
            $scale=array("1","4","6","8","11","14","17","21","25","29","33","38","43","48","54","60","66","72","78","85","92","100");
            for($i=0;$i<21;$i++){
                $lower=$min+($i*$per);
                $upper=$lower+$per;
                $score=$scale[$i];
                $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
                $this->db->query($sql);
            }
            $lower=$upper;
            $upper=1000000;
            $score=$scale[21];
            $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
            $this->db->query($sql);
        }else{
            // $lower=0;
            // $upper=$min;
            // $score=100;
            // $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
            // $this->db->query($sql);
            $scale=array("100","92","85","78","72","66","60","54","48","43","38","33","29","25","21","17","14","11","8","6","4","1");
            for($i=0;$i<21;$i++){
                $lower=$min+($i*$per);
                $upper=$lower+$per;
                $score=$scale[$i];
                $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
                $this->db->query($sql);
            }
            $lower=$upper;
            $upper=1000000;
            $score=1;
            $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
            $this->db->query($sql);
        }
        return 1;
    }

    public function update_param_bmi($data,$param_id,$med){
        $sql="SELECT min,max FROM parameter WHERE param_id=$param_id";
        $query=$this->db->query($sql);
        $new_data=$query->result_array();
        $min_1=$new_data[0]['min'];
        $max_1=$new_data[0]['max'];
        $min=$data['min'];
        $max=$data['max'];
        if($min_1!=$min || $max_1!=$max){
            $sql = "INSERT INTO parameter_history (param_id,min,max) VALUES ('" . $param_id . "','" . $min_1 . "','" . $max_1 . "')";
            $this->db->query($sql);
        }
        $this->db->where('param_id', $param_id);
        $this->db->update('parameter', $data);
        $this->db->where('param_id', $param_id);
        $this->db->delete('norms');
//        $priority=$data['priority'];
        $diff=$med-$min;
        $per=$diff/20;
        $min=$min-$per;
        $upper=$min;
        $priority = "bmi";
        // $lower=0;
        // $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','0','" . $priority . "')";
        // $this->db->query($sql);
        $scale=array("1","4","6","8","11","14","17","21","25","29","33","38","43","48","54","60","66","72","78","85","92","100");
        for($i=0;$i<21;$i++){
            $lower=$min+($i*$per);
            $upper=$lower+$per;
            $score=$scale[$i];
            $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
            $this->db->query($sql);
        }


        $diff=$max-$med;
        $per=$diff/20;
        $priority = "bmi";
        $scale=array("100","92","85","78","72","66","60","54","48","43","38","33","29","25","21","17","14","11","8","6","4","1");
        for($i=0;$i<21;$i++){
            $lower=$med+($i*$per);
            $upper=$lower+$per;
            $score=$scale[$i];
            $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
            $this->db->query($sql);
        }
        $lower=$upper;
        $upper=1000000;
        $score=1;
        $sql = "INSERT INTO norms (param_id,lower_limit,upper_limit,score,priority) VALUES ('" . $param_id . "','" . $lower . "','" . $upper . "','" . $score . "','" . $priority . "')";
        $this->db->query($sql);
        return 1;
    }

    public function change_password($password){
        $admin_id=$_SESSION['admin_id'];
        $sql="UPDATE admin SET `password`=$password WHERE admin_id=$admin_id";
        $query=$this->db->query($sql);
        return 1;
    }
    public function view_norms($param_id,$cat_id){
        $result=array();
        $sql="SELECT * FROM parameter WHERE param_id=$param_id";
        $query=$this->db->query($sql);
        $result['paradata']=$query->result_array();
        $sql="SELECT * FROM categories WHERE cat_id=$cat_id";
        $query=$this->db->query($sql);
        $result['catdata']=$query->result_array();
        $sql="SELECT * FROM norms WHERE param_id=$param_id";
        $query=$this->db->query($sql);
        $result['normdata']=$query->result_array();
        return $result;
    }
    public function down_norms($param_id){
        
    }
    public function update_org($org_id,$data){
        $this->db->where('org_id', $org_id);
        $this->db->update('organization', $data);
    }
    public function org_active($org_id){
        $one="1";
        $sql="UPDATE organization SET `active`=$one WHERE org_id=$org_id";
        $query=$this->db->query($sql);
        return 1;
    }
    public function param_active($param_id){
        $one="1";
        $sql="UPDATE parameter SET `active`=$one WHERE param_id=$param_id";
        $query=$this->db->query($sql);
        return 1;
    }
    public function param_inactive($param_id){
        $zero="0";
        $sql="UPDATE parameter SET `active`=$zero WHERE param_id=$param_id";
        $query=$this->db->query($sql);
        return 1;
    }
    public function org_inactive($org_id){
        $zero="0";
        $sql="UPDATE organization SET `active`=$zero WHERE org_id=$org_id";
        $query=$this->db->query($sql);
        return 1;
    }
    public function org_delete($org_id){

        $this->db->where('org_id', $org_id);
        $this->db->delete('organization');
        $this->db->delete('user');

    }
    public function delete_cat($cat_id){
        $this->db->where('cat_id', $cat_id);
        $this->db->delete('categories');
        $this->db->where('cat_id', $cat_id);
        $this->db->delete('parameter');
        $this->db->where('cat_id', $cat_id);
        $this->db->delete('dist_group');
        $this->db->where('cat_id', $cat_id);
        $this->db->delete('distribution');
    }
    public function user_delete($user_id){

        $this->db->where('user_id', $user_id);
        $this->db->delete('user');

    }
    public function user_active($user_id){
        $one="1";
        $sql="UPDATE user SET `active`=$one WHERE user_id=$user_id";
        $query=$this->db->query($sql);
        return 1;
    }
    public function user_inactive($user_id){
        $zero="0";
        $sql="UPDATE user SET `active`=$zero WHERE user_id=$user_id";
        $query=$this->db->query($sql);
        return 1;
    }
    public function update_cat($cat_id,$name){
        $sql="UPDATE categories SET `name`='$name' WHERE cat_id=$cat_id";
        $query=$this->db->query($sql);
        return 1;
    }
    public function delete_group($group_id,$cat_id){
        $sql="SELECT group_reliability,parameters FROM dist_group WHERE group_id=$group_id";
        $query=$this->db->query($sql);
        $data=$query->result_array();
        $group_var=$data[0]['group_reliability'];
        $para=$data[0]['parameters'];
        $par=explode( ',' , $para);
        foreach($par as $id){
            $sql="UPDATE parameter SET `group_set`='0' WHERE para_group_id=$id";
            $this->db->query($sql);
        }
        $sql="SELECT total_var FROM categories WHERE cat_id=$cat_id";
        $query=$this->db->query($sql);
        $data=$query->result_array();
        $new_var=$data[0]['total_var']-$group_var;
        $sql="UPDATE categories SET `total_var`='$new_var' WHERE cat_id=$cat_id";
        $this->db->query($sql);
        $this->db->where('group_id', $group_id);
        $this->db->delete('distribution');
        $this->db->where('group_id', $group_id);
        $this->db->delete('dist_group');
        return 1;
    }
    public function delete_param($param_id){
        $sql="SELECT * FROM assessment_details WHERE param_id=$param_id";
        $query=$this->db->query($sql);
        $num=$query->num_rows();
        if($num>0){
            $result['status']=false;
        }
        else{
            $this->db->where('param_id', $param_id);
            $this->db->delete('parameter');
            $this->db->where('param_id', $param_id);
            $this->db->delete('norms');
            $sql="SELECT group_id,cat_id FROM distribution WHERE param_id=$param_id";
            $query=$this->db->query($sql);
            $num=$query->num_rows();
            if($num>0){
                $data=$query->result_array();
                $group_id=$data[0]['group_id'];
                $cat_id=$data[0]['cat_id'];
                $this->db->where('param_id', $param_id);
                $this->db->delete('distribution');
                $sql="SELECT parameters,group_reliability FROM dist_group WHERE group_id=$group_id";
                $query=$this->db->query($sql);
                $data=$query->result_array();
                $parameters=$data[0]['parameters'];
                $group_var=$data[0]['group_reliability'];
                $word=", ".$param_id;
                $word2=$param_id.", ";
                if (strpos($parameters, $word)) {
                    $new = str_replace($word, "", $parameters);
                    $sql="UPDATE dist_group SET `parameters`=$new WHERE group_id=$group_id";
                    $this->db->query($sql);
                }
                elseif(strpos($parameters, $word2)){
                    $new = str_replace($word2, "", $parameters);
                    $sql="UPDATE dist_group SET `parameters`=$new WHERE group_id=$group_id";
                    $this->db->query($sql);
                }
                else{
                    $sql="SELECT total_var FROM categories WHERE cat_id=$cat_id";
                    $query=$this->db->query($sql);
                    $data=$query->result_array();
                    $new_var=$data[0]['total_var']-$group_var;
                    $sql="UPDATE categories SET `total_var`='$new_var' WHERE cat_id=$cat_id";
                    $this->db->query($sql);
                    $sql="DELETE FROM dist_group WHERE group_id=$group_id";
                    $this->db->query($sql);
                }
            }
            $result['status']=true;
        }

        return $result;
    }
	public function student_report($data1){
		//print_r($data);
		$result=array();
	if($data1['search_Name']=="")
	{
		$sql="SELECT *,(select org_name from organization where org_id = candidate.org_id) as org_name FROM candidate WHERE candidate_id='".$data1['search_Id']."'";
        $query=$this->db->query($sql);
        $result['data']=$query->result_array();
	}
	else
	{
		$sql="SELECT *,(select org_name from organization where org_id = candidate.org_id) as org_name FROM candidate WHERE name='".$data1['search_Name']."'";
        $query=$this->db->query($sql);
        $result['data']=$query->result_array();
	}
	
	
	return $result;
       
    }
	public function record_student_by_org($data1){
		//print_r($data);
		$result=array();
		$sql="SELECT *,(select org_name from organization where org_id = '".$data1['org_id']."') as org_name FROM candidate WHERE org_id='".$data1['org_id']."'";
        $query=$this->db->query($sql);
        $result['data']=$query->result_array();
	
	return $result;
       
    }
	public function record_student_statewise($data1){
		//print_r($data);
		$result=array();
		$sql="SELECT org_id from organization where state = '".$data1['search_state_by']."'";
		//echo $sql;
		//exit();
        $query=$this->db->query($sql);
        $result['orgdata']=$query->result_array();
		
		$sql="SELECT *,(select org_name from organization where org_id = candidate.org_id) as org_name FROM `candidate` WHERE `org_id`='".$result['orgdata'][0]['org_id']."'";
		//echo $sql;
		//exit();
        $query=$this->db->query($sql);
        $result['data']=$query->result_array();
	
	return $result;
       
    }
	public function record_student_districtwise($data1){
		//print_r($data);
		$result=array();
		$sql="SELECT org_id from organization where district = '".$data1['search_district_by']."'";
		//echo $sql;
		//exit();
        $query=$this->db->query($sql);
        $result['orgdata']=$query->result_array();
		
		$sql="SELECT *,(select org_name from organization where org_id = candidate.org_id) as org_name FROM `candidate` WHERE `org_id`='".$result['orgdata'][0]['org_id']."'";
		//echo $sql;
		//exit();
        $query=$this->db->query($sql);
        $result['data']=$query->result_array();
	
	return $result;
       
    }
	
	public function all_games(){
		//print_r($data);
		$result=array();
		$sql="SELECT * from categories";
		//echo $sql;
		//exit();
        $query=$this->db->query($sql);
        $result['data']=$query->result_array();
	    return $result;
    }
    public function all_years(){
        //print_r($data);
        $result=array();
        $sql="SELECT DISTINCT(timestamp) as year from candidate";
        //echo $sql;
        //exit();
        $query=$this->db->query($sql);
        $result['data']=$query->result_array();
        return $result;
    }
    public function all_position(){
        //print_r($data);
        $result=array();
        $sql="SELECT * from grading";
        //echo $sql;
        //exit();
        $query=$this->db->query($sql);
        $result['data']=$query->result_array();
        return $result;
    }
	
	public function get_game_wise_report($data1){
		//print_r($data);
		$result=array();
		$sql="SELECT name from categories where cat_id='".$data1['search_by_game']."'";
		//echo $sql;
		//exit();
        $query=$this->db->query($sql);
        $result['data1']=$query->result_array();
		
		$sql="SELECT candidate_id,(select name from candidate where candidate_id = assessment_master.candidate_id) as name ,(select age from candidate where candidate_id = assessment_master.candidate_id) as age,(select gender from candidate where candidate_id = assessment_master.candidate_id) as gender,(select city from candidate where candidate_id = assessment_master.candidate_id) as city,(select state from candidate where candidate_id = assessment_master.candidate_id) as state,(select email from candidate where candidate_id = assessment_master.candidate_id) as email from assessment_master where cat_id='".$data1['search_by_game']."'";
		//echo $sql;
		//exit();
        $query=$this->db->query($sql);
        $result['data']=$query->result_array();
		//print_r($result['data']);
		//exit();
	    return $result;
    }
    public function get_position_wise_report($data1){
        //print_r($data);
        $result=array();
        $sql="SELECT candidate_id,(select name from candidate where candidate_id = assessment_master.candidate_id) as name ,(select age from candidate where candidate_id = assessment_master.candidate_id) as age,(select gender from candidate where candidate_id = assessment_master.candidate_id) as gender,(select city from candidate where candidate_id = assessment_master.candidate_id) as city,(select state from candidate where candidate_id = assessment_master.candidate_id) as state,(select email from candidate where candidate_id = assessment_master.candidate_id) as email from assessment_master where talent_status='".$data1['search_by_position']."'";
        $query=$this->db->query($sql);
        $result['data']=$query->result_array();
        return $result;
    }
    public function get_score_wise_report($data1){
        //print_r($data);
        $result=array();
        $low=$data1['low'];
        $high=$data1['high'];
        $sql="SELECT candidate_id,overall_score,(select name from candidate where candidate_id = assessment_master.candidate_id) as name ,(select age from candidate where candidate_id = assessment_master.candidate_id) as age,(select gender from candidate where candidate_id = assessment_master.candidate_id) as gender,(select city from candidate where candidate_id = assessment_master.candidate_id) as city,(select state from candidate where candidate_id = assessment_master.candidate_id) as state,(select email from candidate where candidate_id = assessment_master.candidate_id) as email from assessment_master where overall_score >= '$low' AND overall_score <= '$high' ";
        $query=$this->db->query($sql);
        $result['data']=$query->result_array();
        return $result;
    }
    public function get_year_wise_report($data1){
        $result=array();
        $sql="SELECT * FROM candidate WHERE timestamp='".$data1['search_by_year']."'";
//        $sql="SELECT candidate_id,(select name from candidate where candidate_id = assessment_master.candidate_id) as name ,(select age from candidate where candidate_id = assessment_master.candidate_id) as age,(select gender from candidate where candidate_id = assessment_master.candidate_id) as gender,(select city from candidate where candidate_id = assessment_master.candidate_id) as city,(select state from candidate where candidate_id = assessment_master.candidate_id) as state,(select email from candidate where candidate_id = assessment_master.candidate_id) as email from assessment_master where cat_id='".$data1['search_by_game']."'";
        $query=$this->db->query($sql);
        $result['data']=$query->result_array();
        return $result;
    }
    public function report_main_norms($data){
        $cat_id=$data['category'];
        $type=$data['type'];
        $result=array();
        if($type=='none'){
            $sql="SELECT DISTINCT(para_group_id) FROM parameter WHERE type='specific' AND cat_id=$cat_id AND name != 'BMI'";
            $query=$this->db->query($sql);
            $num=$query->num_rows();
            $arr=$query->result_array();
            for($i=0;$i<$num;$i++){
                $para_group_id=$arr[$i]['para_group_id'];
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='1' AND gender='male'";
                $query=$this->db->query($sql);
                $result['one'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='1' AND gender='female'";
                $query=$this->db->query($sql);
                $result['two'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='2' AND gender='male'";
                $query=$this->db->query($sql);
                $result['three'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='2' AND gender='female'";
                $query=$this->db->query($sql);
                $result['four'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='3' AND gender='male'";
                $query=$this->db->query($sql);
                $result['five'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='3' AND gender='female'";
                $query=$this->db->query($sql);
                $result['six'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='4' AND gender='male'";
                $query=$this->db->query($sql);
                $result['seven'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='4' AND gender='female'";
                $query=$this->db->query($sql);
                $result['eight'][$i]=$query->result_array();
            }
            $result['num1']=$num;
            $sql="SELECT DISTINCT(name) FROM parameter WHERE type='genetic' AND cat_id=$cat_id";
            $query=$this->db->query($sql);
            $num=$query->num_rows();
            $arr=$query->result_array();
            for($i=0;$i<$num;$i++){
                $name=$arr[$i]['name'];
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.name='$name' AND gender='male'";
                $query=$this->db->query($sql);
                $result['nine'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.name='$name' AND gender='female'";
                $query=$this->db->query($sql);
                $result['ten'][$i]=$query->result_array();
            }
            $result['num2']=$num;
        }
        else {
            $sql="SELECT DISTINCT(name) FROM parameter WHERE type='genetic' AND cat_id=$cat_id";
            $query=$this->db->query($sql);
            $num=$query->num_rows();
            $arr=$query->result_array();
            for($i=0;$i<$num;$i++){
                $name=$arr[$i]['name'];
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.name='$name' AND gender='male'";
                $query=$this->db->query($sql);
                $result['one'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.name='$name' AND gender='female'";
                $query=$this->db->query($sql);
                $result['two'][$i]=$query->result_array();
            }
            $result['num']=$num;
        }
        return $result;
    }
	 public function report_sports($data){
        $cat_id=$data['category'];
        $type=$data['type'];
        $s_date=$data['start_date'];
		$start_date = date("Y-m-d", strtotime($s_date));
		$e_date=$data['end_date'];
		$end_date = date("Y-m-d", strtotime($e_date));
		$radio=$data['radio'];
		$order=$data['order'];
        $result=array();
        if($type!="none"){
			if($s_date!="" && $e_date!=""){
				
        $sql="SELECT assessment_master.assessment_id,assessment_master.candidate_id,assessment_master.cat_id,assessment_master.based_on,assessment_master.overall_score,assessment_master.talent_status,assessment_master.user_id,assessment_master.timestamp,candidate.candidate_id,candidate.org_id,candidate.name,candidate.idnumber,candidate.gender,candidate.age,candidate.address,candidate.city,candidate.state,candidate.country from assessment_master join candidate on assessment_master.candidate_id=candidate.candidate_id where assessment_master.based_on='".$type."' and DATE(assessment_master.timestamp) >='$start_date' AND DATE(assessment_master.timestamp)<='$end_date' and assessment_master.cat_id='".$cat_id."'  ORDER BY $radio $order";
            $query=$this->db->query($sql);
			if($query->num_rows() > 0) {
            $result['data']=$query->result_array();
			} else {
			$result['msg']="No data found";	
			}
			} else {
				$sql="SELECT assessment_master.assessment_id,assessment_master.candidate_id,assessment_master.cat_id,assessment_master.based_on,assessment_master.overall_score,assessment_master.talent_status,assessment_master.user_id,assessment_master.timestamp,candidate.candidate_id,candidate.org_id,candidate.name,candidate.idnumber,candidate.gender,candidate.age,candidate.address,candidate.city,candidate.state,candidate.country from assessment_master join candidate on assessment_master.candidate_id=candidate.candidate_id where assessment_master.cat_id='".$cat_id."' ORDER BY $radio $order";

            $query=$this->db->query($sql);
			if($query->num_rows() > 0) {
            $result['data']=$query->result_array();
			} else {
			$result['msg']="No data found";	
			}
			}
		} else {
			if($s_date!="" && $e_date!=""){
				
			$sql="SELECT assessment_master.assessment_id,assessment_master.candidate_id,assessment_master.cat_id,assessment_master.based_on,assessment_master.overall_score,assessment_master.talent_status,assessment_master.user_id,assessment_master.timestamp,candidate.candidate_id,candidate.org_id,candidate.name,candidate.idnumber,candidate.gender,candidate.age,candidate.address,candidate.city,candidate.state,candidate.country from assessment_master join candidate on assessment_master.candidate_id=candidate.candidate_id where DATE(assessment_master.timestamp) >='$start_date' AND DATE(assessment_master.timestamp)<='$end_date' and assessment_master.cat_id='".$cat_id."' ORDER BY $radio $order";

            $query=$this->db->query($sql);
			if($query->num_rows() > 0) {
            $result['data']=$query->result_array();
			} else {
			$result['msg']="No data found";	
			}
			} else {
				$sql="SELECT assessment_master.assessment_id,assessment_master.candidate_id,assessment_master.cat_id,assessment_master.based_on,assessment_master.overall_score,assessment_master.talent_status,assessment_master.user_id,assessment_master.timestamp,candidate.candidate_id,candidate.org_id,candidate.name,candidate.idnumber,candidate.gender,candidate.age,candidate.address,candidate.city,candidate.state,candidate.country from assessment_master join candidate on assessment_master.candidate_id=candidate.candidate_id where assessment_master.cat_id='".$cat_id."' ORDER BY $radio $order";
 
            $query=$this->db->query($sql);
			if($query->num_rows() > 0) {
            $result['data']=$query->result_array();
			} else {
			$result['msg']="No data found";	
			}
			}
		}
       $sql="SELECT name FROM categories where cat_id=$cat_id"; 
      	$query=$this->db->query($sql);
		$result['data1']=$query->result_array();
        return $result;
    }
    public function report_main_howto(){
        $result=array();
        $sql="SELECT DISTINCT(name) as name, type , how_to FROM parameter";
        $query=$this->db->query($sql);
        $result['data']=$query->result_array();
        return $result;
    }
    public function report_main_limit($data){
        $cat_id=$data['category'];
        $type=$data['type'];
        $result=array();
        if($type=='none'){
            $sql="SELECT DISTINCT(para_group_id) FROM parameter WHERE type='specific' AND cat_id=$cat_id AND name != 'BMI'";
            $query=$this->db->query($sql);
            $num=$query->num_rows();
            $arr=$query->result_array();
            $sql="SELECT * FROM parameter_history WHERE cat_id='$cat_id'";
            $query=$this->db->query($sql);
            $result['history']=$query->result_array();
            for($i=0;$i<$num;$i++){
                $para_group_id=$arr[$i]['para_group_id'];
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='1' AND gender='male'";
                $query=$this->db->query($sql);
                $result['one'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='1' AND gender='female'";
                $query=$this->db->query($sql);
                $result['two'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='2' AND gender='male'";
                $query=$this->db->query($sql);
                $result['three'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='2' AND gender='female'";
                $query=$this->db->query($sql);
                $result['four'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='3' AND gender='male'";
                $query=$this->db->query($sql);
                $result['five'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='3' AND gender='female'";
                $query=$this->db->query($sql);
                $result['six'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='4' AND gender='male'";
                $query=$this->db->query($sql);
                $result['seven'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='4' AND gender='female'";
                $query=$this->db->query($sql);
                $result['eight'][$i]=$query->result_array();
            }
            $result['num1']=$num;
            $sql="SELECT DISTINCT(name) FROM parameter WHERE type='genetic' AND cat_id=$cat_id";
            $query=$this->db->query($sql);
            $num=$query->num_rows();
            $arr=$query->result_array();
            for($i=0;$i<$num;$i++){
                $name=$arr[$i]['name'];
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.name='$name' AND gender='male'";
                $query=$this->db->query($sql);
                $result['nine'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.name='$name' AND gender='female'";
                $query=$this->db->query($sql);
                $result['ten'][$i]=$query->result_array();
            }
            $result['num2']=$num;
        }
        else if($type=='genetic') {
            $sql="SELECT * FROM parameter_history WHERE cat_id='$cat_id'";
            $query=$this->db->query($sql);
            $result['history']=$query->result_array();
            $sql="SELECT DISTINCT(name) FROM parameter WHERE type='genetic' AND cat_id=$cat_id";
            $query=$this->db->query($sql);
            $num=$query->num_rows();
            $arr=$query->result_array();
            for($i=0;$i<$num;$i++){
                $name=$arr[$i]['name'];
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.name='$name' AND gender='male'";
                $query=$this->db->query($sql);
                $result['one'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.name='$name' AND gender='female'";
                $query=$this->db->query($sql);
                $result['two'][$i]=$query->result_array();
            }
            $result['num']=$num;
        }
        else{
            $sql="SELECT * FROM parameter_history WHERE cat_id='$cat_id'";
            $query=$this->db->query($sql);
            $result['history']=$query->result_array();
            $sql="SELECT DISTINCT(para_group_id) FROM parameter WHERE type='specific' AND cat_id=$cat_id AND name != 'BMI'";
            $query=$this->db->query($sql);
            $num=$query->num_rows();
            $arr=$query->result_array();
            for($i=0;$i<$num;$i++){
                $para_group_id=$arr[$i]['para_group_id'];
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='1' AND gender='male'";
                $query=$this->db->query($sql);
                $result['one'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='1' AND gender='female'";
                $query=$this->db->query($sql);
                $result['two'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='2' AND gender='male'";
                $query=$this->db->query($sql);
                $result['three'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='2' AND gender='female'";
                $query=$this->db->query($sql);
                $result['four'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='3' AND gender='male'";
                $query=$this->db->query($sql);
                $result['five'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='3' AND gender='female'";
                $query=$this->db->query($sql);
                $result['six'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='4' AND gender='male'";
                $query=$this->db->query($sql);
                $result['seven'][$i]=$query->result_array();
                $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='4' AND gender='female'";
                $query=$this->db->query($sql);
                $result['eight'][$i]=$query->result_array();
            }
            $result['num']=$num;
        }
        return $result;
    }
    public function report_main(){
        $result=array();
        $sql="SELECT * from categories";
        $query=$this->db->query($sql);
        $result['catdata']=$query->result_array();
        return $result;
    }
    public function print_group($para_group_id){
        $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='1' AND gender='male'";
        $query=$this->db->query($sql);
        $result['one']=$query->result_array();
        $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='1' AND gender='female'";
        $query=$this->db->query($sql);
        $result['two']=$query->result_array();
        $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='2' AND gender='male'";
        $query=$this->db->query($sql);
        $result['three']=$query->result_array();
        $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='2' AND gender='female'";
        $query=$this->db->query($sql);
        $result['four']=$query->result_array();
        $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='3' AND gender='male'";
        $query=$this->db->query($sql);
        $result['five']=$query->result_array();
        $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='3' AND gender='female'";
        $query=$this->db->query($sql);
        $result['six']=$query->result_array();
        $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='4' AND gender='male'";
        $query=$this->db->query($sql);
        $result['seven']=$query->result_array();
        $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='4' AND gender='female'";
        $query=$this->db->query($sql);
        $result['eight']=$query->result_array();
        return $result;
    }
    public function print_group_1($name,$cat_id){
        $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.name='$name' AND parameter.cat_id=$cat_id AND gender='male' AND type='genetic'";
        $query=$this->db->query($sql);
        $result['one']=$query->result_array();
        $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.name='$name' AND parameter.cat_id=$cat_id AND gender='female' AND type='genetic'";
        $query=$this->db->query($sql);
        $result['two']=$query->result_array();
        return $result;
    }
    public function print_group_2($cat_id){
        $name='BMI';
        $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.name='$name' AND parameter.cat_id=$cat_id AND gender='male' AND type='specific'";
        $query=$this->db->query($sql);
        $result['one']=$query->result_array();
        $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.name='$name' AND parameter.cat_id=$cat_id AND gender='female' AND type='specific'";
        $query=$this->db->query($sql);
        $result['two']=$query->result_array();
        return $result;
    }
    public function print_cat($cat_id){
        $sql="SELECT name FROM categories WHERE cat_id=$cat_id";
        $query=$this->db->query($sql);
        $new_data=$query->result_array();
        $result['name']=$new_data[0]['name'];
        $sql="SELECT DISTINCT(para_group_id) FROM parameter WHERE type='specific' AND cat_id=$cat_id AND name != 'BMI'";
        $query=$this->db->query($sql);
        $num=$query->num_rows();
        $arr=$query->result_array();
        for($i=0;$i<$num;$i++){
            $para_group_id=$arr[$i]['para_group_id'];
            $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='1' AND gender='male'";
            $query=$this->db->query($sql);
            $result['one'][$i]=$query->result_array();
            $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='1' AND gender='female'";
            $query=$this->db->query($sql);
            $result['two'][$i]=$query->result_array();
            $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='2' AND gender='male'";
            $query=$this->db->query($sql);
            $result['three'][$i]=$query->result_array();
            $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='2' AND gender='female'";
            $query=$this->db->query($sql);
            $result['four'][$i]=$query->result_array();
            $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='3' AND gender='male'";
            $query=$this->db->query($sql);
            $result['five'][$i]=$query->result_array();
            $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='3' AND gender='female'";
            $query=$this->db->query($sql);
            $result['six'][$i]=$query->result_array();
            $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='4' AND gender='male'";
            $query=$this->db->query($sql);
            $result['seven'][$i]=$query->result_array();
            $sql="SELECT * FROM norms,parameter WHERE norms.param_id=parameter.param_id AND parameter.para_group_id=$para_group_id AND age='4' AND gender='female'";
            $query=$this->db->query($sql);
            $result['eight'][$i]=$query->result_array();
        }
        $result['num']=$num;
        return $result;
    }
}
?>