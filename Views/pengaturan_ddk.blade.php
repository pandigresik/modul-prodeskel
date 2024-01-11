@php
  use Modules\Prodeskel\Enums\DDKEnum;
@endphp
@push('css')
    <style>
        ul{margin:0}
    </style>
@endpush
<div class="nav-tabs-custom">
    <ul class="nav nav-tabs" id="nav-tab-ddk" role="tablist">
        <li class="active"><a href="#bagian-ddk-info" data-toggle="tab" id="nav-bagian-ddk-info"><strong>Informasi</strong></a></li>
        <li><a href="#bagian-ddk-pengaturan" data-toggle="tab" id="nav-bagian-ddk-pengaturan"><strong>Pengaturan</strong></a></li>
    </ul>
</div>


<div class="tab-pane active" id="bagian-ddk-info">
    <hr style="margin: 3px 0 10px 0; width:100%">
    Beberapa point dibawah ini akan otomatis terpilih jika kondisinya terpenuhi<br>
    <br><strong># Data Dasar Keluarga</strong><br>
    1.25 Jenis Penyakit yang diderita Anggota Keluarga. Pilihan :
    <ul>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA)[1] }}". <b>Jika Anggota memiliki data Sakit Menahun </b> "1. JANTUNG"</li>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA)[2] }}". <b>Jika Anggota memiliki data Sakit Menahun </b> "2. LEVER"</li>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA)[3] }}". <b>Jika Anggota memiliki data Sakit Menahun </b> "3. PARU-PARU"</li>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA)[4] }}". <b>Jika Anggota memiliki data Sakit Menahun </b> "4. KANKER"</li>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA)[5] }}". <b>Jika Anggota memiliki data Sakit Menahun </b> "5. STROKE"</li>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA)[6] }}". <b>Jika Anggota memiliki data Sakit Menahun </b> "6. DIABETES MELITUS"</li>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA)[7] }}". <b>Jika Anggota memiliki data Sakit Menahun </b> "7. GINJAL"</li>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA)[8] }}". <b>Jika Anggota memiliki data Sakit Menahun </b> "8. MALARIA"</li>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA)[9] }}". <b>Jika Anggota memiliki data Sakit Menahun </b> "9. LEPRA/KUSTA"</li>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA)[10] }}". <b>Jika Anggota memiliki data Sakit Menahun </b> "10. HIV/AIDS"</li>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA)[11] }}". <b>Jika Anggota memiliki data Sakit Menahun </b> "11. GILA/STRESS"</li>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA)[12] }}". <b>Jika Anggota memiliki data Sakit Menahun </b> "12. TBC"</li>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA)[13] }}". <b>Jika Anggota memiliki data Sakit Menahun </b> "13. ASMA"</li>
    </ul>
    1.36 Masalah Kesejahteraan Keluarga. Pilihan :
    <ul>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_MASALAH_KESEJAHTERAAN_KELUARGA)[6] }}". <b>Jika ditemukan data pada</b> 2.2 Cacat Menurut Jenis (Cacat Mental) => [2.Gila/3.Stress]</li>
        <li>
            "{{ DDKEnum::valuesOf(DDKEnum::KODE_MASALAH_KESEJAHTERAAN_KELUARGA)[7] }}".
            <br><b>Jika ada yang terpilih pada form DDK Anggota </b> 2.2 Cacat Menurut Jenis (Cacat Fisik)
            <br><b>Atau Anggota memiliki data</b> "1. Cacat Fisik", "2. CACAT NETRA/BUTA", "3. CACAT RUNGU/WICARA" atau "5. CACAT FISIK DAN MENTAL"
        </li>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_MASALAH_KESEJAHTERAAN_KELUARGA)[8] }}". <b>Jika ditemukan data pada</b> 2.2 Cacat Menurut Jenis (Cacat Mental)</li>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_MASALAH_KESEJAHTERAAN_KELUARGA)[12] }}". <b>Jika kepala keluarga Perempuan dan memiliki data</b> "3. CERAI HIDUP" atau "4. CERAI MATI" </li>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_MASALAH_KESEJAHTERAAN_KELUARGA)[13] }}". <b>Jika kepala keluarga Laki-Laki memiliki data</b> "3. CERAI HIDUP" atau "4. CERAI MATI"</li>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_MASALAH_KESEJAHTERAAN_KELUARGA)[19] }}". <b>Jika Anggota memiliki data</b> "1. BELUM/TIDAK BEKERJA"</li>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_MASALAH_KESEJAHTERAAN_KELUARGA)[21] }}".</li>

    </ul>
    <br><strong># Data Dasar Anggota Keluarga</strong><br>
    2.2 Cacat Menurut Jenis (Cacat Fisik). Pilihan :
    <ul>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_CACAT_FISIK)[1] }}". <b>Jika Anggota memiliki data </b> "3. CACAT RUNGU/WICARA"</li>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_CACAT_FISIK)[2] }}". <b>Jika Anggota memiliki data </b> "3. CACAT RUNGU/WICARA"</li>
        <li>"{{ DDKEnum::valuesOf(DDKEnum::KODE_CACAT_FISIK)[3] }}". <b>Jika Anggota memiliki data </b> "2. CACAT NETRA/BUTA"</li>
    </ul>
