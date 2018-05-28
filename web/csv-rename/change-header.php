<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$f_source = "contacts_4_18.csv";
$f_target = "outlook-contacts-for-orocrm".time().".csv";
function delete_col(&$array, $offset) {
    return array_walk($array, function (&$v) use ($offset) {
        array_splice($v, $offset, 1);
    });
}
/****/
$array_mapping_fields = array("First Name"=>"First name","Last Name"=>"Last name","Suffix"=>"Name suffix","Gender"=>"Gender","Job Title"=>"Job Title","Business Fax"=>"Fax","Birthday"=>"Birthday","E-mail Address"=>"Emails 1 Email","E-mail 2 Address"=>"Emails 2 Email","E-mail 3 Address"=>"Emails 3 Email","Business Phone"=>"Phones 1 Phone","Mobile Phone"=>"Phones 2 Phone","Home Phone"=>"Phones 3 Phone","Business Phone 2"=>"Phones 4 Phone","Home Phone 2"=>"Phones 5 Phone","Other Phone"=>"Phones 6 Phone","Car Phone"=>"Phones 7 Phone","Company Main Phone"=>"Phones 8 Phone","Pager"=>"Phones 9 Phone","Business Street"=>"Addresses 1 Street","Business Street 2"=>"Addresses 1 Street 2","Business Postal Code"=>"Addresses 1 Zip/Postal Code","Business City"=>"Addresses 1 City","Business State"=>"Addresses 1 State","Business Country/Region"=>"Addresses 1 Country ISO2 code","Home Street"=>"Addresses 2 Street","Home Street 2"=>"Addresses 2 Street 2","Home Postal Code"=>"Addresses 2 Zip/Postal Code","Home City"=>"Addresses 2 City","Home State"=>"Addresses 2 State","Home Country/Region"=>"Addresses 2 Country ISO2 code","Other Street"=>"Addresses 3 Street","Other Street 2"=>"Addresses 3 Street 2","Other Postal Code"=>"Addresses 3 Zip/Postal Code","Other City"=>"Addresses 3 City","Other State"=>"Addresses 3 State","Other Country/Region"=>"Addresses 3 Country ISO2 code","Company"=>"Accounts Default Contact 1 Account name"); //Accounts 1 Account name
$outlook_fields_array=array("0"=>"Title","1"=>"First Name","2"=>"Middle Name","3"=>"Last Name","4"=>"Suffix","5"=>"Company","6"=>"Department","7"=>"Job Title","8"=>"Business Street","9"=>"Business Street 2","10"=>"Business Street 3","11"=>"Business City","12"=>"Business State","13"=>"Business Postal Code","14"=>"Business Country/Region","15"=>"Home Street","16"=>"Home Street 2","17"=>"Home Street 3","18"=>"Home City","19"=>"Home State","20"=>"Home Postal Code","21"=>"Home Country/Region","22"=>"Other Street","23"=>"Other Street 2","24"=>"Other Street 3","25"=>"Other City","26"=>"Other State","27"=>"Other Postal Code","28"=>"Other Country/Region","29"=>"Assistant's Phone","30"=>"Business Fax","31"=>"Business Phone","32"=>"Business Phone 2","33"=>"Callback","34"=>"Car Phone","35"=>"Company Main Phone","36"=>"Home Fax","37"=>"Home Phone","38"=>"Home Phone 2","39"=>"ISDN","40"=>"Mobile Phone","41"=>"Other Fax","42"=>"Other Phone","43"=>"Pager","44"=>"Primary Phone","45"=>"Radio Phone","46"=>"TTY/TDD Phone","47"=>"Telex","48"=>"Account","49"=>"Anniversary","50"=>"Assistant's Name","51"=>"Billing Information","52"=>"Birthday","53"=>"Business Address PO Box","54"=>"Categories","55"=>"Children","56"=>"Directory Server","57"=>"E-mail Address","58"=>"E-mail Type","59"=>"E-mail Display Name","60"=>"E-mail 2 Address","61"=>"E-mail 2 Type","62"=>"E-mail 2 Display Name","63"=>"E-mail 3 Address","64"=>"E-mail 3 Type","65"=>"E-mail 3 Display Name","66"=>"Gender","67"=>"Government ID Number","68"=>"Hobby","69"=>"Home Address PO Box","70"=>"Initials","71"=>"Internet Free Busy","72"=>"Keywords","73"=>"Language","74"=>"Location","75"=>"Manager's Name","76"=>"Mileage","77"=>"Notes","78"=>"Office Location","79"=>"Organizational ID Number","80"=>"Other Address PO Box","81"=>"Priority","82"=>"Private","83"=>"Profession","84"=>"Referred By","85"=>"Sensitivity","86"=>"Spouse","87"=>"User 1","88"=>"User 2","89"=>"User 3","90"=>"User 4","91"=>"Web Page");
$orocrm_fields_array=array("0" =>"Id","1" =>"Name prefix","2" =>"First name","3" =>"Middle name","4" =>"Last name","5" =>"Name suffix","6" =>"Gender","7" =>"Description","8" =>"Job Title","9" =>"Fax","10" =>"Skype","11" =>"Twitter","12" =>"Facebook","13" =>"Google+","14" =>"LinkedIn","15" =>"Birthday","16" =>"Source Name","17" =>"Contact Method Name","18" =>"Owner Username","19" =>"Assigned to Username","20" =>"Emails 1 Email","21" =>"Emails 2 Email","22" =>"Emails 3 Email","23" =>"Phones 1 Phone","24" =>"Phones 2 Phone","25" =>"Phones 3 Phone","26" =>"Phones 4 Phone","27" =>"Phones 5 Phone","28" =>"Phones 6 Phone","29" =>"Phones 7 Phone","30" =>"Phones 8 Phone","31" =>"Phones 9 Phone","32" =>"Accounts Default Contact 1 Account name","33" =>"Accounts 1 Account name","34" =>"Accounts 2 Account name","35" =>"Addresses 1 Label","36" =>"Addresses 1 Organization","37" =>"Addresses 1 Name prefix","38" =>"Addresses 1 First name","39" =>"Addresses 1 Middle name","40" =>"Addresses 1 Last name","41" =>"Addresses 1 Name suffix","42" =>"Addresses 1 Street","43" =>"Addresses 1 Street 2","44" =>"Addresses 1 Zip/Postal Code","45" =>"Addresses 1 City","46" =>"Addresses 1 State","47" =>"Addresses 1 State Combined code","48" =>"Addresses 1 Country ISO2 code","49" =>"Addresses 1 Types 1 Name","50" =>"Addresses 1 Types 2 Name","51" =>"Addresses 2 Label","52" =>"Addresses 2 Organization","53" =>"Addresses 2 Name prefix","54" =>"Addresses 2 First name","55" =>"Addresses 2 Middle name","56" =>"Addresses 2 Last name","57" =>"Addresses 2 Name suffix","58" =>"Addresses 2 Street","59" =>"Addresses 2 Street 2","60" =>"Addresses 2 Zip/Postal Code","61" =>"Addresses 2 City","62" =>"Addresses 2 State","63" =>"Addresses 2 State Combined code","64" =>"Addresses 2 Country ISO2 code","65" =>"Addresses 2 Types 1 Name","66" =>"Addresses 2 Types 2 Name","67" =>"Addresses 3 Label","68" =>"Addresses 3 Organization","69" =>"Addresses 3 Name prefix","70" =>"Addresses 3 First name","71" =>"Addresses 3 Middle name","72" =>"Addresses 3 Last name","73" =>"Addresses 3 Name suffix","74" =>"Addresses 3 Street","75" =>"Addresses 3 Street 2","76" =>"Addresses 3 Zip/Postal Code","77" =>"Addresses 3 City","78" =>"Addresses 3 State","79" =>"Addresses 3 State Combined code","80" =>"Addresses 3 Country ISO2 code","81" =>"Addresses 3 Types 1 Name","82" =>"Addresses 3 Types 2 Name","83" =>"Organization Name","84" =>"Picture","85" =>"Tags");
$orocrm_fields_blank_array=array("0"=>" ","1"=>" ","2"=>" ","3"=>" ","4"=>" ","5"=>" ","6"=>" ","7"=>" ","8"=>" ","9"=>" ","10"=>" ","11"=>" ","12"=>" ","13"=>" ","14"=>" ","15"=>" ","16"=>" ","17"=>" ","18"=>" ","19"=>" ","20"=>" ","21"=>" ","22"=>" ","23"=>" ","24"=>" ","25"=>" ","26"=>" ","27"=>" ","28"=>" ","29"=>" ","30"=>" ","31"=>" ","32"=>" ","33"=>" ","34"=>" ","35"=>" ","36"=>" ","37"=>" ","38"=>" ","39"=>" ","40"=>" ","41"=>" ","42"=>" ","43"=>" ","44"=>" ","45"=>" ","46"=>" ","47"=>" ","48"=>" ","49"=>" ","50"=>" ","51"=>" ","52"=>" ","53"=>" ","54"=>" ","55"=>" ","56"=>" ","57"=>" ","58"=>" ","59"=>" ","60"=>" ","61"=>" ","62"=>" ","63"=>" ","64"=>" ","65"=>" ","66"=>" ","67"=>" ","68"=>" ","69"=>" ","70"=>" ","71"=>" ","72"=>" ","73"=>" ","74"=>" ","75"=>" ","76"=>" ","77"=>" ","78"=>" ","79"=>" ","80"=>" ","81"=>" ","82"=>" ","83"=>" ","84"=>" ","85"=>" ");
$finalArray = array();
$array_mapping_fields_keys=array();
$checkEmtpyColumn = array();
/******************/
 $rowNo = 1;
    if (($fp = fopen($f_source, "r")) !== FALSE) {
		 /*********Read Old file****/
        while (($rows = fgetcsv($fp, 10000, ",")) !== FALSE) {
            $num = count($rows);			
			if($rowNo==1){
				ksort($orocrm_fields_array);
				$headerFinalArray=$orocrm_fields_array;//array();
			  foreach($rows as $key=>$value)
			  {
				  if($value!="")
				  {
					 if(array_key_exists($value, $array_mapping_fields))
					 {
						 $orocrmMappedFieldValue=$array_mapping_fields[$value];
						$orocrmMappedFieldKey=array_keys($orocrm_fields_array,$orocrmMappedFieldValue,true);						
						$orocrmMappedFieldKey1 = $orocrmMappedFieldKey[0];				
						$array_mapping_fields_keys[$orocrmMappedFieldKey1]=$key;
					}					  
				  }
			  }
			  $finalArray[]=$headerFinalArray;
		  }
		  else{
			  $tempArray = $orocrm_fields_blank_array;
			  ksort($array_mapping_fields_keys);
			  	 foreach($array_mapping_fields_keys as $key=>$value) 
			  	 {
					// ksort($value);
					 if(array_key_exists($value, $rows))
					 {
						 $arrayValues = $rows[$value];
						 if($rows[$value]=="Unspecified") // For Gender issue
						 {
							 $arrayValues = "";
						 }
						 
						 if($key ==48 || $key ==64 || $key == 80) // short country name 
						 {
							 $arrayValues = "US";
						 }						 
						 $tempArray[$key] = $arrayValues;
						 if(empty($checkEmtpyColumn[$key]))
						 {
							 $checkEmtpyColumn[$key]=$arrayValues;
							 
						 }
					 }
					 
				 }
				 $tempArray["47"] = '';
				 $tempArray["63"] = '';
				 $tempArray["79"] = '';
				 if((isset($tempArray["46"]) && $tempArray["46"]!="") && (isset($tempArray["48"]) && $tempArray["48"]!=""))
				 {
					 $tempArray["47"] = $tempArray["48"]."-".$tempArray["46"];
					 if(empty($checkEmtpyColumn["47"]))
						 {
							 $checkEmtpyColumn["47"]= $tempArray["47"];
							 
						 }
				 }
				 if((isset($tempArray["62"]) && $tempArray["62"]!="") && (isset($tempArray["64"]) && $tempArray["64"]!=""))
				 {
					 $tempArray["63"] = $tempArray["64"]."-".$tempArray["62"];
					 if(empty($checkEmtpyColumn["63"]))
						 {
							 $checkEmtpyColumn["63"]= $tempArray["63"];
							 
						 }
				 }
				 if((isset($tempArray["78"]) && $tempArray["78"]!="") && (isset($tempArray["80"]) && $tempArray["80"]!=""))
				 {
					 $tempArray["79"] = $tempArray["80"]."-".$tempArray["78"];
					 if(empty($checkEmtpyColumn["79"]))
						 {
							 $checkEmtpyColumn["79"]= $tempArray["79"];
							 
						 }
				 }
				 ksort($tempArray);
				 $finalArray[]=$tempArray;
				
				 unset($tempArray);
		  }
		  $rowNo++;
        }
        fclose($fp);
        
        
       
        /*********Read Old file****/
		 $checkEmtpyColumn = array_diff($checkEmtpyColumn, array(""));
		 $checkEmtpyColumn = array_keys($checkEmtpyColumn);
 
        foreach ($finalArray as $keyParent=>$line)
		{
			foreach($line as $key=>$value)
			{
				if(!in_array($key,$checkEmtpyColumn))
				{
					unset($finalArray[$keyParent][$key]);
				}				
			}
		}

        /*********Create new file****/
        $fpwrite = fopen($f_target, "w");
 
		foreach ($finalArray as $line)
		{
			fputcsv(
				$fpwrite, // The file pointer
				$line, // The fields
				',' // The delimiter
			);      
		}
	   /*********Create new file****/
		fclose($fpwrite); 
    }exit;
/*****************/
