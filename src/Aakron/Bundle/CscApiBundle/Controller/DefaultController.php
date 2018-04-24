<?php

namespace Aakron\Bundle\CscApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Response;
use Lsw\ApiCallerBundle\Call\HttpPostJsonBody as HttpPostJsonBody;
use Lsw\ApiCallerBundle\Call\HttpGetJson as HttpGetJson;
use Lsw\ApiCallerBundle\Call\HttpPostJson as HttpPostJson;
class DefaultController extends Controller
{
    /**
     * @Route("/aakron-contacts", name="insert_sql")
     */
    public function aakronContactsAction()
    {
        
        $response = $this->get('aakron_import_contact_api')->syncCscContacts();
//         echo "<pre>";
//         print_r($response);
//         $response = new Response();
//         $serializer = $this->get('jms_serializer');
        
//         // set header content type as json for all response.
//         $response->headers->set('Content-Type', 'application/json');
//         $response->setStatusCode(200);
        
//         $response->setContent($serializer->serialize($returnData, 'json'));
        return $response;
    }
    
}