</div>


<div class="tab-pane panel-group" id="bagian-ddk-pengaturan" role="tablist" aria-multiselectable="true">
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingOne">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" data-parent="#bagian-ddk-pengaturan" href="#form-pengaturan-ddk-pilihan-tambahan" aria-controls="form-pengaturan-ddk-pilihan-tambahan" aria-expanded="false">
                    Pilihan Tambahan
                </a><span class="caret"></span>
            </h4>
        </div>
        {!! form_open('', 'id="form-pengaturan-ddk-pilihan-tambahan" class="form-validasi panel-collapse collapse" role="tabpanel"') !!}
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" >
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 340px">Pertanyaan</th>
                                <th class="text-center">Pilihan Tambahan Ke-?</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($label as $kode_custom => $item_label)
                                {{-- Hanya tampilkan yg bisa memiliki custom value --}}
                                @if(is_array($item_label['custom_values']['value_long']))
                                    <tr>
                                        <td>
                                            {{ $item_label['label'] }}
                                        </td>
                                        <td class="text-right">
                                            {{-- Produksi Tahun ini --}}
                                            @if(str_contains($kode_custom, \Modules\Prodeskel\Enums\DDKEnum::KODE_PRODUKSI_TAHUN_INI))
                                                @foreach($item_label['custom_values']['value_long'] as $key_custom => $item_value)
                                                    @if( ! $loop->first)
                                                    <hr>
                                                    @endif
                                                    <div class="form-group row">
                                                    <label for="{{ $kode_custom.'['.$key_custom.']' }}a" class="col-sm-2 col-form-label">Komoditas (Ke {{$key_custom}})</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" id="{{ $kode_custom.'['.$key_custom.']' }}a" name="{{ $kode_custom.'['.$key_custom.'][komoditas]' }}"
                                                        value="{{ $item_value['komoditas'] }}"
                                                        class="form-control">
                                                    </div>
                                                    </div>
                                                    <div class="form-group row">
                                                    <label for="{{ $kode_custom.'['.$key_custom.']' }}b" class="col-sm-2 col-form-label">Satuan (Ke {{$key_custom}})</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" id="{{ $kode_custom.'['.$key_custom.']' }}b" name="{{ $kode_custom.'['.$key_custom.'][satuan]' }}"
                                                        value="{{ $item_value['satuan'] }}"
                                                        class="form-control">
                                                    </div>
                                                    </div>
                                                @endforeach

                                            @else
                                                @foreach($item_label['custom_values']['value_long'] as $key_custom => $item_value)
                                                    Ke {{ $key_custom }}
                                                    <input type="text" name="{{ $kode_custom.'['.$key_custom.']' }}"
                                                    value="{{ $item_value }}"
                                                    class="form-control" style="width:90%;display:inline">
                                                    <br>
                                                @endforeach

                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                @if($loop->index !== 0 && $loop->index % 5 == 0)
                                    <tr>
                                        <td colspan="2">
                                            <button type="submit" data-loading-text="Menyimpan..." class="btn btn-social pull-right btn-success btn-sm"><i class="fa fa-check"></i> Simpan Perubahan</button>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">
                                    <button type="submit" data-loading-text="Menyimpan..." class="btn btn-social pull-right btn-success btn-sm"><i class="fa fa-check"></i> Simpan Perubahan</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </form>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingOne">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" data-parent="#bagian-ddk-pengaturan" href="#form-pengaturan-ddk-sumber-data-ddk" aria-controls="form-pengaturan-ddk-pilihan-tambahan" aria-expanded="false">
                    Ubah Data untuk Semua DDK Sekaligus
                </a><span class="caret"></span>
            </h4>
        </div>
        <div id="form-pengaturan-ddk-sumber-data-ddk" class="form-validasi panel-collapse collapse" role="tabpanel">
            <div class="panel-body row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="bulan">Bulan</label>
                        @include('ddk.components.select_pilihan_prodeskel', [
                            'class' => 'select2',
                            'attribut' => 'name="bulan"',
                            'pilihan' => $bulan,
                            'selected_value' => '',
                        ])
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="tahun">Tahun</label>
                        @include('ddk.components.select_pilihan_prodeskel', [
                            'class' => 'select2',
                            'attribut' => 'name="tahun"',
                            'pilihan' => $tahun,
                            'selected_value' => '',
                        ])
                    </div>
                </div>
                <div class="col-md-12">
                    <button type="button" id="btn_kosongkan_dan_simpan_periode" class="btn btn-sm btn-success"><i class="fa fa-check"></i>Kosongkan Periode dan Simpan untuk semua data DDK</button>
                    <button type="button" id="btn_simpan_periode" class="btn btn-social btn-success btn-sm pull-right"><i class="fa fa-check"></i>Simpan Periode untuk semua data DDK</button>
                </div>
                <div class="col-md-12">
                    <hr>
                </div>
                <div class="col-md-12">
                    <h5>
                        Sumber Data Untuk Mengisi Data Dasar Keluarga
                        <button type="button" onclick="$('#sumber_data_1').val('Aplikasi OpenSID {{ $sebutan_desa . ' ' . $config->nama_desa }}')" class="btn-sm btn btn-info">
                            <i class="fa fa-check"></i> Terapkan Tulisan Placeholder untuk Sumber 1
                        </button>
                    </h5>
                    <table width="100%">
                        @for($i = 1; $i <= 4; $i++)
                            <tr>
                                <td width="10%">
                                    Sumber {{ $i }}

                                </td>
                                <td width="1%">:</td>
                                <td width="65%">
                                    <input id="sumber_data_{{ $i }}" name="sumber_data_{{ $i }}" maxlength="100" class="form-control input-sm alamat" type="text"
                                    placeholder="{{ ($i == 1 && ($ddk->{'sumber_data_' . $i } ?? null) == null) ? "Aplikasi OpenSID $sebutan_desa {$config->nama_desa}" : '' }}"
                                    value="{{ $ddk->{'sumber_data_' . $i } ?? '' }}">
                                </td>
                                <td>
                                    <button type="button" data-id="{{ $i }}" class="btn btn-social btn-success btn-sm btn_simpan_sumber_data_ddk"><i class="fa fa-check"></i>Simpan "Sumber {{$i}}" untuk semua data DDK</button>
                                </td>
                            </tr>
                        @endfor
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @include('admin.layouts.components.ajax_dtks')
    <script>
        $(function(){
            $('#form-pengaturan-ddk-pilihan-tambahan').on('submit', function(ev){
                ev.preventDefault();
                $('#form-pengaturan-ddk-pilihan-tambahan button').button('loading');

                Swal.fire({
                    title: 'Sedang Menyimpan',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });
                let data_form = $('#form-pengaturan-ddk-pilihan-tambahan').serializeArray();
                ajax_save_dtks('{{ ci_route("prodeskel.ddk.save.pengaturan") }}', {...data_form, 'tipe': 'pilihan-tambahan'},
                    callback_success = function(data){
                        $('#form-pengaturan-ddk-pilihan-tambahan button').button('reset');
                    },
                    callback_fail = function(xhr){
                        $('#form-pengaturan-ddk-pilihan-tambahan button').button('reset');
                    },
                    custom_config = {}
                );
            });
            $('#btn_kosongkan_dan_simpan_periode').on('click', function(){
                let btn = this;
                if(confirm('Anda yakin akan mengosongkan semua periode Data DDK')){
                    $(btn).button('loading');
                    Swal.fire({
                        title: 'Sedang Menyimpan',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });
                    ajax_save_dtks('{{ ci_route("prodeskel.ddk.save.pengaturan") }}', {
                            'bulan':null,
                            'tahun':null,
                            'tipe' : 'set-semua-periode'
                        },
                        callback_success = function(data){
                            $(btn).button('reset');
                        },
                        callback_fail = function(xhr){
                            $(btn).button('reset');
                        },
                        custom_config = {}
                    );
                }
            })
            $('#btn_simpan_periode').on('click', function(){
                let btn = this;
                if(confirm('Anda yakin akan mengubah semua periode Data DDK')){
                    $(btn).button('loading');
                    Swal.fire({
                        title: 'Sedang Menyimpan',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });
                    ajax_save_dtks('{{ ci_route("prodeskel.ddk.save.pengaturan") }}', {
                            'bulan': $('#form-pengaturan-ddk-sumber-data-ddk select[name="bulan"]').val(),
                            'tahun': $('#form-pengaturan-ddk-sumber-data-ddk select[name="tahun"]').val(),
                            'tipe' : 'set-semua-periode'
                        },
                        callback_success = function(data){
                            $(btn).button('reset');
                        },
                        callback_fail = function(xhr){
                            $(btn).button('reset');
                        },
                        custom_config = {}
                    );
                }
            });
            $('.btn_simpan_sumber_data_ddk').on('click', function(){
                let btn = this;
                if(confirm('Anda yakin akan mengubah semua Data DDK "Sumber Data Untuk Mengisi Data Dasar Keluarga" => Sumber ' + $(btn).data('id'))){
                    $(btn).button('loading');
                    Swal.fire({
                        title: 'Sedang Menyimpan',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });
                    ajax_save_dtks('{{ ci_route("prodeskel.ddk.save.pengaturan") }}', {
                            'sumber_data_ke': $(btn).data('id'),
                            'value': $('input[name="sumber_data_' + $(btn).data('id') + '"]').val(),
                            'tipe' : 'set-semua-sumber-data'
                        },
                        callback_success = function(data){
                            $(btn).button('reset');
                        },
                        callback_fail = function(xhr){
                            $(btn).button('reset');
                        },
                        custom_config = {}
                    );
                }
            });
        });
    </script>
@endpush