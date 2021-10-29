$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Condifgurações
    var Toast = Swal.mixin({
        toast: true,
        position: 'center',
        showConfirmButton: false,
        timer: 4000
    });

    $('[name="cnpj"]').mask('00.000.000/0000-00');
    $('[name="post_code"]').mask('00000-000');
    $('[name="phone1"]').mask('(00) 0000-0000');
    $('[name="phone2"]').mask('(00) 00000-0000');
    
    $('.select2').select2();

    $('.date-mask').daterangepicker({
        singleDatePicker: false,
        showDropdowns: true,
        locale: {
            format: 'DD/MM/YYYY',
            daysOfWeek: ['dom','seg','ter','qua','qui','sex','sab'],
            monthNames: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','outubro','Novembro','Dezembro'],
            applyLabel: 'Aplicar',
            cancelLabel: 'Cancelar'
        }
    });

    // Busca dos estados
    $(function(){
        if($('[name="state"]')){
            $.ajax({
                url: 'https://servicodados.ibge.gov.br/api/v1/localidades/estados/',
                type: 'GET',
                success: (data) => {
                    // console.log(data);
                    for(var i=0; data.length>i; i++){
                        $('[name="state"]').append('<option value="'+data[i].sigla+'" data-sigla_id="'+data[i].id+'">'+data[i].sigla+' - '+data[i].nome+'</option>');
                    }
                }
            });
        }
    });

    // Busca das cidades/municipios
    $(document).on('change', '[name="state"]', function(){
        let sigla_id = $(this).find(':selected').data('sigla_id');
        let select = $(this).parent().parent().find('select[name="city"]');

        $.ajax({
            url: 'https://servicodados.ibge.gov.br/api/v1/localidades/estados/'+sigla_id+'/municipios',
            type: 'GET',
            success: (data) => {
                // console.log(data);
                select.empty();
                select.append('<option value="">::Selecione uma Opção::</option>');
                if(select.is('.entrega')){
                    select.append('<option value="Toda Região">Toda Região</option>');
                }

                for(var i=0; data.length>i; i++){
                    select.append('<option value="'+data[i].nome+'">'+data[i].nome+'</option>');
                }
            }
        });
    });

    $('[name="post_code"]').on('keyup blur', function(){
        $(this).parent().parent().find('input, select').attr('readonly', false);
        $(this).parent().parent().find('input, select').trigger('change');

        if($(this).val().length == 9){
            $('.loadCep').removeClass('d-none');
            $.ajax({
                url: '/cep/'+$(this).val(),
                type: 'GET',
                success: (data) => {
                    $('[name="address"]').val(data.logradouro);
                    if(data.logradouro) $('[name="address"]').prop('readonly', true);
                    $('[name="address2"]').val(data.bairro);
                    if(data.bairro) $('[name="address2"]').prop('readonly', true);
                    $('[name="state"]').val(data.uf);
                    if(data.uf) {
                        $('[name="state"]').attr('readonly', true);
                        $('[name="state"]').trigger('change');
                    }
                    setTimeout(() => {
                        $('[name="city"]').val(data.localidade);
                        if(data.localidade) {
                            $('[name="city"]').attr('readonly', true);
                            $('[name="city"]').trigger('change');
                        }
                    }, 800);

                    $('[name="number"]').focus();
                    $('.loadCep').addClass('d-none');
                }
            });
        }
    });

    $(document).ready(function() {
        var user_name = $('#user_name').text();
        user_name = user_name.split(' ');
        var intials = user_name[0].charAt(0) + user_name[user_name.length - 1].charAt(0);
        $('#image_perfil').text(intials.toUpperCase());
    });

    function buscaNotas(){
        $('.carregando').html('<div style="width: 1.5rem; height: 1.5rem;" class="spinner-border" role="status"><span class="sr-only">Loading...</span></div>');
        $('.btnBuscaNotas').prop('disabled', true);

        var datas = {
            'company': '',
            'start_end_date': '',
            'document_template': '',
            'issuer': '',
            'doc_key': '',
            'issuer_cnpj': ''
        };

        $('.buscaNotas').each(function(){
            datas[$(this).attr('name')] = $(this).val();
        });

        $.ajax({
            url: 'buscaNotas',
            type: 'POST',
            data: datas,
            success: (data) => {
                console.log(data);
                $('#div_table_notas').removeClass('d-none');
                $("#table_notas").dataTable().fnClearTable();
                $("#table_notas").dataTable().fnDestroy();

                $("#table_notas").DataTable({
                    "responsive": true,
                    "lengthChange": false,
                    "autoWidth": false,
                    "order": [[6, 'desc']],
                    "buttons": ["copy", "csv", "colvis"],
                    "data": data,
                    "columns": [
                        { data: 'doc_key' },
                        { data: 'button' },
                        { data: 'document_template' },
                        { data: 'grade_series' },
                        { data: 'note_number' },
                        { data: 'amount' },
                        { data: 'issue_date' },
                        { data: 'issuer_cnpj' },
                        { data: 'issuer_name' },
                        { data: 'issuer_state' },
                        { data: 'recipient_cnpj' },
                        { data: 'recipient_name' },
                        { data: 'recipient_state' },
                    ]
                }).buttons().container().appendTo('#table_notas_wrapper .col-md-6:eq(0)');

                $('.carregando').empty();
                $('.btnBuscaNotas').prop('disabled', false);
            }
        });
    }

    $(document).on('change', '.buscaNotas', function(){
        if($(this).attr('data-autoBusca') == 'true'){
            buscaNotas();
            $('#notasRefresh').removeClass('d-none');
        }
    });
    $(document).on('click', '.btnBuscaNotas', function(){
        buscaNotas();
    });

    // Botão para fazer busca das notas da empresa selecionadas
    $('#notasRefresh').on('click', function(){
        var company_id = $('[name="company"]').val();
        var user_id = $('#user_id').val();

        if(company_id !== ''){
            Swal.fire({
                title: 'Baixando NFES',
                html: 'Aguarde enquanto as notas estão sendo baixadas.',
                allowOutsideClick: false,
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '/downloadNFE/'+company_id+'/'+user_id,
                type: 'GET',
                success: (data) => {
                    // console.log(data);

                    Swal.fire({
                        icon: 'success',
                        title: 'NFES Baixados',
                        timer: 3000
                    });

                    setTimeout(() => {
                        Swal.fire({
                            title: 'Baixando CTES',
                            html: 'Aguarde enquanto as notas estão sendo baixadas.',
                            allowOutsideClick: false,
                            timerProgressBar: true,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: '/downloadCTE/'+company_id+'/'+user_id,
                            type: 'GET',
                            success: (data) => {
                                // console.log(data);

                                Swal.fire({
                                    icon: 'success',
                                    title: 'CTES Baixados',
                                    timer: 3000
                                });

                                $('.buscaNotas').trigger('change');
                            },
                            error: (err) => {
                                // console.log(err);
                            }
                        });
                    }, 3000);
                },
                error: (err) => {
                    // console.log(err);
                }
            });
        }
    });

    // Função salva dados gerais
    $(document).on('click', '.btn-salvar', function(){
        // Pegamos os dados do data
        let save_target = $(this).data('save_target');
        let save_route = $(this).data('save_route');
        let update_table = $(this).data('update_table');
        let table_trash = $(this).data('trash');

        // Por mais que tenha erro, limpamos para os outros que não tenha
        $(save_target).find('input').removeClass('is-invalid');
        $(save_target).find('.invalid-feedback').remove();

        // Pegamos o parente do id para adicionar um modelo de carregamento
        let modal = $(save_target).parent();
        if(modal.is('.modal-content')) modal.append('<div class="overlay d-flex justify-content-center align-items-center"><i class="fas fa-2x fa-sync fa-spin"></i></div>');

        $.ajax({
            url: save_route,
            type: "POST",
            data: new FormData($(save_target)[0]),
            cache: false,
            contentType: false,
            processData: false,
            success: (data) => {
                // console.log(data);
                // Procuramos a div adcionada recentemente para removemos e fechamos o modal
                $(modal).find('.overlay').remove();
                $(modal).parent().parent().modal('hide');

                $(save_target).find('input[type="text"]').val('');
                $(save_target).find('input, select').attr('readonly', false);

                if(update_table == 'S') if(data.table) $('table tbody').append(data.table); // Inserindo novos dados
                if(update_table == 'S') if(data.tb_up) $('table tbody').find('.tr-id-'+data.tb_id).html(data.tb_up); // Editando dados

                if(table_trash == 'S'){ // Somente quando for apagar
                    if(data.tb_trash) $('table tbody').find('.tr-id-'+data.tb_trash).remove();

                    Toast.fire({
                        icon: 'success',
                        title: 'Os dados foram excluidos com successo!'
                    });
                }else{
                    Toast.fire({
                        icon: 'success',
                        title: 'Os dados foram salvos com successo!'
                    });
                }
            },
            error: (err) => {
                // console.log(err);
                $(modal).find('.overlay').remove();

                // Adicionamos os erros numa variavel
                let erro_tags = err.responseJSON.errors;
                // console.log(erro_tags);

                $.each(erro_tags, (key, value) => {
                    let tag = $(save_target).find('[name="'+key+'"]');
                    tag.addClass('is-invalid');

                    tag.parent().append('<div class="invalid-feedback">'+value[0]+'</div>');
                });

                if(err.responseJSON.msg_alert){
                    Swal.fire({
                        icon: err.responseJSON.icon_alert,
                        text: err.responseJSON.msg_alert,
                    });
                }
            }
        });
    });

    // Passar os dados nos campos paranão puxar um por e sim recueprar em json em um atributo
    $(document).on('click', '.btn-editar', function(){
        var target = $(this).data('target'); // qual modal ta sendo acessado
        var dados = $(this).data('dados'); // dados que serão passados aos campos

        // Fazemos uma leitura dosa campos
        var data = '';
        $.each(dados, (key, value) => {
            if($(target).find('[name="'+key+'"').attr('type') !== 'file' && $(target).find('[name="'+key+'"').attr('type') !== 'password'){
                $(target).find('[name="'+key+'"').val(value); // os campos name são iguais aos das colunas vidna do banco
                $(target).find('.'+key).val(value); // quando o campo name por motivos especiais for diferente, pega por class tambem

                $(target).find('._'+key).text(value); // qunado campo for texto
            }
        });

        $(target).find('[name="post_code"]').trigger('keyup');
    });

    $(document).on('click', '.btn-modal-info', function(){
        $('#dadosNotas').modal('show');
        var id = $(this).data('id');
        var company_id = $('[name="company"]').val();
        var route = $(this).data('route');
        $('.ver-notas').html('<div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div>');

        $.ajax({
            url: route,
            type: 'POST',
            data: {id, company_id},
            success: (data) => {
                console.log(data);
                $('.ver-notas').empty();

                if(data.cnpj == data.dados.issuer_cnpj){
                    $('.btn-valida-nfe').addClass('d-none');
                }else{
                    $('.btn-valida-nfe').removeClass('d-none');
                }

                $.each(data.dados.doc_xmls, (key, value) => {
                    if(value.document_template == '57'){
                        $('.btn-valida-nfe').addClass('d-none');
                    }else{
                        $('.btn-valida-nfe').removeClass('d-none');
                    }

                    $('.ver-notas').append(
                        '<div class="row border rounded py-3 px-2">'+
                            '<div class="col-6 col-md-4 py-2 px-1">Chave Documento: '+value.doc_key+'</div>'+
                            '<div class="col-6 col-md-4 py-2 px-1">NSU: '+value.nsu+'</div>'+
                            '<div class="col-6 col-md-4 py-2 px-1">Tipo de Evento: '+value.event_type+'</div>'+
                            '<div class="col-6 col-md-4 py-2 px-1">Descrição do Evento: '+value.event_description+'</div>'+
                            '<div class="col-6 col-md-4 py-2 px-1">Tipo de Documento: '+value.document_template+'</div>'+
                            '<div class="col-6 col-md-4 py-2 px-1"><a target="_blank" href="/baixar-xml/'+value.id+'" class="btn btn-primary">Baixar XML</a></div>'+
                        '</div>'
                    );
                });
            }
        });
    });

    // Função para ler o certificado
    // $(document).on('change', '.certificate', function(){
    //     var certificate = $(this);
    //     var formData = new FormData();
    //     formData.append('certificate',certificate.prop('files')[0])
    //     $.ajax({
    //         url: '/leCertificado',
    //         type: "POST",
    //         data: formData,
    //         cache: false,
    //         contentType: false,
    //         processData: false,
    //         success: (data) => {
    //             console.log(data);
    //         },
    //         error: (err) => {
    //             console.log(err);
    //         }
    //     });
    // });
});