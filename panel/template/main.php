      <div id="content">
        <div class="slick">
          <div class="slide mx-3" v-for="stat in allStats">
            <div class="card z-index-2 my-4">
              <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2 bg-transparent">
                <div 
                  class="border-radius-lg py-3 pe-1 text-white"
                  :style="(stat.type == 'stream') ? { backgroundColor: '#' + stat.color, boxShadow: '0px 4px 20px 0px ' + hexToRGB(stat.color, 0.5) } : ''"
                  :class="(stat.type == 'stream') ? 'white-gradient' : 'bg-gradient-secondary shadow-secondary'"
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
                <template v-if="stat.type == 'stream'">
                  <h6 class="mb-0 text-uppercase d-flex">
                    {{ stat.name }}
                  </h6>
                  <input type='color' class="btn-color" :value="'#' + stat.color" :data-stream="stat.name" @change="setStreamColor" />
                </template>
                
                <template v-else>
                  <h6 class="mb-0 text-uppercase d-flex">
                    <span class="badge badge-sm white-gradient me-2" :style="{ backgroundColor: '#' + stat.parentcolor }">{{ stat.parentname }}</span>{{ stat.name }} 
                  </h6>
                  <a class="btn btn-link text-secondary text-sm mb-0 p-0 ms-2" target="_blank" :href="getStreamLink(stat.hash)">
                    <i class="fas fa-external-link-alt"></i></a>
                  <button class="btn btn-link text-secondary text-sm mb-0 p-0 ms-2" @click="copyStreamLink(stat.hash)">
                    <i class="fas fa-copy"></i></button>
                  <button class="btn btn-link text-secondary text-sm mb-0 p-0 ms-2" @click="clearSubstream(stat.name, stat.id)">
                    <i class="fas fa-eraser"></i></button>
                </template>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-12 col-md-8">
            <div class="card my-4">
              <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3 header-with-button">
                  <h6 class="text-white text-capitalize ps-3">Распределение по странам</h6>
                  <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle m-0" type="button" id="streamDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ selectedStreamBtn }}
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="streamDropdown">
                      <li @click="showStream(0)"><span class="dropdown-item" >Все</span></li>
                      <template v-for="stream in streams">
                        <li @click="showStream(stream.id)"><span class="dropdown-item" >{{ stream.stream }}</span></li>
                        <template v-for="substream in stream.substreams">
                          <li @click="showStream(stream.id, substream.id)"><span class="dropdown-item">{{ stream.stream }} / {{ substream.name }}</span></li>
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
              <div class="card-body p-1 pb-3" style="height: 500px;">
                <div class="table-responsive p-0" style="height: 100%; overflow: auto">
                  <table class="table align-items-center mb-0" id="countries-table">
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row mt-4">
          <div class="col-12">
            <div class="card z-index-2">
              <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2 bg-transparent">
                <div class="bg-gradient-success shadow-success border-radius-lg py-3 pe-1">
                  <div class="chart" style="height: 350px">
                    <canvas id="chart-line" class="chart-canvas"></canvas>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <h6 class="mb-0 ">График посещаемости</h6>
              </div>
            </div>
          </div>
        </div>
      </div>

      <script src="/assets/js/plugins/jquery-jvectormap-2.0.5.min.js"></script>
      <script src="/assets/js/plugins/jquery-jvectormap-world-mill.js"></script>
      <script src="/assets/js/plugins/chartjs.min.js"></script>

      <script src="/assets/js/pages/main.js"></script>
