@push('css')
  <style>
    .nav-tabs-custom{
      margin-bottom: 5px;
    }
    .tab-pane {
        display: none;
    }
    .tab-pane.active {
      display: block;
    }
  </style>
@endpush

@extends('admin.layouts.index')

@section('title')
<h1>
    Prodeskel <small>Informasi dan Pengaturan</small>
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
            <div class="box-body tab-content">
              <div class="nav-tabs-custom">
                <ul class="nav nav-tabs" id="nav-tab" role="tablist">
                    <li class="active"><a href="#bagian-ddk" data-toggle="tab" id="nav-bagian-ddk"><strong>Data Dasar Keluarga (DDK)</strong></a></li>
                </ul>
              </div>
              <div class="tab-pane active" id="bagian-ddk">
                @include('pengaturan_ddk')
              </div>
            </div>
          </div>
        </div>
    </div>

@endsection
@push('scripts')
@endpush