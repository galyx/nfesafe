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

            $dockeys = DocKey::where('user_id', '1')->where('company_id', $request->company)->where('issue_date', '>=', $start_date)->where('issue_date', '<=', $final_date);
            if($request->document_template) $dockeys->where('document_template', $request->document_template);
            if($request->issuer) $dockeys->where('issuer_cnpj', ($request->issuer == 'third' ? '!=' : '='), $company->cnpj);
            if($request->doc_key) $dockeys->where('doc_key', $request->doc_key);
            if($request->issuer_cnpj) $dockeys->where('issuer_cnpj', $request->issuer_cnpj);
            $dockeys = $dockeys->get();

            $docKeysNews = [];
            foreach($dockeys as $value){
                $docKeysNews[] = [
                    'doc_key' => $value->doc_key,
                    'button' => '<button type="button" class="btn btn-primary btn-sm" data-id="'.$value->id.'" title="Informações"><i class="fas fa-info"></i></button>',
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
}