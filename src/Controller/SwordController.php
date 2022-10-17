<?php

namespace Drupal\sword\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\sword\Api;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Returns responses for sword routes.
 */
class SwordController extends ControllerBase {

  /**
   * @var Api
   */
  protected Api $api;

  public function __construct(Api $api)
  {
    $this->api = $api;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('sword.api')
    );
  }

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

  /**
   * @param string $api_prefix
   * @param string $api_postfix
   * @return Response
   */
  public function serviceDocument(string $api_prefix, string $api_postfix){

    $swordbase =\Drupal::config('sword.settings')->get('swordbase');

    $sword_api = explode("/",$swordbase);

    if(in_array($api_prefix,$sword_api) && in_array($api_postfix,$sword_api)){
      return $this->api->swordCollection();

      $response = new Response();
      $response->headers->set('Content-Type', 'application/xml');
      $response->setContent($document);
    } else {
      throw new NotFoundHttpException();
    }

    return $response;
  }



}
