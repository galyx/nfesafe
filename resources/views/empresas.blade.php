@extends('layouts.sys')

@section('container')
    <div class="content my-2">
        <div class="container-fluid">
                <div class="col-12">
                    <div class="card card-success card-outline">
                        <div class="card-body pad table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nº</th>
                                        <th>CNPJ</th>
                                        <th>Razão Social</th>
                                        <th>Nome Fantasia</th>
                                        <th>Endereço</th>
                                        <th>Bairro</th>
                                        <th>Cidade</th>
                                        <th>UF</th>
                                        <th>CEP</th>
                                        <th>Status Certificado</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection