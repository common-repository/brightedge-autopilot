<?php
namespace BrightEdge\Wordpress;

include_once('constants.php');

class BEIXFSettings {
    public static function register_settings(){
        register_setting(
            'be_ixf',
            'be_ixf',
            [
                __NAMESPACE__ . __CLASS__,
                'validate_settings',
            ]
        );

        add_settings_section(
            'be_ixf_general',
            '',
            [
                __CLASS__,
                'add_ixf_settings',
            ],
            'be_ixf'
        );

        add_settings_section(
            'be_ixf_lem',
            'Link Equity Manager',
            [
                __CLASS__,
                'add_lem_settings',
            ],
            'be_ixf'
        );
    }

    public static function getVal($var, $default="") {
        return isset($var) ? $var : $default;
    }

    public static function validate_settings($data){
        return [BEIXFConstants::STATUS => BEIXFSettings::getVal($data[BEIXFConstants::STATUS], BEIXFConstants::STATUS_DISABLED),
            BEIXFConstants::ACCOUNT_ID => BEIXFSettings::getVal($data[BEIXFConstants::ACCOUNT_ID]),
            BEIXFConstants::API_ENDPOINT => BEIXFSettings::getVal($data[BEIXFConstants::API_ENDPOINT]),
            BEIXFConstants::CANONICAL_HOST => BEIXFSettings::getVal($data[BEIXFConstants::CANONICAL_HOST]),
            BEIXFConstants::PROTOCOL => BEIXFSettings::getVal($data[BEIXFConstants::PROTOCOL], BEIXFConstants::PROTOCOL_HTTPS),
            BEIXFConstants::WHITE_LIST => BEIXFSettings::getVal($data[BEIXFConstants::WHITE_LIST], "ixf"),
            BEIXFConstants::EXCLUDE_HOMEPAGE => BEIXFSettings::getVal($data[BEIXFConstants::EXCLUDE_HOMEPAGE], BEIXFConstants::EXCLUDE_OPTION),
            BEIXFConstants::STRATEGY => BEIXFSettings::getVal($data[BEIXFConstants::STRATEGY], BEIXFConstants::PRE_CONTENT),
        ];
    }

    public static function add_settings_page(){
        add_options_page(
            'BrightEdge Autopilot',
            'BrightEdge Autopilot',
            'manage_options',
            'be_ixf',
            [
                __CLASS__,
                'settings_page',
            ]
        );
    }

    public static function settings_page(){
        ?>
        <div class="wrap">
            <h2>
                <?php _e("BrightEdge Autopilot Wordpress Plugin Settings", "be_ixf_php_wp"); ?>
            </h2>

            <form method="post" action="options.php">
                <table class="form-table">
                    <?php settings_fields('be_ixf') ?>
                    <?php do_settings_sections('be_ixf'); ?>
                </table>
                <?php submit_button() ?>
            </form>
        </div>
        <?php
    }

