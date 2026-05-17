@extends('frontend.amazy.layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('/public/css/customer_profile.css') }}">
@endpush

@section('content')
<div class="amazy_dashboard_area dashboard_bg section_spacing6">
    <div class="container-fluid">

        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <div class="row flex-lg-nowrap position-relative">
            <div class="col-xl-3 col-lg-4 sidebar-wrapper" id="dashboardSidebar">
                <div class="d-lg-none d-flex justify-content-end mb-2">
                    <button id="closeSidebar" class="btn btn-sm btn-danger rounded-circle"><i class="fa fa-times" aria-hidden="true"></i></button>
                </div>
                @include('frontend.amazy.pages.profile.partials._menu')
            </div>

            <div class="col-xl-10 col-lg-9 relative" id="dashboardContent">
                @include('frontend.amazy.pages.profile.partials._customer_details')
                
                @yield('profile_content')
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const toggleBtnDesktop = document.getElementById('toggleSidebar');
        const toggleBtnMobile = document.getElementById('toggleSidebarMobile'); // Agregado el botón móvil
        const closeBtn = document.getElementById('closeSidebar');
        const sidebar = document.getElementById('dashboardSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const content = document.getElementById('dashboardContent');

        function toggleMenu() {
            const isMobile = window.innerWidth < 992;
            if (isMobile) {
                // Lógica de deslizar desde afuera para móvil
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            } else {
                // Lógica de colapsar íconos para escritorio
                sidebar.classList.toggle('desktop-collapsed');
                content.classList.toggle('desktop-expanded');

                if (toggleBtnDesktop) {
                    if (sidebar.classList.contains('desktop-collapsed')) {
                        toggleBtnDesktop.innerHTML = '<i class="fa fa-arrow-right" aria-hidden="true"></i>';
                    } else {
                        toggleBtnDesktop.innerHTML = '<i class="fa fa-arrow-left" aria-hidden="true"></i>';
                    }
                }
            }
        }

        // Asignar clics a ambos botones
        if(toggleBtnDesktop) toggleBtnDesktop.addEventListener('click', toggleMenu);
        if(toggleBtnMobile) toggleBtnMobile.addEventListener('click', toggleMenu);
        
        if(closeBtn) closeBtn.addEventListener('click', toggleMenu);
        if(overlay) overlay.addEventListener('click', toggleMenu);

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 992) {
                sidebar.classList.remove('active');
                if(overlay) overlay.classList.remove('active');
            } else {
                sidebar.classList.remove('desktop-collapsed');
                if(content) content.classList.remove('desktop-expanded');
            }
        });
    });

    // $('#open_benefits_modal').on('click', function(){
    //     $('#benefits_modal').modal('show');
    // })
</script>
@endpush
