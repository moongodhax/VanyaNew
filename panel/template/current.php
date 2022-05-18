      <div id="content">
        <div class="card">
          <div class="mx-3">
            <div class="row">
              <div class="col-sm-6 col-12">
                <div class="input-group input-group-outline my-3">
                  <select class="form-control" id="type_select" @change="streamSelected()">
                    <template v-for="stream in streams">
                      <optgroup :label="stream.stream">
                        <option :data-stream="stream.id" :data-substream="''">{{ stream.stream }}</option>
                        <option :data-stream="stream.id" :data-substream="sub.id" v-for="sub in stream.substreams">{{ stream.stream }} &mdash; {{ sub.name }}</option>
                      </optgroup>
                    </template>
                  </select>
                </div>
              </div>
              <div class="col-sm-6 col-12">
                <div class="input-group input-group-outline my-3">
                  <select class="form-control" id="date_select" v-model="selectedDate">
                    <option v-for="date in currentDates" :value="date.time">{{ date.name }}</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row mt-4">
          <div class="col-12">
            <div class="card my-4">
              <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                  <h6 class="text-white text-capitalize ps-3"><span id="type_header">MIX</span> <span id="date_header">Текущий</span></h6>
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table align-items-center mb-0" id="current-table"></table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <script src="/assets/js/pages/current.js"></script>
