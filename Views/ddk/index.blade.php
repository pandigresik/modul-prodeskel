@include('admin.layouts.components.asset_datatables')

@extends('admin.layouts.index')

@section('title')
<h1>
    Prodeskel <small>Data Dasar Keluarga (DDK)</small>
</h1>
@endsection

@section('breadcrumb')
    <li class="active">Prodeskel</li>
@endsection

@section('content')

    @include('admin.layouts.components.notifikasi')

    <div class="row">
        <div class="col-md-3" id="sub-menu">
          @include('navigasi')
        </div>
        <div class="col-md-9" id="main-content">
          <div class="box box-info">
            <div class="box-header with-border">
                @if (can('u'))
                    <a href="{{ site_url('prodeskel/ddk/impor') }}" class="btn btn-social btn-flat bg-navy btn-sm btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block" title="Impor Data Survey DDK" >
                        <i class="fa fa-upload"></i>Impor
                    </a>
                @endif
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="tabeldata">
                        <thead>
                            <tr>
                                {{-- <th><input type="checkbox" id="checkall" /></th> --}}
                                <th class="padat">Aksi</th>
                                <th class="padat">No KK (Aktif)</th>
                                <th class="padat">Kepala Keluarga</th>
                                <th>Periode</th>
                                <th>Terakhir Diubah</th>
                                <th>Dusun</th>
                                <th>RW</th>
                                <th>RT</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                {!! form_open(site_url('keluarga/filter/cari'), 'id="form-ke-keluarga"') !!}
                    <input id="cari" name="cari" type="hidden">
                </form>
            </div>
          </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var TableData = $('#tabeldata').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: "{{ ci_route('prodeskel.datatablesDDK') }}",
                columns: [
                    // {
                    //     data: 'ceklist',
                    //     class: 'padat',
                    //     searchable: false,
                    //     orderable: false
                    // },
                    {
                        data: 'aksi',
                        class: 'aksi',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'no_kk',
                        name: 'no_kk',
                        searchable: true,
                        orderable: true,
                        render: function(data, type){
                            if(type == 'display'){
                                return `<a href="#" class="buka-keluarga" onClick="$('#cari').val('` +  data  + `');formAction('form-ke-keluarga', '{{ site_url('keluarga/filter/cari') }}')">` + data + `</a>`;
                            }

                            return data;
                        }
                    },
                    {
                        data: 'nama',
                        name: 'nama',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'updated_at',
                        name: '',
                        searchable: false,
                        orderable: false,
                        render: function(data, type){
                            if(data){
                                return data.split('|')[2] + ' - ' + data.split('|')[1] ;
                            }
                            return '';
                        }
                    },
                    {
                        data: 'updated_at',
                        name: 'updated_at',
                        searchable: false,
                        orderable: true,
                        render: function(data, type){
                            if(data){
                                return data.split('|')[0];
                            }
                            return '';
                        }
                    },
                    {
                        data: 'dusun',
                        name: 'dusun',
                        searchable: true,
                        orderable: true
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
                ]
            });
            $('#tabeldata tbody').on('click', 'tr', function () {
                console.log(this);
                $(this).toggleClass('selected');
            });
        });
    </script>
@endpush
