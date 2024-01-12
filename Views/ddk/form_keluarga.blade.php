@php
    use \Modules\Prodeskel\Enums\DDKEnum;
    use \Modules\Prodeskel\Enums\DDKPilihanProduksiTahunIniEnum;
    use \Modules\Prodeskel\Services\ProdeskelDDKPilihanServices;
    use \Modules\Prodeskel\Enums\DDKPilihanCheckboxEnum;
@endphp

<div class="row">
    <div class="col-md-6">
        <table width="100%">
            <tr>
                <td>Nomor Kartu Keluarga</td>
                <td>:</td>
                <td>{{ $keluarga->no_kk }}</td>
            </tr>
            <tr>
                <td>Nama Kepala Keluarga</td>
                <td>:</td>
                <td>{{ $keluarga->kepalaKeluarga->nama }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>:</td>
                <td>{{ $keluarga->kepalaKeluarga->alamat_wilayah }}</td>
            </tr>
            <tr>
                <td>RT / RW</td>
                <td>:</td>
                <td>{{ $keluarga->wilayah->rt . '/' . $keluarga->wilayah->rw }}</td>
            </tr>
            <tr>
                <td>Dusun / Lingkungan</td>
                <td>:</td>
                <td>{{ $keluarga->wilayah->dusun }}</td>
            </tr>
            <tr>
                <td>Desa / Kelurahan</td>
                <td>:</td>
                <td>{{ $config->nama_desa }}</td>
            </tr>
            <tr>
                <td>Kecamatan</td>
                <td>:</td>
                <td>{{ $config->nama_kecamatan }}</td>
            </tr>
            <tr>
                <td>Kabupaten / Kota</td>
                <td>:</td>
                <td>{{ $config->nama_kabupaten }}</td>
            </tr>
            <tr>
                <td>Provinsi</td>
                <td>:</td>
                <td>{{ $config->nama_propinsi }}</td>
            </tr>
        </table>
    </div>
    {{-- form samping --}}
    <div class="col-md-6">
        <hr>
        <table width="100%">
            <tr>
                <td width="20%">Bulan</td>
                <td width="1%">:</td>
                <td>
                    @include('ddk.components.select_pilihan_prodeskel', [
                        'class' => 'select2',
                        'attribut' => 'name="bulan"',
                        'pilihan' => $bulan,
                        'selected_value' => $ddk->bulan ?? '',
                    ])
                </td>
            </tr>
            <tr>
                <td>Tahun</td>
                <td>:</td>
                <td>
                    @include('ddk.components.select_pilihan_prodeskel', [
                        'class' => 'select2',
                        'attribut' => 'name="tahun"',
                        'pilihan' => $tahun,
                        'selected_value' => $ddk->tahun ?? '',
                    ])
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <button type="button" id="btn_kosongkan_periode" class="btn btn-sm btn-info">Kosongkan Periode</button>
                </td>
            </tr>
        </table>
    </div>
    <div class="col-md-6">
        <hr>
        <table width="100%">
            <tr>
                <td width="20%">Nama Pengisi</td>
                <td width="1%">:</td>
                <td>
                    <input name="nama_pengisi" maxlength="100" class="form-control input-sm nama" type="text" value="{{ $ddk->nama_pengisi ?? '' }}" >
                </td>
            </tr>
            <tr>
                <td>Pekerjaan</td>
                <td>:</td>
                <td>
                    <input name="pekerjaan" maxlength="100" class="form-control input-sm nama" type="text" value="{{ $ddk->pekerjaan ?? '' }}" >
                </td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>:</td>
                <td>
                    <input name="jabatan" maxlength="100" class="form-control input-sm nama" type="text" value="{{ $ddk->jabatan ?? '' }}" >
                </td>
            </tr>
        </table>
    </div>
    <div class="col-md-12">
        <h5>Sumber Data Untuk Mengisi Data Dasar Keluarga</h5>
        <table width="100%">
            @for($i = 1; $i <= 4; $i++)
                <tr>
                    <td width="20%">
                        Sumber {{ $i }}
                        @if($i == 1 && ($ddk->{'sumber_data_' . $i } ?? null) == null)
                            <button type="button" onclick="$('#sumber_data_{{ $i }}').val('Aplikasi OpenSID {{ $sebutan_desa . ' ' . $config->nama_desa }}')" class="btn-sm btn btn-info">
                                <i class="fa fa-check"></i> Terapkan Tulisan Placeholder
                            </button>
                        @endif
                    </td>
                    <td width="1%">:</td>
                    <td>
                        <input id="sumber_data_{{ $i }}" name="sumber_data_{{ $i }}" maxlength="100" class="form-control input-sm alamat" type="text"
                        placeholder="{{ ($i == 1 && ($ddk->{'sumber_data_' . $i } ?? null) == null) ? "Aplikasi OpenSID $sebutan_desa {$config->nama_desa}" : '' }}"
                        value="{{ $ddk->{'sumber_data_' . $i } ?? '' }}">
                    </td>
                </tr>
            @endfor
        </table>
        <button type="submit" class="btn btn-social btn-success btn-sm pull-right"><i class="fa fa-check"></i>Simpan semua data Tab</button>
    </div>
    {{-- Data Keluarga --}}
    <div class="col-md-12">
        <h5>1. Data Keluarga</h5>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="form-group">
            <label class="control-label" for="jumlah_penghasilan_perbulan">1.1 Jumlah Penghasilan Perbulan</label>
            <input name="jumlah_penghasilan_perbulan" maxlength="100" class="form-control input-sm bilangan_titik" type="text" value="{{ $ddk->jumlah_penghasilan_perbulan }}" >
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="form-group">
            <label class="control-label" for="jumlah_pengeluaran_perbulan">1.2 Jumlah Pengeluaran Perbulan</label>
            <input name="jumlah_pengeluaran_perbulan" maxlength="100" class="form-control input-sm bilangan_titik" type="text" value="{{ $ddk->jumlah_pengeluaran_perbulan }}" >
        </div>
    </div>
    {{-- 1.3 Status Kepemilikan Rumah --}}
    <div class="col-lg-4 col-md-6">
        <div class="form-group">
            <label class="control-label" for="status_kepemilikan_rumah">1.3 Status Kepemilikan Rumah</label>
            @include('ddk.components.select_pilihan_prodeskel', ['class' => 'select2', 'attribut' => 'name="'.DDKEnum::KODE_STATUS_KEPEMILIKAN_RUMAH.'"', 'pilihan' => ProdeskelDDKPilihanServices::statusKepemilikanRumah($custom_value), 'selected_value' => $ddk->detailKeluarga[DDKEnum::KODE_STATUS_KEPEMILIKAN_RUMAH]->value ?? ''])
        </div>
    </div>
    {{-- 1.11 Penguasaan Aset Tanah oleh Keluarga --}}
    <div class="col-lg-4 col-md-6">
        <div class="form-group">
            <label class="control-label" for="penguasaan_aset_tanah">1.11 Penguasaan Aset Tanah oleh Keluarga</label>
            @include('ddk.components.select_pilihan_prodeskel', ['class' => 'select2', 'attribut' => 'name="'.DDKEnum::KODE_PENGUASAAN_ASET_TANAH_OLEH_KELUARGA.'"', 'pilihan' => ProdeskelDDKPilihanServices::penguasaanAsetTanah($custom_value), 'selected_value' => $ddk->detailKeluarga[DDKEnum::KODE_PENGUASAAN_ASET_TANAH_OLEH_KELUARGA]->value ?? ''])
        </div>
    </div>
    {{-- 1.21 Perilaku hidup bersih dan sehat dalam Keluarga --}}
    <div class="col-lg-4 col-md-6">
        <div class="form-group">
            <label class="control-label" for="hidup_bersih_sehat">1.21 Perilaku hidup bersih dan sehat dalam Keluarga</label>
            @include('ddk.components.select_pilihan_prodeskel', ['class' => 'select2', 'attribut' => 'name="'.DDKEnum::KODE_PERILAKU_HIDUP_BERSIH_SEHAT.'"', 'pilihan' => ProdeskelDDKPilihanServices::perilakuHidupBersihSehat($custom_value), 'selected_value' => $ddk->detailKeluarga[DDKEnum::KODE_PERILAKU_HIDUP_BERSIH_SEHAT]->value ?? ''])
        </div>
    </div>
    {{-- 1.22 Pola makan Keluarga --}}
    <div class="col-lg-4 col-md-6">
        <div class="form-group">
            <label class="control-label" for="pola_makan_keluarga">1.22 Pola makan Keluarga</label>
            @include('ddk.components.select_pilihan_prodeskel', ['class' => 'select2', 'attribut' => 'name="'.DDKEnum::KODE_POLA_MAKAN_KELUARGA.'"', 'pilihan' => ProdeskelDDKPilihanServices::polaMakanKeluarga($custom_value), 'selected_value' => $ddk->detailKeluarga[DDKEnum::KODE_POLA_MAKAN_KELUARGA]->value ?? ''])
        </div>
    </div>
    {{-- 1.23 Kebiasaan berobat bila sakit dalam keluarga --}}
    <div class="col-lg-4 col-md-6">
        <div class="form-group">
            <label class="control-label" for="pengobatan_bila_sakit">1.23 Kebiasaan berobat bila sakit dalam keluarga</label>
            @include('ddk.components.select_pilihan_prodeskel', ['class' => 'select2', 'attribut' => 'name="'.DDKEnum::KODE_KEBIASAAN_BEROBAT_BILA_SAKIT.'"', 'pilihan' => ProdeskelDDKPilihanServices::kebiasaanBerobatBilaSakit($custom_value), 'selected_value' => $ddk->detailKeluarga[DDKEnum::KODE_KEBIASAAN_BEROBAT_BILA_SAKIT]->value ?? ''])
        </div>
    </div>
    {{-- 1.24 Status Gizi Balita dalam Keluarga --}}
    <div class="col-lg-4 col-md-6">
        <div class="form-group">
            <label class="control-label" for="status_gizi_balita">1.24 Status Gizi Balita dalam Keluarga</label>
            @include('ddk.components.select_pilihan_prodeskel', ['class' => 'select2', 'attribut' => 'name="'.DDKEnum::KODE_STATUS_GIZI_BALITA.'"', 'pilihan' => ProdeskelDDKPilihanServices::statusGiziBalita($custom_value), 'selected_value' => $ddk->detailKeluarga[DDKEnum::KODE_STATUS_GIZI_BALITA]->value ?? ''])
        </div>
    </div>
    {{-- 1.4 Sumber Air Minum --}}
    <hr style="margin: 3px 0 10px 0; width:100%">
    <div class="col-md-12" style="display: flow-root">
        <label class="control-label col-lg-4 col-md-12"  style="padding: 0">1.4 Sumber Air Minum yang digunakan anggota keluarga</label>
        <div class="col-lg-8 col-md-12">
            <div class="input-group input-group-sm">
                @include('ddk.components.select_pilihan_prodeskel', ['class' => 'select2', 'attribut' => 'multiple id="pilihan_sumber_air_minum"', 'pilihan' => ProdeskelDDKPilihanServices::sumberAirMinum($custom_value) ])
                <span class="input-group-btn">
                    <button type="button" class="btn btn-info" id="tambahkan_sumber_air_minum"><i class="fa fa-plus"></i>&nbsp;Tambahkan Sumber Air Minum</button>
                </span>
            </div>
        </div>
        <div style="margin: 3px 0;border: 0;" class="col-sm-12"></div>
        <table id="table_sumber_air_minum" class="col-xs-push-1 col-md-7 col-sm-11 col-xs-11">
            <tr>
                <th>Kode</th>
                <th>Sumber Air Minum</th>
                <th>Kondisi Sumber Air Minum</th>
            </tr>
            @foreach(ProdeskelDDKPilihanServices::sumberAirMinum($custom_value) as $ddk_field => $sumber_air_minum)
                @if(array_key_exists($ddk_field, $ddk->detailKeluarga[DDKEnum::KODE_SUMBER_AIR_MINUM]->value ?? []))
                    <tr>
                        <td>{{ $ddk_field }}</td>
                        <td>{{ $sumber_air_minum }}</td>
                        <td>
                            @include('ddk.components.select_pilihan_prodeskel', ['class' => 'select2', 'attribut' => 'name="'.DDKEnum::KODE_SUMBER_AIR_MINUM.'['. $ddk_field .']"', 'pilihan' => DDKEnum::valuesOf(DDKEnum::KODE_SUMBER_AIR_MINUM_CHECKBOX), 'selected_value' => $ddk->detailKeluarga[DDKEnum::KODE_SUMBER_AIR_MINUM]->value[$ddk_field] ])
                        </td>
                    </tr>
                @endif
            @endforeach
        </table>
    </div>
    <div class="col-lg-12">
        <button type="submit" class="btn btn-social btn-success btn-sm"><i class="fa fa-check"></i>Simpan semua data Tab</button>
    </div>
    {{-- 1.5 Kepemilikan Lahan --}}
    <hr style="margin: 3px 0 10px 0; width:100%">
    <div class="col-md-12" style="display: flow-root">
        <label class="control-label col-lg-4 col-md-12"  style="padding: 0">1.5 Kepemilikan Lahan</label>
        <div class="col-lg-8 col-md-12">
            <div class="input-group input-group-sm">
                @include('ddk.components.select_pilihan_prodeskel', ['class' => 'select2', 'attribut' => 'multiple id="pilihan_kepemilikan_lahan"', 'pilihan' => ProdeskelDDKPilihanServices::kepemilikanLahan($custom_value) ])
                <span class="input-group-btn">
                    <button type="button" class="btn btn-info" id="tambahkan_kepemilikan_lahan"><i class="fa fa-plus"></i>&nbsp;Tambahkan Kepemilikan Lahan</button>
                </span>
            </div>
        </div>
        <div style="margin: 3px 0;border: 0;" class="col-sm-12"></div>
        <table id="table_kepemilikan_lahan" class="col-xs-push-1 col-md-7 col-sm-11 col-xs-11">
            <tr>
                <th>Kode</th>
                <th>Jenis Lahan</th>
                <th>Luas Lahan (ha)</th>
            </tr>
            @foreach(ProdeskelDDKPilihanServices::kepemilikanLahan($custom_value) as $ddk_field => $kepemilikan_lahan)
                @if(array_key_exists($ddk_field, $ddk->detailKeluarga[DDKEnum::KODE_KEPEMILIKAN_LAHAN]->value ?? [] ))
                    <tr>
                        <td>{{ $ddk_field }}</td>
                        <td>{{ $kepemilikan_lahan }}</td>
                        <td>
                            @include('ddk.components.select_pilihan_prodeskel', ['class' => 'select2', 'attribut' => 'name="'.DDKEnum::KODE_KEPEMILIKAN_LAHAN.'['. $ddk_field .']"', 'pilihan' => DDKEnum::valuesOf(DDKEnum::KODE_KEPEMILIKAN_LAHAN_CHECKBOX), 'selected_value' => $ddk->detailKeluarga[DDKEnum::KODE_KEPEMILIKAN_LAHAN]->value[$ddk_field] ])
                        </td>
                    </tr>
                @endif
            @endforeach
        </table>
    </div>
    {{-- 1.6 Produksi tahun ini --}}
    <hr style="margin: 3px 0 10px 0; width:100%">
    <div class="col-md-12" style="display: flow-root; overflow-x: scroll">
        <label class="control-label col-lg-4 col-md-12"  style="padding: 0">1.6 Produksi tahun ini</label>
        <div class="col-lg-8 col-md-12">
            <div class="input-group input-group-sm">
                <select class="form-control input-sm select2" multiple style="width:100%;" id="pilihan_produksi_tahun_ini">
                    <option disabled selected value="">-----   Cari dan Pilih (bisa pilih lebih dari 1)   -----</option>
                    @foreach(ProdeskelDDKPilihanServices::produksiTahunIni($custom_value) as $optgroup => $komoditas_per_grup)
                        <optgroup label="{{ $optgroup }}">
                            @foreach($komoditas_per_grup as $item)
                                <option value="{{ $item['kode'] }}" data-kategori="{{ $optgroup }}" data-satuan="{{ $item['satuan'] }}">{{ $item['komoditas'] }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <span class="input-group-btn">
                    <button type="button" class="btn btn-info" id="tambahkan_produksi_tahun_ini"><i class="fa fa-plus"></i>&nbsp;Tambahkan Produksi Tahun Ini</button>
                </span>
            </div>
        </div>
        <div style="margin: 3px 0;border: 0;" class="col-sm-12"></div>
        <table id="table_produksi_tahun_ini" class="col-xs-push-1 col-xs-11">
            <tr>
                <th>Kode</th>
                <th>Komoditas</th>
                <th>Jumlah Pohon</th>
                <th>Luas Panen (m<sup>2</sup>)</th>
                <th>Nilai Produksi</th>
                <th>Satuan</th>
                <th>Pemasaran Hasil</th>
            </tr>
            @foreach(ProdeskelDDKPilihanServices::produksiTahunIni($custom_value) as $kategori => $komoditas_per_grup)
                @foreach($komoditas_per_grup as $item)
                    @php
                        $ddk_produksi_field = $item['kode'];
                        $produksi_tahun_ini = $item['komoditas'];
                        $is_jumlah_pohon = DDKPilihanProduksiTahunIniEnum::PENGATURAN[$kategori]['jumlah_pohon'];
                        $is_luas_panen = DDKPilihanProduksiTahunIniEnum::PENGATURAN[$kategori]['luas_panen'];
                        $ddk_produksi_data = $ddk->produksi ? $ddk->produksi->firstWhere('kode_komoditas', $ddk_produksi_field) : false;
                    @endphp
                    @if($ddk_produksi_data)
                        <tr>
                            <td>{{ $ddk_produksi_field }}</td>
                            <td>{{ $produksi_tahun_ini }}</td>
                            <td>
                                @if($is_jumlah_pohon)
                                    <input name="produksi_tahun_ini[{{ $ddk_produksi_field }}][jumlah_pohon]" maxlength="11" class="form-control input-sm bilangan_titik" type="text" value="{{ $ddk_produksi_data->jumlah_pohon }}" >
                                @endif
                            </td>
                            <td>
                                @if($is_luas_panen)
                                    <input name="produksi_tahun_ini[{{ $ddk_produksi_field }}][luas_panen]" maxlength="20" class="form-control input-sm bilangan_titik" type="text" value="{{ $ddk_produksi_data->luas_panen }}" >
                                @endif
                            </td>
                            <td>
                                <input name="produksi_tahun_ini[{{ $ddk_produksi_field }}][nilai_produksi_per_satuan]" maxlength="20" class="form-control input-sm bilangan_titik" type="text" value="{{ $ddk_produksi_data->nilai_produksi_per_satuan }}" >
                            </td>
                            <td>{{ $item['satuan']}}</td>
                            <td>
                                <input name="produksi_tahun_ini[{{ $ddk_produksi_field }}][pemasaran_hasil]" maxlength="150" class="form-control input-sm alamat" type="text" value="{{ $ddk_produksi_data->pemasaran_hasil }}" >
                            </td>
                        </tr>
                    @endif
                @endforeach
            @endforeach
        </table>
    </div>
    <div class="col-lg-12">
        <button type="submit" class="btn btn-social btn-success btn-sm"><i class="fa fa-check"></i>Simpan semua data Tab</button>
    </div>
    <hr style="margin: 3px 0 10px 0" class="col-sm-12">
    {{-- 1.7 Kepemilikan Jenis Ternak Keluarga Tahun ini --}}
    @include('ddk.components.table_3_kolom_dan_jumlah', [
        'label'           => '1.7 Kepemilikan Jenis Ternak Keluarga Tahun ini',
        'label_pilihan'   => 'Kepemilikan Ternak',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::kepemilikanJenisTernak($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_KEPEMILIKAN_JENIS_TERNAK_KELUARGA_TAHUN_INI,
        'td_judul_2'      => 'Jenis Binatang Ternak',
        'td_judul_3'      => 'Jumlah (ekor)',
    ])
    <hr style="margin: 3px 0 10px 0" class="col-sm-12">
    {{-- 1.8 Alat produksi budidaya ikan --}}
    @include('ddk.components.table_3_kolom_dan_jumlah', [
        'label'           => '1.8 Alat produksi budidaya ikan',
        'label_pilihan'   => 'Alat Produksi Budidaya Ikan',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::alatProduksiBudidayaIkan($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_ALAT_PRODUKSI_BUDIDAYA_IKAN,
        'td_judul_2'      => 'Nama Alat',
        'td_judul_3'      => 'Jumlah',
    ])
    <div class="col-lg-12">
        <button type="submit" class="btn btn-social btn-success btn-sm"><i class="fa fa-check"></i>Simpan semua data Tab</button>
    </div>
    <hr style="margin: 3px 0 10px 0" class="col-sm-12">
    {{-- (Pertolongan Persalinan) 1.18 Kualitas Persalinan dalam Keluarga (jika ada/pernah ada) --}}
    @include('ddk.components.table_3_kolom_dan_jumlah', [
        'label'           => '1.18.b Kualitas Pertolongan Persalinan dalam Keluarga (jika ada/pernah ada) ',
        'label_pilihan'   => 'Kualitas Persalinan dalam Keluarga',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::pilihanKualitasPertolonganPersalinan($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_KUALITAS_PERTOLONGAN_PERSALINAN,
        'td_judul_2'      => 'Pertolongan Persalinan',
        'td_judul_3'      => 'Jumlah',
    ])
    <hr style="margin: 3px 0 10px 0; width:100%">
    <hr style="margin: 3px 0 10px 0; width:100%">
    {{-- 1.9 Pemanfaatan Danau/Sungai/Waduk/situ/Mata Air oleh Keluarga --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.9 Pemanfaatan Danau/Sungai/Waduk/situ/Mata Air oleh Keluarga',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::pemanfaatanDanauSungaiWadukSituMataAir($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_PEMANFAATAN_DANAU_SUNGAI_WADUK_SITU_MATA_AIR_OLEH_KELUARGA,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_PEMANFAATAN_DANAU_SUNGAI_WADUK_SITU_MATA_AIR_OLEH_KELUARGA]->value ?? [],
    ])
    {{-- 1.10 Lembaga Pendidikan Yang Dimiliki Keluarga/Komunitas --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.10 Lembaga Pendidikan Yang Dimiliki Keluarga/Komunitas',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::lembagaPendidikan($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_LEMBAGA_PENDIDIKAN_YANG_DIMILIKI_KELUARGA_KOMUNITAS,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_LEMBAGA_PENDIDIKAN_YANG_DIMILIKI_KELUARGA_KOMUNITAS]->value ?? [],
    ])
    {{-- 1.12 Aset Sarana Transportasi Umum --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.12 Aset Sarana Transportasi Umum',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::asetSaranaTransportasiUmum($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_ASET_SARANA_TRANSPORTASI_UMUM,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_ASET_SARANA_TRANSPORTASI_UMUM]->value ?? [],
    ])
    {{-- 1.13 Aset Sarana Produksi --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.13 Aset Sarana Produksi',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::asetSaranaProduksi($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_ASET_SARANA_PRODUKSI,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_ASET_SARANA_PRODUKSI]->value ?? [],
    ])
    <div class="col-lg-12">
        <button type="submit" class="btn btn-social btn-success btn-sm"><i class="fa fa-check"></i>Simpan semua data Tab</button>
    </div>
    {{-- 1.14 Aset Perumahan (Dinding) --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.14 Aset Perumahan (Dinding)',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::asetPerumahanDinding($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_ASET_PERUMAHAN_DINDING,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_ASET_PERUMAHAN_DINDING]->value ?? [],
    ])
    {{-- 1.14 Aset Perumahan (Lantai) --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.14 Aset Perumahan (Lantai)',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::asetPerumahanLantai($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_ASET_PERUMAHAN_LANTAI,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_ASET_PERUMAHAN_LANTAI]->value ?? [],
    ])
    {{-- 1.14 Aset Perumahan (Atap) --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.14 Aset Perumahan (Atap)',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::asetPerumahanAtap($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_ASET_PERUMAHAN_ATAP,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_ASET_PERUMAHAN_ATAP]->value ?? [],
    ])
    {{-- 1.15 Aset Lainnya dalam Keluarga --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.15 Aset Lainnya dalam Keluarga',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::asetLainnya($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_ASET_LAINNYA,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_ASET_LAINNYA]->value ?? [],
    ])
    {{-- 1.16 Kualitas Ibu Hamil dalam Keluarga (jika ada/pernah ada ibu hamil/nifas) --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.16 Kualitas Ibu Hamil dalam Keluarga (jika ada/pernah ada ibu hamil/nifas)',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::kualitasIbuHamil($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_KUALITAS_IBU_HAMIL,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_KUALITAS_IBU_HAMIL]->value ?? [],
    ])
    <div class="col-lg-12">
        <button type="submit" class="btn btn-social btn-success btn-sm"><i class="fa fa-check"></i>Simpan semua data Tab</button>
    </div>
    {{-- 1.17	Kualitas Bayi dalam Keluarga (jika ada/pernah ada bayi) --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.17	Kualitas Bayi dalam Keluarga (jika ada/pernah ada bayi)',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::kualitasBayi($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_KUALITAS_BAYI,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_KUALITAS_BAYI]->value ?? [],
    ])
    {{-- (Tempat Persalinan) 1.18 Kualitas Persalinan dalam Keluarga (jika ada/pernah ada) --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.18.a Kualitas Tempat Persalinan dalam Keluarga (jika ada/pernah ada)',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::pilihanKualitasTempatPersalinan($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_KUALITAS_TEMPAT_PERSALINAN,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_KUALITAS_TEMPAT_PERSALINAN]->value ?? [],
    ])
    {{-- 1.19 Cakupan Imunisasi --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.19 Cakupan Imunisasi',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::cakupanImunisasi($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_CAKUPAN_IMUNISASI,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_CAKUPAN_IMUNISASI]->value ?? [],
    ])
    {{-- 1.20 Penderita Sakit dan Kelainan dalam Keluarga (jika ada/pernah) --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.20 Penderita Sakit dan Kelainan dalam Keluarga (jika ada/pernah)',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::penderitaSakitKelainan($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_PENDERITA_SAKIT_DAN_KELAINAN,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_PENDERITA_SAKIT_DAN_KELAINAN]->value ?? [],
    ])
    {{-- 1.25 Jenis Penyakit yang diderita Anggota Keluarga --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.25 Jenis Penyakit yang diderita Anggota Keluarga {sync}',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::jenisPenyakitAnggotaKeluaga($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_JENIS_PENYAKIT_ANGGOTA_KELUARGA]->value ?? [],
    ])
    <div class="col-lg-12">
        <button type="submit" class="btn btn-social btn-success btn-sm"><i class="fa fa-check"></i>Simpan semua data Tab</button>
    </div>
    {{-- 1.26 Kerukunan --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.26 Kerukunan',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::kerukunan($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_KERUKUNAN,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_KERUKUNAN]->value ?? [],
    ])
    {{-- 1.27 Perkelahian --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.27 Perkelahian',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::perkelahian($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_PERKELAHIAN,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_PERKELAHIAN]->value ?? [],
    ])
    {{-- 1.28 Pencurian --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.28 Pencurian',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::pencurian($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_PENCURIAN,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_PENCURIAN]->value ?? [],
    ])
    {{-- 1.29 Penjarahan --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.29 Penjarahan',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::penjarahan($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_PENJARAHAN,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_PENJARAHAN]->value ?? [],
    ])
    {{-- 1.30 Perjudian --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.30 Perjudian',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::perjudian($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_PERJUDIAN,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_PERJUDIAN]->value ?? [],
    ])
    <div class="col-lg-12">
        <button type="submit" class="btn btn-social btn-success btn-sm"><i class="fa fa-check"></i>Simpan semua data Tab</button>
    </div>
    {{-- 1.31 Pemakaian Miras dan Narkoba --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.31 Pemakaian Miras dan Narkoba',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::pemakaianMirasDanNarkoba($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_PEMAKAIAN_MIRAS_DAN_NARKOBA,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_PEMAKAIAN_MIRAS_DAN_NARKOBA]->value ?? [],
    ])
    {{-- 1.32 Pembunuhan --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.32 Pembunuhan',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::pembunuhan($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_PEMBUNUHAN,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_PEMBUNUHAN]->value ?? [],
    ])
    {{-- 1.33 Penculikan --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.33 Penculikan',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::penculikan($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_PENCULIKAN,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_PENCULIKAN]->value ?? [],
    ])
    {{-- 1.34 Kejahatan seksual --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.34 Kejahatan seksual',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::kejahatanSeksual($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_KEJAHATAN_SEKSUAL,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_KEJAHATAN_SEKSUAL]->value ?? [],
    ])
    {{-- 1.35 Kekerasan Dalam Keluarga/Rumah Tangga --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.35 Kekerasan Dalam Keluarga/Rumah Tangga',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::kekerasanDalamKeluargaAtauRumahTangga($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_KEKERASAN_DALAM_KELUARGA_ATAU_RUMAH_TANGGA,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_KEKERASAN_DALAM_KELUARGA_ATAU_RUMAH_TANGGA]->value ?? [],
    ])
    {{-- 1.36 Masalah Kesejahteraan Keluarga --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '1.36 Masalah Kesejahteraan Keluarga {sync}',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::masalahKesejahteraanKeluarga($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_MASALAH_KESEJAHTERAAN_KELUARGA,
        'value_to_be_compared' => $ddk->detailKeluarga[DDKEnum::KODE_MASALAH_KESEJAHTERAAN_KELUARGA]->value ?? [],
    ])
</div>

@push('scripts')
    <script>
        $(function(){
            let $kode_tersimpan = [];
            let $jml_ternak = '{{ DDKEnum::KODE_KEPEMILIKAN_JENIS_TERNAK_KELUARGA_TAHUN_INI }}';
            let $jml_alat_produksi_ikan = '{{ DDKEnum::KODE_ALAT_PRODUKSI_BUDIDAYA_IKAN }}';
            let $jml_kualitas_pp = '{{ DDKEnum::KODE_KUALITAS_PERTOLONGAN_PERSALINAN }}';

            ['sumber_air_minum', 'kepemilikan_lahan', 'produksi_tahun_ini', $jml_ternak, $jml_alat_produksi_ikan, $jml_kualitas_pp].forEach(kategori => {
                if($('#table_'+ kategori +' tr').length == 1){
                    $('#table_'+ kategori +'').hide();
                }else{
                    $('#table_'+ kategori ).prev().html('<div class="alert alert-warning" style="margin: 0 auto;padding: 0px;text-align-last: center;user-select: none;">Kosongkan pilihan dan isian jika baris data tidak ingin disimpan/ingin dihapus</div>');
                    $('#table_'+ kategori +' tr').each(function(index, item){
                        if(kategori == 'produksi_tahun_ini'){
                            $kode_tersimpan.push($(item).find('td').eq(0).text());
                        }else{
                            $kode_tersimpan.push(kategori +'_' + $(item).find('td').eq(0).text());
                        }
                        // ulangi header(tr pertama) per 10 baris
                        if(index % 10 == 0 && index != 0){
                            $('#table_'+ kategori +' tr').eq(0).clone().insertAfter(item);
                        }
                    });
                }
            });
            $('#tambahkan_sumber_air_minum, #tambahkan_kepemilikan_lahan, #tambahkan_produksi_tahun_ini, #tambahkan_' + $jml_ternak + ', #tambahkan_' + $jml_alat_produksi_ikan + ', #tambahkan_' + $jml_kualitas_pp).on('click', function(ev){
                let kategori = ev.currentTarget.id.replace('tambahkan_', '');
                let template_select = '';
                let template_input_jumlah = '';
                let pengaturan_produksi = '';
                if(kategori == 'sumber_air_minum'){
                    template_select = `@include('ddk.components.select_pilihan_prodeskel', [
                        'class' => 'select2 {kode}',
                        'attribut' => 'name="'. DDKEnum::KODE_SUMBER_AIR_MINUM . '[{kode}]"',
                        'pilihan' => DDKEnum::valuesOf(DDKEnum::KODE_SUMBER_AIR_MINUM_CHECKBOX),
                    ])`;
                }else if(kategori == 'kepemilikan_lahan'){
                    template_select = `@include('ddk.components.select_pilihan_prodeskel', [
                        'class' => 'select2 {kode}',
                        'attribut' => 'name="'. DDKEnum::KODE_KEPEMILIKAN_LAHAN .'[{kode}]"',
                        'pilihan' => DDKEnum::valuesOf(DDKEnum::KODE_KEPEMILIKAN_LAHAN_CHECKBOX),
                    ])`;
                }else if(kategori == 'produksi_tahun_ini'){
                    pengaturan_produksi = {!! json_encode(DDKPilihanProduksiTahunIniEnum::PENGATURAN) !!};
                }else if([$jml_ternak, $jml_alat_produksi_ikan, $jml_kualitas_pp].indexOf(kategori) !== -1 ){
                    template_input_jumlah = '<input name="' + kategori + '[{kode}]" maxlength="5" class="form-control input-sm bilangan" type="text">';
                }else{
                    Swal.fire({
                        icon: 'error',
                        html: 'Event tidak ditemukan, Silahkan hubungi developer',
                        timer: 1000,
                    });
                    return;
                }

                function tambahkan_pilihan_ke_tabel(kategori, pilihan, pilihan_text, judul_notif_khusus, template_select, template_input_jumlah, pengaturan_produksi){
                    if($kode_tersimpan.indexOf(kategori + '_' + pilihan) != -1){
                        $(document).find('#' + pilihan).focus();
                        return;
                    }
                    $kode_tersimpan.push(kategori + '_' + pilihan);
                    $('#table_' + kategori).show();
                    $('#table_' + kategori).prev().html('<div class="alert alert-warning" style="margin: 0 auto;padding: 0px;text-align-last: center;user-select: none;">Kosongkan pilihan dan isian jika baris data tidak ingin disimpan/ingin dihapus</div>');

                    if(kategori == 'produksi_tahun_ini'){
                        let satuan_produksi = $('#pilihan_'+kategori+' option[value="' + pilihan + '"]').data('satuan'),
                            kategori_produksi = $('#pilihan_'+kategori+' option[value="' + pilihan + '"]').data('kategori');
                            jumlah_pohon = '',
                            luas_panen = '',
                            nilai_produksi_per_satuan = '<input name="produksi_tahun_ini[' + pilihan + '][nilai_produksi_per_satuan]" maxlength="20" class="form-control input-sm bilangan_titik" type="text">',
                            pemasaran_hasil = '<input name="produksi_tahun_ini[' + pilihan + '][pemasaran_hasil]" maxlength="150" class="form-control input-sm alamat" type="text">';

                        if(pengaturan_produksi[kategori_produksi]['jumlah_pohon']){
                            jumlah_pohon = '<input name="produksi_tahun_ini[' + pilihan + '][jumlah_pohon]" maxlength="11" class="form-control input-sm bilangan_titik" type="text">';
                        }
                        if(pengaturan_produksi[kategori_produksi]['luas_panen']){
                            luas_panen = '<input name="produksi_tahun_ini[' + pilihan + '][luas_panen]" maxlength="20" class="form-control input-sm bilangan_titik" type="text">';
                        }
                        $('#table_' + kategori).append(
                            '<tr>' +
                                '<td>' + pilihan + '</td>' +
                                '<td>' + pilihan_text + '</td>' +
                                '<td>' + jumlah_pohon + '</td>' +
                                '<td>' + luas_panen + '</td>' +
                                '<td>' + nilai_produksi_per_satuan +'</td>' +
                                '<td>' + satuan_produksi + '</td>' +
                                '<td>' + pemasaran_hasil + '</td>' +
                            '</tr>'
                        );
                    }else if([$jml_ternak, $jml_alat_produksi_ikan, $jml_kualitas_pp].indexOf(kategori) !== -1 ){
                        $('#table_' + kategori).append(
                            '<tr>' +
                                '<td>' + pilihan.replace(kategori + '_', '') + '</td>' +
                                '<td>' + pilihan_text + '</td>' +
                                '<td>' + template_input_jumlah.replaceAll('{kode}', pilihan) + '</td>' +
                            '</tr>'
                        );
                    }else{
                        $('#table_' + kategori).append(
                            '<tr>' +
                                '<td>' + pilihan.replace(kategori + '_', '') + '</td>' +
                                '<td>' + pilihan_text + '</td>' +
                                '<td>' + template_select.replaceAll('{kode}', pilihan) + '</td>' +
                            '</tr>'
                        );
                        $(document).find('.' + pilihan).select2({
                            width: '100%',
                            dropdownAutoWidth : true
                        });
                    }

                    setTimeout(() => {
                        $('#pilihan_'+kategori).val(['']).trigger('change');
                    }, 500);
                }

                let judul_notif_khusus = $(document).find('#table_' + kategori).find('tr').eq(0).find('th').eq(1).text();
                let pilihan            = $('#pilihan_'+kategori).val();
                if((typeof pilihan == 'object' || typeof pilihan == 'array') && pilihan.length == 0 || pilihan == ''){
                    Swal.fire({
                        icon: 'info',
                        html: 'Silahkan pilih ' + judul_notif_khusus,
                        timer: 1000,
                    });
                    return;
                }
                if(typeof pilihan == 'object' || typeof pilihan == 'array'){
                    // Loop through the array and set the selected value of the select element
                    $.each(pilihan, function(index, value_pilihan) {
                        let pilihan_text = $('#pilihan_'+kategori+' option[value="' + value_pilihan + '"]').text();
                        tambahkan_pilihan_ke_tabel(kategori, value_pilihan, pilihan_text, judul_notif_khusus, template_select, template_input_jumlah, pengaturan_produksi);
                    });
                }else{
                    let pilihan_text = $('#pilihan_'+kategori+' option:selected').text();
                    tambahkan_pilihan_ke_tabel(kategori, pilihan, pilihan_text, judul_notif_khusus, template_select, template_input_jumlah, pengaturan_produksi);
                }
            });
            $('#btn_kosongkan_periode').on('click', function(){
                $('select[name="bulan"]', document).val('').trigger('change');
                $('select[name="tahun"]', document).val('').trigger('change');
            });
        })
    </script>
@endpush