
      <div id="content">
        <div class="row">
          <div class="col-12 col-sm-6">
            <div class="row">
              <div class="col-12">
                <div class="card my-4">
                  <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3">
                      <h6 class="text-white text-capitalize ps-3">Смена пароля</h6>
                    </div>
                  </div>
                  <div class="card-body">
                    <p v-if="passFormErrors.length" class="text-danger">
                      <template v-for="err in passFormErrors"><span>{{err}}</span><br /></template>
                    </p>
                    <form role="form" class="text-start">
                      <div class="input-group input-group-outline mb-3">
                        <label class="form-label">Старый пароль</label>
                        <input type="password" name="password" class="form-control" v-model="passForm.password">
                      </div>
                      <div class="input-group input-group-outline mb-3">
                        <label class="form-label">Новый пароль</label>
                        <input type="password" name="newpass" class="form-control" v-model="passForm.newpass">
                      </div>
                      <div class="input-group input-group-outline">
                        <label class="form-label">Повтор пароля</label>
                        <input type="password" name="repeat" class="form-control" v-model="passForm.repeat">
                      </div>
                      <div class="text-center">
                        <button type="button" class="btn bg-gradient-primary w-100 my-4 mb-2" v-on:click="checkPassForm()">Сменить</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <div class="card my-4">
                  <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3">
                      <h6 class="text-white text-capitalize ps-3">Подпотоки</h6>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="input-group input-group-outline mb-2">
                      <select class="form-control" v-model="selectedStream">
                        <option v-for="(stream, key) in streams" v-bind:value="key">{{ stream.stream }}</option>
                      </select>
                    </div>
                    
                    <template v-if="streams[selectedStream]">
                      <div id="substreams">
                        <div v-for="substream in streams[selectedStream].substreams" v-bind:id="'substream-' + substream.id">
                          <input type="checkbox" class="btn-check" v-bind:id="'substreamCheck-' + substream.id" autocomplete="off">
                          <label class="btn btn-outline-secondary btn-sm" v-bind:for="'substreamCheck-' + substream.id" v-on:click="toggleSubstreamId(substream.id)" @dblclick="renameSubstream(substream.name)">{{ substream.name }}</label>
                        </div>
                      </div>
                      <!-- <template v-for="substream in streams[selectedStream].substreams" >
                        <input type="checkbox" class="btn-check" v-bind:id="'substreamCheck-' + substream.id" autocomplete="off">
                        <label class="btn btn-outline-secondary btn-sm" v-bind:for="'substreamCheck-' + substream.id" v-on:click="toggleSubstreamId(substream.id)" @dblclick="renameSubstream(substream.name)">{{ substream.name }}</label>
                      </template> -->
                    </template>

                    <div class="text-center">
                      <button type="button" class="btn btn-outline-danger w-100 mb-2" v-on:click="removeSelectedSubstreams()">Удалить выбранные</button>
                      <button type="button" class="btn bg-gradient-primary w-100 mb-2" v-on:click="addSubstream()">Добавить</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <div class="card my-4">
                  <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3">
                      <h6 class="text-white text-capitalize ps-3">Забаненные страны</h6>
                    </div>
                  </div>
                  <div class="card-body">
                    <select id="countries-select" class="form-control" multiple placeholder="Выберите страны...">
                      <?php
                        for ($i = 3; $i < count(geo_ip::$COUNTRY_CODES) - 3; $i++) {
                          echo "<option value='" . geo_ip::$COUNTRY_CODES[$i] . "'>" . geo_ip::$COUNTRY_NAMES[$i] . "</option>";
                        }
                      ?>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-12 col-sm-6">
            <div class="row">
              <div class="col-12">
                <div class="card my-4">
                  <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3">
                      <h6 class="text-white text-capitalize ps-3">Потоки</h6>
                    </div>
                  </div>
                  <div class="card-body">
                    <div id="streams">
                      <div v-for="stream in streams" v-bind:id="'stream-' + stream.id">
                        <input type="checkbox" class="btn-check" v-bind:id="'streamCheck-' + stream.id" autocomplete="off">
                        <label class="btn btn-outline-secondary btn-sm" v-bind:for="'streamCheck-' + stream.id" v-on:click="toggleStreamId(stream.id)" @dblclick="renameStream(stream.stream)">{{ stream.stream }}</label>
                      </div>
                    </div>
                    <div class="text-center">
                      <button type="button" class="btn btn-outline-danger w-100 mb-2" v-on:click="removeSelectedStreams()">Удалить выбранные</button>
                      <button type="button" class="btn bg-gradient-primary w-100 mb-2" v-on:click="addStream()">Добавить</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <div class="card my-4">
                  <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3">
                      <h6 class="text-white text-capitalize ps-3">Потоки с параметром</h6>
                    </div>
                  </div>
                  <div class="card-body">
                    <template v-for="param in params" >
                      <input type="checkbox" class="btn-check" v-bind:id="'paramCheck-' + param" autocomplete="off">
                      <label class="btn btn-outline-secondary btn-sm" v-bind:for="'paramCheck-' + param" v-on:click="toggleParamName(param)">{{ param }}</label>
                    </template>
                    <div class="text-center">
                      <button type="button" class="btn btn-outline-danger w-100 mb-2" v-on:click="removeSelectedParams()">Удалить выбранные</button>
                      <button type="button" class="btn bg-gradient-primary w-100 mb-2" v-on:click="addParam()">Добавить</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <script src="/assets/js/pages/settings.js"></script>