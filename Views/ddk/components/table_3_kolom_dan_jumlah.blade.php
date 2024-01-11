<div class="col-md-12" style="display: flow-root">
    <label class="control-label col-lg-4 col-md-12"  style="padding: 0">
        {{ str_replace('{sync}', '', $label) }}
        {!! str_contains($label, '{sync}') ? '<span class="bg-danger">(Sebagaian/semua data disingkronisasi otomatis)</span>' : '' !!}
    </label>
    <div class="col-lg-8 col-md-12">
        <div class="input-group input-group-sm">
            @include('ddk.components.select_pilihan_prodeskel', ['class' => 'select2', 'attribut' => 'multiple id="pilihan_'. $kode_unik_tabel .'"', 'pilihan' => $daftar_pilihan ])
            <span class="input-group-btn">
                <button type="button" class="btn btn-info" id="tambahkan_{{ $kode_unik_tabel }}"><i class="fa fa-plus"></i>&nbsp;Tambahkan {{ $label_pilihan }}</button>
            </span>
        </div>
    </div>
    <div style="margin: 3px 0;border: 0;" class="col-sm-12"></div>
    <table id="table_{{ $kode_unik_tabel }}" class="col-xs-push-1 col-md-7 col-sm-11 col-xs-11">
        <tr>
            <th>Kode</th>
            <th>{{ $td_judul_2 }}</th>
            <th>{{ $td_judul_3 }}</th>
        </tr>
        @foreach($daftar_pilihan as $ddk_field => $$kode_unik_tabel)
            @php
                try {
                    $data = $ddk->detail->firstWhere('kode_field', $kode_unik_tabel);
                } catch (\Throwable $th) {
                    log_message('error', 'resources\views\admin\prodeskel\ddk\components\table_3_kolom_dan_jumlah.blade.php line 24');
                    $data = collect(['value'=>'']);
                }
            @endphp

            @if(array_key_exists($ddk_field, $data->value))
                <tr>
                    <td>{{ $ddk_field }}</td>
                    <td>{{ $$kode_unik_tabel }}</td>
                    <td>
                        <input name="{{ $kode_unik_tabel }}[{{ $ddk_field }}]" id="jumlah_{{ $ddk_field }}" maxlength="5" class="form-control input-sm bilangan" type="text" value="{{ $data->value[$ddk_field] }}" >
                    </td>
                </tr>
            @endif
        @endforeach
    </table>
</div>