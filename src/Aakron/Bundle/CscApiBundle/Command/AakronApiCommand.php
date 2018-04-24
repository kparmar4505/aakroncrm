<?php

/**
 * @author software
 *
 */
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
      
//            
            
        //$crmParameters= array();
        $responseData = $this->getContainer()->get('api_caller')->call(new HttpGetJson($this->getContainer()->get('aakron_import_contact_api')->getSourceApi(),array()));
        $progressBar = new ProgressBar($output, count($responseData));
        $progressBar->start();
      //  $progressBar->setRedrawFrequency(10);
        
        $responseArray=array();
        foreach($responseData as $key=>$contactData)
        {
            $contactData1 = (Array)$contactData;
            if($this->getContainer()->get('aakron_import_contact_api')->validateCscData($contactData1))
            {
                $checkDuplicate = $this->getContainer()->get('aakron_import_contact_api')->checkDuplicateRecord($contactData1);
                
                if($checkDuplicate<=0)
                {
                    $tempArray = $this->getContainer()->get('aakron_import_contact_api')->getAddContactArray();
                }
                else {
                    $tempArray = $this->getContainer()->get('aakron_import_contact_api')->getUpdateContactArray();
                    $tempArray["data"]["id"] = $checkDuplicate;
                }
                
                $contactData1 = $this->getContainer()->get('aakron_import_contact_api')->updateSocialForContact($contactData1);
                $tempArray['data']['attributes'] = $contactData1;
                $tempArray['data']['attributes']['primaryEmail'] =  $contactData1['emails'];
                $tempArray['data']['attributes']['primaryPhone'] =   $contactData1['phones'];
                unset($tempArray['data']['attributes']['emails']);
                unset($tempArray['data']['attributes']['phones']);
              //  $crmParameters[] = $tempArray;
                
                
                
                /*********Update on CRM *******/
                $options = $this->getContainer()->get('aakron_import_contact_api')->generatAuthentication();
                $responseArray[] = $this->getContainer()->get('api_caller')->call(new HttpPostJsonBody($this->getContainer()->get('aakron_import_contact_api')->getDestinationApi(), $tempArray, false,$options));
                /*****************/
                
                
                // print_r($tempArray);exit;
                unset($tempArray);
            }
            else {
               // $this->unValidatedContacts[$key] = $contactData1;
            }
            $progressBar->advance();
            // print_r($contactData1);exit;
            unset($contactData1);
        }
        
     //   return $crmParameters;
        
        
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