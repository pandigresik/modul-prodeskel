<div class="col-md-12">
    <div class="form-group">
        <label class="control-label" for="{{ $kode_unik_tabel }}">
            {{ str_replace('{sync}', '', $label) }}
            {!! str_contains($label, '{sync}') ? '<span class="bg-danger">(Sebagaian/semua data disingkronisasi otomatis)</span>' : '' !!}
        </label>
        @include('ddk.components.select_pilihan_prodeskel', [
            'class' => 'select2',
            'attribut' => $anggota_id
                ? "multiple id='{$anggota_id}{$kode_unik_tabel}' name='{$kode_unik_tabel}[{$anggota_id}][]'"
                : "multiple id='{$kode_unik_tabel}' name='{$kode_unik_tabel}[]'"
                ,
            'pilihan' => $daftar_pilihan,
            'value_to_be_compared' => $value_to_be_compared,
        ])
    </div>
</div>