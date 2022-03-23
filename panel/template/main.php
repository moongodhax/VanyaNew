      <div id="content">
        <div class="slick">
          <div class="slide mx-3" v-for="stat in allStats">
            <div class="card z-index-2 my-4">
              <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2 bg-transparent">
                <div 
                  class="border-radius-lg py-3 pe-1 text-white"
                  v-bind:class="(stat.type == 'stream') ? 'bg-gradient-primary shadow-primary' : 'bg-gradient-secondary shadow-secondary'"
                >
                  <div class="container">
                    <div class="row mb-3">
                      <div class="col-6">
                        <span class="text-xs">Текущий</span>
                        <h5 class="mb-0 text-white">{{ stat.stats.current }}</h5>
                      </div>
                      <div class="col-6">
                        <span class="text-xs">Час</span>
                        <h5 class="mb-0 text-white">{{ stat.stats.hour }}</h5>
                      </div>
                    </div>
                    <div class="row mb-3">
                      <div class="col-6">
                        <span class="text-xs">Сутки</span>
                        <h5 class="mb-0 text-white">{{ stat.stats.day }}</h5>
                      </div>
                      <div class="col-6">
                        <span class="text-xs">7 Дней</span>
                        <h5 class="mb-0 text-white">{{ stat.stats["7days"] }}</h5>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <h6 class="mb-0 text-uppercase">{{ stat.name }}</h6>
              </div>
            </div>
          </div>
        </div>

        <div class="row mt-4">
          <div class="col-12">
            <div class="card z-index-2">
              <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2 bg-transparent">
                <div class="bg-gradient-success shadow-success border-radius-lg py-3 pe-1">
                  <div class="chart">
                    <canvas id="chart-line" class="chart-canvas" height="170"></canvas>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <h6 class="mb-0 ">График посещаемости</h6>
              </div>
            </div>
          </div>
        </div>

        <div class="row mt-4">
          <div class="col-sm-12 col-md-8">
            <div class="card my-4">
              <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3 header-with-button">
                  <h6 class="text-white text-capitalize ps-3">Распределение по странам</h6>
                  <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle m-0" type="button" id="streamDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ selectedStream.stream + ((selectedStream.substream != "") ? " / " + selectedStream.substream : "") }}
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="streamDropdown">
                      <li @click="showStream({ stream: 'all' })"><span class="dropdown-item" >All</span></li>
                      <template v-for="stream in streams">
                        <li @click="showStream({ stream: stream.stream })"><span class="dropdown-item" >{{ stream.stream }}</span></li>
                        <template v-for="substream in stream.substreams">
                          <li @click="showStream({ stream: stream.stream, substream: substream.name })"><span class="dropdown-item">{{ stream.stream }} / {{ substream.name }}</span></li>
                        </template>
                      </template>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <div id="map" style="height: 400px;"></div>
              </div>
            </div>
          </div>
          <div class="col-sm-12 col-md-4">
            <div class="card my-4 mh-100">
              <div class="card-body p-1" style="height: 500px;">
                <div class="table-responsive p-0" style="height: 100%; overflow: auto">
                  <table class="table align-items-center mb-0" id="countries-table">
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <script src="/assets/js/plugins/jquery-jvectormap-2.0.5.min.js"></script>
      <script src="/assets/js/plugins/jquery-jvectormap-world-mill.js"></script>
      <script src="/assets/js/plugins/chartjs.min.js"></script>

      <script src="/assets/js/pages/main.js"></script>
