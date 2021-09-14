<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use NFePHP\NFe\Tools as nfe;
use NFePHP\NFe\Common\Standardize as standNfe;

use NFePHP\CTe\Tools as cte;
use NFePHP\CTe\Common\Standardize as standCte;

use NFePHP\Common\Certificate;

use App\Models\LastNsu;
use App\Models\Company;
use App\Models\DataCompany;
use App\Models\DocKey;
use App\Models\DocXml;

class SefazController extends Controller
{
    public function downloadNFE($company_id, $user_id)
    {
        ini_set('max_execution_time', 10000);
        ini_set('memory_limit','32192M');

        // Dados da empresa para baixar os dados
        $company = Company::where('id', $company_id)->where('user_id', $user_id)->first();
        // Dados do ultimo NSU
        $lastnsu = LastNsu::where('company_id', $company_id)->where('user_id', $user_id)->first();

        $lastNSU = $lastnsu->last_nsu_nfe; // Ultima NSU

        $arr = [
            "atualizacao" => "2021-01-01 00:00:00",
            "tpAmb" => 1,
            "razaosocial" => $company->corporate_name,
            "cnpj" => $company->cnpj,
            "siglaUF" => $company->state,
            "schemes" => "PL_009_V4",
            "versao" => '4.00',
        ];
        $configJson = json_encode($arr); // Transformando em JSON os dados que precisa mandar
        // $pfxcontent = file_get_contents('./certificados/certificado.pfx'); // Dados do certificado
        $pfxcontent = $company; // Dados do certificado

        $tools = new nfe($configJson, Certificate::readPfx($pfxcontent, '1234')); // Mandando os dado para o sefaz
        $tools->model('55'); //Informando que é o Modelo 55

        $lastNSU = $lastNSU; // Ultimo NSU para fazer o while funcionar
        $maxNSU = intval($lastNSU+1); // Pego o Ultimo e adiciono +1 para funcionar o while
        $loopLimit = 2; // Essa variavel não é importante, somente para limitar a baixa das nfes
        $iCount = 0; // Essa varivel é conjunto do loopLimit
        $total_doc_baixado = 0; // Total de Documento baixados
        $sleepLimit = 0; // O sleepLimit serve para dar um tempo na busca dos dados do cnpj
        while ($lastNSU < $maxNSU){
            // $iCount++; // Variavel contando para quebrar o loop com o if em seguida
            // if ($iCount >= $loopLimit) { // #-----------
            //     break;
            // }

            $lastNSU = str_pad($lastNSU, 15, '0', STR_PAD_LEFT); // O sistema precisa ter no total 15 digitos

            try {
                $response = $tools->sefazDistDFe($lastNSU); // Mandamos o ultimo NSU para o sefaz e liberar baixar os dados
            } catch (\Exception $e) {
                echo $e->getMessage();
            }

            //extrair e salvar os retornos
            $dom = new \DOMDocument();
            $dom->loadXML($response);
            $node       = $dom->getElementsByTagName('retDistDFeInt')->item(0);
            $tpAmb      = $node->getElementsByTagName('tpAmb')->item(0)->nodeValue;
            $verAplic   = $node->getElementsByTagName('verAplic')->item(0)->nodeValue;
            $cStat      = $node->getElementsByTagName('cStat')->item(0)->nodeValue;
            $xMotivo    = $node->getElementsByTagName('xMotivo')->item(0)->nodeValue;
            $dhResp     = $node->getElementsByTagName('dhResp')->item(0)->nodeValue;
            $lastNSU    = $node->getElementsByTagName('ultNSU')->item(0)->nodeValue;
            $maxNSU     = $node->getElementsByTagName('maxNSU')->item(0)->nodeValue;
            $lote       = $node->getElementsByTagName('loteDistDFeInt')->item(0);
            if($cStat == '656') print_r($xMotivo); // Somente para fins de teste;
            if (empty($lote)) {
                //lote vazio
                continue;
            }
            //essas tags irão conter os documentos zipados
            $docs = $lote->getElementsByTagName('docZip');
            foreach ($docs as $doc){
                if($sleepLimit == 3){
                    $sleepLimit = 0;
                    $sleep = 60;
                }else{
                    $sleep = 0;
                }
                $total_doc_baixado++; // soma os docuemntos baixados
                $docXml = [];
                $numnsu = $doc->getAttribute('NSU');
                $schema = $doc->getAttribute('schema');

                $lastnsu->update(['last_nsu_nfe' => $numnsu]);

                //descompacta o documento e recupera o XML original
                $content = gzdecode(base64_decode($doc->nodeValue));
                $stdCl = new standNfe($content);
                $arr   = $stdCl->toArray();
                // echo "<pre>";
                // print_r($arr);
                // echo "</pre>";
                // header('Content-type: text/xml; charset=UTF-8');
                $xml_received           = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>".$content;

                $doc_key                = isset($arr['chNFe'])      ? $arr['chNFe']         : '';
                $organ_code             = isset($arr['cOrgao'])     ? $arr['cOrgao']        : '';
                $issue_date             = isset($arr['dhEvento'])   ? $arr['dhEvento']      : '';
                $receipt_date           = isset($arr['dhRecbto'])   ? $arr['dhRecbto']      : '';
                $protocol_number        = isset($arr['nProt'])      ? $arr['nProt']         : '';
                $event_sequence         = isset($arr['nSeqEvento']) ? $arr['nSeqEvento']    : '';
                $event_type             = isset($arr['tpEvento'])   ? $arr['tpEvento']      : '';
                $event_description      = isset($arr['xEvento'])    ? $arr['xEvento']       : '';
                $event_time_date        = isset($arr['dhEvento'])   ? $arr['dhEvento']      : '';
                $cnpj                   = isset($arr['CNPJ'])       ? $arr['CNPJ']          : '';

                if(isset($arr['retEvento'])){
                    $doc_key                = $arr['retEvento']['infEvento']['chNFe'];
                    $organ_code             = $arr['retEvento']['infEvento']['cOrgao'];
                    $issue_date             = $arr['retEvento']['infEvento']['dhRegEvento'];
                    $receipt_date           = $arr['retEvento']['infEvento']['dhRegEvento'];
                    $note_status            = $arr['retEvento']['infEvento']['cStat'];
                    $reason                 = $arr['retEvento']['infEvento']['xMotivo'];
                    $protocol_number        = $arr['retEvento']['infEvento']['nProt'];
                    $event_sequence         = $arr['retEvento']['infEvento']['nSeqEvento'];
                    $app_version            = $arr['retEvento']['infEvento']['verAplic'];
                    $event_type             = $arr['retEvento']['infEvento']['tpEvento'];
                    $event_description      = $arr['retEvento']['infEvento']['xEvento'] ?? '';
                    $event_time_date        = $arr['retEvento']['infEvento']['dhRegEvento'];
                    $cnpj                   = $arr['retEvento']['infEvento']['CNPJDest'] ?? '';
                }
                if(isset($arr['NFe'])){
                    $doc_key                = $arr['protNFe']['infProt']['chNFe'];
                    $receipt_date           = $arr['protNFe']['infProt']['dhRecbto'];
                    $protocol_number        = $arr['protNFe']['infProt']['nProt'];
                    $app_version            = $arr['protNFe']['infProt']['verAplic'];
                    $note_status            = $arr['protNFe']['infProt']['cStat'];
                    $reason                 = $arr['protNFe']['infProt']['xMotivo'];
                    $issue_date             = $arr['NFe']['infNFe']['ide']['dhEmi'];
                    $event_time_date        = $arr['NFe']['infNFe']['ide']['dhEmi'];
                    $amount                 = $arr['NFe']['infNFe']['total']['ICMSTot']['vNF'];
                    $event_type             = '210210';
                    $event_description      = 'Ciencia da Operacao';

                    $issue_cnpj             = $arr['NFe']['infNFe']['emit']['CNPJ'];
                    $issue_name             = $arr['NFe']['infNFe']['emit']['xNome'];
                    $issue_state            = $arr['NFe']['infNFe']['emit']['enderEmit']['UF'];
                    $recipient_cnpj         = $arr['NFe']['infNFe']['dest']['CNPJ'];
                    $recipient_name         = $arr['NFe']['infNFe']['dest']['xNome'];
                    $recipient_state        = $arr['NFe']['infNFe']['dest']['enderDest']['UF'];
                }

                if(empty($issuer_cnpj)){
                    $issuer_state           = $doc_key[0].$doc_key[1];
                    $issuer_cnpj            = $doc_key[6].$doc_key[7].$doc_key[8].$doc_key[9].$doc_key[10].$doc_key[11].$doc_key[12].$doc_key[13].$doc_key[14].$doc_key[15].$doc_key[16].$doc_key[17].$doc_key[18].$doc_key[19];

                    if($issuer_cnpj == $company->cnpj){
                        $issuer_name     = $company->corporate_name;
                        $issuer_state    = $company->state;
                    }elseif($issuer_cnpj !== $company->cnpj){
                        $data_company = DataCompany::where('cnpj', $issuer_cnpj)->get();

                        if($data_company->count() > 0){
                            $data_company   = $data_company->first();
                            $issuer_name     = $data_company->corporate_name;
                            $issuer_state    = $data_company->state;
                        }else{
                            $sleepLimit++;
                            $cnpj_query     = $this->consultaCNPJ($issuer_cnpj);
                            $issuer_name     = $cnpj_query->nome ?? '';
                            $issuer_state    = $cnpj_query->uf ?? '';

                            if(!empty($issuer_name)){
                                DataCompany::create([
                                    'cnpj'              => $issuer_cnpj,
                                    'corporate_name'    => $cnpj_query->nome,
                                    'fantasy_name'      => $cnpj_query->fantasia,
                                    'address'           => $cnpj_query->logradouro,
                                    'number'            => $cnpj_query->numero,
                                    'adrres2'           => $cnpj_query->bairro,
                                    'city'              => $cnpj_query->municipio,
                                    'state'             => $cnpj_query->uf,
                                    'post_code'         => str_replace(['.','-'],'',$cnpj_query->cep),
                                    'ie'                => 'none',
                                ]);
                            }
                        }
                    }
                }

                if(empty($recipient_cnpj)){
                    if($issuer_cnpj !== $cnpj){
                        $recipient_cnpj = $cnpj;

                        if($recipient_cnpj == $company->cnpj){
                            $recipient_name     = $company->corporate_name;
                            $recipient_state    = $company->state;
                        }elseif($recipient_cnpj !== $company->cnpj){
                            $data_company = DataCompany::where('cnpj', $recipient_cnpj)->get();
    
                            if($data_company->count() > 0){
                                $data_company       = $data_company->first();
                                $recipient_name     = $data_company->corporate_name;
                                $recipient_state    = $data_company->state;
                            }else{
                                $sleepLimit++;
                                $cnpj_query         = $this->consultaCNPJ($recipient_cnpj);
                                $recipient_name     = $cnpj_query->nome ?? '';
                                $recipient_state    = $cnpj_query->uf ?? '';
    
                                if(!empty($recipient_name)){
                                    DataCompany::create([
                                        'cnpj'              => $recipient_cnpj,
                                        'corporate_name'    => $cnpj_query->nome,
                                        'fantasy_name'      => $cnpj_query->fantasia,
                                        'address'           => $cnpj_query->logradouro,
                                        'number'            => $cnpj_query->numero,
                                        'adrres2'           => $cnpj_query->bairro,
                                        'city'              => $cnpj_query->municipio,
                                        'state'             => $cnpj_query->uf,
                                        'post_code'         => str_replace(['.','-'],'',$cnpj_query->cep),
                                        'ie'                => 'none',
                                    ]);
                                }
                            }
                        }
                    }
                }

                $document_template              = $doc_key[20].$doc_key[21];
                $grade_series                   = intval($doc_key[22].$doc_key[23].$doc_key[24]);
                $note_number                    = intval($doc_key[25].$doc_key[26].$doc_key[27].$doc_key[28].$doc_key[29].$doc_key[30].$doc_key[31].$doc_key[32].$doc_key[33]);

                $docXml['user_id']              = '1';
                $docXml['company_id']           = '1';
                $docXml['doc_key']              = $doc_key;
                $docXml['issuer_cnpj']          = $issuer_cnpj;
                $docXml['issuer_name']          = isset($issuer_name)       ? $issuer_name      : '';
                $docXml['issuer_state']         = isset($issuer_state)      ? $issuer_state     : '';
                $docXml['recipient_cnpj']       = isset($recipient_cnpj)    ? $recipient_cnpj   : '';
                $docXml['recipient_name']       = isset($recipient_name)    ? $recipient_name   : '';
                $docXml['recipient_state']      = isset($recipient_state)   ? $recipient_state  : '';
                $docXml['document_template']    = $document_template;
                $docXml['grade_series']         = $grade_series;
                $docXml['note_number']          = $note_number;
                $docXml['issue_date']           = isset($issue_date)        ? date('Y-m-d', strtotime($issue_date)) : '';
                $docXml['amount']               = isset($amount)            ? (float)$amount    : 0.00;

                // Verificando se ja tenho a chave da nfe
                $dockey = DocKey::where('user_id', $user_id)->where('company_id', $company_id)->where('doc_key', $doc_key)->get();
                if($dockey->count() == 0){
                    DocKey::create($docXml);
                }else{
                    $dockey->first()->update($docXml);
                }

                $docXml['nsu']                  = $numnsu;
                $docXml['event_type']           = isset($event_type)        ? $event_type           : '';
                $docXml['event_sequence']       = isset($event_sequence)    ? $event_sequence       : '';
                $docXml['note_status']          = isset($note_status)       ? $note_status          : '';
                $docXml['reason']               = isset($reason)            ? $reason               : '';
                $docXml['protocol_number']      = isset($protocol_number)   ? $protocol_number      : '';
                $docXml['receipt_date']         = isset($receipt_date)      ? date('Y-m-d', strtotime($receipt_date))         : '';
                $docXml['organ_code']           = isset($organ_code)        ? $organ_code           : '';
                $docXml['app_version']          = isset($app_version)       ? $app_version          : '';
                $docXml['event_description']    = isset($event_description) ? $event_description    : '';
                $docXml['event_time_date']      = isset($event_time_date)   ? date('Y-m-d H:i:s', strtotime($event_time_date)) : '';
                $docXml['xml_received']         = isset($xml_received)      ? $xml_received         : '';

                // Verificando se ja tenho a chave da nfe com nsu
                $docxml = DocXml::where('user_id', $user_id)->where('company_id', $company_id)->where('doc_key', $doc_key)->where('nsu', $numnsu)->get();
                if($docxml->count() == 0){
                    DocXml::create($docXml);
                }

                // Limpando as variaveis
                $doc_key = $organ_code = $issue_date = $receipt_date = $note_status = $reason = $protocol_number = $event_sequence = $app_version = $event_type = $event_description = $event_time_date = $cnpj = $amount =$issue_cnpj = $issue_name = $issue_state = $recipient_cnpj = $recipient_name = $recipient_state = '';

                sleep($sleep);
                $issuer_cnpj = $issuer_name = $issuer_state = $recipient_cnpj = $recipient_name = $recipient_state = $amount = '';
            }

            if($lastNSU == $maxNSU) break; //quebrando o processo
            sleep(2);
        }

        return true;
    }

