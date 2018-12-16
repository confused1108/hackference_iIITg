<aside class="left-sidebar" data-sidebarbg="skin5">
        <!-- Sidebar scroll-->
    <?php
    if(!isset($_SESSION['user_id'])){
        redirect(CTRL."User/login_view");
    }
    ?>
        <div class="scroll-sidebar">
            <!-- Sidebar navigation-->
            <nav class="sidebar-nav">
                <ul id="sidebarnav" class="p-t-30">
                    <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="<?=CTRL?>User/user_fir" aria-expanded="false"><i class="mdi mdi-view-dashboard"></i><span class="hide-menu">New FIR</span></a></li>


<!--                    <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="--><?//=CTRL?><!--User/user_old" aria-expanded="false"><i class="mdi mdi-relative-scale"></i><span class="hide-menu">Old Projects</span></a></li>-->

                    <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="<?=CTRL?>User/user_details" aria-expanded="false"><i class="mdi mdi-pencil"></i><span class="hide-menu">Personal Details</span></a></li>

<!--					<li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="--><?//=CTRL?><!--User/report_main" aria-expanded="false"><i class="fa fa-file"></i><span class="hide-menu">Reports</span></a></li>-->
					
                    <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="<?=CTRL?>User/log_out" aria-expanded="false"><i class="mdi mdi-logout"></i><span class="hide-menu">Log Out</span></a></li>
    


                </ul>
            </nav>
            <!-- End Sidebar navigation -->
        </div>
        <!-- End Sidebar scroll-->
    </aside>