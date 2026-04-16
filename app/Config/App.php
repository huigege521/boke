<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Application Configuration
 */
class App extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Base Site URL
     * --------------------------------------------------------------------------
     *
     * URL to your CodeIgniter root. Typically this will be your base URL,
     * WITH a trailing slash:
     *
     *    http://example.com/
     *
     * If this is not set then CodeIgniter will try to guess the protocol, domain
     * and path to your installation.
     *
     * @var string
     */
    public string $baseURL = 'http://testboke.com/';//'http://localhost:8080/';

    /**
     * Allowed Hostnames in the Site URL other than the hostname in the baseURL.
     * If you want to accept multiple Hostnames, set this.
     *
     * E.g.,
     * When your site URL ($baseURL) is 'http://example.com/', and your site
     * also accepts 'http://media.example.com/' and 'http://accounts.example.com/':
     *     ['media.example.com', 'accounts.example.com']
     *
     * @var list<string>
     */
    public array $allowedHostnames = [];

    /**
     * --------------------------------------------------------------------------
     * Force Global Secure Requests
     * --------------------------------------------------------------------------
     *
     * If true, this will force every request made to this application to be
     * made via a secure connection (HTTPS). If the incoming request is not
     * secure, the user will be redirected to a secure version of the page.
     *
     * @var bool
     */
    public bool $forceGlobalSecureRequests = false;

    /**
     * --------------------------------------------------------------------------
     * Index File
     * --------------------------------------------------------------------------
     *
     * Typically this will be your index.php file, unless you've renamed it to
     * something else. If you are using mod_rewrite to remove the page set this
     * variable so that it is blank.
     *
     * @var string
     */
    public string $indexPage = 'index.php';

    /**
     * --------------------------------------------------------------------------
     * URI PROTOCOL
     * --------------------------------------------------------------------------
     *
     * This item determines which server global should be used to retrieve the
     * URI string.  The default setting of 'REQUEST_URI' works for most servers.
     * If your links do not seem to work, try one of the other delicious flavors:
     *
     * 'REQUEST_URI'    Uses $_SERVER['REQUEST_URI']
     * 'QUERY_STRING'   Uses $_SERVER['QUERY_STRING']
     * 'PATH_INFO'      Uses $_SERVER['PATH_INFO']
     *
     * @var string
     */
    public string $uriProtocol = 'REQUEST_URI';

    /**
     * --------------------------------------------------------------------------
     * Default Language
     * --------------------------------------------------------------------------
     *
     * This determines which set of language files should be used.
     * Make sure there is an available translation if you intend to use something
     * other than 'en'.
     *
     * @var string
     */
    public string $defaultLocale = 'zh-CN';

    /**
     * --------------------------------------------------------------------------
     * Negotiate Locale
     * --------------------------------------------------------------------------
     *
     * If true, the current Request object will automatically determine the
     * language to use based on the value of the Accept-Language header.
     *
     * @var bool
     */
    public bool $negotiateLocale = false;

    /**
     * --------------------------------------------------------------------------
     * Supported Locales
     * --------------------------------------------------------------------------
     *
     * If $negotiateLocale is true, this array lists the locales supported
     * by the application in descending order of priority.
     *
     * @var array<string>
     */
    public array $supportedLocales = ['en', 'zh-CN'];

    /**
     * --------------------------------------------------------------------------
     * Application Timezone
     * --------------------------------------------------------------------------
     *
     * @var string
     */
    public string $appTimezone = 'Asia/Shanghai';

    /**
     * --------------------------------------------------------------------------
     * Encryption Key
     * --------------------------------------------------------------------------
     *
     * If you use the Encryption class, you must set an encryption key.
     *
     * @var string
     */
    public string $encryptionKey = '';

    /**
     * --------------------------------------------------------------------------
     * CSRF Token Name
     * --------------------------------------------------------------------------
     *
     * Token name for Cross Site Request Forgery protection.
     *
     * @var string
     */
    public string $CSRFTokenName = 'csrf_test_name';

    /**
     * --------------------------------------------------------------------------
     * CSRF Header Name
     * --------------------------------------------------------------------------
     *
     * Header name for Cross Site Request Forgery protection.
     *
     * @var string
     */
    public string $CSRFHeaderName = 'X-CSRF-TOKEN';

    /**
     * --------------------------------------------------------------------------
     * CSRF Cookie Name
     * --------------------------------------------------------------------------
     *
     * Cookie name for Cross Site Request Forgery protection.
     *
     * @var string
     */
    public string $CSRFCookieName = 'csrf_cookie_name';

    /**
     * --------------------------------------------------------------------------
     * CSRF Expire Time
     * --------------------------------------------------------------------------
     *
     * How long (in seconds) the CSRF token should last.
     *
     * @var int
     */
    public int $CSRFExpire = 7200;

    /**
     * --------------------------------------------------------------------------
     * CSRF Regenerate
     * --------------------------------------------------------------------------
     *
     * Regenerate token on every submission.
     *
     * @var bool
     */
    public bool $CSRFRegenerate = true;

    /**
     * --------------------------------------------------------------------------
     * CSRF Redirect
     * --------------------------------------------------------------------------
     *
     * Redirect to previous page with error on CSRF failure.
     *
     * @var bool
     */
    public bool $CSRFRedirect = true;

    /**
     * --------------------------------------------------------------------------
     * Session Variables
     * --------------------------------------------------------------------------
     *
     * 'sessionDriver'    = The driver to use for sessions.
     * 'sessionCookieName' = The name of the session cookie.
     * 'sessionExpiration' = The number of SECONDS you want the session to last.
     * by default sessions last 7200 seconds (two hours). Set to zero for no expiration.
     * 'sessionSavePath'   = The location to save sessions to, driver dependent.
     * 'sessionMatchIP'    = Whether to match the user's IP address when reading the session data.
     * 'sessionTimeToUpdate' = How many seconds between CI regenerating the session ID.
     * 'sessionRegenerateDestroy' = Whether to destroy session data associated with the old session ID
     * when auto-regenerating the session ID.
     *
     * @var array
     */
    public array $session = [
        'driver' => 'Files',
        'cookieName' => 'ci_session',
        'expiration' => 7200,
        'savePath' => WRITEPATH . 'session',
        'matchIP' => false,
        'timeToUpdate' => 300,
        'regenerateDestroy' => false,
    ];

    /**
     * --------------------------------------------------------------------------
     * Cookie Settings
     * --------------------------------------------------------------------------
     *
     * 'cookiePrefix'   = Set a cookie name prefix if you need to avoid collisions
     * 'cookieDomain'   = Set to .your-domain.com for site-wide cookies
     * 'cookiePath'     = Typically will be a forward slash
     * 'cookieSecure'   = Cookie will only be set if a secure HTTPS connection exists.
     * 'cookieHTTPOnly' = Cookie will only be accessible via HTTP(S) requests and not by JavaScript.
     * 'cookieSameSite' = SameSite cookie setting. Allowed values are: 'None', 'Lax', 'Strict'
     *
     * @var array
     */
    public array $cookie = [
        'prefix' => '',
        'domain' => '',
        'path' => '/',
        'secure' => false,
        'HTTPOnly' => false,
        'SameSite' => 'Lax',
    ];

    /**
     * --------------------------------------------------------------------------
     * Standardize newlines
     * --------------------------------------------------------------------------
     *
     * Determines whether to standardize newline characters in input data.
     *
     * @var bool
     */
    public bool $standardizeNewlines = false;

    /**
     * --------------------------------------------------------------------------
     * Global XSS Filtering
     * --------------------------------------------------------------------------
     *
     * Determines whether the XSS filter is always active when GET, POST or
     * COOKIE data is encountered
     *
     * @var bool
     */
    public bool $globalXSSFiltering = false;

    /**
     * --------------------------------------------------------------------------
     * Output Compression
     * --------------------------------------------------------------------------
     *
     * Enables Gzip output compression for faster page loads.  When enabled,
     * the output class will test whether your server supports Gzip.  Even if
     * it does, however, not all browsers support compression, so enable only
     * if you are reasonably sure your visitors can handle it.
     *
     * @var int
     */
    public int $compressionLevel = 0;

    /**
     * --------------------------------------------------------------------------
     * Master Time Reference
     * --------------------------------------------------------------------------
     *
     * Options are 'local' or any PHP supported timezone.
     * This preference tells the system whether to use your server's local time
     * as the master 'now' reference, or convert it to the configured one.
     * See the 'date' helper for date usage examples.
     *
     * @var string
     */
    public string $masterTimeReference = 'local';

    /**
     * --------------------------------------------------------------------------
     * Reverse Proxy IPs
     * --------------------------------------------------------------------------
     *
     * If your server is behind a reverse proxy, you must whitelist the proxy IP
     * addresses from which CodeIgniter should trust headers such as
     * HTTP_X_FORWARDED_FOR and HTTP_CLIENT_IP in order to properly identify
     * the visitor's IP address.
     *
     * @var array
     */
    public array $proxyIPs = [];

    /**
     * --------------------------------------------------------------------------
     * Content Security Policy
     * --------------------------------------------------------------------------
     *
     * Enables the Content Security Policy header. This helps protect against
     * XSS attacks. You can set this to true or false.
     *
     * @var bool
     */
    public bool $CSPEnabled = false;
}
