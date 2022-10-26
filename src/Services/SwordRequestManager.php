<?php

namespace Drupal\sword\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service description.
 */
class SwordRequestManager
{

  /**
   * Config service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $sword_config;

  protected $request_stack;

  public function __construct(ConfigFactoryInterface $config_factory, RequestStack $requestStack)
  {
    $this->sword_config = $config_factory->get('sword.settings');
    $this->request_stack = $requestStack;
  }


  /**
   * @return string|Response
   */
  function swordCollection()
  {

    $request = $this->request_stack->getCurrentRequest();

    $method = $request->getMethod();
    if ($method === 'GET') {
      return $this->getServiceDocument();
    }
    if ($method === 'POST') {
      return $this->handleCollectionPost();
    } else if ($method === 'PUT') {
      return $this->swordResponse('Method Not Allowed', array('Status' => 405));
    } else if ($method === 'DELETE') {
      return $this->swordResponse('Method Not Allowed', array('Status' => 405));
    } else {
      return $this->swordResponse('Not Implemented', array('Status' => 501));
    }
  }

  /**
   * Response for a service document request.
   * @return string
   */
  function getServiceDocument()
  {

    $workspacename = "Vireo SWORD importer";
    $collectionname = $this->sword_config->get('collectionname');
    $swordbase = $this->sword_config->get('base_url');
    $collectionurl = Url::fromUserInput('/' . $swordbase . '/' . $this->setNamePath($collectionname), array('absolute' => TRUE))->toString();
    $acceptmimetypes = $this->sword_config->get('acceptmimetype');
    $acceptmimetypes = explode(',', $acceptmimetypes);
    array_walk($acceptmimetypes, function (&$value, $key) {
      $value = '<accept>' . $value . '</accept>';
    });
    $acceptmimeelements = implode('', $acceptmimetypes);
    $packaging = $this->sword_config->get('acceptpackaging');
    $packaging = explode(',', $packaging);
    $packagingelements = '';
    foreach ($packaging as $uriq) {
      $uq = explode(' ', $uriq);
      $packagingelements .= '<sword:acceptPackaging';
      if (isset($uq[1])) {
        $packagingelements .= ' q="' . $uq[1] . '"';
      }
      $packagingelements .= '>';
      $packagingelements .= $uq[0];
      $packagingelements .= '</sword:acceptPackaging>';
    }

    $xml = <<<XML
<?xml version="1.0" encoding='utf-8'?>
<service xmlns="http://www.w3.org/2007/app"
         xmlns:atom="http://www.w3.org/2005/Atom"
	 xmlns:sword="http://purl.org/net/sword/"
	 xmlns:dcterms="http://purl.org/dc/terms/">

 <sword:version>1.3</sword:version>
 <workspace>
   <sword:noOp>true</sword:noOp>
   <atom:title>$workspacename</atom:title>
   <collection href="$collectionurl">
     <atom:title>$collectionname</atom:title>
     $acceptmimeelements
     $packagingelements
     <sword:mediation>false</sword:mediation>
   </collection>

 </workspace>
</service>
XML;

    return $this->swordResponse($xml, array('Content-Type' => 'application/xml'));
  }


  function handleCollectionPost()
  {

    // Authenticate
    $isauthenticated = FALSE;
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
      $authheader = $_SERVER['HTTP_AUTHORIZATION'];
      list($authtype, $authcontent) = explode(' ', $authheader);
      if ($authtype === 'Basic') {
        list($name, $pw) = explode(':', base64_decode($authcontent));
        $username = $this->sword_config->get('importusername');
        if ($name === $username) {
          $uid = \Drupal::service('user.auth')->authenticate($name, $pw);
          $isauthenticated = ($uid !== FALSE);
        }
      }
    }
    if (!$isauthenticated) {
      return $this->swordResponse('Unauthorized-Type', array('Status' => 401, 'WWW-Athenticate' => 'Basic'));
    }

    // Check mimetypes
    $acceptmimetypes = $this->sword_config->get('acceptmimetype');

