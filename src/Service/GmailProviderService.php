<?php

namespace Drupal\oauth2_gmail_client\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;
use Drupal\Core\State\StateInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;
use Drupal\oauth2_client\Plugin\Oauth2Client\StateTokenStorage;

/**
 * Helper to generate a new Google Gmail provider
 */
class GmailProviderService {

    use StateTokenStorage;

    /**
     * Config factory
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface
     */
    protected $configFactory;

    /**
     * The request stack
     *
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    /**
     * Injected Drupal state
     *
     * @var \Drupal\Core\State\StateInterface;
     */
    protected $state;

    /**
     * The constructor
     *
     * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
     *   The config factory
     * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
     *   The request stack
     * @param \Drupal\Core\State\StateInterface
     *   The Drupal state
     */
    public function __construct(ConfigFactoryInterface $config_factory, RequestStack $request_stack, StateInterface $state) {

        $this->configFactory = $config_factory;
        $this->requestStack  = $request_stack;
        $this->state         = $state;
    }

    /**
     * Create a new Google provider for SMTP Auth
     *
     * @return object
     *   The Google provider
     */
    public function getProvider() {

        $config      = $this->configFactory->get('oauth2_client.oauth2_client.' . $this->getId());
        $credentials = $this->state->get($config->get('uuid'));

        $url = Url::fromRoute('oauth2_client.code',
                              ['plugin'   => $this->getId()], 
                              ['absolute' => TRUE])->toString(TRUE)->getGeneratedUrl();

        $params = [
            'clientId'     => array_key_exists('client_id',     $credentials) ? $credentials['client_id']     : '',
            'clientSecret' => array_key_exists('client_secret', $credentials) ? $credentials['client_secret'] : '',
            'redirectUri'  => $url,
            'accessType'   => 'offline',
            'prompt'       => 'consent',
        ];

        return new Google($params);
    }

    /**
     * Get OAuth options
     *
     * @return array
     *   PHPMailer auth options
     */
    public function getAuthOptions() {

        $config      = $this->configFactory->get('oauth2_client.oauth2_client.' . $this->getId());
        $credentials = $this->state->get($config->get('uuid'));
        $accessToken = $this->retrieveAccessToken();

        return [
            'provider'     => $this->getProvider(),
            'userName'     => $this->configFactory->get('system.site')->get('mail'),
            'clientId'     => (array_key_exists('client_id',     $credentials) ? $credentials['client_id']     : ''),
            'clientSecret' => (array_key_exists('client_secret', $credentials) ? $credentials['client_secret'] : ''),
            'refreshToken' => ($accessToken ? $accessToken->getRefreshToken() : ''),
        ];
    }

    protected function getId() {

        return 'phpmailer_plugin';
    }
}
