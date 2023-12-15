<?php

/*
Plugin Name: ThinkShout Test
Description: Allows site owners to setup custom alerts for special operating hours.
Version: 1.0
Author: Solomon Dougherty
*/

//activation hook to make db row
register_activation_hook( __FILE__, 'update_option_init' );

function update_option_init() {
    update_option('banner_settings', array());
}

//initialize plugin
add_action('init', function() {
    $Tst_Banner = new Tst_Banner();
    $Tst_Banner->init();
});
class Tst_Banner {


    //call this method to initialize plugin
    public function init() {
        add_action( 'admin_enqueue_scripts', array($this, 'tst_banner_plugin_enqueue_admin_script') );
        add_action( 'wp_enqueue_scripts', array($this, 'tst_banner_plugin_enqueue_script') );
        add_action( 'admin_menu', array($this, 'create_menu_page') );
        add_action( 'admin_menu', array($this, 'add_banner_settings') );
        add_action( 'admin_post_tst_form_submit', array($this, 'tst_form_submit_action') );
        add_action( 'wp_ajax_nopriv_tst_banner_request', array($this, 'banner_popup_ajax') );
        add_action( 'wp_ajax_tst_banner_request', array($this, 'banner_popup_ajax') );

    }

    //load admin stylesheets and datetimepicker library
    public function tst_banner_plugin_enqueue_admin_script() {
        wp_enqueue_style('tst_enqueue_admin_css', plugin_dir_url(__FILE__) . 'admin/admin_css.css' );
        wp_enqueue_style('tst_enqueue_datepicker_css', plugin_dir_url( __FILE__ ) . 'js/datetimepicker/build/jquery.datetimepicker.min.css' );
        wp_enqueue_script('jquery');
        wp_enqueue_script( 'tst_enqueue_datepicker', plugin_dir_url( __FILE__ ) . 'js/datetimepicker/build/jquery.datetimepicker.full.js' );
        wp_enqueue_script('tst_enqueue_datepicker_fields', plugin_dir_url( __FILE__ ) . 'js/tst_datetimepicker_fields.js' );

    }

    //load frontend stylesheets and js
    public function tst_banner_plugin_enqueue_script() {
        wp_enqueue_script('jquery');
        wp_enqueue_style('tst_enqueue_banner_css', plugin_dir_url(__FILE__) . 'public/banner_css.css' );
        wp_enqueue_script('tst_ajax_script', plugin_dir_url(__FILE__) . 'js/tst_popup.js' );
        wp_add_inline_script( 'tst_ajax_script',
            'const tst_ajax = ' . json_encode(
                array(
                    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                    'nonce' => wp_create_nonce('ajax_nonce'),
                ) ),
            'before' );

    }

    //create menu page
    public function create_menu_page() {
        add_menu_page(
            'TST Banner',
            'TST Banner',
            'manage_options',
            'tst_banner_page',
            array($this, 'menu_page_render'),
            '',
            6
        );
    }

    //output admin page for banner settings
    public function menu_page_render() {
        ?>
        <div class="wrap">
            <form action="<?php echo esc_url(admin_url( 'admin-post.php' ) ); ?>" method="post">
                <?php   settings_fields( 'tst_banner_page' );
                        do_settings_sections( 'tst_banner_page' );
                        submit_button();
                        wp_nonce_field('tst_submit_nonce', 'tst_nonce_field'); ?>
                <input type="hidden" name="action" value="tst_form_submit"> <?php //lets us get $_POST ?>
            </form>
            <div class="banner-display">
                <h2>Saved Banners</h2>
                <?php
                $this->admin_banner_display(); //pulling data from banner_settings and looping through to display existing banners

                ?>
            </div>

        </div>
        <?php
    }


    //*** Admin page creation, form, and database saves ***


    //save admin form settings
    public function tst_form_submit_action() {
        if ( ! wp_verify_nonce( $_POST['tst_nonce_field'], 'tst_submit_nonce')) {
            die();
        }
        $banner_settings = $_POST['banner_settings'];
        $sanitized_settings = [];
        foreach ($banner_settings as $banner_setting) {
            $sanitized_settings[] = sanitize_text_field($banner_setting);
        }
        $banner_info = get_option('banner_settings');
        array_push($banner_info, $banner_settings);
        update_option('banner_settings', $banner_info);
        wp_redirect( esc_url( admin_url('admin.php?page=tst_banner_page') ) );
        exit();
    }

    //add settings section and fields
    public function add_banner_settings() {
        add_settings_section(
            'banner_settings',
            'Special Hours Days',
            array($this, 'banner_settings_callback'),
            'tst_banner_page'
        );

        //add start hours field
        add_settings_field(
            'banner_start_hours_field',
            'Start Time',
            array($this, 'settings_start_hours_callback'),
            'tst_banner_page',
            'banner_settings',
            array('Set start time of special closure')
        );

        //add start hours field
        add_settings_field(
            'banner_end_hours_field',
            'End Time',
            array($this, 'settings_end_hours_callback'),
            'tst_banner_page',
            'banner_settings',
            array('Set end time of special closure')
        );

        //add date field
        add_settings_field(
            'banner_date_field',
            'Date',
            array($this, 'settings_date_callback'),
            'tst_banner_page',
            'banner_settings',
            array('Check this box to display a closed for the day banner')
        );

        //add text field
        add_settings_field(
            'banner_text_field',
            'Hours Announcement',
            array($this, 'settings_text_callback'),
            'tst_banner_page',
            'banner_settings',
            array('Write an announcement for special hours')
        );
    }
    //callback for add_settings_section() in register_banner_settings()
    public function banner_settings_callback() {
        echo '<p>Add a banner which will appear to all site visitors for special closure hours.
             The banner will appear on the date selected between the times selected and display 
             the provided message on all site pages. All fields are required. </p>';
    }

    //callback for start hours field
    public function settings_start_hours_callback() {
        echo '<input type="text" id="banner_start_hours_field" name="banner_settings[start_time]" required>';
    }

    //callback for end hours field
    public function settings_end_hours_callback() {
        echo '<input type="text" id="banner_end_hours_field" name="banner_settings[end_time]" required>';
    }

    //callback for date field
    public function settings_date_callback() {
        echo '<input type="text" id="banner_date_field" name="banner_settings[date]" required pattern="\d{2}.\d{2}.\d{4}">';
    }

    //callback for text field
    public function settings_text_callback() {
        echo '<textarea id="banner_text_field" name="banner_settings[text_string]"></textarea required>';
    }

    //*** Admin database requests, saved banners display ***

    public function admin_banner_display() {
        $banners = get_option('banner_settings');
        ?>
        <table>
        <?php
        foreach ($banners as $banner) {
            echo '<tr>';
            foreach($banner as $data) {
                $sanitized = esc_html($data);
                 ?> <td> <?php echo stripslashes($sanitized); ?> </td> <?php
            }
            echo '</tr>';
        }
        ?>
        </table>
        <?php
    }


    //*** Frontend banner display


    //Banner popup
    public function banner_popup_ajax() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax_nonce')) {
            die();
        }
        $banners = get_option('banner_settings');
        $sanitized;
        foreach ($banners as $banner) {
            if ($banner[start_time] <= current_time('H:i') &&
                $banner[end_time] >= current_time('H:i') &&
                $banner[date] == wp_date('d.m.Y') )
            {
                $banner = array_map('esc_html', $banner);
                $banner = array_map('stripslashes', $banner);
                $sanitized = $banner;
                $stripped;
                echo json_encode($sanitized);
                wp_die();
            }
        }
        wp_die();
        }


}