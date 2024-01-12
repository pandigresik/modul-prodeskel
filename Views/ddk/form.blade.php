@php
    use \Modules\Prodeskel\Enums\DDKEnum;
    use \Modules\Prodeskel\Enums\DDKPilihanMultipleSelectEnum;
@endphp

@extends('admin.layouts.index')
@include('admin.layouts.components.asset_validasi')

@push('css')
    <style>
        .tabel-rincian, th, td{
            height: auto;
            padding: 2px 5px;
        }
        .col-md-6 > hr {margin: 0 0 15px 0;}
        #form-1 * {
            font-family: "Helvetica Neue",Helvetica,Arial,sans-serif,FontAwesome
        }
    </style>
@endpush

@section('title')
<h1>
    Prodeskel <small>Formulir Data Dasar Keluarga (DDK)</small>
</h1>
@endsection

@section('breadcrumb')
    <li><a href="{{ ci_route('prodeskel.ddk') }}"><i class="fa fa-users"></i> Data Dasar Keluarga (DDK)</a></li>
    <li class="active">Formulir Data Dasar Keluarga (DDK)</li>
@endsection

@section('content')
    <div class="row">
        {!! form_open(ci_route('prodeskel.ddk.save.form' , $keluarga->id), 'class="form-validasi" id="form-1"') !!}
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <a href="{{ ci_route('prodeskel.ddk') }}" class="btn btn-social btn-info btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block" title="Kembali Ke Data Dtks"><i class="fa fa-arrow-circle-o-left"></i>Kembali Ke Data Dasar Keluarga (DDK)</a>
                    </div>
                    <div class="box-body tab-content" style="padding-left:30px; padding-right:30px">
                        <div class="nav-tabs-custom">
                            <ul class="nav nav-tabs" id="nav-tab" role="tablist">
                                <li><a href="#bagian-keluarga" data-toggle="tab" id="nav-bagian-keluarga"><strong>Data Dasar Keluarga</strong></a></li>
                                @foreach($keluarga->anggota as $item)
                                    <li><a href="#bagian-anggota-{{ $item->id }}" data-toggle="tab" id="nav-bagian-anggota-{{ $item->id }}"><strong>{{ ucwords(strtolower($item->nama)) }}</strong></a></li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="tab-pane" id="bagian-keluarga">
                            @include('ddk.form_keluarga')
                        </div>
                        @foreach($keluarga->anggota as $no_urut => $item)
                            <div class="tab-pane" id="bagian-anggota-{{ $item->id }}">
                                @include('ddk.form_anggota', ['anggota' => $item, 'no_urut' => $no_urut + 1])
                            </div>
                        @endforeach
                        <hr class="col-sm-12">
                        <div class="col-sm-12 text-center">
                            {{-- <button type="reset" class="btn btn-social btn-danger btn-sm"><i class='fa fa-times'></i>Batalkan semua perubahan Tab</button> --}}
                            <button type="submit" class="btn btn-social btn-success btn-sm"><i class="fa fa-check"></i>Simpan semua data Tab</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    @include('admin.layouts.components.ajax_dtks')
    <script>
        var anggota_ids = [
            @foreach($keluarga->anggota as $no_urut => $item)
                {{ $item->id }},
            @endforeach
        ];
        anggota_ids.forEach(function(item, index){
            window['kode_tersimpan_anggota_' + item] = [];
        })
        let multiple_select = {!! json_encode(DDKPilihanMultipleSelectEnum::semuaKode()) !!};

        $(document).ready(function() {
            $.fn.modal.Constructor.prototype.enforceFocus = function() {};
            // Select2 dengan fitur pencarian karena tidak ngeload /js/custom.select2.js
            $('.select2').select2({
                width: '100%',
                dropdownAutoWidth : true
            });

            $.each($(".tab-content .tab-pane"), function(index, val) {
                var id = $(val).attr('id');
                if (index == 0) {
                    $(`#nav-${id}`).trigger("click");
                }
            });

            // form_anggota
            $('.table_bahan_galian').each(function(index, table){
                let id = $(table).parents('.bahan_galian').data('anggota');
                if($(table).find('tr').length == 2){
                    $(table).hide();
                }else{
                    $(table).prev().html('<div class="alert alert-warning" style="margin: 0 auto;padding: 0px;text-align-last: center;user-select: none;">Kosongkan pilihan dan isian jika baris data tidak ingin disimpan/ingin dihapus</div>');
                    $(table).find('tr').each(function(index, item){
                        window['kode_tersimpan_anggota_' + id].push($(item).find('td').eq(0).text());
                        // ulangi header(tr pertama) per 10 baris
                        if(index % 10 == 0 && index != 0){
                            $(table).find('tr').eq(0).clone().insertAfter(item);
                            $(table).find('tr').eq(1).clone().insertAfter(item);
                        }
                    });
                }
            });

            function tambahkan_pilihan_ke_tabel(pilihan, pilihan_text, table, id){
                if(window['kode_tersimpan_anggota_' + id].indexOf(pilihan) != -1){
                    $(document).find('#' + pilihan).focus();
                    return;
                }
                window['kode_tersimpan_anggota_' + id].push(pilihan);
                $(table).show();
                $(table).prev().html('<div class="alert alert-warning" style="margin: 0 auto;padding: 0px;text-align-last: center;user-select: none;">Kosongkan pilihan dan isian jika baris data tidak ingin disimpan/ingin dihapus</div>');

                $(table).append(
                    '<tr>' +
                        '<td>' + pilihan + '</td>' +
                        '<td>' + pilihan_text + '</td>' +
                        '<td>' +
                            '<input name="{{ DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA }}[' + id + '][' + pilihan + '][nilai_produksi]" maxlength="20" class="form-control input-sm bilangan_titik" type="text">' +
                        '</td>' +
                        '<td>' +
                            '<input name="{{ DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA }}[' + id + '][' + pilihan + '][milik_adat]" maxlength="100" class="form-control input-sm alamat" type="text">' +
                        '</td>' +
                        '<td>' +
                            '<input name="{{ DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA }}[' + id + '][' + pilihan + '][milik_perorangan]" maxlength="100" class="form-control input-sm alamat" type="text">' +
                        '</td>' +
                        '<td>' +
                            '<input name="{{ DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA }}[' + id + '][' + pilihan + '][pemasaran_hasil]" maxlength="150" class="form-control input-sm alamat" type="text">' +
                        '</td>' +
                    '</tr>'
                );

            }
            $('.tambahkan_bahan_galian').each(function(index, tombol){
                $(tombol).on('click', function(ev){
                    let container = $(tombol).parents('.bahan_galian');
                    let id = $(container).data('anggota');
                    let table = $(container).find('.table_bahan_galian');
                    let pilihan = $(container).find('.pilihan_bahan_galian').val();

                    if((typeof pilihan == 'object' || typeof pilihan == 'array') && pilihan.length == 0 || pilihan == ''){
                        Swal.fire({
                            icon: 'info',
                            html: 'Silahkan pilih Bahan Galian',
                            timer: 1000,
                        });
                        return;
                    }
                    if(typeof pilihan == 'object' || typeof pilihan == 'array'){
                        // Loop through the array and set the selected value of the select element
                        $.each(pilihan, function(index, value_pilihan) {
                            let pilihan_text = $(container).find('.pilihan_bahan_galian option[value="' + value_pilihan + '"]').text();
                            tambahkan_pilihan_ke_tabel(value_pilihan, pilihan_text, table, id);
                        });
                    }else{
                        let pilihan_text = $(container).find('.pilihan_bahan_galian option:selected').text();
                        tambahkan_pilihan_ke_tabel(pilihan, pilihan_text, table, id);
                    }
                    setTimeout(() => {
                        $(container).find('.pilihan_bahan_galian').val(['']).trigger('change');
                    }, 500);
                });
            });

            $('#form-1').on('submit', function(ev){
                ev.preventDefault();

                let is_valid = is_form_valid($(this).attr('id'));
                if( ! is_valid){
                    return false;
                }

                let form = $('#form-1').serializeArray();
                $(document).find('#form-1 select').each(function(index, el){
                    if($(el).attr('multiple') !== 'multiple'){
                        form.push({'name': $(el).attr('name'), 'value': $(el).val()});
                    }
                });
                Swal.fire({
                    title: 'Sedang Menyimpan',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });
                ajax_save_dtks("{{ ci_route('prodeskel.ddk.save.form' , $keluarga->id) }}", form,
                    callback_success = function(data){
                        let otomatis = data.otomatis;
                        if((typeof otomatis == 'object' || typeof otomatis == 'array')){
                            for (let key in otomatis) {
                                if (otomatis.hasOwnProperty(key)) {
                                    let arr = otomatis[key];
                                    arr.push('');
                                    if(multiple_select.indexOf(key) != -1){
                                        $('#' + key).val(arr).select2({
                                            width: '100%',
                                            dropdownAutoWidth : true
                                        });;
                                    }
                                }
                            }
                        }
                        Swal.close();
                    },
                    callback_fail = false,
                    custom_config = {});
            });
        });
    </script>
@endpush
