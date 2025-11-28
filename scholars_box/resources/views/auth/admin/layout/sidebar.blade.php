<nav class="sidebar">
  <div class="sidebar-header">
    <a href="#" class="sidebar-brand">CSR<span>BOX</span></a>
    <div class="sidebar-toggler not-active">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </div>
  <div class="sidebar-body">
    <ul class="nav">
      <li class="nav-item">
        <a href="{{ url('/') }}" class="nav-link">
          <i class="link-icon" data-feather="box"></i>
          <span class="link-title">Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#scholarship" role="button" aria-expanded="false">
          <i class="link-icon" data-feather="mail"></i>
          <span class="link-title">Scholarship Management</span>
          <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="collapse" id="scholarship">
          <ul class="nav sub-menu">
            <li class="nav-item">
              <a href="{{ route('admin.scholarship.list') }}" class="nav-link">Scholarship List</a>
            </li>
            
            </li>
          </ul>
        </div>
      </li>

      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#student" role="button" aria-expanded="false">
          <i class="link-icon" data-feather="mail"></i>
          <span class="link-title">Student Management</span>
          <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="collapse" id="student">
          <ul class="nav sub-menu">
            <li class="nav-item">
              <a href="{{ route('admin.student.list') }}" class="nav-link">Student List</a>
            </li>
            
            </li>
          </ul>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#blog" role="button" aria-expanded="false">
          <i class="link-icon" data-feather="mail"></i>
          <span class="link-title">Blog Management</span>
          <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="collapse" id="blog">
          <ul class="nav sub-menu">
            <li class="nav-item">
              <a href="{{ route('admin.blog.list') }}" class="nav-link">Blog List</a>
            </li>
            
            </li>
          </ul>
        </div>
      </li>

      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#faq" role="button" aria-expanded="false">
          <i class="link-icon" data-feather="mail"></i>
          <span class="link-title">FAQ Management</span>
          <i class="link-arrow" data-feather="chevron-down"></i>
        </a>
        <div class="collapse" id="faq">
          <ul class="nav sub-menu">
            <li class="nav-item">
              <a href="{{ route('admin.faq.list') }}" class="nav-link">Faqs List</a>
            </li>
            
            </li>
          </ul>
        </div>
      </li>
    </ul>
  </div>
</nav>
