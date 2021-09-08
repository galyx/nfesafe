@extends('layouts.sys')

@section('container')
    <div class="content my-2">
        <div class="container-fluid">
                <div class="col-12">
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h3>Empresas</h3>
                                </div>
                                <div class="col-6 text-right">
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#novaEmpresa"><i class="fas fa-plus"></i> Registrar nova Empresa</button>
                                </div>
                            </div>
                        </div>
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

    <div class="modal fade" id="novaEmpresa">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{route('novaEmpresa')}}" method="post" id="postNovaEmpresa">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">Nova Empresa <div class="spinner-border d-none loadCep" role="status"><span class="sr-only">Loading...</span></div></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 pb-3">
                                <label for="certificate">Certificado Digital</label>
                                <input type="file" name="certificate" class="form-control-file">
                            </div>
                            <div class="col-12">
                                <label for="certificate_pass">Senha do Certificado Digital</label>
                                <input type="password" name="certificate_pass" class="form-control">
                            </div>
                            <div class="col-12">
                                <label for="cnpj">CNPJ da Empresa</label>
                                <input type="text" name="cnpj" class="form-control">
                            </div>
                            <div class="col-12">
                                <label for="corporate_name">Razão Social</label>
                                <input type="text" name="corporate_name" class="form-control">
                            </div>
                            <div class="col-12">
                                <label for="fantasy_name">Nome Fantasia</label>
                                <input type="text" name="fantasy_name" class="form-control">
                            </div>
                            
                            <div class="form-group col-5 col-md-4">
                                <label for="post_code">CEP</label>
                                <input type="text" name="post_code" class="form-control" placeholder="00000-000">
                            </div>
                            <div class="form-group col-7 col-md-8">
                                <label for="address">Endereço</label>
                                <input type="text" name="address" class="form-control" placeholder="Endereço/Rua/Avenida">
                            </div>
                            <div class="form-group col-3">
                                <label for="number">Nº</label>
                                <input type="text" name="number" class="form-control" placeholder="0000">
                            </div>
                            <div class="form-group col-9">
                                <label for="complement">Complemento</label>
                                <input type="text" name="complement" class="form-control" placeholder="Complemento">
                            </div>
                            <div class="form-group col-12">
                                <label for="address2">Bairro</label>
                                <input type="text" name="address2" class="form-control" placeholder="Bairro">
                            </div>
                            <div class="form-group col-12 col-md-6">
                                <label for="state">Estado</label>
                                <select name="state" class="form-control select2 state">
                                    <option value="">::Selecione uma Opção::</option>
                                </select>
                            </div>
                            <div class="form-group col-12 col-md-6">
                                <label for="city">Cidade</label>
                                <select name="city" class="form-control select2 city">
                                    <option value="">::Selecione uma Opção::</option>
                                </select>
                            </div>
                            <div class="form-group col-12 col-md-6">
                                <label for="phone1">Telefone</label>
                                <input type="text" name="phone1" class="form-control" placeholder="(00) 0000-0000">
                            </div>
                            <div class="form-group col-12 col-md-6">
                                <label for="phone2">Celular</label>
                                <input type="text" name="phone2" class="form-control" placeholder="(00) 00000-0000">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Fechar</button>
                        <button type="button" class="btn btn-success btn-salvar" data-update_table="S" data-save_target="#postNovaEmpresa" data-save_route="{{route('novaEmpresa')}}"><i class="fas fa-save"></i> Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection