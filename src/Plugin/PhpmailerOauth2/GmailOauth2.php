<?php

namespace Drupal\oauth2_gmail_client\Plugin\PhpmailerOauth2;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\oauth2_gmail_client\Service\GmailProviderService;
use Drupal\phpmailer_smtp\Plugin\PhpmailerOauth2\PhpmailerOauth2PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Gmail OAuth2 plugin
 *
 * @PhpmailerOauth2(
 *   id = "gmail_oauth2",
 *   name = @Translation("Gmail OAuth2"),
 * )
 */
class GmailOauth2 extends PhpmailerOauth2PluginBase implements ContainerFactoryPluginInterface {

    /**
     * The Gmail provider service
     *
     * @var \Drupal\phpmailer_gmail_oauth2\Service\GmailProviderService
     */
    protected $gmailService;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $configuration, $plugin_id, $plugin_definition, GmailProviderService $gmail_service) {

        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->gmailService = $gmail_service;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('oauth2_gmail_client.gmail_service')
        );
    }

    /**
     * {@inheritdoc}
     */

    public function getAuthOptions() {

        \Drupal::logger('GmailOauth2')->notice('getAuthOptions()');
        return $this->gmailService->getAuthOptions();
    }
}
