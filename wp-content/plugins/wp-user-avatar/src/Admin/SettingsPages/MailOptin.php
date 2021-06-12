<?php

namespace ProfilePress\Core\Admin\SettingsPages;

use MailOptin\Core\Repositories\EmailCampaignRepository;
use MailOptin\Core\Repositories\OptinCampaignsRepository;
use ProfilePress\Core\Classes\Installer\PluginSilentUpgrader;
use ProfilePress\Core\Classes\Installer\PPress_Install_Skin;

class MailOptin
{
    const SLUG = 'pp-mailoptin';

    private $config = array(
        'lite_plugin'        => 'mailoptin/mailoptin.php',
        'lite_download_url'  => 'https://downloads.wordpress.org/plugin/mailoptin.latest-stable.zip',
        'mailoptin_settings' => 'admin.php?page=mailoptin-optin-campaigns',
    );

    private $output_data = array();

    public function __construct()
    {
        if ( ! $this->is_configured()) {
            add_action('ppress_admin_hooks', function () {
                add_action('admin_menu', array($this, 'register_settings_page'));
            });
        }

        add_action('wp_ajax_ppress_activate_plugin', [$this, 'ppress_activate_plugin']);
        add_action('wp_ajax_ppress_install_plugin', [$this, 'ppress_install_plugin']);

        if (wp_doing_ajax()) {
            add_action('wp_ajax_ppress_mailoptin_page_check_plugin_status', array($this, 'ajax_check_plugin_status'));
        }

        // Check what page we are on.
        $page = isset($_GET['page']) ? \sanitize_key(\wp_unslash($_GET['page'])) : '';

        if (self::SLUG !== $page) return;

        add_action('admin_init', array($this, 'redirect_to_mailoptin_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function ppress_install_plugin()
    {
        // Run a security check.
        check_ajax_referer('ppress-admin-nonce', 'nonce');

        $generic_error = esc_html__('There was an error while performing your request.', 'wp-user-avatar');
        $type          = ! empty($_POST['type']) ? sanitize_key($_POST['type']) : 'plugin';

        if ( ! current_user_can('install_plugins')) {
            wp_send_json_error($generic_error);
        }

        // Determine whether file modifications are allowed.
        if ( ! wp_is_file_mod_allowed('ppress_can_install')) {
            wp_send_json_error($generic_error);
        }

        $error = $type === 'plugin' ? esc_html__('Could not install plugin. Please download and install manually.', 'wp-user-avatar') : esc_html__('Could not install addon. Please download from wpforms.com and install manually.', 'wp-user-avatar');

        if (empty($_POST['plugin'])) {
            wp_send_json_error($error);
        }

        // Set the current screen to avoid undefined notices.
        set_current_screen('profilepress_page_pp-mailoptin');

        // Prepare variables.
        $url = esc_url_raw(
            add_query_arg(
                [
                    'page' => 'pp-extensions',
                ],
                admin_url('admin.php')
            )
        );

        ob_start();
        $creds = request_filesystem_credentials($url, '', false, false, null);

        // Hide the filesystem credentials form.
        ob_end_clean();

        // Check for file system permissions.
        if ($creds === false) {
            wp_send_json_error($error);
        }

        if ( ! WP_Filesystem($creds)) {
            wp_send_json_error($error);
        }

        /*
         * We do not need any extra credentials if we have gotten this far, so let's install the plugin.
         */

        // Do not allow WordPress to search/download translations, as this will break JS output.
        remove_action('upgrader_process_complete', ['Language_Pack_Upgrader', 'async_upgrade'], 20);

        // Create the plugin upgrader with our custom skin.
        $installer = new PluginSilentUpgrader(new PPress_Install_Skin());

        // Error check.
        if ( ! method_exists($installer, 'install') || empty($_POST['plugin'])) {
            wp_send_json_error($error);
        }

        $installer->install($_POST['plugin']); // phpcs:ignore

        // Flush the cache and return the newly installed plugin basename.
        wp_cache_flush();

        $plugin_basename = $installer->plugin_info();

        if (empty($plugin_basename)) {
            wp_send_json_error($error);
        }

        $result = [
            'msg'          => $generic_error,
            'is_activated' => false,
            'basename'     => $plugin_basename,
        ];

        // Check for permissions.
        if ( ! current_user_can('activate_plugins')) {
            $result['msg'] = $type === 'plugin' ? esc_html__('Plugin installed.', 'wp-user-avatar') : esc_html__('Addon installed.', 'wp-user-avatar');

            wp_send_json_success($result);
        }

        // Activate the plugin silently.
        $activated = activate_plugin($plugin_basename);

        if ( ! is_wp_error($activated)) {
            $result['is_activated'] = true;
            $result['msg']          = $type === 'plugin' ? esc_html__('Plugin installed & activated.', 'wp-user-avatar') : esc_html__('Addon installed & activated.', 'wp-user-avatar');

            wp_send_json_success($result);
        }

        // Fallback error just in case.
        wp_send_json_error($result);
    }

    public function ppress_activate_plugin()
    {
        // Run a security check.
        check_ajax_referer('ppress-admin-nonce', 'nonce');

        // Check for permissions.
        if ( ! current_user_can('activate_plugins')) {
            wp_send_json_error(esc_html__('Plugin activation is disabled for you on this site.', 'wp-user-avatar'));
        }

        if (isset($_POST['plugin'])) {

            $plugin   = sanitize_text_field(wp_unslash($_POST['plugin']));
            $activate = activate_plugins($plugin);

            if ( ! is_wp_error($activate)) {
                wp_send_json_success(esc_html__('Plugin activated.', 'wp-user-avatar'));
            }
        }

        wp_send_json_error(esc_html__('Could not activate plugin. Please activate from the Plugins page.', 'wp-user-avatar'));
    }

    public function register_settings_page()
    {
        add_submenu_page(
            PPRESS_SETTINGS_SLUG,
            'Popups & Optin Forms',
            esc_html__('Popups & Optins', 'wp-user-avatar'),
            'manage_options',
            self::SLUG,
            array($this, 'output')
        );
    }

    public function enqueue_assets()
    {
        wp_enqueue_script(
            'ppress-admin-page-mailoptin',
            PPRESS_ASSETS_URL . "/js/mailoptin.js",
            array('jquery'),
            PPRESS_VERSION_NUMBER,
            true
        );

        \wp_localize_script(
            'ppress-admin-page-mailoptin',
            'ppress_pluginlanding',
            $this->get_js_strings()
        );
    }

    /**
     * JS Strings.
     */
    protected function get_js_strings()
    {
        $error_could_not_install = sprintf(
            wp_kses( /* translators: %s - Lite plugin download URL. */
                __('Could not install plugin. Please <a href="%s">download</a> and install manually.', 'wp-user-avatar'),
                array(
                    'a' => array(
                        'href' => true,
                    ),
                )
            ),
            esc_url($this->config['lite_download_url'])
        );

        $error_could_not_activate = sprintf(
            wp_kses( /* translators: %s - Lite plugin download URL. */
                __('Could not activate plugin. Please activate from the <a href="%s">Plugins page</a>.', 'wp-user-avatar'),
                array(
                    'a' => array(
                        'href' => true,
                    ),
                )
            ),
            esc_url(admin_url('plugins.php'))
        );

        return array(
            'installing'                => esc_html__('Installing...', 'wp-user-avatar'),
            'activating'                => esc_html__('Activating...', 'wp-user-avatar'),
            'activated'                 => esc_html__('MailOptin Installed & Activated', 'wp-user-avatar'),
            'install_now'               => esc_html__('Install Now', 'wp-user-avatar'),
            'activate_now'              => esc_html__('Activate Now', 'wp-user-avatar'),
            'download_now'              => esc_html__('Download Now', 'wp-user-avatar'),
            'plugins_page'              => esc_html__('Go to Plugins page', 'wp-user-avatar'),
            'error_could_not_install'   => $error_could_not_install,
            'error_could_not_activate'  => $error_could_not_activate,
            'manual_install_url'        => $this->config['lite_download_url'],
            'manual_activate_url'       => admin_url('plugins.php'),
            'mailoptin_settings_button' => esc_html__('Go to MailOptin Settings', 'wp-user-avatar'),
        );
    }

    /**
     * Generate and output page HTML.
     */
    public function output()
    {
        ?>
        <style>
            #ppress-admin-mailoptin {
                width: 700px;
                margin: 0 auto;
            }

            #ppress-admin-mailoptin .notice {
                display: none
            }

            #ppress-admin-mailoptin *, #ppress-admin-mailoptin *::before, #ppress-admin-mailoptin *::after {
                -webkit-box-sizing: border-box;
                -moz-box-sizing: border-box;
                box-sizing: border-box;
            }

            #ppress-admin-mailoptin section {
                margin: 50px 0;
                text-align: left;
                clear: both;
            }

