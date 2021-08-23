$(document).ready(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
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
    $(document).on('click', '.btn-save', function(){
        // Pegamos os dados do data
        let save_target = $(this).data('save_target');
        let save_route = $(this).data('save_route');
        let update_table = $(this).data('update_table');

        // Função extra antes de chamar o ajax para resolver antes de entrar aqui
        // funcaoEventoExtra($(save_target).serializeArray(), save_target);

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

                if(update_table == 'S') if(data.table_tr) $('table tbody').append(data.table_tr);

                Swal.fire({
                    toast: true,
                    icon: 'success',
                    title: 'Os dados foram salvos com successo!',
                    showConfirmButton: false,
                    timer: 1500
                });

                // funcaoSuccessExtra(data, save_target);
            },
            error: (err) => {
                // console.log(err);
                $(modal).find('.overlay').remove();

                // Adicionamos os erros numa variavel
                let erro_tags = err.responseJSON.errors;

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
});