    public function downloadCTE($company_id, $user_id)
    {
        ini_set('max_execution_time', 10000);
        ini_set('memory_limit','32192M');

        // Dados da empresa para baixar os dados
        $company = Company::where('id', $company_id)->where('user_id', $user_id)->first();
        // Dados do ultimo NSU
        $lastnsu = LastNsu::where('company_id', $company_id)->where('user_id', $user_id)->first();

        $lastNSU = $lastnsu->last_nsu_cte; // Ultima NSU

        $arr = [
            "atualizacao" => "2021-01-01 00:00:00",
            "tpAmb" => 1,
            "razaosocial" => $company->corporate_name,
            "cnpj" => $company->cnpj,
            "siglaUF" => $company->state,
            "schemes" => "PL_CTe_300",
            "versao" => '3.00',
        ];
        $configJson = json_encode($arr); // Transformando em JSON os dados que precisa mandar
        $pfxcontent = file_get_contents('./certificados/certificado.pfx'); // Dados do certificado

        $tools = new cte($configJson, Certificate::readPfx($pfxcontent, '1234')); // Mandando os dado para o sefaz

        $lastNSU = $lastNSU; // Ultimo NSU para fazer o while funcionar
        $maxNSU = intval($lastNSU+1); // Pego o Ultimo e adiciono +1 para funcionar o while
        $loopLimit = 2; // Essa variavel não é importante, somente para limitar a baixa das nfes
        $iCount = 0; // Essa varivel é conjunto do loopLimit
        $total_doc_baixado = 0; // Total de Documento baixados
        $sleepLimit = 0; // O sleepLimit serve para dar um tempo na busca dos dados do cnpj
        while ($lastNSU < $maxNSU){
            // $iCount++; // Variavel contando para quebrar o loop com o if em seguida
            // if ($iCount >= $loopLimit) { // #-----------
            //     break;
            // }

            $lastNSU = str_pad($lastNSU, 15, '0', STR_PAD_LEFT); // O sistema precisa ter no total 15 digitos

            try {
                $response = $tools->sefazDistDFe($lastNSU); // Mandamos o ultimo NSU para o sefaz e liberar baixar os dados
            } catch (\Exception $e) {
                echo $e->getMessage();
            }

            //extrair e salvar os retornos
            $dom = new \DOMDocument();
            $dom->loadXML($response);
            $node       = $dom->getElementsByTagName('retDistDFeInt')->item(0);
            $tpAmb      = $node->getElementsByTagName('tpAmb')->item(0)->nodeValue;
            $verAplic   = $node->getElementsByTagName('verAplic')->item(0)->nodeValue;
            $cStat      = $node->getElementsByTagName('cStat')->item(0)->nodeValue;
            $xMotivo    = $node->getElementsByTagName('xMotivo')->item(0)->nodeValue;
            $dhResp     = $node->getElementsByTagName('dhResp')->item(0)->nodeValue;
            $lastNSU    = $node->getElementsByTagName('ultNSU')->item(0)->nodeValue;
            $maxNSU     = $node->getElementsByTagName('maxNSU')->item(0)->nodeValue;
            $lote       = $node->getElementsByTagName('loteDistDFeInt')->item(0);
            if($cStat == '656') print_r($xMotivo); // Somente para fins de teste;
            if (empty($lote)) {
                //lote vazio
                continue;
            }
            //essas tags irão conter os documentos zipados
            $docs = $lote->getElementsByTagName('docZip');
            foreach ($docs as $doc){
                if($sleepLimit == 3){
                    $sleepLimit = 0;
                    $sleep = 60;
                }else{
                    $sleep = 0;
                }
                $total_doc_baixado++; // soma os docuemntos baixados
                $docXml = [];
                $numnsu = $doc->getAttribute('NSU');
                $schema = $doc->getAttribute('schema');

                $lastnsu->update(['last_nsu_cte' => $numnsu]);

                //descompacta o documento e recupera o XML original
                $content = gzdecode(base64_decode($doc->nodeValue));
                $stdCl = new standCte($content);
                $arr   = $stdCl->toArray();
                // echo "<pre>";
                // print_r($arr);
                // echo "</pre>";
                // header('Content-type: text/xml; charset=UTF-8');
                $xml_received           = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>".$content;

                $doc_key                = isset($arr['chCTe'])      ? $arr['chCTe']         : '';
                $organ_code             = isset($arr['cOrgao'])     ? $arr['cOrgao']        : '';
                $issue_date             = isset($arr['dhEvento'])   ? $arr['dhEvento']      : '';
                $receipt_date           = isset($arr['dhRecbto'])   ? $arr['dhRecbto']      : '';
                $protocol_number        = isset($arr['nProt'])      ? $arr['nProt']         : '';
                $event_sequence         = isset($arr['nSeqEvento']) ? $arr['nSeqEvento']    : '';
                $event_type             = isset($arr['tpEvento'])   ? $arr['tpEvento']      : '';
                $event_description      = isset($arr['xEvento'])    ? $arr['xEvento']       : '';
                $event_time_date        = isset($arr['dhEvento'])   ? $arr['dhEvento']      : '';
                $cnpj                   = isset($arr['CNPJ'])       ? $arr['CNPJ']          : '';

                if(isset($arr['retEventoCTe'])){
                    $doc_key                = $arr['retEventoCTe']['infEvento']['chCTe'];
                    $organ_code             = $arr['retEventoCTe']['infEvento']['cOrgao'];
                    $issue_date             = $arr['retEventoCTe']['infEvento']['dhRegEvento'];
                    $receipt_date           = $arr['retEventoCTe']['infEvento']['dhRegEvento'];
                    $note_status            = $arr['retEventoCTe']['infEvento']['cStat'];
                    $reason                 = $arr['retEventoCTe']['infEvento']['xMotivo'];
                    $protocol_number        = $arr['retEventoCTe']['infEvento']['nProt'];
                    $event_sequence         = $arr['retEventoCTe']['infEvento']['nSeqEvento'];
                    $app_version            = $arr['retEventoCTe']['infEvento']['verAplic'];
                    $event_type             = $arr['retEventoCTe']['infEvento']['tpEvento'];
                    $event_description      = $arr['retEventoCTe']['infEvento']['xEvento'] ?? '';
                    $event_time_date        = $arr['retEventoCTe']['infEvento']['dhRegEvento'];
                    $cnpj                   = $arr['retEventoCTe']['infEvento']['CNPJDest'] ?? '';
                }
                if(isset($arr['CTe'])){
                    $doc_key                = $arr['protCTe']['infProt']['chCTe'];
                    $receipt_date           = $arr['protCTe']['infProt']['dhRecbto'];
                    $protocol_number        = $arr['protCTe']['infProt']['nProt'];
                    $app_version            = $arr['protCTe']['infProt']['verAplic'];
                    $note_status            = $arr['protCTe']['infProt']['cStat'];
                    $reason                 = $arr['protCTe']['infProt']['xMotivo'];
                    $issue_date             = $arr['CTe']['infCte']['ide']['dhEmi'];
                    $event_time_date        = $arr['CTe']['infCte']['ide']['dhEmi'];
                    $amount                 = $arr['CTe']['infCte']['vPrest']['vTPrest'];
                    $event_type             = '210210';
                    $event_description      = 'Ciencia da Operacao';

                    $issue_cnpj             = $arr['CTe']['infCte']['emit']['CNPJ'];
                    $issue_name             = $arr['CTe']['infCte']['emit']['xNome'];
                    $issue_state            = $arr['CTe']['infCte']['emit']['enderEmit']['UF'];
                    $recipient_cnpj         = $arr['CTe']['infCte']['dest']['CNPJ'];
                    $recipient_name         = $arr['CTe']['infCte']['dest']['xNome'];
                    $recipient_state        = $arr['CTe']['infCte']['dest']['enderDest']['UF'];
                }

                if(empty($issuer_cnpj)){
                    $issuer_state           = $doc_key[0].$doc_key[1];
                    $issuer_cnpj            = $doc_key[6].$doc_key[7].$doc_key[8].$doc_key[9].$doc_key[10].$doc_key[11].$doc_key[12].$doc_key[13].$doc_key[14].$doc_key[15].$doc_key[16].$doc_key[17].$doc_key[18].$doc_key[19];

                    if($issuer_cnpj == $company->cnpj){
                        $issuer_name     = $company->corporate_name;
                        $issuer_state    = $company->state;
                    }elseif($issuer_cnpj !== $company->cnpj){
                        $data_company = DataCompany::where('cnpj', $issuer_cnpj)->get();

                        if($data_company->count() > 0){
                            $data_company   = $data_company->first();
                            $issuer_name     = $data_company->corporate_name;
                            $issuer_state    = $data_company->state;
                        }else{
                            $sleepLimit++;
                            $cnpj_query     = $this->consultaCNPJ($issuer_cnpj);
                            $issuer_name     = $cnpj_query->nome ?? '';
                            $issuer_state    = $cnpj_query->uf ?? '';

                            if(!empty($issuer_name)){
                                DataCompany::create([
                                    'cnpj'              => $issuer_cnpj,
                                    'corporate_name'    => $cnpj_query->nome,
                                    'fantasy_name'      => $cnpj_query->fantasia,
                                    'address'           => $cnpj_query->logradouro,
                                    'number'            => $cnpj_query->numero,
                                    'adrres2'           => $cnpj_query->bairro,
                                    'city'              => $cnpj_query->municipio,
                                    'state'             => $cnpj_query->uf,
                                    'post_code'         => str_replace(['.','-'],'',$cnpj_query->cep),
                                    'ie'                => 'none',
                                ]);
                            }
                        }
                    }
                }

                if(empty($recipient_cnpj)){
                    if($issuer_cnpj !== $cnpj){
                        $recipient_cnpj = $cnpj;

                        if($recipient_cnpj == $company->cnpj){
                            $recipient_name     = $company->corporate_name;
                            $recipient_state    = $company->state;
                        }elseif($recipient_cnpj !== $company->cnpj){
                            $data_company = DataCompany::where('cnpj', $recipient_cnpj)->get();
    
                            if($data_company->count() > 0){
                                $data_company       = $data_company->first();
                                $recipient_name     = $data_company->corporate_name;
                                $recipient_state    = $data_company->state;
                            }else{
                                $sleepLimit++;
                                $cnpj_query         = $this->consultaCNPJ($recipient_cnpj);
                                $recipient_name     = $cnpj_query->nome ?? '';
                                $recipient_state    = $cnpj_query->uf ?? '';
    
                                if(!empty($recipient_name)){
                                    DataCompany::create([
                                        'cnpj'              => $recipient_cnpj,
                                        'corporate_name'    => $cnpj_query->nome,
                                        'fantasy_name'      => $cnpj_query->fantasia,
                                        'address'           => $cnpj_query->logradouro,
                                        'number'            => $cnpj_query->numero,
                                        'adrres2'           => $cnpj_query->bairro,
                                        'city'              => $cnpj_query->municipio,
                                        'state'             => $cnpj_query->uf,
                                        'post_code'         => str_replace(['.','-'],'',$cnpj_query->cep),
                                        'ie'                => 'none',
                                    ]);
                                }
                            }
                        }
                    }
                }

                $document_template              = $doc_key[20].$doc_key[21];
                $grade_series                   = intval($doc_key[22].$doc_key[23].$doc_key[24]);
                $note_number                    = intval($doc_key[25].$doc_key[26].$doc_key[27].$doc_key[28].$doc_key[29].$doc_key[30].$doc_key[31].$doc_key[32].$doc_key[33]);

                $docXml['user_id']              = '1';
                $docXml['company_id']           = '1';
                $docXml['doc_key']              = $doc_key;
                $docXml['issuer_cnpj']          = $issuer_cnpj;
                $docXml['issuer_name']          = isset($issuer_name)       ? $issuer_name      : '';
                $docXml['issuer_state']         = isset($issuer_state)      ? $issuer_state     : '';
                $docXml['recipient_cnpj']       = isset($recipient_cnpj)    ? $recipient_cnpj   : '';
                $docXml['recipient_name']       = isset($recipient_name)    ? $recipient_name   : '';
                $docXml['recipient_state']      = isset($recipient_state)   ? $recipient_state  : '';
                $docXml['document_template']    = $document_template;
                $docXml['grade_series']         = $grade_series;
                $docXml['note_number']          = $note_number;
                $docXml['issue_date']           = isset($issue_date)        ? date('Y-m-d', strtotime($issue_date)) : '';
                $docXml['amount']               = isset($amount)            ? (float)$amount    : 0.00;

                // Verificando se ja tenho a chave da nfe
                $dockey = DocKey::where('user_id', $user_id)->where('company_id', $company_id)->where('doc_key', $doc_key)->get();
                if($dockey->count() == 0){
                    DocKey::create($docXml);
                }else{
                    $dockey->first()->update($docXml);
                }

                $docXml['nsu']                  = $numnsu;
                $docXml['event_type']           = isset($event_type)        ? $event_type           : '';
                $docXml['event_sequence']       = isset($event_sequence)    ? $event_sequence       : '';
                $docXml['note_status']          = isset($note_status)       ? $note_status          : '';
                $docXml['reason']               = isset($reason)            ? $reason               : '';
                $docXml['protocol_number']      = isset($protocol_number)   ? $protocol_number      : '';
                $docXml['receipt_date']         = isset($receipt_date)      ? date('Y-m-d', strtotime($receipt_date))         : '';
                $docXml['organ_code']           = isset($organ_code)        ? $organ_code           : '';
                $docXml['app_version']          = isset($app_version)       ? $app_version          : '';
                $docXml['event_description']    = isset($event_description) ? $event_description    : '';
                $docXml['event_time_date']      = isset($event_time_date)   ? date('Y-m-d H:i:s', strtotime($event_time_date)) : '';
                $docXml['xml_received']         = isset($xml_received)      ? $xml_received         : '';

                // Verificando se ja tenho a chave da nfe com nsu
                $docxml = DocXml::where('user_id', $user_id)->where('company_id', $company_id)->where('doc_key', $doc_key)->where('nsu', $numnsu)->get();
                if($docxml->count() == 0){
                    DocXml::create($docXml);
                }

                // Limpando as variaveis
                $doc_key = $organ_code = $issue_date = $receipt_date = $note_status = $reason = $protocol_number = $event_sequence = $app_version = $event_type = $event_description = $event_time_date = $cnpj = $amount =$issue_cnpj = $issue_name = $issue_state = $recipient_cnpj = $recipient_name = $recipient_state = '';

                sleep($sleep);
                $issuer_cnpj = $issuer_name = $issuer_state = $recipient_cnpj = $recipient_name = $recipient_state = $amount = '';
            }

            if($lastNSU == $maxNSU) break; //quebrando o processo
            sleep(2);
        }

        return true;
    }

    public function downloadTOTAL()
    {
        $companies = Company::where('active', 'S')->get();

        foreach($companies as $company){
            echo "<pre>";
            print_r([
                'empresa' => $company->corporate_name,
                'cnpj' => $company->cnpj,
                'nota' => 'NFE'
            ]);
            echo "<pre>";
            $this->downloadNFE($company->id, $company->user_id);

            echo "<pre>";
            print_r([
                'empresa' => $company->corporate_name,
                'cnpj' => $company->cnpj,
                'nota' => 'CTE'
            ]);
            echo "<pre>";
            $this->downloadCTE($company->id, $company->user_id);
        }
    }

    public function consultaCNPJ($cnpj){
        if(strlen($cnpj) == 14){
            // Pegar dados da empresa e preencher os campos com apenas o cnpj
            $url = "https://www.receitaws.com.br/v1/cnpj/$cnpj";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
            $retorno = curl_exec($ch);
            curl_close($ch);
            $retorno = json_decode($retorno);
            return $retorno;
        }
    }
}