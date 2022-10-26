<?php

namespace Drupal\sword\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\sword\Services\SwordRequestManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Returns responses for sword routes.
 */
class SwordController extends ControllerBase {

  /**
   * @var SwordRequestManager
   */
  protected SwordRequestManager $swordRequestManager;

  public function __construct(SwordRequestManager $swordRequestManager)
  {
    $this->swordRequestManager = $swordRequestManager;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('sword.request.manager')
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


    $swordRequestManager = \Drupal::service('sword.request.manager');
    $swordbase =\Drupal::config('sword.settings')->get('swordbase');

    $sword_api = explode("/",$swordbase);

    if(in_array($api_prefix,$sword_api) && in_array($api_postfix,$sword_api)){
      return $swordRequestManager->swordCollection();

    } else {
      throw new NotFoundHttpException();
    }

  }



}
