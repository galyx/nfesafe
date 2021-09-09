<?php

namespace App\Http\Controllers;

use App\Models\Company;

use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function novaEmpresa(Request $request)
    {
        $request->validate([
            'certificate' => 'required|file',
            'certificate_pass' => 'required|string',
            'cnpj' => 'required|string',
            'corporate_name' => 'required|string',
            'fantasy_name' => 'required|string',
            'post_code' => 'required|string',
            'address' => 'required|string',
            'number' => 'required|string',
            'address2' => 'required|string',
            'state' => 'required|string',
            'city' => 'required|string',
        ]);

        $pkcs12 = $request->file('certificate')->getContent();

        if(openssl_pkcs12_read($pkcs12, $certs, $request->certificate_pass)){
            $dados = openssl_x509_parse(openssl_x509_read($certs['cert']));

            if(date('Y-m-d H:i:s') >= date('Y-m-d H:i:s', $dados['validTo_time_t'])){
                return response()->json(['icon_alert' => 'error', 'msg_alert' => 'Certificado Vencido!'],412);
            }
        }else{
            return response()->json(['icon_alert' => 'error', 'msg_alert' => 'Senha do Certificado incorreta!'],412);
        }

        $company = Company::create([
            'user_id' => '1',
            'cnpj' => $request->cnpj,
            'corporate_name' => mb_convert_case($request->corporate_name, MB_CASE_UPPER, 'UTF-8'),
            'fantasy_name' => mb_convert_case($request->fantasy_name, MB_CASE_UPPER, 'UTF-8'),
            'address' => $request->address,
            'number' => $request->number,
            'address2' => $request->address2,
            'complement' => $request->complement,
            'city' => $request->city,
            'state' => $request->state,
            'post_code' => $request->post_code,
            'phone1' => $request->phone1,
            'phone2' => $request->phone2,
            'certificate' => base64_encode($pkcs12),
            'validate_certificate' => date('Y-m-d H:i:s', $dados['validTo_time_t']),
            'password' => $request->certificate_pass,
            'active' => 'S',
        ]);

        return response()->json([
            'table' => '<tr class="tr-id-'.$company->id.'">
                <td>#'.$company->id.'</td>
                <td>'.$company->cnpj.'</td>
                <td>'.$company->corporate_name.'</td>
                <td>'.$company->fantasy_name.'</td>
                <td>'.$company->address.', Nº '.$company->number.'</td>
                <td>'.$company->address2.'</td>
                <td>'.$company->city.'</td>
                <td>'.$company->state.'</td>
                <td>'.$company->post_code.'</td>
                <td>'.date('d/m/Y H:i:s', strtotime(str_replace('-','/',$company->validate_certificate))).'</td>
                <td>
                    <div class="btn-group" role="group" aria-label="">
                        <a href="#" class="btn btn-info btn-xs btn-editar" data-dados=\''.json_encode($company).'\'><i class="fas fa-edit"></i> Alterar</a>
                        <a href="#" class="btn btn-danger btn-xs btn-excluir" data-id="'.$company->id.'"><i class="fas fa-trash"></i> Apagar</a>
                    </div>
                </td>
            </tr>'
        ]);
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
