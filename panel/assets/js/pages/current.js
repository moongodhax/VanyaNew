var app = new Vue({
  el: '#content',
  data: {
    streams: [],
    selectedStream: "",
    selectedSubstream: "",
    currentDates: [],
    selectedDate: "",
  },
  mounted: function () {
    let self = this;
    $("#current-table").DataTable({
      serverSide: true,
      ajax: function (data, callback, settings) {
        if (self.selectedStream == "" || self.selectedDate == "") return callback([]);
        $.post(`/datatables.php?stream=${self.selectedStream}&substream=${self.selectedSubstream}&timestamp=${self.selectedDate}`, data)
        .done(function (data) {
          callback(JSON.parse(data));
        })
        .fail(function () {
          Toastify({ text: "Произошла ошибка во время загрузки таблицы", className: "bg-gradient-danger border-radius-lg" }).showToast();
        });
      },
      language: {
        url: "//cdn.datatables.net/plug-ins/1.10.19/i18n/Russian.json"
      },
      columns: [
        {
          title: "Подпоток",
          data: "substream"
        },
        {
          title: "IP",
          data: "ip",
          render: function ( data, type, row, meta ) {
            return `<img src="/assets/img/flags/${row.country.toLowerCase()}.svg" class="avatar-xs me-2" title="${row.country}"> ${data}`;
          }
        },
        {
          title: "Sub",
          data: "sub"
        },
        {
          title: "Distributor",
          data: "distributor",
        },
        {
          title: "Страна",
          data: "country",
          visible: false
        },
        {
          title: "Дата",
          data: "timestamp",
          render: function ( data, type, row, meta ) {
            var date = new Date(+data * 1000);
            return formatDate(date);
          }
        },
        {
          title: "Действия",
          class: "text-center",
          orderable: false,
          searchable: false,
          data: null,
          render: function ( data, type, row, meta ) {
            return `
            <a href="javascript:;" onclick="removeRecord(${row.id})" >
              <i class="fas fa-trash"></i>
            </a>`;
          }
        },
      ],
      order: [[5, "desc"]],
    });
    
    this.updateStreams();
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

        if (self.streams.length > 0) {
          self.$nextTick(() => {
            self.selectedStream = self.streams[0].stream;
            $("#type_header").html(self.selectedStream);
            self.updateDates();
          })
        }
      })
      .catch(function (error) {
        Toastify({ text: "Произошла ошибка во время получения потоков", className: "bg-gradient-danger border-radius-lg" }).showToast();
      });
    },
    updateDates: function() {
      let self = this;
      axios({
        method: "get",
        url: '/api/getCurrentDates?stream=' + this.selectedStream,
      })
      .then(function (response) {
        self.currentDates = [];
        for(stream of response.data) {
          self.currentDates.push(stream);
        }

        if (self.currentDates.length > 0) {
          self.$nextTick(() => {
            self.selectedDate = self.currentDates[0].time;
            $("#current-table").DataTable().ajax.reload();
            $("#date_header").html(self.currentDates[0].name);
          })
        }
      })
      .catch(function (error) {
        Toastify({ text: "Произошла ошибка во время получения дат", className: "bg-gradient-danger border-radius-lg" }).showToast();
      });
    },
    clearCurrent: function() {
      let self = this;
      axios({
        method: "get",
        url: '/api/clearCurrent?stream=' + this.selectedStream,
      })
      .then(function (response) {
        self.updateDates();
        $("#current-table").DataTable().ajax.reload();
      })
      .catch(function (error) {
        Toastify({ text: "Произошла ошибка во время очистки текущей таблицы", className: "bg-gradient-danger border-radius-lg" }).showToast();
      });
    },
    streamSelected: function() {
      let selected_option = $("#type_select").find(":selected");
      this.selectedStream = selected_option.data("stream");
      this.selectedSubstream = selected_option.data("substream");

      let header = this.selectedStream;
      if (this.selectedSubstream != "") header += " - " + this.selectedSubstream

      $("#type_header").html(header);

      this.updateDates();
    }
  },
  watch: {
    selectedDate: function(value) {
      $("#current-table").DataTable().ajax.reload();
    }
  }
});

function formatDate(date) {
  var hours = date.getHours() < 10 ? "0" + date.getHours() : date.getHours();
  var minutes = date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes();

  var day = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();
  var month = date.getMonth() + 1 < 10 ? "0" + (+date.getMonth() + 1) : +date.getMonth() + 1;
  var year = date.getFullYear();

  return day + "/" + month + "/" + year + " " + hours + ":" + minutes;
}

function removeRecord(id) {
  $.get("/api/removeRecord?id=" + id)
    .done(function (data) {
      $("#all-table").DataTable().ajax.reload();
      Toastify({ text: "Запись успешно удалена", className: "bg-gradient-success border-radius-lg" }).showToast();
    })
    .fail(function () {
      Toastify({ text: "Ошибка во время удаления записи", className: "bg-gradient-danger border-radius-lg" }).showToast();
    });
}
