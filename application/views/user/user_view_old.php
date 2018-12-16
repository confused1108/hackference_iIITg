<?php
/**
 * Created by PhpStorm.
 * User: Hitesh
 * Date: 21-Sep-18
 * Time: 3:59 PM
 */
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="<?=THEME?>assets/images/favicon.png">
    <title>Talent System</title>
    <!-- Custom CSS -->
    <link href="<?=THEME?>assets/libs/jquery-steps/jquery.steps.css" rel="stylesheet">
    <link href="<?=THEME?>assets/libs/jquery-steps/steps.css" rel="stylesheet">
    <link href="<?=THEME?>dist/css/style.min.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
<!-- ============================================================== -->
<!-- Preloader - style you can find in spinners.css -->
<!-- ============================================================== -->
<div class="preloader">
    <div class="lds-ripple">
        <div class="lds-pos"></div>
        <div class="lds-pos"></div>
    </div>
</div>
<!-- ============================================================== -->
<!-- Main wrapper - style you can find in pages.scss -->
<!-- ============================================================== -->
<div id="main-wrapper">
    <!-- ============================================================== -->
    <!-- Topbar header - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <header class="topbar" data-navbarbg="skin5">
        <nav class="navbar top-navbar navbar-expand-md navbar-dark">
            <div class="navbar-header" data-logobg="skin5">
                <!-- This is for the sidebar toggle which is visible on mobile only -->
                <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
                <!-- ============================================================== -->
                <!-- Logo -->
                <!-- ============================================================== -->
                <a class="navbar-brand" href="index.html">
                    <!-- Logo icon -->
                    <b class="logo-icon p-l-10">
                        <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                        <!-- Dark Logo icon -->
                        <img src="<?=THEME?>assets/images/logo-icon.png" alt="homepage" class="light-logo" />

                    </b>
                    <!--End Logo icon -->
                    <!-- Logo text -->
                    <span class="logo-text">
                             <!-- dark Logo text -->
                             <img src="<?=THEME?>assets/images/logo-text.png" alt="homepage" class="light-logo" />

                        </span>
                    <!-- Logo icon -->
                    <!-- <b class="logo-icon"> -->
                    <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                    <!-- Dark Logo icon -->
                    <!-- <img src="<?=THEME?>assets/images/logo-text.png" alt="homepage" class="light-logo" /> -->

                    <!-- </b> -->
                    <!--End Logo icon -->
                </a>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Toggle which is visible on mobile only -->
                <!-- ============================================================== -->
                <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="ti-more"></i></a>
            </div>
            <!-- ============================================================== -->
            <!-- End Logo -->
            <!-- ============================================================== -->
            <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
                <!-- ============================================================== -->
                <!-- toggle and nav items -->
                <!-- ============================================================== -->
                <ul class="navbar-nav float-left mr-auto">
                    <li class="nav-item d-none d-md-block"><a class="nav-link sidebartoggler waves-effect waves-light" href="javascript:void(0)" data-sidebartype="mini-sidebar"><i class="mdi mdi-menu font-24"></i></a></li>
                    <!-- ============================================================== -->
                    <!-- create new -->
                    <!-- ============================================================== -->
                
                </ul>
                <!-- ============================================================== -->
                <!-- Right side toggle and nav items -->
                <!-- ============================================================== -->
               
                        
                    <!-- ============================================================== -->
                    <!-- End Messages -->
                    <!-- ============================================================== -->

                    <!-- ============================================================== -->
                    <!-- User profile and search -->
                    <!-- ============================================================== -->
                   
            </div>
        </nav>
    </header>
    <!-- ============================================================== -->
    <!-- End Topbar header -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->
    <?php include "includes/aside.php"; ?> 

    <!-- ============================================================== -->
    <!-- End Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Page wrapper  -->
    <!-- ============================================================== -->
    <div class="page-wrapper">
        <!-- ============================================================== -->
        <!-- Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->

        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Container fluid  -->
        <!-- ============================================================== -->
        <div class="container-fluid">

            <!-- ============================================================== -->
            <!-- Start Page Content -->
            <!-- ============================================================== -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Candidate Details</h5>
							
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Score</th>
                                        <th>Talent</th>
                                        <th>Name</th>
                                        <th>Sports</th>
                                        <th>Gender</th>
                                        <th>Age</th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ($userdata as $val) {
                                        ?>
                                        <tr>
                                            <td><?php echo $val['based_on']; ?></td>
                                            <td><?php echo $val['overall_score']; ?></td>
                                            <td><?php echo $val['talent_status']; ?></td>
                                            <td><?php echo $val['name']; ?></td>
                                            <td><?php echo $val['cname']; ?></td>
                                            <td><?php echo $val['gender']; ?></td>
                                            <td><?php echo $val['age']; ?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    </tbody>

                                </table>
                            </div>
							<div class="card-body">
							 <a href="<?= CTRL ?>User/assessment_print/<?php echo $userdata[0]['assessment_id']; ?>" target="_blank  "> <button type="button" class="btn btn-primary">Print Certificate</button></a>
							</div>
                            <br>
                            <h5 class="card-title">Parameter Details</h5>
                            <div class="table-responsive">
                                <table id="zero_config" class="table table-striped table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Input Score</th>
                                        <th>Output Score</th>
                                        
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ($olddata as $val) {
                                        ?>
                                        <tr>
                                            <td><?php echo $val['name']; ?></td>
                                            <td><?php echo $val['input_score']; ?></td>
                                            <td><?php echo $val['output_score']; ?></td>
                                            
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    </tbody>
                                    
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End PAge Content -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Right sidebar -->
            <!-- ============================================================== -->
            <!-- .right-sidebar -->
            <!-- ============================================================== -->
            <!-- End Right sidebar -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Container fluid  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- footer -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- End footer -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Page wrapper  -->
    <!-- ============================================================== -->
</div>
<!-- ============================================================== -->
<!-- End Wrapper -->
<!-- ============================================================== -->
<!-- ============================================================== -->
<!-- All Jquery -->
<!-- ============================================================== -->
<script src="<?=THEME?>assets/libs/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap tether Core JavaScript -->
<script src="<?=THEME?>assets/libs/popper.js/dist/umd/popper.min.js"></script>
<script src="<?=THEME?>assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- slimscrollbar scrollbar JavaScript -->
<script src="<?=THEME?>assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
<script src="<?=THEME?>assets/extra-libs/sparkline/sparkline.js"></script>
<!--Wave Effects -->
<script src="<?=THEME?>dist/js/waves.js"></script>
<!--Menu sidebar -->
<script src="<?=THEME?>dist/js/sidebarmenu.js"></script>
<!--Custom JavaScript -->
<script src="<?=THEME?>dist/js/custom.min.js"></script>
<!-- this page js -->
<script src="<?=THEME?>assets/libs/jquery-steps/build/jquery.steps.min.js"></script>
<script src="<?=THEME?>assets/libs/jquery-validation/dist/jquery.validate.min.js"></script>
<script>
    // Basic Example with form
    var form = $("#example-form");
    form.validate({
        errorPlacement: function errorPlacement(error, element) { element.before(error); },
        rules: {
            confirm: {
                equalTo: "#password"
            }
        }
    });
    form.children("div").steps({
        headerTag: "h3",
        bodyTag: "section",
        transitionEffect: "slideLeft",
        onStepChanging: function(event, currentIndex, newIndex) {
            form.validate().settings.ignore = ":disabled,:hidden";
            return form.valid();
        },
        onFinishing: function(event, currentIndex) {
            form.validate().settings.ignore = ":disabled";
            return form.valid();
        },
        onFinished: function(event, currentIndex) {
            alert("Submitted!");
        }
    });


</script>
</body>

</html>
