<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
  <div class="container-fluid py-1 px-3">
    <h3 class="font-weight-bolder mb-0"><?=$pagename?></h3>
    <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
      <ul class="navbar-nav  justify-content-end">
        <li class="nav-item ps-3 d-flex align-items-center">
          <span id="clock">
          </span>
        </li>
        <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
          <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
            <div class="sidenav-toggler-inner">
              <i class="sidenav-toggler-line"></i>
              <i class="sidenav-toggler-line"></i>
              <i class="sidenav-toggler-line"></i>
            </div>
          </a>
        </li>
        <li class="nav-item ps-3 d-flex align-items-center">
          <a href="javascript:;" class="nav-link text-body p-0">
            <i class="fas fa-palette fixed-plugin-button-nav cursor-pointer"></i>
          </a>
        </li>
        <li class="nav-item ps-3 d-flex align-items-center">
          <a href="/exit" class="nav-link text-body p-0">
            <i class="fas fa-sign-out-alt cursor-pointer"></i>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<script>
$(function () {
  let clck_ts = <?=$clck_ts?>;
  function startTime() {
    const today = new Date(clck_ts);

    let h = today.getHours();
    let m = today.getMinutes();
    let s = today.getSeconds();

    m = checkTime(m);
    s = checkTime(s);

    document.getElementById('clock').innerHTML =  h + ":" + m + ":" + s;
    clck_ts += 1000;
  }

  function checkTime(i) {
    if (i < 10) {i = "0" + i};
    return i;
  }

  let interval = setInterval(startTime, 1000);
});
</script>