    $acceptmimetypes = explode(',', $acceptmimetypes);
    $mimetype = $_SERVER['CONTENT_TYPE'];
    if (!in_array($mimetype, $acceptmimetypes)) {
      return $this->swordErrorResponse(415, 'http://purl.org/net/sword/error/ErrorContent', $this->t('The mime-type !mime is not supported', array('!mime' => $mimetype)));
    }
    // Check packaging
    $acceptpackaging = $this->sword_config->get('acceptpackaging');
    $acceptpackaging = explode(',', $acceptpackaging);
    $acceptpackaging = array_map(function ($ap) {
      return explode(' ', $ap)[0];
    }, $acceptpackaging);
    $packaging = $_SERVER['HTTP_X_PACKAGING'];
    if (!in_array($packaging, $acceptpackaging)) {
      return $this->swordErrorResponse(415, 'http://purl.org/net/sword/error/ErrorContent', t('The packagin !packaging is not supported', array('!packaging' => $packaging)));
    }

    // X-No-Op header
    $do_dry_run_only = isset($_SERVER['HTTP_X_NO_OP']) && $_SERVER['HTTP_X_NO_OP'] === 'true';


    $atomentryxml = $this->atomEntryXml($packaging);


    return $this->swordResponse($atomentryxml, array('Status' => 201, 'Content-Type' => 'application/atom+xml; charset="utf-8"',));

  }


  /**
   * Helper function to get the collection name as used in urls.
   **/
  function setNamePath($collectionname)
  {
    $collection = preg_replace('/[^a-z0-9_]+/', '_', strtolower($collectionname));
    return $collection;
  }

  function swordErrorResponse($statuscode, $erroruri, $errorsummary)
  {
    $swordbase = $this->sword_config->get('base_url');
    $generatoruri = Url::fromUserInput('/' . $swordbase, array('absolute' => TRUE))->toString();
    $updatedate = date('Y-m-d\TH:i:s\Z');
    $errorhref = isset($erroruri) ? 'href="' . $erroruri . '"' : '';
    $errorxml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<sword:error xmlns="http://www.w3.org/2005/Atom"
       xmlns:sword="http://purl.org/net/sword/"
       xmlns:arxiv="http://arxiv.org/schemas/atom"
       $errorhref>
  <title>ERROR</title>
  <updated>$updatedate</updated>
  <sword:userAgent>Islandora SWORD Importer</sword:userAgent>
  <generator uri="$generatoruri"/>
  <summary>$errorsummary</summary>
  <sword:treatment>processing failed</sword:treatment>
</sword:error>
XML;

    return $this->swordResponse($errorxml, array('Status' => $statuscode));
  }

  /**
   * Print response.
   */
  function swordResponse($data, $headers)
  {

    $response = new Response();

    foreach ($headers as $name => $value) {

      $response->headers->set($name, $value);
    }
    if (isset($data)) {
      $response->setContent($data);
    }

    return $response;
  }


  function atomEntryXml($packaging)
  {
    $title = "Test title";
    $pid = 123;
    $updatedate = date('Y-m-d\TH:i:s\Z');
    $swordbase = $this->sword_config->get('base_url');
    $generatoruri = Url::fromUserInput('/' . $swordbase . '/' . $this->setNamePath($swordbase), array('absolute' => TRUE))->toString();
    $objecturl = Url::fromUserInput('/islandora/object/' . $pid, array('absolute' => TRUE))->toString();
    $packagingxml = '';
    if (is_array($packaging)) {
      foreach ($packaging as $p) {
        $packagingxml .= "<sword:packaging>$p</sword:packaging>";
      }
    } else {
      $packagingxml = "<sword:packaging>$packaging</sword:packaging>";
    }
    $treatment = "Converted with workflow";
    $atomentryxml = <<<XML
<?xml version="1.0"?>
<entry xmlns="http://www.w3.org/2005/Atom" xmlns:sword="http://purl.org/net/sword/">
  <title>$title</title>
  <id>$pid</id>
  <updated>$updatedate</updated>
  <sword:userAgent>Islandora Instant Importer (SWORD activator)</sword:userAgent>
  <generator uri="$generatoruri"/>
  <content type="text/html" src="$objecturl"/>
  $packagingxml
  <sword:treatment>$treatment</sword:treatment>
</entry>
XML;

    return $atomentryxml;
  }
}
