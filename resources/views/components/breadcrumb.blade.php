<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">{{ $title }}</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    @if(isset($li1))
                        <li class="breadcrumb-item"><a href={{ route($li1link) }}>{{ $li1 }}</a></li>
                    @endif

                    @if(isset($li2))
                        <li class="breadcrumb-item"><a href={{ route($li2link) }}>{{ $li2 }}</a></li>
                    @endif
                    
                    @if(isset($title))
                        <li class="breadcrumb-item active">{{ $title }}</li>
                    @endif
                </ol>
            </div>

        </div>
    </div>
</div>
<!-- end page title -->