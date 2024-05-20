
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

function detail_entry_material_use_edit(id) {
  
  // Kirim data melalui Ajax
  $.ajax({
    url: '/production-entry-material-use-detail-edit-get/' + id,
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









