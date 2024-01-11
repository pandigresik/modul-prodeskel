<select class="form-control input-sm {{ $class ?? '' }}" {!! $attribut ?? '' !!} style="width:100%;">
    <option selected @disabled(str_contains($attribut, 'multiple')) value="">-----   {{ str_contains($attribut, 'multiple') ? 'Cari dan Pilih (bisa pilih lebih dari 1)' : 'Pilih'}}   -----</option>
    @foreach($pilihan as $key => $item)
        <option value="{{ $key }}"
            {{-- jika single cek menggunakan ===, jika multiple cek menggunakan === dan in_array --}}
            @selected(
                ($key . '' === ($selected_value . '' ?? ''))
                 ||
                 (str_contains($attribut, 'multiple')
                    ? $key . '' === ''. $value_to_be_compared || in_array($key, $value_to_be_compared)
                    : false)
            )
        >
            {{ $item }}
        </option>
    @endforeach
</select>