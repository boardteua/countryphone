<?php

/**
 * CountryPhone plugin
 *
 * @link              https://weekdays.te.ua
 * @since             1.0.0
 * @package           CountryPhone
 *
 * @wordpress-plugin
 * Plugin Name:       CountryPhone Plugin
 * Plugin URI:        #
 * Description:       Simple plugin to show your support phone by country code. Use with shortcode [countryphone];
 * Version:           1.0.0
 * Author:            org100h
 * Author URI:        https://weekdays.te.ua
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace countryphone;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class countryphone {

    public $prefix = null;
    public $api = null;
    private static $instance = null;

    public static function get_instance() {
        if (null == self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->prefix = 'countryphone';
        $this->api = 'http://ip-api.com/json/';
        //add shortcode
        add_shortcode($this->prefix, [$this, 'phone_list']);
        add_action('wp_enqueue_scripts', [$this, 'front_assets']);
        add_filter('wp_nav_menu_items', [$this, 'add_drop_to_menu'], 10, 2);
    }

    /**
     * 
     * add_shortcode($this->prefix, [$this, 'phone_list']);
     * 
     * @param array $atts
     * @param string $content
     * @return string
     */
    public function phone_list($atts, $content = null) {
        $this->print_list(true);
    }

    /**
     * Advanced Method to Retrieve Client IP Address
     * @return string ip
     */
    private function get_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
// trim for safety measures
                    $ip = trim($ip);
// attempt to validate IP
                    if ($this->validate_ip($ip)) {
                        return $ip;
                    }
                }
            }
        }
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
    }

    /**
     * Ensures an ip address is both a valid IP and does not fall within
     * a private network range.
     * @return boolean 
     */
    private function validate_ip($ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }
        return true;
    }

    /**
     * 
     * @param string $ip
     * @return string
     */
    private function get_cc_by_ip($ip) {
        if (false === ($json = get_transient($this->prefix . $ip))) {
            $service_url = $this->api . $ip;
            try {
                if ($json = file_get_contents($service_url)) {
                    $obj = json_decode($json);
                }
            } catch (Exeption $e) {
                error_log('An error occurred. ' . $e);
            }
            set_transient($this->prefix . $ip, $json, DAY_IN_SECONDS);
        } else {
            $obj = json_decode($json);
        }

        return $obj->countryCode;
    }

    private function print_list($echo) {

        $rows = get_option($this->prefix . '-option');
        $cc = $this->get_cc_by_ip($this->get_ip());

        $cur_key = array_search($cc, array_column($rows, 'country_code'));
        $cur = $rows[$cur_key];
        unset($rows[$cur_key]);
        $rows = array_values($rows);

        $spear = '<svg width="6" height="5" viewBox="0 0 6 5" fill="none" class="spear" xmlns="http://www.w3.org/2000/svg">
        <path d="M3 0L5.59808 4.5L0.401924 4.5L3 0Z" transform="translate(6 5) rotate(-180)" fill="#D42323"/>
        </svg>';
        if ($echo) {
            echo '<div class="cc-phones">';
            echo '<div class="main-phone"><div class="flag ' . strtolower($cur['country_code']) . '"></div>' . $cur['phone_number'] . $spear . '</div>';
            echo '<div class="phones">';
            foreach ($rows as $row) {
                echo '<div class="phone-item">' . '<div class="flag ' . strtolower($row['country_code']) . '"></div>' . $row['phone_number'] . '</div>';
            }
            echo '</div>';
            echo '</div>';
        } else {
            $html = '';
            $html .= '<div class="cc-phones">';
            $html .= '<div class="main-phone"><div class="flag ' . strtolower($cur['country_code']) . '"></div>' . $cur['phone_number'] . $spear . '</div>';
            $html .= '<div class="phones">';
            foreach ($rows as $row) {
                $html .= '<div class="phone-item">' . '<div class="flag ' . strtolower($row['country_code']) . '"></div>' . $row['phone_number'] . '</div>';
            }
            $html .= '</div>';
            $html .= '</div>';

            return $html;
        }
    }

    public function add_drop_to_menu($items, $args) {

        $nav_setting = get_option($this->prefix . '-inmenu');
        $sel_menu = get_option($this->prefix . '-selnav');

        if ($nav_setting == 'true') {
            if ($args->theme_location == $sel_menu) {
                $items .= '<li class="menu-item cc-item cc-phones d-flex align-items-center"';
                $items .= $this->print_list(false);
                $items .= '</li>';
            }
        }

        return $items;
    }

    public function front_assets() {
        wp_enqueue_script($this->prefix . '-fjs', plugins_url('/assets/js/front.js', __FILE__), array('jquery'), '1.0');
        wp_enqueue_style($this->prefix . '-css', plugins_url('/assets/css/style.css', __FILE__));
        wp_enqueue_style($this->prefix . '-flags', plugins_url('/assets/css/flags.css', __FILE__));
    }

}

require_once 'ch_setting.php';

$countryphone = countryphone::get_instance();

if (is_admin()) {
    $ch_setting = new ch_setting();
}







