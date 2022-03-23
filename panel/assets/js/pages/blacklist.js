
function blacklistAdd() {
  let ip = $("#ip").val();
  let reason = $("#reason").val();

  $.post("/api/addBlacklist", { ip: ip, reason: reason })
    .done(function () {
      $("#blacklist-table").DataTable().ajax.reload();
      Toastify({ text: "IP успешно забанен", className: "bg-gradient-success border-radius-lg" }).showToast();
    })
    .fail(function () {
      Toastify({ text: "Ошибка при добавлении записи", className: "bg-gradient-danger border-radius-lg" }).showToast();
    })
    .always(function () {
      $("#ip").val("");
      $("#reason").val("");
    });
}

function blacklistRemove(ip) {
  $.get("/api/removeBlacklist?ip=" + ip)
    .done(function (data) {
      $("#blacklist-table").DataTable().ajax.reload();
      Toastify({ text: "IP успешно удален", className: "bg-gradient-success border-radius-lg" }).showToast();
    })
    .fail(function () {
      Toastify({ text: "Ошибка во время удаления записи", className: "bg-gradient-danger border-radius-lg" }).showToast();
    });
}

$(document).ready(function () {
  $("#blacklist-table").DataTable({
    ajax: {
      url: `/api/getBlacklist`,
      type: "GET",
    },
    language: {
      url: "//cdn.datatables.net/plug-ins/1.10.19/i18n/Russian.json"
    },
    columns: [
      {
        name: "ip",
        title: "IP",
        data: "ip",
      },
      {
        name: "reason",
        title: "Причина",
        data: "reason",
      },
      {
        name: "acts",
        title: "Действия",
        class: "text-center",
        orderable: false,
        searchable: false,
        data: null,
        render: function ( data, type, row, meta ) {
          return `
          <a href="javascript:;" onclick="blacklistRemove('${row.ip}')" >
            <i class="fas fa-trash"></i>
          </a>`;
        }
      },
    ],
    order: [[0, "desc"]],
  });
});
