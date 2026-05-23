<?php defined('BASEPATH') or exit('No direct script access allowed');
ob_start();
$getUserData = get_staff(get_staff_user_id());
$hrmsAutoLoginURL = "https://hrms.farmworld.in/autologin/" . base64_encode($getUserData->email);
?>
<li id="top_search" class="dropdown" data-toggle="tooltip" data-placement="bottom"
    data-title="<?php echo _l('search_by_tags'); ?>">
    <input type="search" id="search_input" class="form-control"
        placeholder="<?php echo _l('top_search_placeholder'); ?>">
    <div id="search_results">
    </div>
    <ul class="dropdown-menu search-results animated fadeIn no-mtop search-history" id="search-history">
    </ul>
</li>
<li id="top_search_button">
    <button class="btn"><i class="fa fa-search"></i></button>
</li>
<?php
$top_search_area = ob_get_contents();
ob_end_clean();
?>
<div id="header">
    <div class="hide-menu"><i class="fa fa-bars"></i></div>
    <div id="logo">
        <?php get_company_logo(get_admin_uri() . '/') ?>
    </div>
    <nav>
        <div class="small-logo">
            <span class="text-primary">
                <?php get_company_logo(get_admin_uri() . '/') ?>
            </span>
        </div>

        <div class="mobile-menu">
            <button type="button" class="navbar-toggle visible-md visible-sm visible-xs mobile-menu-toggle collapsed"
                data-toggle="collapse" data-target="#mobile-collapse" aria-expanded="false">
                <i class="fa fa-chevron-down"></i>
            </button>

            <ul class="mobile-icon-menu">
                <?php if (is_mobile()) { ?>
                    <li class="dropdown notifications-wrapper header-notifications">
                        <?php $this->load->view('admin/includes/notifications'); ?>
                    </li>
                    <li class="header-timers">
                        <a href="#" id="top-timers" class="dropdown-toggle top-timers" data-toggle="dropdown">
                            <i class="fa fa-clock-o fa-fw fa-lg"></i>
                            <span
                                class="label bg-success icon-total-indicator icon-started-timers<?php if (count($startedTimers) == 0)
                                    echo ' hide'; ?>">
                                <?php echo count($startedTimers); ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu animated fadeIn started-timers-top width300" id="started-timers-top">
                            <?php $this->load->view('admin/tasks/started_timers', ['startedTimers' => $startedTimers]); ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>

            <div class="mobile-navbar collapse" id="mobile-collapse" role="navigation">
                <ul class="nav navbar-nav">
                    <li class="header-my-profile"><a
                            href="<?php echo admin_url('profile'); ?>"><?php echo _l('nav_my_profile'); ?></a></li>
                    <li class="header-my-timesheets"><a
                            href="<?php echo admin_url('staff/timesheets'); ?>"><?php echo _l('my_timesheets'); ?></a>
                    </li>
                    <li class="header-edit-profile"><a
                            href="<?php echo admin_url('staff/edit_profile'); ?>"><?php echo _l('nav_edit_profile'); ?></a>
                    </li>
                    <li class="header-hrms-login"><a href="<?= $hrmsAutoLoginURL ?>" target="_blank">HRMS Login</a></li>
                    <?php if (is_staff_member()) { ?>
                        <li class="header-newsfeed"><a href="#"
                                class="open_newsfeed mobile"><?php echo _l('whats_on_your_mind'); ?></a></li>
                    <?php } ?>
                    <li class="header-logout"><a href="#"
                            onclick="logout(); return false;"><?php echo _l('nav_logout'); ?></a></li>
                </ul>
            </div>
        </div>

        <ul class="nav navbar-nav navbar-right">
            <?php if (!is_mobile())
                echo $top_search_area; ?>
            <?php hooks()->do_action('after_render_top_search'); ?>

            <!-- Profile Image -->
            <li class="icon header-user-profile" data-toggle="tooltip" title="<?php echo get_staff_full_name(); ?>"
                data-placement="bottom">
                <a href="#" class="dropdown-toggle profile" data-toggle="dropdown" aria-expanded="false">
                    <?php echo staff_profile_image($current_user->staffid, ['img', 'img-responsive', 'staff-profile-image-small', 'pull-left']); ?>
                </a>
                <ul class="dropdown-menu animated fadeIn">
                    <li><a href="<?php echo admin_url('profile'); ?>"><?php echo _l('nav_my_profile'); ?></a></li>
                    <li><a href="<?php echo admin_url('staff/timesheets'); ?>"><?php echo _l('my_timesheets'); ?></a>
                    </li>
                    <li><a
                            href="<?php echo admin_url('staff/edit_profile'); ?>"><?php echo _l('nav_edit_profile'); ?></a>
                    </li>
                    <li><a href="<?= $hrmsAutoLoginURL ?>" target="_blank">HRMS Login</a></li>

                    <?php if (get_option('disable_language') == 0) { ?>
                        <li class="dropdown-submenu pull-left">
                            <a href="#" tabindex="-1"><?php echo _l('language'); ?></a>
                            <ul class="dropdown-menu">
                                <li class="<?php if ($current_user->default_language == "")
                                    echo 'active'; ?>">
                                    <a
                                        href="<?php echo admin_url('staff/change_language'); ?>"><?php echo _l('system_default_string'); ?></a>
                                </li>
                                <?php foreach ($this->app->get_available_languages() as $user_lang) { ?>
                                    <li<?php if ($current_user->default_language == $user_lang)
                                        echo ' class="active"'; ?>>
                                        <a
                                            href="<?php echo admin_url('staff/change_language/' . $user_lang); ?>"><?php echo ucfirst($user_lang); ?></a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>

            <li><a href="#" onclick="logout(); return false;"><?php echo _l('nav_logout'); ?></a></li>
        </ul>
        </li>

        <!-- HRMS Icon -->
             <?php if ($getUserData->staffid == 5) { 
    // HRMS User Login (staffid 4)
    $userLoginURL = "https://hrms.farmworld.in/autologin/" . base64_encode($getUserData->email);

    // HRMS Admin Login (staffid 4)
    $adminData     = get_staff(4);
    $adminLoginURL = "https://hrms.farmworld.in/autologin/" . base64_encode($adminData->email);
    ?>
    
    <!-- HRMS User Login -->
    <li class="header-newsfeed" data-toggle="tooltip" title="HRMS User Login" data-placement="bottom">
        <a href="<?= $userLoginURL ?>" target="_blank">
            <i class="fa fa-address-card fa-2x text-primary" aria-hidden="true" style="margin-top: 15px !important;"></i>
        </a>
    </li>

    <!-- HRMS Admin Login -->
    <li class="header-newsfeed" data-toggle="tooltip" title="HRMS Admin Login" data-placement="bottom">
        <a href="<?= $adminLoginURL ?>" target="_blank">
            <i class="fa fa-address-card fa-2x text-danger" aria-hidden="true" style="margin-top: 15px !important;"></i>
        </a>
    </li>

<?php } else { ?>
    <!-- Default HRMS Icon -->
    <li class="header-newsfeed" data-toggle="tooltip" title="HRMS Login" data-placement="bottom">
        <a href="<?= $hrmsAutoLoginURL ?>" target="_blank">
            <i class="fa fa-address-card fa-2x" aria-hidden="true" style="margin-top: 15px !important;"></i>
        </a>
    </li>
<?php } ?>
        <!--<li class="header-newsfeed" data-toggle="tooltip" title="HRMS Login" data-placement="bottom">-->
        <!--    <a href="<?= $hrmsAutoLoginURL ?>" target="_blank">-->
        <!--        <i class="fa fa-address-card fa-2x" aria-hidden="true" style="margin-top: 15px !important;"></i>-->
        <!--    </a>-->
        <!--</li>-->
        <?php if (in_array($current_user->staffid, [1, 3, 4, 5])) { ?>
            <li class="header-newsfeed" data-toggle="tooltip" title="Shipment Report" data-placement="bottom">
                <a href="https://shipment.farmworld.in/" target="_blank">
                    <i class="fa fa-truck fa-2x" aria-hidden="true" style="margin-top: 15px !important;"></i>
                </a>
            </li>
        <?php } ?>


        <!-- Logout Icon -->
        <li class="header-newsfeed" data-toggle="tooltip" title="Logout" data-placement="bottom">
            <a href="#" onclick="logout(); return false;">
                <i class="fa fa-power-off fa-2x" aria-hidden="true" style="margin-top: 15px !important;"></i>
            </a>
        </li>

        <!-- Newsfeed -->
        <?php if (is_staff_member()) { ?>
            <li class="icon header-newsfeed">
                <a href="#" class="open_newsfeed desktop" data-toggle="tooltip"
                    title="<?php echo _l('whats_on_your_mind'); ?>" data-placement="bottom">
                    <i class="fa fa-share fa-fw fa-lg" aria-hidden="true"></i>
                </a>
            </li>
        <?php } ?>

        <!-- Todo Icon -->
        <li class="icon header-todo">
            <a href="<?php echo admin_url('todo'); ?>" data-toggle="tooltip" title="<?php echo _l('nav_todo_items'); ?>"
                data-placement="bottom">
                <i class="fa fa-check-square-o fa-fw fa-lg"></i>
                <span
                    class="label bg-warning icon-total-indicator nav-total-todos<?php if ($current_user->total_unfinished_todos == 0)
                        echo ' hide'; ?>">
                    <?php echo $current_user->total_unfinished_todos; ?>
                </span>
            </a>
        </li>

        <!-- Notifications -->
        <li class="dropdown notifications-wrapper header-notifications" data-toggle="tooltip"
            title="<?php echo _l('nav_notifications'); ?>" data-placement="bottom">
            <?php $this->load->view('admin/includes/notifications'); ?>
        </li>
        </ul>
    </nav>

</div>
<div id="mobile-search" class="<?php if (!is_mobile()) {
    echo 'hide';
} ?>">
    <ul>
        <?php
        if (is_mobile()) {
            echo $top_search_area;
        } ?>
    </ul>
</div>