            #ppress-admin-mailoptin .top {
                text-align: center;
            }

            #ppress-admin-mailoptin .top img {
                margin-bottom: 38px;
            }

            #ppress-admin-mailoptin .top h1 {
                font-size: 26px;
                font-weight: 600;
                margin-bottom: 0;
                padding: 0;
            }

            #ppress-admin-mailoptin .top p {
                font-size: 17px;
                color: #777777;
                margin-top: .5em;
            }

            #ppress-admin-mailoptin p {
                font-size: 15px;
            }

            #ppress-admin-mailoptin .cont {
                display: inline-block;
                position: relative;
                width: 100%;
                padding: 5px;
                background-color: #ffffff;
                -webkit-box-shadow: 0px 2px 5px 0px rgb(0 0 0 / 5%);
                -moz-box-shadow: 0px 2px 5px 0px rgba(0, 0, 0, 0.05);
                box-shadow: 0px 2px 5px 0px rgb(0 0 0 / 5%);
                border-radius: 3px;
                box-sizing: border-box;
            }

            #ppress-admin-mailoptin .screenshot > * {
                vertical-align: middle;
            }

            #ppress-admin-mailoptin .screenshot .cont img {
                max-width: 100%;
                display: block;
            }

            #ppress-admin-mailoptin .screenshot ul {
                display: inline-block;
                margin: 0 0 0 30px;
                list-style-type: none;
                max-width: 100%;
            }

            #ppress-admin-mailoptin .screenshot li {
                margin: 16px 0;
                padding: 0 0 0 24px;
                font-size: 15px;
                color: #777777;
            }

            #ppress-admin-mailoptin .step {
                background-color: #F9F9F9;
                -webkit-box-shadow: 0px 2px 5px 0px rgb(0 0 0 / 5%);
                -moz-box-shadow: 0px 2px 5px 0px rgba(0, 0, 0, 0.05);
                box-shadow: 0px 2px 5px 0px rgb(0 0 0 / 5%);
                border: 1px solid #E5E5E5;
                margin: 0 0 25px 0;
            }

            #ppress-admin-mailoptin .step .num {
                display: inline-block;
                position: relative;
                width: 100px;
                height: 50px;
                text-align: center;
            }

            .ppress-admin-plugin-landing .loader {
                margin: 0 auto;
                position: relative;
                text-indent: -9999em;
                border-top: 4px solid #969696;
                border-right: 4px solid #969696;
                border-bottom: 4px solid #969696;
                border-left: 4px solid #404040;
                -webkit-transform: translateZ(0);
                -ms-transform: translateZ(0);
                transform: translateZ(0);
                -webkit-animation: load8 1.1s infinite linear;
                animation: load8 1.1s infinite linear;
                background-color: transparent;
            }

            .ppress-admin-plugin-landing .loader, .ppress-admin-plugin-landing .loader:after {
                display: block;
                border-radius: 50%;
                width: 50px;
                height: 50px
            }

            @-webkit-keyframes load8 {
                0% {
                    -webkit-transform: rotate(0deg);
                    transform: rotate(0deg)
                }

                100% {
                    -webkit-transform: rotate(360deg);
                    transform: rotate(360deg)
                }
            }

            @keyframes load8 {
                0% {
                    -webkit-transform: rotate(0deg);
                    transform: rotate(0deg)
                }

                100% {
                    -webkit-transform: rotate(360deg);
                    transform: rotate(360deg)
                }
            }

            #ppress-admin-mailoptin .step .loader {
                margin-top: -54px;
                transition: all .3s;
                opacity: 1;
            }

            #ppress-admin-mailoptin .step .hidden {
                opacity: 0;
                transition: all .3s;
            }

            #ppress-admin-mailoptin .step div {
                display: inline-block;
                width: calc(100% - 104px);
                background-color: #ffffff;
                padding: 30px;
                border-left: 1px solid #eeeeee;
            }

            #ppress-admin-mailoptin .step h2 {
                font-size: 24px;
                line-height: 22px;
                margin-top: 0;
                margin-bottom: 15px;
            }

            #ppress-admin-mailoptin .step p {
                font-size: 16px;
                color: #777777;
            }


            #ppress-admin-mailoptin .step .button {
                font-weight: 500;
                box-shadow: none;
                padding: 12px;
                min-width: 200px;
                height: auto;
                line-height: 13px;
                text-align: center;
                font-size: 15px;
                transition: all .3s;
            }

            #ppress-admin-mailoptin .grey {
                opacity: 0.5;
            }
        </style>
        <?php
        echo '<div id="ppress-admin-mailoptin" class="wrap ppress-admin-wrap ppress-admin-plugin-landing">';

        $this->output_section_heading();
        $this->output_section_screenshot();
        $this->output_section_step_install();
        $this->output_section_step_setup();

        echo '</div>';
    }

    /**
     * Generate and output heading section HTML.
     */
    protected function output_section_heading()
    {
        // Heading section.
        printf(
            '<section class="top">
				<img class="img-top" src="%1$s" alt="%2$s"/>
				<h1>%3$s</h1>
				<p>%4$s</p>
			</section>',
            esc_url(PPRESS_ASSETS_URL . '/images/profilepressxmailoptin.png'),
            esc_attr__('ProfilePress ♥ MailOptin', 'wp-user-avatar'),
            esc_html__('#1 Popup, Optin Forms & Marketing Automation Plugin', 'wp-user-avatar'),
            esc_html__('MailOptin lets you create popups and newsletter opt-in forms that integrates with Mailchimp, Aweber, Constant Contact, Active Campaign & more.', 'wp-user-avatar')
        );
    }

    /**
     * Generate and output screenshot section HTML.
     */
    protected function output_section_screenshot()
    {
        printf(
            '<section class="screenshot">
				<div class="cont">
					<img src="%1$s" alt="%2$s"/>
				</div>
				<ul>
					<li>%3$s</li>
					<li>%4$s</li>
					<li>%5$s</li>
					<li>%6$s</li>
					<li>%7$s</li>
				</ul>			
			</section>',
            'https://mailoptin.io/wp-content/uploads/2018/10/lead-generation-customizer-demo.jpg',
            esc_attr__('MailOptin screenshot', 'wp-user-avatar'),
            esc_html__('Automatically notify your subscribers every time you publish a new post.', 'wp-user-avatar'),
            esc_html__('Keep your subscribers engaged with daily, weekly and monthly email digest of published posts.', 'wp-user-avatar'),
            esc_html__('Different types of opt-in form including Popup, notification bar, inline, scroll box, slide ins, sidebar forms.', 'wp-user-avatar'),
            esc_html__('Page-level targeting and optin triggers to build hyper segmented email list.', 'wp-user-avatar'),
            esc_html__('Analytics with actionable reporting & insights to improve your lead-generation strategy.', 'wp-user-avatar')
        );
    }

    /**
     * Generate and output step 'Install' section HTML.
     */
    protected function output_section_step_install()
    {
        $step = $this->get_data_step_install();

        if (empty($step)) {
            return;
        }

        printf(
            '<section class="step step-install">
				<aside class="num">
					<img src="%1$s" alt="%2$s" />
					<i class="loader hidden"></i>
				</aside>
				<div>
					<h2>%3$s</h2>
					<p>%4$s</p>
					<button class="button %5$s" data-plugin="%6$s" data-action="%7$s">%8$s</button>
				</div>		
			</section>',
            esc_url(PPRESS_ASSETS_URL . '/images/' . $step['icon']),
            esc_attr__('Step 1', 'wp-user-avatar'),
            esc_html__('Install and Activate MailOptin', 'wp-user-avatar'),
            esc_html__('Install MailOptin from the WordPress.org plugin repository.', 'wp-user-avatar'),
            esc_attr($step['button_class']),
            esc_attr($step['plugin']),
            esc_attr($step['button_action']),
            esc_html($step['button_text'])
        );
    }

    /**
     * Generate and output step 'Setup' section HTML.
     */
    protected function output_section_step_setup()
    {
        $step = $this->get_data_step_setup();

        if (empty($step)) {
            return;
        }

        printf(
            '<section class="step step-setup %1$s">
				<aside class="num">
					<img src="%2$s" alt="%3$s" />
					<i class="loader hidden"></i>
				</aside>
				<div>
					<h2>%4$s</h2>
					<p>%5$s</p>
					<button class="button %6$s" data-url="%7$s">%8$s</button>
				</div>		
			</section>',
            esc_attr($step['section_class']),
            esc_url(PPRESS_ASSETS_URL . '/images/' . $step['icon']),
            esc_attr__('Step 2', 'wp-user-avatar'),
            esc_html__('Set Up MailOptin', 'wp-user-avatar'),
            esc_html__('Configure and create your first optin form.', 'wp-user-avatar'),
            esc_attr($step['button_class']),
            esc_url(admin_url($this->config['mailoptin_settings'])),
            esc_html($step['button_text'])
        );
    }

    /**
     * Step 'Install' data.
     */
    protected function get_data_step_install()
    {
        $step = array();

        $this->output_data['all_plugins']      = get_plugins();
        $this->output_data['plugin_installed'] = array_key_exists($this->config['lite_plugin'], $this->output_data['all_plugins']);
        $this->output_data['plugin_activated'] = false;
        $this->output_data['plugin_setup']     = false;

        if ( ! $this->output_data['plugin_installed']) {
            $step['icon']          = 'step-1.svg';
            $step['button_text']   = esc_html__('Install MailOptin', 'wp-user-avatar');
            $step['button_class']  = '';
            $step['button_action'] = 'install';
            $step['plugin']        = $this->config['lite_download_url'];
        } else {
            $this->output_data['plugin_activated'] = $this->is_activated();
            $this->output_data['plugin_setup']     = $this->is_configured();
            $step['icon']                          = $this->output_data['plugin_activated'] ? 'step-complete.svg' : 'step-1.svg';
            $step['button_text']                   = $this->output_data['plugin_activated'] ? esc_html__('MailOptin Installed & Activated', 'wp-user-avatar') : esc_html__('Activate MailOptin', 'wp-user-avatar');
            $step['button_class']                  = $this->output_data['plugin_activated'] ? 'grey disabled' : '';
            $step['button_action']                 = $this->output_data['plugin_activated'] ? '' : 'activate';
            $step['plugin']                        = $this->config['lite_plugin'];
        }

        return $step;
    }

    /**
     * Step 'Setup' data.
     */
    protected function get_data_step_setup()
    {
        $step = array();

        $step['icon']          = 'step-2.svg';
        $step['section_class'] = $this->output_data['plugin_activated'] ? '' : 'grey';
        $step['button_text']   = esc_html__('Start Setup', 'wp-user-avatar');
        $step['button_class']  = 'grey disabled';

        if ($this->output_data['plugin_setup']) {
            $step['icon']          = 'step-complete.svg';
            $step['section_class'] = '';
            $step['button_text']   = esc_html__('Go to MailOptin settings', 'wp-user-avatar');
        } else {
            $step['button_class'] = $this->output_data['plugin_activated'] ? '' : 'grey disabled';
        }

        return $step;
    }

    /**
     * Ajax endpoint. Check plugin setup status.
     * Used to properly init step 'Setup' section after completing step 'Install'.
     */
    public function ajax_check_plugin_status()
    {
        // Security checks.
        if (
            ! check_ajax_referer('ppress-admin-nonce', 'nonce', false) ||
            ! current_user_can('activate_plugins')
        ) {
            wp_send_json_error(
                array(
                    'error' => esc_html__('You do not have permission.', 'wp-user-avatar'),
                )
            );
        }

        $result = array();

        if ( ! $this->is_activated()) {
            wp_send_json_error(
                array(
                    'error' => esc_html__('Plugin unavailable.', 'wp-user-avatar'),
                )
            );
        }

        $result['setup_status'] = (int)$this->is_configured();

        wp_send_json_success($result);
    }

    /**
     * Whether MailOptin plugin configured or not.
     */
    protected function is_configured()
    {
        if ( ! $this->is_activated()) return false;

        $optins = OptinCampaignsRepository::get_optin_campaign_ids();

        $emails = EmailCampaignRepository::get_email_campaign_ids();

        return ! empty($optins) || ! empty($emails);
    }

    /**
     * Whether MailOptin plugin active or not.
     */
    protected function is_activated()
    {
        return class_exists('MailOptin\Core\Base');
    }

    public function redirect_to_mailoptin_settings()
    {
        if ($this->is_configured()) {
            wp_safe_redirect(admin_url($this->config['mailoptin_settings']));
            exit;
        }
    }

    /**
     * @return self
     */
    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}