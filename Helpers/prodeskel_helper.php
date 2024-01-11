<?php

/**
 * Buat baris pilihan dan coret selain yang ditemukan, jika tidak ditemukan buat tanpa coretan
 *
 * @param array $hub_tersedia [ string title => ( int | string | array of ) kode to_be_compared, ... ]
 * @param mixed $compare_value ( int | string | array of ) to_be_compared
 * @param mixed $default_key ( int | string | false )
 */
function baris_pilihan_di_coret(array $hub_tersedia, $compare_value, $default_key = false, string $strikethrough_star_pattern = '<del>*</del>', $space_divider_replacement = '&nbsp;&nbsp;'){
    $tersedia = false;
    $string = '';
    foreach($hub_tersedia as $key => $item){
        $ketemu = false;
        if(is_array($compare_value)){
            foreach($compare_value as $item_value){
                if(
                    (is_array($item) && in_array($item_value, $item))
                    || (!is_array($item) && $item_value == $item)
                    || ($default_key !== false && $key == $default_key && ! $tersedia)
                ){
                    $tersedia = true;
                    $ketemu = true;
                    break;
                }
            }
        }else{
            if(
                (is_array($item) && in_array($compare_value, $item))
                || (!is_array($item) && $compare_value == $item)
                || ($default_key !== false && $key == $default_key && ! $tersedia)
            ){
                $tersedia = true;
                $ketemu = true;
            }
        }

        if($ketemu){
            $string .= $key . str_replace(' ', $space_divider_replacement, ' / ');
        }else{
            $string .= str_replace('*', $key, $strikethrough_star_pattern) . str_replace(' ', $space_divider_replacement, ' / ');
        }
    }

    // jika tidak ditemukan buat baris pilihan tanpa coretan
    if($tersedia == false){
        $string = '';
        foreach($hub_tersedia as $key => $item){
            $string .= $key . str_replace(' ', $space_divider_replacement, ' / ');
        }
    }

    return $string;
}

function empty_as_null_or_value($value = null){
    if ($value === '') {
        return null;
    }

    return $value;
}