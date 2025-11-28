<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
      <li class="nav-item">
        <a class="nav-link {{ (in_array(request()->path(),['home'])) ? '' : 'collapsed'}}" href="{{ route('home') }}">
          <i class="bi bi-grid"></i>
          <span>Dashboard</span>
        </a>
      </li>
      @if(Auth::user()->role == '1')
      <li class="nav-item">
        <a class="nav-link {{ (in_array(request()->path(),['user/create','user'])) ? '' : 'collapsed'}}" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-person"></i><span>User Management</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="components-nav" class="nav-content collapse {{ (in_array(request()->path(),['user/create','user'])) ? 'show' : ''}}" data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('user.index') }}" class="{{ (in_array(request()->path(),['user'])) ? 'active' : ''}}">
              <i class="bi bi-circle"></i><span>User</span>
            </a>
          </li>
          <li>
            <a href="{{ route('user.create') }}" class="{{ (in_array(request()->path(),['user/create'])) ? 'active' : ''}}">
              <i class="bi bi-circle"></i><span>Register User</span>
            </a>
          </li>
        </ul>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ (in_array(request()->path(),['user/show_list'])) ? '' : 'collapsed'}}" href="{{route('user.show_list')}}">
          <i class="bi bi-envelope"></i>
          <span>Report</span>
        </a>
      </li>
      @else
      <li class="nav-item">
            <a class="nav-link {{ (in_array(request()->path(),['emails','user/show_list'])) ? '' : 'collapsed'}}" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
              <i class="bi bi-envelope"></i><span>Report</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="forms-nav" class="nav-content collapse  {{ (in_array(request()->path(),['emails','user/show_list'])) ? 'show' : ''}}" data-bs-parent="#sidebar-nav">
              <li>
                <a href="{{route('user.show_list')}}" class="{{ (in_array(request()->path(),['user/show_list'])) ? 'active' : ''}}">
                  <i class="bi bi-circle"></i><span>Report</span>
                </a>
              </li>
              <li>
                <a href="{{ route('emails.index') }}" class="{{ (in_array(request()->path(),['emails'])) ? 'active' : ''}}">
                  <i class="bi bi-circle"></i><span>Send Email</span>
                </a>
              </li>
            </ul>
          </li>
      @endif
    </ul>
  </aside>
