<?php

namespace BrightEdge\Wordpress;

class BEIXFConstants{
    // wordpress plugin version
    const WP_VERSION = '<meta name="be:wp" content="1.1.16">';
    // account id
    const ACCOUNT_ID = 'account_id';

    // status
    const STATUS = 'disabled';
    const STATUS_ENABLED = 'Enabled';
    const STATUS_DISABLED = 'Disabled';
    const BODY_1 = 'body_1';

    // exclude homepage
    const EXCLUDE_HOMEPAGE = 'exclude_homepage';
    const EXCLUDE_HOMEPAGE_OPTIONS = 'inclusions';
    const EXCLUDE_OPTION = 'Exclude';
    const INCLUDE_OPTION = 'Include';

    // strategy
    const STRATEGY = 'strategy';
    const PRE_CONTENT = 'Pre-Content';
    const POST_CONTENT = 'Post-Content';
    const WIDGET = 'Widget';
    const SHORTCODE = 'Shortcode';
    const ABOVE_FOOTER = 'Above Footer';
    const FIRST_ELEMENT_IN_FOOTER = 'First Element in Footer';

    // protocol
    const PROTOCOL = 'protocol';
    const PROTOCOL_HTTP = 'HTTP';
    const PROTOCOL_HTTPS = 'HTTPS';

    // whitelist
    const WHITE_LIST = 'whitelist';

    // API endpoint
    const API_ENDPOINT = 'api_endpoint';

    // canonical host
    const CANONICAL_HOST = 'canonical_host';
}
