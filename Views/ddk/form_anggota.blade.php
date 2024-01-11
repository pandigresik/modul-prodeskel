@php
    use \App\Enums\JenisKelaminEnum;
    use \Modules\Prodeskel\Enums\DDKEnum;
    use \Modules\Prodeskel\Enums\DDKPilihanBahanGalianAnggotaEnum;
    use \App\Services\ProdeskelDDKPilihanServices;
@endphp

<div class="row">
    <div class="col-md-12">
        <h5>2.1 Biodata</h5>
        <table width="100%">
            <tr>
                <td>2.1.1</td>
                <td>Nomor Urut</td>
                <td>:</td>
                <td>{{ $no_urut }}</td>
            </tr>
            <tr>
                <td>2.1.2</td>
                <td>Nomor Induk Kependudukan (NIK)</td>
                <td>:</td>
                <td>{{ $anggota->nik }}</td>
            </tr>
            <tr>
                <td>2.1.3</td>
                <td>Nama Lengkap </td>
                <td>:</td>
                <td>{{ $anggota->nama }}</td>
            </tr>
            <tr>
                <td>2.1.4</td>
                <td>Nomor Akte Kelahiran</td>
                <td>:</td>
                <td>{{ $anggota->akta_lahir }}</td>
            </tr>
            <tr>
                <td>2.1.5</td>
                <td>Jenis Kelamin</td>
                <td>:</td>
                <td>{{ JenisKelaminEnum::valueOf($anggota->sex) }}</td>
            </tr>
            <tr>
                <td>2.1.6</td>
                <td>Hubungan dengan Kepala Keluarga</td>
                <td>:</td>
                <td>
                    {!! baris_pilihan_di_coret(
                            $hub_tersedia = [
                                'Istri'     => 3, // ISTRI
                                'Suami'     => 2, // SUAMI
                                'Anak'      => 4, // ANAK
                                'Cucu'      => 6, // CUCU
                                'Mertua'    => 8, // MERTUA
                                'Menantu'   => 5, // MENANTU
                                'Keponakan' => 9, // FAMILIAIN
                                'Lain-Lain' => 11, // LAINNYA
                            ],
                            $compare_value = $anggota->kk_level,
                            $default_key = false,
                    ) !!}
                </td>
            </tr>
            <tr>
                <td>2.1.7</td>
                <td>Tempat Lahir</td>
                <td>:</td>
                <td>{{ $anggota->tepatlahir }}</td>
            </tr>
            <tr>
                <td>2.1.8</td>
                <td>Tanggal Lahir</td>
                <td>:</td>
                <td>{{ tgl_indo($anggota->tanggallahir) }}</td>
            </tr>
            <tr>
                <td>2.1.9</td>
                <td>Tanggal Pencatatan</td>
                <td>:</td>
                <td><input name="{{ DDKEnum::KODE_TANGGAL_PENCATATAN_LAHIR }}[{{ $anggota->id }}]" type="date" class="form-control input-sm"
                    value="{{ $ddk->detailAnggota[$anggota->id][DDKEnum::KODE_TANGGAL_PENCATATAN_LAHIR]->value ?? '' }}"></td>
            </tr>
            <tr>
                <td>2.1.10</td>
                <td>Status Perkawinan</td>
                <td>:</td>
                <td>
                    {!! baris_pilihan_di_coret(
                            $hub_tersedia = [
                                'Kawin' => 1, // KAWIN
                                'Belum Kawin' => 2, // BELUM KAWIN
                                'Pernah Kawin' => [
                                    3, // CERAI HIDUP
                                    4, // CERAI MATI
                                ]
                            ],
                            $compare_value = $anggota->status_kawin,
                            $default_key = false,
                    ) !!}
                </td>
            </tr>
            <tr>
                <td>2.1.11</td>
                <td>Agama dan Aliran Kepercayaan</td>
                <td>:</td>
                <td>
                    {!! baris_pilihan_di_coret(
                        $hub_tersedia = [
                            'Islam'         => 1, // ISLAM
                            'Protestan'     => 7, // Kepercayaan Terhadap Tuhan YME / Lainnya
                            'Katolik'       => 3, // KATHOLIK
                            'Hindu'         => 4, // HINDU
                            'Budha'         => 5, // BUDHA
                            'Kong Hu Chu'   => 6, // KHONGHUCU
                        ],
                        $compare_value = $anggota->agama_id,
                        $default_key = false,
                    ) !!}
                </td>
            </tr>
            <tr>
                <td>2.1.12</td>
                <td>Golongan Darah</td>
                <td>:</td>
                <td>
                    {!! baris_pilihan_di_coret(
                        $hub_tersedia = [
                            'O'  => [
                                4, //O
                                11, //O+
                                12, //O-
                            ],
                            'A'  => [
                                1, //A
                                5, //A+
                                6, //A-
                            ],
                            'B'  => [
                                2, //B
                                7, //B+
                                8, //B-
                            ],
                            'AB' => [
                                3, //AB
                                9, //AB+
                                10, //AB-
                            ],
                        ],
                        $compare_value = $anggota->agama_id,
                        $default_key = false,
                    ) !!}
                </td>
            </tr>
            <tr>
                <td>2.1.13</td>
                <td>Kewarganegaraan/Etnis/Suku</td>
                <td>:</td>
                <td>{{ $anggota->suku }}</td>
            </tr>
            <tr>
                <td>2.1.14</td>
                <td>Pendidikan Umum Terakhir</td>
                <td>:</td>
                <td>
                    {!! baris_pilihan_di_coret(
                            $hub_tersedia = [
                                'SD'        => 3, // TAMAT SD / SEDERAJAT
                                'SMP'       => 4, // SLTP/SEDERAJAT
                                'SMA'       => 5, // SLTA / SEDERAJAT
                                'Diploma'   => [
                                    6, // DIPLOMA I / II
                                    7, // AKADEMI/ DIPLOMA III/S. MUDA
                                ],
                                'S1'        => 8, // DIPLOMA IV/ STRATA I
                                'S2'        => 9, // STRATA II
                                'S3'        => 10, // STRATA III
                            ],
                            $compare_value = $anggota->pendidikan_kk_id,
                            $default_key = ' ',
                    ) !!}
                </td>
            </tr>
            <tr>
                <td>2.1.15</td>
                <td>Mata Pencaharian Pokok/Pekerjaan</td>
                <td>:</td>
                <td>{{ $anggota->pekerjaan->nama }}</td>
            </tr>
            <tr>
                <td>2.1.16</td>
                <td>Nama Bapak/Ibu Kandung</td>
                <td>:</td>
                <td>{{ $anggota->nama_ayah }} / {{ $anggota->nama_ibu}}</td>
            </tr>
            <tr>
                <td>2.1.17</td>
                <td>Akseptor KB</td>
                <td>:</td>
                <td>
                    <select class="form-control input-sm select2" name="{{ DDKEnum::KODE_AKSEPTOR_KB . '['. $anggota->id . '][]' }}" multiple style="width:100%;">
                        <option selected disabled value="">-----   {{ 'Cari dan Pilih (bisa pilih lebih dari 1)' }}   -----</option>
                        @foreach(DDKEnum::valuesOf(DDKEnum::KODE_AKSEPTOR_KB) as $kode => $item)
                            <option value="{{ $kode }}" @selected(($kode . '' === ''. $ddk->detailAnggota[$anggota->id][DDKEnum::KODE_AKSEPTOR_KB]->value ?? false) || (in_array($kode . '', $ddk->detailAnggota[$anggota->id][DDKEnum::KODE_AKSEPTOR_KB]->value ?? [])))>
                                {{ $item }}
                            </option>
                        @endforeach
                    </select>
                </td>
            </tr>
        </table>
    </div>
    <div class="col-lg-12">
        <button type="submit" class="btn btn-social btn-success btn-sm"><i class="fa fa-check"></i>Simpan semua data Tab</button>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label class="control-label" for="lembaga_pemerintah">2.4 Lembaga Pemerintahan Yang Diikuti Anggota Keluarga</label>
            @include('ddk.components.select_pilihan_prodeskel', [
                'class' => 'select2',
                'attribut' => 'name="'.DDKEnum::KODE_LEMBAGA_PEMERINTAHAN_YANG_DIIKUTI_ANGGOTA.'['.$anggota->id.']"',
                'pilihan' => ProdeskelDDKPilihanServices::lembagaPemerintahanAgt($custom_value),
                'selected_value' => $ddk->detailAnggota[$anggota->id][DDKEnum::KODE_LEMBAGA_PEMERINTAHAN_YANG_DIIKUTI_ANGGOTA]->value ?? ''
            ])
        </div>
    </div>
    {{-- 2.2 Cacat Menurut Jenis (Cacat Fisik) --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '2.2 Cacat Menurut Jenis (Cacat Fisik) {sync}',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::cacatFisik($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_CACAT_FISIK,
        'value_to_be_compared' => $ddk->detailAnggota[$anggota->id][DDKEnum::KODE_CACAT_FISIK]->value ?? [],
        'anggota_id'      => $anggota->id,
    ])
    {{-- 2.2 Cacat Menurut Jenis (Cacat Mental) --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '2.2 Cacat Menurut Jenis (Cacat Mental)',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::cacatMental($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_CACAT_MENTAL,
        'value_to_be_compared' => $ddk->detailAnggota[$anggota->id][DDKEnum::KODE_CACAT_MENTAL]->value ?? [],
        'anggota_id'      => $anggota->id,
    ])
    {{-- 2.3 Kedudukan Anggota Keluarga sebagai Wajib Pajak dan Retribusi --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '2.3 Kedudukan Anggota Keluarga sebagai Wajib Pajak dan Retribusi',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::kedudukanAgtWajibPajakRetribusi($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_KEDUDUKAN_ANGGOTA_SEBAGAI_WAJIB_PAJAK_DAN_RETRIBUSI,
        'value_to_be_compared' => $ddk->detailAnggota[$anggota->id][DDKEnum::KODE_KEDUDUKAN_ANGGOTA_SEBAGAI_WAJIB_PAJAK_DAN_RETRIBUSI]->value ?? [],
        'anggota_id'      => $anggota->id,
    ])
    {{-- 2.5 Lembaga Kemasyarakatan Yang Diikuti Anggota Keluarga  --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '2.5 Lembaga Kemasyarakatan Yang Diikuti Anggota Keluarga',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::lembagaKemasyarakatanAgt($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_LEMBAGA_KEMASYARAKATAN_YANG_DIIKUTI_ANGGOTA,
        'value_to_be_compared' => $ddk->detailAnggota[$anggota->id][DDKEnum::KODE_LEMBAGA_KEMASYARAKATAN_YANG_DIIKUTI_ANGGOTA]->value ?? [],
        'anggota_id'      => $anggota->id,
    ])
    {{-- 2.6 Lembaga Ekonomi Yang Dimiliki Anggota Keluarga  --}}
    @include('ddk.components.pilihan_multiple', [
        'label'           => '2.6 Lembaga Ekonomi Yang Dimiliki Anggota Keluarga',
        'daftar_pilihan'  => ProdeskelDDKPilihanServices::lembagaEkonomiAgt($custom_value),
        'kode_unik_tabel' => DDKEnum::KODE_LEMBAGA_EKONOMI_YANG_DIMILIKI_ANGGOTA,
        'value_to_be_compared' => $ddk->detailAnggota[$anggota->id][DDKEnum::KODE_LEMBAGA_EKONOMI_YANG_DIMILIKI_ANGGOTA]->value ?? [],
        'anggota_id'      => $anggota->id,
    ])
    <div class="col-lg-12">
        <button type="submit" class="btn btn-social btn-success btn-sm"><i class="fa fa-check"></i>Simpan semua data Tab</button>
    </div>
    {{-- 2.7 Produksi bahan galian yang dimiliki anggota keluarga --}}
    <hr style="margin: 3px 0 10px 0; width:100%">
    <div class="col-md-12 bahan_galian" data-anggota="{{ $anggota->id }}" style="display: flow-root; overflow-x: scroll">
        <label class="control-label col-md-4"  style="padding: 0">2.7 Produksi bahan galian yang dimiliki anggota keluarga</label>
        <div class="col-md-8">
            <div class="input-group input-group-sm">
                <select class="form-control input-sm select2 pilihan_bahan_galian" multiple style="width:100%;">
                    <option disabled selected value="">-----   Cari dan Pilih (bisa pilih lebih dari 1)   -----</option>
                    @foreach(ProdeskelDDKPilihanServices::produksiBahanGalianAgt($custom_value) as $key => $item)
                        <option value="{{ $key }}">{{ $item }}</option>
                    @endforeach
                </select>
                <span class="input-group-btn">
                    <button type="button" class="btn btn-info tambahkan_bahan_galian"><i class="fa fa-plus"></i>&nbsp;Pilih dan Tambahkan Jenis Bahan Galian</button>
                </span>
            </div>
        </div>
        <div style="margin: 3px 0;border: 0;" class="col-sm-12"></div>
        <table class="col-xs-push-1 col-xs-11 table_bahan_galian">
            <tr>
                <th rowspan="2">Kode</th>
                <th rowspan="2">Jenis bahan galian</th>
                <th colspan="3">Pemilik dan Produksi Bahan Galian (Ton/Tahun)</th>
                <th rowspan="2">Pemasaran Hasil</th>
            </tr>
            <tr>
                <th>Nilai Produksi</th>
                <th>Milik Adat</th>
                <th>Perorangan</th>
            </tr>
            @foreach(ProdeskelDDKPilihanServices::produksiBahanGalianAgt($custom_value) as $key => $item)
                @php
                    $ddk_produksi_field = $key;
                    $bahan_galian = $item;
                    $ada_ddk_anggota = $ddk ? true : false;
                    if($ada_ddk_anggota){
                        $ddk_produksi_data = $ddk->bahanGalianAnggota->where('penduduk_id', $anggota->id)
                            ? $ddk->bahanGalianAnggota->where('penduduk_id', $anggota->id)
                                ->where('kode_komoditas', $ddk_produksi_field)->first()
                            : false;
                    }
                @endphp
                @if($ada_ddk_anggota && $ddk_produksi_data)
                    {{-- NOTE: harap sesuaikan kode js jika ada perubahan format tr dibawah ini beserta seluruh bagiannya --}}
                    <tr>
                        <td>{{ $ddk_produksi_field }}</td>
                        <td>{{ $bahan_galian }}</td>
                        <td>
                            <input name="{{ DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA }}[{{ $anggota->id }}][{{ $ddk_produksi_field }}][nilai_produksi]" maxlength="20" class="form-control input-sm bilangan_titik" type="text" value="{{ $ddk_produksi_data->nilai_produksi }}" >
                        </td>
                        <td>
                            <input name="{{ DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA }}[{{ $anggota->id }}][{{ $ddk_produksi_field }}][milik_adat]" maxlength="100" class="form-control input-sm alamat" type="text" value="{{ $ddk_produksi_data->milik_adat }}" >
                        </td>
                        <td>
                            <input name="{{ DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA }}[{{ $anggota->id }}][{{ $ddk_produksi_field }}][milik_perorangan]" maxlength="100" class="form-control input-sm alamat" type="text" value="{{ $ddk_produksi_data->milik_perorangan }}" >
                        </td>
                        <td>
                            <input name="{{ DDKEnum::KODE_PRODUKSI_BAHAN_GALIAN_YANG_DIMILIKI_ANGGOTA }}[{{ $anggota->id }}][{{ $ddk_produksi_field }}][pemasaran_hasil]" maxlength="150" class="form-control input-sm alamat" type="text" value="{{ $ddk_produksi_data->pemasaran_hasil }}" >
                        </td>
                    </tr>
                @endif
            @endforeach
        </table>
    </div>
</div>

@push('scripts')
    {{--
        NOTE: fungsi onClick tambahkan_bahan_galian ada di file form.blade.php,
        NOTE: fungsi hide table_bahan_galian jika kosong ada di file form.blade.php
    --}}
@endpush