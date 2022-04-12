var app = new Vue({
  el: '#content',
  data: {
    allStats: [],
    streams: [],
    selectedStream: {
      stream: "",
      substream: "",
    },
    chart: null,
  },
  mounted: function () {
    $('.slick').slick({
      infinite: true,
      slidesToShow: 4,
      slidesToScroll: 1,
      nextArrow: '<div class="slick-next"><i class="fas fa-angle-right"></i></div>',
      prevArrow: '<div class="slick-prev"><i class="fas fa-angle-left"></i></div>',
      responsive: [
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: 3
          }
        },
        {
          breakpoint: 800,
          settings: {
            slidesToShow: 2
          }
        },
        {
          breakpoint: 576,
          settings: {
            slidesToShow: 1
          }
        },
      ]
    });

    this.chart = new Chart(document.getElementById("chart-line").getContext("2d"), {
      type: "line",
      data: {
        labels: [],
        datasets: [
          {
            label: "Инсталлов",
            tension: 0,
            borderWidth: 0,
            pointRadius: 5,
            pointBackgroundColor: "rgba(255, 255, 255, .8)",
            pointBorderColor: "transparent",
            borderColor: "rgba(255, 255, 255, .8)",
            borderColor: "rgba(255, 255, 255, .8)",
            borderWidth: 4,
            backgroundColor: "transparent",
            fill: true,
            data: [],
            maxBarThickness: 6,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        interaction: {
          intersect: false,
          mode: "index",
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5],
              color: "rgba(255, 255, 255, .2)",
            },
            ticks: {
              display: true,
              color: "#f8f9fa",
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: "normal",
                lineHeight: 2,
              },
            },
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [5, 5],
            },
            ticks: {
              display: true,
              color: "#f8f9fa",
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: "normal",
                lineHeight: 2,
              },
            },
          },
        },
      },
    });

    
    let countries_table = $("#countries-table")
      .DataTable({
        "paging": false,
        "ordering": false,
        "info": false,
        "searching": false,
        "processing": true,
        language: {
          url: "//cdn.datatables.net/plug-ins/1.10.19/i18n/Russian.json"
        },
        columns: [
          {
            title: "",
            data: "flag",
            render: function ( data, type, row, meta ) {
              return `<img src="/assets/img/flags/${data}.svg" class="avatar-xs me-2" title="${data}">`;
            }
          },
          {
            title: "Страна",
            data: "name",
            class: "country-name text-truncate"
          },
          {
            title: "Кол-во",
            data: "count",
          },
        ],
      });

    this.updateAllStats();
    this.updateStreams();

    this.showStream({ stream: "all" });
  },
  methods: {
    updateAllStats: function() {
      let self = this;
      axios({
        method: "get",
        url: '/api/getAllStats',
      })
      .then(function (response) {
        let stats = [];
        for(let stream of response.data) {
          stats.push(stream);
        }

        self.allStats = [null, null, null, null];
        for (let i = 0; i < stats.length; i++) {
          let stat = stats[i];
          if (stat.type == "stream") {
            if (stat.name == "mixone") {
              self.allStats[0] = stat;
              stats.splice(i, 1);
              i--;
            }
            if (stat.name == "mixtwo") {
              self.allStats[1] = stat;
              stats.splice(i, 1);
              i--;
            }
            if (stat.name == "eu") {
              self.allStats[2] = stat;
              stats.splice(i, 1);
              i--;
            }
            if (stat.name == "us") {
              self.allStats[3] = stat;
              stats.splice(i, 1);
              i--;
            }
          }
        }

        function compare(a, b) {
          if (+a.stats.day < +b.stats.day) return 1;
          if (+a.stats.day > +b.stats.day) return -1;
          return 0;
        }

        stats.sort(compare);

        self.allStats.push(...stats);

        self.$nextTick(() => {
          $('.slick').slick('refresh');
        })
      })
      .catch(function (error) {
        Toastify({ text: "Произошла ошибка во время получения статистики", className: "bg-gradient-danger border-radius-lg" }).showToast();
      });
    },
    updateStreams: function() {
      let self = this;
      axios({
        method: "get",
        url: '/api/getStreams',
      })
      .then(function (response) {
        self.streams = [];
        for(let stream of response.data) {
          self.streams.push(stream);
        }
      })
      .catch(function (error) {
        Toastify({ text: "Произошла ошибка во время получения потоков", className: "bg-gradient-danger border-radius-lg" }).showToast();
      });
    },
    showStream(options) {
      let self = this;

      this.selectedStream.stream = options.stream;
      this.selectedStream.substream = options.substream ? options.substream : "";

      $("#map").html("");

      axios({
        method: "get",
        url: `/api/getStreamStats?stream=${this.selectedStream.stream}&substream=${this.selectedStream.substream}`,
      })
      .then(function (response) {
        // если делать перезагрузку через регион, 
        // то не работает изменение интенсивности цвета стран
        $("#map").vectorMap({
          map: "world_mill", backgroundColor: "transparent",
          series: { regions: [ { values: response.data.map, scale: ["#a8a8a8", "#363636"], normalizeFunction: "polynomial", }, ], },
          onRegionTipShow: function (e, el, code) {
            let worldMap = $('#map').vectorMap('get', 'mapObject'); 
            el.html(el.html() + ": " + worldMap.series.regions[0].values[code]);
          },
          hoverOpacity: 0.7, hoverColor: false,
        });

        $("#countries-table")
          .DataTable()
          .clear()
          .draw()
          .rows.add(response.data.countries)
          .draw();
        
        self.chart.data.labels = response.data.chart.labels;
        self.chart.data.datasets[0].data = response.data.chart.data;
        self.chart.update();
      })
      .catch(function (error) {
        Toastify({ text: "Произошла ошибка во время получения статистики", className: "bg-gradient-danger border-radius-lg" }).showToast();
      });
    },
    getStreamLink(hash) {
      let url = new URL(location.href);
      let text = url.protocol + "//" + url.hostname + "/view/" + hash;
      return text;
    },
    copyStreamLink(hash) {
      var textArea = document.createElement("textarea");
    
      textArea.style.position = 'fixed';
      textArea.style.top = 0;
      textArea.style.left = 0;
    
      textArea.style.width = '2em';
      textArea.style.height = '2em';
    
      textArea.style.padding = 0;
    
      textArea.style.border = 'none';
      textArea.style.outline = 'none';
      textArea.style.boxShadow = 'none';
    
      textArea.style.background = 'transparent';
    
      textArea.value = this.getStreamLink(hash);
    
      document.body.appendChild(textArea);
      textArea.focus();
      textArea.select();
    
      try {
        var successful = document.execCommand('copy');
        if (successful) {
          Toastify({ text: "Скопировано", className: "bg-gradient-success border-radius-lg" }).showToast();
        } else {
          Toastify({ text: "Произошла ошибка", className: "bg-gradient-danger border-radius-lg" }).showToast();
        }
      } catch (err) {
        Toastify({ text: "Произошла ошибка", className: "bg-gradient-danger border-radius-lg" }).showToast();
      }
    
      document.body.removeChild(textArea);
    },
  }
});