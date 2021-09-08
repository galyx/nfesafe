<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function novaEmpresa(Request $request)
    {
        return response()->json($request->all());
    }

    
    // public function leCertificado(Request $request)
    // {
    //     //Caminho do Certificado

    //     $certs = array ();
    //     $pkcs12 = $request->file('certificate')->getContent();
    //     if( openssl_pkcs12_read($pkcs12, $certs, '1234') ){
            
    //         $dados = array ();
    //         $dados = openssl_x509_parse( openssl_x509_read($certs['cert']) );

    //         //print_r( $dados );
            
    //         //Dados mais importantes
    //         echo $dados['subject']['C'].'<br>'; //País
    //         echo $dados['subject']['ST'].'<br>'; //Estado
    //         echo $dados['subject']['L'].'<br>'; //Município
    //         echo $dados['subject']['CN'].'<br>'; //Razão Social e CNPJ / CPF
    //         echo date('d/m/Y', $dados['validTo_time_t'] ).'<br>';//Validade	
    //         echo $dados['extensions']['subjectAltName'].'<br>';	//Emails Cadastrados separado por ,

    //     }

    //     return response()->json();
    // }
}
