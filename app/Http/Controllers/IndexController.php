<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\LastNsu;
use App\Models\DataCompany;
use App\Models\DocKey;
use App\Models\DocXml;
use Illuminate\Http\Request;


class IndexController extends Controller
{
    public function cepConsulta($cep)
    {
        function consultaCep($cep){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://viacep.com.br/ws/$cep/json/");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_POST, FALSE);

            $response = json_decode(curl_exec($ch), true);
            curl_close($ch);

            return $response;
        }

        return response()->json(consultaCep($cep));
    }

    public function dashboard()
    {
        return view('dashboard');
    }

    public function notas()
    {
        $companies = Company::where('user_id', '1')->get();
        return view('notas', compact('companies'));
    }

    public function empresas()
    {
        $companies = Company::where('user_id', '1')->get();
        return view('empresas', compact('companies'));
    }

    public function buscaNotas(Request $request)
    {
        if($request->company){
            $dates = explode('-', $request->start_end_date);
            $start_date = date('Y-m-d', strtotime(str_replace('/','-',trim($dates[0]))));
            $final_date = date('Y-m-d', strtotime(str_replace('/','-',trim($dates[1]))));

            $company = Company::where('id', $request->company)->first();

            $dockeys = DocKey::with(['DocXmls' => function ($query) {
                return $query->orderBy('nsu', 'DESC');
            }])->where('user_id', '1')->where('company_id', $request->company)->where('issue_date', '>=', $start_date)->where('issue_date', '<=', $final_date);
            if($request->document_template) $dockeys->where('document_template', $request->document_template);
            if($request->issuer) $dockeys->where('issuer_cnpj', ($request->issuer == 'third' ? '!=' : '='), str_replace(['.','/','-'],'',$company->cnpj));
            if($request->doc_key) $dockeys->where('doc_key', $request->doc_key);
            if($request->issuer_cnpj) $dockeys->where('issuer_cnpj', $request->issuer_cnpj);
            $dockeys = $dockeys->get();

            $docKeysNews = [];
            foreach($dockeys as $value){
                $docKeysNews[] = [
                    'doc_key' => $value->doc_key.' '.($value->DocXmls ? ($value->DocXmls[0]->event_type == '210210' && $value->DocXmls[0]->issuer_cnpj !== str_replace(['.','/','-'],'',$company->cnpj) ? '<i class="fas fa-file-code"></i>' : '') : ''),
                    'button' => '<button type="button" class="btn btn-primary btn-sm btn-modal-info" data-id="'.$value->id.'" data-route="'.route('buscaDadosNotas').'" title="Informações"><i class="fas fa-info"></i></button>',
                    'document_template' => $value->document_template,
                    'grade_series' => $value->grade_series,
                    'note_number' => $value->note_number,
                    'amount' => 'R$ '.number_format($value->amount, 2, ',', '.'),
                    'issue_date' => date('d-m-Y', strtotime($value->issue_date)),
                    'issuer_cnpj' => $value->issuer_cnpj,
                    'issuer_name' => $value->issuer_name,
                    'issuer_state' => $value->issuer_state,
                    'recipient_cnpj' => $value->recipient_cnpj,
                    'recipient_name' => $value->recipient_name,
                    'recipient_state' => $value->recipient_state,
                ];
            }
            return response()->json($docKeysNews);
        }
    }

    public function buscaDadosNotas(Request $request)
    {
        $dockeys = DocKey::with('DocXmls')->find($request->id);

        return response()->json($dockeys);
    }

    public function baixarXml($id)
    {
        $docxml = DocXml::find($id);

        $temp = fopen(public_path($docxml->doc_key.'.xml'), 'a');
        fwrite($temp, '<?xml version="1.0" encoding="UTF-8"?>'.(str_replace('<?xml version="1.0" encoding="UTF-8"?>','', $docxml->xml_received)));
        fclose($temp);

        $zip = new \ZipArchive;
        $fileName = $docxml->doc_key.'.zip'; // nome do zip
        $zipPath = $fileName; // path do zip
        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE)
        {
            // adicionar arquivo ao zip
            $zip->addFile($docxml->doc_key.'.xml', basename($docxml->doc_key.'.xml'));

            // concluir a operacao
            $zip->close();
        }

        unlink($docxml->doc_key.'.xml');

        if(file_exists($zipPath)){
            // Forçamos o donwload do arquivo.
            header('Content-Type: application/zip');
            header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
            header('Content-Disposition: attachment; filename="'.$zipPath.'"');
            readfile($zipPath);
            //removemos o arquivo zip após download
            unlink($zipPath);
        }

        // return response()->download($zipPath);
    }
}