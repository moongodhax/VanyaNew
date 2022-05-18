<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3" id="sidenav-main">
  <div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
    <span class="navbar-brand m-0">
      <img src="./assets/img/logo-ct.png" class="navbar-brand-img h-100" alt="main_logo">
      <span class="ms-1 font-weight-bold text-white">Панель статистики</span>
    </span>
  </div>
  <hr class="horizontal light mt-0 mb-2">
  <div class="collapse navbar-collapse  w-auto  max-height-vh-100" id="sidenav-collapse-main">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link text-white <?php if ($menu_active[0] == 1) echo "active"?>" href="/main"> 
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-chart-line"></i>
          </div>
          <span class="nav-link-text ms-1">Главная</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white <?php if ($menu_active[1] == 1) echo "active" ?>" href="/current">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-clock"></i>
          </div>
          <span class="nav-link-text ms-1">Текущее</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white <?php if ($menu_active[2] == 1) echo "active" ?>" href="/all">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-table"></i>
          </div>
          <span class="nav-link-text ms-1">Все данные</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white <?php if ($menu_active[3] == 1) echo "active" ?>" href="/blacklist">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fas fa-ban"></i>
          </div>
          <span class="nav-link-text ms-1">Черный список</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white <?php if ($menu_active[4] == 1) echo "active" ?>" href="/settings">
          <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
            <i class="fa fa-cog"></i>
          </div>
          <span class="nav-link-text ms-1">Настройки</span>
        </a>
      </li>
    </ul>
  </div>
</aside>