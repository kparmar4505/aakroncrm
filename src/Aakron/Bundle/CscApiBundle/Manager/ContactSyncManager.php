<?php
namespace Aakron\Bundle\CscApiBundle\Manager;
use Lsw\ApiCallerBundle\Call\HttpPostJsonBody as HttpPostJsonBody;
use Lsw\ApiCallerBundle\Call\HttpGetJson as HttpGetJson;
use Lsw\ApiCallerBundle\Call\HttpPostJson as HttpPostJson;
use Symfony\Component\Console\Helper\ProgressBar;
/**
 *
 * @author OfficeBrain 4505 <info@officebrain.com>
 *
 * Description : Extended class
 *
 */
class ContactSyncManager
{
    public function __construct($container,$destinationApiUrl,$sourceApiUrl,$userName,$userApiKey)
    {
        $this->destinationApiUrl = $destinationApiUrl;
        $this->sourceApiUrl = $sourceApiUrl;
        $this->container = $container->getContainer();
        $this->userName = $userName;
        $this->userApiKey = $userApiKey;
        $this->addContactArray = $this->getAddContactArray();
        $this->updateContactArray = $this->getUpdateContactArray();
        $this->unValidatedContacts = array();
       
    }
    public function getSourceApi()
    {
        return $this->sourceApiUrl;
    }
    public function getDestinationApi()
    {
        return $this->destinationApiUrl;
    }
    public function getAddContactArray()
    {        
        $addContactArray["data"]["type"]="contacts";
        $addContactArray["data"]["attributes"]=array();
        $addContactArray["data"]["relationships"]["owner"]["data"]["type"]="users";
        $addContactArray["data"]["relationships"]["owner"]["data"]["id"]="1"; //If user need to change the change user id
        $addContactArray["data"]["relationships"]["organization"]["data"]["type"]="organizations";
        $addContactArray["data"]["relationships"]["organization"]["data"]["id"]="1"; //it should be 1 always because we have only one organization
        return $addContactArray;
    }
    public function getAddAccountArray()
    {
        $addContactArray["data"]["type"]="accounts";
        $addContactArray["data"]["attributes"]["extend_description"]="null";
        $addContactArray["data"]["attributes"]["name"]=""; //Dynamically update contact name
        $addContactArray["data"]["relationships"]["owner"]["data"]["type"]="users"; 
        $addContactArray["data"]["relationships"]["owner"]["data"]["id"]="1";//If user need to change the change user id
        $addContactArray["data"]["relationships"]["contacts"]["data"]=array();   //Dynamically push array of contact
        $addContactArray["data"]["relationships"]["defaultContact"]["data"]=array(); //Dynamically push array of default contact
        $addContactArray["data"]["relationships"]["organization"]["data"]["type"]="organizations";
        $addContactArray["data"]["relationships"]["organization"]["data"]["id"]="1"; //it should be 1 always because we have only one organization
        return $addContactArray;
//         array (
//             'data' =>
//             array (
//                 'type' => 'accounts',
//                 'attributes' =>
//                 array (
//                     'extend_description' => NULL,
//                     'name' => 'Gartner management group',
//                 ),
//                 'relationships' =>
//                 array (
//                     'owner' =>
//                     array (
//                         'data' =>
//                         array (
//                             'type' => 'users',
//                             'id' => '44',
//                         ),
//                     ),
//                     'contacts' =>
//                     array (
//                         'data' =>
//                         array (
//                             0 =>
//                             array (
//                                 'type' => 'contacts',
//                                 'id' => '1',
//                             ),
//                             1 =>
//                             array (
//                                 'type' => 'contacts',
//                                 'id' => '3',
//                             ),
//                             2 =>
//                             array (
//                                 'type' => 'contacts',
//                                 'id' => '22',
//                             ),
//                         ),
//                     ),
//                     'defaultContact' =>
//                     array (
//                         'data' =>
//                         array (
//                             'type' => 'contacts',
//                             'id' => '1',
//                         ),
//                     ),
//                     'organization' =>
//                     array (
//                         'data' =>
//                         array (
//                             'type' => 'organizations',
//                             'id' => '1',
//                         ),
//                     ),
//                 ),
//             ),
//         )
    }
    public function getUpdateContactArray()
    {
        $updateContactArray["data"]["type"]="contacts";
        $updateContactArray["data"]["id"]="1";
        $updateContactArray["data"]["attributes"]=array();
        return $updateContactArray;
    }
    public function generatAuthentication()
    {
        
        $nonce = base64_encode(substr(md5(uniqid()), 0, 16));
        $created  = date('c');
        $digest   = base64_encode(sha1(base64_decode($nonce) . $created . $this->userApiKey, true));
        $options = array(
            "httpheader" => array("Content-type: application/vnd.api+json",
                "Accept: application/vnd.api+json",
                "Authorization: WSSE profile=\"UsernameToken\"",
                "X-WSSE: UsernameToken Username=\"".$this->userName."\", PasswordDigest=\"".$digest."\", Nonce=\"".$nonce."\", Created=\"".$created."\""
            )
        );
        return $options;
    }
    public function syncCscContacts()
    {
        $requestArray = $this->callAakronCscApi();
 
        $responseData = array();
        $i = 1;
        foreach($requestArray as $contactRequest){
            if($i>0){
            $options = $this->generatAuthentication();
            $responseData[] = $this->container->get('api_caller')->call(new HttpPostJsonBody($this->destinationApiUrl, $contactRequest, false,$options)); // true to have an associative array as answer
     //       print_r($responseData);exit;
            }
            $i = 0;
        }
        return $responseData;
    }
    
