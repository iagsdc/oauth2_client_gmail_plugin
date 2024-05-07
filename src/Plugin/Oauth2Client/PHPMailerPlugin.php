<?php

declare(strict_types = 1);

namespace Drupal\oauth2_gmail_client\Plugin\Oauth2Client;

use Drupal\oauth2_client\Plugin\Oauth2Client\Oauth2ClientPluginBase;
use Drupal\oauth2_client\Plugin\Oauth2Client\StateTokenStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use League\OAuth2\Client\Provider\AbstractProvider;

use Drupal\oauth2_client\Plugin\Oauth2Client\Oauth2ClientPluginRedirectInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\oauth2_client\OwnerCredentials;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\oauth2_client\Exception\AuthCodeRedirect;

/**
 * PHPMailerPlugin
 *
 * @Oauth2Client(
 *   id = "phpmailer_plugin",
 *   name = @Translation("PHPMailer Gmail Oauth2"),
 *   grant_type = "authorization_code",
 *   success_message = TRUE,
 *   scopes = {
 *     "scope" = {
 *       "https://mail.google.com/"
 *     }
 *   }
 * )
 */
class PHPMailerPlugin extends Oauth2ClientPluginBase implements Oauth2ClientPluginRedirectInterface {

  /*
   * Storing a single AccessToken in state for the plugin shares access to the
   * external resource for ALL users of this plugin.
   */

    use StateTokenStorage;

    /**
     * Injected services
     */
    protected Request $currentRequest;

    protected $gmailService;

    /**
     * The Drupal tempstore.
     */
    protected PrivateTempStore $tempStore;

    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

        $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);

        $instance->gmailService   = $container->get('oauth2_gmail_client.gmail_service');

        $requestStack             = $container->get('request_stack');
        $instance->currentRequest = $requestStack->getCurrentRequest();

        $tempstoreFactory         = $container->get('tempstore.private');
        $instance->tempStore      = $tempstoreFactory->get('oauth2_client');

        return $instance;
    }

    /**
     * Retrieves the authorization_uri of the OAuth2 server.
     *
     * @return string
     *   The authorization_uri of the OAuth2 server.
     */
    public function getAuthorizationUri(): string {

        // set up redirect to Google authorization login page
        $url = $this->gmailService->getProvider()->getAuthorizationUrl($this->getScopes());
        return $url;
    }

    /**
     * Creates a new provider object.
     *
     * @return \League\OAuth2\Client\Provider\GenericProvider
     *   The provider of the OAuth2 Server.
     */
    public function getProvider(): AbstractProvider {

        return $this->gmailService->getProvider();
    }

    public function getAccessToken(?OwnerCredentials $credentials = NULL): ?AccessTokenInterface {

        $provider = $this->gmailService->getProvider();

        // set up redirect to Google authorization login page
        $authUrl = $provider->getAuthorizationUrl($this->getScopes());

        if(!empty($authUrl)) {
            // save the state to Drupal's tempstore
            $this->tempStore->set('oauth2_client_state-' . $this->getPluginId(), $provider->getState());
        }

        // may need to add accounts.google.com to your settings.php file:  $settings['trusted_host_patterns'] = ['^accounts\.google\.com$'];
        throw new AuthCodeRedirect($authUrl);

        return Null;
    }

    public function getPostCaptureRedirect(): RedirectResponse {

        $url = Url::fromRoute('entity.oauth2_client.collection');
        return new RedirectResponse($url->toString(TRUE)->getGeneratedUrl());
    }
}
