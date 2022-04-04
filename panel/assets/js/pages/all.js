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

$(document).ready(function () {
  $("#all-table").DataTable({
      serverSide: true,
      ajax: {
        url: `/datatables.php`,
        type: "POST",
      },
      language: {
        url: "//cdn.datatables.net/plug-ins/1.10.19/i18n/Russian.json"
      },
      columns: [
        {
          title: "Поток",
          data: "stream",
          render: function( data, type, row, meta ) {
            if (row.substream != "") return `${row.stream} / ${row.substream}`;
            else return data;
          }
        },
        {
          data: "substream",
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
          data: "country",
          visible: false,
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
});
