<?php
class ConnectionBitrix24{
    /**
     * Write data to log file.
     *
     * @param mixed $data
     * @param string $title
     *
     * @return bool
     */


    public static function writeToLog($data, $title = '') {
        $log = "\n------------------------\n";
        $log .= date("Y.m.d G:i:s") . "\n";
        $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
        $log .= print_r($data, 1);
        $log .= "\n------------------------\n";
        file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
        return true;
    }

    public static function ExecutionConn($queryData,$URL){
        $queryUrl = 'https://b24-mowx23.bitrix24.com.br/rest/1/0srzm0w5eq0gfwtm/'. $URL;
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $queryUrl,
        CURLOPT_POSTFIELDS => $queryData,
        ));
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    public static function AddContact($name,$phone,$email,$CPF){
        $URL="crm.contact.add"; 
    
        $data = http_build_query(array(
            'fields' => array(
                "NAME" => $name,
                "OPENED" => "Y",
                "PHONE" => array(array("VALUE" => $phone, "VALUE_TYPE" => "WORK")),
                "EMAIL" => array(array("VALUE" => $email, "VALUE_TYPE" => "WORK")),
                "UF_CRM_1592106833" => $CPF
            ),
            'params' => array("REGISTER_SONET_EVENT" => "Y")
            ));
        $result = ConnectionBitrix24::ExecutionConn($data,$URL);
        $result = json_decode($result, 1);
        ConnectionBitrix24::writeToLog($result, 'new contact');
    }

    public static function ListContact($CPF){
        $URL="crm.contact.list";     
        $data = http_build_query(array(
            'filter' => ["UF_CRM_1592106833" => $CPF],
            'select' => [ "ID"]
            ));
        $result = ConnectionBitrix24::ExecutionConn($data,$URL);
        $result = json_decode($result, 1);
        ConnectionBitrix24::writeToLog($result, 'checa se CPF já existe');
        
        if ($result['result']==NULL) {
            return NULL;
        }
        else{
            return $result['result'][0]['ID'];
       }
    }

    public static function UpdateContact($id,$name,$phone,$email){
        $Url = 'crm.contact.update';
        $data = http_build_query(array(
            'ID' => $id,
            'fields' => array(
            "NAME" => $name,
            "OPENED" =>"Y", 
            "PHONE" => array(array("VALUE" => $phone, "VALUE_TYPE" => "WORK" )),
            "EMAIL" => array(array("VALUE" => $email, "VALUE_TYPE" => "WORK" ))
        ),
        'params' => array("REGISTER_SONET_EVENT" => "Y")
        ));
        $result = ConnectionBitrix24::ExecutionConn($data,$Url);
        $result = json_decode($result, 1);
        ConnectionBitrix24:: writeToLog($result, 'Update contact');
    }
    
    public static function AddCompany($name_da_empresa,$CNPJ){
        $URL="crm.company.add"; 
        $data = http_build_query(array(
            'fields' => array(
                "TITLE" => $name_da_empresa,
                "OPENED" => "Y",
                "UF_CRM_1592106903" => $CNPJ
                ),
                'params' => array("REGISTER_SONET_EVENT" => "Y")
            ));
        $result = ConnectionBitrix24::ExecutionConn($data,$URL);
        $result = json_decode($result, 1);
        ConnectionBitrix24::writeToLog($result, 'new company');
    }

    public static function ListCompany($CNPJ){
        $URL="crm.company.list"; 
        $data = http_build_query(array(
            'filter' => ["UF_CRM_1592106903" => $CNPJ],
            'select' => ["ID"]
            ));        
        $result = ConnectionBitrix24::ExecutionConn($data,$URL);        
        $result = json_decode($result, 1);
        ConnectionBitrix24::writeToLog($result, 'checa se CNPJ já existe');

        if ($result['result']==NULL) {
            return NULL;
        }
        else{
            return $result['result'][0]['ID'];
       }
    }

    public static function UpdateCompany($id,$name_da_empresa){
        $Url = 'crm.company.update';
        $data = http_build_query(array(
            'ID' => $id,
            'fields' => array(
            "TITLE" =>  $name_da_empresa,
            ),
            'params' => array("REGISTER_SONET_EVENT" => "Y")
        ));
        $result = ConnectionBitrix24::ExecutionConn($data,$Url);
        $result = json_decode($result, 1);
        ConnectionBitrix24:: writeToLog($result, 'Update em company');
    }

    public static function AddCompanyContact($company_id,$contact_id){
        $Url = 'crm.company.contact.add';
        $data = http_build_query(array(
            'ID' => $company_id,
            'fields' => array(
                "CONTACT_ID" => $contact_id
                )
        ));
        $result = ConnectionBitrix24::ExecutionConn($data,$Url);
        $result = json_decode($result, 1);
        ConnectionBitrix24:: writeToLog($result, 'add a contact to the specified company');
    }

    public static function GET_Deal($array){
        $Url = 'crm.deal.get';        
        $data = http_build_query(array(
            'ID' => $array["data"]['FIELDS']["ID"]
        ));
        $result = ConnectionBitrix24::ExecutionConn($data,$Url);
        $result = json_decode($result, 1);
        ConnectionBitrix24:: writeToLog($result, 'GET deals');

        if($result['result']['STAGE_ID'] == 'WON'){
            ConnectionBitrix24::WON_Deal($result);
        }
    }

    public static function WON_Deal($array){
        $Deal = $array['result']['OPPORTUNITY'];
        $ID_Company = $array['result']['COMPANY_ID'];
        $SaldoAtual = ConnectionBitrix24::ListCompanyBank($ID_Company);
        $NovoSaldo = $SaldoAtual + $Deal;
        ConnectionBitrix24::UpdateCompanyWON($ID_Company,$NovoSaldo);
    }


    public static function ListCompanyBank($ID_Company){
        $URL="crm.company.list"; 
        $data = http_build_query(array(
            'filter' => ["ID" => $ID_Company],
            'select' => ["UF_CRM_1592205695"]
        ));
        
        $result = ConnectionBitrix24::ExecutionConn($data,$URL);        
        $result = json_decode($result, 1);
        ConnectionBitrix24:: writeToLog($result, 'list company');

        if ($result['result']==NULL) {
            return 0;
        }
        else{
            return $result['result'][0]['UF_CRM_1592205695'];
       }
    }
    
    public static function UpdateCompanyWON($ID_Company,$NovoSaldo){
        $Url = 'crm.company.update';
        $data = http_build_query(array(
            'ID' => $ID_Company,
            'fields' => array(
            "UF_CRM_1592205695" => $NovoSaldo,
        ),
        'params' => array("REGISTER_SONET_EVENT" => "Y")
        ));
        $result = ConnectionBitrix24::ExecutionConn($data,$Url);
        $result = json_decode($result, 1);
        ConnectionBitrix24:: writeToLog($result, 'Update no saldo');
    }
}