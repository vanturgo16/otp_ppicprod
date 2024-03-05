
function detail_sparepart_auxiliaries_edit(id) {
  
  // Kirim data melalui Ajax
  $.ajax({
    url: '/production-req-sparepart-auxiliaries-detail-edit-get/' + id,
    method: 'GET',
    data: {
      id: id
    },
    success: function (response) {
      // Tangkap pesan dari server dan tampilkan ke user
      // console.log(response.data.find.cc_co);

      $('#form_detail_sparepart_auxiliaries_edit').attr('action', '/production-req-sparepart-auxiliaries-detail-edit-save/' + response.data.find.id)
      $('#qty_pr').val(response.data.find.qty)
	  $('#remarks_pr').val(response.data.find.remarks)
      $('#request_number_pr').val(document.getElementById('request_number_original').value)
	  
      let produkSelect = response.data.find.id_master_tool_auxiliaries

      $('#id_master_tool_auxiliaries_pr').empty()
      $('#id_master_tool_auxiliaries_pr').append(` <option>Pilih Produk</option>`)
      $.each(response.data.ms_tool_auxiliaries, function (i, value) {
        let isSelected = produkSelect == value.id ? 'selected' : ''

        $('#id_master_tool_auxiliaries_pr').append(
          `<option value="` + value.id + `"` + isSelected + `>` + value.description + `</option>`
        )
      });


      // Contoh: Lakukan tindakan selanjutnya setelah data berhasil dikirim
      // window.location.href = '/success-page';
    },
    error: function (xhr, status, error) {
      // Tangkap pesan error jika ada
      alert('Terjadi kesalahan saat mengirim data.');
    }
  });
  // })
}


function get_data() {
  
  request_number = $('.request_number option:selected').attr('data-id');
  // alert(request_number);
  $.ajax({
    url: '/get-data/',
    method: 'GET',
    data: {id : request_number},
    success: function (response) {
      console.log(response);
      // unit = response.data_lengkap.master_unit.unit_code
      rn = response.data_lengkap[0].request_number
      sp = response.data_lengkap[0].name
      // console.log(unit);
      // Loop melalui data dan tambahkan opsi ke dalam select
      // $('#type_pr').val(response.pr_detail.type)
      $('#reference_number').empty()
        $('#reference_number').append(` <option>Pilih Reference Number</option>`)
        $.each(response.data_pr, function (i, value) {
          isSelected = value.request_number == rn ? 'selected' : '';
          $('#request_number').append(
            `<option value="` + value.id + `" ` + isSelected + `>` + value.request_number + `</option>`
          )
        });

      $('#id_master_suppliers').empty()
        $('#id_master_suppliers').append(` <option>Pilih Supplier</option>`)
        $.each(response.data_sp, function (i, value) {
          isSelected = value.name == sp ? 'selected' : '';
          $('#id_master_suppliers').append(
            `<option value="` + value.id + `" ` + isSelected + `>` + value.name + `</option>`
          )
        });
        $('#type').val(response.data_lengkap[0].type)
    },
    error: function (xhr, status, error) {
      // Tangkap pesan error jika ada
      alert('Terjadi kesalahan saat mengirim data.');
    }
  });
}

function get_data_pr() {
  
  request_number = $('.request_number option:selected').attr('data-id');
  // alert(request_number);
  $.ajax({
    url: '/get-data/',
    method: 'GET',
    data: {id : request_number},
    success: function (response) {
      console.log(response);
      // unit = response.data_lengkap.master_unit.unit_code
      sp = response.data_lengkap_pr[0].name
      // console.log(unit);
      // Loop melalui data dan tambahkan opsi ke dalam select
      // $('#type_pr').val(response.pr_detail.type)

      $('#id_master_suppliers').empty()
        $('#id_master_suppliers').append(` <option>Pilih Supplier</option>`)
        $.each(response.data_sp, function (i, value) {
          isSelected = value.name == sp ? 'selected' : '';
          $('#id_master_suppliers').append(
            `<option value="` + value.id + `" ` + isSelected + `>` + value.name + `</option>`
          )
        });
        $('#type').val(response.data_lengkap_pr[0].type)
    },
    error: function (xhr, status, error) {
      // Tangkap pesan error jika ada
      alert('Terjadi kesalahan saat mengirim data.');
    }
  });
}









