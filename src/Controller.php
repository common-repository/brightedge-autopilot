<?php
namespace BrightEdge\Wordpress;

include_once('constants.php');

class BEIXFController {
    public $options = [];
    public $loaded = false;
    public $be_ixf;
    protected $ixf_config;
    public $ixf_data = null;
    public $ixf_body_open_data = null;
    public $ixf_close_data = null;

    public function __construct() {
        $this->options = $this->getPluginOptions();

        if ($this->isDisabled()) {
            $this->ixf_data = '<!-- be_ixf plugin set to disabled in settings -->';
            $this->ixf_body_open_data = '<!-- be_ixf plugin set to disabled in settings -->';
            return;
        }

        $this->setIXFConfig();
        $this->createIXFClient();
        $this->load();
    }

    public function load(){
        $this->ixfAddHead();
        if (!$this->isValid()) {
            return;
        }

        $this->ixfGetBodyOpenBlock();
        $this->ixfGetLinkBlock();
        $this->ixfGetCloseBlock();

        $this->loaded = true;
    }

    public static function getPluginOptions(){
        return wp_parse_args(
            get_option('be_ixf'),
            [
                BEIXFConstants::STATUS => BEIXFConstants::STATUS_DISABLED,
                BEIXFConstants::STRATEGY => BEIXFConstants::PRE_CONTENT,
                BEIXFConstants::ACCOUNT_ID => '',
                BEIXFConstants::EXCLUDE_HOMEPAGE => BEIXFConstants::EXCLUDE_OPTION,
                BEIXFConstants::WHITE_LIST => 'ixf',
                BEIXFConstants::API_ENDPOINT => '',
                BEIXFConstants::CANONICAL_HOST => '',
                BEIXFConstants::PROTOCOL => BEIXFConstants::PROTOCOL_HTTPS,
            ]
        );
    }

    public static function getPluginMultiOptions(){
        return [
            BEIXFConstants::STATUS => array(BEIXFConstants::STATUS_ENABLED, BEIXFConstants::STATUS_DISABLED),
            BEIXFConstants::STRATEGY => array(
                BEIXFConstants::PRE_CONTENT,
                BEIXFConstants::POST_CONTENT,
                BEIXFConstants::ABOVE_FOOTER,
                BEIXFConstants::FIRST_ELEMENT_IN_FOOTER,
                BEIXFConstants::WIDGET,
                BEIXFConstants::SHORTCODE
            ),
            BEIXFConstants::EXCLUDE_HOMEPAGE_OPTIONS => array(BEIXFConstants::EXCLUDE_OPTION, BEIXFConstants::INCLUDE_OPTION),
            BEIXFConstants::PROTOCOL => array(BEIXFConstants::PROTOCOL_HTTPS, BEIXFConstants::PROTOCOL_HTTP),
        ];
    }

    protected function setIXFConfig(){
        $this->ixf_config = array(
            BEIXFClient::$CAPSULE_MODE_CONFIG => BEIXFClient::$REMOTE_PROD_CAPSULE_MODE,
            BEIXFClient::$ACCOUNT_ID_CONFIG => $this->options[BEIXFConstants::ACCOUNT_ID],
            BEIXFClient::$WHITELIST_PARAMETER_LIST_CONFIG => $this->options[BEIXFConstants::WHITE_LIST],
        );

        // optional values:
        if (!empty( $this->options[BEIXFConstants::API_ENDPOINT])) {
            $this->ixf_config[BEIXFClient::$API_ENDPOINT_CONFIG] = $this->options[BEIXFConstants::API_ENDPOINT];
        }
        if (!empty( $this->options[BEIXFConstants::CANONICAL_HOST])) {
            $this->ixf_config[BEIXFClient::$CANONICAL_HOST_CONFIG] = $this->options[BEIXFConstants::CANONICAL_HOST];
        }
        if (!empty( $this->options[BEIXFConstants::PROTOCOL])) {
            $this->ixf_config[BEIXFClient::$CANONICAL_PROTOCOL_CONFIG] = strtolower($this->options[BEIXFConstants::PROTOCOL]);
        }
    }

    protected function createIXFClient(){
        if ($this->ixf_config) {
            $this->be_ixf = new BEIXFClient($this->ixf_config);
        }
    }

    public function ixfAddHead(){
        add_action('wp_head', function() {
            echo BEIXFConstants::WP_VERSION;
            echo html_entity_decode(wp_unslash($this->be_ixf->getHeadOpen()));
        },1);
    }

    public function ixfGetBodyOpenBlock(){
        if (is_null($this->be_ixf )) {
            return;
        } else {
            $this->ixf_body_open_data = $this->be_ixf->getBodyOpen();
        }
    }

    public function ixfGetLinkBlock(){
        // Output buffering HTML
        if (is_null($this->be_ixf )) {
            return;
        } else {
            $this->ixf_data = $this->be_ixf->getBodyString(BEIXFConstants::BODY_1);
        }
    }

    public function ixfGetCloseBlock(){
        if(is_null($this->be_ixf)){
            return;
        } else {
            $this->ixf_close_data = $this->be_ixf->close();
        }
    }

    protected function isDisabled(){
        return ($this->options[BEIXFConstants::STATUS] == BEIXFConstants::STATUS_DISABLED? true : false);
    }


     /**
     * Check to see if base requirements are set: Account ID & SDK initialized
     *
     * @return boolean
     */
    protected function isValid(){
        if ($this->options[BEIXFConstants::ACCOUNT_ID] == '' | is_null($this->be_ixf)) {
            return false;
        } else {
            return true;
        }
    }
}
