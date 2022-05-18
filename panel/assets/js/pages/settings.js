var app = new Vue({
  el: '#content',
  data: {
    passForm: {
      password: "",
      newpass: "",
      repeat: "",
    },
    passFormErrors: [],
    streamFormErrors: [],
    streams: [],
    selectedStreams: [],
    selectedSubstreams: [],
    selectedStream: 0,
    params: [],
    selectedParams: [],
    countriesSelect: null,
  },
  mounted: function () {
    let tmp_select = $('#countries-select').selectize();
    this.countriesSelect = tmp_select[0].selectize;

    this.countriesSelect.on("change", function() {
      let countries = JSON.stringify($("#countries-select").val());
      axios({
        method: "get",
        url:`/api/banCountries?countries=${countries}`,
      })
      .then(function (response) {
        if(response.data.success == true) {
          Toastify({ text: "Успешно установил страны", className: "bg-gradient-success border-radius-lg" }).showToast();
        }
        else {
          Toastify({ text: "Произошла ошибка", className: "bg-gradient-danger border-radius-lg" }).showToast();
        }
      })
      .catch(function (error) {
        Toastify({ text: "Произошла ошибка во время установки стран", className: "bg-gradient-danger border-radius-lg" }).showToast();
      });
    });

    this.updateStreams();
    this.updateParams();
    this.updateCountries();
  },
  methods: {
    updateStreams: function() {
      let self = this;
      axios({
        method: "get",
        url: '/api/getStreams',
      })
      .then(function (response) {
        self.streams = [];
        for(stream of response.data) {
          self.streams.push(stream);
        }

        function compare(a, b) {
          if (a.position < b.position)
            return -1;
          if (a.position > b.position)
            return 1;
          return 0;
        }
        
        self.streams.sort(compare);

        for (let i = 0; i < self.streams.length; i++) {
          self.streams[i].substreams.sort(compare);
        }

        $("#streams").sortable({
          update: self.saveStreamsOrder
        });

        self.$nextTick(function () {
          $("#substreams").sortable({
            update: self.saveSubstreamsOrder
          });
        });
      })
      .catch(function (error) {
        Toastify({ text: "Произошла ошибка во время получения потоков", className: "bg-gradient-danger border-radius-lg" }).showToast();
      });
    },
    saveStreamsOrder: function() {
      let streams = $('#streams').sortable("toArray");
      for (let i = 0; i < streams.length; i++) {
        streams[i] = streams[i].replace("stream-", "");
      }
      
      $.post("/api/updateStreamsOrder", { streams: streams })
      .done(function(data) {
        data = JSON.parse(data);
        if (data.success == true) {
          Toastify({ text: "Порядок сохранен", className: "bg-gradient-success border-radius-lg" }).showToast();
        } else {
          Toastify({ text: data.error, className: "bg-gradient-danger border-radius-lg" }).showToast();
        }
      })
      .fail(function() {
        Toastify({ text: "Произошла ошибка", className: "bg-gradient-danger border-radius-lg" }).showToast();
      })
    },
    saveSubstreamsOrder: function() {
      let substreams = $('#substreams').sortable("toArray");
      for (let i = 0; i < substreams.length; i++) {
        substreams[i] = substreams[i].replace("substream-", "");
      }
      
      $.post("/api/updateSubstreamsOrder", { substreams: substreams })
      .done(function(data) {
        data = JSON.parse(data);
        if (data.success == true) {
          Toastify({ text: "Порядок сохранен", className: "bg-gradient-success border-radius-lg" }).showToast();
        } else {
          Toastify({ text: data.error, className: "bg-gradient-danger border-radius-lg" }).showToast();
        }
      })
      .fail(function() {
        Toastify({ text: "Произошла ошибка", className: "bg-gradient-danger border-radius-lg" }).showToast();
      })
    },
    updateParams: function() {
      let self = this;
      axios({
        method: "get",
        url: '/api/getParams',
      })
      .then(function (response) {
        self.params = [];
        for(stream of response.data) {
          self.params.push(stream);
        }
      })
      .catch(function (error) {
        Toastify({ text: "Произошла ошибка во время получения параметров", className: "bg-gradient-danger border-radius-lg" }).showToast();
      });
    },
    updateCountries: function() {
      let self = this;
      axios({
        method: "get",
        url: '/api/getBannedCountries',
      })
      .then(function (response) {
        let countries = JSON.parse(response.data);
        if(countries != null) {
          countries.forEach(element => {
            self.countriesSelect.addItem(element, true);
          });
        }
      })
      .catch(function (error) {
        Toastify({ text: "Произошла ошибка во время загрузки стран", className: "bg-gradient-danger border-radius-lg" }).showToast();
      });
    },

    checkPassForm: function() {
      this.passFormErrors = [];
      res = true;
      if (this.passForm.password.length < 1)  {
        this.passFormErrors.push("Поле 'Пароль' не может быть пустым.");
        res = false;
      }
      if (this.passForm.newpass.length < 1)  {
        this.passFormErrors.push("Поле 'Новый пароль' не может быть пустым.");
        res = false;
      }
      if (this.passForm.repeat.length < 1)  {
        this.passFormErrors.push("Поле 'Повторите пароль' не может быть пустым.");
        res = false;
      }

      if (res) this.sendPassForm();
    },
    sendPassForm: function() {
      let fd = new FormData();

      fd.append("password", this.passForm.password);
      fd.append("newpass", this.passForm.newpass);
      fd.append("repeat", this.passForm.repeat);

      axios({
        method: "post",
        url: '/api/changePass',
        data: fd
      })
      .then(function (response) {
        if (response.data.success == true) {
          Toastify({ text: "Пароль успешно изменен", className: "bg-gradient-success border-radius-lg" }).showToast();
        } else {
          Toastify({ text: response.data.error, className: "bg-gradient-danger border-radius-lg" }).showToast();
        }
      })
      .catch(function (error) {
        Toastify({ text: "Произошла ошибка", className: "bg-gradient-danger border-radius-lg" }).showToast();
      });
    },
    addStream: function() {
      let streamName = prompt("Введите имя потока");
      let check = /^[A-Za-z_]*$/.test(streamName);
      if (streamName.length > 0 && check == true) {
        let self = this;
        let fd = new FormData();
  
        fd.append("stream", streamName);
  
        axios({
          method: "post",
          url: '/api/addStream',
          data: fd
        })
        .then(function (response) {
          if (response.data.success == true) {
            Toastify({ text: "Поток успешно добавлен", className: "bg-gradient-success border-radius-lg" }).showToast();
            self.updateStreams();
          } else {
            Toastify({ text: response.data.error, className: "bg-gradient-danger border-radius-lg" }).showToast();
          }
        })
        .catch(function (error) {
          Toastify({ text: "Произошла ошибка", className: "bg-gradient-danger border-radius-lg" }).showToast();
        });
      } else {
        Toastify({ text: "Ошибка в имени потока", className: "bg-gradient-danger border-radius-lg" }).showToast();
      }
    },
    addSubstream: function() {
      let substreamName = prompt("Введите имя подпотока");
      let check = /^[A-Za-z_]*$/.test(substreamName);
      if (substreamName.length > 0 && check == true) {
        let self = this;
        let fd = new FormData();
  
        fd.append("streamid", this.streams[this.selectedStream].id);
        fd.append("name", substreamName);
  
        axios({
          method: "post",
          url: '/api/addSubstream',
          data: fd
        })
        .then(function (response) {
          if (response.data.success == true) {
            Toastify({ text: "Подпоток успешно добавлен", className: "bg-gradient-success border-radius-lg" }).showToast();
            self.updateStreams();
          } else {
            Toastify({ text: response.data.error, className: "bg-gradient-danger border-radius-lg" }).showToast();
          }
        })
        .catch(function (error) {
          Toastify({ text: "Произошла ошибка", className: "bg-gradient-danger border-radius-lg" }).showToast();
        });
      } else {
        Toastify({ text: "Ошибка в имени подпотока", className: "bg-gradient-danger border-radius-lg" }).showToast();
      }
    },
    addParam: function() {
      let name = prompt("Введите имя параметра");
      if (name.length > 0) {
        let self = this;
        let fd = new FormData();
  
        fd.append("name", name);
  
        axios({
          method: "post",
          url: '/api/addParam',
          data: fd
        })
        .then(function (response) {
          if (response.data.success == true) {
            Toastify({ text: "Параметр успешно добавлен", className: "bg-gradient-success border-radius-lg" }).showToast();
            self.updateParams();
          } else {
            Toastify({ text: response.data.error, className: "bg-gradient-danger border-radius-lg" }).showToast();
          }
        })
        .catch(function (error) {
          Toastify({ text: "Произошла ошибка", className: "bg-gradient-danger border-radius-lg" }).showToast();
        });
      } else {
        Toastify({ text: "Ошибка в имени параметра", className: "bg-gradient-danger border-radius-lg" }).showToast();
      }
    },
    toggleStreamId: function(id) {
      let ind = this.selectedStreams.indexOf(id);
      if (ind != -1) {
        this.selectedStreams.splice(ind, 1);
      } else {
        this.selectedStreams.push(id);
      }
    },
    toggleSubstreamId: function(id) {
      let ind = this.selectedSubstreams.indexOf(id);
      if (ind != -1) {
        this.selectedSubstreams.splice(ind, 1);
      } else {
        this.selectedSubstreams.push(id);
      }
    },
    toggleParamName: function(name) {
      let ind = this.selectedParams.indexOf(name);
      if (ind != -1) {
        this.selectedParams.splice(ind, 1);
      } else {
        this.selectedParams.push(name);
      }
    },
    removeSelectedStreams: function() {
      if (this.selectedStreams.length < 1) {
        Toastify({ text: "Необходимо выбрать потоки", className: "bg-gradient-info border-radius-lg" }).showToast();
        return;
      }

      let check = confirm("Вы действительно хотите удалить выделенное?");
      if (check == true) {
        let self = this;
        let fd = new FormData();
  
        fd.append("ids", this.selectedStreams);
  
        axios({
          method: "post",
          url: '/api/removeStreams',
          data: fd
        })
        .then(function (response) {
          if (response.data.success == true) {
            Toastify({ text: "Потоки успешно удалены", className: "bg-gradient-success border-radius-lg" }).showToast();
            self.updateStreams();
          } else {
            Toastify({ text: response.data.error, className: "bg-gradient-danger border-radius-lg" }).showToast();
          }
        })
        .catch(function (error) {
          Toastify({ text: "Произошла ошибка", className: "bg-gradient-danger border-radius-lg" }).showToast();
        });
      }
    },
    removeSelectedSubstreams: function() {
      if (this.selectedSubstreams.length < 1) {
        Toastify({ text: "Необходимо выбрать подпотоки", className: "bg-gradient-info border-radius-lg" }).showToast();
        return;
      }

      let check = confirm("Вы действительно хотите удалить выделенное?");
      if (check == true) {
        let self = this;
        let fd = new FormData();
  
        fd.append("ids", this.selectedSubstreams);
  
        axios({
          method: "post",
          url: '/api/removeSubstreams',
          data: fd
        })
        .then(function (response) {
          if (response.data.success == true) {
            Toastify({ text: "Подпотоки успешно удалены", className: "bg-gradient-success border-radius-lg" }).showToast();
            self.updateStreams();
          } else {
            Toastify({ text: response.data.error, className: "bg-gradient-danger border-radius-lg" }).showToast();
          }
        })
        .catch(function (error) {
          Toastify({ text: "Произошла ошибка", className: "bg-gradient-danger border-radius-lg" }).showToast();
        });
      }
    },
    removeSelectedParams: function() {
      if (this.selectedParams.length < 1) {
        Toastify({ text: "Необходимо выбрать параметры", className: "bg-gradient-info border-radius-lg" }).showToast();
        return;
      }

      let check = confirm("Вы действительно хотите удалить выделенное?");
      if (check == true) {
        let self = this;
        let fd = new FormData();
  
        fd.append("names", this.selectedParams);
  
        axios({
          method: "post",
          url: '/api/removeParams',
          data: fd
        })
        .then(function (response) {
          if (response.data.success == true) {
            Toastify({ text: "Параметры успешно удалены", className: "bg-gradient-success border-radius-lg" }).showToast();
            self.updateParams();
          } else {
            Toastify({ text: response.data.error, className: "bg-gradient-danger border-radius-lg" }).showToast();
          }
        })
        .catch(function (error) {
          Toastify({ text: "Произошла ошибка", className: "bg-gradient-danger border-radius-lg" }).showToast();
        });
      }
    },
    renameStream(stream) {
      let name = prompt("Введите новое имя");
      let check = /^[A-Za-z_]*$/.test(name);
      if (name.length > 0 && check == true) {
        let self = this;
        let fd = new FormData();
  
        fd.append("oldName", stream);
        fd.append("newName", name);
  
        axios({
          method: "post",
          url: '/api/renameStream',
          data: fd
        })
        .then(function (response) {
          if (response.data.success == true) {
            Toastify({ text: "Успешно переименовано", className: "bg-gradient-success border-radius-lg" }).showToast();
            self.updateStreams();
          } else {
            Toastify({ text: response.data.error, className: "bg-gradient-danger border-radius-lg" }).showToast();
          }
        })
        .catch(function (error) {
          Toastify({ text: "Произошла ошибка", className: "bg-gradient-danger border-radius-lg" }).showToast();
        });
      } else {
        Toastify({ text: "Ошибка в имени", className: "bg-gradient-danger border-radius-lg" }).showToast();
      }
    },
    renameSubstream(substream) {
      let name = prompt("Введите новое имя");
      let check = /^[A-Za-z_]*$/.test(name);
      if (name.length > 0 && check == true) {
        let self = this;
        let fd = new FormData();
  
        fd.append("oldName", substream);
        fd.append("newName", name);
  
        axios({
          method: "post",
          url: '/api/renameSubstream',
          data: fd
        })
        .then(function (response) {
          if (response.data.success == true) {
            Toastify({ text: "Успешно переименовано", className: "bg-gradient-success border-radius-lg" }).showToast();
            self.updateStreams();
          } else {
            Toastify({ text: response.data.error, className: "bg-gradient-danger border-radius-lg" }).showToast();
          }
        })
        .catch(function (error) {
          Toastify({ text: "Произошла ошибка", className: "bg-gradient-danger border-radius-lg" }).showToast();
        });
      } else {
        Toastify({ text: "Ошибка в имени", className: "bg-gradient-danger border-radius-lg" }).showToast();
      }
    }
  },
  watch: {
    selectedStream: function (value) {
      $("#substreams").sortable({
        update: this.saveSubstreamsOrder
      });
    }
  }
})