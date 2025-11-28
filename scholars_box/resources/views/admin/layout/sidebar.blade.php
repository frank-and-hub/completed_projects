<nav class="sidebar">
    <div class="sidebar-header">
        <a href="#" class="sidebar-brand">SCHOLARS<span>BOX</span></a>
        <div class="sidebar-toggler not-active">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <div class="sidebar-body">
        <ul class="nav">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link">
                    <i class="link-icon" data-feather="box"></i>
                    <span class="link-title">Dashboard</span>
                </a>
            </li>
            @if (condication(auth()->user(), '1', 'view'))
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#scholarship" role="button"
                        aria-expanded="false">
                        <i class="link-icon" data-feather="mail"></i>
                        <span class="link-title">Scholarship Management</span>
                        <i class="link-arrow" data-feather="chevron-down"></i>
                    </a>
                    <div class="collapse" id="scholarship">
                        <ul class="nav sub-menu">
                            @if (condication(auth()->user(), '1', 'view'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.scholarship.list') }}" class="nav-link">Scholarship
                                        List</a>
                                    <!--<a href="{{ route('admin.scholarship.moving.text') }}" class="nav-link">Scholarship Moving text</a>-->
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif
            @if (condication(auth()->user(), '2', 'view'))
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#student" role="button" aria-expanded="false">
                        <i class="link-icon" data-feather="mail"></i>
                        <span class="link-title">Student Management</span>
                        <i class="link-arrow" data-feather="chevron-down"></i>
                    </a>
                    <div class="collapse" id="student">
                        <ul class="nav sub-menu">
                            @if (condication(auth()->user(), '2', 'view'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.student.list') }}" class="nav-link">Student List</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif
            @if (in_array(auth()->user()->role_id, ['1']))
                @if (condication(auth()->user(), '5', 'view'))
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#user-management" role="button"
                            aria-expanded="false">
                            <i class="link-icon" data-feather="mail"></i>
                            <span class="link-title">User Management</span>
                            <i class="link-arrow" data-feather="chevron-down"></i>
                        </a>
                        <div class="collapse" id="user-management">
                            <ul class="nav sub-menu">
                                @if (condication(auth()->user(), '5', 'add'))
                                    <li class="nav-item">
                                        <a href="{{ route('user.create') }}" class="nav-link">Add New User</a>
                                    </li>
                                @endif
                                @if (condication(auth()->user(), '5', 'view'))
                                    <li class="nav-item">
                                        <a href="{{ route('user.index') }}" class="nav-link">User List</a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif
            @endif
            @if (condication(auth()->user(), '3', 'view'))
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#blog" role="button" aria-expanded="false">
                        <i class="link-icon" data-feather="mail"></i>
                        <span class="link-title">Blog Management</span>
                        <i class="link-arrow" data-feather="chevron-down"></i>
                    </a>
                    <div class="collapse" id="blog">
                        <ul class="nav sub-menu">
                            @if (condication(auth()->user(), '3', 'view'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.blog.list') }}" class="nav-link">Blog List</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif
            @if (condication(auth()->user(), '4', 'view'))
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#page-management" role="button"
                        aria-expanded="false">
                        <i class="link-icon" data-feather="mail"></i>
                        <span class="link-title">Page Management</span>
                        <i class="link-arrow" data-feather="chevron-down"></i>
                    </a>
                    <div class="collapse" id="page-management">
                        <ul class="nav sub-menu">
                            @if (condication(auth()->user(), '4', 'view'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.about.us') }}" class="nav-link">About us</a>
                                </li>
                            @endif
                            @if (condication(auth()->user(), '4', 'view'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.contact.us') }}" class="nav-link">Contact us</a>
                                </li>
                            @endif
                            @if (condication(auth()->user(), '4', 'view'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.login.page') }}" class="nav-link">login page</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif
            @if (in_array(auth()->user()->role_id, ['1']))
                @if (condication(auth()->user(), '6', 'view'))
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#company-management" role="button"
                            aria-expanded="false">
                            <i class="link-icon" data-feather="mail"></i>
                            <span class="link-title">Company Management</span>
                            <i class="link-arrow" data-feather="chevron-down"></i>
                        </a>
                        <div class="collapse" id="company-management">
                            <ul class="nav sub-menu">
                                @if (condication(auth()->user(), '6', 'add'))
                                    <li class="nav-item">
                                        <a href="{{ route('company.create') }}" class="nav-link">Add Company</a>
                                    </li>
                                @endif
                                @if (condication(auth()->user(), '6', 'view'))
                                    <li class="nav-item">
                                        <a href="{{ route('company.index') }}" class="nav-link">Company List</a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif
            @endif
            @if (condication(auth()->user(), '7', 'view'))
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#faq" role="button"
                        aria-expanded="false">
                        <i class="link-icon" data-feather="mail"></i>
                        <span class="link-title">FAQ Management</span>
                        <i class="link-arrow" data-feather="chevron-down"></i>
                    </a>
                    <div class="collapse" id="faq">
                        <ul class="nav sub-menu">
                            @if (condication(auth()->user(), '7', 'view'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.faq.list') }}" class="nav-link">Faqs List</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif
            @if (condication(auth()->user(), '7', 'view'))
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#mfaq" role="button"
                    aria-expanded="false">
                    <i class="link-icon" data-feather="mail"></i>
                    <span class="link-title">MicroSite FAQ Management</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="mfaq">
                    <ul class="nav sub-menu">
                        @if (condication(auth()->user(), '7', 'view'))
                            <li class="nav-item">
                                <a href="{{ route('admin.mfaq.list') }}" class="nav-link">Faqs List</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </li>
        @endif
        @if (condication(auth()->user(), '7', 'view'))
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#term" role="button"
                aria-expanded="false">
                <i class="link-icon" data-feather="mail"></i>
                <span class="link-title">Term & Condition Management</span>
                <i class="link-arrow" data-feather="chevron-down"></i>
            </a>
            <div class="collapse" id="term">
                <ul class="nav sub-menu">
                    @if (condication(auth()->user(), '7', 'view'))
                        <li class="nav-item">
                            <a href="{{ route('admin.term.list') }}" class="nav-link">Terms List</a>
                        </li>
                    @endif
                </ul>
            </div>
        </li>
    @endif
            @if (condication(auth()->user(), '9', 'view'))
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#home" role="button"
                        aria-expanded="false">
                        <i class="link-icon" data-feather="mail"></i>
                        <span class="link-title">Home Management</span>
                        <i class="link-arrow" data-feather="chevron-down"></i>
                    </a>
                    <div class="collapse" id="home">
                        <ul class="nav sub-menu">
                            @if (condication(auth()->user(), '9', 'view'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.home.banner.list') }}" class="nav-link">banner</a>
                                </li>
                            @endif
                            @if (condication(auth()->user(), '9', 'view'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.home.partner.list') }}" class="nav-link">Partner With
                                        Us</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif
            @if (condication(auth()->user(), '8', 'view'))
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#cms" role="button"
                        aria-expanded="false">
                        <i class="link-icon" data-feather="mail"></i>
                        <span class="link-title">CMS Management</span>
                        <i class="link-arrow" data-feather="chevron-down"></i>
                    </a>
                    <div class="collapse" id="cms">
                        <ul class="nav sub-menu">
                            @if (condication(auth()->user(), '8', 'view'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.cms.list') }}" class="nav-link">CMS List</a>
                                </li>
                            @endif
                            @if (condication(auth()->user(), '8', 'view'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.cms.social_media') }}" class="nav-link">Social Media</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif
            @if (condication(auth()->user(), '10', 'view'))
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#request" role="button"
                        aria-expanded="false">
                        <i class="link-icon" data-feather="mail"></i>
                        <span class="link-title">Request Management</span>
                        <i class="link-arrow" data-feather="chevron-down"></i>
                    </a>
                    <div class="collapse" id="request">
                        <ul class="nav sub-menu">
                            @if (condication(auth()->user(), '10', 'view'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.request.list') }}" class="nav-link">Request List</a>
                                </li>
                            @endif

                        </ul>
                    </div>
                </li>
            @endif
            @if (condication(auth()->user(), '10', 'view'))
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#study" role="button"
                        aria-expanded="false">
                        <i class="link-icon" data-feather="mail"></i>
                        <span class="link-title">Study Material</span>
                        <i class="link-arrow" data-feather="chevron-down"></i>
                    </a>
                    <div class="collapse" id="study">
                        <ul class="nav sub-menu">
                            @if (condication(auth()->user(), '10', 'view'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.study.list') }}" class="nav-link">Study List</a>
                                </li>
                            @endif

                        </ul>
                    </div>
                </li>
            @endif
            @if (condication(auth()->user(), '10', 'view'))
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#amount" role="button"
                    aria-expanded="false">
                    <i class="link-icon" data-feather="mail"></i>
                    <span class="link-title">Awarded Student</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="amount">
                    <ul class="nav sub-menu">
                        @if (condication(auth()->user(), '10', 'view'))
                            <li class="nav-item">
                                <a href="{{ route('admin.awarede.list') }}" class="nav-link">Study List</a>
                            </li>
                        @endif

                    </ul>
                </div>
            </li>
        @endif
        @if (condication(auth()->user(), '10', 'view'))
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#carrer" role="button"
                    aria-expanded="false">
                    <i class="link-icon" data-feather="mail"></i>
                    <span class="link-title">Carrer Management</span>
                    <i class="link-arrow" data-feather="chevron-down"></i>
                </a>
                <div class="collapse" id="carrer">
                    <ul class="nav sub-menu">
                        @if (condication(auth()->user(), '10', 'view'))
                            <li class="nav-item">
                                <a href="{{ route('admin.carrer.list') }}" class="nav-link">Job Request List</a>
                            </li>
                        @endif

                    </ul>
                </div>
            </li>
        @endif

        @if (condication(auth()->user(), '10', 'view'))
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#microsite" role="button"
                aria-expanded="false">
                <i class="link-icon" data-feather="book"></i>
                <span class="link-title">MicroSite Management</span>
                <i class="link-arrow" data-feather="chevron-down"></i>
            </a>
            <div class="collapse" id="microsite">
                <ul class="nav sub-menu">
                    @if (condication(auth()->user(), '10', 'view'))
                        <li class="nav-item">
                            <a href="{{ route('admin.microsite.index') }}" class="nav-link">Microsites</a>
                        </li>
                    @endif

                </ul>
            </div>
        </li>
    @endif
        </ul>
    </div>
</nav>