    public static function add_ixf_settings(){
        $options = BEIXFController::getPluginOptions();
        $options_choices = BEIXFController::getPluginMultiOptions();
        ?>
        <style>
            .required:after {
                content: " *";
                color: red;
            }
        </style>

        <tr valign="top">
            <th scope="row">
                <?php _e("Status", "be_ixf_php_wp"); ?>
            </th>
            <td>
                <fieldset>
                    <label for="disabled">
                        <?php
                        foreach ($options_choices['disabled'] as $state) {
                            ?> <input type="radio"
                                      name="be_ixf[disabled]"
                                      value="<?php echo esc_attr($state); ?>" <?php checked($state == $options['disabled']); ?>/><?php echo $state; ?>
                        <?php } ?>
                </fieldset>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="required">
                <?php _e("Account ID", "be_ixf_php_wp"); ?>
            </th>
            <td>
                <fieldset>
                    <label for="account_id">
                        <input type="text"
                               name="be_ixf[account_id]"
                               id="account_id"
                               value="<?php echo esc_attr($options['account_id']); ?>"
                               size="64"
                               class="regular-text code"/>
                    </label>
                </fieldset>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <?php _e("API Endpoint (Optional)", "be_ixf_php_wp"); ?>
            </th>
            <td>
                <fieldset>
                    <label for="api_endpoint">
                        <input type="text"
                               name="be_ixf[api_endpoint]"
                               id="api_endpoint"
                               value="<?php echo esc_attr($options['api_endpoint']); ?>"
                               size="64"
                               class="regular-text code"/>
                    </label>
                </fieldset>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <?php _e("Canonical Host (Optional)", "be_ixf_php_wp"); ?>
            </th>
            <td>
                <fieldset>
                    <label for="canonical_host">
                        <input type="text"
                               name="be_ixf[canonical_host]"
                               id="canonical_host"
                               value="<?php echo esc_attr($options['canonical_host']); ?>"
                               size="64"
                               class="regular-text code"/>
                    </label>
                </fieldset>
                <p class="description">
                    Example: <?php echo parse_url(site_url())['host']; ?>
                </p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <?php _e("Protocol", "be_ixf_php_wp"); ?>
            </th>
            <td>
                <fieldset>
                    <label for="protocol">
                        <?php
                        foreach ($options_choices['protocol'] as $state) {
                            ?> <input type="radio"
                                      name="be_ixf[protocol]"
                                      value="<?php echo esc_attr($state); ?>" <?php checked($state == $options['protocol']); ?>/><?php echo $state; ?>
                        <?php } ?><br>
                </fieldset>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <?php _e("URL Parameter Whitelist", "be_ixf_php_wp"); ?>
            </th>
            <td>
                <fieldset>
                    <label for="whitelist">
                        <input type="text"
                               name="be_ixf[whitelist]"
                               id="whitelist"
                               value="<?php echo esc_attr($options['whitelist']); ?>"
                               size="64"
                               class="regular-text code"/><br>
                        <?php _e("Default: <code>ixf</code>", "be_ixf_php_wp"); ?>
                    </label>
                    <p class="description">
                        <?php _e("By default, all URL parameters are ignored. If you have URL parameters that add value to page content.  Add them to this config value, separated by the pipe character (|).", "be_ixf_php_wp"); ?>
                    </p>
                </fieldset>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <?php _e("Homepage", "be_ixf_php_wp"); ?>
            </th>
            <td>
                <fieldset>
                    <label for="exclude_homepage">
                        <?php
                        foreach ($options_choices['inclusions'] as $state) {
                            ?> <input type="radio"
                                      name="be_ixf[exclude_homepage]"
                                      value="<?php echo esc_attr($state); ?>" <?php checked($state == $options['exclude_homepage']); ?>/><?php echo $state; ?>
                        <?php } ?>
                </fieldset>
            </td>
        </tr>
        <?php
    }

    public static function add_lem_settings()
    {
        $options = BEIXFController::getPluginOptions();
        $options_choices = BEIXFController::getPluginMultiOptions();
        ?>
        <style>
            .required:after {
                content: " *";
                color: red;
            }
        </style>
        <tr valign="top">
            <th scope="row">
                <?php _e("Integration Strategy", "be_ixf_php_wp"); ?>
            </th>
            <td>
                <fieldset>
                    <label for="strategy">
                        <select name='be_ixf[strategy]' id='strategy'>
                            <?php
                            foreach ($options_choices['strategy'] as $strategy) {
                                ?>
                                <option value='<?php echo esc_attr($strategy); ?>' <?php selected($options['strategy'], $strategy); ?>><?php echo $strategy; ?></option>
                            <?php } ?>
                        </select>
                    </label>
                </fieldset>
                <p class="description">
                    Please select one strategy:</p>
                <ul>
                    <li>
                        <strong>Pre-Content:</strong>
                        Show links before the page content.
                    </li>
                    <li>
                        <strong>Post-Content:</strong>
                        Show links after the page content.
                    </li>
                    <li>
                        <strong>Widget</strong>:
                        Go to Appearance > Widgets, then assign the widget "Link Equity Block" to
                        an active theme sidebar or to the dedicated "BrightEdge Link Equity Container".
                        You <strong>must</strong> add a function call to dynamic_sidebar in your template to enable the
                        dedicated container. For example:<br/>
                        <code>dynamic_sidebar('be-foundations-sidebar')</code>
                    </li>
                    <li>
                        <strong>Shortcode</strong>: Simply add
                        <code>[autopilot_shortcode]</code>
                        in content or
                        <code>echo do_shortcode("[autopilot_shortcode]");</code>
                        to your template to render the link block.
                    </li>
                </ul>
            </td>
        </tr>
        <?php
    }
}
