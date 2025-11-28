<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('home') }}" class="brand-link">
        <img src="https://w7.pngwing.com/pngs/715/70/png-transparent-computer-icons-business-corporate-social-responsibility-company-service-counseling-miscellaneous-hand-handshake-thumbnail.png" alt="CSR" class="brand-image img-circle elevation-3">
        <span class="brand-text font-weight-light">CSR</span>
    </a>

    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills  nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                @include('layouts.menu')
            </ul>
        </nav>
    </div>

</aside>