<?php
/** 
 * @author software 
 **/
namespace Aakron\Bundle\CscApiBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Lsw\ApiCallerBundle\Call\HttpPostJsonBody as HttpPostJsonBody;
use Lsw\ApiCallerBundle\Call\HttpGetJson as HttpGetJson;
use Lsw\ApiCallerBundle\Call\HttpPostJson as HttpPostJson;
use Symfony\Component\Console\Helper\ProgressBar;
class AakronApiCommand extends ContainerAwareCommand implements CronCommandInterface
{
    const COMMAND_NAME   = 'oro:cron:aakron-api-command';

    /**
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '*/1 * * * *';
    }

    /**
     * Console command configuration
     */
    public function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Aakron csc api');
    }

    /**
     * Runs command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     *
     * @throws \InvalidArgumentException
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $importApiManager = $this->getContainer()->get('aakron_import_contact_api');  
        $apiCallerManager = $this->getContainer()->get('api_caller');  
            
        //$crmParameters= array();
        $responseData = $apiCallerManager->call(new HttpGetJson($importApiManager->getSourceApi(),array()));
        $progressBar = new ProgressBar($output, count($responseData));
        $progressBar->start();
      //  $progressBar->setRedrawFrequency(10);
       // print_r($responseData);exit;
        $responseArray=array();
        $accountData=array();
        $i = 0;
        foreach($responseData as $key=>$contactData)
        {
//             if($i==5)
//             {
//                 continue ;
//               //  print_r($accountData);exit;
//             }
            $contactData1 = (Array)$contactData;
            if($importApiManager->validateCscData($contactData1))
            {
                $checkDuplicate = $importApiManager->checkDuplicateRecord($contactData1);
                
                if($checkDuplicate<=0)
                {
                    $tempArray = $importApiManager->getAddContactArray();
                }
                else {
                    $tempArray = $importApiManager->getUpdateContactArray();
                    $tempArray["data"]["id"] = $checkDuplicate;
                }
                
                $contactData2 = $importApiManager->updateSocialForContact($contactData1);
                $contactData3 = $importApiManager->extractAccountData($contactData2);
                $contactData1 = $contactData3["contact"];
                if(key_exists("account", $contactData3))
                {
                    $accountData[$contactData3["account"]["account_number"]]["accountName"] = $contactData3["account"]["account_name"];
                }
                
                $tempArray['data']['attributes'] = $contactData1;
                $tempArray['data']['attributes']['primaryEmail'] =  $contactData1['emails'];
                $tempArray['data']['attributes']['primaryPhone'] =   $contactData1['phones'];
                unset($tempArray['data']['attributes']['emails']);
                unset($tempArray['data']['attributes']['phones']);
              //  $crmParameters[] = $tempArray;
                
                
                
                /*********Add/Update contact on CRM *******/
                $options = $importApiManager->generatAuthentication();
                $responseData = $apiCallerManager->call(new HttpPostJsonBody($importApiManager->getDestinationApi(), $tempArray, false,$options));
                $responseArray[] = $responseData;
                /*********Add/Update contact on CRM *******/
                
                /*********accounts on CRM *******/
                if(key_exists("account", $contactData3))
                {
                    if(empty($accountData[$contactData3["account"]["account_number"]]["accountName"]))
                    {
                        $accountData[$contactData3["account"]["account_number"]]["accountName"] = $contactData3["account"]["account_name"];
                    }
                    $accountData[$contactData3["account"]["account_number"]]["contacts"][] = array ("type" => "contacts","id" => $responseData->data->id);    
                }
                /*********accounts on CRM *******/
                $i++;
                unset($tempArray);
            }
            else {
               // $this->unValidatedContacts[$key] = $contactData1;
            }
            
        
            $progressBar->advance();
            
            unset($contactData1);
        }
        
        $responseAccountData=array();
        /*********Add/Update accounts on CRM *******/
        if(count($accountData)>0)
        {
            foreach($accountData as $accountId=>$accountData)
            {
                $optionsForAccount = $importApiManager->generatAuthentication();
                $postArray = $importApiManager->getAddAccountArray();
                $postArray["data"]["attributes"]["name"]=$accountData["accountName"];
                $defaultContactData = $accountData["contacts"][0];
                
                $postArray["data"]["relationships"]["contacts"]["data"]=$accountData["contacts"];
                $postArray["data"]["relationships"]["defaultContact"]["data"]=$defaultContactData;
                
                $responseAccountData = $apiCallerManager->call(new HttpPostJsonBody($this->getContainer()->getParameter("accounts.destination.url"), $postArray, false,$optionsForAccount));
            }
        }
         //  print_r($responseAccountData );exit;
        /*********Add/Update accounts on CRM *******/
     
        // ensures that the progress bar is at 100%
        $progressBar->finish();
        $output->write("Import done");
  //      exit;
        
     //   $this->getContactData();     
     //   $output->write("Import done");
    }
    public function isActive()
    {}

    public function getContactData()
    {
        
        $this->getContainer()->get('aakron_import_contact_api')->syncCscContacts();
       
    }
   
}