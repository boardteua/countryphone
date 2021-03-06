<?php
/**
 * ch_setting class
 * 
 * @link              https://weekdays.te.ua
 * @since             1.0.0
 * @package           CountryPhone
 *
 * @author org100h
 */

namespace countryphone;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class ch_setting {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $ch;

    /**
     * Start up
     * 
     */
    public function __construct() {

        $this->ch = countryphone::get_instance();

        add_action('admin_menu', [$this, 'add_plugin_page']);
        add_action('admin_init', [$this, 'page_init']);

        // load assets to admin page
        add_action('admin_enqueue_scripts', [$this, 'add_assets']);

        add_action('wp_ajax_get_rows', [$this, 'get_rows']);
        add_action('wp_ajax_add_row', [$this, 'add_row']);
        add_action('wp_ajax_edit_row', [$this, 'edit_row']);
        add_action('wp_ajax_delete_row', [$this, 'delete_row']);
        add_action('wp_ajax_add_to_menu', [$this, 'add_to_menu']);
        add_action('wp_ajax_set_menu', [$this, 'set_menu']);
    }

    /**
     * Add options page
     */
    public function add_plugin_page() {

        // This page will be under "Settings"
        add_options_page(
                'Phone Setting', 'Phone Setting', 'manage_options', $this->ch->prefix . '_admin', array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page() {
        // Set class property

        $nav_setting = get_option($this->ch->prefix . '-inmenu');
        ?>
        <div class="wrap">
            <h1>Phone by Country Setting Page</h1>
            Using api: <strong> <?php echo $this->ch->api; ?> </strong>
            <ul class="phone-list">

            </ul>
            <form method="post" id="add_number">                
                Phone <input name="phone_number" class="ch-phone-number" /> Geo Code <input name="country_code" class="ch-country-code " />
                <a href="#" class="ch-add-hone button button-primary">Add Phone</a>
                <a href="#" class="ch-save-hone button button-primary">Save Phone</a>

            </form>

            <form method="post" id="add_setting">
                <div class="add_to_menu_box">
                    <label for="add_to_nav">Add phone dropdown to menu</label>
                    <input name="add_to_nav" type="checkbox" id="add_to_nav" <?php if ($nav_setting == 'true') echo'checked="checked"'; ?>>
                </div>
                <div class="select_menu_box "  >

                    <?php $this->get_navs() ?>
                </div>
            </form>


        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init() {
        register_setting(
                $this->ch->prefix . '-option-group', // Option group
                $this->ch->prefix . '-option', // Option name
                array($this, 'sanitize') // Sanitize
        );
    }

    /**
     * get dropdown with avaliable navs list
     */
    private function get_navs() {

        $menus = get_registered_nav_menus();

        $sel_menu = (get_option($this->ch->prefix . '-selnav')) ? get_option($this->ch->prefix . '-selnav') : '';

        echo ' <label for="locations-primary" class="selected-menu">Select a menu to edit:</label>';
        echo ' <select name="menu-locations" id="locations-primary">';
        foreach ($menus as $location => $description) {
            $cur = '';
            if ($sel_menu == $location) {
                $cur = 'selected="selected"';
            }
            echo '<option ' . $cur . '  value="' . $location . '">' . $description . '</option>';
        }


        echo '</select>';
    }

    /**
     * show dropdown in navigation menu
     */
    public function add_to_menu() {
        $set = $_POST['req'];
        if ($set != '' && !get_option($this->ch->prefix . '-inmenu')) {
            add_option($this->ch->prefix . '-inmenu', $set);

            wp_send_json_success(array(
                'option' => $set
            ));
        } elseif ($set != '') {
            update_option($this->ch->prefix . '-inmenu', $set);

            wp_send_json_success(array(
                'option' => $set
            ));
        }
        die();
    }

    /**
     * select menu where dropdown placed 
     */
    public function set_menu() {
        $set = $_POST['req'];
        if ($set != '' && !get_option($this->ch->prefix . '-selnav')) {
            add_option($this->ch->prefix . '-selnav', $set);

            wp_send_json_success(array(
                'option' => $set
            ));
        } elseif ($set != '') {
            update_option($this->ch->prefix . '-selnav', $set);

            wp_send_json_success(array(
                'option' => $set
            ));
        }
        die();
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input) {
        $new_input = array();
        foreach ($input as $key => $row) {

            if (isset($row['phone_number'])) {
                $new_input[$key]['phone_number'] = sanitize_text_field($row['phone_number']);
            }
            if (isset($row['country_code'])) {
                $new_input[$key]['country_code'] = sanitize_text_field($row['country_code']);
            }
        }

        return $new_input;
    }

    /**
     * Add new phone\code pair to option
     */
    public function add_row() {

        $res = array();
        parse_str($_POST['req'], $form);


        if (!get_option($this->ch->prefix . '-option')) {
            $res[] = $form;
            add_option($this->ch->prefix . '-option', $res);
        } else {

            $res = get_option($this->ch->prefix . '-option');
            $res[] = $form;

            update_option($this->ch->prefix . '-option', $res);
        }


        if ($res) {
            wp_send_json_success(array(
                'option' => get_option($this->ch->prefix . '-option')
            ));
        } else {
            wp_send_json_error(array(
                'option' => 'error'
            ));
        };
    }

    /**
     * Get all pair from options
     */
    public function get_rows() {

        if (get_option($this->ch->prefix . '-option')) {
            wp_send_json_success(array(
                'option' => get_option($this->ch->prefix . '-option')
            ));
        } else {
            wp_send_json_error(array(
                'option' => 'error'
            ));
        }
    }

    /**
     * Delete pair from option
     */
    public function delete_row() {
        $res = get_option($this->ch->prefix . '-option');

        if (count($res) == 1) {
            delete_option($this->ch->prefix . '-option');

            wp_send_json_success(array(
                'option' => get_option($this->ch->prefix . '-option')
            ));
        }

        unset($res[absint($_POST['req'])]);

        update_option($this->ch->prefix . '-option', array_values($res));

        if (get_option($this->ch->prefix . '-option')) {
            wp_send_json_success(array(
                'option' => get_option($this->ch->prefix . '-option')
            ));
        } else {
            wp_send_json_error(array(
                'option' => 'error'
            ));
        }
    }

    /**
     * Edit pair from option
     */
    public function edit_row() {


        $res = get_option($this->ch->prefix . '-option');

        $id = absint($_POST['req']['id']);
        $phone_number = $_POST['req']['phone_number'];
        $country_code = $_POST['req']['country_code'];

        $res[$id]['phone_number'] = $phone_number;
        $res[$id]['country_code'] = $country_code;


        update_option($this->ch->prefix . '-option', $res);

        if (get_option($this->ch->prefix . '-option')) {
            wp_send_json_success(array(
                'option' => get_option($this->ch->prefix . '-option')
            ));
        } else {
            wp_send_json_error(array(
                'option' => 'error'
            ));
        }
    }

    /**
     * register assets on plugin setting page
     * 
     * @param string $hook
     * @return NULL
     */
    public function add_assets($hook) {

        if ($hook != 'settings_page_' . $this->ch->prefix . '_admin') {
            return;
        }

        wp_enqueue_script($this->ch->prefix . '-js', plugins_url('/assets/js/back.js', __FILE__), array('jquery'), '1.0');
        wp_enqueue_style($this->ch->prefix . '-bcss', plugins_url('/assets/css/back.css', __FILE__));

       // wp_enqueue_script($this->ch->prefix . '-js');
       // wp_enqueue_style($this->ch->prefix . '-bcss');
    }

}
