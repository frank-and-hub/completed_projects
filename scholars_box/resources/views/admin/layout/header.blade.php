<nav class="navbar">
  <a href="#" class="sidebar-toggler">
    <i data-feather="menu"></i>
  </a>
  <div class="navbar-content">
    <form class="search-form">
      <div class="input-group">
        <div class="input-group-text">
          <i data-feather="search"></i>
        </div>
        <input type="text" class="form-control" id="navbarForm" placeholder="Search here...">
      </div>
    </form>
    <ul class="navbar-nav">
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i data-feather="bell"></i>
          <div class="indicator notification_bell">
            <div class="circle"></div>
          </div>
        </a>
        <div class="dropdown-menu p-0" aria-labelledby="notificationDropdown" style="max-width:500px;width:450px;">
          <div class="px-3 py-2 d-flex align-items-center justify-content-between border-bottom">
            <p>Notifications</p>
          </div>
          <div id="notification-container" class="p-1"></div>
          <div class="px-3 py-2 d-flex align-items-center justify-content-center border-top">
            <a href="javascript:void();">View all</a>
          </div>
        </div>
      </li>
      <style>
        .notification_bell {
          display: none;
        }

        .marquee-wrapper {
          overflow: hidden;
          white-space: nowrap;
          width: 100%;
        }

        .marquee-content {
          display: inline-block;
          animation: marquee 5s linear infinite;
          /* Adjust duration as needed */
          animation-play-state: paused;
        }

        .marquee-wrapper:hover .marquee-content {
          animation-play-state: running;
        }

        @keyframes marquee {
          from {
            transform: translateX(1%);
          }

          to {
            transform: translateX(-100%);
          }
        }
      </style>
      <script>
        document.addEventListener("DOMContentLoaded", function() {
          function fetchNotifications() {
            fetch("{{ route('admin.notification.fetch-all') }}")
              .then(response => {
                if (!response.ok) {
                  throw new Error('Network response was not ok');
                }
                return response.json();
              })
              .then(data => {
               
                const container = document.getElementById('notification-container');
                container.innerHTML = '';
                let ntf = data.notifications;
                let route = data.url;

                if (ntf.length === 0) {
                  container.innerHTML = '<p>No notifications found.</p>';
                  return;
                }

                ntf.forEach((notifi, index) => {
                  var url = route[index] ?? 'javascript:void();';
                  let notificationHtml = `<a href="${url}" class="dropdown-item d-flex align-items-center py-2">
                                                      <div class="flex-grow-1 me-2" ${notifi.user_id}>
                                                          <div class="marquee-wrapper">
                                                              <div class="marquee-content">${notifi.message}</div>
                                                          </div>
                                                      </div>
                                                  </a>`;
                  container.innerHTML += notificationHtml;
                });

                document.querySelector('.notification_bell').style.display = (ntf.length > 0) ? 'block' : 'none';
              })
              .catch(error => {
                console.error('Error fetching notifications:', error);
                const container = document.getElementById('notification-container');
                container.innerHTML = '<p>Error fetching notifications. Please try again later.</p>';
              });
          }
          fetchNotifications();
          setInterval(fetchNotifications, 6000000);
        });
      </script>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <img class="wd-30 ht-30 rounded-circle" src="https://storage.needpix.com/rsynced_images/profile-42914_1280.png" alt="profile">
        </a>
        <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
          <div class="d-flex flex-column align-items-center border-bottom px-5 py-3">
            <div class="mb-3">
              <img class="wd-80 ht-80 rounded-circle" src="https://storage.needpix.com/rsynced_images/profile-42914_1280.png" alt="">
            </div>
            <div class="text-center">
              <p class="tx-16 fw-bolder">{{ucwords(auth()->user()->first_name)}}</p>
              <p class="tx-12 text-muted">{{auth()->user()->email}}</p>
            </div>
          </div>
          <ul class="list-unstyled p-1">

            <li class="dropdown-item py-2">
              <a href="{{route('admin.logout')}}" class="text-body ms-0">
                <i class="me-2 icon-md" data-feather="log-out"></i>
                <span>Log Out</span>
              </a>
            </li>
          </ul>
        </div>
      </li>
    </ul>
  </div>
</nav>