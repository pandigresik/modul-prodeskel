@push('css')
    <style type="text/css">
        .mini-submenu {
            display: none;
            background-color: rgba(0, 0, 0, 0);
            border: 1px solid rgba(0, 0, 0, 0.9);
            border-radius: 4px;
            padding: 9px;
            /*position: relative;*/
            width: 42px;
            cursor: pointer;
        }

        #slide-submenu {
            display: inline-block;
            padding: 2px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
@endpush
<button type="button" class="mini-submenu bg-purple">
  <i class="fa fa-bars"></i>
</button>
<div class="box box-info">
  <div class="box-header with-border">
    <h3 class="box-title">Sub Menu</h3>
    <div class="box-tools">

      <button type="button" class="hide-menu btn btn-box-tool" id="slide-submenu">
        <i class="fa fa-bars"></i>
      </button>
      {{-- <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button> --}}
    </div>
  </div>
  <div class="box-body no-padding">
    <ul class="nav nav-pills nav-stacked">
      <li class="@active($navigasi == 'pengaturan')"><a href="{{ ci_route('prodeskel') }}"><i class="fa fa-gear"></i> Informasi dan Pengaturan </a></li>
      <li class="@active($navigasi == 'ddk')"><a href="{{ ci_route('prodeskel.ddk') }}"><i class="fa fa-users"></i> Data Dasar Keluarga (DDK)</a></li>
    </ul>
  </div>
</div>

@push('scripts')
    <script>
        $('document').ready(function() {
            $('#slide-submenu').on('click', function() {
                $(this).closest('.box.box-info').fadeOut('fast', function() {
                    $('.mini-submenu').fadeIn('fast', function() {
                      $('#sub-menu').removeClass("col-md-3");
                      $('#sub-menu').addClass("col-md-1");
                      $('#main-content').removeClass("col-md-9");
                      $('#main-content').addClass("col-md-11");
                    });
                });
            });

            $('.mini-submenu').on('click', function() {
                $(this).next('.box.box-info').fadeIn('fast');
                $('.mini-submenu').hide();
                $('#sub-menu').removeClass("col-md-1");
                $('#sub-menu').addClass("col-md-3");
                $('#main-content').removeClass("col-md-11");
                $('#main-content').addClass("col-md-9");
            })
        });
    </script>
@endpush