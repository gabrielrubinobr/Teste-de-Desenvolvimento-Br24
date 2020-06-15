<?php
require_once ('../Model/connectionBitrix24.php');

$CheckID_Company = ConnectionBitrix24::ListCompany($_REQUEST['CNPJ']);
$CheckID_Contact = ConnectionBitrix24::ListContact($_REQUEST['CPF']);
if($CheckID_Company == NULL && $CheckID_Contact == NULL){
    ConnectionBitrix24::AddCompany($_REQUEST['name_da_empresa'],$_REQUEST['CNPJ']);
    ConnectionBitrix24::AddContact($_REQUEST['name'], $_REQUEST['phone'], $_REQUEST['email'],$_REQUEST['CPF']);
    
    $CheckID_Company = ConnectionBitrix24::ListCompany($_REQUEST['CNPJ']);
    $CheckID_Contact = ConnectionBitrix24::ListContact($_REQUEST['CPF']);
    ConnectionBitrix24::AddCompanyContact($CheckID_Company,$CheckID_Contact);
    echo '<meta http-equiv="refresh" content="1; url=../View/CadastroRealizado.html">';
}

elseif($CheckID_Company == NULL && $CheckID_Contact != NULL){
    ConnectionBitrix24::AddCompany($_REQUEST['name_da_empresa'],$_REQUEST['CNPJ']);
    ConnectionBitrix24::UpdateContact($CheckID_Contact,$_REQUEST['name'], $_REQUEST['phone'], $_REQUEST['email']);
    
    $CheckID_Company = ConnectionBitrix24::ListCompany($_REQUEST['CNPJ']);
    ConnectionBitrix24::AddCompanyContact($CheckID_Company,$CheckID_Contact);
    echo '<meta http-equiv="refresh" content="1; url=../View/CadastroRealizado.html">';
}

elseif($CheckID_Company != NULL && $CheckID_Contact == NULL){
    ConnectionBitrix24::UpdateCompany($CheckID_Company,$_REQUEST['name_da_empresa']);
    ConnectionBitrix24::AddContact($_REQUEST['name'], $_REQUEST['phone'], $_REQUEST['email'],$_REQUEST['CPF']);
    
    $CheckID_Contact = ConnectionBitrix24::ListContact($_REQUEST['CPF']);
    ConnectionBitrix24::AddCompanyContact($CheckID_Company,$CheckID_Contact);
    echo '<meta http-equiv="refresh" content="1; url=../View/CadastroRealizado.html">';
}


else{
    ConnectionBitrix24::UpdateContact($CheckID_Contact,$_REQUEST['name'], $_REQUEST['phone'], $_REQUEST['email']);
    ConnectionBitrix24::UpdateCompany($CheckID_Company,$_REQUEST['name_da_empresa']);

    ConnectionBitrix24::AddCompanyContact($CheckID_Company,$CheckID_Contact);
    echo '<meta http-equiv="refresh" content="1; url=../View/CadastroAtualizado.html">';

}