    public function callAakronCscApi()
    {
        $crmParameters= array();
        $responseData = $this->container->get('api_caller')->call(new HttpGetJson($this->sourceApiUrl,array()));
       
        foreach($responseData as $key=>$contactData)
        {
            $contactData1 = (Array)$contactData;
            if($this->validateCscData($contactData1))
            {
                $checkDuplicate = $this->checkDuplicateRecord($contactData1);
                
                if($checkDuplicate<=0)
                {
                    $tempArray = $this->addContactArray;                    
                }
                else {
                    $tempArray = $this->updateContactArray;     
                    $tempArray["data"]["id"] = $checkDuplicate; 
                }
                
                $contactData1 = $this->updateSocialForContact($contactData1);
                $tempArray['data']['attributes'] = $contactData1;
                $tempArray['data']['attributes']['primaryEmail'] =  $contactData1['emails'];
                $tempArray['data']['attributes']['primaryPhone'] =   $contactData1['phones'];
                unset($tempArray['data']['attributes']['emails']);
                unset($tempArray['data']['attributes']['phones']);
                $crmParameters[] = $tempArray;
               // print_r($tempArray);exit;
                unset($tempArray);
            }
            else {
                $this->unValidatedContacts[$key] = $contactData1;
            }
           // print_r($contactData1);exit;
            unset($contactData1);
        }
       
        return $crmParameters;
    }
    public function validateCscData($contactRequest)
    {
        if(empty($contactRequest['firstName']) || empty($contactRequest['lastName']) || empty($contactRequest['emails']) || empty($contactRequest['phones']))
        {
            return false;
        }
        return true;
    }
    public function updateSocialForContact($contactData)
    {
        if(!empty($contactData['social_account']))
        {
            $socialAcountType = strtolower(trim($contactData['social_type']));
            
            switch ($socialAcountType) {
                case facebook:
                    $contactData['facebook'] = $contactData['social_account'];
                    break;
                case skype:
                    $contactData['skype'] = $contactData['social_account'];
                    break;
                case twitter:
                    echo "test";
                    $contactData['twitter'] = $contactData['social_account'];
                    echo "test";
                    break;
                case linkedIn:
                    $contactData['linkedIn'] = $contactData['social_account'];
                    break;
                default:
                    
            }
            unset($contactData['social_type']);
            unset($contactData['social_account']);
        }
        return $contactData;
    }
    public function checkDuplicateRecord($contactData)
    {
        $requestArray = array();
        $requestArray['page']['number']=1;
        $requestArray['page']['size']=10;
        $requestArray['sort']='';
        $requestArray['filter']['emails']=$contactData['emails'];
      
        $options = $this->generatAuthentication();
        $responseData = $this->container->get('api_caller')->call(new HttpGetJson($this->destinationApiUrl, $requestArray,false,$options));
        $responseData = (Array)$responseData;
        if(count($responseData['data'])<=0)
        {
            return 0;
        }
        else {
            $record = (Array)$responseData["data"]["0"];
            return $record["id"];
        }
    }
    public function extractAccountData($contactData)
    {
       // $this->container->get('api_caller')->call(new HttpPostJsonBody($this->destinationApiUrl, $contactRequest, false,$options));
       $responseArray = array();
       foreach($contactData as $key=>$value)
       {
           if(isset($contactData["account"]) && isset($contactData["accountName"]))
           {
               $responseArray["account"]["account_number"]=$contactData["account"];
               $responseArray["account"]["account_name"]=$contactData["accountName"];
               
               unset($contactData["account"]);
               unset($contactData["accountName"]);
           }
           
       }
       $responseArray["contact"]=$contactData;
       
       return $responseArray;
    }
}