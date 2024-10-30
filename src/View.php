<?php

namespace BrightEdge\Wordpress;
include_once('constants.php');

class BEIXFView {
    public $controller;

    public function __construct($instance){
        $this->controller = $instance;
        $options = $instance->options;

        if ($this->controller->loaded) {
            // body open
            // insert content just after the body tag
            if(isset($this->controller->ixf_body_open_data)
            && $this->checkBodyStr($this->controller->ixf_body_open_data)){
                $this->ixfAddBodyOpenBlock();
            }

            // Display Comments in case of Empty body content for shortcode, currently it displays '[autopilot_shortcode]'.
            if ((isset($this->controller->ixf_data)) && ($options[BEIXFConstants::STRATEGY] == BEIXFConstants::SHORTCODE)) {
                $this->ixfAddBody1BlockShortcode();
            }

            if (isset($this->controller->ixf_data)
            && $this->checkBodyStr($this->controller->ixf_data)){
                // body 1
                if ($options[BEIXFConstants::STRATEGY] == BEIXFConstants::ABOVE_FOOTER) {
                    $this->ixfAddBody1BlockAboveFooter();
                } else if($options[BEIXFConstants::STRATEGY] == BEIXFConstants::FIRST_ELEMENT_IN_FOOTER) {
                    $this->ixfAddBody1BlockFirstElementInFooter();
                } else {
                    $this->ixfAddBody1Block();
                }
            }

            if(isset($this->controller->ixf_close_data)){
                $this->ixfAddCloseBlock();
            }
        }
    }

    function checkBodyStr($bodyStr){
        return strlen(trim(preg_replace('/<!--(.|\s)*?-->/', '', $bodyStr))) !== 0;
    }

    function buildFooterScript($data, $strategy){
        $insertPosition = "";
        if ($strategy == BEIXFConstants::ABOVE_FOOTER) {
            $insertPosition = 'beforebegin';
        } else if ($strategy == BEIXFConstants::FIRST_ELEMENT_IN_FOOTER) {
            $insertPosition = 'afterbegin';
        }

        $insertData = str_replace("\n", "", $data);
        $insertData = str_replace("\t", "", $insertData);
        // footer tag -> <div id='footer'> -> <div class='footer'>
        return "<script>
var footer = null;

if(document.getElementsByTagName('footer')) {
    var footersTag = document.getElementsByTagName('footer')
    footer = footersTag[footersTag.length - 1];
}

if (!footer){
    footer = document.getElementById('footer');
}

if(!footer) {
    var footersClass = document.getElementsByClassName('footer');
    footer = footersClass[footersClass.length - 1];
}

if (footer){
    footer.insertAdjacentHTML('" . $insertPosition . "', '" . $insertData . "');
}
</script>";
    }

    public function ixfAddBody1BlockAboveFooter(){
        $options = $this->controller->options;
        if (is_home() && $options[BEIXFConstants::EXCLUDE_HOMEPAGE] == BEIXFConstants::EXCLUDE_OPTION) {
            return;
        }

        if(is_singular()){
            add_filter('wp_footer', array($this, 'ixfRenderBody1BlockAboveFooter'));
        }
    }

    public function ixfRenderBody1BlockAboveFooter(){
        if ($this->controller->ixf_data){
            echo html_entity_decode(wp_unslash($this->buildFooterScript($this->controller->ixf_data, BEIXFConstants::ABOVE_FOOTER)));
        }

        remove_filter('wp_footer', array($this, 'ixfRenderBody1BlockAboveFooter'));
    }

    public function ixfAddBody1BlockFirstElementInFooter(){
        $options = $this->controller->options;
        if (is_home() && $options[BEIXFConstants::EXCLUDE_HOMEPAGE] == BEIXFConstants::EXCLUDE_OPTION) {
            return;
        }

        if(is_singular()){
            add_filter('wp_footer', array($this, 'ixfRenderBody1BlockFirstElementInFooter'));
        }
    }

    public function ixfRenderBody1BlockFirstElementInFooter(){
        if ($this->controller->ixf_data){
            echo html_entity_decode(wp_unslash($this->buildFooterScript($this->controller->ixf_data, BEIXFConstants::FIRST_ELEMENT_IN_FOOTER)));
        }

        remove_filter('wp_footer', array($this, 'ixfRenderBody1BlockFirstElementInFooter'));
    }

    public function ixfAddBodyOpenBlock(){
        $options = $this->controller->options;
        if (is_home() && $options[BEIXFConstants::EXCLUDE_HOMEPAGE] == BEIXFConstants::EXCLUDE_OPTION){
            return;
        }
        if(is_singular()){
            add_action('wp_footer',function() {
                echo html_entity_decode(wp_unslash($this->controller->ixf_body_open_data));
            }, 99);
        }
    }

    public function ixfAddBody1Block(){
        $options = $this->controller->options;
        if (is_home() && $options[BEIXFConstants::EXCLUDE_HOMEPAGE] == BEIXFConstants::EXCLUDE_OPTION) {
            return;
        }

        if(in_array($options[BEIXFConstants::STRATEGY], array(BEIXFConstants::PRE_CONTENT, BEIXFConstants::POST_CONTENT))
            && is_singular()){
            add_filter('the_content', array($this, 'ixfRenderBody1Block'));
        }
    }

    public function ixfRenderBody1Block($content = ''){
        ob_start();

        if ($this->controller->options[BEIXFConstants::STRATEGY] == BEIXFConstants::POST_CONTENT){
            echo $content;
        }

        if (isset($this->controller->ixf_data)){
            echo html_entity_decode(wp_unslash($this->controller->ixf_data));
        }

        if ($this->controller->options[BEIXFConstants::STRATEGY] == BEIXFConstants::PRE_CONTENT){
            echo $content;
        }

        remove_filter('the_content',  array($this, 'ixfRenderBody1Block'));

        return ob_get_clean();
    }

    public function ixfRenderWidgetBlock(){
        ob_start();

        if (isset($this->controller->ixf_data)){
            echo html_entity_decode(wp_unslash($this->controller->ixf_data));
        }

        return ob_get_clean();
    }

    public function ixfAddBody1BlockShortcode(){
        # shortcode tag is added to every page, so to avoid showing a tag [autopilot_shortcode] on every page, we should display actual link block content or comment section. So we removed the conditions to check Home page and is_singular
        add_shortcode('autopilot_shortcode', array($this, 'ixfRenderBody1BlockShortcode'));
    }

    public function ixfRenderBody1BlockShortcode(){
        ob_start();
        if (isset($this->controller->ixf_data)){
            echo html_entity_decode(wp_unslash($this->controller->ixf_data));
        }
        return ob_get_clean();
    }

    public function ixfAddCloseBlock(){
        add_action('wp_footer',function() {
            echo html_entity_decode(wp_unslash($this->controller->ixf_close_data));
        }, 100);
    }
}
