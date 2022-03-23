      
      <div class="card">
        <div class="mx-3">
          <div class="row">
            <div class="col-sm-6 col-12">
              <div class="input-group input-group-outline mt-3">
                <label class="form-label">IP</label>
                <input type="text" id="ip" class="form-control">
              </div>
            </div>
            <div class="col-sm-6 col-12">
              <div class="input-group input-group-outline mt-3">
                <label class="form-label">Причина</label>
                <input type="text" id="reason" class="form-control">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <button type="button" onclick="blacklistAdd()" class="btn bg-gradient-primary w-100 mt-3">Забанить</button>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-4">
        <div class="col-12">
          <div class="card my-4">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
              <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                <h6 class="text-white text-capitalize ps-3">Черный список</h6>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table align-items-center mb-0" id="blacklist-table"></table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <script src="/assets/js/pages/blacklist.js"></script>

