<aside class="left-sidebar" data-sidebarbg="skin5">
    <?php
    if(!isset($_SESSION['admin_id'])){
        redirect(CTRL."Admin/login_view");
    }
    ?>
        <!-- Sidebar scroll-->
        <div class="scroll-sidebar">

            <!-- Sidebar navigation-->
            <nav class="sidebar-nav">
                <ul id="sidebarnav" class="p-t-30">
                    <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="<?=CTRL?>Admin/admin_org_view" aria-expanded="false"><i class="mdi mdi-pencil"></i><span class="hide-menu">Users</span></a></li>
					<li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="<?=CTRL?>Admin/admin_profile" aria-expanded="false"><i class="mdi mdi-human-male"></i><span class="hide-menu">Profile</span></a></li>
                    <li class="sidebar-item"> <a class="sidebar-link waves-effect waves-dark sidebar-link" href="<?=CTRL?>Admin/admin_logout" aria-expanded="false"><i class="mdi mdi-logout"></i><span class="hide-menu">Log out</span></a></li>
                </ul>
            </nav>
            <!-- End Sidebar navigation -->
        </div>
        <!-- End Sidebar scroll-->
    </aside>