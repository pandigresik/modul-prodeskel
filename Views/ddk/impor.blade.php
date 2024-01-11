@include('admin.layouts.components.asset_datatables')

@extends('admin.layouts.index')

@section('title')
    <h1>
        Prodeskel <small>Impor Data Survey Data Dasar Keluarga (DDK)</small>
    </h1>
@endsection

@section('breadcrumb')
    <li><a href="{{ ci_route('prodeskel/ddk') }}"><i class="fa fa-users"></i> Data Dasar Keluarga (DDK)</a></li>
    <li class="active">Impor Data Survey</li>
@endsection

@section('content')
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa fa-ban"></i> Gagal</h4>
            <p>{!! session('error') !!}</p>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa fa-ban"></i> Berhasil</h4>
            <p>{!! session('success') !!}</p>
        </div>
    @endif
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <a href="{{ ci_route('prodeskel/ddk') }}"
                        class="btn btn-social btn-info btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"
                        title="Kembali Ke Data Dtks"><i class="fa fa-arrow-circle-o-left"></i>Kembali Ke Data Dasar Keluarga
                        (DDK)</a>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <p>Fitur ini dimaksudkan untuk memasukkan Data Dasar Keluarga (DDK) serta mengubah Data Dasar Keluarga (DDK) yang sudah ada secara masal
                            </p>
                            <p>Mempersiapkan data dengan bentuk excel untuk Impor ke dalam database SID : </p>
                            <div class="col-sm-12">
                                <div class="row">
                                    <ol>
                                        <li value="1">Unduh file "Template Impor DDK" untuk diisi data ubahan
                                            <a href="{{ site_url('prodeskel/ddk/impor/download-template-impor-ddk') }}" class="btn btn-social btn-info btn-sm margin">
                                                <i class="fa fa-download"></i> Template Impor DDK
                                            </a>
                                            <p>Berkas pada tautan tersebut dapat dipergunakan untuk memasukkan data DDK Keluarga dan Anggota. Klik
                                                'Enable Macros' pada waktu membuka berkas tersebut.
                                            </p>
                                        </li>
                                        <li>Unduh file "Daftar Keluarga dan Anggota DDK" untuk memudahkan mengisi file "Template Impor DDK"
                                            <a href="#" data-remote="false" data-toggle="modal" data-target="#unduhDaftarKeluargaDanAnggotaDDK" class="btn btn-social btn-info btn-sm margin">
                                                <i class="fa fa-download"></i> Daftar Keluarga dan Anggota DDK
                                            </a>
                                            <div class="modal fade" id="unduhDaftarKeluargaDanAnggotaDDK" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                            <h4 class="modal-title">
                                                                Pilih Keluarga
                                                                <small>yang akan disertakan dalam file unduhan</small>
                                                            </h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <table class="table table-bordered table-hover" id="tabeldata">
                                                                <thead>
                                                                    <tr>
                                                                        <th><input type="checkbox" id="checkall" /></th>
                                                                        <th class="padat">No KK (Aktif)</th>
                                                                        <th class="padat">Kepala Keluarga</th>
                                                                        <th>Dusun</th>
                                                                        <th>RW</th>
                                                                        <th>RT</th>
                                                                    </tr>
                                                                </thead>
                                                            </table>
                                                            {!! form_open(site_url('keluarga/filter/cari'), 'id="form-ke-keluarga"') !!}
                                                                <input id="cari" name="cari" type="hidden">
                                                            </form>
                                                        </div>
                                                        <div class="modal-footer">
                                                            {!! form_open(site_url('prodeskel/ddk/impor/download-daftar-data-impor-ddk'), 'id="unduhDataKeluargaForm"') !!}
                                                                <input type="hidden" name="ids" value="">
                                                                <button type="submit" class="unduhSemuaDataKeluarga btn btn-social btn-info btn-sm pull-left">
                                                                    <i class="fa fa-download"></i>Unduh semua data keluarga (Aktif)
                                                                </button>
                                                                <button type="submit" disabled class="unduhDataKeluargaTerpilih btn btn-social btn-info btn-sm pull-left">
                                                                    <i class="fa fa-download"></i>Unduh 0 data terpilih
                                                                </button>
                                                            </form>
                                                            <button type="button" class="btn btn-warning btn-sm" data-dismiss="modal">Batal</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li>Simpan (Save As) file "Template Impor DDK" ke dalam bentuk .xlsx </li>
                                        <li>Pastikan format excel ber-ekstensi .xlsx (format Excel versi 2007 ke atas)</li>
                                    </ol>
                                </div>
                            </div>
                            <p></p>
                            <p>
                            </p>
                            <p>Batas maksimal pengunggahan berkas <strong>{{ max_upload() }} MB.</strong></p>
                            <p>Proses ini akan membutuhkan waktu beberapa menit.</p>
                            <p></p>
                            <hr>
                            {!! form_open(site_url('prodeskel/ddk/impor/upload-excel-ubahan'), 'class="form-horizontal" id="impor" enctype="multipart/form-data"') !!}
                                <div class="form-group">
                                    <label for="file" class="col-md-2 col-lg-3 control-label">Pilih File .xlsx:</label>
                                    <div class="col-sm-12 col-md-5 col-lg-5">
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" id="file_path_ddk" name="userfile" onclick="$('#file_ddk').click()">
                                            <input type="file" class="hidden" id="file_ddk" name="userfile" accept="application/octet-stream, application/vnd.ms-excel, application/x-csv, text/x-csv, text/csv, application/csv, application/excel, application/vnd.msexcel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel.sheet.macroenabled.12">
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-info" id="file_browser_ddk"><i class="fa fa-search"></i>
                                                    Browse
                                                </button>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-5 col-lg-4">
                                        <button type="submit" class="btn btn-block btn-success btn-sm" id="btn-impor">
                                             Impor Data DDK
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="modal fade" id="loading" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header btn-warning">
                                    <h4 class="modal-title" id="myModalLabel">Proses Impor ......</h4>
                                </div>
                                <div class="modal-body">
                                    Harap tunggu sampai proses impor selesai. Proses ini biasa memakan waktu antara 1
                                    (satu) Menit hingga 45 Menit, tergantung kecepatan komputer dan juga jumlah data
                                    yang di masukkan...
                                    <div class="text-center">
                                        <img src="https://open-sidp.test/assets/images/background/loading.gif?v707db34c054f1d55f0fda41f0e14bf06">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let terpilih = [];
        $(document).ready(function() {
            var TableData = $('#tabeldata').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: "{{ ci_route('prodeskel.datatablesDDK') }}",
                columns: [
                    {
                        data: 'ceklist',
                        class: 'padat',
                        searchable: false,
                        orderable: false,
                        render: function(data, type, full, meta){
                            if(terpilih.indexOf(full.id) != -1){
                                return `<input type="checkbox" checked name="id_cb[]" value="${full.id}"/>`;
                            }

                            return data;
                        }
                    },
                    {
                        data: 'no_kk',
                        name: 'no_kk',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'nama',
                        name: 'nama',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'dusun',
                        name: 'dusun',
                        searchable: true,
                        orderable: false
                    },
                    {
                        data: 'rw',
                        name: 'rw',
                        searchable: true,
                        orderable: false
                    },
                    {
                        data: 'rt',
                        name: 'rt',
                        searchable: true,
                        orderable: false
                    },
                ],
                order: [
                    [2, 'asc']
                ],
                "fnDrawCallback": function(oSettings) {
                    let terpilih_semua = true;
                    $(document).find('#tabeldata input[name="id_cb[]"]').each(function(index, el){
                        if(terpilih.indexOf(el.value) == -1){
                            terpilih_semua = false;
                        }
                    });
                    $('#tabeldata #checkall').prop('checked', terpilih_semua && terpilih.length > 0);
                }
            });

            $(document).on('click', '#tabeldata input[name="id_cb[]"]', function(ev){
                let el = ev.currentTarget;
                if(el.checked){
                    terpilih.push(el.value);
                }else if(terpilih.indexOf(el.value) != -1){
                    terpilih.splice(terpilih.indexOf(el.value), 1);
                }
                $('.unduhDataKeluargaTerpilih').prop('disabled', terpilih.length == 0).html(`<i class="fa fa-download"></i>Unduh ${terpilih.length} data terpilih`);
                $('#tabeldata #checkall').prop('checked', false);
            });
            $(document).on('click', '#tabeldata #checkall', function(ev){
                let el = ev.currentTarget;
                $(document).find('#tabeldata input[name="id_cb[]"]').each(function(index, el2){
                    if(el.checked && terpilih.indexOf(el2.value) == -1){
                        terpilih.push(el2.value);
                    }else if(!el.checked){
                        terpilih.splice(terpilih.indexOf(el2.value), 1);
                    }
                });
                $('.unduhDataKeluargaTerpilih').prop('disabled', terpilih.length == 0).html(`<i class="fa fa-download"></i>Unduh ${terpilih.length} data terpilih`);
            });
            $('.unduhSemuaDataKeluarga').on('click', function(){
                $('#unduhDataKeluargaForm input[name="ids"]').val('semua');
                $('#unduhDataKeluargaForm').submit();
            });
            $('.unduhDataKeluargaTerpilih').on('click', function(){
                $('#unduhDataKeluargaForm input[name="ids"]').val(terpilih);
                $('#unduhDataKeluargaForm').submit();
            });

            $('#impor').on('submit', function(){
                $('#loading').modal('show');
            });

            $('#file_ddk').change(function() {
                $('#file_path_ddk').val($(this).val());
            });

            $('#file_path_ddk').click(function() {
                $('#file_browser_ddk').click();
            });
        });
    </script>
@endpush
