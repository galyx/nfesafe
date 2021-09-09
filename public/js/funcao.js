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
                // console.log(data);
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
        var images = $(this).data('images'); // dados que serão passados aos campos

        // Fazemos uma leitura dosa campos
        var data = '';
        $.each(dados, (key, value) => {
            $(target).find('[name="'+key+'"').val(value); // os campos name são iguais aos das colunas vidna do banco
            $(target).find('.'+key).val(value); // quando o campo name por motivos especiais for diferente, pega por class tambem

            $(target).find('._'+key).text(value); // qunado campo for texto

            // Especifico para o modal editarProduto
            if(key == 'description'){
                $(target).find('.note-editable').html(value);
            }

            // Especifico para o modal editarProduto
            if(key == 'value'){
                if(target !== '#editarCupom' && target !== '#editarPromocao'){
                    if(value){
                        $(target).find('[name="'+key+'"').val(parseFloat(value).toFixed(2).toString().replace('.',','));
                    }
                }
            }

            if(key == 'price'){
                if(target !== '#editarCupom'){
                    if(value){
                        $(target).find('[name="'+key+'"').val(parseFloat(value).toFixed(2).toString().replace('.',','));
                    }
                }
            }

            if(key == 'weight'){
                if(target !== '#editarCupom'){
                    if(value){
                        $(target).find('[name="'+key+'"').val(parseFloat(value).toFixed(3).toString().replace('.',','));
                    }
                }
            }

            // Especifico para o modal editarProduto
            if(key == 'has_preparation' && value == 'S'){
                $(target).find('.has_preparation').trigger('click');
            }
            if(key == 'product_category'){
                var sub_category = [];
                for(var i=0; value.length>i; i++){
                    if(value[i].category_pai == 'S'){
                        $(target).find('[name="main_category"]').val(value[i].category_id);
                        $(target).find('.main_category').trigger('change');
                    }

                    if(value[i].category_pai == 'N'){
                        sub_category.push(value[i].category_id);
                    }
                }

                setTimeout(()=>{
                    $(target).find('.sub_category').val(sub_category).trigger('change');
                },1000, sub_category);
            }
            if(key == 'product_attribute'){
                for(var i=0; value.length>i; i++){
                    if(value[i].attribute_id == null) {
                        $(target).find('#edit_icheck_'+value[i].parent_id).trigger('click');
                    }
                    $(target).find('#edit_icheck_'+value[i].attribute_id).trigger('click');
                    $(target).find('[name="attribute['+value[i].attribute_id+'][attribute_value]"]').val(typeof value[i].attribute_value == 'number' ? value[i].attribute_value.toFixed(2).toString().replace('.',',') : '');
                }
            }

            if(target == '#editarCupom'){
                if(key == 'discount_accepted') $(target).find('.discount_accepted').val(JSON.parse(value));
                if(key == 'user_id') $(target).find('.user_id').val(JSON.parse(value));
            }

            // Especifico para promoções
            if(key == 'start_date'){
                var date = value.split('-');
                $(target).find('[name="start_end_date"]').data('daterangepicker').setStartDate(date[2]+'/'+date[1]+'/'+date[0]);
            }
            if(key == 'final_date'){
                var date = value.split('-');
                $(target).find('[name="start_end_date"]').data('daterangepicker').setEndDate(date[2]+'/'+date[1]+'/'+date[0]);
            }

            // especifico atributo
            if(key == 'hexadecimal') {
                if(value) $(target).find('[value="color"]').trigger('click');
                if(value) $(target).find('[name="color"]').trigger('change');
            }
            if(key == 'image') if(value) $(target).find('[value="image"]').trigger('click');
        });

        // Caso teha as imagens ele le e adiconsa
        if(images){
            for(var i=0; images.length>i; i++){
                if(i == 0){
                    $(target).find('.img-principal').append('<img class="rounded" style="height: 180px" src="'+images[i].image+'">');
                }else{
                    $(target).find('.img-multipla').append('<img class="rounded mx-1" style="height: 80px" src="'+images[i].image+'">');
                }
            }
        }

        // Especifico para o modal editarProduto
        $(target).find('.sales_unit').trigger('change');
        $(target).find('[name="post_code"]').trigger('keyup');
        $(target).find('select').trigger('change');
    });

    $(document).on('click', '.btn-disable', function(){
        let disable_name    = $(this).data('disable_name');
        let disable_id      = $(this).data('disable_id');
        let type_disabled   = $(this).data('type_disabled');
        let save_route      = $(this).data('save_route');

        var isValid = true;
        if(!type_disabled){
            isValid = false;
            Swal.fire({
                icon: 'error',
                title: '"type_disabled" vazio!'
            });
        }else if(!disable_id){
            isValid = false;
            Swal.fire({
                icon: 'error',
                title: '"disable_id" vazio!'
            });
        }

        let title = '';

        switch(type_disabled){
            case 'S':
                title = 'Ativar "'+disable_name+'"?';
            break;
            case 'N':
                title = 'Desativar "'+disable_name+'"?';
            break;
            case 'X':
                title = 'Excluir Permanentemente "'+disable_name+'"?';
            break;
        }

        if(isValid){
            Swal.fire({
                icon: 'info',
                title: title,
                showCancelButton: true,
                confirmButtonText: 'SIM',
                cancelButtonText: 'NÃO',
                showLoaderOnConfirm: true,
                preConfirm: (disabled) => {
                    return $.ajax({
                                url: save_route,
                                type: 'POST',
                                data: {id: disable_id, type: type_disabled}
                            }).then((response) => {
                                // console.log(response);
                                $('table tbody').find('.tr-id-'+response).remove();
                                return response;
                            }).catch((error) => {
                                // console.log(error);
                                Swal.showValidationMessage(error.responseJSON);
                            });
                }
            }).then((result) => {
                if(result.isConfirmed){
    
                }
            });
        }
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