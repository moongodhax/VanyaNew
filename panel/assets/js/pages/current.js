var app = new Vue({
  el: '#content',
  data: {
    streams: [],
    selectedStreamid: 0,
    selectedSubstreamid: 0,
    currentDates: [],
    selectedDate: "",
  },
  mounted: function () {
    let self = this;
    $("#current-table").DataTable({
      serverSide: true,
      ajax: function (data, callback, settings) {
        if (self.selectedStreamid == 0 || self.selectedDate == "") return callback([]);
        $.post(`/datatables.php?streamid=${self.selectedStreamid}&substreamid=${self.selectedSubstreamid}&timestamp=${self.selectedDate}`, data)
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
          title: "Тип",
          data: "type",
          render: function( data, type, row, meta ) {
            if (row.reason != "") return `${row.type} / ${row.reason}`;
            else return data;
          }
        },
        {
          data: "reason",
          visible: false,
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
      order: [[7, "desc"]],
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

        if (self.streams.length > 0) {
          self.$nextTick(() => {
            self.selectedStreamid = self.streams[0].id;
            $("#type_header").html(self.streams[0].stream);
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
        url: '/api/getCurrentDates?streamid=' + this.selectedStreamid,
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
    streamSelected: function() {
      let selected_option = $("#type_select").find(":selected");
      this.selectedStreamid = selected_option.data("stream");
      this.selectedSubstreamid = selected_option.data("substream");
      $("#type_header").html(selected_option.text());
      this.updateDates();
    }
  },
  watch: {
    selectedDate: function(value) {
      let selected_option = $("#date_select").find(":selected");
      $("#date_header").html(selected_option.text());
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
