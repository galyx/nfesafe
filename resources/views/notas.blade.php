@extends('layouts.sys')

@section('container')
    <div class="content my-2">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary card-outline collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title">Filtros</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body pad">
                            <div class="container">
                                {{-- Campos --}}
                                <div class="row">
                                    {{-- Filtro - Data Inicial & Final --}}
                                    <div class="form-group col-12 col-md-4">
                                        <label for="start_end_date">Data Inicial e Final</label>
                                        <input type="text" class="form-control form-control-sm date-mask buscaNotas" name="start_end_date" value="{{date('d/m/Y', strtotime('-30 Days'))}} - {{date('d/m/Y')}}">
                                    </div>
                                    {{-- Filtro - Modelo de Nota --}}
                                    <div class="form-group col-12 col-md-4">
                                        <label for="document_template">Modelo do Documento</label>
                                        <select name="document_template" class="form-control form-control-sm buscaNotas">
                                            <option value="">Todos os Modelos</option>
                                            <option value="55">Modelo 55 (NFE)</option>
                                            <option value="57">Modelo 57 (CTE)</option>
                                        </select>
                                    </div>
                                    {{-- Filtro - Emissor --}}
                                    <div class="form-group col-12 col-md-4">
                                        <label for="issuer">Notas Emitidas</label>
                                        <select name="issuer" class="form-control form-control-sm buscaNotas">
                                            <option value="">Todos os Emitentes</option>
                                            <option value="third">Emitidos por Terceiros</option>
                                            <option value="company">Emtidos pela Empresa</option>
                                        </select>
                                    </div>

                                    {{-- Filtro - Chave do Documento --}}
                                    <div class="form-group col-12 col-md-5">
                                        <label for="doc_key">Chave do Documento</label>
                                        <input type="text" class="form-control form-control-sm buscaNotas" name="doc_key">
                                    </div>
                                    {{-- Filtro - Emissor --}}
                                    <div class="form-group col-12 col-md-4">
                                        <label for="issuer_cnpj">CNPJ do Emitente</label>
                                        <input type="text" class="form-control form-control-sm buscaNotas" name="issuer_cnpj">
                                    </div>
                                </div>
                                {{-- Botões --}}
                                <div class="row">
                                    <div class="col-12 text-right">
                                        <button type="button" class="btn btn-primary btn-sm btnBuscaNotas"><i class="fas fa-sync"></i> Filtrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 d-none" id="div_table_notas">
                    <div class="card card-success card-outline">
                        <div class="card-body pad">
                            <table class="table table-hover" id="table_notas">
                                <thead>
                                    <tr>
                                        <th>Chave do Documento</th>
                                        <th>::</th>
                                        <th>Modelo do Documento</th>
                                        <th>Serie da Nota</th>
                                        <th>Numero da Nota</th>
                                        <th>Valor da Nota</th>
                                        <th>Data de Emissão</th>
                                        <th>CNPJ/CPF do Emitente</th>
                                        <th>Nome de Emitente</th>
                                        <th>UF de Emitente</th>
                                        <th>CNPJ/CPF do Destinatário</th>
                                        <th>Nome do Destinatário</th>
                                        <th>UF do Destinatário</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="dadosNotas" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="dadosNotasLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dadosNotasLabel">Dados da Nota <span class="_dockey"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container btn-valida-nfe d-none mb-3">
                        <div class="row">
                            <div class="col-3">
                                <a target="_blank" href="#" data-url="{{asset('ciencia-op/')}}" class="btn btn-success btn-block a-ciencia">Ciencia da Operação</a>
                            </div>
                            <div class="col-3">
                                <a target="_blank" href="#" data-url="{{asset('confirma-op/')}}" class="btn btn-success btn-block a-confirma">Confirmar Operação</a>
                            </div>
                            <div class="col-3">
                                <a href="#" data-url="{{asset('desconhecimento-op/')}}" class="btn btn-success btn-block a-desconhecimento">Desconhecimento da Operação</a>
                            </div>
                            <div class="col-3">
                                <a href="#" data-url="{{asset('op-n-realizada/')}}" class="btn btn-success btn-block a-Nrealizada">Operação não Realizada</a>
                            </div>
                        </div>
                    </div>
                    <h1>Eventos Gerados</h1>
                    <div class="container ver-notas"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('customjs')
    <script>
        $(document).ready(function(){
            // $("#table_notas").DataTable({
            //     "responsive": true, "lengthChange": false, "autoWidth": false,
            //     "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
            // }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        });
    </script>
@endsection