$(document).ready(function () {
    // In your Javascript (external .js resource or <script> tag)
    $('.data-select2').select2({
        width: 'resolve', // need to override the changed default
        theme: "classic"
    });

    $(".data-select2.read").prop("disabled", true);

    $('#startDate').change(function () {
        const date = new Date($(this).val());
        const newDate = addDays(date, 7);
        const finish_date = newDate.toISOString().split('T')[0];

        $('#finishDate').val(finish_date)
    });


	//JS PRODUCTION START
	$('#departementSelectx').change(function () {
        let proccessProduction = $(this).find(':selected').data('code');

        if (proccessProduction != '') {
            $.ajax({
                url: baseRoute + '/ppic/workOrder/generate-wo-number',
                type: 'GET',
                dataType: 'json',
                data: {
                    proccessProduction: proccessProduction
                },
                success: function (response) {
                    // console.log(response);
                    $('#wo_number').val(response.code)
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        } else {
            $('#wo_number').val('')
        }
    });	
	//JS PRODUCTION END




    // ketika option sales order berubah
    $('#salesOrderSelect').change(function () {
        // let so_number = $(this).val();
        let so_number = $(this).select2().find(":selected").data("so-number");

        if (so_number != '') {
            // mengambil data type_product, product, qty dan unit sesuai so_number yang dipilih
            $.ajax({
                url: baseRoute + '/ppic/workOrder/get-order-detail',
                type: 'GET',
                dataType: 'json',
                data: {
                    so_number: so_number
                },
                success: function (response) {
                    // console.log(response);
                    let products = response.products

                    // Mendapatkan detail dari respons AJAX
                    let details = response.sales_order.sales_order_details;
                    $('.data-select2').select2("destroy");

                    // let idMasterTermPayment = response.order.id_master_term_payments;

                    // let isTermPaymentDisabled = response.termPayments.some(termPayment => idMasterTermPayment == termPayment.id);
                    // $('#termPaymentSelect').prop('disabled', isTermPaymentDisabled);

                    // Mengisi baris baru sesuai dengan detail
                    for (let i = 0; i < details.length; i++) {
                        // console.log(details[i].id_master_product);
                        // Mengisi nilai dari detail ke dalam baris yang di-klon
                        $('.typeProductSelect').val(details[i].type_product);

                        // Function untuk menambahkan opsi produk ke elemen select
                        function appendProductOption(product) {
                            $('.productSelect').append($('<option>', {
                                value: product.id,
                                text: product.description
                            }));
                        }

                        // Function untuk memfilter produk berdasarkan tipe
                        function filterProductsByType(productType) {
                            // Bersihkan opsi yang ada sebelum menambahkan yang baru
                            $('.productSelect').empty();
                            // $('.productSelect').append('<option value="">** Please select a Product</option>');

                            // Filter dan tambahkan opsi produk sesuai dengan tipe yang dipilih
                            products.filter(function (product) {
                                return product.type_product === productType;
                            }).forEach(function (filteredProduct) {
                                appendProductOption(filteredProduct);
                            });
                        }

                        // Panggil fungsi pertama kali untuk menampilkan semua produk (jika ada)
                        filterProductsByType(details[i].type_product);
                        // $('.productSelect').val(details[i].id_master_product);
                        $('.qty').val(details[i].qty);
                        $('.unitSelect').val(details[i].id_master_units);
                    }

                    // Menginisialisasi Select2 untuk baris baru
                    $('.data-select2').select2({
                        width: 'resolve', // need to override the changed default
                        theme: "classic"
                    });
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        } else {

        }
    })

    $('#proccessProductionSelect').change(function () {
        let proccessProduction = $(this).find(':selected').data('code');

        if (proccessProduction != '') {
            $.ajax({
                url: baseRoute + '/ppic/workOrder/generate-wo-number',
                type: 'GET',
                dataType: 'json',
                data: {
                    proccessProduction: proccessProduction
                },
                success: function (response) {
                    // console.log(response);
                    $('#wo_number').val(response.code)
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        } else {
            $('#wo_number').val('')
        }
    });

    $(document).on('submit', '#formWorkOrder', function (e) {
        // e.preventDefault(); // Mencegah formulir terkirim secara default

        $("select").removeAttr("disabled");
        this.submit();
    });

    var isChecked = false;
    $('#checkAllRows').click(function () {
        isChecked = !isChecked;
        $(':checkbox').prop("checked", isChecked);
    });

    $(document).on('change', '.rowCheckbox', function () {
        // $(this).closest('tr').toggleClass('table-success', this.checked);
        if (!this.checked) {
            $('#checkAllRows').prop('checked', false);
        } else {
            // Check if all checkboxes in tbody are checked
            if ($('.rowCheckbox:checked').length === $('.rowCheckbox').length) {
                $('#checkAllRows').prop('checked', true);
            }
        }
    });
});

function addDays(date, days) {
    const copy = new Date(Number(date))
    copy.setDate(date.getDate() + days)
    return copy
}

function getAllUnit() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: baseRoute + '/ppic/workOrder/get-all-unit',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                resolve(response);
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText);
                reject(error);
            }
        });
    });
}

function fetchProducts(selectElement) {
    let selectedType = $(selectElement).val();
    let productSelect = $('.productSelect');
    let unitSelect = $('.unitSelect');
    let options = '<option value="">** Please select a Product</option>';

    // Hanya membuat permintaan AJAX jika tipe dipilih
    if (selectedType) {
        $.ajax({
            url: baseRoute + '/ppic/workOrder/get-data-product',
            type: 'GET',
            dataType: 'json',
            data: {
                typeProduct: selectedType
            },
            success: function (response) {
                // Tanggapi dengan mengisi opsi produk sesuai data dari server
                if (selectedType == 'WIP') {
                    $.each(response.products, function (index, product) {
                        options += '<option value="' + product.id + '">' + product.wip_code + ' | ' + product.description + '</option>';
                    });
                } else if (selectedType == 'FG') {
                    $.each(response.products, function (index, product) {
                        options += '<option value="' + product.id + '">' + product.product_code + ' | ' + product.description + '</option>';
                    });
                }
                productSelect.html(options);
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    } else {
        productSelect.html(options);
    }
    // Cara menggunakannya
    getAllUnit()
        .then(response => {
            // Lakukan sesuatu dengan response
            let optionsUnit = `<option value="">** Please select a Unit Proccess</option>${response.map(unit => `<option value="${unit.id}">${unit.unit}</option>`).join('')}`;
            // unitSelect.html(optionsUnit);
            unitSelect.html(optionsUnit);
        })
        .catch(error => {
            // Tangani kesalahan
            console.error(error);
        });
}

function fetchProductMaterials(selectElement) {
    let selectedType = $(selectElement).val();
    let productSelect = $('.productMaterialSelect');
    let unitSelect = $('.unitNeeded');
    let options = '<option value="">** Please select a Product Material</option>';

    // Hanya membuat permintaan AJAX jika tipe dipilih
    if (selectedType) {
        $.ajax({
            url: baseRoute + '/ppic/workOrder/get-data-product',
            type: 'GET',
            dataType: 'json',
            data: {
                typeProduct: selectedType
            },
            success: function (response) {
                // Tanggapi dengan mengisi opsi produk sesuai data dari server
                if (selectedType == 'WIP') {
                    $.each(response.products, function (index, product) {
                        options += '<option value="' + product.id + '">' + product.wip_code + ' | ' + product.description + '</option>';
                    });
                } else if (selectedType == 'FG') {
                    $.each(response.products, function (index, product) {
                        options += '<option value="' + product.id + '">' + product.product_code + ' | ' + product.description + '</option>';
                    });
                }
                productSelect.html(options);
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    } else {
        productSelect.html(options);
    }
    // Cara menggunakannya
    getAllUnit()
        .then(response => {
            // Lakukan sesuatu dengan response
            let optionsUnit = `<option value="">** Please select a Unit Needed</option>${response.map(unit => `<option value="${unit.id}">${unit.unit}</option>`).join('')}`;
            // unitSelect.html(optionsUnit);
            unitSelect.html(optionsUnit);
        })
        .catch(error => {
            // Tangani kesalahan
            console.error(error);
        });
}

function fethchProductDetail(selectElement) {
    let typeProduct = $('.typeProductSelect').val();
    let selectedProductId = $(selectElement).val();

    if (selectedProductId) {
        $.ajax({
            url: baseRoute + '/ppic/workOrder/get-product-detail',
            type: 'GET',
            dataType: 'json',
            data: {
                typeProduct: typeProduct,
                idProduct: selectedProductId
            },
            success: function (response) {
                let qty = $('.qty');
                let unitSelect = $('.unitSelect');
                let idUnit = response.product.id_master_units;
                getAllUnit()
                    .then(response => {
                        // Lakukan sesuatu dengan response
                        let optionsUnit = `<option value="">** Please select a Unit</option>${response.map(unit => `<option value="${unit.id}"${idUnit == unit.id ? 'selected' : ''}>${unit.unit}</option>`).join('')}`;
                        unitSelect.html(optionsUnit);
                    })
                    .catch(error => {
                        // Tangani kesalahan
                        console.error(error);
                    });
                qty.focus();
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }
}

function fethchProductMaterialDetail(selectElement) {
    let typeProduct = $('.typeProductMaterialSelect').val();
    let selectedProductId = $(selectElement).val();

    if (selectedProductId) {
        $.ajax({
            url: baseRoute + '/ppic/workOrder/get-product-detail',
            type: 'GET',
            dataType: 'json',
            data: {
                typeProduct: typeProduct,
                idProduct: selectedProductId
            },
            success: function (response) {
                let qtyNeeded = $('.qtyNeeded');
                let unitSelect = $('.unitNeeded');
                let idUnit = response.product.id_master_units;
                getAllUnit()
                    .then(response => {
                        // Lakukan sesuatu dengan response
                        let optionsUnit = `<option value="">** Please select a Unit Needed</option>${response.map(unit => `<option value="${unit.id}"${idUnit == unit.id ? 'selected' : ''}>${unit.unit}</option>`).join('')}`;
                        unitSelect.html(optionsUnit);
                    })
                    .catch(error => {
                        // Tangani kesalahan
                        console.error(error);
                    });
                qtyNeeded.focus();
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }
}

function showModal(selectElement, actionButton = null) {
    let wo_number = $(selectElement).attr("data-wo-number");
    let status = $(selectElement).attr("data-status");

    let statusTitle = actionButton == 'Delete' ? 'Confirm to Delete' : (status == 'Request' ? 'Confirm to Posted' : 'Confirm to Un Posted');
    let statusLabel = actionButton == 'Delete' ? 'Are you sure you want to <b class="text-danger">delete</b> this data' : (status == 'Request' ? 'Are you sure you want to <b class="text-success">posted</b> this data?' : 'Are you sure you want to <b class="text-warning">unposted</b> this data?');
    let mdiIcon = actionButton == 'Delete' ? '<i class="mdi mdi-trash-can label-icon"></i>Delete' : (status == 'Request' ? '<i class="mdi mdi-check-bold label-icon"></i>Posted' : '<i class="mdi mdi-arrow-left-top-bold label-icon"></i>Un Posted');
    let buttonClass = actionButton == 'Delete' ? 'btn-danger' : (status == 'Request' ? 'btn-success' : 'btn-warning');
    let attrFunction = actionButton == 'Delete' ? `bulkDeleted('${wo_number}');` : (status == 'Request' ? `bulkPosted('${wo_number}');` : `bulkUnPosted('${wo_number}');`);

    $('#staticBackdropLabel').text(statusTitle);
    $("#staticBackdrop .modal-body").html(statusLabel);
    $("#staticBackdrop button:last")
        .html(mdiIcon)
        .removeClass()
        .addClass(`btn waves-effect btn-label waves-light ${buttonClass}`)
        .attr('onClick', attrFunction);

    $('#staticBackdrop').modal('show');
}

function toggle(element) {
    $(element).slideToggle(500);
}
