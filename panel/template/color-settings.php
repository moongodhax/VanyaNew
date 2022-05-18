  <div class="fixed-plugin">
    <div class="card shadow-lg">
      <div class="card-header pb-0 pt-3">
        <div class="float-start">
          <h5 class="mt-3 mb-0">Цветовая схема</h5>
        </div>
        <div class="float-end mt-4">
          <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <!-- End Toggle Button -->
      </div>
      <hr class="horizontal dark my-1">
      <div class="card-body pt-sm-3 pt-0">
        <!-- Sidebar Backgrounds -->
        <div>
          <h6 class="mb-0">Цвета меню</h6>
        </div>
        <a href="javascript:void(0)" class="switch-trigger background-color">
          <div class="badge-colors my-2 text-start">
            <span class="navbar-color badge filter bg-gradient-primary active" onclick="sidebarColor('primary')"></span>
            <span class="navbar-color badge filter bg-gradient-dark" onclick="sidebarColor('dark')"></span>
            <span class="navbar-color badge filter bg-gradient-info" onclick="sidebarColor('info')"></span>
            <span class="navbar-color badge filter bg-gradient-success" onclick="sidebarColor('success')"></span>
            <span class="navbar-color badge filter bg-gradient-warning" onclick="sidebarColor('warning')"></span>
            <span class="navbar-color badge filter bg-gradient-danger" onclick="sidebarColor('danger')"></span>
          </div>
        </a>
        <!-- Sidenav Type -->
        <div class="mt-3">
          <h6 class="mb-0">Фон меню</h6>
        </div>
        <div class="d-flex">
          <button class="navbar-type btn bg-gradient-dark px-2 mb-2 type-gradient-dark active" onclick="sidebarType('gradient-dark')">Темный</button>
          <button class="navbar-type btn bg-gradient-dark px-2 mb-2 type-transparent ms-2" onclick="sidebarType('transparent')">Прозрачный</button>
          <button class="navbar-type btn bg-gradient-dark px-2 mb-2 type-white ms-2" onclick="sidebarType('white')">Белый</button>
        </div>
        <p class="text-sm d-xl-none d-block mt-2">You can change the sidenav type just on desktop view.</p>
        <div class="mt-2 d-flex">
          <h6 class="mb-0">Светлая / темная тема</h6>
          <div class="form-check form-switch ps-0 ms-auto my-auto">
            <input class="form-check-input mt-1 ms-auto" type="checkbox" id="dark-version" onclick="darkMode(this)" <?php //if ($color_scheme["background"]) echo "checked" ?>>
          </div>
        </div>
      </div>
    </div>
  </div>